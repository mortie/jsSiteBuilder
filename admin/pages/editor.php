<?php
	requirePassword();

	global $entry;
	if (!empty($_GET['id'])) {
		$id = $mysqli->real_escape_string($_GET['id']);
		$entry = $mysqli->query("SELECT * FROM entries WHERE id='$id'")->fetch_assoc();
	} else {
		$entry = [
			"title"=>"",
			"slug"=>"",
			"type"=>0,
			"listCategory"=>1,
			"markdown"=>"",
			"id"=>"",
			"category"=>0,
			"sort"=>0,
			"display"=>true
		];
	}

	function getProp($property, $escape=true) {
		global $entry;
		if ($escape) {
			return htmlentities($entry[$property]);
		} else {
			return $entry[$property];
		}
	}
?>

<script>
	var usedEditor;
	function useEditor(editor) {
		var text = document.getElementById("textEditor");
		var list = document.getElementById("listEditor");
		if (editor == "text") {
			text.style.display = "";
			list.style.display = "none";
		} else if (editor == "list") {
			text.style.display = "none";
			list.style.display = "";
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
			getDoc("formType").value = 0;
		} else {
			getDoc("formType").value = getRadio("type").value;
		}
		getDoc("formCategory").value = getDoc("category").value; 
		getDoc("formListCategory").value = getDoc("listCategory").value;
		getDoc("formSort").value = getDoc("sort").value;
		getDoc("formDisplay").value = getDoc("display").checked?"1":"0";
	}
</script>

<form id="form" method="post" action="?s=updateEntry">
	<textarea id="formMarkdown" style="display: none" name="markdown"></textarea>
	<input id="formSlug" type="hidden" name="slug">
	<input id="formTitle" type="hidden" name="title">
	<input id="formId" type="hidden" name="id" value="<?=$entry['id'] ?>">
	<input id="formType" type="hidden" name="type">
	<input id="formListCategory" type="hidden" name="listCategory">
	<input id="formCategory" type="hidden" name="category">
	<input id="formSort" type="hidden" name="sort">
	<input id="formDisplay" type="hidden" name="display">
</form>

<div class="section">
	Title:
	<input id="title" type="text" class="wide" value="<?=getProp('title') ?>">
</div>

<div class="section">
	Slug:
	<input id="slug" type="text" class="wide" value="<?=getProp('slug') ?>">
</div>

<div class="section">
	<div>
		Sort:
		<input id="sort" type="number" value="<?=getProp('sort') ?>">
	</div>
	<select id="category">
<option value='0'>Page</option>
<?php
	$categories = $mysqli->query("SELECT * FROM categories");
	if ($categories) {
		while ($category = $categories->fetch_assoc()) {
			if ($entry['category'] == $category['id']) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			echo "<option value='".$category['id']."' $selected>".$category['name']."</option>\n";
		}
	}
?>
	</select>
</div>

<div class="section">
	<label><input type="radio" name="textOrList" onclick="useEditor('text')" <?php if ($entry['type'] == 0) echo "checked" ?>>Entry</label>
	<label><input type="radio" name="textOrList" onclick="useEditor('list')" <?php if ($entry['type'] != 0) echo "checked" ?>>List</label><br>

	<div id="textEditor">
		<textarea id="textarea"><?=getProp('markdown', false) ?></textarea>
	</div>

	<div id="listEditor">
		<div class="section">
		List:<br>
			<label><input type="radio" name="type" value="1" <?php if ($entry['type'] == 1) echo "checked" ?>>Link</label><br>
			<label><input type="radio" name="type" value="2" <?php if ($entry['type'] == 2) echo "checked" ?>>Short</label><br>
			<label><input type="radio" name="type" value="3" <?php if ($entry['type'] == 3) echo "checked" ?>>Full</label>
		</div>

		<div class="selection">
			List entries of type:
			<select id="listCategory">
<option value='0'>Page</option>
<?php
	$categories = $mysqli->query("SELECT * FROM categories");
	
	if ($categories) {
		while ($category = $categories->fetch_assoc()) {
			if ($entry['listCategory'] == $category['id']) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			echo "<option value='".$category['id']."' $selected>".$category['name']."</option>\n";
		}
	}
?>
			</select>
		</div>
	</div>
</div>

<div class="section">
	<label><input id="display" type="checkbox" <?php if ($entry['display']) echo "checked" ?>>Display</label>
</div>

<script>
	if (<?=$entry['type'] ?> == 0) {
		useEditor("text");
	} else {
		useEditor("list");
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
	addNav("<button onclick=\"prepareForm(); document.getElementById('form').submit()\">Submit</button>");
	addNav(template("buttonLink", [
		"url"=>"?s=deleteEntry&id=$entry[id]",
		"title"=>"Delete"
	]));
