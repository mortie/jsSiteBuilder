<?php
	chdir($root);
	message(exec("git pull"));

	header("Location: ?s=runNode&t=update");
