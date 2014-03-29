<?php
	chdir($root);
	message(exec("git fetch; git checkout --theirs ."));

	header("Location: ?s=runNode&t=update");
