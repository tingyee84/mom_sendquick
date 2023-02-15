<?php
	$logdbname = 'logdb';
	$logdbuser = 'msg';
	$logdbpass = 'msg!@#$%';	
	$logdbconstr = "host=localhost port=5432 dbname=$logdbname user=$logdbuser password=$logdbpass";
	$logdbconn = pg_connect($logdbconstr);
	if(!($logdbconn))
	{
		die("Database Connection Failed: ".pg_last_error($logdbconn));
	}
?>
