<?php
	$php_name = "sms_status.php";
	include "lib/db_webapp.php";

	$remote_ip = $_SERVER['REMOTE_ADDR'];

	if( strcmp($remote_ip, "127.0.0.1") != 0 ){
		echo "Invalid requet";
		exit;
	}

	if(isset($_REQUEST['mno']))
	{
		$mobile_numb = $_REQUEST['mno'];
	}
	if(isset($_REQUEST['trackid']))
	{
		$trackid = $_REQUEST['trackid'];
	}
	if(isset($_REQUEST['status']))
	{
		$status = $_REQUEST['status'];
	}
	if(isset($_REQUEST['totalsms']))
	{
		$total_sms = $_REQUEST['totalsms'];
	}

	//$trackid = msgid
	if( ($trackid != "") && ($status != "") )
	{
		$sqlcmd = " update outgoing_logs set " .
					"message_status = '" .pg_escape_string($status). "', " .
					"completed_dtm = 'now()', " .
					"totalsms = '" . pg_escape_string($total_sms) . "' " .
					"where trackid = '" .pg_escape_string($trackid). "' ";
		$row = pg_query($dbconn, $sqlcmd);
	}
	
	echo "OK";
?>
