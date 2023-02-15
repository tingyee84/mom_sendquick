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
	case "listInbox":
		listInbox($userid, $date_from, $date_to);
		break;
	case "listapiInbox":
		listapiInbox($userid, $date_from, $date_to);
		break;
	case "listGlobalInbox":
		listGlobalInbox($userid, $department, $date_from, $date_to);
		break;
	case "delete":
		delete($idx);
		break;
	case "delete_api":
		delete_api($idx);
		break;
	case "emptyInbox":
		emptyInbox($userid);
		break;
	case "emptyGlobalInbox":
		emptyGlobalInbox($userid, $department);
		break;
}

function listInbox($userid, $from, $to)
{
	global $dbconn;
	$result_array = array();
	
	if(empty($from)) { $from = date(); }
	if(empty($to)) { $to = date(); }

	$sqlcmd = "select incoming_id, mobile_numb, message, to_char(received_dtm, 'DD/MM/YYYY HH24:MI:SS') as received_dtm_formatted
				from incoming_logs where matched_keyword='".pg_escape_string($userid)."' and received_dtm >= to_date('".$from."','DD/MM/YYYY') 
				and received_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day' order by received_dtm desc";
	$result = pg_query($dbconn, $sqlcmd);
	
	while ($row = pg_fetch_array($result)) 
	{ 
		 array_push($result_array,Array(
			$row['received_dtm_formatted'],
			htmlspecialchars($row['mobile_numb']),
			str_to_html($row['message']),
			'<input type="checkbox" class="user_checkbox" name="no" value="'.$row['incoming_id'].'">'
		));
	} 
	
	echo json_encode(Array("data"=>$result_array));
}
function listapiInbox($userid, $from, $to)
{
	global $dbconn;
	$result_array = array();
	
	if(empty($from)) { $from = date(); }
	if(empty($to)) { $to = date(); }
	$depttxt = "";
	if(!isUserAdmin($userid)) {
		$depttxt = "AND b.dept = '".dbSafe($_SESSION["department"])."'";
	}
	$sqlcmd = "SELECT id, mobile_numb, message, b.serviceid, to_char(received_dtm, 'DD/MM/YYYY HH24:MI:SS') as received_dtm_formatted, c.department, a.totalsms,a.send_mode
				from appn_incoming_logs a
				LEFT JOIN appn_list b ON a.clientid = b.clientid
				LEFT JOIN department_list c on b.dept = c.department_id 
				WHERE received_dtm >= to_date('".$from."','DD/MM/YYYY') 
				AND received_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day'
				AND mobile_numb SIMILAR TO '\+\d{7,15}' $depttxt order by received_dtm desc";
	$result = pg_query($dbconn, $sqlcmd);
	
	while ($row = pg_fetch_array($result)) 
	{ 
		 array_push($result_array,Array(
			$row['received_dtm_formatted'],
			htmlspecialchars($row['mobile_numb']),
			$row['serviceid'],
			$row['department'],
			str_to_html($row['message']),
			(strcmp($row['send_mode'],'sms') === 0 ? ($row['totalsms'] == null ? 1 : $row['totalsms']) : "MIM"),
			'<input type="checkbox" name="no" value="'.$row['id'].'">'
		));
	} 
	echo json_encode(Array("data"=>$result_array,"sqlcmd"=>$sqlcmd));
}
function listGlobalInbox($userid, $department, $from, $to)
{
	global $dbconn;
	$result_array = array();
	
	if(empty($from)) { $from = date(); }
	if(empty($to)) { $to = date(); }
	
	$msg_column = "message";
	if(getDisableOutbox()) {
		$msg_column = "length(message) || ' characters.' as message";
	}
	
	$list_cond = "received_dtm >= to_date('".$from."','DD/MM/YYYY') and 
				received_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day'
				order by received_dtm desc";
	
	if(isUserAdmin($userid)) {	
		$sqlcmd = "select incoming_id, matched_keyword, mobile_numb, ".$msg_column.", 
					to_char(received_dtm, 'DD/MM/YYYY HH24:MI') as received_dtm_formatted, 
					department_list.department as department from incoming_logs left outer join 
					department_list on (incoming_logs.department = department_list.department_id) 
					where ".$list_cond;
	} else {	
		$sqlcmd = "select incoming_id, matched_keyword, mobile_numb, ".$msg_column.",department,
					to_char(received_dtm, 'DD/MM/YYYY HH24:MI') as received_dtm_formatted 
					from incoming_logs where department='".pg_escape_string($department)."' 
					and ".$list_cond ;
	}
	
	$result = pg_query($dbconn, $sqlcmd);
	
	while ($row = pg_fetch_array($result)) 
	{ 
		 array_push($result_array,Array(
			$row['received_dtm_formatted'],
			htmlentities($row['matched_keyword'], ENT_SUBSTITUTE, "UTF-8"),
			htmlspecialchars($row['mobile_numb']),
			htmlspecialchars($row['department']),
			str_to_html($row['message']),
			'<input type="checkbox" name="no" value="'.$row['incoming_id'].'">'
		));
	} 
	
	echo json_encode(Array("data"=>$result_array));
}

function delete($idx)
{
	global $dbconn;
	
	$cmd = "delete from incoming_logs where incoming_id='".pg_escape_string($idx)."'";
	$res = doSQLcmd($dbconn, $cmd);
	
	if (!empty($res)) { 
		echo "Database Error: ".$res;
	}
	insertAuditTrail("Delete $idx from incoming_logs");
}
function delete_api($idx)
{
	global $dbconn;
	
	$cmd = "delete from appn_incoming_logs where id='".pg_escape_string($idx)."'";
	$res = doSQLcmd($dbconn, $cmd);
	
	if (!empty($res)) { 
		echo "Database Error: ".$res;
	}
	insertAuditTrail("Delete $idx from appn_incoming_logs");
}

function emptyInbox($userid)
{
	global $dbconn;
	
	$cmd = "delete from incoming_logs where matched_keyword='".pg_escape_string($userid)."'";
	$res = doSQLcmd($dbconn, $cmd);
	
	if (!empty($res)) { 
		echo "Database Error: ".$res;
	}
	insertAuditTrail("Inbox has been truncated.");
}

function emptyGlobalInbox($userid, $department)
{
	global $dbconn;

	if(isUserAdmin($userid))
	{
		$cmd = "delete from incoming_logs";
	}
	else
	{
		$cmd = "delete from incoming_logs where department='".pg_escape_string($department)."'";
	}
	
	$res = doSQLcmd($dbconn, $cmd);
	
	if (!empty($res)) { 
		echo "Database Error: ".$res;
	}
	insertAuditTrail("Global Inbox has been truncated.");
}

function str_to_html($str) 
{
    if (mb_detect_encoding(utf8_decode($str))==='UTF-8') {
		$str = utf8_decode($str);
	} 
	
	return htmlentities($str, ENT_SUBSTITUTE, "UTF-8");
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
