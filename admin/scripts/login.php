<?php
	$pass = $_POST['pass'];

	if ($pass === $settings->adminPass) {
		$_SESSION['authorized'] = true;
		header("Location: ?");
		die();
	} else {
		message("Wrong password");
		header("Location: ?p=login");
		die();
	}
