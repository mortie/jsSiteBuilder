<?php
	requirePassword();

	foreach ($_POST as $rKey=>$rVal) {
		$sKey = $mysqli->real_escape_string($rKey);
		$sVal = $mysqli->real_escape_string($rVal);

		if ($sKey[0] == "m") {
			$sId = ltrim($sKey, "m");
			$mysqli->query("UPDATE media SET title='$sVal' WHERE id=$sId");
		} else if ($sKey[0] == "d") {
			$sId = ltrim($sKey, "d");
			$mysqli->query("DELETE FROM media WHERE id='$sId'");
		}

		message($mysqli->error);
	}

	$settings->updated = false;
	writeSettings();

	header("Location: ?p=media");
