<?php
	requirePassword();
	addNav("<a href='?'><button>Back</button><a>");

	chdir($root);

	exec("git fetch&& git log --graph --left-right --cherry-pick HEAD..FETCH_HEAD", $out);

	if (empty($out)) {
		echo "No updates.";
	} else {
		foreach($out as $line) {
			if (preg_match("/(\s{4})|(Date).+/", $line)) {
				echo "$line<br>";
			}
		}
		addNav("<a href='?s=upgrade'><button>Update</button></a>");
	}
