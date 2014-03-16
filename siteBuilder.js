var mysql = require('mysql');
var fs = require('fs');
var async = require('async');
var markdown = require('markdown').markdown;

var context = {};

function log(text, shouldHalt) {
	console.log(text);

	if (context.settings.logToFile) {
		if (!fs.existsSync(context.settings.dir.log))
			fs.mkdirSync(context.settings.dir.log);

		var date = new Date();
		var dateString = date.getFullYear()+"."+date.getMonth()+"."+date.getDate();

		fs.writeFileSync(context.settings.dir.log+dateString, text+"\n") ;
	}
}

function error(explanation, err) {
	if (!err) {
		return false;
	}

	var errorText = explanation+" ("+err+")";
	log(errorText, true);
	process.exit();
}

function template(template, args, callback) {
	fs.readFile(context.settings.dir.templates, function(err, data) {
		error("Error reading template file.", err);

		var str = "";
		for (var i in args) {
			str.replace(new RegExp("{"+i+"}", "g"), args[i]);
		}

		callback(false, str);
	})
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
		fs.readFile("sqlsetup.txt", "utf8", function(err, data) {
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
							fs.createReadStream("media/"+file.name).pipe(fs.createWriteStream(context.settings.dir.out+"media/"+file.name));
						}.bind(file));
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
					log("HTML not updated for entry '"+entry.title+"'. Conveting from markdown...")
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
		
	}
});
