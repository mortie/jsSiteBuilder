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
		<div id="<?=$page ?>" class="page">
			<div id="message">
				<?=getMessage() ?>
			</div>
