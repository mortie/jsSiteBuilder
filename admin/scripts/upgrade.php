<?php

	//use git to update
	chdir($root);
	message(exec("git reset --hard; git pull"));

	//read new settings file
	$newSettings = json_decode(file_get_contents("settings.json"));

	//merge new settings file to old settings array
	$settings = array_merge($newSettings, $settings);

	//write the resulting settings
	$settings->updated = false;
	writeSettings(true);

	header("Location: ?p=update");
