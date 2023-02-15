<?php
	$keydbname = 'sendquickdb';
	$keydbuser = 'msg';
	$keydbpass = 'msg!@#$%';
    $keyconstr = "host=localhost port=5432 dbname=$keydbname user=$keydbuser password=$keydbpass";
    $keydbconn = pg_connect($keyconstr);

	if(!($keydbconn))
	{
		$_SESSION['error_msg'] = "Database Connection Failed (" .$php_name. ") : " .dbSafe(pg_last_error($keydbconn));
		include("error.php");
		exit;
	}

?>
