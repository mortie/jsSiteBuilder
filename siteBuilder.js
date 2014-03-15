var mysql = require('mysql');
var fs = require('fs');
var async = require('async');
var markdown = require('markdown').markdown;

var context = {};

function log(text) {
	console.log(text)
}

function error(explanation, err) {
	if (!err) {
		return false;
	}

	var errorText = explanation+" ("+err+")";
	log(errorText);
	process.kill();
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

	"setup": function(next) {
		fs.readFile("sqlsetup.txt", "utf8", function(err, data) {
			error("Error reading SQL setup file.", err);

			query = data.replace(/\{db\}/g, context.settings.mysql.database);

			context.connection.query(query, function(err, result) {
				error("Error querying database.", err);

				next();
			});
		});
	},

	"buildTree": function(next) {
		context.tree = [];
		context.connection.query("SELECT * FROM articles ORDER BY sort, dateSeconds", function(err, result) {
			error("Error querying database.", err);

			for (var i=0; i<result.length; ++i) {
				var article = result[i];

				//update HTML if it's not already updated
				if (!article.updated) {
					log("HTML not updated for "+article.title+". Conveting from markdown...")
					article.html = markdown.toHTML(article.markdown);
				}

				//do the tree building
				if (!context.tree[article.type]) {
					context.tree[article.type] = [];
				}
				context.tree[article.type].push(article);

				//push HTML updates to the SQL if necessary
				if (!article.updated) {
					var values = {
						"updated": true,
						"html": article.html
					};
					context.connection.query("UPDATE articles SET ? WHERE id="+article.id+";", values, function(err, result) {
						error("Error querying database.", err);
					})
				}
			}
			next();
		});
	},

	"buildHTML": function(next) {
		log(context.tree);
	}
});
