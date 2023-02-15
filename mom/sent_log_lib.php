<?php
require "lib/commonFunc.php";
include("lib/db_sq.php");

$department = $_SESSION['department'];
$userid = strtolower($_SESSION['userid']);
$mode = filter_input(INPUT_POST,'mode');
$date_from = filter_input(INPUT_POST,'from');
$date_to = filter_input(INPUT_POST,'to');
$idx = filter_input(INPUT_POST,'idx');

switch ($mode) {
	case "listLog":
		listLog($userid, $date_from, $date_to);
		break;
	case "listapiLog":
		listapiLog($userid, $date_from, $date_to);
		break;
	case "listGlobalLog":
		listGlobalLog($userid, $department, $date_from, $date_to);
		break;
	case "delete":
		delete($idx);
		break;
	case "delete_api":
		delete_api($idx);
		break;
	case "emptyLog":
		emptyLog($userid);
		break;
	case "emptyGlobalLog":
		emptyGlobalLog($userid, $department);
		break;
	default:
		die("Unknown request");
}

function listLog($userid, $from, $to)
{
	global $dbconn;
	$result_array = array();
	
	if(empty($from)) { $from = date(); }
	if(empty($to)) { $to = date(); }

	$sqlcmd = "SELECT bot_message_status_id, outgoing_id, mobile_numb, message, message_status, totalsms, 
			to_char(completed_dtm, 'DD/MM/YYYY HH24:MI:SS') as completed_dtm_formatted,campaign_name,campaign_type
			FROM outgoing_logs
			LEFT OUTER JOIN campaign_mgnt ON outgoing_logs.campaign_id = campaign_mgnt.campaign_id
			where sent_by='".pg_escape_string($userid). "' and 
			(message_status in ('R', 'Y') or (message_status = 'U' AND bot_types_id = null)) and completed_dtm >= to_date('".$from."','DD/MM/YYYY') and 
			completed_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day' AND is_deleted = FALSE order by completed_dtm desc";
	$result = pg_query($dbconn, $sqlcmd);
	
	while ($row = pg_fetch_array($result)) 
	{ 
		$row['message'] = str_replace('\r\n',"<br/>", $row['message'] );
		
		 array_push($result_array,Array(
			$row['completed_dtm_formatted'],
			htmlspecialchars($row['mobile_numb']) . ( $row['bot_message_status_id'] ? " (MIM)" : "" ),
			htmlspecialchars($row['campaign_name'])."(".(($row["campaign_type"]==0?"NA":($row["campaign_type"]==1?"Broadcast":"Interactive"))).")",
			//htmlentities($row['message'], ENT_SUBSTITUTE, "UTF-8"),
			$row['message'],
			$row['message_status'].($row["message_status"] == "U" ? $row["status_code"] : ""),
			$row['totalsms'],
			'<input type="checkbox" class="user_checkbox" name="no" value="'.$row['outgoing_id'].'">'
		));
	} 
	
	echo json_encode(Array("data"=>$result_array));
}

function listapiLog($userid, $from, $to)
{
	global $dbconn;
	$result_array = array();
	
	if(empty($from)) { $from = date(); }
	if(empty($to)) { $to = date(); }
	$depttxt = "";
	if(!isUserAdmin($userid)) {
		$depttxt = "AND b.dept = '".dbSafe($_SESSION["department"])."'";
	}
	$sqlcmd = "SELECT id, mobile_numb, message, message_status, totalsms, serviceid,c.department,send_mode,
			to_char(completed_dtm, 'DD/MM/YYYY HH24:MI:SS') as completed_dtm_formatted,status_code
			FROM appn_outgoing_logs a
			LEFT JOIN appn_list b ON a.clientid = b.clientid
			LEFT JOIN department_list c on b.dept = c.department_id 
			WHERE 
			(message_status in ('R', 'Y') or (message_status = 'U' AND send_mode = 'sms')) and completed_dtm >= to_date('".$from."','DD/MM/YYYY') and 
			completed_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day' $depttxt AND is_deleted = FALSE ORDER by completed_dtm desc";
	$result = pg_query($dbconn, $sqlcmd);
	
	while ($row = pg_fetch_array($result)) 
	{ 
		 array_push($result_array,Array(
			$row['completed_dtm_formatted'],
			htmlspecialchars($row['mobile_numb']).($row['send_mode'] == "mim" ? "(MIM)" : ""),
			$row['serviceid'],
			$row['department'],
			//htmlentities($row['message'], ENT_SUBSTITUTE, "UTF-8"),
			$row['message'],
			$row['message_status'].($row["message_status"] == "U" ? $row["status_code"] : ""),
			$row['totalsms'],
			'<input type="checkbox" name="no" value="'.$row['id'].'">'
		));
	} 
	
	echo json_encode(Array("data"=>$result_array));
}

