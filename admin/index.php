<?php
	ini_set('display_startup_errors',1);
	ini_set('display_errors',1);
	error_reporting(-1);

	session_start();

	$root = "../../";
	$settings = json_decode(file_get_contents($root."settings.json"));

	$mysqli = new mysqli(
		$settings->mysql->host,
		$settings->mysql->user,
		$settings->mysql->password,
		$settings->mysql->database
	);

	$navBar = [];
	function addNav($text) {
		global $navBar;
		array_push($navBar, $text);
	}

	function requirePassword() {
		if ($_SESSION['authorized'] === true) {
			return true;
		} else {
			header("Location: ?p=login");
			die();
		}
	}

	function message($message) {
		if (empty($_SESSION['messagee'])) {
			$_SESSION['message'] = $message;
		} else {
			$_SESSION['message'] += "<br>$message";
		}
	}

	function getMessage() {
		if (empty($_SESSION['message'])) {
			$str = "";
		} else {
			$str = $_SESSION['message'];
		}
		$_SESSION['message'] = "";
		return $str;
	}

	addNav("<a href='/'><button>Home</button></a>");

	if (!empty($_GET['s'])) {
		include("scripts/".$_GET['s'].".php");
	} else {
		if (!empty($_GET['p'])) {
			$page = $_GET['p'];
		} else {
			$page = "index";
		}

		include("templates/start.html");
		include("pages/$page.php");
		include("templates/end.html");
	}
