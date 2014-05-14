<?php
	requirePassword();
?>

<form id="form" method="post" action="?s=updateCategories">
<?php
	$types = $mysqli->query("SELECT * FROM categories");

	while ($type = $types->fetch_assoc()) {
		echo template("categoryListEntry", [
			"id"=>$type['id'],
			"name"=>$type['name']
		]);
	}

	echo template("categoryListNew");

	addNav("<button onclick='document.getElementById(\"form\").submit()'>Submit</button>");
?>
</form>
