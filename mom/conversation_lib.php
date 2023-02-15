<?php
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 0);
require('lib/db_spool.php');
require('lib/commonFunc.php');

$mode = @$_REQUEST['mode'];//@$_POST['mode'];
$userid = @$_SESSION['userid'];
$message = @$_REQUEST['message'];
$receiver = @$_REQUEST['receiver'];
$id = @$_REQUEST['id'];
$dept = (isUserAdmin($userid) ? '0' : getUserDepartment($userid));
$priority = @$_REQUEST['priority'];
$s_mode = @$_REQUEST['s_mode'];
$search_conv = @$_REQUEST['search_conv'];
$activity_id = @$_REQUEST['activity_id'];
$using_by = @$_REQUEST['using_by'];
$not_using_by = @$_REQUEST['not_using_by'];
$inc_id = @$_REQUEST['inc_id'];
$mime_image = @$_REQUEST['mime_image'];
$msgid = @$_REQUEST['msgid'];

switch ($mode) {
    case "user":
        convList($userid,$search_conv);
        break;
    case "add":
        $chat_activity_id = @$_REQUEST['chat_activity_id'];
        sendConversation($userid, $dept, $message, $priority, $s_mode, $chat_activity_id, $inc_id, $mime_image);
        break;
    case "list":
        convRoom($userid,$id);
        break;
    case "addAssign":
        $assign_to = $_REQUEST['asg_user'];
        $assign_msg = $_REQUEST['assign_msg'];
        addAssign($userid,$assign_to,$assign_msg,$activity_id);
        break;
    case "using":
        convUsingBy($activity_id,$using_by);
        break;
    case "not_using":
        convNotUsingBy($activity_id,$not_using_by);
        break;
    case "listAddr":
        listToAddressBook($inc_id);
        break;
    case "getNotiCount":
        getNotiCount();
        break;
    case "notification":
        getNotification();
        break;
    case "updateUnreadFlag":
        $chat_activity_id = $_REQUEST['chat_activity_id'];
        updateUnreadFlag($inc_id,$chat_activity_id);
        break;
    case "saveContact":
        $contact_name = $_REQUEST['contact'];
        $mobile = $_REQUEST['mobile'];
        $group = $_REQUEST['group'];
        saveToAddressBook($inc_id, $contact_name, $mobile, $group, $userid, $dept);
        break;
    case "replying_by":
        getReplyingBy($activity_id);
        break;
    case "getchatid":
        getChatID($inc_id);
        break;
    case "uploadImage":
        uploadImage($_FILES["img_file"]["name"]);
        break;
		case 'retry':
			retry($msgid);
			break;
    default:
        die("Invalid Command");
}

function retry($msgid) {
	global $dbconn, $spdbconn;
	if($msgid) {
		$sql = "UPDATE outgoing_logs SET message_status = 'P' WHERE msgid = '$msgid'";
		pg_query($dbconn, $sql);

		$sql = "DELETE FROM extern_app WHERE msgid = '$msgid'";
		pg_query($spdbconn, $sql);

		$sql = "UPDATE bot_message_status SET status = 'N', api_response = '', send_template = 1 WHERE msgid = '$msgid'";
		pg_query($spdbconn, $sql);
	}
}

