<?php
	requirePassword();

	if (!empty($_GET['id'])) {
		$id = $mysqli->real_escape_string($_GET['id']);
		$entry = $mysqli->query("SELECT * FROM entries WHERE id='$id'")->fetch_assoc();
	} else {
		$entry = [
			"title"=>"",
			"slug"=>"",
			"allposts"=>0,
			"allpostsType"=>1,
			"markdown"=>"",
			"id"=>"",
			"type"=>0,
		];
	}
?>

<script>
	var usedEditor;
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
		usedEditor = editor;
	}

	function getDoc(name) {
		return document.getElementById(name);
	}

	function getRadio(name) {
		var radios = document.getElementsByName(name);
		for (var i=0; i<radios.length; ++i) {
			if (radios[i].checked) {
				return radios[i];
			}
		}
	}

	function prepareForm() {
		getDoc("formMarkdown").value = getDoc("textarea").value;
		getDoc("formSlug").value = getDoc("slug").value;
		getDoc("formTitle").value = getDoc("title").value;
		if (usedEditor == "text") {
			getDoc("formAllposts").value = 0;
		} else {
			getDoc("formAllposts").value = getRadio("allposts").value;
		}
		getDoc("formType").value = getRadio("type").value; 
		getDoc("formAllpostsType").value = getDoc("allpostsType").value;
	}
</script>

<form id="form" method="post" action="?s=updateEntry">
	<textarea id="formMarkdown" style="display: none" name="markdown"></textarea>
	<input id="formSlug" type="hidden" name="slug">
	<input id="formTitle" type="hidden" name="title">
	<input id="formId" type="hidden" name="id" value="<?=$entry['id'] ?>">
	<input id="formAllposts" type="hidden" name="allposts">
	<input id="formAllpostsType" type="hidden" name="allpostsType">
	<input id="formType" type="hidden" name="type">
</form>

<div class="section">
	Title:
	<input id="title" type="text" class="wide" value="<?=$entry['title'] ?>">
</div>

<div class="section">
	Slug:
	<input id="slug" type="text" class="wide" value="<?=$entry['slug'] ?>">
</div>

<div class="section">
<label><input type="radio" name="type" value="0" <?php if ($entry['type'] == 0) echo "checked" ?>>Page</label><br>
	<label><input type="radio" name="type" value="1" <?php if ($entry['type'] == 1) echo "checked" ?>>Post</label>
</div>

<div class="section">
	<label><input type="radio" name="allpostsType" onclick="useEditor('text')" <?php if ($entry['allposts'] == 0) echo "checked" ?>>Entry</label>
	<label><input type="radio" name="allpostsType" onclick="useEditor('allposts')" <?php if ($entry['allposts'] != 0) echo "checked" ?>>List</label><br>

	<div id="textEditor">
		<textarea id="textarea"><?=$entry['markdown'] ?></textarea>
	</div>

	<div id="allpostsEditor">
		<br>
		List:<br>
		<label><input type="radio" name="allposts" value="1" <?php if ($entry['allposts'] == 1) echo "checked" ?>>Link</label><br>
		<label><input type="radio" name="allposts" value="2" <?php if ($entry['allposts'] == 2) echo "checked" ?>>Short</label><br>
		<label><input type="radio" name="allposts" value="3" <?php if ($entry['allposts'] == 3) echo "checked" ?>>Full</label><br>

		<label>List entries of type:<input type="number" id="allpostsType" value="<?=$entry['allpostsType'] ?>"></label>
	</div>
</div>


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
	addNav("<button onclick=\"prepareForm(); document.getElementById('form').submit()\">Submit</button>");
	addNav("<a href='?s=deleteEntry&id=".$entry['id']."'><button>Delete</button></a>");
