<?php
	requirePassword();

	foreach ($_POST as $rKey=>$rVal) {
		$sKey = $mysqli->real_escape_string($rKey);
		$sVal = $mysqli->real_escape_string($rVal);

		if ($sKey[0] == "m") {
			$sId = ltrim($sKey, "m");
			$oldName = $mysqli->query("SELECT name FROM media WHERE id=$sId")->fetch_assoc()['name'];

			rename($root.$settings->dir->media.$oldName, $root.$settings->dir->media.$sVal);

			$mysqli->query("UPDATE media SET name='$sVal' WHERE id=$sId");
		} else if ($sKey[0] == "d") {
			$sId = ltrim($sKey, "d");
			$name = $mysqli->query("SELECT name FROM media WHERE id=$sId")->fetch_assoc()['name'];

			$mysqli->query("DELETE FROM media WHERE id='$sId'");

			unlink($root.$settings->dir->media.$name);
		}

		message($mysqli->error);
	}

	header("Location: ?p=media");