function convList($userid,$search_conv){
  global $dbconn;
  $data = Array();

  if(isUserAdmin($userid)){
    $cmd_id = "SELECT ic.inc_id FROM incoming_contact ic LEFT OUTER JOIN chat_activity ca ON(ic.inc_id = ca.inc_id) ORDER BY ca.lastmsg_dtm DESC";
  } else{
    $cmd_botroute = "SELECT bot_string FROM department_list dl LEFT OUTER JOIN user_list ul ON(dl.department_id = ul.department) WHERE lower(userid) = lower('$userid')";
    $botroute_res = getSQLresult($dbconn,$cmd_botroute);
    $botroute_arr = explode(",",$botroute_res[0]['bot_string']);
    $botroute_id = implode("','",$botroute_arr);
    $cmd_id = "SELECT ic.inc_id FROM incoming_contact ic LEFT OUTER JOIN chat_activity ca ON(ic.inc_id = ca.inc_id) WHERE bot_route_id IN('$botroute_id') ORDER BY ca.lastmsg_dtm DESC";
  }
  $res_id = pg_query($dbconn,$cmd_id);
  while($row_id = pg_fetch_assoc($res_id)){
    $inc_id = $row_id['inc_id'];

    if(empty($search_conv)){
      $condition = "ORDER BY dtm";
    }else{
      //$condition = "WHERE '$search_conv' IN(display_name,channel,mobile,sender) OR lower(msg) LIKE lower('%$search_conv%') OR lower(channel) LIKE lower('%$search_conv%') ORDER BY dtm";
      $condition = "WHERE lower(msg) LIKE lower('%$search_conv%') OR lower(channel) LIKE lower('%$search_conv%') OR lower(display_name) LIKE lower('%$search_conv%') OR lower(mobile) LIKE lower('%$search_conv%') OR lower(sender) LIKE lower('%$search_conv%') ORDER BY dtm";
    }

    $cmd = "SELECT id,mobile,msg,sender,type,assign_to,inc.channel AS channel, inc.display_name AS display_name, ca.chat_activity_id AS chat_id,
            ca.locked_flag AS assign_flag, ca.editing_by AS using_by, ca.unread_flag AS unread_flag FROM(
              SELECT ol.inc_id AS id, ol.mobile_numb AS mobile, ol.message AS msg, sent_by AS sender, ol.created_dtm AS dtm, 'outgoing' AS type, 'null' AS assign_to
              FROM outgoing_logs ol WHERE ol.inc_id = '$inc_id'
              UNION ALL
              SELECT ci.inc_id AS id, ci.mobile_numb AS mobile, ci.unmatched_keyword AS msg, inc.display_name AS sender, ci.created_dtm AS dtm, 'incoming' AS type,
              'null' AS assign_to FROM common_inbox ci LEFT OUTER JOIN incoming_contact inc ON(ci.inc_id = inc.inc_id) WHERE ci.inc_id = '$inc_id'
              UNION ALL
              SELECT live.inc_id AS id, 'NULL' AS mobile, live.message AS msg, sent_by AS sender, live.created_dtm AS dtm, 'livechat' AS type, 'null' AS assign_to
              FROM livechat_log live WHERE live.inc_id = '$inc_id'
              UNION ALL
              SELECT ca.inc_id AS id,'NULL' AS mobile, cal.assigned_msg AS msg, ca.lastassigned_by AS sender, cal.assigned_dtm AS dtm, 'assign' AS type,
              cal.assigned_to AS assign_to FROM chat_activity ca LEFT OUTER JOIN chat_assignment_log cal ON(ca.chat_activity_id = cal.chat_activity_id)
              WHERE ca.inc_id = '$inc_id' AND cal.assigned_msg IS NOT NULL
            ) AS tbl
            LEFT OUTER JOIN incoming_contact inc ON(id = inc.inc_id) LEFT OUTER JOIN chat_activity ca ON(id = ca.inc_id) $condition DESC LIMIT 1";
    $res = pg_query($dbconn,$cmd);

  if ($res) {
		while($row = pg_fetch_assoc($res)){
      $assignee = '';
      $cmd2 = "SELECT DISTINCT array_agg(assigned_to) AS assignee FROM chat_assignment_log WHERE chat_activity_id = '".$row['chat_id']."'";
      $res2 = getSQLresult($dbconn,$cmd2);
      $assignee = $res2[0]['assignee'];
			$mobile = htmlspecialchars($row['mobile']);
      //$msg = utf8_decode($row['msg']);
      //check MIME type
      $cmd_mime = substr($row['msg'],0,10);
      $mime_flag = '';
      if($cmd_mime == 'MIME:IMAGE'){
        $msg = substr($row['msg'],11);
        $mime_flag = 1;
      } else{
        $msg = $row['msg'];
        $mime_flag = 0;
      }
      //MIME type end

      $cmd_count = "SELECT count(unread_flag) AS c_unread_flag from common_inbox WHERE unread_flag = TRUE AND inc_id = '$inc_id'";
      $count_res = getSQLresult($dbconn,$cmd_count);

      //$msg = $row['msg'];
			$data[] = array(
        'id' => $row['id'],
				'title' => htmlspecialchars(ltrim($row['display_name'])),
        'msg' => $msg,
        'channel' => $row['channel'],
        'sender' => $row['sender'],
        'chat_id' => $row['chat_id'],
        'type' => $row['type'],
        'assign_to' => $row['assign_to'],
        'assign_flag' => $row['assign_flag'],
        'assignee' => $assignee,
        'using_by' => $row['using_by'],
        'mime_flag' => $mime_flag,
        'unread_flag' => $row['unread_flag'],
        'c_unread_flag' => $count_res[0]['c_unread_flag']
			);
		}
		//echo json_encode($data);
	} else {
		echo "Database Error: ".pg_last_error();
	}
  }
echo json_encode($data);
}

