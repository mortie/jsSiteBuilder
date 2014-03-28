<?php
	requirePassword();

	chdir($root);

	$result = exec($settings->nodeCommand." siteBuilder.js");
	if ($result == "") {
		message("Couldn't run command. See if your \"Node.js Command\" setting is correct.");
	} else {
		message($result);
	}

	if (!empty($_GET['t'])) {
		header("Location: ?p=".$_GET['t']);
	} else if (!empty($_SERVER['HTTP_REFERER'])) {
		header("Location: ".$_SERVER['HTTP_REFERER']);
	} else {
		header("Location: ?");
	}
