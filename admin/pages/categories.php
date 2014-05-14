<?php
	requirePassword();
?>

<form id="form" method="post" action="?s=updateCategories">
<?php
	$categories = $mysqli->query("SELECT * FROM categories");

	if ($categories) {
		while ($category = $categories->fetch_assoc()) {
			echo template("categoryListEntry", [
				"id"=>$category['id'],
				"name"=>$category['name']
			]);
		}
	}

	echo template("categoryListNew");

	addNav("<button onclick='document.getElementById(\"form\").submit()'>Submit</button>");
?>
</form>