function listGlobalLog($userid, $department, $from, $to)
{
	global $dbconn;
	$result_array = array();
	
	if(empty($from)) { $from = date(); }
	if(empty($to)) { $to = date(); }
	
	$msg_column = "message";
	if(getDisableOutbox()) {
		$msg_column = "length(message) || ' characters.' as message";
	}
	
	$list_cond = "(message_status in ('R', 'Y') or (message_status = 'U' AND bot_types_id is null)) and 
				completed_dtm >= to_date('".$from."','DD/MM/YYYY') and 
				completed_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day' AND is_deleted = FALSE 
				order by completed_dtm desc";	
	
	if(isUserAdmin($userid)) {	
		$sqlcmd = "SELECT bot_message_status_id, outgoing_id,mobile_numb,message_status,totalsms,sent_by,".$msg_column.", 
					to_char(completed_dtm, 'DD/MM/YYYY HH24:MI') as completed_dtm_formatted,campaign_name,campaign_type,
					department_list.department as department from outgoing_logs
					LEFT OUTER JOIN department_list ON (outgoing_logs.department = department_list.department_id)
					LEFT OUTER JOIN campaign_mgnt ON outgoing_logs.campaign_id = campaign_mgnt.campaign_id
					WHERE ".$list_cond;
	} else {	
		$sqlcmd = "SELECT bot_message_status_id, outgoing_id,mobile_numb,message_status,totalsms,sent_by,".$msg_column.",department,
					to_char(completed_dtm, 'DD/MM/YYYY HH24:MI') as completed_dtm_formatted,campaign_name,campaign_type
					from outgoing_logs
					LEFT OUTER JOIN campaign_mgnt ON outgoing_logs.campaign_id = campaign_mgnt.campaign_id
					WHERE department='".pg_escape_string($department)."' 
					and ".$list_cond ;
	}
	
	$result = pg_query($dbconn, $sqlcmd);
	
	while ($row = pg_fetch_array($result)) 
	{ 
		 array_push($result_array,Array(
			$row['completed_dtm_formatted'],
			htmlspecialchars($row['mobile_numb']). ( $row['bot_message_status_id'] ? " (MIM)" : "" ),
			htmlspecialchars($row['sent_by']),
			htmlspecialchars($row['department']),
			htmlspecialchars($row['campaign_name'])."(".(($row["campaign_type"]==0?"NA":($row["campaign_type"]==1?"Broadcast":"Interactive"))).")",
			//htmlentities($row['message'], ENT_SUBSTITUTE, "UTF-8"),
			$row['message'],
			$row['message_status'].($row["message_status"] == "U" ? $row["status_code"] : ""),
			$row['totalsms'],
			'<input type="checkbox" name="no" value="'.$row['outgoing_id'].'">'
		));
	} 
	
	echo json_encode(Array("data"=>$result_array));
}

