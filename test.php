<?php
	error_reporting(E_ALL);
	echo $_REQUEST["inc"]."<br/>";
	if ($_REQUEST["inc"] == 1)
		require_once 'HTTP/Request2.php';
	echo "past require";
?>