<?php
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 120);
require "lib/commonFunc.php";
include("lib/db_sq.php");

switch ($_POST['mode']) {
	case "view":
        listCommonInbox(filter_input(INPUT_POST,'from'), 
						filter_input(INPUT_POST,'to'));
        break;
    case "delete":
        deleteCommonInbox(filter_input(INPUT_POST,'idx'));
        break;
	case "truncate":
        emptyCommonInbox();
        break;
    default:
		die("Unknown request");
}

function listCommonInbox($from, $to)
{
	global $dbconn;
	$result_array = array();
	
	if(empty($from)) { $from = date(); }
	if(empty($to)) { $to = date(); }
	
	if(getDisableOutbox()){	
		$sqlcmd = "select common_id, mobile_numb, length(unmatched_keyword) || ' characters.' as unmatched_keyword, 
					to_char(received_dtm, 'DD/MM/YYYY HH24:MI:SS') as received_dtm_formatted 
					from common_inbox where received_dtm >= to_date('".$from."','DD/MM/YYYY') and
					received_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day' order by received_dtm desc";	
	} else {
		$sqlcmd = "select common_id, mobile_numb, unmatched_keyword, 
					to_char(received_dtm, 'DD/MM/YYYY HH24:MI:SS') as received_dtm_formatted 
					from common_inbox where received_dtm >= to_date('".$from."','DD/MM/YYYY') and
					received_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day' order by received_dtm desc";
	}
	
	$result = pg_query($dbconn, $sqlcmd);
	
	while ($row = pg_fetch_array($result)) 
	{ 
		 array_push($result_array,Array(
			$row['received_dtm_formatted'],
			(empty($row['mobile_numb']) ? 'MIM' : htmlspecialchars($row['mobile_numb'])),
			str_to_html($row['unmatched_keyword']),
			'<input type="checkbox" name="no" value="'.$row['common_id'].'">'
		));
	} 
	
	echo json_encode(Array("data"=>$result_array));
}

function deleteCommonInbox($idx)
{
	global $dbconn;
	
	$cmd = "delete from common_inbox where common_id='".pg_escape_string($idx)."'";
	$res = doSQLcmd($dbconn, $cmd);
	
	if (!empty($res)) { 
		echo "Database Error: ".$res;
	}
}

function emptyCommonInbox()
{
	global $dbconn;
	
	$cmd = "delete from common_inbox";
	$res = doSQLcmd($dbconn, $cmd);
	
	if (!empty($res)) { 
		echo "Database Error: ".$res;
	}
}

function str_to_html($str) 
{
    if (mb_detect_encoding(utf8_decode($str))==='UTF-8') {
		//Double encoded, bug?
		$str = htmlentities(utf8_decode($str), ENT_SUBSTITUTE, "UTF-8");
	} else if (substr($str, 0, 10) === "MIME:IMAGE") {
		$str = '<img src="..'.substr($str, 11).'" width="50" height="50"/>';
	}  else {
		//To handle bad encoding
		$str = htmlentities($str, ENT_SUBSTITUTE, "UTF-8");
	}
	
	return $str;
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
