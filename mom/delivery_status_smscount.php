<?php
	$php_name = "delivery_status_smscount.php";
	include "lib/db_webapp.php";

	$remote_ip = $_SERVER['REMOTE_ADDR'];
    
    if(!preg_match("/(\b42.61.125.3\b|\b118.189.175.117\b)/",$remote_ip,$matches)){
        echo "Invalid requet";
		exit;
    }
	
	if(isset($_REQUEST['trackid']))
	{
		$trackid = $_REQUEST['trackid'];
	}
	
	if(isset($_REQUEST['totalsms']))
	{
		$total_sms = $_REQUEST['totalsms'];
	}
	
	if( ($trackid != "") && ($total_sms != ""))
	{	
		
		if(preg_match("/^\w\d+/",$trackid,$matches)){
			// Update to Web Portal
			error_log("$trackid Update to Web Portal ( totalsms=$total_sms )");
			$sqlcmd = " update outgoing_logs set " .                    
					"totalsms = '" . pg_escape_string($total_sms) . "' " .
					"where trackid = '" .pg_escape_string($trackid). "' ";
		}	
		else{
			// Update to API
			error_log("$trackid Update to Web API ( totalsms=$total_sms )");
			$sqlcmd = " update appn_outgoing_logs set " .                    
					"totalsms = '" . pg_escape_string($total_sms) . "' " .
					"where trackitem = '" .pg_escape_string($trackid). "' ";
		}
        
		$row = pg_query($dbconn, $sqlcmd);
	}
	
	echo "OK";
?>
