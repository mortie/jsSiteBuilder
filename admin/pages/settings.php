<?php
	requirePassword();

	addNav("<a href='?'><button>Back</button></a>");
	addNav("<button onclick='document.getElementById(\"form\").submit();'>Submit</button>");

	$names = [
		"adminPass"=>["Admin Password"],
		"timeZone"=>["Time Zone"],
		"allpostsShortLength"=>["Length of Short List"],
		"logToFile"=>false,
		"nodeCommand"=>["Node.js Command"],
		
		"mysql"=>["MySQL"],
		"mysql->host"=>["Hostname"],
		"mysql->user"=>["Username"],
		"mysql->password"=>["Password"],
		"mysql->database"=>["Database Name"],

		"display"=>["Display"],
		"display->title"=>["Title"],
		"display->theme"=>["Theme"],
		"display->templates"=>["Template"]
	];

	function printSetting($key, $val, $parent=false) {
		global $names;
		echo "<div class='key'>\n";
		if ($parent === false) {
			echo $names[$key][0];
		} else {
			echo $names["$parent->$key"][0];
		}
		echo "</div><div class='val'>\n";
		if ($parent === false) {
			echo "<input type='text' name='$key' value='$val'>\n";
		} else {
			echo "<input type='text' name='$parent->$key' value='$val'>\n";
		}
		echo "</div>\n";
	}
?>

<form id="form" class="table" method="post" action="?s=updateSettings">
<?php
	echo "<div class='subtitle'>General</div>\n";
	foreach ($settings as $key=>$val) {
		if (!is_object($val) && !empty($names[$key])) {
			printSetting($key, $val);
		}
	}

	foreach ($settings as $key=>$val) {
		if (is_object($val) && array_key_exists($key, $names)) {
			echo "<div class='subtitle'>".$names[$key][0]."</div>\n";
			foreach ($val as $subkey=>$subval) {
				printSetting($subkey, $subval, $key);
			}
		}
	}
?>
</form>
