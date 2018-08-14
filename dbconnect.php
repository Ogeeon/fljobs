<?php

$dbhost = 'localhost';
$dbname = 'fljobs';
$dbuser = 'admin3p6ksfx';
$dbpass = '8VIZ1trAVlBa';

function SqlQuery($query)
{
    global $connection;
	$res = array();

	if ($result = mysqli_query($connection, $query))
	{
		if ($result === TRUE) return FALSE; // для не-select'ов возвращаем FALSE, потому что нет результата
		while ($row = mysqli_fetch_assoc($result))
		    array_push($res, $row);
		mysqli_free_result($result);
	}
	else return FALSE;

	return count($res) > 0 ? $res : FALSE;
}
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);


?>