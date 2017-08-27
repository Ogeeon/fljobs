<?php
	require_once 'dbconnect.php';
	
	$monthAgo = strtotime("-1 month");
	$query = "DELETE FROM `projects` where `added` < ".$monthAgo;
	SqlQuery($query);
?>