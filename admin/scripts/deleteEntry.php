<?php
	$id = $mysqli->real_escape_string($_GET['id']);
	$mysqli->query("DELETE FROM entries WHERE id=$id");
	header("Location: ?p=entries");
