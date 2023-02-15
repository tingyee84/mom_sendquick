<?php
require "lib/commonFunc.php";

$mode = filter_input(INPUT_POST,'mode');
$userid = strtolower($_SESSION['userid']);

switch ($mode) {
  case "listScheduled":
        listScheduled($userid);
        break;
  case "deleteScheduled":
        deleteScheduled(filter_input(INPUT_POST,'idx'));
        break;
  case "emptyScheduled":
        emptyScheduled($userid);
        break;
  default:
        die("Unknown request");
}

function listScheduled($userid)
{
	global $dbconn;
	$result_array = array();

	$sqlcmd = "SELECT send_mode, bot_id,scheduled_id, mobile_numb, message, priority_status,
			to_char(scheduled_time, 'DD/MM/YYYY HH24:MI:SS') AS scheduled_time_formatted
			FROM scheduled_sms WHERE created_by='".pg_escape_string($userid)."' AND inc_id IS NULL ORDER BY scheduled_time";

	$result = pg_query($dbconn, $sqlcmd);

	while ($row = pg_fetch_array($result))
	{
		
		/*
		if( $row['bot_id'] != "" ){
			$send_type = "SMS & MIM";
		}else{
			$send_type = "SMS";
		}
		*/
		if( strtolower($row['send_mode']) == "sms" ){
			$send_type = "SMS";
		}elseif( strtolower($row['send_mode']) == "sms_mim" ){
			$send_type = "SMS & MIM";
		}elseif( strtolower($row['send_mode']) == "mim" ){
			$send_type = "MIM";
		}else{
			$send_type = "-";
		}
		
		$row['message'] = str_replace('\r\n',"<br>", $row['message'] );
	
		 array_push($result_array,Array(
			htmlspecialchars($row['mobile_numb']),
			$row['message'],
			//htmlentities($row['message'], ENT_SUBSTITUTE, "UTF-8"),
			$row['scheduled_time_formatted'],
			$row['priority_status'],
			$send_type,
			'<input type="checkbox" name="no" value="'.$row['scheduled_id'].'">'
		));
	}

	echo json_encode(Array("data"=>$result_array));
}

function deleteScheduled($idx)
{
	global $dbconn;
	
	//get data
	$owner = $message = $send_mode = '';
	$sql1 = "select message, send_mode, created_by from scheduled_sms where scheduled_id='".pg_escape_string($idx)."'";
	$result = pg_query($dbconn, $sql1);
	if ($row = pg_fetch_array($result)){
		$message = $row['message'];
		$send_mode = $row['send_mode'];
		$owner = $row['created_by'];
	}
	
	if( $send_mode && $owner ){
		
		$total_sms_needed = 0;
		
		if( $send_mode == "sms" ){
			$total_sms_needed = getSMSNeeded($message);	
		}elseif( $send_mode == "sms_mim" ){
			$total_sms_needed = getSMSNeeded($message) + 1;	
		}elseif( $send_mode == "mim" ){
			$total_sms_needed = 1;
		}
		
		//return quota
		if( $total_sms_needed > 0 ){
			
			$sql2 = "update quota_mnt set quota_left = quota_left + $total_sms_needed where unlimited_quota = '0' and userid = '$owner'";
			$res2 = doSQLcmd($dbconn, $sql2);
			
		}
		
	}

	$cmd = "delete from scheduled_sms where scheduled_id='".pg_escape_string($idx)."'";
	$res = doSQLcmd($dbconn, $cmd);

	if (!empty($res)) {
		echo "Database Error: ".$res;
	}else{
		
	}
	
	insertAuditTrail("Delete Scheduled Message");
}

function emptyScheduled($userid)
{
	global $dbconn;

	$cmd = "DELETE FROM scheduled_sms WHERE created_by='".pg_escape_string($userid)."' AND inc_id IS NULL";
	$res = doSQLcmd($dbconn, $cmd);

	if (!empty($res)) {
		echo "Database Error: ".$res;
	}
	
	insertAuditTrail("Empty Scheduled Message");
}

function getSMSNeeded($sms_text){
	// Default charset is ASCII	
	$character_set = 0;
	if(mb_detect_encoding($sms_text, 'ASCII', true)){
				$character_set = 1;
	}			
	if ($character_set == 1)
	{
		//ASCII		
		$total_length = 670;
		$max_length = 153;
		if ( strlen($sms_text) > 160 )
		{
			$max_length = 153;
		} 
		else 
		{
			$max_length = 160;
		}
		
		//total_length = max_length * 4;		
		$number_of_sms_needed = ceil(strlen($sms_text) / $max_length);
	} 
	else 
	{
		// UTF-8		
		$total_length = 670;		
		$max_length = 70;
		if ( strlen($sms_text)  > 70 )
		{
			$max_length = 67;
		} 
		else 
		{
			$max_length = 70;
		}				
		$number_of_sms_needed = ceil(mb_strlen($sms_text, "UTF-8") / $max_length);
	}	
	error_log("NoOfSMS: $number_of_sms_needed");
	return $number_of_sms_needed;
}
?>
