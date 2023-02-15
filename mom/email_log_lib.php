<?php
require "lib/commonFunc.php";

$mode = filter_input(INPUT_POST, 'mode');
$date_from = filter_input(INPUT_POST, 'from');
$date_to = filter_input(INPUT_POST, 'to');
$userid = strtolower($_SESSION['userid']);
$dept = (isUserAdmin($userid) ? '0' : getUserDepartment($userid));

//error_log("mode:".$mode);
switch ($mode) {
  case 'list':
		listLog($date_from, $date_to, $userid, $dept);
		break;
  case "delete":
		delete(@$_REQUEST['idx']);
		break;
  case "empty":
	   emptyLog($userid);
	   break;
  default:
    die("Unknown request.");
}

function listLog($from, $to, $userid, $dept){
  global $dbconn;

  $res_arr = array();

  $cmd = '';
  if(empty($from)) { $from = date(); }
	if(empty($to)) { $to = date(); }
  if(isUserAdmin($userid)){
    $cmd = "select email_id, sent_by, dl.department, email, status, to_char(completed_dtm, 'DD-MM-YYYY HH24:MI') as cdtm, email_from, email_subj, body, comment from email_logs e left outer join department_list dl ".
			"on (e.department=dl.department_id) ".
            "WHERE completed_dtm >= to_date('$from','DD/MM/YYYY') AND completed_dtm <= to_date('$to','DD/MM/YYYY') + interval '1 day'
            ORDER BY completed_dtm DESC";
  } else{
    $cmd = "select sent_by, dl.department, email, status, to_char(completed_dtm, 'DD-MM-YYYY HH24:MI') as cdtm, email_from, email_subj, body, comment from email_logs e left outer join department_list dl ".
			"on (e.department=dl.department_id) ".
            "WHERE e.department='$dept' and completed_dtm >= to_date('$from','DD/MM/YYYY') AND completed_dtm <= to_date('$to','DD/MM/YYYY') + interval '1 day'
            ORDER BY completed_dtm DESC";
  }
  $res = pg_query($dbconn, $cmd);

  //error_log($cmd);

  for ($a=0; $row = pg_fetch_array($res); $a++)
  {

    array_push($res_arr, Array(
		htmlspecialchars($row['cdtm']),
		htmlspecialchars($row['sent_by']),
		htmlspecialchars($row['department']),
		htmlspecialchars($row['email_from']),
		htmlspecialchars($row['email']),
		htmlspecialchars($row['email_subj']),
		htmlspecialchars($row['body']),
		htmlspecialchars($row['comment']),
		'<input type="checkbox" name="no" value="'.$row['email_id'].'">'
    ));
  }


  echo json_encode(Array("data"=>$res_arr));
}

function delete($idx)
{
	global $dbconn;




		$sqlcmd = "delete from email_logs where email_id='".$idx."'";

		$res = doSQLcmd($dbconn, $sqlcmd);

		if (!empty($res)) {
			echo "Database Error: ".$res;
		}

}

function emptyLog($userid)
{
	global $dbconn;

	$sqlcmd = "delete from email_logs";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if (!empty($res)) {
		echo "Database Error: ".$res;
	}
}
?>
