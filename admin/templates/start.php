<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="/admin/style.css">
		<meta charset="utf-8">
	</head>
	<body>
<?php
	if (!$settings->updated) {?>
		<div class="page">
			<form action="?s=runNode" method="post">
				You need to run the update script for your changes to take effect.
				<button>Update</button>
			</form>
		</div>
	<?php }
?>

		<div id="nav">
			<ul>
<?php
	$pages = [
		"editor"=>"Editor",
		"entries"=>"Entries",
		"categories"=>"Categories",
		"settings"=>"Settings",
		"update"=>"Update",
		"media"=>"Media"
	];

	foreach ($pages as $slug=>$title) {
		if ($slug == $page) {
			$current = "current";
		} else {
			$current = "";
		}
		echo template("navListEntry", [
			"url"=>"?p=$slug",
			"title"=>$title,
			"current"=>$current
		]);
	}

	echo template("navListEntry", [
		"url"=>"/",
		"title"=>"Home",
		"current"=>""
	]);
?>
			</ul>
		</div>

		<div id="<?=$page ?>" class="page">
			<div id="message">
<?=getMessage() ?>
			</div>
