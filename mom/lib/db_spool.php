<?php
	$spdbname = 'spooldb';
	$spdbuser = 'msg';
	$spdbpass = 'msg!@#$%';	
	$spdbconstr = "host=localhost port=5432 dbname=$spdbname user=$spdbuser password=$spdbpass";
	$spdbconn = pg_connect($spdbconstr);
	if(!($spdbconn))
	{
		die("Database Connection Failed: ".pg_last_error($spdbconn));
	}
?>
