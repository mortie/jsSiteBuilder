<?php
	requirePassword();

	foreach ($_POST as $rKey=>$rVal) {
		$sKey = $mysqli->real_escape_string($rKey);
		$sVal = $mysqli->real_escape_string($rVal);

		if ($sKey[0] == "t") {
			$sId = ltrim($sKey, "t");
			if ($sKey === "tnew" && $sVal) {
				$mysqli->query("INSERT INTO categories (name) VALUES ('$sVal')");
			} else if ($sVal){
				$mysqli->query("UPDATE categories SET name='$sVal' WHERE id=$sId");
			}
		} else if ($sKey[0] == "d") {
			$sId = ltrim($sKey, "d");
			$mysqli->query("DELETE FROM categories WHERE id=$sId");
		}

		message($mysqli->error);
	}

	header("Location: ?p=categories");
