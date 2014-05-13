<?php
	requirePassword();

	//build entry array
	$entry = [];
	foreach($_POST as $key=>$val) {
		$entry[$key] = $mysqli->real_escape_string($val);
	}
	$entry['updated'] = false;

	if ($entry['id'] == "") {

		//fix slug conflicts
		$modifiedSlug = false;
		while($mysqli->query("SELECT * FROM entries WHERE slug='".$entry['slug']."'")->num_rows !== 0) {
			$entry['slug'] .= "_"; 
			$modifiedSlug = true;
		}
		if ($modifiedSlug) {
			message("Slug has been changed from ".$_POST['slug']." to ".$entry['slug'].".");
		}

		//create new, empty entry to be populated later
		$mysqli->query("INSERT INTO `entries` (`id`) VALUES (NULL)");
		$entry['id'] = $mysqli->insert_id;

		message($mysqli->error);
		$entry['dateSeconds'] = time();
	}

	//create SQL string
	$firstRun = true;
	foreach($entry as $key=>$val) {
		if ($firstRun) {
			$str = "$key=\"$val\"";
			$firstRun = false;
		} else {
			$str .= ", $key=\"$val\"";
		}
	}

	//insert updated data into the entry
	$mysqli->query("UPDATE entries SET $str WHERE id=".$entry['id']);
	message($mysqli->error);

	$settings->updated = false;
	writeSettings();

	header("Location: ?p=editor&id=".$entry['id']);
