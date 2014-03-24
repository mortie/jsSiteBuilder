<?php
	requirePassword();

	addNav("<a href='?'><button>Back</button></a>");
	addNav("<button onclick='document.getElementById(\"form\").submit()'>Submit</button>");
?>

<form id="form" method="post" action="?s=updatePostTypes">
<?php
	$types = $mysqli->query("SELECT * FROM types");

	while ($type = $types->fetch_assoc()) {
		echo template("typeListEntry", [
			"id"=>$type['id'],
			"name"=>$type['name']
		]);
	}

	echo template("typeListNew");
?>
</form>
