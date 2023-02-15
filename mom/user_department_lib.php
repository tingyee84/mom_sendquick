<?php
require_once('lib/commonFunc.php');
include("lib/db_spool.php");

$userid = strtolower($_SESSION['userid']);
$id = @$_REQUEST['id'];
$mode = @$_REQUEST['mode'];
$department = @$_REQUEST['department'];
$enable_unlimited = @$_REQUEST['enable_unlimited'];
$quota_left = @$_REQUEST['quota_left'];
$frequency = @$_REQUEST['frequency'];
$topup_value = @$_REQUEST['topup_value'];

//error_log("Department::".$department);
$x = GetLanguage("user_account",$lang);
$z = GetLanguage("user_department",$lang);

$bot_route = @$_REQUEST['mimroute'];

switch ($mode) {
	case "getDepartmentList":
        getDepartmentList($userid);
        break;
	case "listDepartment":
        listDepartment($userid);
        break;
	case "addDepartment":
        addDepartment($userid, $department, $bot_route, $enable_unlimited, $quota_left, $frequency, $topup_value );
        break;
	case "deleteDepartment":
        deleteDepartment($id);
        break;
	case "emptyDepartment":
        emptyDepartment($userid);
        break;
	case "getMIMRoute":
				getMIMRoute();
				break;
	case "editDepartment":
        editDepartment($id);
        break;
	case "saveDepartment":
        saveDepartment($userid, $department, $id, $bot_route, $enable_unlimited, $quota_left, $frequency, $topup_value );
        break;
    default:
        die('Invalid Command');
}

function getDepartmentList($userid)
{
	global $dbconn, $x;

	$getDepartmentList_msg1 = (string)$x->getDepartmentList_msg1;
	$getDepartmentList_msg2 = (string)$x->getDepartmentList_msg2;
	
	$sqlWhere = "";
	$getUserType = getUserType();
	if( $getUserType == "admin" ){
		$sqlWhere = "";
	}elseif( $getUserType == "bu" ){
		$sqlWhere = " and department_id in ( select department from user_list where userid = '".dbSafe( $_SESSION['userid'] )."' )";
	}
	
	if(empty($department)) {
		$sqlcmd = "select department_id, department, user_access from department_list where 1=1 $sqlWhere order by department";
	} else {
		$department = getUserDepartment($userid);
		$sqlcmd = "select department_id, department, user_access from department_list where ( department_id='".dbSafe($department)."' or user_access='1' ) $sqlWhere order by department";
	}

	$row = getSQLresult($dbconn, $sqlcmd);

	if(empty($row)) {
		error_log($getDepartmentList_msg1. " '".dbSafe($userid)."' \n".$getDepartmentList_msg2);
	} else {
		echo json_encode($row);
	}
}

