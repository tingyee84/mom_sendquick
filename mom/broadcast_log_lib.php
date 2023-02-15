<?php
require "lib/commonFunc.php";

$mode = filter_input(INPUT_POST, 'mode');
$date_from = filter_input(INPUT_POST, 'from');
$date_to = filter_input(INPUT_POST, 'to');
$userid = strtolower($_SESSION['userid']);
$dept = (isUserAdmin($userid) ? '0' : getUserDepartment($userid));

switch ($mode) {
  case 'list':
    listBCLog($date_from, $date_to, $userid, $dept);
    break;
  default:
    die("Unknown request.");
}

function listBCLog($from, $to, $userid, $dept){
  global $dbconn;

  $res_arr = array();

  if(empty($from)) { $from = date(); }
	if(empty($to)) { $to = date(); }
  if(isUserAdmin($userid)){
    $cmd = "SELECT DISTINCT bml.bc_id, bml.inc_id AS inc_id_str, created_by, to_char(bml.created_dtm, 'DD/MM/YYYY HH24:MI') AS created_dtm, msg FROM(
            SELECT bc_id, message AS msg FROM outgoing_logs
            UNION ALL
            SELECT bc_id, message AS msg FROM livechat_log) AS tbl LEFT OUTER JOIN broadcast_mim_log bml ON(tbl.bc_id = bml.bc_id)
            WHERE tbl.bc_id IS NOT NULL AND bml.created_dtm >= to_date('$from','DD/MM/YYYY') AND bml.created_dtm <= to_date('$to','DD/MM/YYYY') + interval '1 day'
            ORDER BY created_dtm DESC";
  } else{
    $dept_cmd = "SELECT userid FROM user_list WHERE department = '$dept'";
    $dept_res = getSQLresult($dbconn,$dept_cmd);
    for($h=0; $h<count($dept_res); $h++){
      if($h==0){
        $res2 = $dept_res[$h]['userid'];
      } else{
        $res2 .= ",".$dept_res[$h]['userid'];
      }
    }
    $dept_arr = explode(",",$res2);
    $dept_str = implode("','",$dept_arr);
    $cmd = "SELECT DISTINCT bml.bc_id, bml.inc_id AS inc_id_str, created_by, to_char(bml.created_dtm, 'DD/MM/YYYY HH24:MI') AS created_dtm, msg FROM(
            SELECT bc_id, message AS msg FROM outgoing_logs
            UNION ALL
            SELECT bc_id, message AS msg FROM livechat_log) AS tbl LEFT OUTER JOIN broadcast_mim_log bml ON(tbl.bc_id = bml.bc_id)
            WHERE bml.created_by IN('$dept_str') AND bml.created_dtm >= to_date('$from','DD/MM/YYYY') AND bml.created_dtm <= to_date('$to','DD/MM/YYYY') + interval '1 day'
            ORDER BY created_dtm DESC";
  }
  $res = pg_query($dbconn, $cmd);


  for ($a=0; $row = pg_fetch_array($res); $a++)
	{
    $inc_id_str = "------";
    $tmp = $row['inc_id_str'];
    $tmp_arr = explode(",", $tmp);
    for($b=0; $b<count($tmp_arr); $b++)
    {
      $rcpt_sql = "SELECT display_name FROM incoming_contact WHERE inc_id = '$tmp_arr[$b]'";
      $rcpt_name = getMIMName($dbconn, $rcpt_sql);
      $rcpt_sql2 = "SELECT channel FROM incoming_contact WHERE inc_id = '$tmp_arr[$b]'";
      $rcpt_channel = getMIMChannel($dbconn, $rcpt_sql2);

      $img = '';
  		if($rcpt_channel == 'FACEBOOK'){
  			$img = '<img src="images/icons/icon_messenger@2x.png">';
  		} else if($rcpt_channel == 'TELEGRAM'){
  			$img = '<img src="images/icons/icon_telegram@2x.png">';
  		}else if($rcpt_channel == 'SQOOPE'){
  			$img = '<img src="images/icons/icon_sqoope@2x.png">';
  		}else if($rcpt_channel == 'LINE'){
  			$img = '<img src="images/icons/icon_line@2x.png">';
  		}else if($rcpt_channel == 'LIVECHAT'){
  			$img = '<img src="images/icons/icon_livechat@2x.png">';
  		}else if($rcpt_channel == 'SLACK'){
  			$img = '<img src="images/icons/icon_slack@2x.png">';
  		}else if($rcpt_channel == 'MICROSOFT TEAMS'){
  			$img = '<img src="images/icons/icon_teams@2x.png">';
  		}else if($rcpt_channel == 'VIBER'){
  			$img = '<img src="images/icons/icon_viber@2x.png">';
  		}else if($rcpt_channel == 'WECHAT'){
  			$img = '<img src="images/icons/icon_wechat@2x.png">';
  		} else if($rcpt_channel == 'WEBEX'){
  			$img = '<img src="images/icons/icon_webex_round@0.5.png">';
  		} else{
  			$img = '<img src="images/icons/icon_text@2x.png">';
  		}

      if(strcmp($inc_id_str, "------") == 0)
      {
        //$inc_id_str = "<span title=".$rcpt_channel.">".$img." ".htmlspecialchars($rcpt_name, ENT_QUOTES) ."</span>";
        $inc_id_str = "<li title=".$rcpt_channel.">".$img." ".htmlspecialchars($rcpt_name, ENT_QUOTES) ."</li>";
      }
      else
      {
        if($b > 3){
          $inc_id_str .= "<li class=\"read-more-target\" title=".$rcpt_channel.">".$img." ".htmlspecialchars($rcpt_name, ENT_QUOTES) ."</li>";
          $lbl_btn = "<label for=\"".$row['bc_id']."\" class=\"read-more-trigger\"></label>";
        }else{
          $inc_id_str .= "<li title=".$rcpt_channel.">".$img." ".htmlspecialchars($rcpt_name, ENT_QUOTES) ."</li>";
          $lbl_btn = "";
          //$inc_id_str .= "<br><br> <span title=".$rcpt_channel.">".$img." ".htmlspecialchars($rcpt_name, ENT_QUOTES) ."</span>";
        }
      }
    }

    //check MIME type
    $cmd_mime = substr($row['msg'],0,10);
    if($cmd_mime == 'MIME:IMAGE'){
      $msg = '<img src="'.substr($row['msg'],11).'" style="height: 50px;">';
    } else{
      $msg = $row['msg'];
    }
    //MIME type end

    $rcpt_list = "<div><input type=\"checkbox\" class=\"read-more-state\" id=\"".$row['bc_id']."\" /><ul class=\"read-more-wrap\">".$inc_id_str."</ul>".$lbl_btn."</div>";
    //error_log($inc_id_str);
		 array_push($res_arr,Array(
			$row['created_dtm'],
			htmlspecialchars($row['created_by']),
      $msg,
      $rcpt_list
      //"<div style=\"max-height: 200px;overflow-y: auto;\">".$inc_id_str."</div>",
			//'<input type="checkbox" name="no" value="'.$row['bc_id'].'">'
		));
	}
  echo json_encode(Array("data"=>$res_arr));
}

function getMIMName($dbconn, $cmd)
{
	$res = pg_query($dbconn, $cmd);
	if(!$res)
	{
		$_SESSION['error_msg'] = "Database Error: (" .dbSafe($cmd). ") -- " .dbSafe(pg_last_error($dbconn));
		return;
	}
	else
	{
		$row = pg_fetch_all($res);
		if(empty($row))
		{
			return;
		}
		else
		{
			return $row[0]['display_name'];
		}
	}
}

function getMIMChannel($dbconn, $cmd)
{
	$res = pg_query($dbconn, $cmd);
	if(!$res)
	{
		$_SESSION['error_msg'] = "Database Error: (" .dbSafe($cmd). ") -- " .dbSafe(pg_last_error($dbconn));
		return;
	}
	else
	{
		$row = pg_fetch_all($res);
		if(empty($row))
		{
			return;
		}
		else
		{
			return $row[0]['channel'];
		}
	}
}
?>
