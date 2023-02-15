<?php
ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "lib/db_webapp.php";

if( isset( $_GET['short_url'] ) ){
	
	$short_url = dbSafe( "http://mmstv.tv/" . $_GET['short_url'] );
	
	$sql0 = "select url from shortened_url where short_url = '".pg_escape_string($short_url)."'";
	$result0 = pg_query($dbconn, $sql0);
	$row0 = pg_fetch_array($result0);
	
	echo ( $row0['url'] != "" ? $row0['url'] : "NA" );

}else{
	
	echo "NA";
	die;
	
}

function dbSafe($raw_string){
	
	$tmpbuf = $raw_string;
	$len = strlen($tmpbuf);
	$resbuf = "";

	for($i=0; $i<$len; $i++)
	{
		$elem = substr($tmpbuf, 0, 1);
		$next = substr($tmpbuf, 1);
		$tmpbuf = $next;

		$tmp = "";
		if(strcmp($elem, "\\") == 0){
			$tmp = '\\\\';
		} else if(strcmp($elem, "'") == 0){
			$tmp = "''";
		} else {
			$tmp = $elem;
		}

		if(strlen($resbuf) == 0) {
			$resbuf = $tmp;
		} else {
			$resbuf .= $tmp;
		}
	}
	return $resbuf;
}
?>
