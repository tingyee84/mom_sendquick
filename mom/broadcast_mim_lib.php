<?php
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 0);
 require_once('lib/commonFunc.php');
	
	if( !isset($_POST['bc_date']) ){
		$_POST['bc_date'] = date("d-m-Y");
	}
	
	if( !isset($_POST['bc_hour']) ){
		$_POST['bc_hour'] = date("H");
	}
	
	if( !isset($_POST['bc_min']) ){
		$_POST['bc_min'] = date("i");
	}
	
 $mode = filter_input(INPUT_POST,'mode');
 $userid = strtolower($_SESSION['userid']);
 $inc_id = filter_input(INPUT_POST,'bc_rcpt');
 $message = filter_input(INPUT_POST,'bc_text');
 $dept = (isUserAdmin($userid) ? '0' : getUserDepartment($userid));
 $s_mode = 'text';
 $bc_sch = $_POST['bc_date']." ".$_POST['bc_hour'].":".$_POST['bc_min'];
 $mime_image = filter_input(INPUT_POST,'mime_image');

 switch ($mode) {
	case "list":
		listMIM($userid);
		break;
  case "sendBC":
    sendBroadcast($userid, $dept, $message, $s_mode, $inc_id, $mime_image);
    break;
  case "temp":
		listTemplate($userid, $dept);
		break;
  case "sendScheduledBC":
    sendScheduledBC($userid, $dept, $message, $s_mode, $inc_id, $bc_sch, $mime_image);
    break;
  case "uploadImage":
    uploadImage($_FILES["img_file"]["name"]);
    break;
    default:
        die('Invalid Command');
}

