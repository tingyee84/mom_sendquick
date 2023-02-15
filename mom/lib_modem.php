<?php
require_once('lib/commonFunc.php');
include("lib/db_sq.php");

switch ($_REQUEST['mode']) {
	case "listModem":
        listModem();
        break;
	default:
		die('Invalid Command');
}

function listModem()
{
	global $sqdbconn;

	$sqlcmd = "select idx,modem_imei,by_domain,by_prefix,modem_label from modem_route order by modem_label";
	$row = getSQLresult($sqdbconn, $sqlcmd);

	echo json_encode($row);
}
?>