function convRoom($userid,$id){
  global $dbconn;
	$data = Array();

  $cmd = "SELECT id,mobile,msg,dtm_string,type,sender,bc_id,inc.display_name AS display_name, inc.channel AS channel, message_status, msgid FROM(
          SELECT ol.inc_id AS id, ol.mobile_numb AS mobile, ol.message AS msg, to_char(ol.created_dtm, 'DD/MM/YYYY HH24:MI:SS') AS dtm_string, sent_by AS sender,
          ol.created_dtm AS dtm, 'outgoing' AS type, bc_id AS bc_id, ol.message_status as message_status, ol.msgid FROM outgoing_logs ol WHERE ol.inc_id = '$id'
          UNION ALL
          SELECT live.inc_id AS id, 'NULL' AS mobile, live.message AS msg, to_char(live.created_dtm, 'DD/MM/YYYY HH24:MI:SS') AS dtm_string,
          live.sent_by AS sender, live.created_dtm AS dtm, 'livechat' AS type, bc_id AS bc_id, NULL as message_status, NULL as msgid
          FROM livechat_log live LEFT OUTER JOIN incoming_contact inc ON(live.inc_id = inc.inc_id)
          WHERE live.inc_id = '$id' AND live.sent_by = 'useradmin'
          UNION ALL
          SELECT ci.inc_id AS id, ci.mobile_numb AS mobile, ci.unmatched_keyword AS msg, to_char(ci.created_dtm, 'DD/MM/YYYY HH24:MI:SS') AS dtm_string,
          inc.display_name AS sender, ci.created_dtm AS dtm, 'incoming' AS type, null AS bc_id, NULL as message_status, NULL as msgid
          FROM common_inbox ci LEFT OUTER JOIN incoming_contact inc ON(ci.inc_id = inc.inc_id)
          WHERE ci.inc_id = '$id'
          UNION ALL
          SELECT ca.inc_id AS id,'NULL' AS mobile, cal.assigned_msg AS msg,to_char(cal.assigned_dtm, 'DD/MM/YYYY HH24:MI:SS') AS dtm_string, lastassigned_by AS sender,
          cal.assigned_dtm AS dtm, 'assign' AS type, null AS bc_id, NULL as message_status, NULL as msgid FROM chat_activity ca LEFT OUTER JOIN chat_assignment_log cal ON(ca.chat_activity_id = cal.chat_activity_id)
          WHERE ca.inc_id = '$id' AND cal.assigned_msg IS NOT NULL) AS tbl
          LEFT OUTER JOIN incoming_contact inc ON(id = inc.inc_id) ORDER BY dtm";
	$res = pg_query($dbconn,$cmd);

	if ($res) {
    $cmd_last_sent_type = "SELECT id,type FROM(SELECT ol.inc_id AS id, ol.created_dtm AS dtm, 'outgoing' AS type FROM outgoing_logs ol WHERE ol.inc_id = '$id'
      UNION ALL
      SELECT ci.inc_id AS id, ci.created_dtm AS dtm, 'incoming' AS type FROM common_inbox ci LEFT OUTER JOIN incoming_contact inc ON(ci.inc_id = inc.inc_id)
      WHERE ci.inc_id = '$id' UNION ALL
      SELECT ca.inc_id AS id,cal.assigned_dtm AS dtm, 'assign' AS type FROM chat_activity ca LEFT OUTER JOIN chat_assignment_log cal
      ON(ca.chat_activity_id = cal.chat_activity_id) WHERE ca.inc_id = '$id' AND cal.assigned_msg IS NOT NULL) AS tbl LEFT OUTER JOIN incoming_contact inc ON(id = inc.inc_id)
      ORDER BY dtm DESC LIMIT 1";
    $last_sent_type = getSQLresult($dbconn,$cmd_last_sent_type);

    $cmd2 = "UPDATE chat_activity SET lastview_by = '$userid', lastview_dtm = 'now()' WHERE inc_id = '$id'";
    $res2 = doSQLcmd($dbconn,$cmd2);
    if($res2){
      //error_log("Last viewed by '$userid'");
    } else{
      //error_log("Database Error (" .$cmd2. ") -- ".pg_last_error($dbconn));
    }

    $add_flag = 0;
    $cmd3 = "SELECT * FROM address_book WHERE inc_id = '$id'";
    $res3 = getSQLresult($dbconn,$cmd3);
    if($res3){
      $add_flag = 1;
    }
		while($row = pg_fetch_assoc($res)) {
			//$msg = utf8_decode($row['msg']);
      //check MIME type
      $cmd_mime = substr($row['msg'],0,10);
      $mime_flag = '';
      if($cmd_mime == 'MIME:IMAGE'){
        $msg = substr($row['msg'],11);
        $mime_flag = 1;
      } else{
        $msg = $row['msg'];
        $mime_flag = 0;
      }
      //MIME type end
      //$msg = $row['msg'];
      $from = htmlspecialchars(ltrim($row['sender']));
			$data[] = array(
				'from' => $from,
        'user' => $userid,
				'msg' => $msg,
        'dtm' => $row['dtm_string'],
        'channel' => $row['channel'],
        'type' => $row['type'],
        'display_name' => $row['display_name'],
        'last_sent_type' => $last_sent_type[0]['type'],
        'mime_flag' => $mime_flag,
        'add_flag' =>$add_flag,
        'bc_id' => $row['bc_id'],
				'message_status' => $row['message_status'],
				'msgid' => $row['msgid'],
			);
		}
		echo json_encode($data);
	} else {
		echo "Database Error: ".pg_last_error();
	}
}

