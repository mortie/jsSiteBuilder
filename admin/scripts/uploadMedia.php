<?php
	if ($_FILES['file']['error']) {
		message($_FILES['file']['error']);
	} else {
		move_uploaded_file($_FILES['file']['tmp_name'], $root."media/".$_FILES['file']['name']);
	}

	$mysqli->query("INSERT INTO media (name) VALUES ('".$mysqli->real_escape_string($_FILES[file][name])."')");
	message($mysqli->error);

	header("Location: ?p=media");
