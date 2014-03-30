var context = {};
context.callbacks = 0;
context.caches = {};
context.startTime = (new Date()).getTime();

var mysql = require('mysql');
var fs = require('fs');
var async = require('async');
var markdown = require('markdown').markdown;
var wrench = require('wrench');

async.series({
	"getSettings": function(next) {
		++context.callbacks;
		fs.readFile("settings.json", "utf8", function(err, data) {
			error("Reading settings file failed.", err);

			context.settings = JSON.parse(data);
			next();
			--context.callbacks;
		});
	},

	"mysqlConnect": function(next) {
		context.connection = mysql.createConnection({
			"host": context.settings.mysql.host,
			"user": context.settings.mysql.user,
			"password": context.settings.mysql.password,
			"multipleStatements": true
		});

		++context.callbacks;
		context.connection.connect(function(err) {
			error("Connecting to database failed.", err);

			next();
			--context.callbacks;
		});
	},

	"setupSql": function(next) {
		++context.callbacks;
		fs.readFile("sqlSetup.txt", "utf8", function(err, data) {
			error("Reading SQL setup file failed.", err);

			var query = data.replace(/\{db\}/g, context.settings.mysql.database);

			++context.callbacks;
			context.connection.query(query, function(err, result) {
				error("Querying database failed.", err);

				next();
				--context.callbacks;
			});
			--context.callbacks;
		});
	},

	"setupMedia": function(next) {
		++context.callbacks;
		fs.mkdir(context.settings.dir.out, function(err) {
			if (err && err.code != "EEXIST") {
				error("Creating public dir failed.", err);
			}

			++context.callbacks;
			fs.mkdir(context.settings.dir.media, function(err) {
				if (err && err.code != "EEXIST") {
					error("Creating hidden media dir failed.", err);
				}
				--context.callbacks;
			});

			++context.callbacks;
			fs.mkdir(context.settings.dir.out+"media", function(err) {
				if (err && err.code != "EEXIST") {
					error("Creating public media dir failed.", err);
				}

				++context.callbacks;
				context.connection.query("SELECT * FROM media WHERE updated=FALSE", function(err, result) {
					error("Querying database failed.", err);

					for (var i=0; i<result.length; ++i) {
						var file = result[i];

						++context.callbacks;
						fs.unlink(context.settings.dir.out+"media/"+file.name, function(err) {
							if (err) {
								log("Deleting file failed. ("+err+")", 2);
							}

							//copy from media/ to public/media/
							try {
								fs.createReadStream("media/"+file.name).pipe(
									fs.createWriteStream(context.settings.dir.out+"media/"+file.name)
								);
							} catch (err) {
								log("Copying file failed. ("+err+")", 2);
							}

							++context.callbacks;
							context.connection.query("UPDATE media SET updated=TRUE WHERE id="+file.id, function(err, result) {
								error("Querying database failed.", err);
								--context.callbacks;
							});

							--context.callbacks;
						}.bind(file));
					}
					--context.callbacks;
				});
				--context.callbacks;
			});

			//fixing media things in the file system can be done async; the rest will just pretend it is there already.
			next();
			--context.callbacks;
		});
	},

	"setupTheme": function(next) {
		var path = context.settings.dir.themes+context.settings.display.theme+"/";

		++context.callbacks;
		fs.exists(path, function(exists) {
			if (exists) {
				++context.callbacks;
				fs.readdir(path, function(err, result) {
					error("Reading theme directory failed.", err);

					var css = "";
					for (var i=0; i<result.length; ++i) {
						css += "/*Start of "+result[i]+"*/\n";
						css += fs.readFileSync(path+result[i])+"\n";
						css += "/*End of "+result[i]+"*/\n";
					}

					++context.callbacks;
					fs.writeFile(context.settings.dir.out+"style.css", css, function(err) {
						error("Writing CSS file failed.", err);
						--context.callbacks;
					});

					--context.callbacks;
				}.bind(path));
				--context.callbacks;
			}
		}.bind(path));

		//moving CSS files can also be done async.
		next();
	},

	"setupAdmin": function(next) {
		try {
			wrench.copyDirSyncRecursive("admin", context.settings.dir.out+"admin", {
				"forceDelete": true
			});
		} catch (err) {
			error("Copying admin dir failed.", err);
		}
		next();
	},

	"buildTree": function(next) {
		context.tree = [];
		++context.callbacks;
		context.connection.query("SELECT * FROM entries ORDER BY sort, dateSeconds DESC", function(err, result) {
			error("Querying database failed.", err);

			for (var i=0; i<result.length; ++i) {
				var entry = result[i];

				//update HTML if it's not already updated
				if (!entry.updated) {
					log("HTML not updated for entry '"+entry.title+"'. Conveting from markdown...", 0);
					entry.html = markdown.toHTML(entry.markdown);
				}

				//do the tree building
				if (!context.tree[entry.type]) {
					context.tree[entry.type] = [];
				}
				context.tree[entry.type].push(entry);

				//push HTML updates to the SQL if necessary
				if (!entry.updated) {
					var values = {
						"updated": true,
						"html": entry.html
					};
					++context.callbacks;
					context.connection.query("UPDATE entries SET ? WHERE id="+entry.id+";", values, function(err, result) {
						error("Querying database failed.", err);
						--context.callbacks;
					});
				}
			}
			next();
			--context.callbacks;
		});
	},

	"buildHTML": function(next) {
		context.html = [];

		for (var i=0; i<context.tree.length; ++i) {
			if (!context.tree[i]) {
				continue;
			}
			for (var j=0; j<context.tree[i].length; ++j) {
				var entry = context.tree[i][j];

				var entryHTML = template("index", {
					"title": entry.title+" - "+context.settings.display.title,
					"menu": buildMenu(entry),
					"content": parseEntry(entry)
				});

				context.html.push({
					"html": entryHTML,
					"metadata": entry
				});
			}
		}
		next();
	},

	"writeHTML": function(next) {
		var path;
		for (var i=0; i<context.html.length; ++i) {
			var entry = context.html[i];

			path = context.settings.dir.out+entry.metadata.slug+"/";
			writeEntry(path, entry);
		}
		if (context.html[0]) {
			path = context.settings.dir.out;
			writeEntry(path, context.html[0]);
		} else {
			log("No entries to write.", 1);
		}

		next();
	},

	"cleanup": function(next) {
		setInterval(function() {
			if (context.callbacks === 0) {
				var endTime = (new Date()).getTime();
				log("Completed in "+(endTime-context.startTime)+" milliseconds.", 0);
				process.exit();
			}
		}, 0);
	}
});

