<?php
require_once('lib/commonFunc.php');

$mode = filter_input(INPUT_POST,'mode');
$userid = strtolower($_SESSION['userid']);
$dept = $_SESSION['department'];
$ctype = filter_input(INPUT_POST,'contacttype');
$sendtype = filter_input(INPUT_POST,'sendtype');
$RadioType = filter_input(INPUT_POST,'RadioType');
$x = GetLanguage("file_upload_status",$lang);

//error_log("edwin mode:".$mode);

//$mode = "listGlobalMIMTemplate";//for test
//$ctype = "sms";//for test

switch ($mode) {
	
	case "findTplParamElement":
		listTplParamElement(
										filter_input(INPUT_POST,'tpl_id')
										);
		break;
	case "listContacts":
		listContacts($userid, $ctype);
		break;
	case "listGroup":
		listGroup($userid, $ctype);
		break;
	case "listGlobalContacts":
		listGlobalContacts($userid, $dept, $ctype,$RadioType);
		break;
	case "listGlobalGroup":
		listGlobalGroup($userid, $dept, $ctype,$sendtype);
		break;
	case "listTemplate":
		listTemplate($userid);
		break;
	case "listGlobalTemplate":
		listGlobalTemplate($userid, $dept);
		break;
	case "listMIMTemplate":
		listMIMTemplate($userid);
		break;
	case "listGlobalMIMTemplate":
		listGlobalMIMTemplate($userid);
		break;
	case "sendSMS":
		sendSMS(
				$userid, 
				$dept, 
				filter_input(INPUT_POST,'mobile'),
				filter_input(INPUT_POST,'email'),
				filter_input(INPUT_POST,'eml_fr'),
				filter_input(INPUT_POST,'eml_subj'),
				filter_input(INPUT_POST,'smstext'),
				filter_input(INPUT_POST,'count_chars',FILTER_SANITIZE_NUMBER_INT),
				filter_input(INPUT_POST,'charset'),
				filter_input(INPUT_POST,'sendtype'),
				filter_input(INPUT_POST,'priority',FILTER_SANITIZE_NUMBER_INT),
				filter_input(INPUT_POST,'campaign_id',FILTER_SANITIZE_NUMBER_INT),
				filter_input(INPUT_POST,'sendmode'),
				filter_input(INPUT_POST,'bot_id'),
				filter_input(INPUT_POST,'tpl_type'),
				filter_input(INPUT_POST,'tpl_id'),
				filter_input(INPUT_POST,'mim_params'),
				$_FILES,
				filter_input(INPUT_POST,'callerid')
				);
		break;
	
	case "sendSMS_v2":
	
		$pre_activated_dtm = strtotime( date("Y-m-d H:i:s") ) - 120;
		
		sendScheduledSMS(
						$userid, 
						$dept, 
						filter_input(INPUT_POST,'mobile'),
						filter_input(INPUT_POST,'email'),
						filter_input(INPUT_POST,'eml_fr'),
						filter_input(INPUT_POST,'eml_subj'),
						filter_input(INPUT_POST,'smstext'), 
						filter_input(INPUT_POST,'count_chars',FILTER_SANITIZE_NUMBER_INT), 
						filter_input(INPUT_POST,'charset'),
						filter_input(INPUT_POST,'sendtype'), 
						filter_input(INPUT_POST,'priority',FILTER_SANITIZE_NUMBER_INT), 
						date( "Y-m-d", $pre_activated_dtm )." ".date( "H", $pre_activated_dtm ).":".date( "i", $pre_activated_dtm ),
						filter_input(INPUT_POST,'sendmode'),
						filter_input(INPUT_POST,'campaign_id',FILTER_SANITIZE_NUMBER_INT),
						filter_input(INPUT_POST,'tpl_type'),
						filter_input(INPUT_POST,'tpl_id'),
						filter_input(INPUT_POST,'mim_params'),
						filter_input(INPUT_POST,'bot_id'),
						$_FILES,
						filter_input(INPUT_POST,'callerid'),
						filter_input(INPUT_POST,'mim_file_type')
						);
					
		break;
		
	case "sendScheduledSMS":
		sendScheduledSMS(
						$userid, 
						$dept, 
						filter_input(INPUT_POST,'mobile'),
						filter_input(INPUT_POST,'email'),
						filter_input(INPUT_POST,'eml_fr'),
						filter_input(INPUT_POST,'eml_subj'),
						filter_input(INPUT_POST,'smstext'), 
						filter_input(INPUT_POST,'count_chars',FILTER_SANITIZE_NUMBER_INT), 
						filter_input(INPUT_POST,'charset'),
						filter_input(INPUT_POST,'sendtype'), 
						filter_input(INPUT_POST,'priority',FILTER_SANITIZE_NUMBER_INT), 
						$_POST['sms_date']." ".$_POST['sms_hour'].":".$_POST['sms_min'],
						filter_input(INPUT_POST,'sendmode'),
						filter_input(INPUT_POST,'campaign_id',FILTER_SANITIZE_NUMBER_INT),
						filter_input(INPUT_POST,'tpl_type'),
						filter_input(INPUT_POST,'tpl_id'),
						filter_input(INPUT_POST,'mim_params'),
						filter_input(INPUT_POST,'bot_id'),
						$_FILES,
						filter_input(INPUT_POST,'callerid'),
						filter_input(INPUT_POST,'mim_file_type')
						);
		break;
	case "getMessage":
		getMessage($userid,$department,filter_input(INPUT_POST,'msgid'));
		break;
	case "get_template_datas":
		get_template_datas( filter_input(INPUT_POST,'tpl_id'), filter_input(INPUT_POST,'field') );
		break;
    default:
        die('Invalid Command');
}

function get_template_datas( $tpl_id, $field ){
	
	global $dbconn, $lang;

	$sqlcmd = "select $field from message_template where template_id='".pg_escape_string($tpl_id)."'";
	$row = getSQLresult($dbconn, $sqlcmd);
	
	echo $row[0][ $field ];
	//echo nl2br($row[0][ $field ]);
	//print_r( $row[0][ $field ] );
	//echo json_encode($row[]);
	
}

function  updateSurvey( $campaign_id, $department, $mobile_list, $sendmode, $bot_id, $callerid ){
	
	global $dbconn, $lang, $userid;
	
	$mobile_arr = explode("\n", stripslashes(trim($mobile_list)));
		
	$campaign_detail = getCampaign( $campaign_id );
	$now = date("Y-m-d H:i:s");
	$campaign_start_date = $campaign_detail['campaign_start_date'] . " 00:00:00";
	$campaign_end_date = $campaign_detail['campaign_end_date'] . " 00:00:00";
	
	if( $campaign_detail["campaign_type"] == "2" && $campaign_detail["keyword"] ){
	
		if( strtotime( $campaign_start_date ) <= strtotime( $now ) && strtotime( $campaign_end_date ) >= strtotime( $now ) ){
		
			for($a=0; $a<count($mobile_arr); $a++) {
				
				$tmp_arr = explode("(", $mobile_arr[$a]);
				$mobile_numb = trim($tmp_arr[0]);
				
				$mobile_status = validateMno( $mobile_numb );
				
				if( $mobile_status !=  "-1" ){
					
					$mobile_numb = $mobile_status;//replace with updated format
					$keyword = $campaign_detail['keyword'];
					
					$keyword_full_txt = "";
					$sqlZ1 = "select keyword from mom_sms_response where id in ( $keyword ) order by keyword asc";
					$rowZ1 = getSQLresult($dbconn, $sqlZ1);
					foreach( $rowZ1 as $key => $data ){
						
						if( $keyword_full_txt == "" ){
							$keyword_full_txt = $data["keyword"];
						}else{
							$keyword_full_txt = $keyword_full_txt . "," . $data["keyword"];	
						}
						
					}
					
					$sql1 = "insert into campagin_survey_outbox ( campagin_id, department, keywords, type, send_mode, mobile_no, bot_id, cby, label, keyword2 ) values ( '$campaign_id', '". pg_escape_string($department) ."', '". pg_escape_string($keyword) ."', '0', '".$sendmode."', '".$mobile_numb."', '".$bot_id."', '$userid', '$callerid', '$keyword_full_txt' )";
					
					//echo $sql1;
					//die;
					
					$row1 = doSQLcmd($dbconn, $sql1);
					
				}
				
			}
		
		}
		
	}
	
	return 1;
		
}

