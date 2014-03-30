<form class="hidden" method="post" id="form" action="?s=uploadMedia" enctype="multipart/form-data" onclick="document.getElementById('file').click()">
	<input class="hidden" type="file" name="file" id="file" onchange="document.getElementById('form').submit()">
</form>
<?php
	requirePassword();

	$media = $mysqli->query("SELECT * FROM media");

	while ($medium = $media->fetch_assoc()) {
		echo template("mediaListEntry", [
			"name"=>$medium['name']
		]);
	}

	addNav("<button onclick=\"document.getElementById('file').click()\">Upload</button>");
