var mysql = require('mysql');
var fs = require('fs');
var async = require('async');

var context = {};

async.series({
	"getSettings": function(next) {
		fs.readFile("settings.json", "utf8", function(err, data) {
			if (err)
				return console.log(err);

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
			if (err)
				return console.log(err);

			next();
		});
	},

	"setup": function(next) {
		fs.readFile("sqlsetup.txt", "utf8", function(err, data) {
			if (err)
				return console.log(err);

			context.connection.query(data, function(err, result) {
				if (err)
					return console.log(err);

				next();
			});
		})
	},

	"build": function(next) {
		console.log("This is where building and such will happen.");
	}
});
