<?php
	$dbname = 'momdb';
	$dbuser = 'msg';
	$dbpass = 'msg!@#$%';
	$constr = "host=localhost port=5432 dbname=$dbname user=$dbuser password=$dbpass";
	$dbconn = pg_connect($constr);
	
	if(!($dbconn))
	{
		$_SESSION['error_msg'] = "Database Connection Failed (" .$php_name. ") : " .pg_last_error($dbconn);
		include("error.php");
		exit;
	}
?>
