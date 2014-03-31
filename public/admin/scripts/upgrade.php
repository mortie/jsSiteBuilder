<?php
	chdir($root);
	message(exec("git fetch; git checkout --theirs ."));

	$settings->updated = false;
	writeSettings();
	header("Location: ?p=update");
