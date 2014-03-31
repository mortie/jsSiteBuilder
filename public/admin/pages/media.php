<form class="hidden" method="post" id="uploadForm" action="?s=uploadMedia" enctype="multipart/form-data">
	<input class="hidden" type="file" name="file" id="file" onchange="document.getElementById('uploadForm').submit()">
</form>

<form method="post" id="modifyForm" action="?s=updateMedia">
<?php
	requirePassword();

	$media = $mysqli->query("SELECT * FROM media");

	if ($media) {
		while ($mediaItem = $media->fetch_assoc()) {
			echo template("mediaListEntry", [
				"name"=>$mediaItem['title'],
				"id"=>$mediaItem['id']
			]);
		}
	}
?>
</form>

<?php
	addNav("<button onclick=\"document.getElementById('modifyForm').submit()\">Submit</button>");
	addNav("<button onclick=\"document.getElementById('file').click()\">Upload</button>");