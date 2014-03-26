<?php
	foreach($_POST as $key=>$val) {
		$exploded = explode("->", $key);
		if (empty($exploded[1])) {
			$settings->$exploded[0] = htmlentities($val);
		} else {
			$settings->$exploded[0]->$exploded[1] = htmlentities($val);
		}
	}

	$settings = json_encode($settings, JSON_PRETTY_PRINT);

	if (!file_put_contents($root."settings.json", $settings)) {
		message("Couldn't write settings file. Make sure PHP has write access.");
	}
	header("Location: ?p=settings");