function listMIM($userid){
  global $dbconn, $lang;
	$result_array = array();

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$listGlobalContacts_msg1 = (string)$msgstr->listGlobalContacts_msg1;
	$db_err = (string)$msgstr->db_err;

  if(isUserAdmin($userid)){
	  $sqlcmd = "SELECT adbk.inc_id AS inc_id, contact_name, ic.channel AS channel FROM address_book adbk LEFT OUTER JOIN incoming_contact ic ON(adbk.inc_id = ic.inc_id)
    WHERE adbk.inc_id IS NOT NULL ORDER BY contact_name";
  } else{
    $cmd_botroute = "SELECT bot_string FROM department_list dl LEFT OUTER JOIN user_list ul ON(dl.department_id = ul.department) WHERE lower(userid) = lower('$userid')";
    $botroute_res = getSQLresult($dbconn,$cmd_botroute);
    $botroute_arr = explode(",",$botroute_res[0]['bot_string']);
    $botroute_id = implode("','",$botroute_arr);
    $cmd_inc_id = "SELECT inc_id FROM incoming_contact WHERE bot_route_id IN('$botroute_id')";
    $result2 = getSQLresult($dbconn,$cmd_inc_id);
    for($h=0; $h<count($result2); $h++){
      if($h==0){
        $result3 = $result2[$h]['inc_id'];
      } else{
        $result3 .= ",".$result2[$h]['inc_id'];
      }
    }
    $inc_id_tmp = explode(",",$result3);
    $inc_id_str = implode("','",$inc_id_tmp); 
    $sqlcmd = "SELECT adbk.inc_id AS inc_id, contact_name, ic.channel AS channel FROM address_book adbk LEFT OUTER JOIN incoming_contact ic ON(adbk.inc_id = ic.inc_id)
    WHERE adbk.inc_id IN('$inc_id_str') ORDER BY contact_name";
  }
	$result = pg_query($dbconn, $sqlcmd);

	if(!$result) {
		echo $db_err. " (" .$sqlcmd. ") -- " .pg_last_error($dbconn);
	} else {
		for ($i=1; $row = pg_fetch_array($result); $i++){
      $img = '';
  		if($row['channel'] == 'FACEBOOK'){
  			$img = '<img src="images/icons/icon_messenger@2x.png">';
  		} else if($row['channel'] == 'TELEGRAM'){
  			$img = '<img src="images/icons/icon_telegram@2x.png">';
  		}else if($row['channel'] == 'SQOOPE'){
  			$img = '<img src="images/icons/icon_sqoope@2x.png">';
  		}else if($row['channel'] == 'LINE'){
  			$img = '<img src="images/icons/icon_line@2x.png">';
			}else if($row['channel'] == 'LINE NOTIFY'){
    		$img = '<img src="images/icons/icon_line@2x.png">';
  		}else if($row['channel'] == 'LIVECHAT'){
  			$img = '<img src="images/icons/icon_livechat@2x.png">';
  		}else if($row['channel'] == 'SLACK'){
  			$img = '<img src="images/icons/icon_slack@2x.png">';
  		}else if($row['channel'] == 'MICROSOFT TEAMS'){
  			$img = '<img src="images/icons/icon_teams@2x.png">';
  		}else if($row['channel'] == 'VIBER'){
  			$img = '<img src="images/icons/icon_viber@2x.png">';
  		}else if($row['channel'] == 'WECHAT'){
  			$img = '<img src="images/icons/icon_wechat@2x.png">';
  		}else if($row['channel'] == 'WEBEX'){
  			$img = '<img src="images/icons/icon_webex.png">';
			}else if($row['channel'] == 'WHATSAPPDC'){
  			$img = '<img src="images/icons/icon_whatsapp.png" width="24px" height="24px">';
  		} else{
  			$img = '<img src="images/icons/icon_text@2x.png">';
  		}

			array_push($result_array,Array(
				$i,
				htmlspecialchars($row['contact_name']),
				$img,
				'<input type="checkbox" name="selected" value="'.$row['contact_name'].'" data-channel="'.$row['channel'].'" data-id="'.$row['inc_id'].'">'
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function sendBroadcast($userid, $dept, $message, $s_mode, $inc_id, $mime_image){
  global $dbconn;

  if(empty($message)){
    $message = $mime_image;
  }

  error_log("MIME IMAGE::".$mime_image);

  $priority = 5;
  $message = urldecode($message);
  $inc_id_arr = explode(",",trim($inc_id));

  $sent_status = "0";
  $output = '';

  $b = count($inc_id_arr);

  $bc_id = "BC".time().sprintf("%05d",getSequence($dbconn,'broadcast_mim_log_bc_id_seq'));
  $cmd2 = "INSERT INTO broadcast_mim_log (bc_id, inc_id, created_by, created_dtm, total_rcpt)
      VALUES ('$bc_id', '$inc_id', '$userid', 'now()', '$b')";
  $res2 = doSQLcmd($dbconn,$cmd2);

  if($res2 == 1){
    for($a=0; $a<count($inc_id_arr); $a++){
      $cmd = "SELECT channel, display_name, chat_activity_id FROM incoming_contact ic LEFT OUTER JOIN chat_activity ca ON(ic.inc_id = ca.inc_id) WHERE ic.inc_id = '$inc_id_arr[$a]'";
      $res = getSQLresult($dbconn,$cmd);
      $channel = $res[0]['channel'];
      $display_name = $res[0]['display_name'];
      $chat_activity_id = $res[0]['chat_activity_id'];

      $t = getSQLresult($dbconn, "select nextval('message_trackid') as trackid");
      $timenow = date('His');
      $trackid = $_SESSION['server_prefix'].$timenow.$t[0]['trackid'];

      $message_escape = pg_escape_literal($message);
      $message_insert = preg_replace('/\n/','<br>',$message_escape);

      if($channel == 'SMS' || $channel == 'SQOOPE'){
        $mobile = $res[0]['display_name'];
      } else {
        $mobile = '';
      }

      if ($channel == 'LIVECHAT') {
        // Get the active session id
        $cmd = "SELECT session_id FROM chat_activity WHERE chat_activity_id = '$chat_activity_id'";
        $res = getSQLresult($dbconn,$cmd);
        $session_id = $res[0]['session_id'];

        // Insert into Livechat log table
        $livechat_log_id = getSequenceID($dbconn,'livechat_log_livechat_log_id_seq');

        $sqlcmd = "INSERT INTO livechat_log (livechat_log_id, session_id, sent_by, message, created_dtm, inc_id, bc_id)
              VALUES ('".dbSafe($livechat_log_id)."','".dbSafe($session_id)."','".dbSafe($userid)."',{$message_insert},'now()', '$inc_id_arr[$a]', '$bc_id')";
        $row = doSQLcmd($dbconn, $sqlcmd);
        if($row == "0"){
          $sent_status = "1";
        }
      } else {
        $sent_status = sendSMS($userid, $dept, $mobile, $message_insert, $priority, $inc_id_arr[$a], $trackid, $s_mode, $channel, $message_escape, $bc_id);
      }

      if($sent_status == '0'){
        error_log("message sent successfully!");
        $cmd3 = "UPDATE chat_activity SET lastmsg_by = '$userid', lastmsg_dtm = 'now()' WHERE inc_id = '$inc_id_arr[$a]'";
        $res3 = doSQLcmd($dbconn,$cmd3);
        if($res3){
          error_log("chat activity for ".$inc_id_arr[$a]." updated!");
        }
      }

      $cmd4 = "SELECT COUNT(*) FROM chat_activity WHERE inc_id = '$inc_id_arr[$a]'";
      $res4 = getSQLresult($dbconn,$cmd4);
      if($res4[0]['count'] == 0){
        $chat_id = "AC".time().sprintf("%05d",getSequence($dbconn,'chat_activity_chat_activity_id_seq'));
        $cmd5 = "INSERT INTO chat_activity (chat_activity_id, inc_id, lastmsg_by, lastmsg_dtm, unread_flag, notification_flag)
    				VALUES ('$chat_id', '$inc_id_arr[$a]', '$userid', 'now()', 'false', 'false')";
        $res5 = doSQLcmd($dbconn,$cmd5);
      }
    }
  } else{
    $sent_status == "1";
    error_log("Database Error (" .$cmd2. ") -- ".pg_last_error($dbconn));
  }

  if($sent_status == "1"){
    $output = "Message(s) delivery failed.";
  } else{
    //$output = "<b>$message</b> delivered to <b>$a</b> recipient(s).";
    $output = "Message(s) delivered to <b>$a</b> recipient(s).";
  }

  $val = array();
  $val['error'] = $sent_status;
  $val['output'] = $output;

  echo json_encode($val);
}

function sendSMS($userid, $department, $mobile, $message_insert, $priority, $inc_id, $trackid, $mode, $channel, $message_escape, $bc_id)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$sendSMS_msg1 = (string)$msgstr->sendSMS_msg1;
	$sendSMS_msg2 = (string)$msgstr->sendSMS_msg2;

	$msg_from = $userid. " (" .$_SERVER['REMOTE_ADDR']. ")";

	$error = 0;
	$sent_sms = 0;
	$pattern = "/^\+?\d+$/";
	$curr_time = strftime("%Y%m%d%H%M%S", time());
	$quota_msg="";
	$unsub = "";
  $deptLabel = "";

  $row = '';
  $cmd = "SELECT department FROM department_list WHERE department_id = '$department'";
  $res = getSQLresult($dbconn,$cmd);
  $deptLabel = $res[0]['department'];
      if($channel == 'SMS'){
				//Check Quota
				$check_quota_type = checkQuotaType($_SESSION['userid'], $dbconn);
				$check_unlimited_quota = checkQuotaUnlimited($_SESSION['userid'], $dbconn);
				$check_quota = 0;

				if($check_unlimited_quota != 1) {
					$check_quota = checkQuota($_SESSION['userid'], $dbconn);
					if( $check_quota == 0 ){
				 		$quota_msg = $sendSMS_msg1;
				 		$error++;
					} else {
						$new_quota = $check_quota - 1;
						$sql = "UPDATE quota_mnt SET quota_left='$new_quota' WHERE userid='". $_SESSION['userid']."'";
						$result = doSQLcmd($dbconn,$sql);
			  			if(!$result){
							$error++;
            	 			error_log($sqlcmd . ' -- ' .pg_last_error($dbconn));
       					}
					}
				}

				if( $check_quota > 0 || $check_unlimited_quota == 1){
					$outgoing_id = getSequenceID($dbconn,'outgoing_logs_outgoing_id_seq');
					$sqlcmd = "INSERT INTO outgoing_logs (outgoing_id, priority, trackid, sent_by, department, mobile_numb, message, message_status, inc_id, bc_id)
								VALUES ('".dbSafe($outgoing_id)."','".dbSafe($priority)."','".dbSafe($trackid)."','".dbSafe($userid)."','".dbSafe($department)."','$mobile',
                {$message_insert},'Q', '$inc_id', '$bc_id')";
					$row = doSQLcmd($dbconn, $sqlcmd);
				} else {
					$row = 0;
					$error++;
				}
      } else {
        $outgoing_id = getSequenceID($dbconn,'outgoing_logs_outgoing_id_seq');
        $cmd2 = "INSERT INTO outgoing_logs (outgoing_id, priority, trackid, sent_by, department, message, message_status, inc_id, bc_id)
              VALUES ('".dbSafe($outgoing_id)."','".dbSafe($priority)."','".dbSafe($trackid)."','".dbSafe($userid)."','".dbSafe($department)."',{$message_insert},'Q', '$inc_id', '$bc_id')";
        $row = doSQLcmd($dbconn, $cmd2);
      }

				if($row > 0) {
					$tar_msg = $message_insert;
					$response = internal_post($mobile, $tar_msg, $mode, $priority, $msg_from, $trackid, $deptLabel, $inc_id, $message_escape);

					if($response == 1) {
						$updatesql = "UPDATE outgoing_logs SET message_status='P' WHERE trackid='".dbSafe($trackid)."'";
						$update = doSQLcmd($dbconn, $updatesql);
						$sent_sms++;
					} else {
						$updatesql = "UPDATE outgoing_logs SET message_status='F', completed_dtm='now()' WHERE trackid='".dbSafe($trackid)."'";
						$update = doSQLcmd($dbconn, $updatesql);
						$error++;
					}
				}

  $val = '';
	if($error > 0) {
		$val = "1";
	} else {
		$val = "0";
	}

	return $val;
}

function internal_post($mobile, $msg_content, $mode, $priority, $msg_from, $trackid, $label, $inc_id, $message_escape)
{
	global $dbconn;

	if( !isset($priority) ){
		$priority = "5";
	}

  $label = '';

  	$sqlcmd = "INSERT INTO webapp_sms (msgid, mobile_numb, msg_content, mode, priority, msg_from, msg_status, label, inc_id, raw_msg, send_template)
				VALUES ('$trackid', '$mobile', {$msg_content}, 'MSG', '$priority', '$msg_from', 'W','$label', '$inc_id', {$message_escape}, 1)";
	$result = pg_query($dbconn, $sqlcmd);

	if(!$result)
	{
		error_log("Database Error (" .$sqlcmd. ") -- ".pg_last_error($dbconn));
		return 0;
	} else {
		$no_of_affected = pg_affected_rows($result);
    error_log("no_of_affected:".$no_of_affected);
		return $no_of_affected;
	}
}

function checkQuotaType($userid,$conn)
{
	$sqlcmd = "SELECT topup_frequency FROM quota_mnt WHERE lower(userid)='".$userid."'";
	$result = pg_query($conn,$sqlcmd);
	$arr = pg_fetch_all($result);
	$res = $arr[0]['topup_frequency'];
	return $res;
}

function checkQuotaUnlimited($userid, $dbconn)
{
	$sqlcmd = "SELECT unlimited_quota FROM quota_mnt WHERE lower(userid)='".$userid."'";
	$result = pg_query($dbconn, $sqlcmd);
	$arr = pg_fetch_all($result);
	$res = $arr[0]['unlimited_quota'];
	return $res;
}

function checkQuota($userid,$conn)
{
	$sqlcmd = "SELECT quota_left FROM quota_mnt WHERE lower(userid)='".$userid."'";
	$result = pg_query($conn,$sqlcmd);
	$arr = pg_fetch_all($result);
	$res = $arr[0]['quota_left'];
	return $res;
}

function listTemplate($userid, $department)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$db_err = (string)$msgstr->db_err;

	if(isUserAdmin($userid)) {
		$sqlcmd = "SELECT template_id, template_text FROM message_template WHERE access_type='1' ORDER BY template_text";
	} else {
		$sqlcmd = "SELECT template_id, template_text FROM message_template WHERE (department='".pg_escape_string($department)."' or department = '0') and access_type = '1' order by template_text";
	}

	$row = getSQLresult($dbconn, $sqlcmd);

	if(empty($row)) {
		$row[0]['template_id'] = "0";
		$row[0]['template_text'] = trim($msgstr->msg_tmp);
	}

	echo json_encode($row);
}

function sendScheduledBC($userid, $dept, $message, $s_mode, $inc_id, $bc_sch, $mime_image){
  global $dbconn, $lang;

  if(empty($message)){
    $message = $mime_image;
  }

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$sendSMS_msg1 = (string)$msgstr->sendSMS_msg1;
	$sendScheduledSMS_msg1 = (string)$msgstr->sendScheduledSMS_msg1;

	$msg_from = $userid. " (" .$_SERVER['REMOTE_ADDR']. ")";

  $priority = 5;
  $message = urldecode($message);
  $inc_id_arr = explode(",",trim($inc_id));

  $sent_status = "0";
  $output = '';
  $deptLabel = "";
  $error = 0;
  $quota_msg="";
  $queue_sms = 0;

  $b = count($inc_id_arr);

  $bc_id = "BC".time().sprintf("%05d",getSequence($dbconn,'broadcast_mim_log_bc_id_seq'));
  $cmd2 = "INSERT INTO broadcast_mim_log (bc_id, inc_id, created_by, created_dtm, total_rcpt)
      VALUES ('$bc_id', '$inc_id', '$userid', 'now()', '$b')";
  $res2 = doSQLcmd($dbconn,$cmd2);

  if($res2 == 1){
    for($a=0; $a<count($inc_id_arr); $a++){
      $cmd = "SELECT channel, display_name, chat_activity_id FROM incoming_contact ic LEFT OUTER JOIN chat_activity ca ON(ic.inc_id = ca.inc_id)
              WHERE ic.inc_id = '$inc_id_arr[$a]'";
      $res = getSQLresult($dbconn,$cmd);
      $channel = $res[0]['channel'];
      $display_name = $res[0]['display_name'];
      $chat_activity_id = $res[0]['chat_activity_id'];

      $t = getSQLresult($dbconn, "select nextval('message_trackid') as trackid");
      $timenow = date('His');
      $trackid = $_SESSION['server_prefix'].$timenow.$t[0]['trackid'];

      $message_escape = pg_escape_literal($message);
      $message_insert = preg_replace('/\n/','<br>',$message_escape);

      if($channel == 'SMS' || $channel == 'SQOOPE'){
        $mobile = $res[0]['display_name'];
      } else {
        $mobile = '';
      }

      $row = '';
      $cmd3 = "SELECT department FROM department_list WHERE department_id = '$dept'";
      $res3 = getSQLresult($dbconn,$cmd3);
      $deptLabel = $res3[0]['department'];
      if($channel == 'SMS'){
        //check quota
        $check_quota_type = checkQuotaType($_SESSION['userid'], $dbconn);
				$check_unlimited_quota = checkQuotaUnlimited($_SESSION['userid'], $dbconn);
				$check_quota = 0;

				if($check_unlimited_quota != 1) {
					$check_quota = checkQuota($_SESSION['userid'], $dbconn);
					if( $check_quota == 0 ){
				 		$quota_msg = $sendSMS_msg1;
				 		$error++;
					} else {
						$new_quota = $check_quota - 1;
						$sql = "UPDATE quota_mnt SET quota_left='$new_quota' WHERE userid='". $_SESSION['userid']."'";
						$result = doSQLcmd($dbconn,$sql);
			  			if(!$result){
							$error++;
            	 			error_log($sql . ' -- ' .pg_last_error($dbconn));
       					}
					}
				}

        if( $check_quota > 0 || $check_unlimited_quota == 1){
          $scheduled_id = getSequenceID($dbconn,'scheduled_sms_scheduled_id_seq');
					$cmd4 = "INSERT INTO scheduled_sms (scheduled_id,trackid,department,mobile_numb,message,priority_status,character_set,sent_by,scheduled_time,
            created_by,inc_id,bc_id) VALUES ('".pg_escape_string($scheduled_id)."','".pg_escape_string($trackid). "','".pg_escape_string($dept). "','"
						.pg_escape_string($mobile). "',{$message_insert},'".pg_escape_string($priority). "','".pg_escape_string($s_mode)."','"
						.pg_escape_string($msg_from)."',to_timestamp('".pg_escape_string($bc_sch)."','DD-MM-YYYY HH24:MI'), '$userid', '$inc_id_arr[$a]', '$bc_id')";
					$res4 = pg_query($dbconn, $cmd4);

					if(!$res4) {
						$error++;
						error_log($cmd4 . ' -- ' . pg_last_error($dbconn));
					} else {
						if(pg_affected_rows($res4) > 0) {
							$queue_sms++;
						}
					}
        } else{
          $error++;
        }
      } else{
        $scheduled_id = getSequenceID($dbconn,'scheduled_sms_scheduled_id_seq');
        $cmd4 = "INSERT INTO scheduled_sms (scheduled_id,trackid,department,mobile_numb,message,priority_status,character_set,sent_by,scheduled_time,
          created_by,inc_id,bc_id) VALUES ('".pg_escape_string($scheduled_id)."','".pg_escape_string($trackid). "','".pg_escape_string($dept). "','"
          .pg_escape_string($mobile). "',{$message_insert},'".pg_escape_string($priority). "','".pg_escape_string($s_mode)."','"
          .pg_escape_string($msg_from)."',to_timestamp('".pg_escape_string($bc_sch)."','DD-MM-YYYY HH24:MI'), '$userid', '$inc_id_arr[$a]', '$bc_id')";
        $res4 = pg_query($dbconn, $cmd4);
      }

    }
  } else{
    $sent_status == "1";
    error_log("Database Error (" .$cmd2. ") -- ".pg_last_error($dbconn));
  }

  $val = array();
	if($error > 0) {
		$val['output'] = $error. $sendScheduledSMS_msg1.(!empty($quota_msg)?"<br>".$quota_msg:"");
		$val['error'] = "1";
	} else {
		$msgstr2 = GetLanguage("send_sms",$lang);
		$val['output'] = $a." ".$msgstr2->alert_8;
		$val['error'] = "0";
	}

	echo json_encode($val);
}

//image upload
function uploadImage($uploadname){
  global $webappdb;

  $data = array();
  $file_data = "";
  if(!empty($brochure)) {
    if(!empty($_FILES['img_file']['tmp_name'])) {
      $uploadname = preg_replace("/[^A-Za-z0-9-_\.]+/", "", $uploadname);
      $uploadfile = "../mim/images/".$uploadname;

      if(is_uploaded_file($_FILES["img_file"]["tmp_name"])) {
        if (!move_uploaded_file($_FILES["img_file"]["tmp_name"], $uploadfile)) {
          error_log("Moved failed - ".$_FILES["img_file"]["error"]);
        } else {
          $imagedata = file_get_contents($uploadfile);
          $file_data = base64_encode($imagedata);
        }
      }  else {
        error_log("Upload failed - ".$_FILES["img_file"]["error"]);
      }
    } else {
      $uploadname = "NA";
    }
  } else {
    $brochure = 0;
    //test upload without brochure check
    if(!empty($_FILES['img_file']['tmp_name'])) {
      $uploadname = preg_replace("/[^A-Za-z0-9-_\.]+/", "", $uploadname);
      $uploadfile = "../mim/images/".$uploadname;

      if(is_uploaded_file($_FILES["img_file"]["tmp_name"])) {
        if (!move_uploaded_file($_FILES["img_file"]["tmp_name"], $uploadfile)) {
          error_log("Moved failed - ".$_FILES["img_file"]["error"]);
        } else {
          error_log("works");
          $imagedata = file_get_contents($uploadfile);
          $file_data = base64_encode($imagedata);
        }
      }  else {
        error_log("Upload failed - ".$_FILES["img_file"]["error"]);
      }
    } else {
      $uploadname = "NA";
    }
    //test end
  }
  $data['imgpath'] = $uploadfile;
  echo json_encode($data);
}
?>