function listDepartment($userid)
{
	global $dbconn, $spdbconn, $x;

	$listDepartment_msg1 = (string)$x->listDepartment_msg1;
	$db_err = (string)$x->db_err;

	$access_string =  explode(",", $_SESSION['access_string']);
	if(in_array("30", $access_string)){
		$sqlcmd = "SELECT department_id, department, created_by, bot_string FROM department_list ORDER BY department";
	} else {
		$department = getUserDepartment($userid);
		$sqlcmd = "SELECT department_id, department, created_by, bot_string FROM department_list WHERE department_id='".dbSafe($department)."' ORDER BY department";
	}

	$result = pg_query($dbconn, $sqlcmd);
	if(!$result)
	{
		error_log("listDepartment: ".$db_err." (" .$sqlcmd. ") -- " .pg_last_error($dbconn));
		echo $db_err;
	}
	else
	{
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			if(isUserAdmin($row['created_by']) && !isUserAdmin($userid)) {
				$disable = "disabled";
			} else {
				$disable = "";
			}

			$tmp = $row['bot_string'];
			if($tmp != "")
			{
				$bot_string = "------";

				$tmp_arr = explode(",", $tmp);
				for($b=0; $b<count($tmp_arr); $b++)
				{
					$bot_sql = "SELECT description, name FROM bot_route br LEFT OUTER JOIN bot_types bt ON(br.bot_type_id = bt.id) WHERE br.id='".dbSafe($tmp_arr[$b])."'";
					$bot_res = getBotRoute($spdbconn, $bot_sql);

					$bot_res2 = explode("|",$bot_res);
					$bot_name = $bot_res2[0];
					if(isset($bot_res2[1])){
						$bot_type = $bot_res2[1];
					} else{
						$bot_type = '';
					}


					$img = '';
					if($bot_type == 'FACEBOOK'){
						$img = '<img src="images/icons/icon_messenger@2x.png">';
					} else if($bot_type == 'TELEGRAM'){
						$img = '<img src="images/icons/icon_telegram@2x.png">';
					}else if($bot_type == 'SQOOPE'){
						$img = '<img src="images/icons/icon_sqoope@2x.png">';
					}else if($bot_type == 'LINE'){
						$img = '<img src="images/icons/icon_line@2x.png">';
					}else if($bot_type == 'LIVECHAT'){
						$img = '<img src="images/icons/icon_livechat@2x.png">';
					}else if($bot_type == 'SLACK'){
						$img = '<img src="images/icons/icon_slack@2x.png">';
					}else if($bot_type == 'MICROSOFT TEAMS'){
						$img = '<img src="images/icons/icon_teams@2x.png">';
					}else if($bot_type == 'VIBER'){
						$img = '<img src="images/icons/icon_viber@2x.png">';
					}else if($bot_type == 'WECHAT'){
						$img = '<img src="images/icons/icon_wechat@2x.png">';
					}else if($bot_type == 'WEBEX'){
						$img = '<img src="images/icons/icon_webex_round@0.5.png">';
					} else{
						$img = '<img src="images/icons/icon_text@2x.png">';
					}

					if($bot_name != "")
					{
						if(strcmp($bot_string, "------") == 0)
						{
							$bot_string = "<span id='bot_route_list'>".htmlspecialchars($bot_name, ENT_QUOTES).$img."</span>";
						}
						else
						{
							$bot_string .= ", " ."<span id='bot_route_list'>".htmlspecialchars($bot_name, ENT_QUOTES).$img."</span>";
						}
					}
				}
			}
			else
			{
				$bot_string = "------";
			}

			array_push($result_array,Array(
				'<a href="#myDept" data-bs-toggle="modal" data-id="'.$row['department_id'].'">'.htmlspecialchars($row['department'],ENT_QUOTES).'<i class="fa fa-pencil-square-o fa-fw"></i></a>',
				htmlspecialchars($row['created_by']),
				$bot_string,
				// '<input type="checkbox" id="no" name="no" value="'.$row['department_id'].'" '.$disable.'/>'
				// assmi
				'<input type="checkbox" class="user_checkbox" id="no" name="no" value="'.$row['department_id'].'" '.$disable.'/>'
				// assmi
			));
		}

		echo json_encode(Array("data"=>$result_array));
	}
}

