<?php
	requirePassword();

	chdir($root);
	message(exec($settings->nodeCommand." siteBuilder.js"));
	if (!empty($_SERVER['HTTP_REFERER'])) {
		header("Location: ".$_SERVER['HTTP_REFERER']);
	} else {
		header("Location: ?");
	}
