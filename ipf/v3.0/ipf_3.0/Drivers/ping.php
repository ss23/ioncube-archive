<?php 

	

	$cmd=$_GET['cmd'];
	if ($cmd=="ping")
		echo("pong");
	else if ($cmd=="run")
		echo("execution begun");
	else
		echo("Unknown command : $cmd");

?>