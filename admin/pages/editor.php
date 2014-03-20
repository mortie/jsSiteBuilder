<?php
	if (!empty($_GET['id'])) {
		$id = $mysqli->real_escape_string($_GET['id']);
		$entry = $mysqli->query("SELECT * FROM entries WHERE id='$id'")->fetch_assoc();
	} else {
		$entry = [
			"title"=>"",
			"slug"=>"",
			"allposts"=>0,
			"markdown"=>"",
			"id"=>""
		];
	}
?>

<script>
	function useEditor(editor) {
		var text = document.getElementById("textEditor");
		var allposts = document.getElementById("allpostsEditor");
		if (editor == "text") {
			text.style.display = "";
			allposts.style.display = "none";
		} else if (editor == "allposts") {
			text.style.display = "none";
			allposts.style.display = "";
		}
	}

	function updateTextEditorContent() {
		var input = document.getElementById("textEditorContent");
		var div = document.getElementById("textEditor");
		input.value = div.innerText || div.textContent;
	}
</script>

<form id="form" method="post" action="?s=updateEntry">
	<div class="section">
		Title:
		<input name="title" type="text" class="wide" value="<?=$entry['title'] ?>">
	</div>

	<div class="section">
		Slug:
		<input name="slug" type="text" class="wide" value="<?=$entry['slug'] ?>">
	</div>

	<input type="hidden" name="id" value="<?=$entry['id'] ?>">

	<div class="section">
	<label><input type="radio" name="pageOrPost" onclick="useEditor('text')" <?php if ($entry['allposts'] == 0) echo "checked" ?>>Entry</label>
	<label><input type="radio" name="pageOrPost" onclick="useEditor('allposts') <?php if ($entry['allposts'] != 0) echo "checked" ?>">List</label>

		<div id="textEditor">
			<div id="textarea" contenteditable="true"><?=$entry['markdown'] ?></div>
			<input type="hidden" name="markdown" id="textEditorContent">
		</div>

		<div id="allpostsEditor">
			<br>
			List type:<br>
			<label><input type="radio" name="allposts" value="1" <?php if ($entry['allposts'] == 1) echo "checked" ?>>Link</label><br>
			<label><input type="radio" name="allposts" value="2" <?php if ($entry['allposts'] == 2) echo "checked" ?>>Short</label><br>
			<label><input type="radio" name="allposts" value="3" <?php if ($entry['allposts'] == 3) echo "checked" ?>>Full</label><br>
		</div>
	</div>
</form>

<script>
	if (<?=$entry['allposts'] ?> == 0) {
		useEditor("text");
	} else {
		useEditor("allposts");
	}
	var TAB = "\t";

	document.getElementById("textarea").addEventListener("keydown", function(e) {
		if (e.keyCode === 9) {
			e.preventDefault();
			document.execCommand('insertHTML', false, TAB);
		}
	});
</script>

<?php
	addNav("<a href='?p=entries'><button>Back</button></a>");
	addNav("<button onclick=\"updateTextEditorContent(); document.getElementById('form').submit()\">Submit</button>");

