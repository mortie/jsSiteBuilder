<?php
	foreach($_POST as $key=>$val) {
		$exploded = explode("->", $key);
		if (empty($exploded[1])) {
			$settings->$exploded[0] = htmlentities($val);
		} else {
			$settings->$exploded[0]->$exploded[1] = htmlentities($val);
		}
	}
	$settings->updated = false;
	writeSettings();

	header("Location: ?p=settings");
