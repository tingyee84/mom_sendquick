<?php
require "lib/commonFunc.php";

$mode = filter_input(INPUT_POST,'mode');
$userid = strtolower($_SESSION['userid']);

switch ($mode) {
  case "listBCScheduled":
    listBCScheduled($userid);
    break;
  case "deleteBCScheduled":
    deleteBCScheduled(filter_input(INPUT_POST,'idx'));
    break;
  case "emptyBCScheduled":
    emptyBCScheduled($userid);
    break;
  default:
    die("Unknown request");
}

function listBCScheduled($userid){
  global $dbconn;
  $res_arr = array();


  $cmd = "SELECT scheduled_id, display_name, channel, message, to_char(scheduled_time, 'DD/MM/YYYY HH24:MI:SS') AS scheduled_time_formatted
			FROM scheduled_sms ss LEFT OUTER JOIN incoming_contact ic ON(ss.inc_id = ic.inc_id)
      WHERE created_by='".pg_escape_string($userid)."' AND ss.inc_id IS NOT NULL ORDER BY scheduled_time";

  $res = pg_query($dbconn, $cmd);

  while ($row = pg_fetch_array($res))
	{
    $img = '';
		if($row['channel'] == 'FACEBOOK'){
			$img = '<img src="images/icons/icon_messenger@2x.png">';
		} else if($row['channel'] == 'TELEGRAM'){
			$img = '<img src="images/icons/icon_telegram@2x.png">';
		}else if($row['channel'] == 'SQOOPE'){
			$img = '<img src="images/icons/icon_sqoope@2x.png">';
		}else if($row['channel'] == 'LINE'){
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
			$img = '<img src="images/icons/icon_webex_round@0.5.png">';
		} else{
			$img = '<img src="images/icons/icon_text@2x.png">';
		}

    //check MIME type
    $cmd_mime = substr($row['message'],0,10);
    if($cmd_mime == 'MIME:IMAGE'){
      $msg = '<img src="'.substr($row['message'],11).'" style="height: 50px;">';
    } else{
      $msg = $row['message'];
    }
    //MIME type end

		 array_push($res_arr,Array(
			htmlspecialchars($row['display_name']).' '.$img,
      $msg,//$row['message'],
			$row['scheduled_time_formatted'],
			'<input type="checkbox" name="no" value="'.$row['scheduled_id'].'">'
		));
	}

	echo json_encode(Array("data"=>$res_arr));
}

function deleteBCScheduled($idx)
{
	global $dbconn;

  $cmd2 = "SELECT inc_id, bc_id FROM scheduled_sms WHERE scheduled_id='".pg_escape_string($idx)."'";
  $res2 = getSQLresult($dbconn, $cmd2);

  if($res2){
    $cmd3 = "SELECT inc_id FROM broadcast_mim_log WHERE bc_id = '".$res2[0]['bc_id']."'";
    $res3 = getSQLresult($dbconn,$cmd3);

    $inc_arr = explode(",",$res3[0]['inc_id']);
    $inc_arr2 = str_replace($res2[0]['inc_id'],"",$inc_arr);
    $inc_arr_count = count(array_filter($inc_arr2));
    $inc_imp = implode(",",array_filter($inc_arr2));

    $cmd4 = "UPDATE broadcast_mim_log SET inc_id = '$inc_imp', total_rcpt = '$inc_arr_count' WHERE bc_id = '".$res2[0]['bc_id']."'";
    $res4 = doSQLcmd($dbconn,$cmd4);
    if($res4){
      $cmd = "DELETE FROM scheduled_sms WHERE scheduled_id='".pg_escape_string($idx)."'";
    	$res = doSQLcmd($dbconn, $cmd);
    }
  }

  $cmd5 = "SELECT inc_id FROM broadcast_mim_log WHERE bc_id = '".$res2[0]['bc_id']."'";
  $res5 = getSQLresult($dbconn,$cmd5);

  $res5_inc = $res5[0]['inc_id'];
  if($res5_inc == '' || $res5_inc = null){
    $cmd6 = "DELETE FROM broadcast_mim_log WHERE bc_id = '".$res2[0]['bc_id']."'";
    $res6 = doSQLcmd($dbconn,$cmd6);
  }

}

function emptyBCScheduled($userid)
{
	global $dbconn;

  $cmd2 = "SELECT DISTINCT bc_id FROM scheduled_sms WHERE inc_id IS NOT NULL";
  $res2 = getSQLresult($dbconn, $cmd2);

	$cmd = "DELETE FROM scheduled_sms WHERE created_by='".pg_escape_string($userid)."' AND inc_id IS NOT NULL";
	$res = doSQLcmd($dbconn, $cmd);

	if ($res){
    for($a=0; $a<count($res2); $a++){
      $cmd3 = "DELETE FROM broadcast_mim_log WHERE bc_id = '".$res2[$a]['bc_id']."'";
      $res3 = doSQLcmd($dbconn,$cmd3);

      error_log("Broadcast log deleted.");
    }
  }
}
?>
