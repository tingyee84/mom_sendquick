<?php
require_once("/home/msg/www/htdocs/mom/lib/Logger.class.php");
require_once("/home/msg/www/htdocs/mom/lib/db_webapp.php");
require_once("/home/msg/www/htdocs/mom/printpdf.php");

/*
ini_set('display_errors', 0);
//ini_set('display_startup_errors', 0);
//error_reporting(E_ALL);
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
*/

//delete > 14 days csv
$dir = "/home/sqguest/sftp/";

foreach (glob($dir."*") as $folder) {
	
	if( is_dir( $folder ) ){
		
		$dir2 = "$folder/download/";
		
		foreach (glob($dir2."*") as $file) {
			
			if( is_dir( $file ) ){
				
			}else{
				
				//echo $file . "<br><br>";
				
				if ( filemtime($file) < time() - 1209600 ) { //14 days
					unlink($file);
				}
			
			}
			
		}
		
	}else{
		
	}
	
}

//die;

global $dbconn;

$date = date("Y-m-d");
$date_from = date('Y-m-d',(strtotime ( '-14 day' , strtotime ( $date) ) ));

$cdate = date("Y-m-d H:i:s");
$logfile = "/home/msg/logs/agent.log";
$logger = new Logger($logfile);
$logger->setTag("MOMBroadcastReportCSV - " . $cdate);
$logger->logMessage("Started ...");

//lock
$lockfile = '/tmp/elock_broadcast.txt';
$message = '1';

if (file_exists($lockfile)) {
		/*$fh = fopen($lockfile, 'a');
		fwrite($fh, $message."\n");
		fclose($fh);*/
} else {
		$fh = fopen($lockfile, 'w');
		fwrite($fh, $message."\n");
		fclose($fh);
}

$fp = fopen($lockfile, 'r+');

    /* Activate the LOCK_NB option on an LOCK_EX operation */

if(!flock($fp, LOCK_EX | LOCK_NB)) {
		echo 'Unable to obtain lock';
		//$logger->logMessage('Unable to obtain lock');
		exit(-1);
}
//end lock

