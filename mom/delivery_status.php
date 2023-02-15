<?php
	$php_name = "delivery_status.php";
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
	if(isset($_REQUEST['status']))
	{
        $status = $_REQUEST['status'];
        if(strcmp($status,"0000") == 0){
            $message_status = 'R';
        }
        elseif(strcmp($status,"0008") == 0){
            $message_status = 'Y';
        }
        else{
            $message_status = 'U';
        }
	}
	if(isset($_REQUEST['totalsms']))
	{
		$total_sms = $_REQUEST['totalsms'];
	}
	
	if( ($trackid != "") && ($status != "") )
	{	
		
		if(preg_match("/^\w\d+/",$trackid,$matches)){
			// Update to Web Portal
			error_log("$trackid Update to Web Portal ( status_code=$status, totalsms=$total_sms )");
			$sqlcmd = " update outgoing_logs set " .
                    "status_code = '" .pg_escape_string($status). "', " .
					"message_status = '" .pg_escape_string($message_status). "', " .
					"delivered_dtm = 'now()' " .
					// "totalsms = '" . pg_escape_string($total_sms) . "' " .
					"where trackid = '" .pg_escape_string($trackid). "' ";
		}	
		else{
			// Update to API
			error_log("$trackid Update to Web API ( status_code=$status, totalsms=$total_sms )");
			$sqlcmd = " update appn_outgoing_logs set " .
                    "status_code = '" .pg_escape_string($status). "', " .
					"message_status = '" .pg_escape_string($message_status). "', " .
					"delivered_dtm = 'now()' " .
					// "totalsms = '" . pg_escape_string($total_sms) . "' " .
					"where trackitem = '" .pg_escape_string($trackid). "' ";
		}
        
		$row = pg_query($dbconn, $sqlcmd);
	}

	
	echo "OK";
?>
