var mysql = require('mysql');
var fs = require('fs');
var async = require('async');
var markdown = require('markdown').markdown;

var context = {};

function log(text) {
	console.log(text);

	if (context.settings.logToFile) {
		if (!fs.existsSync(context.settings.dir.log))
			fs.mkdirSync(context.settings.dir.log);

		var date = new Date();

		var yyyy = date.getFullYear();
		var mm = date.getMonth()+1;
		var dd = date.getDate();

		var hours = date.getHours();
		var minutes = date.getMinutes();
		var seconds = date.getSeconds();

		var path = context.settings.dir.log+yyyy+"."+mm+"."+dd;
		text = "["+hours+":"+minutes+":"+seconds+"] "+text;

		fs.appendFileSync(path, text+"\n");
	}
}

function error(explanation, err) {
	if (!err) {
		return false;
	}

	var errorText = explanation+" ("+err+")";
	log(errorText);
	process.exit(1);
}

function template(template, args, directString) {
	if (!directString) {
		try {
			var path = context.settings.dir.templates+
			           context.settings.display.templates+
			           "/"+template+".html";
			var str = fs.readFileSync(path, "utf8");
		} catch(err) {
			error("Error reading template file.", err);
		}
	} else {
		var str = template;
	}

	for (var i in args) {
		str = str.replace("{"+i+"}",  args[i], "g");
	}

	str = "<!--Start of template "+template+"-->\n"+str;
	str += "<!--End of template "+template+"-->\n";

	return str;
}

function buildMenu(currentEntry) {
	var pages = context.tree[0];

	if (!pages) {
		log("No pages available! Will not build website.");
		return;
	}

	var htmlNav = "";
	for (var i=0; i<pages.length; ++i) {
		var page = pages[i];

		if (currentEntry && currentEntry.slug == page.slug) {
			var current = "current";
		} else {
			var current = "";
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

async.series({
	"getSettings": function(next) {
		fs.readFile("settings.json", "utf8", function(err, data) {
			error("Error reading settings file.", err);

			context.settings = JSON.parse(data);
			next();
		});
	},

	"mysqlConnect": function(next) {
		context.connection = mysql.createConnection({
			"host": context.settings.mysql.host,
			"user": context.settings.mysql.user,
			"password": context.settings.mysql.password,
			"multipleStatements": true
		});

		context.connection.connect(function(err) {
			error("Error connecting to database.", err);

			next();
		});
	},

	"setupSql": function(next) {
		fs.readFile("sqlSetup.txt", "utf8", function(err, data) {
			error("Error reading SQL setup file.", err);

			query = data.replace(/\{db\}/g, context.settings.mysql.database);

			context.connection.query(query, function(err, result) {
				error("Error querying database.", err);

				next();
			});
		});
	},

	"setupMedia": function(next) {
		 fs.mkdir(context.settings.dir.out, function(err) {
			if (err && err.code != "EEXIST") {
				error("Error creating public dir", err);
			}

			fs.mkdir("media", function(err) {
				if (err && err.code != "EEXIST") {
					error("Error creating hidden media dir", err);
				}
			});

			fs.mkdir(context.settings.dir.out+"media", function(err) {
				if (err && err.code != "EEXIST") {
					error("Error creating public media dir", err);
				}

				context.connection.query("SELECT * FROM media WHERE updated=FALSE", function(err, result) {
					error(err);

					for (var i=0; i<result.length; ++i) {
						var file = result[i];

						fs.unlink(context.settings.dir.out+"media/"+result.name, function(err) {
							error("Error deleting file", err);

							//copy from media/ to public/media/
							fs.createReadStream("media/"+file.name).pipe(
								fs.createWriteStream(context.settings.dir.out+"media/"+file.name)
							);
						}.bind(file));t
					}
				});
			});

		 	//fixing media things in the file system can be done async; the rest will just pretend it is there already.
			next()
		 });
	},

	"buildTree": function(next) {
		context.tree = [];
		context.connection.query("SELECT * FROM entries ORDER BY sort, dateSeconds", function(err, result) {
			error("Error querying database.", err);

			for (var i=0; i<result.length; ++i) {
				var entry = result[i];

				//update HTML if it's not already updated
				if (!entry.updated) {
					log("HTML not updated for entry '"+entry.title+"'. Conveting from markdown...");
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
					context.connection.query("UPDATE entries SET ? WHERE id="+entry.id+";", values, function(err, result) {
						error("Error querying database.", err);
					});
				}
			}
			next();
		});
	},

	"buildHTML": function(next) {
		context.html = {};

		for (var j=0; j<context.tree.length; ++j) {
			for (var i=0; i<context.tree[j].length; ++i) {
				var entry = context.tree[j][i];

				var entryHTML = template("index", {
					"title": entry.title+" - "+context.settings.display.title,
					"menu": buildMenu(entry),
					"content": entry.html
				});

				var path=context.settings.dir.out+entry.slug;

				log("Writing '"+entry.title+"' to "+path);

				fs.writeFile(path, entryHTML, function(err) {
					error("Error writing file to public dir.", err);
				});
			}
		}
	}
});