//send into conversation
function sendConversation($userid, $dept, $message, $priority, $s_mode, $chat_activity_id, $inc_id, $mime_image)
{
  global $dbconn;

  if(empty($message)){
    $message = $mime_image;
  }

  $cmd = "SELECT channel, display_name FROM incoming_contact WHERE inc_id = '$inc_id'";
  $res = getSQLresult($dbconn,$cmd);
  $priority = 5;
  $channel = $res[0]['channel'];
  $display_name = $res[0]['display_name'];

  $t = getSQLresult($dbconn, "select nextval('message_trackid') as trackid");
  $timenow = date('His');
  $trackid = $_SESSION['server_prefix'].$timenow.$t[0]['trackid'];

  $message_escape = pg_escape_literal($message);
  #$message_insert = utf8_encode(preg_replace('/\n/','<br>',$message_escape));
  $message_insert = preg_replace('/\n/','<br>',$message_escape);
//message sending starts
    if($channel == 'SMS' || $channel == 'SQOOPE'){
      $mobile = $res[0]['display_name'];
    } else {
      $mobile = '';
    }
    //error_log("Send Conversation:: INC ID :: '$inc_id'");
    //error_log("Send Conversation:: Channel :: '$channel'");
    if ($channel == 'LIVECHAT') {
      //error_log("Send Conversation:: Inside Live chat process");
      $sent_status = "0";
      // Get the active session id
      $cmd = "SELECT session_id FROM chat_activity WHERE chat_activity_id = '$chat_activity_id'";
      $res = getSQLresult($dbconn,$cmd);
      $session_id = $res[0]['session_id'];

      // Insert into Livechat log table
      $livechat_log_id = getSequenceID($dbconn,'livechat_log_livechat_log_id_seq');
      //error_log("Send Conversation:: Livechat Log Id :: '$livechat_log_id'");
      $sqlcmd = "INSERT INTO livechat_log (livechat_log_id, session_id, sent_by, message, created_dtm, inc_id)
            VALUES ('".dbSafe($livechat_log_id)."','".dbSafe($session_id)."','".dbSafe($userid)."',{$message_insert},'now()', '$inc_id')";
      $row = doSQLcmd($dbconn, $sqlcmd);
    } else {
      $sent_status = "";
      $sent_status = sendSMS($userid, $dept, $mobile, $message_insert, $priority, $inc_id, $trackid, $s_mode, $channel, $message_escape);
    }

    //error_log("Send Conversation:: Sent Status :: '$sent_status'");

    if($sent_status == '0'){
      //error_log("message sent successfully!");
      $cmd3 = "UPDATE chat_activity SET lastmsg_by = '$userid', lastmsg_dtm = 'now()' WHERE inc_id = '$inc_id'";
      $res3 = doSQLcmd($dbconn,$cmd3);
      if($res3){
        //error_log("chat activity for ".$inc_id." updated!");
      }
    }

    $cmd4 = "SELECT COUNT(*) FROM chat_activity WHERE inc_id = '$inc_id'";
    $res4 = getSQLresult($dbconn,$cmd4);
    if($res4[0]['count'] == 0){
      $chat_id = "AC".time().sprintf("%05d",getSequence($dbconn,'chat_activity_chat_activity_id_seq'));
      $cmd5 = "INSERT INTO chat_activity (chat_activity_id, inc_id, lastmsg_by, lastmsg_dtm, unread_flag, notification_flag)
  				VALUES ('$chat_id', '$inc_id', '$userid', 'now()', 'false', 'false')";
      $res5 = doSQLcmd($dbconn,$cmd5);
    }

  //message sending ends
}

