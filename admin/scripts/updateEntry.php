<?php
	$entry = [
		"markdown"=>$mysqli->real_escape_string($_POST['markdown']),
		"updated"=>false,
		"slug"=>$mysqli->real_escape_string($_POST['slug']),
		"title"=>$mysqli->real_escape_string($_POST['title']),
		"id"=>$mysqli->real_escape_string($_POST['id'])
	];
	if (empty($_POST['allposts'])) {
		$entry['allposts'] = 0;
	} else {
		$entry['allposts'] = $mysqli->real_escape_string($S_POST['allposts']);
	}

	if (!)