function addDepartment($userid, $department, $bot_route, $enable_unlimited, $quota_left, $frequency, $topup_value )
{
	global $dbconn, $x, $z;
	$data = array();
	
	if(!txvalidator($department,TX_STRING)){
		$data['flag'] = 0;
		$data['status'] = (string)$z->invalid_department;
		$data['field'] = "department";
	}else if(!validateSize($z->new_dept,$department,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "department";
	}else if(!$enable_unlimited && !txvalidator($quota_left,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$z->invalid_quota_left;
		$data['field'] = "quota_left";
	}else if($frequency !=3 && !txvalidator($topup_value,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$z->invalid_topup_value;
		$data['field'] = "topup_value";
	}else{
		if(empty($frequency)) { $frequency = 3; }
		if(empty($topup_value)) { $topup_value = 0; }
		if(empty($quota_left)) { $quota_left = 0; }
		if(empty($enable_unlimited)) { $enable_unlimited = 0; }
	
		if($frequency == 1){
			$last_topup_dtm = "now()";
			$next_topup_dtm = "now() + '1 week'";
		}else if($frequency == 2){
			$last_topup_dtm = "now()";
			$next_topup_dtm = "now() + '1 month'";
		} else {
			$last_topup_dtm = "null";
			$next_topup_dtm = "null";
		}

		$addDepartment_msg1 = (string)$x->addDepartment_msg1;
		$already_exist = (string)$x->already_exist;
		$success_created = (string)$x->success_created;
		$unsuccess_created = (string)$x->unsuccess_created;
		$db_err = (string)$x->db_err;

		$query_sql = "SELECT department_id FROM department_list WHERE lower(department)='".dbSafe(strtolower($department))."'";
		$query_row = getSQLresult($dbconn, $query_sql);

		if(is_string($query_row))
		{
			$data['flag'] = 2;
			$data['status'] = $db_err;
			error_log("addDepartment: ".$db_err." (" .$query_sql. ") -- " .pg_last_error($dbconn));
		}
		else
		{
			if(!empty($query_row))
			{
				$data['flag'] = 2;
				$data['status'] = $addDepartment_msg1." '" .htmlspecialchars($department,ENT_QUOTES). "' ".$already_exist;
			}
			else
			{
				$department_id = getSequenceID($dbconn,'department_list_department_id_seq');
				$sqlcmd = "INSERT INTO department_list (department_id, department, created_by, bot_string, quota_limit, topup_frequency, unlimited_quota, quota_left, next_topup_dtm, last_topup_dtm ) VALUES ('".dbSafe($department_id)."', '".dbSafe($department)."', '".dbSafe($userid)."', '".dbSafe($bot_route)."', '".dbSafe($topup_value)."', '".dbSafe($frequency)."', '".dbSafe($enable_unlimited)."', '".dbSafe($quota_left)."', $next_topup_dtm, $last_topup_dtm) ";
				
				$row = doSQLcmd($dbconn, $sqlcmd);
				if($row == 0)
				{
					$data['flag'] = 2;
					$data['status'] = $addDepartment_msg1." '" .htmlspecialchars($department,ENT_QUOTES). "' ".$unsuccess_created;
				}else{
					insertAuditTrail( "Add New Department" );
				}
				
			}
		}		
	}
	echo json_encode($data);
}

function deleteDepartment($id)
{
	global $dbconn, $x;

	$deleteDepartment_msg1 = (string)$x->deleteDepartment_msg1;
	$deleteDepartment_msg2 = (string)$x->deleteDepartment_msg2;
	$deleteDepartment_msg3 = (string)$x->deleteDepartment_msg3;

	$getsql = "select user_role, count(user_role) as total from user_list where department='".dbSafe($id)."' and department!='0' group by user_role ";
	$getrow = getSQLresult($dbconn, $getsql);
	if(!is_string($getrow))
	{
		if(!empty($getrow))
		{
			for($s=0; $s<count($getrow); $s++)
			{
				$role_id = $getrow[$s]['user_role'];
				$total = $getrow[$s]['total'];
				updateTotal($dbconn, 'user_role_list', 'role_id', $role_id, 'total_users', '2', $total);
			}
		}
	}

	$usersql = "select id from user_list where department = '".dbSafe($id)."' ";
	$userrow = getSQLresult($dbconn, $usersql);

	$sqlcmd = "delete from department_list where department_id = '".dbSafe($id)."' ";
	$row = doSQLcmd($dbconn, $sqlcmd);
	if($row != 0)
	{
		$update_sql = "delete from user_list where department = '".dbSafe($id)."' ";
		$update = doSQLcmd($dbconn, $update_sql);
		if(!is_string($userrow))
		{
			for($t=0; $t<count($userrow); $t++)
			{
				$user_id = $userrow[$t]['id'];
				$ab_sql = "delete from address_book where user_id = '".dbSafe($user_id)."' ";
				$ab = doSQLcmd($dbconn, $ab_sql);
				$mt_sql = "delete from message_template where user_id = '".dbSafe($user_id)."' ";
				$mt = doSQLcmd($dbconn, $mt_sql);
			}
		}
	} else {
		echo $deleteDepartment_msg1;
	}
	
	insertAuditTrail( "Delete Department" );
}

function emptyDepartment($userid)
{
	global $dbconn, $x;

	$emptyDepartment_msg1 = (string)$x->emptyDepartment_msg1;
	$emptyDepartment_msg2 = (string)$x->emptyDepartment_msg2;

	if(strcmp($userid, "useradmin") == 0 || strcmp($userid, "momadmin") == 0 )
	{
		$sqlcmd = "truncate department_list";
		$row = doSQLcmd($dbconn, $sqlcmd);
		if (empty($row)) {
			error_log("emptyDepartment: Database Error: ".pg_last_error($dbconn));
			echo "Database Error";
		}

		$updatesql = "delete from user_list where department != '0' ";
		$updaterow = doSQLcmd($dbconn, $updatesql);

		$getsql = "update user_role_list set total_users = '0' ";
		$getrow = doSQLcmd($dbconn, $getsql);

		$ab_sql = "delete from address_book where user_id != '0' ";
		$ab = doSQLcmd($dbconn, $ab_sql);

		$mt_sql = "delete from message_template where user_id != '0' ";
		$mt = doSQLcmd($dbconn, $mt_sql);
	}
	else
	{
		echo $emptyDepartment_msg1; //Operation Denied!
	}
	
	insertAuditTrail( "Empty Department" );
}

function getMIMRoute(){
	global $spdbconn;

	$cmd = "SELECT br.id, description, name FROM bot_route br LEFT OUTER JOIN bot_types bt ON (br.bot_type_id = bt.id) ORDER BY description";
	$res = getSQLresult($spdbconn,$cmd);

	echo json_encode($res);
}

function editDepartment($id)
{
	global $dbconn, $lang;

	$res_arr = array();

	$cmd = "SELECT * FROM department_list WHERE department_id='".dbSafe($id)."'";
	$row = getSQLresult($dbconn, $cmd);
	if(is_string($row))
	{
		error_log ("editDepartment: Database Error (" .dbSafe($cmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		echo "Database Error";
	}
	else
	{
		if(empty($row))
		{
			echo "System Error -- Department ID '" .htmlspecialchars($id,ENT_QUOTES). "' Could Not Be Found In Table 'department_list'";
		}
		else
		{
			$res_arr['department'] = $row[0]['department'];
			$res_arr['bot_string'] = $row[0]['bot_string'];
			$res_arr['quota_limit'] = $row[0]['quota_limit'];
			$res_arr['frequency'] = $row[0]['topup_frequency'];
			$res_arr['unlimited_quota'] = $row[0]['unlimited_quota'];
			$res_arr['quota_left'] = $row[0]['quota_left'];
			$res_arr['next_topup_dtm'] = $row[0]['next_topup_dtm'];
			$res_arr['last_topup_dtm'] = $row[0]['last_topup_dtm'];
			//$res_arr['cmd'] = $cmd;
			
			echo json_encode($res_arr);
		}
	}
}

function saveDepartment($userid, $department, $id, $bot_route, $enable_unlimited, $quota_left, $frequency, $topup_value )
{
	global $dbconn, $lang, $z;
	$data = array();
	
	if(!$enable_unlimited && !txvalidator($quota_left,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$z->invalid_quota_left;
		$data['field'] = "quota_left";
	}else if($frequency != 3 && !txvalidator($topup_value,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$z->invalid_topup_value;
		$data['field'] = "topup_value";
	}else{

		if(empty($frequency)) { $frequency = 3; }
		if(empty($topup_value)) { $topup_value = 0; }
		if(empty($quota_left)) { $quota_left = 0; }
		if(empty($enable_unlimited)) { $enable_unlimited = 0; }
		
		if($frequency == 1){
			$last_topup_dtm = "now()";
			$next_topup_dtm = "now() + '1 week'";
		}else if($frequency == 2){
			$last_topup_dtm = "now()";
			$next_topup_dtm = "now() + '1 month'";
		} else {
			$last_topup_dtm = "null";
			$next_topup_dtm = "null";
		}
		$contact = stripslashes($contact);
		$res_arr = array();
		if (!empty($bot_route)) {
			$bot_str = implode(",",$bot_route);
		} else {
			$bot_str = "";
		}
	
		$cmd = "UPDATE department_list SET department = '" .dbSafe($department). "', bot_string = '" .dbSafe($bot_str). "',".
				" modified_by = '" .dbSafe($userid). "', modified_dtm = 'now()',  quota_limit = '".dbSafe($topup_value)."',  topup_frequency = '".dbSafe($frequency)."',  unlimited_quota = '".dbSafe($enable_unlimited)."',  quota_left = '".dbSafe($quota_left)."',  next_topup_dtm = $next_topup_dtm,  last_topup_dtm = $last_topup_dtm WHERE department_id = '" .dbSafe($id). "'";

		$row = doSQLcmd($dbconn, $cmd);
		
		if($row == 0){
			$data['flag'] = 2;
			$data['status'] = (string)$z->alert_5;
		}else{
			$data['flag'] = 1;
			insertAuditTrail( "Edit Department" );
		}		
	}	
	echo json_encode($data);
}

function getBotRoute($dbconn, $cmd)
{
	global $lang;

	$res = pg_query($dbconn, $cmd);
	if(!$res)
	{
		error_log("Database Error (" .dbSafe($cmd). ") -- " .dbSafe(pg_last_error($dbconn)));
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
			return $row[0]['description']."|".$row[0]['name'];
		}
	}
}
?>
