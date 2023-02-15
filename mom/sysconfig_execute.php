<?php
	require "lib/commonFunc.php";
	require "lib/db_spool.php";

$mode = @$_REQUEST['mode'];
$lang = @$_REQUEST['lang'];

if($mode == 'saveSMSTime'){
	saveSMSTime();
}

function saveSMSTime(){
	global $spdbconn;
	global $lang;

	$xml = GetLanguage("execute_sysconfig",$lang);
	$xml_title = $xml->title;
	$xml_alert_1 = $xml->alert_1;
	$xml_alert_2 = $xml->alert_2;

	$webapp_sms = $_REQUEST['webapp_sms'];
	$batch_sms = $_REQUEST['batch_sms'];
	if(strlen($webapp_sms) == 0){
		$webapp_sms = "0";
	} else {
		$webapp_sms = "1";
	}
	if(strlen($batch_sms) == 0){
		$batch_sms = "0";
	} else {
		$batch_sms = "1";
	}
	$monday_check = $_REQUEST['monday_cb'];
	$monday_check_db = $_REQUEST['monday_cb'];
	if(empty($monday_check_db)){$monday_check_db = 0;} else {$monday_check_db = 1;}
	$mon_start_hour = $_REQUEST['mon_start_hour'];
	$mon_start_min = $_REQUEST['mon_start_min'];
	$mon_end_hour = $_REQUEST['mon_end_hour'];
	$mon_end_min = $_REQUEST['mon_end_min'];

	$tuesday_check = $_REQUEST['tuesday_cb'];
	$tuesday_check_db = $_REQUEST['tuesday_cb'];
	if(empty($tuesday_check_db)){$tuesday_check_db = 0;} else {$tuesday_check_db = 1;}
	$tues_start_hour = $_REQUEST['tues_start_hour'];
	$tues_start_min = $_REQUEST['tues_start_min'];
	$tues_end_hour = $_REQUEST['tues_end_hour'];
	$tues_end_min = $_REQUEST['tues_end_min'];

	$wed_check = $_REQUEST['wed_cb'];
	$wed_check_db = $_REQUEST['wed_cb'];
	if(empty($wed_check_db)){$wed_check_db = 0;} else {$wed_check_db = 1;}
	$wed_start_hour = $_REQUEST['wed_start_hour'];
	$wed_start_min = $_REQUEST['wed_start_min'];
	$wed_end_hour = $_REQUEST['wed_end_hour'];
	$wed_end_min = $_REQUEST['wed_end_min'];

	$thurs_check = $_REQUEST['thurs_cb'];
	$thurs_check_db = $_REQUEST['thurs_cb'];
	if(empty($thurs_check_db)){$thurs_check_db = 0;} else {$thurs_check_db = 1;}
	$thurs_start_hour = $_REQUEST['thurs_start_hour'];
	$thurs_start_min = $_REQUEST['thurs_start_min'];
	$thurs_end_hour = $_REQUEST['thurs_end_hour'];
	$thurs_end_min = $_REQUEST['thurs_end_min'];

	$fri_check = $_REQUEST['fri_cb'];
	$fri_check_db = $_REQUEST['fri_cb'];
	if(empty($fri_check_db)){$fri_check_db = 0;} else {$fri_check_db = 1;}
	$fri_start_hour = $_REQUEST['fri_start_hour'];
	$fri_start_min = $_REQUEST['fri_start_min'];
	$fri_end_hour = $_REQUEST['fri_end_hour'];
	$fri_end_min = $_REQUEST['fri_end_min'];

	$sat_check = $_REQUEST['sat_cb'];
	$sat_check_db = $_REQUEST['sat_cb'];
	if(empty($sat_check_db)){$sat_check_db = 0;} else {$sat_check_db = 1;}
	$sat_start_hour = $_REQUEST['sat_start_hour'];
	$sat_start_min = $_REQUEST['sat_start_min'];
	$sat_end_hour = $_REQUEST['sat_end_hour'];
	$sat_end_min = $_REQUEST['sat_end_min'];

	$sun_check = $_REQUEST['sun_cb'];
	$sun_check_db = $_REQUEST['sun_cb'];
	if(empty($sun_check_db)){$sun_check_db = 0;} else {$sun_check_db = 1;}
	$sun_start_hour = $_REQUEST['sun_start_hour'];
	$sun_start_min = $_REQUEST['sun_start_min'];
	$sun_end_hour = $_REQUEST['sun_end_hour'];
	$sun_end_min = $_REQUEST['sun_end_min'];

	if($start_hour < 10 ){
		$start_hour = "0$start_hour";
	}
	if($monday_check == 1){
		$monday_check = "Y";
	} else {
		$monday_check = "N";
	}
	if($tuesday_check == 1){
		$tuesday_check = "Y";
	} else {
		$tuesday_check = "N";
	}
	if($wed_check == 1){
		$wed_check = "Y";
	} else {
		$wed_check = "N";
	}
	if($thurs_check == 1){
		$thurs_check = "Y";
	} else {
		$thurs_check = "N";
	}
	if($fri_check == 1){
		$fri_check = "Y";
	} else {
		$fri_check = "N";
	}
	if($sat_check == 1){
		$sat_check = "Y";
	} else {
		$sat_check = "N";
	}
	if($sun_check == 1){
		$sun_check = "Y";
	} else {
		$sun_check = "N";
	}

	$rootELementStart = "<time_config>";
   $rootElementEnd = "</time_config>";
   $start_hour = "<start_hour>";
   $start_hour_end = "</start_hour>";
   $start_min = "<start_min>";
   $start_min_end = "</start_min>";
   $end_hour = "<end_hour>";
   $end_hour_end = "</end_hour>";
   $end_min = "<end_min>";
   $end_min_end = "</end_min>";


   $xml_dec = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
   $xml_doc=  $xml_dec . "\r\n";
   $xml_doc .=  $rootELementStart . "\r\n";
   $xml_doc .=  "\t";
   $xml_doc .=  "<webapp_sms>";
   $xml_doc .=  $webapp_sms;
   $xml_doc .=  "</webapp_sms>";
   $xml_doc .=  "\r\n\t";
   $xml_doc .=  "<batch_sms>";
   $xml_doc .=  $batch_sms;
   $xml_doc .=  "</batch_sms>";
   $xml_doc .=  "\r\n\t\t";

   $xml_doc .=  "<day_0>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  "<to_send>";
   $xml_doc .=  "$monday_check";
   $xml_doc .=  "</to_send>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  $start_hour;
   $xml_doc .=  $mon_start_hour;
   $xml_doc .=  $start_hour_end . "\r\n\t\t\t";
   $xml_doc .=  $start_min;
   $xml_doc .=  $mon_start_min;
   $xml_doc .=  $start_min_end . "\r\n\t\t\t";
   $xml_doc .=  $end_hour;
   $xml_doc .=  $mon_end_hour;
   $xml_doc .=  $end_hour_end ."\r\n\t\t\t";
   $xml_doc .=  $end_min;
   $xml_doc .=  $mon_end_min;
   $xml_doc .=  $end_min_end ."\r\n\t\t";
   $xml_doc .=  "</day_0>";
   $xml_doc .=  "\r\n\t\t";
   $xml_doc .=  "<day_1>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  "<to_send>";
   $xml_doc .=  "$tuesday_check";
   $xml_doc .=  "</to_send>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  $start_hour;
   $xml_doc .=  $tues_start_hour;
   $xml_doc .=  $start_hour_end . "\r\n\t\t\t";
   $xml_doc .=  $start_min;
   $xml_doc .=  $tues_start_min;
   $xml_doc .=  $start_min_end . "\r\n\t\t\t";
   $xml_doc .=  $end_hour;
   $xml_doc .=  $tues_end_hour;
   $xml_doc .=  $end_hour_end ."\r\n\t\t\t";
   $xml_doc .=  $end_min;
   $xml_doc .=  $tues_end_min;
   $xml_doc .=  $end_min_end ."\r\n\t\t";
   $xml_doc .=  "</day_1>";
   $xml_doc .=  "\r\n\t\t";
   $xml_doc .=  "<day_2>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  "<to_send>";
   $xml_doc .=  "$wed_check";
   $xml_doc .=  "</to_send>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  $start_hour;
   $xml_doc .=  $wed_start_hour;
   $xml_doc .=  $start_hour_end . "\r\n\t\t\t";
   $xml_doc .=  $start_min;
   $xml_doc .=  $wed_start_min;
   $xml_doc .=  $start_min_end . "\r\n\t\t\t";
   $xml_doc .=  $end_hour;
   $xml_doc .=  $wed_end_hour;
   $xml_doc .=  $end_hour_end ."\r\n\t\t\t";
   $xml_doc .=  $end_min;
   $xml_doc .=  $wed_end_min;
   $xml_doc .=  $end_min_end ."\r\n\t\t";
   $xml_doc .=  "</day_2>";
   $xml_doc .=  "\r\n\t\t";
   $xml_doc .=  "<day_3>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  "<to_send>";
   $xml_doc .=  "$thurs_check";
   $xml_doc .=  "</to_send>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  $start_hour;
   $xml_doc .=  $thurs_start_hour;
   $xml_doc .=  $start_hour_end . "\r\n\t\t\t";
   $xml_doc .=  $start_min;
   $xml_doc .=  $thurs_start_min;
   $xml_doc .=  $start_min_end . "\r\n\t\t\t";
   $xml_doc .=  $end_hour;
   $xml_doc .=  $thurs_end_hour;
   $xml_doc .=  $end_hour_end ."\r\n\t\t\t";
   $xml_doc .=  $end_min;
   $xml_doc .=  $thurs_end_min;
   $xml_doc .=  $end_min_end ."\r\n\t\t\t";
   $xml_doc .=  "</day_3>";
   $xml_doc .=  "\r\n\t\t";
   $xml_doc .=  "<day_4>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  "<to_send>";
   $xml_doc .=  "$fri_check";
   $xml_doc .=  "</to_send>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  $start_hour;
   $xml_doc .=  $fri_start_hour;
   $xml_doc .=  $start_hour_end . "\r\n\t\t\t";
   $xml_doc .=  $start_min;
   $xml_doc .=  $fri_start_min;
   $xml_doc .=  $start_min_end . "\r\n\t\t\t";
   $xml_doc .=  $end_hour;
   $xml_doc .=  $fri_end_hour;
   $xml_doc .=  $end_hour_end ."\r\n\t\t\t";
   $xml_doc .=  $end_min;
   $xml_doc .=  $fri_end_min;
   $xml_doc .=  $end_min_end ."\r\n\t\t";
   $xml_doc .=  "</day_4>";
   $xml_doc .=  "\r\n\t\t";
   $xml_doc .=  "<day_5>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  "<to_send>";
   $xml_doc .=  "$sat_check";
   $xml_doc .=  "</to_send>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  $start_hour;
   $xml_doc .=  $sat_start_hour;
   $xml_doc .=  $start_hour_end . "\r\n\t\t\t";
   $xml_doc .=  $start_min;
   $xml_doc .=  $sat_start_min;
   $xml_doc .=  $start_min_end . "\r\n\t\t\t";
   $xml_doc .=  $end_hour;
   $xml_doc .=  $sat_end_hour;
   $xml_doc .=  $end_hour_end ."\r\n\t\t\t";
   $xml_doc .=  $end_min;
   $xml_doc .=  $sat_end_min;
   $xml_doc .=  $end_min_end ."\r\n\t\t";
   $xml_doc .=  "</day_5>";
   $xml_doc .=  "\r\n\t\t";
	$xml_doc .=  "<day_6>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  "<to_send>";
   $xml_doc .=  "$sun_check";
   $xml_doc .=  "</to_send>";
   $xml_doc .=  "\r\n\t\t\t";
   $xml_doc .=  $start_hour;
   $xml_doc .=  $sun_start_hour;
   $xml_doc .=  $start_hour_end . "\r\n\t\t\t";
   $xml_doc .=  $start_min;
   $xml_doc .=  $sun_start_min;
   $xml_doc .=  $start_min_end . "\r\n\t\t\t";
   $xml_doc .=  $end_hour;
   $xml_doc .=  $sun_end_hour;
   $xml_doc .=  $end_hour_end ."\r\n\t\t\t";
   $xml_doc .=  $end_min;
   $xml_doc .=  $sun_end_min;
   $xml_doc .=  $end_min_end ."\r\n\t\t";
   $xml_doc .=  "</day_6>";
	$xml_doc .=  "\r\n\t\t";
	$xml_doc .=  "\r\n";
	$xml_doc .= $rootElementEnd;

	$file = "/home/msg/conf/sms_time_ctl.xml";//"/home/msg/project/conf/sms_time_ctl.xml";

	$fh = fopen($file, "w");

	if (fwrite($fh, $xml_doc) === FALSE) {
       		//echo "Cannot write to file (autogenerate_report.xml)";
       		echo $xml_alert_1;
					$status = $xml_alert_1;
       		exit;
   	} else{
			$flag = 1;
			$status = $xml_alert_2;
		}

	fclose($fh);

	if( $mon_start_hour < 10 ){
		$mon_start_hour = '0' . $mon_start_hour;
	}
	if( $mon_end_hour < 10 ){
		$mon_end_hour = '0' . $mon_end_hour;
	}
	if( $mon_start_min < 10 ){
		$mon_start_min = '0' . $mon_start_min;
	}
	if( $mon_end_min < 10 ){
		$mon_end_min = '0' . $mon_end_min;
	}

	if( $tues_start_hour < 10 ){
		$tues_start_hour = '0' . $tues_start_hour;
	}
	if( $tues_end_hour < 10 ){
		$tues_end_hour = '0' . $tues_end_hour;
	}
	if( $tues_start_min < 10 ){
		$tues_start_min = '0' . $tues_start_min;
	}
	if( $tues_end_min < 10 ){
		$tues_end_min = '0' . $tues_end_min;
	}

	if( $wed_start_hour < 10 ){
		$wed_start_hour = '0' . $wed_start_hour;
	}
	if( $wed_end_hour < 10 ){
		$wed_end_hour = '0' . $wed_end_hour;
	}
	if( $wed_start_min < 10 ){
		$wed_start_min = '0' . $wed_start_min;
	}
	if( $wed_end_min < 10 ){
		$wed_end_min = '0' . $wed_end_min;
	}

	if( $thurs_start_hour < 10 ){
		$thurs_start_hour = '0' . $thurs_start_hour;
	}
	if( $thurs_end_hour < 10 ){
		$thurs_end_hour = '0' . $thurs_end_hour;
	}
	if( $thurs_start_min < 10 ){
		$thurs_start_min = '0' . $thurs_start_min;
	}
	if( $thurs_end_min < 10 ){
		$thurs_end_min = '0' . $thurs_end_min;
	}

	if( $fri_start_hour < 10 ){
		$fri_start_hour = '0' . $fri_start_hour;
	}
	if( $fri_end_hour < 10 ){
		$fri_end_hour = '0' . $fri_end_hour;
	}
	if( $fri_start_min < 10 ){
		$fri_start_min = '0' . $fri_start_min;
	}
	if( $fri_end_min < 10 ){
		$fri_end_min = '0' . $fri_end_min;
	}

	if( $sat_start_hour < 10 ){
		$sat_start_hour = '0' . $sat_start_hour;
	}
	if( $sat_end_hour < 10 ){
		$sat_end_hour = '0' . $sat_end_hour;
	}
	if( $sat_start_min < 10 ){
		$sat_start_min = '0' . $sat_start_min;
	}
	if( $sat_end_min < 10 ){
		$sat_end_min = '0' . $sat_end_min;
	}

	if( $sun_start_hour < 10 ){
		$sun_start_hour = '0' . $sun_start_hour;
	}
	if( $sun_end_hour < 10 ){
		$sun_end_hour = '0' . $sun_end_hour;
	}
	if( $sun_start_min < 10 ){
		$sun_start_min = '0' . $sun_start_min;
	}
	if( $sun_end_min < 10 ){
		$sun_end_min = '0' . $sun_end_min;
	}
$data['flag'] = $flag;
$data['status'] = $status;
echo json_encode($data);
}
?>
