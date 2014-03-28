<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="/admin/style.css">
	</head>
	<body>
<?php
	if ($mysqli->query("SELECT * FROM entries WHERE updated=FALSE")->num_rows !== 0) {?>
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
	$names = [
		"editor"=>"Editor",
		"entries"=>"Entries",
		"postTypes"=>"Post Types",
		"settings"=>"Settings",
		"update"=>"Update"
	];

	$sPages = scandir("pages");
	foreach($sPages as $sPage) {
		$slug = rtrim($sPage, ".php");
		if (!empty($names[$slug])) {
			if ($slug == $page) {
				$current = "current";
			} else {
				$current = "";
			}
			echo template("navListEntry", [
				"url"=>"?p=$slug",
				"title"=>$names[$slug],
				"current"=>$current
			]);
		}
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
