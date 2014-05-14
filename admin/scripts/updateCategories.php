<?php
	requirePassword();

	foreach ($_POST as $rKey=>$rVal) {
		$sKey = $mysqli->real_escape_string($rKey);
		$sVal = $mysqli->real_escape_string($rVal);

		if ($sKey[0] == "t") {
			$sId = ltrim($sKey, "t");
			if ($sKey === "tnew" && $sVal != "") {
				$mysqli->query("INSERT INTO categories (name) VALUES ('$sVal')");
			} else {
				$mysqli->query("UPDATE types SET name='$sVal' WHERE id=$sId");
			}	
		} else if ($sKey[0] == "d") {
			$sId = ltrim($sKey, "d");
			$mysqli->query("DELETE FROM types WHERE id=$sId");
		}

		message($mysqli->error);
	}

	header("Location: ?p=postTypes");