try{
	
	//total_sms_sent
	$sql1 = "SELECT a.campaign_id, a.department, sum(cast(a.totalsms as integer)) as total_sms_sent, b.campaign_name, b.campaign_type, date(a.created_dtm) as created_dtm FROM outgoing_logs a, campaign_mgnt b where a.campaign_id = b.campaign_id and a.message_status in ('Y','R') and a.bot_message_status_id = 0 and a.is_deleted = FALSE and date(a.created_dtm) >= '$date_from' group by a.campaign_id, a.department, b.campaign_name, b.campaign_type, date(a.created_dtm) order by date(a.created_dtm) desc";
	$result1 = pg_query($dbconn, $sql1);
    $sms_sent = pg_fetch_all($result1);
	
	if( is_array( $sms_sent ) ){
		foreach( $sms_sent as $no => $datas ){
			$csv[ $datas['department'] ][ $datas['created_dtm'] ][ $datas['campaign_id'] ]['total_sms_sent'] = $datas['total_sms_sent'];
			$csv[ $datas['department'] ][ $datas['created_dtm'] ][ $datas['campaign_id'] ]['campaign_type'] = $datas['campaign_type'];
			$csv[ $datas['department'] ][ $datas['created_dtm'] ][ $datas['campaign_id'] ]['campaign_name'] = $datas['campaign_name'];
		}
	}
	
	//total_sms_unsent
	$sql1 = "SELECT a.campaign_id, a.department, sum(cast(a.totalsms as integer)) as total_sms_unsent, b.campaign_name, b.campaign_type, date(a.created_dtm) as created_dtm FROM outgoing_logs a, campaign_mgnt b where a.campaign_id = b.campaign_id and a.message_status in ('F','U') and a.bot_message_status_id = 0 and a.is_deleted = FALSE and date(a.created_dtm) >= '$date_from' group by a.campaign_id, a.department, b.campaign_name, b.campaign_type, date(a.created_dtm)  order by date(a.created_dtm) desc";
	$result1 = pg_query($dbconn, $sql1);
    $sms_unsent = pg_fetch_all($result1);
	
	if( is_array( $sms_unsent ) ){
		foreach( $sms_unsent as $no => $datas ){
			$csv[ $datas['department'] ][ $datas['created_dtm'] ][ $datas['campaign_id'] ]['total_sms_unsent'] = $datas['total_sms_unsent'];
			$csv[ $datas['department'] ][ $datas['created_dtm'] ][ $datas['campaign_id'] ]['campaign_type'] = $datas['campaign_type'];
			$csv[ $datas['department'] ][ $datas['created_dtm'] ][ $datas['campaign_id'] ]['campaign_name'] = $datas['campaign_name'];
		}
	}
	
	//total_mim_sent
	$sql1 = "SELECT a.campaign_id, a.department, count(*) as total_mim_sent, b.campaign_name, b.campaign_type, date(a.created_dtm) as created_dtm FROM outgoing_logs a, campaign_mgnt b where a.campaign_id = b.campaign_id and a.message_status in ('Y','R') and a.bot_message_status_id > 0 and a.is_deleted = FALSE and date(a.created_dtm) >= '$date_from' group by a.campaign_id, a.department, b.campaign_name, b.campaign_type, date(a.created_dtm)  order by date(a.created_dtm) desc";
	$result1 = pg_query($dbconn, $sql1);
    $mim_sent = pg_fetch_all($result1);
	
	if( is_array( $mim_sent ) ){
		foreach( $mim_sent as $no => $datas ){
			$csv[ $datas['department'] ][ $datas['created_dtm'] ][ $datas['campaign_id'] ]['total_mim_sent'] = $datas['total_mim_sent'];
			$csv[ $datas['department'] ][ $datas['created_dtm'] ][ $datas['campaign_id'] ]['campaign_type'] = $datas['campaign_type'];
			$csv[ $datas['department'] ][ $datas['created_dtm'] ][ $datas['campaign_id'] ]['campaign_name'] = $datas['campaign_name'];
		}
	}
	
	//total_mim_unsent
	$sql1 = "SELECT a.campaign_id, a.department, count(*) as total_mim_unsent, b.campaign_name, b.campaign_type, date(a.created_dtm) as created_dtm FROM outgoing_logs a, campaign_mgnt b where a.campaign_id = b.campaign_id and a.message_status in ('F','U') and a.bot_message_status_id > 0 and a.is_deleted = FALSE and date(a.created_dtm) >= '$date_from' group by a.campaign_id, a.department, b.campaign_name, b.campaign_type, date(a.created_dtm)  order by date(a.created_dtm) desc";
	$result1 = pg_query($dbconn, $sql1);
    $mim_unsent = pg_fetch_all($result1);
	
	if( is_array( $mim_unsent ) ){
		foreach( $mim_unsent as $no => $datas ){
			$csv[ $datas['department'] ][ $datas['created_dtm'] ][ $datas['campaign_id'] ]['total_mim_unsent'] = $datas['total_mim_unsent'];
			$csv[ $datas['department'] ][ $datas['created_dtm'] ][ $datas['campaign_id'] ]['campaign_type'] = $datas['campaign_type'];
			$csv[ $datas['department'] ][ $datas['created_dtm'] ][ $datas['campaign_id'] ]['campaign_name'] = $datas['campaign_name'];
		}
	}
	
	foreach( $csv as $department_id => $data1 ){
		
		//serviceid
		$sql01 = "select serviceid from appn_list where sftp_status = '1' and dept = '".$department_id."'";
		$result01 = pg_query($dbconn, $sql01);
		$row01 = pg_fetch_assoc( $result01 );
		$serviceid = $row01['serviceid'];
			
		//get department_name
		$department_name = '';
		$sql3 = "select department from department_list where department_id = '".$department_id."'";
		$result3 = pg_query($dbconn, $sql3);
		if( $row3 = pg_fetch_assoc( $result3 ) ){
			$department_name = $row3['department'];
		}
	
		foreach( $data1 as $created_dtm => $data2 ){
			
			$fields = "";
			
			if( $department_name && $serviceid ){
			
				$file_name = $serviceid . "_SMSBROADCAST_".date( "Ymd", strtotime( $created_dtm ) ).".csv";
				$full_path = "/home/sqguest/sftp/".$department_name."/download/" . $file_name;
				
				//if( file_exists( $full_path ) ){
					$fp = fopen( $full_path , 'w');
				//}else{
					//$logger->logMessage( "File missing: " . $full_path );
					//exit;
				//}
				
				//echo "AA";
				//die;
				
				if( $fp ){
					//header
					$fields = array("Campaign Name", "Campaign Type", "Total SMS Sent", "Total SMS Unsent", "Total MIM Sent", "Total MIM Unsent", "Total Received");
				
					fputcsv($fp, $fields);
				}else{
					$logger->logMessage( "Unable access: " . $full_path );
					exit;
				}
				
			}
			
			foreach( $data2 as $campaign_id => $data3 ){
				
				//get total received if have
				$sql2 = "select count(*) as total_received from campagin_survey_inbox where campagin_id = '".$campaign_id."'";
				$result2 = pg_query($dbconn, $sql2);
				$row2 = pg_fetch_assoc( $result2 );
				$total_received = $row2['total_received'] > 0 ? $row2['total_received'] : 0;
		
				$campaign_name = $data3['campaign_name'];
				$campaign_type = $data3['campaign_type'] == "1" ? "Broadcast" : "Interactive";
				$total_sms_sent = isset($data3['total_sms_sent']) ? $data3['total_sms_sent'] : "0";
				$total_sms_unsent = isset($data3['total_sms_unsent']) ? $data3['total_sms_unsent'] : "0";
				$total_mim_sent = isset($data3['total_mim_sent']) ? $data3['total_mim_sent'] : "0";
				$total_mim_unsent = isset($data3['total_mim_unsent']) ? $data3['total_mim_unsent'] : "0";
				
				if( $fp ){
					
					$fields = array( $campaign_name, $campaign_type, $total_sms_sent, $total_sms_unsent, $total_mim_sent, $total_mim_unsent, $total_received );
					
					if( $department_name && $serviceid ){
						$put_status = fputcsv($fp, $fields);
					}
				
				}else{
					$logger->logMessage( "Unable access: " . $full_path );
					exit;
				}
				//echo "fields: <br>";
				//print_r( $fields );
				//echo "<br><br><br>";
			}
			
			if( $department_name && $serviceid ){
				fclose($fp);
			}

		}
		
	}
	
	//print_r( $csv );
	//die;
	
}catch (Exception $e){
    $errMsg = $e->getMessage();
    $errCode = $e->getCode();
    $logger->logMessage("Exception [$errCode] $errMsg");
}
exit;
?>
