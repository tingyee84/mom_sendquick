<?php
require "lib/commonFunc.php";
require('lib/db_sq.php');
require('lib/db_spool.php');

$mode = filter_input(INPUT_POST,'mode');
$userid = strtolower($_SESSION['userid']);
$label = filter_input(INPUT_POST,'label');
$dept = filter_input(INPUT_POST,'dept');
$idx = filter_input(INPUT_POST,'idx');

switch ($mode) {
	case "listModemDept":
		listModemDept();
		break;
	case "editModemDept":
		editModemDept($idx);
		break;
	case "addModemLabel":
		addModemLabel($userid, $dept, $label);
		break;
	case "updateModemLabel":
		updateModemLabel($userid, $idx, $label);
		break;
	case "deleteModemDept":
		deleteModemDept($idx);
		break;
	case "getLabel":
		listLabel();
		break;
	default:
		die("Invalid Command");
}

function listModemDept()
{
	global $dbconn;
	$result_array = array();

	$sqlcmd = "select idx, department, dept, modem_label, to_char(modem_dept.created_dtm, 'DD-MM-YYYY') as created_dtm, ".
			  "updated_by, to_char(modem_dept.updated_dtm, 'DD-MM-YYYY') as updated_dtm, ".
			  "modem_dept.created_by as created_by from modem_dept left outer join department_list ".
			  "on (modem_dept.dept=department_list.department_id) order by department";
	$result = pg_query($dbconn, $sqlcmd);

	for($i=1;$row=pg_fetch_array($result);$i++){
		if(empty($row['dept'])){
			$modem_dept = 'All Departments';
		} else{
			$modem_dept = htmlspecialchars($row['department']);
		}
		array_push($result_array,Array(
			$i,
			'<a href="#myModem" data-bs-toggle="modal" data-idx="'.$row['idx'].'">'.$modem_dept.' <i class="fa fa-pencil-square-o fa-fw"></i></a>',
			htmlspecialchars($row['modem_label']),
			htmlspecialchars($row['created_by']),
			$row['created_dtm'],
			htmlspecialchars($row['updated_by']),
			$row['updated_dtm'],
			'<input type="checkbox" name="no" value="'.$row['idx'].'">'
		));
	}
	
	echo json_encode(Array("data"=>$result_array));
}

function addModemLabel($userid, $dept, $modem_label)
{
	global $dbconn;
	
	if(check_duplicate($dept, $modem_label) > 0) {
		echo "Modem Configuration already exist";
	} else {
		$sqlcmd = "insert into modem_dept (idx, dept, modem_label, created_by) values ('"
					.getSequenceID($dbconn,'modem_dept_idx_seq')."','".$dept."','".$modem_label."','".$userid."')";
		$res = doSQLcmd($dbconn, $sqlcmd);

		if(empty($res)) { 
			echo "Database Error: ".$res;
		}
	}
}

function editModemDept($idx)
{
	global $dbconn;

	$sqlcmd = "select dept, modem_label from modem_dept where idx='".pg_escape_string($idx)."'";
	$rows = getSQLresult($dbconn, $sqlcmd);
	
	echo json_encode($rows);
}

function updateModemLabel($userid, $idx, $modem_label)
{
	global $dbconn;

	$sqlcmd = "update modem_dept set modem_label='".$modem_label."',updated_by='".$userid."',updated_dtm='now()' where idx='".$idx."'";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if(empty($res)) {
		echo "Database Error: ".$res;
	}
}

function deleteModemDept($idx)
{
	global $dbconn;

	$sqlcmd = "delete from modem_dept where idx='".pg_escape_string($idx)."'";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if(empty($res)) { 
		echo "Database Error: ".$res;
	}
}

function check_duplicate($dept, $label)
{
	global $dbconn;

	$chksql = "select idx from modem_dept where dept='".$dept."' and modem_label='".$label."'";
	$res = pg_query($dbconn, $chksql);
	$rows = pg_num_rows($res);
	
	return $rows;
}

function listLabel()
{
	global $spdbconn,$sqdbconn;
	$data = array();
	
	$cmd = "SELECT modem_label as label FROM modem_route WHERE trim(modem_label) > ''";	
	$res = pg_query($sqdbconn,$cmd);
	while($row = pg_fetch_assoc($res)) {
		if (strpos($row['label'],',') !== false) {
			$tmp = explode(",",$row['label']);
			foreach($tmp as $key) {    
				$row['label'] = trim($key);
				$data[] = $row;
			}
		} else {
			$data[] = $row;
		}
	}
	
	$cmd2 = "select tbl.label from 
			(select label FROM asp_route where enable_flag=1 union 
			select label FROM sqoope_route where enable_flag=1 union 
			select label FROM direct_conn where enableflag=1 union 
			select label FROM virtual_modem_route where enable_flag=1) as tbl
			where trim(label) > ''";	
	$res2 = pg_query($spdbconn,$cmd2);
	while($row2 = pg_fetch_assoc($res2)) {
		$data[] = $row2;
	}
	
	//get unique value and sort
	$data = array_map('json_encode', $data);
	$data = array_unique($data);
	sort($data, SORT_NATURAL | SORT_FLAG_CASE);
	$data = array_map('json_decode', $data);

	echo json_encode($data);
}
?>
