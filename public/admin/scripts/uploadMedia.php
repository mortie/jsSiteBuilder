<?php
	if ($_FILES['file']['error']) {
		message($_FILES['file']['error']);
	} else {
		$file = $_FILES['file'];
		$fileInfo = pathinfo($file['name']);

		$content = $mysqli->real_escape_string(file_get_contents($file['tmp_name']));
		$extension = $fileInfo['extension'];
		$title = htmlentities($fileInfo['filename']);

		$mysqli->query("INSERT INTO media SET title='$title', type='$file[type]', content='$content', extension='$extension'");
		message($mysqli->error);
	}

	$settings->updated = false;
	writeSettings();

	header("Location: ?p=media");
