<?php
	requirePassword();

	$id = $mysqli->real_escape_string($_GET['id']);
	$mysqli->query("DELETE FROM entries WHERE id=$id");

	//update entries which list this entry
	$mysqli->query("UPDATE entries SET updated=0 WHERE listCategory=".$entry['category']);

	$settings->updated = false;
	writeSettings();

	header("Location: ?p=entries");
