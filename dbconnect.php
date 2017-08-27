<?php

// доступ к базе на OpenShift

$dbhost = getenv('mysql_SERVICE_HOST');
$dbname = 'fljobs';
$dbuser = 'admin3p6KSFX';
$dbpass = '8VIZ1trAVlBa';

// доступ к базе локально 
/*
$dbhost = 'localhost';
$dbname = 'fl_projects';
$dbuser = 'root';
$dbpass = 'root';
*/

function SqlQuery($query)
{
	$res = array();

	if ($result = mysql_query($query))
	{
		if ($result === TRUE) return FALSE; // для не-select'ов возвращаем FALSE, потому что нет результата
		while ($row = mysql_fetch_assoc($result)) array_push($res, $row);
		mysql_free_result($result);
	}
	else return FALSE;

	return count($res) > 0 ? $res : FALSE;
}

mysql_connect($dbhost, $dbuser, $dbpass) or die('Error1');
mysql_select_db($dbname) or die('Error2');

mysql_query("SET NAMES utf8");
mysql_query("SET CHARACTER SET utf8");

?>