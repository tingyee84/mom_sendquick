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

	$sqlcmd = "select bot_message_status_id,outgoing_id, mobile_numb, message, message_status,
			to_char(completed_dtm, 'DD/MM/YYYY HH24:MI:SS') as completed_dtm_formatted,status_code
			from outgoing_logs where sent_by='".pg_escape_string($userid). "' and 
			message_status in ('F','E','U') and completed_dtm >= to_date('".$from."','DD/MM/YYYY') and 
			completed_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day' order by completed_dtm desc";
	$result = pg_query($dbconn, $sqlcmd);
	
	while ($row = pg_fetch_array($result)) 
	{ 
		$row['message'] = str_replace('\r\n',"<br/>", $row['message'] );
		
		 array_push($result_array,Array(
			$row['completed_dtm_formatted'],
			htmlspecialchars($row['mobile_numb']) . ( $row['bot_message_status_id'] ? " (MIM)" : "" ),
			//htmlentities($row['message'], ENT_SUBSTITUTE, "UTF-8"),
			$row['message'],
			//htmlspecialchars($row['mobile_numb']),
			//htmlentities($row['message'], ENT_SUBSTITUTE, "UTF-8"),
			$row['message_status'].($row["message_status"] == "U" ? $row["status_code"] : ""),
			$row['outgoing_id'],
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
	$sqlcmd = "SELECT mim_channel, id, mobile_numb, message, message_status, totalsms, serviceid,c.department,
			to_char(completed_dtm, 'DD/MM/YYYY HH24:MI:SS') as completed_dtm_formatted, status_code
			FROM appn_outgoing_logs a
			LEFT JOIN appn_list b ON a.clientid = b.clientid
			LEFT JOIN department_list c on b.dept = c.department_id 
			WHERE 
			message_status in ('F','E','U') and completed_dtm >= to_date('".$from."','DD/MM/YYYY') and 
			completed_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day' $depttxt ORDER by completed_dtm desc";
	$result = pg_query($dbconn, $sqlcmd);
	
	while ($row = pg_fetch_array($result)) 
	{ 
		 array_push($result_array,Array(
			$row['completed_dtm_formatted'],
			htmlspecialchars($row['mobile_numb']). ( $row['mim_channel'] ? " (MIM)" : "" ),
			$row['serviceid'],
			$row['department'],
			$row['message'],
			$row['message_status'].($row["message_status"] == "U" ? $row["status_code"] : ""),
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
	
	$list_cond = "message_status in ('F','E','U') and 
				completed_dtm >= to_date('".$from."','DD/MM/YYYY') and 
				completed_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day' 
				order by completed_dtm desc";	
	
	if(isUserAdmin($userid)) {	
		$sqlcmd = "select bot_message_status_id, outgoing_id,mobile_numb,message_status,".$msg_column.", 
					to_char(completed_dtm, 'DD/MM/YYYY HH24:MI') as completed_dtm_formatted, 
					department_list.department as department,status_code from outgoing_logs left outer join 
					department_list on (outgoing_logs.department = department_list.department_id) 
					where ".$list_cond;
	} else {	
		$sqlcmd = "select bot_message_status_id, outgoing_id,mobile_numb,message_status,".$msg_column.",department,
					to_char(completed_dtm, 'DD/MM/YYYY HH24:MI') as completed_dtm_formatted,status_code
					from outgoing_logs where department='".pg_escape_string($department)."' 
					and ".$list_cond ;
	}
	
	$result = pg_query($dbconn, $sqlcmd);
	
	while ($row = pg_fetch_array($result)) 
	{ 
		 array_push($result_array,Array(
			$row['completed_dtm_formatted'],
			htmlspecialchars($row['mobile_numb']). ( $row['bot_message_status_id'] ? " (MIM)" : "" ),
			htmlspecialchars($row['department']),
			//htmlentities($row['message'], ENT_SUBSTITUTE, "UTF-8"),
			$row['message'],
			$row['message_status'].($row["message_status"] == "U" ? $row["status_code"] : ""),
			$row['outgoing_id'],
			'<input type="checkbox" name="no" value="'.$row['outgoing_id'].'">'
		));
	} 
	
	echo json_encode(Array("data"=>$result_array));
}

function delete($idx)
{
	global $dbconn;
	
	$getsql = "select trackid,mobile_numb from outgoing_logs where outgoing_id='".pg_escape_string($idx)."'";
	$get = getSQLresult($dbconn, $getsql);
	
	if(!empty($get) && !is_string($get))
	{
		deleteSpoolDBLogs($get[0]['trackid'],$get[0]['mobile_numb']);
		
		$sqlcmd = "delete from outgoing_logs where trackid='".pg_escape_string($get[0]['trackid'])."';
					delete from webapp_sms where msgid='".pg_escape_string($get[0]['trackid'])."'";
		$res = doSQLcmd($dbconn, $sqlcmd);
		
		if (!empty($res)) { 
			echo "Database Error: ".$res;
		}
	}
}

function emptyLog($userid)
{
	global $dbconn;
	
	$getsql = "select trackid,mobile_numb from outgoing_logs where message_status in ('F','E') and sent_by='".pg_escape_string($userid)."'";
	$get = getSQLresult($dbconn, $getsql);
	
	if(!is_string($get)) {
		if(!empty($get)) {
			for($a=0; $a<count($get); $a++) {
				deleteSpoolDBLogs($get[$a]['trackid'],$get[$a]['mobile_numb']);
			}
		}
	}
	
	$sqlcmd = "delete from outgoing_logs where message_status in ('F','E') and sent_by='".pg_escape_string($userid)."'";
	$res = doSQLcmd($dbconn, $sqlcmd);
	
	if (!empty($res)) { 
		echo "Database Error: ".$res;
	}
}

function emptyGlobalLog($userid, $department)
{
	global $dbconn;
	
	if(isUserAdmin($userid)) {
		$getsql = "select trackid,mobile_numb from outgoing_logs where message_status in ('F','E')";
		$sqlcmd = "delete from outgoing_logs where message_status in ('F','E')";
	} else {
		$getsql = "select trackid,mobile_numb from outgoing_logs where department='".pg_escape_string($department)."' and message_status in ('F','E')";
		$sqlcmd = "delete from outgoing_logs where department='".pg_escape_string($department)."' and message_status in ('F','E')";
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