function buildMenu(currentEntry) {
	var pages = context.tree[0];

	if (!pages) {
		log("No pages available! Will not build website.");
		return;
	}

	var htmlNav = "";
	var current;
	for (var i=0; i<pages.length; ++i) {
		var page = pages[i];

		if (currentEntry && currentEntry.slug == page.slug) {
			current = "current";
		} else {
			current = "";
		}

		htmlNav += template("navEntry", {
			"slug": page.slug,
			"title": page.title,
			"current": current
		});
	}

	return template("menu", {
		"title": context.settings.display.title,
		"nav": htmlNav,
	});
}

function parseEntry(entry) {
	var entryText = "";
	var i;
	var j;
	var placeholders;

	log("parsing "+entry.title, 0);

	//do all allposts stuff
	if (!entry.allposts) { //if allposts is 0, just output the entry itself
		entryText = template("entry", {
			"title": entry.title,
			"date": parseDate(entry.dateSeconds),
			"content": entry.html
		});
	} else if (context.tree[entry.allpostsType]) {
		var numEntries = context.tree[entry.allpostsType].length;
		for (i=0; i<numEntries; ++i) { //of not, loop through all entries requested by the entry...;
			var addEntry = context.tree[entry.allpostsType][i];
			log("Adding "+addEntry.title+" to "+entry.title+".", 0);

			if (entry.allposts === 1) { //if allposts is 1, just add a link
				var addEntryHTML = template("allpostsLink", {
					"slug": addEntry.slug,
					"title": addEntry.title
				});
			} else if (entry.allposts === 2) { //if allposts is 2, add a few paragraphs from the entry
				var paragraphs = addEntry.html.split("</p>");
				var addEntryText = "";
				for (j=0; j<=context.settings.allpostsShortLength; ++j) {
					if (paragraphs[j]) {
						addEntryText += paragraphs[j]+"</p>";
					}
				}

				var addEntryHTML = template("entry", {
					"title": template("link", {
						"title": addEntry.title,
						"url": "/"+addEntry.slug
					}),
					"date": parseDate(addEntry.dateSeconds),
					"content": addEntryText+template("readMore", {
						"slug": addEntry.slug
					})
				});
			} else if (entry.allposts === 3) { // if allposts is 3, add the whole entry
				var addEntryHTML = template("entry", {
					"title": template("link", {
						"title": addEntry.title,
						"url": "/"+addEntry.slug
					}),
					"date": parseDate(addEntry.dateSeconds),
					"content": addEntry.html
				});
			}
			entryText += addEntryHTML;
		}
	}

	//fix {placeHolders}
	var placeHolders = entryText.match(/\{.+\}/);
	if (placeHolders) {
		for (i=0; i<placeHolders.length; ++i) {
			var placeHolder = placeHolders[i].replace(/[\{\}\s+]/g, "").split(",");

			if (placeHolder[0] == "img" || placeHolder[0] == "video") {
				entryText = entryText.replace(placeHolders[i], template("media", {
					"tag": placeHolder[0],
					"src": "media/"+placeHolder[1],
					"desc": placeHolder[2]
				}));
			}
		}
	}


	if (entry.allposts === 1) {
		return template("entry", {
			"title": "",
			"date": "",
			"content": entryText
		});
	}
	return entryText;
}