function sendSMS($userid, $department, $mobile_list, $email_list, $email_from, $email_subj, $sms_text, $total_length, $mode, $sendtype, $priority, $campaign_id, $sendmode, $bot_id, $tpl_type, $tpl_id, $mim_params, $files, $callerid )
{
	
	global $dbconn, $lang;
	
	$now = date("Y-m-d 23:59:59");
	
	//update survey
	$updateSurvey = updateSurvey( $campaign_id, $department, $mobile_list, $sendtype, $bot_id, $callerid );
	
	//print_r( $updateSurvey );
	//die;
	
	error_log("sms len: ". strlen($sms_text));

	$email_body = $sms_text;
	$msgstr = GetLanguage("lib_send_sms",$lang);
	$sendSMS_msg1 = (string)$msgstr->sendSMS_msg1;
	$sendSMS_msg2 = (string)$msgstr->sendSMS_msg2;

	$msg_from = $userid. " (" .$_SERVER['REMOTE_ADDR']. ")";
	$sms_text = urldecode(trim($sms_text));
	$email_body = urldecode(trim($email_body));
	//$label = getLabel($department);
	$label = $callerid;

	error_log("sms len 2: ". strlen($sms_text));

	
	if( $tpl_type == "mim_msg_template" ){
		
		$split1 = explode( "@@", $mim_params );
		
		foreach( $split1 as $key => $value ){
			
			$split2 = explode( "==", $value );
			
			$ParamName = "<" . $split2[0] . ">";
			$ParamValue = $split2[1];
			
			$sms_text = str_replace( $ParamName, $ParamValue ,$sms_text );
			
			if( $ParamValueOnly == "" ){
				$ParamValueOnly = $split2[1];
			}else{
				$ParamValueOnly = $ParamValueOnly . "@@@". $split2[1];
			}
	
		}
		
	}
	
	if($mode == "text") {
		$sms_length = strlen($sms_text);
	} else {
		$sms_length = mb_strlen($sms_text,'UTF-8');
	}
	
	/*
	if($sms_length > $total_length) {
		$sms_text = substr($sms_text, 0, $total_length);
	}
	*/
	
	$mobile_arr = explode("\n", stripslashes(trim($mobile_list)));
	$result = processSendSMS($userid, $department,$mode, $mobile_arr, $sms_text, $msg_from, $label,$priority, $msgstr, $campaign_id);
	$sent_sms = $result[0];
	$error = $result[1];
	$quota_msg = $result[2];

	//error_log("sendtype:".$sendtype);
	$email_result = array();
	$eml_totsend =0;
	$eml_unsend = 0;
	$eml_comment = '';
	
	if($sendtype == 'both'){
		$email_arr = explode("\n", stripslashes(trim($email_list)));
		//error_log("emailcount:".empty($email_arr)." ".json_encode($email_arr));
		if(!empty($email_arr)){
			$email_result = processSendEmail($userid, $department,$mode, $email_arr, $email_from, $email_subj, $email_body);
		}
		
		$eml_totsend = $email_result[0];
		$eml_unsend	 = $email_result[1];
		$eml_comment = $email_result[2];
		
		if($eml_unsend > 0){
			$error = 1;
		}
	}
	
	if($sendtype == 'sms_mim'){
	
		//get bot details
		$bot = getBotDetails( $bot_id );
		$bot_datas = getBotByBotID( $bot_id );
		
		if( $bot_datas['bot_type_id'] == 13 ){//whatsapp DC
			
			$datas['id'] = $bot['campaignId'];//IO campaign_id
			$datas['secret'] = $bot['campaignSecret'];//IO Campaign Secret
		
			$datas['subscribers'] = explode("\n", stripslashes(trim($mobile_list)));
			
			foreach( $datas['subscribers'] as $key => $mobile_no ){
				
				$mobile_status = validateMno( $mobile_no );
				
				//Check Quota again for sms_mim
				$check_quota_type = checkQuotaType($_SESSION['userid'], $dbconn);
				$check_unlimited_quota = checkQuotaUnlimited($_SESSION['userid'], $dbconn);
				$check_quota = 0;
				$ReservedQuota = checkReservedQuota( $_SESSION['userid'],$dbconn );
				
				if($check_unlimited_quota != 1) {
					
					$check_quota = checkQuota($_SESSION['userid'], $dbconn);
				
					if( ( $check_quota == 0 ) || ( ( $check_quota - $ReservedQuota ) <  1 ) ){
					
						$valid_quota = "no";
						
					} else {
						
						$valid_quota = "yes";
						
						$new_quota = $check_quota - 1;
						$sql = "update quota_mnt set quota_left='".$new_quota."' where userid='". $_SESSION['userid']."'";
						$result = doSQLcmd($dbconn,$sql);
						
						if(!$result){
							
							$valid_quota = "no";//error deduct, no send mim
							//$error++;
							error_log($sqlcmd . ' -- ' .pg_last_error($dbconn));
						}
						
					}
					
				}else{
					
					$valid_quota = "yes";//unlimited, just passed
				}
				//end check quota
				
				if( $mobile_status != "-1" && $valid_quota == "yes" ){
				
					if( $subscribers_new == "" ){
						$subscribers_new = $mobile_no;
					}else{
						$subscribers_new = $subscribers_new . "," . $mobile_no;
					}
					
				}
				
			}
			
			$datas['subscribers']  = explode( ",", $subscribers_new );//replace with valid number, excluded non +65
			
			if( $files["mim_image1"] ){
				
				$uploaded_files_url = upload_files( $files );
				
				if( $uploaded_files_url['status'] == "1" ){
					$file_location = $uploaded_files_url['file_location'];
				}else{
					$file_location = "";
				}
			
			}else{
				$file_location = "";
			}
			
			if( $file_location ){
				
				$sendAsTemplate = "0";
				$datas['url'] = $file_location;
				
				//below 2 line is for test from .52
				//$mainURL = "https://stagingmom.sendquickasp.com/mom/";//for test, bcoz dont have domain
				//$datas['url'] = $mainURL . "images/mim_uploaded/2020061214051307825.png";//for test, bcoz dont have domain
				
				//$datas['caption'] = "test image";
				$datas['caption'] = $sms_text;
				$datas['type'] = "image";
				
			}else{
				
				$datas['type'] = "text";
				
				$tpl_detail = getTplDetail( $tpl_id );
				if( $tpl_detail['mim_tpl_id'] ){
					$sendAsTemplate = "1";
					$datas['sendAsTemplate'] = "1";
					$datas['templateName'] = $tpl_detail['mim_tpl_id'];
					$datas['message'] = explode( "@@@", $ParamValueOnly );//for whatsapp, only send param value
				}else{
					$sendAsTemplate = "0";
					$datas['message'] = $sms_text;//if not use tpl, send all text
					
				}
			
			}
		
			$datas = json_encode($datas);
			
			//print_r( $sendAsTemplate );
			//die;
			
			$WDC_result = SendMsgViaWhatsappDC( $datas, $bot['campaignAccessToken'], $bot_id, $sms_text, $priority, $department, $label, $campaign_id, $sendAsTemplate, $file_location );
			if( $WDC_result['status'] == "error" ){
				$error = 1;
			}
		
		}
	
	}

	$val = array();
	if($error > 0) {
		$val['output'] = $error.(!empty($quota_msg)?"<br>".$quota_msg:"");
		if($sendtype == 'both') {
			if($eml_unsend > 0) {
				$val['output'] .= "<br>".$eml_unsend." Email doesn't send. Error:".$eml_comment;
			}
			if($eml_totsend > 0){
				$val['output'] .= "<br>".$eml_totsend." Email Successfully Sent. ";
			}
		}elseif( $sendtype == 'sms_mim' ){
			
			if( $WDC_result['status'] == "error" ){
				$val['output'] .= "<br>Failed send MIM.";
			}else{
				$val['output'] .= "<br>MIM successfully sent.";
			}
			
		}
		
		$val['error'] = "1";
	} else {
	
		$msgstr2 = GetLanguage("send_sms",$lang);
		$val['output'] = $sent_sms." ".$msgstr2->alert_7;
		if($sendtype == 'both') {
			$val['output'] .= "<br>".$eml_totsend." Email Successfully Sent. ";
		}elseif( $sendtype == 'sms_mim' ){
			
			if( $WDC_result['status'] == "error" ){
				$val['output'] .= "<br>Failed send MIM.";
			}else{
				$val['output'] .= "<br>MIM successfully sent.";
			}
			
		}
		
		$val['error'] = "0";
	}
	
	insertAuditTrail( "Send Message By Manual" );

	echo json_encode($val);
}

