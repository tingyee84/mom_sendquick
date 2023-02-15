<?php
require_once('lib/commonFunc.php');

$mode = @$_REQUEST['mode'];
$from = @$_REQUEST['from'];
$to = @$_REQUEST['to'];
$x = GetLanguage("user_account",$lang);

switch ($mode) {
	case "getAccessLog":
        getAccessLog($from,$to);
        break;
    default:
        die('Invalid Command');
}

//Get Access Log
function getAccessLog($from,$to)
{
	global $dbconn;
	global $x;

	$getAccessLog_msg1 = (string)$x->getAccessLog_msg1;
	$db_err = (string)$x->db_err;

	$sqlcmd = "select userid, to_char(login_dtm, 'DD-MM-YYYY HH24:MI:SS') as login_dtm_new, remote_ip, user_agent 
				from access_log where   
				login_dtm >= to_date('".$from."','DD/MM/YYYY') and  
				login_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day' 
				order by login_dtm desc";
	$row = getSQLresult($dbconn, $sqlcmd);
	if(is_string($row))
	{
		echo $db_err." (" .$sqlcmd. ") -- " .pg_last_error($dbconn);
	}
	else
	{
		echo json_encode(Array("data"=>$row));
	}
}
//End getAccessLog
?>