function parseDate(seconds) {
	var date = new Date(seconds*1000);
	return date.toDateString();
}

function writeEntry(path, entry) {
	if (entry) {
		log("Writing '"+entry.metadata.title+"' to "+path+"index.html.", 0);

		++context.callbacks;
		fs.mkdir(path, function(result, err) {
			if (err && err.code != "EEXIST") {
				error("Making entry dir failed.", err);
			}

			++context.callbacks;
			fs.writeFile(path+"index.html", entry.html, function(err) {
				error("Writing file to public dir failed.", err);
				--context.callbacks;
			});
			--context.callbacks;
		});
	} else {
		log("Couldn't write entry.", 2);
	}
}

function template(tmp, args) {
	if (!context.caches.template) {
		context.caches.template = {};
	}
	if (!context.caches.template[tmp]) {
		try {
			var path = context.settings.dir.templates+
			           context.settings.display.templates+
			           "/"+tmp+".html";
			context.caches.template[tmp] = fs.readFileSync(path, "utf8");
		} catch(err) {
			try {
				var path = context.settings.dir.templates+
				           "default"+
				           "/"+tmp+".html";
				context.caches.template[tmp] = fs.readFileSync(path, "utf8");
			} catch(err) {
				error("Reading template file failed.", err);
			}
		}
	}

	var str = context.caches.template[tmp];

	for (var i in args) {
		if (args.hasOwnProperty(i)) {
			if (!args[i]) {
				args[i] = "";
			}
			str = str.split("{"+i+"}").join(args[i]);
		}
	}

	str = "<!--Start of template "+tmp+"-->\n"+str;
	str += "<!--End of template "+tmp+"-->\n";

	return str;
}

var errorGrades = [
	"Info   ",
	"Notice ",
	"Warning",
	"Error  "
];

function log(text, grade) {
	if (!grade) {
		grade = 0;
	}

	text = errorGrades[grade]+": "+text;
	console.log(text);

	if (context.settings.logToFile) {
		try {
			if (!fs.existsSync(context.settings.dir.log)) {
				fs.mkdirSync(context.settings.dir.log);
			}
		} catch(err) {
			console.log(errorGrades[2]+": Could not create log dir! ("+err+")");
		}

		if (!context.caches.log) {
			context.caches.log = {};

			var date = new Date();

			var yyyy = new String(date.getFullYear());	
			var mm = new String(date.getMonth()+1);
			    if (mm.length < 2) mm = "0"+mm;
			var dd = new String(date.getDate());
			    if (dd.length < 2) dd = "0"+dd;

			var hours = new String(date.getHours());
			    if (hours.length < 2) hours = "0"+hours;
			var minutes = new String(date.getMinutes());
			    if (minutes.length < 2) minutes = "0"+minutes;
			var seconds = new String(date.getSeconds());
			    if (seconds.length < 2) seconds = "0"+seconds;

			context.caches.log.dateString = yyyy+"."+mm+"."+dd;
			context.caches.log.timeString = hours+":"+minutes+":"+seconds;
		}

		var path = context.settings.dir.log+context.caches.log.dateString;
		text = "["+context.caches.log.timeString+"] "+text;

		try {
			fs.appendFileSync(path, text+"\n");
		} catch(err) {
			console.log(errorGrades[2]+": Could not write to log file! ("+err+")");
		}
	}
}

function error(explanation, err) {
	if (!err) {
		return false;
	}

	var errorText = explanation+" ("+err+")";
	log(errorText, 3);
	process.exit(1);
}
