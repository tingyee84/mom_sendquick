<?php
require('lib/commonFunc.php');

$mode = filter_input(INPUT_POST,'mode2');
$inc_id = filter_input(INPUT_POST,'inc_id2');
$from_dt = filter_input(INPUT_POST,'from_dt');
$to_dt = filter_input(INPUT_POST,'to_dt');

switch($mode){
  case "exportchat":
      exportConversation($inc_id,$from_dt,$to_dt);
      break;
  default:
      die("Invalid Command");
}

function exportConversation($inc_id, $from_dt, $to_dt){
  global $dbconn;
  $data = array();

  if(empty($from_dt)) { $from_dt = date(); }
	if(empty($to_dt)) { $to_dt = date(); }

  $cmd = "SELECT id,mobile,msg,dtm_string,type,sender,bc_id,inc.display_name AS display_name, inc.channel AS channel FROM(
          SELECT ol.inc_id AS id, ol.mobile_numb AS mobile, ol.message AS msg, to_char(ol.created_dtm, 'DD/MM/YYYY HH24:MI:SS') AS dtm_string, sent_by AS sender,
          ol.created_dtm AS dtm, 'outgoing' AS type, bc_id AS bc_id FROM outgoing_logs ol WHERE ol.inc_id = '$inc_id'
          UNION ALL
          SELECT live.inc_id AS id, 'NULL' AS mobile, live.message AS msg, to_char(live.created_dtm, 'DD/MM/YYYY HH24:MI:SS') AS dtm_string,
          live.sent_by AS sender, live.created_dtm AS dtm, 'livechat' AS type, bc_id AS bc_id
          FROM livechat_log live LEFT OUTER JOIN incoming_contact inc ON(live.inc_id = inc.inc_id)
          WHERE live.inc_id = '$inc_id' AND live.sent_by = 'useradmin'
          UNION ALL
          SELECT ci.inc_id AS id, ci.mobile_numb AS mobile, ci.unmatched_keyword AS msg, to_char(ci.created_dtm, 'DD/MM/YYYY HH24:MI:SS') AS dtm_string,
          inc.display_name AS sender, ci.created_dtm AS dtm, 'incoming' AS type, null AS bc_id
          FROM common_inbox ci LEFT OUTER JOIN incoming_contact inc ON(ci.inc_id = inc.inc_id)
          WHERE ci.inc_id = '$inc_id'
          UNION ALL
          SELECT ca.inc_id AS id,'NULL' AS mobile, cal.assigned_msg AS msg,to_char(cal.assigned_dtm, 'DD/MM/YYYY HH24:MI:SS') AS dtm_string, lastassigned_by AS sender,
          cal.assigned_dtm AS dtm, 'assign' AS type, null AS bc_id FROM chat_activity ca LEFT OUTER JOIN chat_assignment_log cal ON(ca.chat_activity_id = cal.chat_activity_id)
          WHERE ca.inc_id = '$inc_id' AND cal.assigned_msg IS NOT NULL) AS tbl
          LEFT OUTER JOIN incoming_contact inc ON(id = inc.inc_id) WHERE dtm >= to_date('".$from_dt."','DD/MM/YYYY') AND dtm <= to_date('".$to_dt."','DD/MM/YYYY') ORDER BY dtm";
// error_log($cmd);
  $res = pg_query($dbconn,$cmd);
  while($row = pg_fetch_array($res)) {
    array_push($data,Array(
			$row['id'],
			htmlspecialchars($row['mobile']),
			htmlentities($row['msg'], ENT_SUBSTITUTE, "UTF-8"),
			$row['dtm_string'],
			$row['type'],
      $row['sender'],
      $row['bc_id'],
      $row['display_name'],
      $row['channel']
		));
  }
  // error_log('data:::'.print_r($data,true));
  echo json_encode(Array("data"=>$data));
}
?>
