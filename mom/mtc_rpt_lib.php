<?php
/*	Conversation: Log Maintenance & Report
	 	@author Wafie
*/
require('lib/commonFunc.php');
require('lib/db_sq.php');

$config_file = "/home/msg/conf/chat_rptconfig.xml";
$admin_conf = "/home/msg/conf/reportconfig.xml";
$mode = filter_input(INPUT_POST,'mode');

switch ($mode) {
	case "view":
        view();
        break;
	case "updateLog":
        updateLog(filter_input(INPUT_POST,'keep_chat'));
        break;
	case "updateUsg":
        updateUsg(filter_input(INPUT_POST,'schedule_opt'),
				filter_input(INPUT_POST,'schedule_tm'),
				filter_input(INPUT_POST,'email_report'));
        break;
    default:
        die("Unknown request");
}

function view()
{
	global $config_file;
	$val = array();

	$val['keep_chat'] = Get_ConfigElem($config_file, "keep_chat_history");
	$val['type'] = Get_ConfigElem($config_file, "schedule");
	$val['time'] = Get_ConfigElem($config_file, "time");
	$val['email'] = Get_ConfigElem($config_file, "email_report");

	echo json_encode($val);
}

function updateLog($keep_chat)
{
	global $config_file, $admin_conf;

		$xml = simplexml_load_file($config_file);
		$xml2 = simplexml_load_file($admin_conf);
		$keep_in = Get_ConfigElem($admin_conf, "keep_inbox_message");
		$xml['keep_chat_history'] = trim($keep_chat);

		if($xml['keep_chat_history'] > $keep_in){
			echo "ERROR - Please ensure log keeping duration not exceed admin. Current admin log keeping duration is ".$keep_in." day(s)";
		} else{
			$xml->asXml($config_file);

			//error_log("UPDATE - Maintenance & Report");

			echo "1";
		}
}

function updateUsg($type,$time,$email)
{
	global $config_file;
	$status = 1;

		$xml = simplexml_load_file($config_file);
		$xml['schedule'] = trim($type);
		$xml['time'] = trim($time);
		$xml['email_report'] = trim($email);

		$xml->asXml($config_file);

		//error_log("UPDATE - Maintenance & Report");

	echo $status;
}

?>
