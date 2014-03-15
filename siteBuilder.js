var mysql = require('mysql');
var fs = require('fs');
var async = require('async');

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

	"build": function(next) {
		context.connection.query("SELECT * FROM articles", function(err, result) {
			error("Error querying database.", err);

			console.log(result);
		});
	}
});
