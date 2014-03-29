<?php
	requirePassword();

	$adminDir = getcwd();
	chdir($root);
	exec("git fetch && git log HEAD..FETCH_HEAD", $out);
	chdir($adminDir);

	if (empty($out)) {
		echo "No updates available.";
	} else {
		$commitStr = "";
		foreach($out as $line) {
			$commitStr .= "$line\n";
		}
		$commits = preg_split("/commit .+/", $commitStr);

		foreach ($commits as $commit) {
			preg_match("/Date.+/", $commit, $dateStr);
			preg_match("/\s{4}.+/", $commit, $msgStr);
			echo template("commit", [
				"date"=>$dateStr[0],
				"message"=>$msgStr[0]
			]);
		}
		addNav("<a href='?s=upgrade'><button>Update</button></a>");
	}
