<?php
	requirePassword();

	$eMarkdown = $mysqli->real_escape_string($_POST['markdown']);
	$eSlug = $mysqli->real_escape_string($_POST['slug']);
	$eTitle = $mysqli->real_escape_string($_POST['title']);
	$eId = $mysqli->real_escape_string($_POST['id']);
	$eType = $mysqli->real_escape_string($_POST['type']);
	if (empty($_POST['allposts'])) {
		$eAllposts = 0;
	} else {
		$eAllposts = $mysqli->real_escape_string($_POST['allposts']);
	}
	$eAllpostsType = $mysqli->real_escape_string($_POST['allpostsType']);

	if ($eId == "") {
		$mysqli->query("INSERT INTO entries (markdown, updated, slug, title, allposts, allpostsType, type, dateSeconds) VALUES ('$eMarkdown', FALSE, '$eSlug', '$eTitle', '$eAllposts', '$allpostsType', '$eType', ".time().")");
		header("Location: ?p=editor&id=".$mysqli->insert_id);
	} else {
		$mysqli->query("UPDATE entries SET markdown='$eMarkdown', updated=FALSE, slug='$eSlug', title='$eTitle', allposts='$eAllposts', allpostsType='$eAllpostsType', type='$eType' WHERE id=$eId");
		header("Location: ?p=editor&id=$eId");
	}
