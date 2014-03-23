<?php
	requirePassword();

	$entries = $mysqli->query("SELECT * FROM entries ORDER BY type, sort, dateSeconds");

	while ($entry = $entries->fetch_assoc()) {
		if ($entry['type'] == 0) {
			$type = "page";
		} else {
			$type = "post";
		}
		$type = "<span style='display: inline-block; width: 70px'>[$type]</span>";
		echo "$type<a href='?p=editor&id=".$entry['id']."'>".$entry['title']."</a><br>\n";
	}

	addNav("<a href='?'><button>Back</button></a>");
	addNav("<a href='?p=editor'><button>New Entry</button></a>");
