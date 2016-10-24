<?php
	$username = 'Sql455718';
	$password = 'eb463f3b';
	$hostname = '62.149.150.133'; 
	$dbname = 'Sql455718_4';
	echo 'Connessione...<br>';
	$dbhandle = mysql_connect($hostname, $username, $password)
		or die('Unable to connect to MySQL<br>');
	echo 'Selezione DB...<br>';
	$db_selected = mysql_select_db($dbname, $dbhandle);
	if (!$db_selected) {
		die ('Can\'t use DB: ' . mysql_error() . '<br>');
	}
?>
