<?php
	requirePassword();

	$entries = $mysqli->query("SELECT * FROM entries ORDER BY category, sort, dateSeconds");

	while ($entry = $entries->fetch_assoc()) {
		if ($entry['category'] == 0) {
			$type = "Page";
		} else {
			$type = $mysqli->query("SELECT * FROM categories WHERE id=".$entry['category'])->fetch_assoc()['name'];
		}
		$type = "<span style='display: inline-block; width: 70px'>$type</span>";
		echo "$type<a href='?p=editor&id=".$entry['id']."'>".$entry['title']."</a><br>\n";
	}

	addNav(template("buttonLink", [
		"url"=>"?p=editor",
		"title"=>"New Entry"
	]));
