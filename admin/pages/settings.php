<?php
	requirePassword();

	addNav("<button onclick='document.getElementById(\"form\").submit();'>Submit</button>");

	$names = [
		"adminPass"=>"Admin Password",
		"timeZone"=>"Time Zone",
		"allpostsShortLength"=>"Short List Length",
		"logToFile"=>false,
		"nodeCommand"=>"Node.js Command",
		
		"mysql"=>"MySQL",
		"mysql->host"=>"Hostname",
		"mysql->user"=>"Username",
		"mysql->password"=>"Password",
		"mysql->database"=>"Database Name",

		"display"=>"Display",
		"display->title"=>"Title",
		"display->theme"=>"Theme",
		"display->templates"=>"Template"
	];

	$printSetting = function($key, $val, $parent=false) use ($names){
		if ($parent === false) {
			$name = $key;
		} else {
			$name = "$parent->$key";
		}

		echo template("settingsListEntry", [
			"displayName"=>$names[$name],
			"name"=>$name,
			"value"=>$val,
		]);
	};
?>

<form id="form" class="table" method="post" action="?s=updateSettings">
<?php
	echo "<div class='subtitle'>General</div>\n";
	foreach ($settings as $key=>$val) {
		if (!is_object($val) && !empty($names[$key])) {
			$printSetting($key, $val);
		}
	}

	foreach ($settings as $key=>$val) {
		if (is_object($val) && array_key_exists($key, $names)) {
			echo "<div class='subtitle'>".$names[$key]."</div>\n";
			foreach ($val as $subkey=>$subval) {
				$printSetting($subkey, $subval, $key);
			}
		}
	}
?>
</form>
