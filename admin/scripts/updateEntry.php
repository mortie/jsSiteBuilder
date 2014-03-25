<?php
	requirePassword();

	$eMarkdown = $mysqli->real_escape_string($_POST['markdown']);
	$eSlug = $mysqli->real_escape_string(htmlentities($_POST['slug']));
	$eTitle = $mysqli->real_escape_string(htmlentities($_POST['title']));
	$eId = $mysqli->real_escape_string($_POST['id']);
	$eType = $mysqli->real_escape_string($_POST['type']);
	$eSort = $mysqli->real_escape_string($_POST['sort']);
	if (empty($_POST['allposts'])) {
		$eAllposts = 0;
	} else {
		$eAllposts = $mysqli->real_escape_string($_POST['allposts']);
	}
	$eAllpostsType = $mysqli->real_escape_string($_POST['allpostsType']);

	if ($eId == "") {
		while($mysqli->query("SELECT * FROM entries WHERE slug='$eSlug'")->num_rows !== 0) {
			$eSlug .= "_"; 
		}
		$mysqli->query("INSERT INTO entries (markdown, updated, slug, title, allposts, allpostsType, type, dateSeconds, sort) VALUES ('$eMarkdown', FALSE, '$eSlug', '$eTitle', '$eAllposts', '$allpostsType', '$eType', ".time().", $eSort)");
		message($mysqli->error);
		header("Location: ?p=editor&id=$mysqli->insert_id");
	} else {
		$mysqli->query("UPDATE entries SET markdown='$eMarkdown', updated=FALSE, slug='$eSlug', title='$eTitle', allposts='$eAllposts', allpostsType='$eAllpostsType', type='$eType', sort='$eSort' WHERE id=$eId");
		message($mysqli->error);
		header("Location: ?p=editor&id=$eId");
	}