function getBotDetails( $bot_id ){
	
	global $spdbconn;
	
	$sqlcmd = "select a.bot_api_parameter_id, a.parameter_value, b.parameter from bot_api_configs a, bot_api_parameters b where a.bot_api_parameter_id = b.id and a.bot_id = '$bot_id'";
	
	$row = getSQLresult($spdbconn, $sqlcmd);
	foreach( $row as $key => $settings ){
		$returns[ $settings["parameter"] ] = $settings["parameter_value"];
	}
	
	return $returns;
	
}

function getTplDetail( $tpl_id ){
	
	global $dbconn;
	
	$sqlcmd = "select * from message_template where template_id = '$tpl_id'";
	
	$row = getSQLresult($dbconn, $sqlcmd);
	
	return $row[0];
	
}

function getBotByBotID( $bot_id ){
	
	global $spdbconn;
	
	$sqlcmd = "select * from bot_route where id = '$bot_id'";
	
	$row = getSQLresult($spdbconn, $sqlcmd);
	
	return $row[0];
	
}

function SendMsgViaWhatsappDC( $datas, $campaignAccessToken, $bot_id, $sms_txt, $priority, $department, $label, $campaign_id, $sendAsTemplate, $file_location ){
	
	global $spdbconn, $dbconn;
	
	$url = "https://api.sendquick.io/channel-subscribers/send";

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $datas);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		//'Authorization: 7wMVLp35hSQfhnscfwrID554OXwAbMiURhqJ8CTUMcUvfZFY34VppZwB1Ob9MEvo',//IO Campaign Access Token
		'Authorization: ' . $campaignAccessToken,//IO Campaign Access Token
		'Content-Type: application/json',
	));

	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

	$api_result = curl_exec($curl);
	//$api_result2 = json_encode($api_result);
	$api_result3 = json_decode($api_result);
	
	if(  isset($api_result3->data->broadcastId) ){
		
		$msg_status = "Y";
		$returns["status"] = "sent";
		$returns["broadcastId"] = ( $api_result3->data->broadcastId ? $api_result3->data->broadcastId : "" );
		$api_response = $returns["broadcastId"];
		$send_status = "Y";
		$outgoing_log_message_status = "Y";
		
	}else{
		
		$msg_status = "F";
		$returns["status"] = "error";
		$returns["code"] = ( $api_result3->error->statusCode ? $api_result3->error->statusCode : "" );
		$returns["message"] = ( $api_result3->error->message ? $api_result3->error->message : "" );
		$api_response = $returns["message"];
		$send_status = "F";
		$outgoing_log_message_status = "F";
		//echo "fail";
	}
	
	$ori_datas = json_decode( $datas );

	$api_sent_received = array( "sent"=> $ori_datas, "received"=>$api_result3 );
	
	//insert into bot_message_status and message_status
	foreach( $ori_datas->subscribers as $key => $mno ){
		
		$msgid = date("YmdHIs") . mt_rand(100000, 999999);
		$msg_from = $_SESSION['userid'] . " (" . $_SESSION['server_prefix'] . ")";
		$msg_content = $sms_txt;
		$msg_type = "W";
		$charset = "1";
		$bot = getBotByBotID( $bot_id );
		$bot_msg_status_id = getSequence($spdbconn,'bot_message_status_id_seq');
		
		$sql1 = "insert into message_status ( msgid, mobile_numb, msg_from, msg_content, msg_status, msg_type, completed_dtm, charset, raw_message, priority ) values ( '$msgid', '$mno', '$msg_from', '".pg_escape_string($msg_content)."', '$msg_status', '$msg_type', now(), '$charset', '".pg_escape_string($msg_content)."', '$priority' )";
	
		$sql2 = "insert into bot_message_status ( id, msgid, bot_id, type, target_user_id, api_response, status, send_process_flag, remote_msgid, send_template, priority, api_sent_received ) values ( '$bot_msg_status_id', '$msgid', '$bot_id', '$bot[bot_type_id]', '$mno', '$api_response', '$send_status', '1', '".$returns["broadcastId"]."', '$sendAsTemplate', '$priority', '".print_r( $api_sent_received, true )."'  )";
		
		//if mim need insert one copy, so that in mom log page can view
		$t = getSQLresult($dbconn, "select nextval('message_trackid') as trackid");
		$trackid = $_SESSION['server_prefix'].date('His').$t[0]['trackid'];
					
		$outgoing_id = getSequenceID($dbconn,'outgoing_logs_outgoing_id_seq');
		$sql3 = "insert into outgoing_logs (outgoing_id,msgid, priority,trackid,sent_by,department,mobile_numb,message,message_status,completed_dtm, modem_label, campaign_id, bot_message_status_id, bot_types_id, file_location ) values ('".$outgoing_id."', '".$msgid."', '".$priority."','".$trackid."','".pg_escape_string( $_SESSION['userid'] )."','".pg_escape_string($department)."','".$mno."','".pg_escape_string($msg_content)."','$outgoing_log_message_status', now(), '".$label."', '".$campaign_id."', '$bot_msg_status_id', '$bot[bot_type_id]', '".pg_escape_string($file_location)."')";
		
		//echo $sql2;
		//die;
		//$all_sql['sql1'][] = $sql1;
		//$all_sql['sql2'][] = $sql2;
		//$all_sql['sql3'][] = $sql3;
		
		//print_r( $all_sql );
		//die;
		
		$row1 = doSQLcmd($spdbconn, $sql1);
		if( $row1 ){
			
			$row2 = doSQLcmd($spdbconn, $sql2);
			if( $row2 ){
				
				$row3 = doSQLcmd($dbconn, $sql3);
				//if( !$row3 ){
					//echo $sql3;
				//}
				
			}
			
		}
		
	}
	
	//print_r( $all_sql );
	//die;
	
	return $returns;
}

