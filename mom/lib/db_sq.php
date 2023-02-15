<?php
	$sqdbname = 'sendquickdb';
	$sqdbuser = 'msg';
	$sqdbpass = 'msg!@#$%';	
	$sqdbconstr = "host=localhost port=5432 dbname=$sqdbname user=$sqdbuser password=$sqdbpass";
	$sqdbconn = pg_connect($sqdbconstr);
	if(!($sqdbconn))
	{
		die("Database Connection Failed: ".pg_last_error($sqdbconn));
	}
?>
