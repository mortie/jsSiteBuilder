<?php
	$entries = $mysqli->query("SELECT * FROM entries ORDER BY type, sort, dateSeconds");

	while ($entry = $entries->fetch_assoc()) {
		echo "<a href='?p=editor&id=".$entry['id']."'>".$entry['title']."</a><br>";
	}

	addNav("<a href='admin'><button>Back</button></a>");
	addNav("<a href='?p=editor'><button>New Entry</button></a>");
