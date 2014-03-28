<?php
	requirePassword();

	$id = $mysqli->real_escape_string($_GET['id']);
	$mysqli->query("DELETE FROM entries WHERE id=$id");

	$settings->updated = false;
	writeSettings();

	header("Location: ?p=entries");