function upload_files( $files, $mim_file_type ){

	$file = $files['mim_image1'];
	$FileType = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
	
	$getcwd = basename(getcwd());
	//$mainURL = "https://" . $_SERVER['HTTP_HOST'] . "/mom/";
	$mainURL = "https://" . $_SERVER['HTTP_HOST'] . "/$getcwd/";
	//$mainURL = "https://stagingmom.sendquickasp.com/mom/";//for test, bcoz dont have domain

	$target_dir = "images/mim_uploaded/";
	$target_file = $target_dir . date("YmdHIs") . mt_rand(100000, 999999) . "." . $FileType;
	
	if( $mim_file_type == "1" ){
		$allowed_image = array( "jpg", "jpeg", "png" );
	}elseif( $mim_file_type == "2" ){
		$allowed_image = array( "pdf" );
	}else{
		$allowed_image = array( "jpg", "jpeg", "png" );
	}
	
	$check = getimagesize( $file["tmp_name"] );
	
	//if( $check ) {//is image
		
		if ( $file["size"] < 5000000 ) {//5mb
			
			//check extension
			if( in_array( $FileType, $allowed_image ) ){//valid extension
				
				if ( move_uploaded_file( $file["tmp_name"], $target_file ) ) {//saved
				
					$returns['status'] = "1";
					$returns['message'] = "Uploaded";
					$returns['file_location'] = $mainURL . $target_file;
					
				}else{//failed save
				
					$returns['status'] = "2";
					$returns['message'] = "Fail upload file";
					
				}
				
			}else{//invalid extension
				
				$returns['status'] = "3";
				$returns['message'] = "Invalid file extension";
					
			}
		
		}else{//not allowed file size
			
			$returns['status'] = "3";
			$returns['message'] = "Invalid file size";
				
		}
		
	//} else {//not image
		
		//$returns['status'] = "3";
		//$returns['message'] = "Only image jpeg and png allowed.";
				
	//}
	
	return $returns;
	
}

function processSendSMS($userid, $department,$mode, $mobile_arr, $sms_text, $msg_from, $label,$priority, $msgstr, $campaign_id){
	global $dbconn, $lang;
	error_log("Send SMS");
	if(empty($priority)) { $priority = "5";}
	
	$sendSMS_msg1 = (string)$msgstr->sendSMS_msg1;
	$sendSMS_msg2 = (string)$msgstr->sendSMS_msg2;
	$quota_msg="";
	$unsub = "";
	
	$error = 0;
	$sent_sms = 0;
	$pattern = "/^\+?\d+$/";
	
	for($a=0; $a<count($mobile_arr); $a++) {
		$tmp_arr = explode("(", $mobile_arr[$a]);
		$mobile_numb = trim($tmp_arr[0]);

		if(strlen($mobile_numb) != 0) {
			if(preg_match($pattern, $mobile_numb)) {
				$match = checkUnsubMobile($mobile_numb);
				if($match){
					$error++;
					if($unsub != "") {
						$unsub .= ", ".$mobile_numb;
					} else {
						$unsub = $mobile_numb;
					}
					continue;
				}
				
				$mobile_numb_check = validateMno($mobile_numb);
		
				if( $mobile_numb_check != "-1" ){
					
					$mobile_numb = $mobile_numb_check;//replace
					
					$t = getSQLresult($dbconn, "select nextval('message_trackid') as trackid");
					$trackid = $_SESSION['server_prefix'].date('His').$t[0]['trackid'];

					//Check Quota
					$check_quota_type = checkQuotaType($_SESSION['userid'], $dbconn);
					$check_unlimited_quota = checkQuotaUnlimited($_SESSION['userid'], $dbconn);
					$check_quota = 0;
					$ReservedQuota = checkReservedQuota( $_SESSION['userid'],$dbconn );					
					if($check_unlimited_quota != 1) {
						$check_quota = checkQuota($_SESSION['userid'], $dbconn);
						$total_sms_needed = getSMSNeeded($sms_text);
						error_log("NewQuota: $check_quota - $total_sms_needed");
						$new_quota = $check_quota - $total_sms_needed;
						if( ( $check_quota == 0 ) || ( $new_quota <  0 ) ){
							$quota_msg = $sendSMS_msg1;
							$row = 0;
							$error++;
						} else {						
							$sql = "update quota_mnt set quota_left='".$new_quota."' where userid='". $_SESSION['userid']."'";
							error_log("SQL: $sql");
							$result = doSQLcmd($dbconn,$sql);
							
							if(!$result){
								//$error++;
								error_log($sql . ' -- ' .pg_last_error($dbconn));
							}
						}
					}

					if( $check_quota > 0 || $check_unlimited_quota == 1){
						$sms_text_escape = pg_escape_literal($sms_text);
						$sms_text_insert = preg_replace('/\n/','<br>',$sms_text_escape);
						
						$outgoing_id = getSequenceID($dbconn,'outgoing_logs_outgoing_id_seq');
						$sqlcmd = "insert into outgoing_logs (outgoing_id,priority,trackid,sent_by,department,mobile_numb,message,message_status,modem_label, campaign_id )
									values ('".$outgoing_id."','".$priority."','".$trackid."','".pg_escape_string($userid)."','".pg_escape_string($department)."','".$mobile_numb."',{$sms_text_insert},'P','".$label."', '".$campaign_id."')";
						$row = doSQLcmd($dbconn, $sqlcmd);
					} 

					if($row > 0) {
						$response = internal_post($mobile_numb, $sms_text_escape, $mode, $priority, $msg_from, $trackid, $label, $campaign_id);
						
						if(empty($response)) {
							$updatesql = "update outgoing_logs set message_status='F',completed_dtm='now()' where trackid='".dbSafe($trackid)."'";
							$update = doSQLcmd($dbconn, $updatesql);
							$error++;
						} else {
							$sent_sms++;
						}
					}
					
				}else{
					$error++;
					if($invalid != "") {
						$invalid .= ", ".$mobile_numb;
					} else {
						$invalid = $mobile_numb;
					}
					continue;	
				}
				
			}
		}
	}

	if($invalid != ""){
		$invalid_msg = "Invalid Moblie Number Found:<br>".$invalid;
		$quota_msg .= "<br>".$invalid_msg."<br>";
	}
	if($unsub != ""){
		 $unsub_msg = "Unsubscribe Moblie Number Found:<br>".$unsub;
		 $quota_msg .="<br>".$unsub_msg."<br>";
	}	
	$error = 0;
	return array($sent_sms, $error, $quota_msg);
}
function processSendEmail($userid, $department,$mode, $email_arr, $email_from, $email_subj, $body){
	
	require('lib/class.smtp.php');
	require('lib/class.phpmailer.php');
	
	global $dbconn;
	global $spdbconn;
	
	$cmd = "select host,port,auth,type,username,password 
			from smtp_route where idx='1'";
	$res = pg_query($spdbconn, $cmd);
	$row = pg_fetch_row($res);

	$totalsend = 0;
	$totalunsent = 0;
	
	if($email_from == ''){
		$email_from = "system@smsgateway.com";
	}
	if($email_subj == ''){
		$email_subj = "From SMS Gateway";
	}

	$comment = '';
	for($a=0; $a<count($email_arr); $a++) {
		
		$fields = "sent_by, department, email_from, email_subj, body, completed_dtm, email_id, email, status, comment";
		$cols = "'$userid', '$department', '".dbSafe($email_from)."', '".dbSafe($email_subj)."', '".dbSafe($body)."'";
	
		$tmp_arr = explode("(", $email_arr[$a]);
		$to_email = trim($tmp_arr[0]);
		
		//reset from start to make it each email sent individual and can not see other receiver
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->CharSet = 'UTF-8';
		$mail->Timeout = 10;
		//$mail->Host = trim($row[0]);
		//$mail->Port = trim($row[1]);
		$mail->Host = "localhost";
		$mail->Port = 25;
		
		/*
		if (trim($row[2]) == "t") {
			$mail->SMTPAuth = true;
			$mail->Username = trim($row[4]);
			$mail->Password = trim($row[5]);
			if (!empty($row[2])) { 
				$mail->SMTPSecure = strtolower($row[3]);
			}
		}
		*/
		
		$mail->From     = $email_from;
		$mail->Subject  = $email_subj;
		$mail->Body     = $body;
		
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
				)
		);
	
	
		$mail->AddAddress($to_email);
		
		$email_id = getSequenceID($dbconn,'outgoing_logs_outgoing_id_seq');
		//$date = date('Y-m-d H:i:s');
		$cols .= ", 'now()', '$email_id','".dbSafe($to_email)."'";
		if(!$mail->Send()) {
			$comment = @$mail->ErrorInfo;
			error_log("Fail to send: ".$to_email." ".$comment);
			$cols .= ", '0', '".dbSafe($comment)."'";
			$totalunsent++;
		} else {
			$totalsend++;
			$cols .= ", '1', '".dbSafe($comment)."'";
		}
		
		//error_log( "sqlcmd: " . $sqlcmd . "\n\n\n" );
		
		$sqlcmd = "insert into email_logs (".$fields.") values (".$cols.")";
		if(!doSQLcmd($dbconn, $sqlcmd)){
			error_log("FAIL:".$sqlcmd);
		}
	}
	
	return array($totalsend, $totalunsent, $comment);
}
function internal_post($mobile_numb, $msg_content, $mode, $priority, $msg_from, $trackid, $label, $campaign_id)
{
	global $dbconn;

  	$sqlcmd = "INSERT INTO webapp_sms (msgid, mobile_numb, msg_content, mode, priority, msg_from, msg_status, label, campaign_id )
				VALUES ('$trackid', '$mobile_numb', {$msg_content}, '$mode', '$priority', '$msg_from', 'W','$label', '$campaign_id') ";
	$result = pg_query($dbconn, $sqlcmd);
	
	if(!$result) {
		error_log("Database Error (" .$sqlcmd. ") -- ".pg_last_error($dbconn));
		return 0;
	} else {
		return pg_affected_rows($result);
	}
}