function sendSMS($userid, $department, $mobile, $message_insert, $priority, $inc_id, $trackid, $mode, $channel, $message_escape)
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
            	 			//error_log($sqlcmd . ' -- ' .pg_last_error($dbconn));
       					}
					}
				}

				if( $check_quota > 0 || $check_unlimited_quota == 1){
					$outgoing_id = getSequenceID($dbconn,'outgoing_logs_outgoing_id_seq');
					$sqlcmd = "INSERT INTO outgoing_logs (outgoing_id, priority, trackid, sent_by, department, mobile_numb, message, message_status, inc_id)
								VALUES ('".dbSafe($outgoing_id)."','".dbSafe($priority)."','".dbSafe($trackid)."','".dbSafe($userid)."','".dbSafe($department)."','$mobile',
                {$message_insert},'Q', '$inc_id')";
					$row = doSQLcmd($dbconn, $sqlcmd);
				} else {
					$row = 0;
					$error++;
				}
      } else {
        $outgoing_id = getSequenceID($dbconn,'outgoing_logs_outgoing_id_seq');
        $cmd2 = "INSERT INTO outgoing_logs (outgoing_id, priority, trackid, sent_by, department, message, message_status, inc_id)
              VALUES ('".dbSafe($outgoing_id)."','".dbSafe($priority)."','".dbSafe($trackid)."','".dbSafe($userid)."','".dbSafe($department)."',{$message_insert},'Q', '$inc_id')";
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

  	$sqlcmd = "INSERT INTO webapp_sms (msgid, mobile_numb, msg_content, mode, priority, msg_from, msg_status, label, inc_id, raw_msg)
				VALUES ('$trackid', '$mobile', {$msg_content}, 'TEXT', '$priority', '$msg_from', 'W','$label', '$inc_id', {$message_escape})";
	$result = pg_query($dbconn, $sqlcmd);

	if(!$result)
	{
		//error_log("Database Error (" .$sqlcmd. ") -- ".pg_last_error($dbconn));
		return 0;
	} else {
		$no_of_affected = pg_affected_rows($result);
    //error_log("no_of_affected:".$no_of_affected);
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

function addAssign($userid,$assign_to,$assign_msg,$activity_id){
  global $dbconn;

  $cmd_by = "SELECT userid FROM user_list WHERE id = '$assign_to'";
  $res_by = getSQLresult($dbconn,$cmd_by);

  $assign_to_name = $res_by[0]['userid'];

  $assign_note_id = "AS".sprintf("1%08d",getSequence($dbconn,'assign_note_assign_id_seq'));
  $cmd = "INSERT INTO chat_assignment_log(chat_assignment_id,chat_activity_id,assigned_dtm,assigned_by,assigned_to,assigned_msg)
          VALUES('$assign_note_id','$activity_id','now()','$userid','$assign_to_name','$assign_msg')";
  $res = doSQLcmd($dbconn,$cmd);

  if($res){
    $cmd2 = "UPDATE chat_activity SET lastassigned_dtm='now()',lastassigned_to='$assign_to_name',lastassigned_by='$userid', locked_flag='TRUE' WHERE chat_activity_id='$activity_id'";
    $res2 = doSQLcmd($dbconn,$cmd2);

    if($res2){
      echo "assigned";
    } else{
      //error_log("Database Error (" .$cmd2. ") -- ".pg_last_error($dbconn));
    }
  } else{
    //error_log("Database Error (" .$cmd. ") -- ".pg_last_error($dbconn));
    echo "unassigned";
  }

}

function convUsingBy($activity_id,$using_by){
  global $dbconn;

  $cmd = "UPDATE chat_activity SET editing_by = '$using_by' WHERE chat_activity_id = '$activity_id'";
  $res = doSQLcmd($dbconn,$cmd);
//error_log("res using:".$res);
  if($res){
    $cmd2 = "UPDATE chat_activity SET editing_by = null WHERE editing_by = '$using_by' AND chat_activity_id != '$activity_id'";
    $res2 = doSQLcmd($dbconn,$cmd2);
    if($res2){
      echo "using by ".$using_by;
    }
  } else{
    //error_log("Database Error (" .$cmd. ") -- ".pg_last_error($dbconn));
  }
}

function convNotUsingBy($activity_id,$not_using_by){
  global $dbconn;

  $cmd = "UPDATE chat_activity SET editing_by = null WHERE editing_by = '$not_using_by' AND chat_activity_id != '$activity_id'";
  $res = doSQLcmd($dbconn,$cmd);
//error_log("res not using:".$res);
  if($res){
    echo "not using by ".$not_using_by;
  } else{
    //error_log("Database Error (" .$cmd. ") -- ".pg_last_error($dbconn));
  }
}

function saveToAddressBook($inc_id, $contact_name, $mobile, $group, $userid, $dept){
  global $dbconn;

  $id_of_user = getUserID($userid);

  $status = '';

  if (!empty($group)) {
		$group_str = implode(",",$group);
	} else {
		$group_str = "";
	}

  $contact_name = pg_escape_literal($contact_name);

  $cmd2 = "SELECT * FROM address_book WHERE inc_id = '$inc_id'";
  $res2 = getSQLresult($dbconn,$cmd2);

  if($res2){
    $cmd = "UPDATE address_book SET contact_name = $contact_name, mobile_numb = '$mobile', user_id = '$id_of_user', department = '$dept',
      group_string = '$group_str', created_by = '$userid', access_type = 1 WHERE inc_id = '$inc_id'";
    $res = doSQLcmd($dbconn,$cmd);

  }else{
    $contact_id = getSequenceID($dbconn,'address_book_contact_id_seq');
    $cmd = "INSERT INTO address_book(contact_id, contact_name, mobile_numb, user_id, group_string, created_by, access_type, inc_id, department)
          VALUES('$contact_id', $contact_name, '$mobile', '$id_of_user', '$group_str', '$userid', 1, '$inc_id', '$dept')";
    $res = doSQLcmd($dbconn,$cmd);

    if(!$res){
      $status = "Save Contact Unsuccessfully.";
    }
  }

  echo $status;
}

function listToAddressBook($inc_id){
  global $dbconn;

  $data = Array();

  $cmd2 = "SELECT contact_name, mobile_numb, group_string, modem_label, inc_id FROM address_book WHERE inc_id = '$inc_id'";
  $res2 = getSQLresult($dbconn,$cmd2);

  if($res2){
    $data['display_name'] = $res2[0]['contact_name'];
    $data['mobile'] = $res2[0]['mobile_numb'];
    $data['group_string'] = $res2[0]['group_string'];
  } else{
    $cmd = "SELECT display_name, inc_id FROM incoming_contact WHERE inc_id = '$inc_id'";
    $res = getSQLresult($dbconn,$cmd);

    $data['display_name'] = $res[0]['display_name'];
    $data['mobile'] = 0;
    $data['group_string'] = '';
  }
  echo json_encode($data);
}

//Notifcation
function getNotiCount(){
  global $dbconn;

  $cmd = "SELECT COUNT(unread_flag) AS c_unread_flag FROM chat_activity WHERE unread_flag = TRUE";
  $res = getSQLresult($dbconn,$cmd);

  echo json_encode($res);
}

function updateUnreadFlag($inc_id,$chat_activity_id){
  global $dbconn;
  $msg = '';
  $status = 0;
  $data = Array();
  $cmd = "UPDATE chat_activity SET unread_flag = FALSE WHERE chat_activity_id = '$chat_activity_id'";
  $res = doSQLcmd($dbconn,$cmd);
  if($res){
    $cmd2 = "UPDATE common_inbox SET unread_flag = FALSE WHERE inc_id = '$inc_id'";
    $res2 = doSQLcmd($dbconn,$cmd2);

    if($res2){
      $status = 1;
      $msg = "Unread flag set to false.";
    }
  }
  $data['status'] = $status;
  $data['msg'] = $msg;

  echo json_encode($data);
}

function getNotification(){
  global $dbconn;

  $cmd = "SELECT a.chat_activity_id,a.inc_id,a.lastmsg_by AS lastmsg_by,b.unmatched_keyword FROM chat_activity a, common_inbox b WHERE a.inc_id=b.inc_id AND a.notification_flag='TRUE' ORDER BY a.lastmsg_dtm DESC, b.created_dtm DESC limit 1";
  $cmd2 = "UPDATE chat_activity SET notification_flag = 'false'";

  $data = array();
  $res = pg_query($dbconn,$cmd);
  $res2 = pg_query($dbconn,$cmd2);
  if ($res) {
    while($row = pg_fetch_assoc($res)){
      //check MIME type
      $cmd_mime = substr($row['unmatched_keyword'],0,10);
      $mime_flag = '';
      if($cmd_mime == 'MIME:IMAGE'){
        $unmatched_keyword = substr($row['unmatched_keyword'],11);
        $mime_flag = 1;
      } else{
        $unmatched_keyword = $row['unmatched_keyword'];
        $mime_flag = 0;
      }
      //MIME type end
      $data[] = array(
        'name'  => $row['lastmsg_by'],
        'msg'         => $unmatched_keyword,//$row['unmatched_keyword'],
        'roomID'      => $row['inc_id'],
        'chat_id'     => $row['chat_activity_id'],
        'noti_flag'   => $mime_flag
      );
    }
    echo json_encode($data);
  }
}

function getReplyingBy($activity_id){
  global $dbconn;

  $cmd = "SELECT editing_by FROM chat_activity WHERE chat_activity_id = '$activity_id'";
  $res = getSQLresult($dbconn,$cmd);
  //error_log("editing_by:::".$res[0]['editing_by']);
  $data = array();
  $data['replying_by'] = $res[0]['editing_by'];
  echo json_encode($data);
}

function getChatID($inc_id){
  global $dbconn;

  $cmd = "SELECT chat_activity_id FROM chat_activity WHERE inc_id = '$inc_id'";
  $res = getSQLresult($dbconn,$cmd);

  $data = array();
  $data['chat_id'] = $res[0]['chat_activity_id'];

  echo json_encode($data);
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
          //error_log("Moved failed - ".$_FILES["img_file"]["error"]);
        } else {
          $imagedata = file_get_contents($uploadfile);
          $file_data = base64_encode($imagedata);
        }
      }  else {
        //error_log("Upload failed - ".$_FILES["img_file"]["error"]);
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
          //error_log("Moved failed - ".$_FILES["img_file"]["error"]);
        } else {
          //error_log("works");
          $imagedata = file_get_contents($uploadfile);
          $file_data = base64_encode($imagedata);
        }
      }  else {
        //error_log("Upload failed - ".$_FILES["img_file"]["error"]);
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
