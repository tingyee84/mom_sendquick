<?php
require "lib/commonFunc.php";

$mode = filter_input(INPUT_POST,'mode');
$config_file = "/home/msg/conf/sms_time_ctl.xml";

switch ($mode) {
	case "viewSMSTime":
		if(file_exists($config_file)){	
			$xml = simplexml_load_file($config_file);
			$val['webapp_sms'] = trim($xml->webapp_sms);
			$val['monday_value'] = trim($xml->day_0->to_send);
			$val['monday_starthr'] = trim($xml->day_0->start_hour);
			$val['monday_startmin'] = trim($xml->day_0->start_min);
			$val['monday_endhr'] = trim($xml->day_0->end_hour);
			$val['monday_endmin'] = trim($xml->day_0->end_min);
			$val['tues_value'] = trim($xml->day_1->to_send);
			$val['tues_starthr'] = trim($xml->day_1->start_hour);
			$val['tues_startmin'] = trim($xml->day_1->start_min);
			$val['tues_endhr'] = trim($xml->day_1->end_hour);
			$val['tues_endmin'] = trim($xml->day_1->end_min);
			$val['wed_value'] = trim($xml->day_2->to_send);
			$val['wed_starthr'] = trim($xml->day_2->start_hour);
			$val['wed_startmin'] = trim($xml->day_2->start_min);
			$val['wed_endhr'] = trim($xml->day_2->end_hour);
			$val['wed_endmin'] = trim($xml->day_2->end_min);
			$val['thurs_value'] = trim($xml->day_3->to_send);
			$val['thurs_starthr'] = trim($xml->day_3->start_hour);
			$val['thurs_startmin'] = trim($xml->day_3->start_min);
			$val['thurs_endhr'] = trim($xml->day_3->end_hour);
			$val['thurs_endmin'] = trim($xml->day_3->end_min);
			$val['fri_value'] = trim($xml->day_4->to_send);
			$val['fri_starthr'] = trim($xml->day_4->start_hour);
			$val['fri_startmin'] = trim($xml->day_4->start_min);
			$val['fri_endhr'] = trim($xml->day_4->end_hour);
			$val['fri_endmin'] = trim($xml->day_4->end_min);
			$val['sat_value'] = trim($xml->day_5->to_send);
			$val['sat_starthr'] = trim($xml->day_5->start_hour);
			$val['sat_startmin'] = trim($xml->day_5->start_min);
			$val['sat_endhr'] = trim($xml->day_5->end_hour);
			$val['sat_endmin'] = trim($xml->day_5->end_min);
			$val['sun_value'] = trim($xml->day_6->to_send);
			$val['sun_starthr'] = trim($xml->day_6->start_hour);
			$val['sun_startmin'] = trim($xml->day_6->start_min);
			$val['sun_endhr'] = trim($xml->day_6->end_hour);
			$val['sun_endmin'] = trim($xml->day_6->end_min);
			echo json_encode($val);
		}
		break;
	case "saveSMSTime":
		if(!file_exists($config_file)){	
			$xmlstr='<?xml version="1.0" encoding="UTF-8"?><time_config></time_config>';
			$newXML = new SimpleXMLElement($xmlstr);
			$newXML->asXml($config_file);
		}
	
		$xml = simplexml_load_file($config_file);
		$xml->webapp_sms = $_POST['webapp_sms'] ? $_POST['webapp_sms'] : '0';
		$xml->batch_sms = '0';
		$xml->day_0->to_send = $_POST['monday_cb'] ? $_POST['monday_cb'] : 'N';
		$xml->day_0->start_hour = filter_input(INPUT_POST,'mon_start_hour');
		$xml->day_0->start_min = filter_input(INPUT_POST,'mon_start_min');
		$xml->day_0->end_hour = filter_input(INPUT_POST,'mon_end_hour');
		$xml->day_0->end_min = filter_input(INPUT_POST,'mon_end_min');
		$xml->day_1->to_send = $_POST['tuesday_cb'] ? $_POST['tuesday_cb'] : 'N';
		$xml->day_1->start_hour = filter_input(INPUT_POST,'tue_start_hour');
		$xml->day_1->start_min = filter_input(INPUT_POST,'tue_start_min');
		$xml->day_1->end_hour = filter_input(INPUT_POST,'tue_end_hour');
		$xml->day_1->end_min = filter_input(INPUT_POST,'tue_end_min');
		$xml->day_2->to_send = $_POST['wed_cb'] ? $_POST['wed_cb'] : 'N';
		$xml->day_2->start_hour = filter_input(INPUT_POST,'wed_start_hour');
		$xml->day_2->start_min = filter_input(INPUT_POST,'wed_start_min');
		$xml->day_2->end_hour = filter_input(INPUT_POST,'wed_end_hour');
		$xml->day_2->end_min = filter_input(INPUT_POST,'wed_end_min');
		$xml->day_3->to_send = $_POST['thurs_cb'] ? $_POST['thurs_cb'] : 'N';
		$xml->day_3->start_hour = filter_input(INPUT_POST,'thu_start_hour');
		$xml->day_3->start_min = filter_input(INPUT_POST,'thu_start_min');
		$xml->day_3->end_hour = filter_input(INPUT_POST,'thu_end_hour');
		$xml->day_3->end_min = filter_input(INPUT_POST,'thu_end_min');
		$xml->day_4->to_send = $_POST['fri_cb'] ? $_POST['fri_cb'] : 'N';
		$xml->day_4->start_hour = filter_input(INPUT_POST,'fri_start_hour');
		$xml->day_4->start_min = filter_input(INPUT_POST,'fri_start_min');
		$xml->day_4->end_hour = filter_input(INPUT_POST,'fri_end_hour');
		$xml->day_4->end_min = filter_input(INPUT_POST,'fri_end_min');
		$xml->day_5->to_send = $_POST['sat_cb'] ? $_POST['sat_cb'] : 'N';
		$xml->day_5->start_hour = filter_input(INPUT_POST,'sat_start_hour');
		$xml->day_5->start_min = filter_input(INPUT_POST,'sat_start_min');
		$xml->day_5->end_hour = filter_input(INPUT_POST,'sat_end_hour');
		$xml->day_5->end_min = filter_input(INPUT_POST,'sat_end_min');
		$xml->day_6->to_send = $_POST['sun_cb'] ? $_POST['sun_cb'] : 'N';
		$xml->day_6->start_hour = filter_input(INPUT_POST,'sun_start_hour');
		$xml->day_6->start_min = filter_input(INPUT_POST,'sun_start_min');
		$xml->day_6->end_hour = filter_input(INPUT_POST,'sun_end_hour');
		$xml->day_6->end_min = filter_input(INPUT_POST,'sun_end_min');
		$xml->asXml($config_file);
		break;
	default:
		die("Invalid Command");
}
?>