function sendScheduledSMS($userid,$department,$mobile_list,$email_list, $email_from, $email_subj,$sms_text,$total_length,$mode,$sendtype, $priority_mode,$scheduled_time, $sendmode, $campaign_id, $tpl_type, $tpl_id, $mim_params, $bot_id, $files, $callerid, $mim_file_type )
{
	global $dbconn, $lang;
	//error_log("Schedule");
	
	//echo $mim_file_type;
	//die;
	
	$msgstr = GetLanguage("lib_send_sms",$lang);
	$sendSMS_msg1 = (string)$msgstr->sendSMS_msg1;
	$sendScheduledSMS_msg1 = (string)$msgstr->sendScheduledSMS_msg1;
	$sendSMS_msg2 = (string)$msgstr->sendSMS_msg2;

	$msg_from = $userid." (" .$_SERVER['REMOTE_ADDR']. ")";
	$sms_text = urldecode(trim($sms_text));
	//$label = getLabel($department);
	$label = $callerid;
	
	if( $tpl_type == "mim_msg_template" || $tpl_type == "global_mim_msg_template"  ){
		
		$tpl_detail = getTplDetail( $tpl_id );
		if( $tpl_detail['mim_tpl_id'] ){
			$mim_tpl_id = $tpl_detail['mim_tpl_id'];
		}else{
			$mim_tpl_id = "";
		}
			
		$split1 = explode( "@@", $mim_params );
		
		foreach( $split1 as $key => $value ){
			
			$split2 = explode( "==", $value );
			
			$ParamName = "<" . $split2[0] . ">";
			$ParamValue = $split2[1];
			
			$sms_text = str_replace( $ParamName, $ParamValue ,$sms_text );
			
			if( $ParamValueOnly == "" ){
				$ParamValueOnly = $split2[1];
			}else{
				$ParamValueOnly = $ParamValueOnly . "@@@". $split2[1];
			}
	
		}
		
	}
	
	if($mode == "text") {
		$sms_length = strlen($sms_text);
	} else {
		$sms_length = mb_strlen($sms_text,'UTF-8');
	}

	if($sms_length > $total_length) {
		$sms_text = substr($sms_text, 0, $total_length);
	}

	if(empty($priority_mode)) {
		$priority_mode = "5";
	}

	$error = 0;
	$queue_sms = 0;
	$pattern = "/^\+?\d+$/";
	
	$mobile_arr = explode("\n", stripslashes(trim($mobile_list)));
	$quota_msg = '';
	$unsub = "";
	$invalid = "";
	
	//check this batch all credit required
	$is_unlimited_quota = checkQuotaUnlimited($_SESSION['userid'], $dbconn);
	$current_quot_left = checkQuota($_SESSION['userid'], $dbconn);
	$ReservedQuota = checkReservedQuota( $_SESSION['userid'],$dbconn );	
	
	if( $sendtype == "sms" ){
		$total_sms_needed = getSMSNeeded($sms_text);	
	}elseif( $sendtype == "sms_mim" ){
		$total_sms_needed = getSMSNeeded($sms_text) + 1;	
	}elseif( $sendtype == "mim" ){
		$total_sms_needed = 1;
	}
	
	$this_batch_required_credit = $total_sms_needed * count($mobile_arr);
	
	//echo "is_unlimited_quota: $is_unlimited_quota | current_quot_left: $current_quot_left | ReservedQuota: $ReservedQuota | total_sms_needed: $total_sms_needed | this_batch_required_credit: $this_batch_required_credit";
	//die;
	
	if( ( $is_unlimited_quota == 0 && $current_quot_left >= ( $this_batch_required_credit + $ReservedQuota ) ) || $is_unlimited_quota == 1 ){
		
		//saved file 1st
		$uploaded_files_url = upload_files( $files, $mim_file_type );
				
		if( $uploaded_files_url['status'] == "1" ){
			$file_location = $uploaded_files_url['file_location'];
		}else{
			$file_location = "";
		}
		
		if( ( $files && $file_location ) || ( !$files ) ){
			
			for($a=0; $a<count($mobile_arr); $a++) {
				$tmp_arr = explode("(", $mobile_arr[$a]);
				$mobile_numb = trim($tmp_arr[0]);		

				if(strlen($mobile_numb) != 0) {
					if(preg_match($pattern, $mobile_numb)) {
						$match = checkUnsubMobile($mobile_numb);
						if($match){
							$error++;
							if($unsub != "") {
								$unsub .= ", ".$mobile_numb;
							} else {
								$unsub = $mobile_numb;
							}
							continue;
						}
						
						$mobile_numb_check = validateMno($mobile_numb);				
					
						if( $mobile_numb_check != "-1" ){
							
							$mobile_numb = $mobile_numb_check;//replace
							
							$t = getSQLresult($dbconn, "select nextval('message_trackid') as trackid");
							$trackid = $_SESSION['server_prefix'].date('His').$t[0]['trackid'];

							//Check Quota
							$check_quota_type = checkQuotaType($_SESSION['userid'], $dbconn);
							$check_unlimited_quota = checkQuotaUnlimited($_SESSION['userid'], $dbconn);
							$check_quota = 0;
							$ReservedQuota = checkReservedQuota( $_SESSION['userid'],$dbconn );			
							$can_send = "no";
							//echo "check_unlimited_quota: $check_unlimited_quota | ReservedQuota: $ReservedQuota";
							//die;
							
							if($check_unlimited_quota != 1) {//not is unlimited
								$check_quota = checkQuota($_SESSION['userid'], $dbconn);
								//$total_sms_needed = getSMSNeeded($sms_text);

								if( $sendtype == "sms" ){
									$total_sms_needed = getSMSNeeded($sms_text);	
								}elseif( $sendtype == "sms_mim" ){
									$total_sms_needed = getSMSNeeded($sms_text) + 1;	
								}elseif( $sendtype == "mim" ){
									$total_sms_needed = 1;
								}
								
								$new_quota = $check_quota - $total_sms_needed;	
								//error_log("NewQuota: $new_quota");			
								
								$total_credit_required = $total_sms_needed + $ReservedQuota;
								
								if( $check_quota >= $total_credit_required ){//enough quota for current looping contact total sms required
									
									$sql = "update quota_mnt set quota_left = quota_left - $total_sms_needed where userid = '".$_SESSION['userid']."'";
									//error_log("Schedule SQL: $sql");
									$result = doSQLcmd($dbconn,$sql);
									if( !$result ){
										//error_log($sql . ' -- ' .pg_last_error($dbconn));
									}else{
										
										$can_send = "yes";
									}
									
								}else{
									
									$quota_msg = $sendSMS_msg1; //You don't have enough quota to send sms
									$error++;
									
								}
								
								//echo "current quota: $check_quota | required credit: $new_quota | total_sms_needed: $total_sms_needed";
								//die;
								
								/*
								if( ( $check_quota == 0 ) || ( $new_quota < 0 ) ){							
									$quota_msg = $sendSMS_msg1; //You don't have enough quota to send sms
									$error++;
								}else {								
									$sql = "update quota_mnt set quota_left='".$new_quota."' where userid = '".$_SESSION['userid']."'";
									//error_log("Schedule SQL: $sql");
									$result = doSQLcmd($dbconn,$sql);
									if( !$result ){
										//error_log($sql . ' -- ' .pg_last_error($dbconn));
									}
								}
								*/
								
							}else{
								//unlimited quota, just send
								$can_send = "yes";
							}

							//if( $check_quota > 0 || $check_unlimited_quota == 1){
							if( $can_send == "yes" ){
								
								$sms_text_escape = pg_escape_literal($sms_text);
								//$sms_text_insert = preg_replace('/\n/','\r\n',$sms_text_escape);
								//$sms_text_insert = $sms_text_escape;
								// $sms_text_insert = preg_replace('/\n/','\r\n',$sms_text_escape);
								$sms_text_insert = $sms_text_escape;
								
								$scheduled_id = getSequenceID($dbconn,'scheduled_sms_scheduled_id_seq');
								$sqlcmd = "insert into scheduled_sms (scheduled_id,trackid,department,mobile_numb,message,priority_status,character_set,sent_by,scheduled_time,created_by,modem_label, campaign_id, template_id, mim_tpl_id, tpl_params_value, bot_id, file_location, send_mode ) 
											values ('".pg_escape_string($scheduled_id)."','"
													  .pg_escape_string($trackid). "','"
													  .pg_escape_string($department). "','"
													  .pg_escape_string($mobile_numb). "',{$sms_text_insert},'"
													  .pg_escape_string($priority_mode). "','"
													  .pg_escape_string($mode)."','"
													  .pg_escape_string($msg_from)."',to_timestamp('"
													  .pg_escape_string($scheduled_time)."','DD-MM-YYYY HH24:MI'),'"
													  .pg_escape_string($userid)."','".$label."', '".$campaign_id."', '".$tpl_id."', '".$mim_tpl_id."', '".$ParamValueOnly."', '".dbSafe($bot_id)."', '$file_location', '$sendtype' );";
													  
								$result = pg_query($dbconn, $sqlcmd);
								
								if(!$result) {
									$error++;
									//error_log($sqlcmd . ' -- ' . pg_last_error($dbconn));
								} else {
									if(pg_affected_rows($result) > 0) {
										$queue_sms++;
									}
								}
								
							}
							
						}else{
							$error++;
							if($invalid != "") {
								$invalid .= ", ".$mobile_numb;
							} else {
								$invalid = $mobile_numb;
							}
							continue;					
						}
						
					}
				}
			}
			
			$val = array();
			if($error > 0) {
				if($invalid != ""){
					$invalid_msg = "Invalid Moblie Number Found:<br>".$invalid;
					$quota_msg .= "<br>".$invalid_msg."<br>";
				}
				if($unsub != ""){
					 $unsub_msg = "Unsubscribe Moblie Number Found:<br>".$unsub;
					 $quota_msg .="<br>".$unsub_msg."<br>";
				}
				$val['output'] = $error. $sendSMS_msg2.(!empty($quota_msg)?"<br>".$quota_msg:"");
				$val['error'] = "1";
				$error = 0;
			} else {
				$msgstr2 = GetLanguage("send_sms",$lang);
				
				if( $sendtype == "sms" ){
					$val['output'] = $queue_sms." ".$msgstr2->alert_8;
				}elseif( $sendtype == "sms_mim" ){
					$val['output'] = $queue_sms." ".$msgstr2->alert_12;
				}elseif( $sendtype == "mim" ){
					$val['output'] = $queue_sms." ".$msgstr2->alert_13;
				}
		
				$val['error'] = "0";
			}
		
		}else{
			
			$val['output'] = "File upload failed.";
			$val['error'] = "1";
		}
			
	}else{
		
		$val['output'] = "You dont have enough quota to proceed. Total credit required $this_batch_required_credit ( $total_sms_needed each receiptient ), current quota left: $current_quot_left";
		$val['error'] = "1";
	}
	
	//update campaign as used
	$sql0 = "update campaign_mgnt set used_flag = '1' where campaign_id = '$campaign_id'";
	$result0 = pg_query($dbconn, $sql0);
	
	insertAuditTrail( "Send Message By Manual(Scheduling SMS)" );

	echo json_encode($val);
}

function listContacts($userid, $ctype)
{
	global $dbconn, $lang;
	$result_array = array();

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$listContacts_msg1 = (string)$msgstr->listContacts_msg1;
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "select contact_id, contact_name, mobile_numb, email from address_book where created_by='".dbSafe($userid)."' and access_type='0' order by contact_name";
	$result = pg_query($dbconn, $sqlcmd);
	
	//echo $sqlcmd;
	//die;
	
	if(!$result) {
		echo $db_err. " (" .$sqlcmd. ") -- " .pg_last_error($dbconn);
	} else {
		
		for ($i=1; $row = pg_fetch_array($result); $i++){
			
			$checkbok = '<input type="checkbox" name="selected" value="'.$row['mobile_numb'].'('.htmlspecialchars($row['contact_name']).')">';
			if($ctype == 'email'){
				error_log("name:".$row['contact_name']." email:".$row['email']);
				if(trim($row['email']) != ''){
					$checkbok = '<input type="checkbox" name="selected" value="'.$row['email'].'('.htmlspecialchars($row['contact_name']).')">';
				} else {
					$checkbok = '<input type="checkbox" name="selected" disabled>';
				}
				
			}
			
			array_push($result_array,Array(
				$i,
				htmlspecialchars($row['contact_name']),
				htmlspecialchars($row['mobile_numb']),
				htmlspecialchars($row['email']),
				$checkbok
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function listGlobalContacts($userid, $department,$ctype,$RadioType)
{
	global $dbconn, $lang;
	$result_array = array();

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$listGlobalContacts_msg1 = (string)$msgstr->listGlobalContacts_msg1;
	$db_err = (string)$msgstr->db_err;

	$list_cond = "";
	$select_cond = "";
	if(isUserAdmin($userid)) {
		$list_cond = "where access_type = '1'";
		$select_cond = ",department_list.department as department from address_book left outer join department_list on (address_book.department = department_list.department_id)";
	} else {
		$list_cond = "where address_book.department IN ('".dbSafe($department)."','0') and access_type = '1'";
		$select_cond = ",department_list.department as department from address_book left outer join department_list on (address_book.department = department_list.department_id)";
	}

	$sqlcmd = " select contact_id, contact_name, mobile_numb, email" .$select_cond. " " .$list_cond. " order by contact_name";
	$result = pg_query($dbconn, $sqlcmd);
	
	if(!$result) {
		echo $db_err. " (" .$sqlcmd. ") -- " .pg_last_error($dbconn);
	} else {
		for ($i=1; $row = pg_fetch_array($result); $i++){
			
			if( strlen(trim($row['email'])) > 0 ){
				$email_str = "|" . $row['email'] . "(" . htmlspecialchars($row['contact_name']) . ")";
			}else{
				$email_str = "";
			}
			
			$checkbox = '<input type="checkbox" name="selected" value="'.$row['mobile_numb'] . $email_str.'">';
			if($ctype == 'email'){
				if(trim($row['email']) != ''){
					$checkbox = '<input type="checkbox" name="selected" value="'.$row['mobile_numb'] . $email_str.'">';
				} else {
					
					if( $RadioType == "EmailOnly" ){
						$checkbox = "";
					}else{
						//$checkbox = '<input type="checkbox" name="selected" disabled>';
						$checkbox = '<input type="checkbox" name="selected">';
					}
					
				}
			}
		
			array_push($result_array,Array(
				$i,
				htmlspecialchars($row['contact_name']),
				htmlspecialchars($row['mobile_numb']),
				htmlspecialchars($row['email']),
				(empty($row['department'])?'All Departments':htmlspecialchars($row['department'])),
				$checkbox
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}
function listGroup($userid,$ctype)
{
	global $dbconn, $lang;
	$result_array = array();

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$listGroup_msg1 = (string)$msgstr->listGroup_msg1;
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "select group_id, group_name from address_group_main where created_by='".dbSafe($userid)."' and access_type='0' order by group_name";
	$result = pg_query($dbconn, $sqlcmd);
	if(!$result) {
		echo $db_err. " (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn));
	} else {
		for ($i=1; $row = pg_fetch_array($result); $i++){
			$tmp_arr = array();
			$tmp_arr2 = array();

			$getsql = "select contact_name, mobile_numb,email from address_book where group_string like '%".dbSafe($row['group_id'])."%' and created_by='".dbSafe($userid)."' and access_type='0' order by contact_name";
			$get = pg_query($dbconn, $getsql);

			if(!is_string($get))
			{
				for ($j=0; $row2 = pg_fetch_array($get); $j++){
					array_push($tmp_arr, trim($row2['contact_name']));
					array_push($tmp_arr2, trim($row2['mobile_numb']).'('.$row2['contact_name'].')');
				}
			}

			$list = implode(", ", $tmp_arr);
			$list2 = implode(",", $tmp_arr2);
			if($list == "") { $list = "------"; }

			array_push($result_array,Array(
				$i,
				htmlspecialchars($row['group_name']),
				htmlspecialchars($list),
				'<input type="checkbox" name="selected" value="'.htmlspecialchars($list2).'">'
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function listGlobalGroup($userid, $department,$ctype,$sendtype)
{
	global $dbconn, $lang;
	$result_array = array();

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$listGlobalGroup_msg1 = (string)$msgstr->listGlobalGroup_msg1;
	$db_err = (string)$msgstr->db_err;

	$list_cond = "";
	$select_cond = "";
	if(isUserAdmin($userid)) {
		$list_cond = "where access_type = '1'";
		$select_cond = ", department_list.department as department from address_group_main left outer join department_list on (address_group_main.department = department_list.department_id)";
	} else {
		$list_cond = "where address_group_main.department IN ('".pg_escape_string($department)."','0') and access_type='1'";
		$select_cond = ", department_list.department as department from address_group_main left outer join department_list on (address_group_main.department = department_list.department_id)";
	}

	$sqlcmd = "select group_id, group_name" .$select_cond. " " .$list_cond. " order by group_name";
	$result = pg_query($dbconn, $sqlcmd);
	if(!$result)
	{
		echo $db_err. " (" .$sqlcmd. ") -- " .pg_last_error($dbconn);
	} else {
		for ($i=1; $row = pg_fetch_array($result); $i++){
			$tmp_arr = array();
			$tmp_arr2 = array();
			$tmp_arr3 = array();
			
			if(isUserAdmin($userid)) {
				$getsql = "select contact_name, mobile_numb, email from address_book where group_string like '%".pg_escape_string($row['group_id'])."%'  and access_type='1' order by contact_name";
			} else {
				$getsql = "select contact_name, mobile_numb, email from address_book where group_string like '%".pg_escape_string($row['group_id'])."%' and (department='".pg_escape_string($department)."' or department='0') and access_type='1' order by contact_name";
			}
			$get = pg_query($dbconn, $getsql);

			if(!is_string($get))
			{
				for ($j=0; $row2 = pg_fetch_array($get); $j++){
					array_push($tmp_arr, trim($row2['contact_name']));
					if($sendtype == 'both'){
						
						array_push($tmp_arr2, trim($row2['mobile_numb']).'('.$row2['contact_name'].')');
						if(trim($row2['email']) != ''){ array_push($tmp_arr3, trim($row2['email']).'('.$row2['contact_name'].')');}
						
					} else {
						if($ctype == 'email'){
							if(trim($row2['email']) != ''){ array_push($tmp_arr2, trim($row2['email']).'('.$row2['contact_name'].')');}
						} else {
							array_push($tmp_arr2, trim($row2['mobile_numb']).'('.$row2['contact_name'].')');
						}
					}
				}
			}

			$list = implode(", ", $tmp_arr);
			$list2 = implode(",", $tmp_arr2);
			
			if($sendtype == 'both'){
				$list3 = implode(",", $tmp_arr3);
				$list2 = $list2."|".$list3;
			}
			
			
			
			if($list == "") { $list = "------"; }

			array_push($result_array,Array(
				$i,
				htmlspecialchars($row['group_name']),
				htmlspecialchars($list),
				(empty($row['department'])?'All Departments':htmlspecialchars($row['department'])),
				'<input type="checkbox" name="selected" value="'.htmlspecialchars($list2).'">'
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function listTemplate($userid)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "select template_id, template_text, template_name from message_template where created_by='".pg_escape_string($userid)."' and access_type='0' order by template_text";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(empty($row)) {
		$row[0]['template_id'] = "0";
		$row[0]['template_text'] = trim($msgstr->msg_tmp);
	}
	
	echo json_encode($row);
}

function listGlobalTemplate($userid, $department)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$db_err = (string)$msgstr->db_err;

	if(isUserAdmin($userid)) {
		$sqlcmd = "select template_id, template_text, template_name from message_template where access_type='1' order by template_text";
	} else {
		$sqlcmd = "select template_id, template_text, template_name from message_template where (department='".pg_escape_string($department)."' or department = '0') and access_type = '1' order by template_text";
	}

	$row = getSQLresult($dbconn, $sqlcmd);
	
	if(empty($row)) {
		$row[0]['template_id'] = "0";
		$row[0]['template_text'] = trim($msgstr->msg_tmp);
	}
	
	echo json_encode($row);
}

function listMIMTemplate($userid)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$db_err = (string)$msgstr->db_err;
	
	/*
	if(isUserAdmin($userid)){
		$sqlcmd = "select template_id, template_text, mim_tpl_id, template_name from message_template where access_type='2' order by template_text";
	}else{
		$sqlcmd = "select template_id, template_text, mim_tpl_id, template_name from message_template where user_id in ( select id from user_list where userid = '$userid' ) and access_type='2' order by template_text";
	}
	*/
	
	if(isUserAdmin($userid)){
		$sqlcmd = "select template_name,mim_tpl_id,template_id, template_text, department_list.department as department, department_id from message_template left outer join department_list on (message_template.department = department_list.department_id) 
					where message_template.access_type='2' and message_template.user_id is not null order by message_template.template_text";
	}else{
		$department = getUserDepartment($userid);
		$sqlcmd = "select template_name,mim_tpl_id, template_id, template_text, department_list.department as department, department_id from message_template left outer join department_list on (message_template.department = department_list.department_id) 
					where message_template.access_type='2'  and message_template.user_id in ( select id from user_list where userid = '".$_SESSION["userid"]."') order by message_template.template_text";
	}
	
	//echo $sqlcmd;
	//die;
	
	$row = getSQLresult($dbconn, $sqlcmd);

	if(empty($row)) {
		$row[0]['template_id'] = "0";
		$row[0]['template_text'] = trim($msgstr->msg_tmp);
	}
	
	echo json_encode($row);
}

function listGlobalMIMTemplate($userid)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$db_err = (string)$msgstr->db_err;
	
	/*
	if(isUserAdmin($userid)){
		$sqlcmd = "select template_id, template_text, mim_tpl_id, template_name from message_template where access_type='2' order by template_text";
	}else{
		$sqlcmd = "select template_id, template_text, mim_tpl_id, template_name from message_template where department='".pg_escape_string($_SESSION['department'])."' and access_type='2' order by template_text";
	}
	*/
	
	if(isUserAdmin($userid)){
		
		$sqlcmd = "select template_name, message_template.created_by,message_template.mim_tpl_id,message_template.template_id, message_template.template_text, department_list.department as department, department_id from message_template left outer join department_list on (message_template.department = department_list.department_id) 
					where access_type='2' order by template_text";
	}else{
		$department = getUserDepartment($userid);
		$sqlcmd = "select template_name, message_template.created_by,message_template.mim_tpl_id, message_template.template_id, message_template.template_text, department_list.department as department, department_id from message_template left outer join department_list on (message_template.department = department_list.department_id) 
					where message_template.department='".dbSafe($department)."' and access_type='2' order by template_text";
	}
	
	$row = getSQLresult($dbconn, $sqlcmd);

	if(empty($row)) {
		$row[0]['template_id'] = "0";
		$row[0]['template_text'] = trim($msgstr->msg_tmp);
	}

	echo json_encode($row);
}

function getUnsubList()
{
	global $dbconn;

	$sqlcmd = "select mobile_numb from unsubscribe_list order by unsubscribe_id";
	$row = getSQLresult($dbconn, $sqlcmd);
	
	if(is_string($row)) {
		error_log("Database Error (".$sqlcmd.") -- ".pg_last_error($dbconn));
		return;
	} else {
		return $row;
	}
}

function matchNumb($target, $num)
{
	if( strlen($target) == strlen($num) ){
		if( $target == $num ){
			return 1;
		}
	} else if ( strlen($num) < strlen($target) ){
		$pos = strlen($target) - strlen($num);
		$tmp = substr($target, $pos, strlen($num));

		if( $num ==  $tmp ){
			return 1;
		}
	} else if ( strlen($num) > strlen($target) ){
		$pos = strlen($num) - strlen($target);
		$tmp = substr($num, $pos, strlen($target));

		if( $target == $tmp ){
			return 1;
		}
	}
	return 0;
}

function checkUnsubMobile($mobile_numb)
{
	$unsub_list = getUnsubList();
	$match = 0;
	
	if( is_array( $unsub_list ) ){
		if( count($unsub_list) >= 1 && isset($unsub_list[0]['mobile_numb'])){
			foreach($unsub_list as $key){
				$target = $key['mobile_numb'];
				$match = matchNumb($target, $mobile_numb);
				if($match == 1) {
					return 1;
				}
			}
		}
	}
	return 0;
}

function checkQuotaType($userid,$conn)
{
	$sqlcmd = "select topup_frequency from quota_mnt where lower(userid)='".pg_escape_string($userid)."'";	
	$result = pg_query($conn,$sqlcmd);
	$arr = pg_fetch_all($result);
	return $arr[0]['topup_frequency'];
}

function checkQuotaUnlimited($userid, $dbconn)
{
	$sqlcmd = "select unlimited_quota from quota_mnt where lower(userid)='".pg_escape_string($userid)."'";
	$result = pg_query($dbconn, $sqlcmd);
	$arr = pg_fetch_all($result);
	return $arr[0]['unlimited_quota'];
}

function checkReservedQuota( $userid,$conn ){
	
	#get reserved_quota
	$reserved_quota = 0;
	$sqlcmd = " select sum(reserved_quota) as reserved_quota from broadcast_sms_file where upload_by = '".dbSafe($userid)."'";
	$result = pg_query($conn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$reserved_quota = $row['reserved_quota'];
	}
	
	return $reserved_quota;
}

function checkQuota($userid,$conn)
{
	
	$sqlcmd = "select quota_left from quota_mnt where lower(userid)='".pg_escape_string($userid)."'";
	error_log("CheckQuota: $sqlcmd");
	$result = pg_query($conn,$sqlcmd);
	$arr = pg_fetch_all($result);
	return $arr[0]['quota_left'];

}

function getLabel($department)
{
	global $dbconn;
	$sqlcmd = "select modem_label from modem_dept where dept='".pg_escape_string($department)."'";
	$result = pg_query($dbconn,$sqlcmd);
	$arr = pg_fetch_all($result);
	return trim($arr[0]['modem_label']);
}

function validateMno($mno){
	
	$mno = trim($mno);
	$mno_len = strlen($mno);
	
	if( $mno == "+60162338320" || $mno == "60162338320" ){
		return $mno;
	}
	
	if (!preg_match('/^[0-9+]*$/', $mno)){//only digits
		return "-1";
	}

	if(!($mno_len == 11 || $mno_len == 10 || $mno_len == 8)){
		return "-1";
	}
	
	if($mno_len == 11){
		
		if( substr($mno,0,4) == "+658" || substr($mno,0,4) == "+659" ){
			return $mno;
		}else{
			return "-1";
		}
		
	}
	
	if($mno_len == 10){
		
		if( substr($mno,0,3) == "658" || substr($mno,0,3) == "659" ){
			return "+" . $mno;
		}else{
			return "-1";
		}
		
	}
	
	if($mno_len == 8){
		
		if( substr($mno,0,1) == "9" || substr($mno,0,1) == "8" ){
			return "+" . $mno;
		}else{
			return "-1";
		}
		
		/*
		if(stripos($mno, "9") == 0 || stripos($mno, "8") == 0){
			return "+65" . $mno;
		}else{
			return "-1";
		}
		*/
		
	}

}

function listTplParamElement( $tpl_id ){
	
	global $dbconn;
	
	$sqlcmd = "select template_text from message_template where template_id = '$tpl_id'";
	
	$row = getSQLresult($dbconn, $sqlcmd);
	$template_text = $row[0]['template_text'];
	$break = false;
	
	for ($x = 1; $x <= 20; $x++) {
		
		$find_this = "data" . $x;
		
		if (strpos( $template_text, $find_this ) !== false) {
			
			//found param, continue
			$returns['element'] .= '<textarea name = "'.$find_this.'" id = "'.$find_this.'" class = "form-control input-sm" row = "5">'.$find_this.'</textarea><br/>';
			
		}else{
			$break = true;
		}
		
		if( $break ){
			break;
		}
		
	}
	
	$returns['total'] = $x-1;
	echo json_encode($returns);
}

function getCampaign( $campaign_id ){
	
	global $dbconn;
	
	$sqlcmd = "select * from campaign_mgnt where campaign_id = '$campaign_id'";
	
	$row = getSQLresult($dbconn, $sqlcmd);
	$returns = $row[0];
	
	return $returns;
	
}

function getMessage($userid,$deptid,$msgid) {
	global $dbconn;
	
	$sqlcmd = "SELECT mobile_numb, message FROM outgoing_logs WHERE outgoing_id = '".pg_escape_string($msgid)."'";
	$row = getSQLresult($dbconn,$sqlcmd);
	echo json_encode($row[0]);
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
