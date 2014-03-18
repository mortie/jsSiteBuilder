var context = {};
context.callbacks = 0;
context.caches = {};
context.startTime = (new Date()).getTime();

var mysql = require('mysql');
var fs = require('fs');
var async = require('async');
var markdown = require('markdown').markdown;

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
			fs.mkdir("media", function(err) {
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
						fs.unlink(context.settings.dir.out+"media/"+result.name, function(err) {
							error("Deleting file failed.", err);

							//copy from media/ to public/media/
							fs.createReadStream("media/"+file.name).pipe(
								fs.createWriteStream(context.settings.dir.out+"media/"+file.name)
							);
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

	"buildTree": function(next) {
		context.tree = [];
		++context.callbacks;
		context.connection.query("SELECT * FROM entries ORDER BY sort, dateSeconds", function(err, result) {
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
		path = context.settings.dir.out;
		writeEntry(path, context.html[0]);

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

var errorGrades = [
	"Infio  ",
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
		if (!fs.existsSync(context.settings.dir.log)) {
			fs.mkdirSync(context.settings.dir.log);
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

		fs.appendFileSync(path, text+"\n");
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
			error("Reading template file failed.", err);
		}
	}

	var str = context.caches.template[tmp];

	for (var i in args) {
		if (args.hasOwnProperty(i)) {
			str = str.replace("{"+i+"}",  args[i], "g");
		}
	}

	str = "<!--Start of template "+tmp+"-->\n"+str;
	str += "<!--End of template "+tmp+"-->\n";

	return str;
}

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
	var entryText = entry.html;

	var matches = entryText.match(/{.+}/);
	if (matches) {
		for (var i=0; i<matches.length; ++i) {
			var match = matches[i]
			    .replace("{", "")
			    .replace("}", "")
			    .replace(/\s+/g, "")
			    .split(",");

			if (!match[2]) {
				match[2] = 1;
			}

			if (match[0] == "allposts") {
				if (context.tree[match[2]]) {
					for (var j=0; j<context.tree[match[2]].length; ++j) {
						var entryToAdd = context.tree[match[2]];
					}
				} else {
					log("Entry '"+entry.title+"' requested all entries with type "+match[2]+", but no entries with type "+match[2]+" was found.", 1);
				}
			}
		}
	}
	return entryText;
}

function writeEntry(path, entry) {
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
}
