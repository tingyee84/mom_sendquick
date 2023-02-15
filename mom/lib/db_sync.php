<?php
	$syncdbname = 'syncdb';
	$syncdbuser = 'msg';
	$syncdbpass = 'msg!@#$%';	
	$syncdbconstr = "host=localhost port=5432 dbname=$syncdbname user=$syncdbuser password=$syncdbpass";
	$syncdbconn = pg_connect($syncdbconstr);
	if(!($syncdbconn))
	{
		die("Database Connection Failed: ".pg_last_error($syncdbconn));
	}
?>