function delete($idx)
{
	// TODO need enhance this code so cannot anyone delete this
	global $dbconn, $userid;
	
	$getsql = "select trackid,mobile_numb from outgoing_logs where outgoing_id='".pg_escape_string($idx)."'";
	$get = getSQLresult($dbconn, $getsql);
	
	if(!empty($get) && !is_string($get))
	{
		deleteSpoolDBLogs($get[0]['trackid'],$get[0]['mobile_numb']);
		
		// $sqlcmd = "delete from outgoing_logs where trackid='".pg_escape_string($get[0]['trackid'])."';
		//			delete from webapp_sms where msgid='".pg_escape_string($get[0]['trackid'])."'";
		$sqlcmd = "UPDATE outgoing_logs SET is_deleted = TRUE, modified_by = '".dbSafe($userid)."', modified_dtm = now() WHERE trackid='".pg_escape_string($get[0]['trackid'])."'";
		$res = doSQLcmd($dbconn, $sqlcmd);
		
		if (!empty($res)) { 
			echo "Database Error: ".$res;
		} else {
			insertAuditTrail($idx. " at outgoing_logs has been deleted.");
		}
		
	}
	// insertAuditTrail($get[0]['trackid']." from sent logs has been removed from log.");
}
function delete_api($idx)
{
	global $dbconn, $userid;
	
	$getsql = "SELECT trackid,mobile_numb from appn_outgoing_logs where id='".pg_escape_string($idx)."'";
	$get = getSQLresult($dbconn, $getsql);
	
	if(!empty($get) && !is_string($get))
	{
		deleteSpoolDBLogs($get[0]['trackid'],$get[0]['mobile_numb']);
		
		//$sqlcmd = "DELETE from appn_outgoing_logs where id='".pg_escape_string($get[0]['id'])."'";
		$sqlcmd = "UPDATE appn_outgoing_logs SET is_deleted = TRUE, modified_dtm = now() WHERE id='".pg_escape_string($get[0]['id'])."'";

		$res = doSQLcmd($dbconn, $sqlcmd);
		
		if (!empty($res)) { 
			echo "Database Error: ".$res;
		} else {
			insertAuditTrail($idx. " at API outgoing logs have been deleted.");
		}
	}
	// insertAuditTrail($get[0]['trackid']." from sent logs (api) has been removed from log.");
}
function emptyLog($userid)
{
	global $dbconn;
	
	$getsql = "select trackid,mobile_numb from outgoing_logs where message_status in ('R','Y') and sent_by='".pg_escape_string($userid)."'";
	$get = getSQLresult($dbconn, $getsql);
	
	if(!is_string($get)) {
		if(!empty($get)) {
			for($a=0; $a<count($get); $a++) {
				deleteSpoolDBLogs($get[$a]['trackid'],$get[$a]['mobile_numb']);
			}
		}
	}
	
	$sqlcmd = "UPDATE outgoing_logs SET is_deleted = TRUE, modified_dtm = now() where message_status in ('R','Y') and sent_by='".pg_escape_string($userid)."'";
	$res = doSQLcmd($dbconn, $sqlcmd);
	
	if (!empty($res)) { 
		echo "Database Error: ".$res;
	} else {
		insertAuditTrail("All confirmed messages sent by user: $userid at outgoing_logs has been deleted.");
	}
}

function emptyGlobalLog($userid, $department)
{
	// TODO Continue fixed this code
	global $dbconn;
	
	if(isUserAdmin($userid)) {
		$getsql = "select trackid,mobile_numb from outgoing_logs where message_status in ('R','Y')";
		$sqlcmd = "UPDATE outgoing_logs SET is_deleted = TRUE, modified_dtm = now() where message_status in ('R','Y')";
		// $sqlcmd = "delete from outgoing_logs where message_status in ('R','Y')";
	} else {
		$getsql = "select trackid,mobile_numb from outgoing_logs where department='".pg_escape_string($department)."' and message_status in ('R','Y')";
		$sqlcmd = "UPDATE outgoing_logs SET is_deleted = TRUE, modified_dtm = now() where department='".pg_escape_string($department)."' and message_status in ('R','Y')";
		//$sqlcmd = "delete from outgoing_logs where department='".pg_escape_string($department)."' and message_status in ('R','Y')";
	}
	
	$get = getSQLresult($dbconn, $getsql);
	
	if(!is_string($get)) {
		if(!empty($get)) {
			for($a=0; $a<count($get); $a++) {
				deleteSpoolDBLogs($get[$a]['trackid'], $get[$a]['mobile_numb']);
			}
		}
	}
	
	$res = doSQLcmd($dbconn, $sqlcmd);
	if (!empty($res)) { 
		echo "Database Error: ".$res;
	} else {
		insertAuditTrail("All confirmed messages sent by department: $department at outgoing_logs has been deleted.");
	}
}

function deleteSpoolDBLogs($trackid, $mobile_numb)
{
	readOptNetXMLFile();
	
	if($_SESSION['def_webport'] == 1) {
		$url = "http://127.0.0.1/cmd/system/api/reqdelete.cgi";
	} else {
		$url = "http://127.0.0.1:" .$_SESSION['new_webport']. "/cmd/system/api/reqdelete.cgi";
	}

	$param = "trackid=".urlencode($trackid)."&tar_num=".urlencode($mobile_numb);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$response = curl_exec($ch);
	//error_log($response);
	curl_close($ch);
}

function getDisableOutbox()
{
	global $sqdbconn;

	$cmd = "select config_value from system_config where config_key='disable_outbox'";
	$res = pg_query($sqdbconn, $cmd);
	$row = pg_fetch_row($res);
	
	return $row[0];
}
?>
