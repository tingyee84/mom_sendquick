<?php
require_once('lib/commonFunc.php');

$userid = strtolower($_SESSION['userid']);
$id = @$_REQUEST['id'];
$mode = @$_REQUEST['mode'];
$username = @$_REQUEST['username'];
$password = @$_REQUEST['new_password'];
$confirmpwd = @$_REQUEST['confirmpwd'];
$mobile_numb = @$_REQUEST['mobile_numb'];
$role = @$_REQUEST['user_role'];
$department = @$_REQUEST['department'];
$access = @$_REQUEST['access'];
$access_start = @$_REQUEST['access_start'];
$access_end = @$_REQUEST['access_end'];
$email = @$_REQUEST['email'];
$user_type = @$_REQUEST['user_type'];
$pwd_threshold = @$_REQUEST['pwd_threshold'];
$pwd_expire = @$_REQUEST['pwd_expire'];
$timeout = @$_REQUEST['session_timeout'];
$chg_onlogon = @$_REQUEST['pwd_chgonfirst'];
$x = GetLanguage("user_account",$lang);
$patternscore = 2;

switch ($mode) {
	case "listUserAccount":
        listUserAccount($userid);
        break;
	case "addUserAccount":
        addUserAccount($userid, $username, $password, $confirmpwd, $mobile_numb, $department, $role, $access, $access_start, $access_end, $email, $user_type, $pwd_threshold, $pwd_expire, $timeout, $chg_onlogon);
        break;
	case "editUserAccount":
        editUserAccount($id);
        break;
	case "saveUserAccount":
		if ($password == $confirmpwd) {
			if (strlen($password) > 0 && pwdPatternCheck($password) < $patternscore && $password == $username) {
				echo (string) $x->min_8;
			} else {
				saveUserAccount($userid, $id, $username, $password, $confirmpwd, $mobile_numb, $department, $role, $access, $access_start, $access_end, $email, $user_type, $pwd_threshold, $pwd_expire, $timeout, $chg_onlogon);
			}
		} else {
			echo (string) $x->alert_3;
		}
        break;
	case "deleteUserAccount":
        deleteUserAccount($id);
        break;
	case "emptyUserAccount":
        emptyUserAccount($userid);
        break;
	case "listAssign":
		listAssign();
		break;
	case "checkPwdHistory":
		checkPwdHistory($password);
		break;
    default:
        die('Invalid Command');
}
function pwdPatternCheck($pwd) {
	$pattern[0] = '/[a-z]+/';
	$pattern[1] = '/[A-Z]+/';
	$pattern[2] = '/[0-9]+/';
	//$pattern[3] = '/[!-/:-@\[-`{-~]+/';
	$pattern[3] = '/\W+/';
	$point = 0;
	if (preg_match('/^[^\ ]{12,}$/',$pwd))
		foreach($pattern as $ptt)
			if (preg_match($ptt,$pwd))
				$point++;
	return $point;
}
function listUserAccount($userid)
{
	global $dbconn, $x;

	$listUserAccount_msg1 = (string)$x->listUserAccount_msg1;
	$db_err = (string)$x->db_err;

	if(strcmp($userid, "useradmin") != 0 && strcmp($userid, "momadmin") != 0 ) {
		$department = getUserDepartment($userid);
		$sqlcmd = "select user_type,id, userid, department_list.department as department, department_id, l_name ".
				"from user_list left outer join department_list on (user_list.department = department_list.department_id) ".
				"left outer join ldapserver on (user_list.data_source_id = ldapserver.l_id) ".
				"where userid!='".dbSafe($userid)."' AND user_list.department='".$department."' and user_list.userid not in ( 'useradmin', 'momadmin' ) order by userid";
	} else {
		$sqlcmd = "select user_type,id, userid, department_list.department as department, department_id, l_name ".
				"from user_list left outer join department_list on (user_list.department = department_list.department_id) ".
				"left outer join ldapserver on (user_list.data_source_id = ldapserver.l_id) ".
				"where user_list.department != '0' and user_list.userid not in ( 'useradmin', 'momadmin' ) order by userid";
	}

	$result = pg_query($dbconn, $sqlcmd);
	if(!$result) {
		error_log("listUserAccount: ".$db_err." (" .$sqlcmd. ") -- " .pg_last_error($dbconn));
		echo $db_err;
	} else {
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			
			$access_status = "Valid";
			$access_end = "";
			$now = date("Y-m-d 00:00:00");
			$sql2 = "select end_dtm from user_sub where userid = '".dbSafe( $row['userid'] )."' and start_dtm <= '".dbSafe( $now )."'";
			$result1 = pg_query($dbconn, $sql2 );
			for ($i=1; $row1 = pg_fetch_array($result1); $i++){
				$access_end = $row1['end_dtm'];
			}

			if( ( strtotime( $access_end ) <= strtotime( $now ) ) || strlen( $access_end ) == 0 ){
				$access_status = "Expired";
			}
			
			if( $row['user_type'] == "admin" ){
				$userType = "Admin";
			}elseif( $row['user_type'] == "bu" ){
				$userType = "BU";
			}elseif( $row['user_type'] == "user" ){
				$userType = "User";
			}else{
				$userType = "N/A";
			}
			
			array_push($result_array,Array(
				'<a href="#myUser" data-bs-toggle="modal" data-id="'.$row['id'].'">'.$row['userid'].' <i class="fa fa-pencil-square-o fa-fw"></i></a>',
				htmlspecialchars($row['department'],ENT_QUOTES),
				$userType,
				//htmlspecialchars($row['l_name']),
				$access_status,
				// '<input type="checkbox" id="no" name="no" value="'.$row['id'].'">'
				// assmi
				'<input type="checkbox" class="user_checkbox" id="no" name="no" value="'.$row['id'].'">'
				// assmi
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}
function addUserAccount($useridimplode, $new_userid, $password, $confirmpwd, $mobile_numb, $department, $user_role, $access, $access_start, $access_end, $email, $user_type, $pwd_threshold, $pwd_expire, $timeout,$chg_onlogon)
{
	global $dbconn, $x, $lang, $patternscore;
	$data = array();

	$addUserAccount_msg1 = (string)$x->addUserAccount_msg1;
	$addUserAccount_msg2 = (string)$x->addUserAccount_msg2;
	$addUserAccount_msg3 = (string)$x->addUserAccount_msg3;
	$db_err = (string)$x->db_err;
	$success_created = (string)$x->success_created;
	$unsuccess_created = (string)$x->unsuccess_created;
	$already_exist= (string)$x->already_exist;
	$alert_3 = (string)$x->alert_3;

	$userid = strtolower($useridimplode);
	$lower_new_userid = strtolower(stripslashes($new_userid));

	$mobile_verify = validateMno($mobile_numb);

	if(!txvalidator($new_userid,TX_STRING)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_username;
		$data['field'] = "username";
	}
	elseif(!validateSize($x->user_acc_name,$new_userid,"UID")){		
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "username";
	}
	elseif(!txvalidator($password,TX_STRING,"ALL")){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_new_password;
		$data['field'] = "new_password";
	}
	elseif(!validateSize($x->user_pwd,$password,"PWD")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "new_password";
	}
	elseif(strcmp($password,$confirmpwd) != 0){
		$data['flag'] = 0;
		$data['status'] = $alert_3;
		$data['field'] = "confirmpwd";
	}
	elseif(!txvalidator($mobile_numb,TX_SGMOBILEPHONE) || $mobile_verify == -1){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_mobile_numb;
		$data['field'] = "mobile_numb";
	}
	elseif(!txvalidator($email,TX_EMAILADDR)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_email;
		$data['field'] = "email";
	}
	elseif(!validDate($access_start)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_date;
		$data['field'] = "access_start";
	}
	elseif(!validDate($access_end)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_date;
		$data['field'] = "access_end";
	}
	elseif(!checkDateDiff($access_start,$access_end)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_access_start;
		$data['field'] = "access_start";
	}	
	elseif(!txvalidator($timeout,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_session_timeout;
		$data['field'] = "session_timeout";
	}
	elseif(!txvalidator($pwd_threshold,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_pwd_threshold;
		$data['field'] = "pwd_threshold";
	}
	elseif(!txvalidator($pwd_expire,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_pwd_expire;
		$data['field'] = "pwd_expire"; 
	}
	else{
		if (pwdPatternCheck($password) < $patternscore || $password == $new_userid) {
			$data['flag'] = 0;
			$data['status'] = (string)$x->min_8;
			$data['field'] = "new_password";			
		} else {
			if (empty($access)) { $access = array(); }
			$access_string = implode(",", $access);
			$query_sql = "select id from user_list where userid='".dbSafe($lower_new_userid)."'";
			$query_row = getSQLresult($dbconn, $query_sql);

			if(is_string($query_row)) {
				$data['flag'] = 2;
				$data['status'] = $db_err;		
				error_log("addUserAccount: ".$db_err." (" .$query_sql. ") -- " .pg_last_error($dbconn));						
			} else {
				if(!empty($query_row))
				{
					$data['flag'] = 2;
					$data['status'] = $addUserAccount_msg1." '" .htmlspecialchars($new_userid,ENT_QUOTES). "' ".$already_exist;					
				}
				else
				{
					$user_list_id = getSequenceID($dbconn,'user_list_id_seq');
					$encrypted_password = getEncryptedPassword(trim($password));
					error_log("xxxxxxxxxx encrypted_password: " . $encrypted_password);

					$sqlcmd = "insert into user_list 
								(id, userid, password, access_string, mobile_numb, user_role, department, created_by, language, email, user_type, pwd_threshold, pwd_expire, timeout, bu_userid, chg_onlogon) 
								values ('".dbSafe($user_list_id)."', '".dbSafe($lower_new_userid)."', 
								'".dbSafe($encrypted_password)."', '".dbSafe($access_string)."', 
								'".dbSafe($mobile_verify)."', '".dbSafe($user_role)."', 
								'".dbSafe($department)."', '".dbSafe($userid)."', '".$lang."', '".dbSafe($email)."', '".dbSafe($user_type)."', 
								'".dbSafe($pwd_threshold)."', '".dbSafe($pwd_expire)."', '".dbSafe($timeout)."', '".dbSafe($userid)."', ".(dbSafe($chg_onlogon)=='y'?"TRUE":"FALSE").") ";
					$row = doSQLcmd($dbconn, $sqlcmd);
					
					if($row != 0)
					{
						$user_sub_id = getSequenceID($dbconn,'user_sub_id_seq');
						$sqlcmd2 = "insert into user_sub ( subid, userid, start_dtm, end_dtm ) values ( '".dbSafe($user_sub_id)."', '".dbSafe($lower_new_userid)."', '".dbSafe( date( "Y-m-d 00:00:00", strtotime( $access_start ) ) )."', '".dbSafe( date( "Y-m-d 00:00:00", strtotime( $access_end ) ) )."' )";
						$row2 = doSQLcmd($dbconn, $sqlcmd2);
					
						$result_array[0] = $row;
						if($user_role != 0)
						{
							updateTotal($dbconn, 'user_role_list', 'role_id', $user_role, 'total_users', '1', '1');
						}
						updateTotal($dbconn, 'department_list', 'department_id', $department, 'total_users', '1', '1');
						
						$qid = getSequenceID($dbconn,'quota_mnt_idx_seq');
						$sqlquota = "insert into quota_mnt (idx, userid, topup_frequency, quota_limit, quota_left, next_topup_dtm, updated_dtm, updated_by, unlimited_quota)".
									"values ('$qid','$lower_new_userid','3','0','0',null,'now()','$userid','0')";
						doSQLcmd($dbconn, $sqlquota);
						insertAuditTrail("New User ".dbSafe($lower_new_userid). " created.");
						$data['flag'] = 1;
						$data['status'] = (string)$x->success_save;
					} else {
						$data['flag'] = 2;
						$data['status'] = $addUserAccount_msg3." '" .htmlspecialchars($new_userid,ENT_QUOTES). "' ".$unsuccess_created;	
					}
				}
			}
		}		
	}
	echo json_encode($data);
}

function editUserAccount($id)
{
	global $dbconn, $x;

	$editUserAccount_msg1 = (string)$x->editUserAccount_msg1;
	$editUserAccount_msg2 = (string)$x->editUserAccount_msg2;
	$db_err = (string)$x->db_err;

	$sqlcmd = "select userid, access_string, mobile_numb, user_role, department, data_source_id, email, user_type, pwd_threshold, pwd_expire, timeout, chg_onlogon from user_list where id='".dbSafe($id)."'";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row)) {
		error_log("editUserAccount: ".$db_err." (" .$sqlcmd. ") -- " .pg_last_error($dbconn));
		echo $db_err;
	} else {
		if(empty($row))
		{
			echo $editUserAccount_msg1." '" .htmlspecialchars($id,ENT_QUOTES). "' ".$editUserAccount_msg2;
		}
		else
		{
			$result_array = array();
			//$decryptedPwd ='';
			// if($row[0]['data_source_id'] == 0){
				//$decryptedPwd = trim(getDecryptedPassword(stripslashes($row[0]['password'])));
			//}
			$result_array['userid'] = $row[0]['userid'];
			// $result_array['password'] = $decryptedPwd;
			$result_array['access_string'] = $row[0]['access_string'];
			$result_array['mobile_numb'] = $row[0]['mobile_numb'];
			$result_array['user_role'] = $row[0]['user_role'];
			$result_array['department'] = $row[0]['department'];
			$result_array['data_source_id'] = $row[0]['data_source_id'];
			$result_array['email'] = $row[0]['email'];
			$result_array['user_type'] = $row[0]['user_type'];
			$result_array['pwd_threshold'] = $row[0]['pwd_threshold'];
			$result_array['pwd_expire'] = $row[0]['pwd_expire'];
			$result_array['timeout'] = $row[0]['timeout'];
			$result_array['chg_onlogon'] = $row[0]['chg_onlogon'] === "t" ? 1 : 0;
			
			$sqlcmd2 = "select * from user_sub where userid='".dbSafe( $row[0]['userid'] )."'";
			$row2 = getSQLresult($dbconn, $sqlcmd2);
			
			$result_array['access_start'] = date("d-m-Y",strtotime($row2[0]['start_dtm']));
			$result_array['access_end'] = date("d-m-Y",strtotime($row2[0]['end_dtm']));
			
			echo json_encode($result_array);
		}
	}
}

function saveUserAccount($userid, $id, $edit_userid, $password, $confirmpwd, $mobile_numb, $department, $user_role, $access, $access_start, $access_end, $email, $user_type, $pwd_threshold, $pwd_expire, $timeout, $chg_onlogon)
{
	global $dbconn, $x;
	$data = array();

	$saveUserAccount_msg1 = (string)$x->saveUserAccount_msg1;
	$saveUserAccount_msg2 = (string)$x->saveUserAccount_msg2;
	$saveUserAccount_msg3 = (string)$x->saveUserAccount_msg3;
	$success_save = (string)$x->success_save;
	$unsuccess = (string)$x->unsuccess;
	$alert_3 = (string)$x->alert_3;
	$db_err = (string)$x->db_err;

	$mobile_verify = validateMno($mobile_numb);
	
	if(!checkChangePwd($password,$confirmpwd)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_new_password;
		$data['field'] = "new_password";
	}	
	elseif(!txvalidator($mobile_numb,TX_SGMOBILEPHONE) || $mobile_verify == -1){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_mobile_numb;
		$data['field'] = "mobile_numb";
	}
	elseif(!txvalidator($email,TX_EMAILADDR)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_email;
		$data['field'] = "email";
	}
	elseif(!validDate($access_start)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_date;
		$data['field'] = "access_start";
	}
	elseif(!validDate($access_end)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_date;
		$data['field'] = "access_end";
	}
	elseif(!checkDateDiff($access_start,$access_end)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_access_start;
		$data['field'] = "access_start";
	}	
	elseif(!txvalidator($timeout,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_session_timeout;
		$data['field'] = "session_timeout";
	}
	elseif(!txvalidator($pwd_threshold,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_pwd_threshold;
		$data['field'] = "pwd_threshold";
	}
	elseif(!txvalidator($pwd_expire,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_pwd_expire;
		$data['field'] = "pwd_expire"; 
	}
	else{
		$getsql = "select password, old_pwd1, old_pwd2, department, user_role, userid from user_list where id='".dbSafe($id)."'";
		$get = getSQLresult($dbconn, $getsql);
		
		if(is_string($get)) {
			$data['flag'] = 2;
			$data['status'] = $db_err;	
			error_log("saveUserAccount: ".$db_err." (" .$getsql. ") -- " .pg_last_error($dbconn));
			echo json_encode($data);
			die();		
		} elseif(empty($get)) {
			$data['flag'] = 2;
			$data['status'] = $saveUserAccount_msg1." '" .htmlspecialchars($id,ENT_QUOTES). "' ".$saveUserAccount_msg2;
			echo json_encode($data);
			die();
		}else {
			$dbuserpasswd = $get[0]['password'];
			$old_department = $get[0]['department'];
			$old_user_role = $get[0]['user_role'];
			$old_user_id = $get[0]['userid'];
			$old_pwd1 = $get[0]['old_pwd1'];
			$old_pwd2 = $get[0]['old_pwd2'];			
		}
		
		if ($dbuserpasswd=="ldap-authen") {
			$encrypted_password = $dbuserpasswd;
		} else {
			$encrypted_password = getEncryptedPassword(trim($password));
		}
		$access_string = implode(",", $access);
		$txtpass = "";
		
		if (strlen($password) != 0 && strlen($confirmpwd) != 0) {
			if (strcmp($dbuserpasswd,$encrypted_password)===0 || strcmp($old_pwd1,$encrypted_password) === 0 || strcmp($old_pwd2,$encrypted_password)===0) {
				error_log("Failed! Password History Found.");
				$data['flag'] = 0;
				$data['status'] = "New password can not be the same as previous 3 old passwords.";
				$data['field'] = "new_password";
				echo json_encode($data);
				die();
			} else {
				$txtpass = "password='".dbSafe($encrypted_password)."', pwd_lastchg='now()', old_pwd1 = '$dbuserpasswd', old_pwd2 = '$old_pwd1', ";
			}
		}
		$sqlcmd = "update user_list set $txtpass access_string = '".dbSafe($access_string)."',
				mobile_numb='".dbSafe($mobile_verify)."', department='" .dbSafe($department)."', modified_by='".dbSafe($userid)."',
				user_role='".dbSafe($user_role)."', modified_dtm='now()', email = '".dbSafe($email)."', user_type = '".dbSafe($user_type)."',
				pwd_expire = '".dbSafe($pwd_expire)."', pwd_threshold = '".dbSafe($pwd_threshold)."', timeout = '".dbSafe($timeout)."', chg_onlogon = ".(dbSafe($chg_onlogon)=='y'?"TRUE":"FALSE")." where id='".dbSafe($id)."' ";

		$row = doSQLcmd($dbconn, $sqlcmd);
		if($row != 0)
		{
			$sqlcmd2 = "update user_sub set start_dtm = '".dbSafe( date( "Y-m-d 00:00:00", strtotime( $access_start ) ) )."', end_dtm = '".dbSafe( date( "Y-m-d 00:00:00", strtotime( $access_end ) ) )."', modified_dtm = 'now()' where userid = '".dbSafe($old_user_id)."'";
			$row2 = doSQLcmd($dbconn, $sqlcmd2);
			
			$result_array[0] = $row;
			if(($old_department != "") && ($old_user_role != ""))
			{
				if(($old_user_role != 0) && ($old_user_role != $user_role))
				{
					updateTotal($dbconn, 'user_role_list', 'role_id', $old_user_role, 'total_users', '2', '1');
					updateTotal($dbconn, 'user_role_list', 'role_id', $user_role, 'total_users', '1', '1');
				}
				if($old_department != $department)
				{
					updateTotal($dbconn, 'department_list', 'department_id', $old_department, 'total_users', '2', '1');
					updateTotal($dbconn, 'department_list', 'department_id', $department, 'total_users', '1', '1');
				}
			}
			insertAuditTrail("User ".dbSafe($old_user_id). " info updated.");
			$data['flag'] = 1;
			$data['status'] = (string)$x->success_save;
		} else {
			$data['flag'] = 2;
			$data['status'] = $saveUserAccount_msg3."'" .htmlspecialchars($edit_userid,ENT_QUOTES). "' ".$unsuccess . ":" .$sqlcmd;			
		}
	}
	echo json_encode($data);
}

function deleteUserAccount($id)
{
	global $dbconn;
	global $x;
	$department = $user_role = $data_source_id = '';

	$saveUserAccount_msg1 = (string)$x->saveUserAccount_msg1;
	$saveUserAccount_msg2 = (string)$x->saveUserAccount_msg2;
	$deleteUserAccount_msg1 = (string)$x->deleteUserAccount_msg1;
	$deleteUserAccount_msg2 = (string)$x->deleteUserAccount_msg2;
	$deleteUserAccount_msg3 = (string)$x->deleteUserAccount_msg3;
	$db_err = (string)$x->db_err;

	$getsql = "select department, user_role, data_source_id from user_list where id='".dbSafe($id)."'";
	$get = getSQLresult($dbconn, $getsql);

	if(is_string($get)) {
		error_log($db_err." (" .$getsql. ") -- " .pg_last_error($dbconn));
		echo $db_err;
	} else {
		if(empty($get)) {
			echo $saveUserAccount_msg1." '" .htmlspecialchars($id,ENT_QUOTES). "' ".$saveUserAccount_msg2;
		} else {
			$department = $get[0]['department'];
			$user_role = $get[0]['user_role'];
			$data_source_id = $get[0]['data_source_id'];
		}
	}

	$usr_quo_sql = "select userid from user_list where id='".dbSafe($id)."'";
	$usr_quo = getSQLresult($dbconn, $usr_quo_sql);
	$quo_id = $usr_quo[0]['userid'];

	$sqlcmd = "delete from user_list where id='".$id."'";
	$row = doSQLcmd($dbconn, $sqlcmd);

	if($row != 0)
	{
		$ab_sql = "delete from address_book where user_id='".dbSafe($id)."' and access_type ='0'";
		$ab = doSQLcmd($dbconn, $ab_sql);

		$agm_sql = "select group_id from address_group_main where user_id='".dbSafe($id)."' and access_type ='0'";
		$agm = getSQLresult($dbconn, $agm_sql);

		if(!is_string($agm_sql) && !empty($agm_sql))
		{
			$agm_id = $agm[0]['group_id'];
			$agm2_sql = " delete from address_group where main_id = '".dbSafe($agm_id)."' ";
			$agm2 = doSQLcmd($dbconn, $agm2_sql);
		}

		$ag_sql = "delete from address_group_main where user_id = '".dbSafe($id)."' and access_type ='0'";
		$ag = doSQLcmd($dbconn, $ag_sql);

		$mt_sql = "delete from message_template where user_id = '".dbSafe($id)."' and access_type ='0'";
		$mt = doSQLcmd($dbconn, $mt_sql);

		$quota_sql = "delete from quota_mnt where userid = '".dbSafe($quo_id)."'";
		$quota = doSQLcmd($dbconn, $quota_sql);
		
		$campaign_mgnt_sql = "delete from campaign_mgnt where cby = '".dbSafe($quo_id)."'";
		$campaign_mgnt = doSQLcmd($dbconn, $campaign_mgnt_sql);
		
		$broadcast_sms_file_sql = "delete from broadcast_sms_file where upload_by = '".dbSafe($quo_id)."'";
		$broadcast_sms_file = doSQLcmd($dbconn, $broadcast_sms_file_sql);
		
		$scheduled_sms_sql = "delete from scheduled_sms where created_by = '".dbSafe($quo_id)."'";
		$scheduled_sms = doSQLcmd($dbconn, $scheduled_sms_sql);
		
		$scheduled_sms_mim_sql = "delete from scheduled_sms_mim where created_by = '".dbSafe($quo_id)."'";
		$scheduled_sms_mim = doSQLcmd($dbconn, $scheduled_sms_mim_sql);
		
		$mom_sms_response_sql = "delete from mom_sms_response where cby = '".dbSafe($quo_id)."' and in_use_status = 'no'";
		$mom_sms_response = doSQLcmd($dbconn, $mom_sms_response_sql);
		
		$message_template_sql = "delete from message_template where user_id = '$id'";
		$message_template = doSQLcmd($dbconn, $message_template_sql);
		
		$user_sub_sql = "delete from user_sub where userid = '".dbSafe($quo_id)."'";
		$user_sub = doSQLcmd($dbconn, $user_sub_sql);
		
		if($data_source_id != '0') {
			updateTotal($dbconn, 'ldapserver', 'l_id', $data_source_id, 'totalusers', '2', '1');
		}

		if(($department != "") && ($user_role != "")) {
			if($user_role != 0) {
				updateTotal($dbconn, 'user_role_list', 'role_id', $user_role, 'total_users', '2', '1');
			}
			updateTotal($dbconn, 'department_list', 'department_id', $department, 'total_users', '2', '1');
		}
		insertAuditTrail("User ".dbSafe($id). " deleted.");
	} else {
		echo $deleteUserAccount_msg1;
	}
}

function emptyUserAccount($userid)
{
	global $dbconn, $x;
	$sqlcmd = $ab_sql = $agm_sql = $ag_sql = $mt_sql = $quota_sql = '';

	$emptyUserAccount_msg1 = (string)$x->emptyUserAccount_msg1;

	if(strcmp($userid, "useradmin") != 0 && strcmp($userid, "momadmin") != 0 )
	{
		$department = getUserDepartment($userid);
		$sqlcmd = "delete from user_list where department='".$department."' and userid!='$userid'";
		$getsql = "select id, role_id, data_source_id from user_list where department = '".dbSafe($_SESSION['department'])."' and userid !='$userid'";
		$get = getSQLresult($dbconn, $getsql);

		if(!is_string($get)) {
			if(!empty($get)) {
				for($s=0; $s<count($get); $s++) {
					$get_arr[$s] = $get[$s]['id'];

					$data_source_id = $get[$s]['data_source_id'];
					if($data_source_id != '0') {
						updateTotal($dbconn, 'ldapserver', 'l_id', $data_source_id, 'totalusers', '2', '1');
					}
					$role_id = $get[$s]['role_id'];
					if($role_id != '' || $role_id != 0) {
						updateTotal($dbconn, 'user_role_list', 'role_id', $user_role, 'total_users', '2', '1');
					}
				}
				$get_str = "'" .implode("', '", $get_arr). "'";

				$ab_sql = "delete from address_book where user_id in (".dbSafe($get_str).") or department='".dbSafe($_SESSION['department'])."' ";
				$agm_sql = "select group_id from address_group_main where user_id in (" .dbSafe($get_str). ") or department='".dbSafe($_SESSION['department'])."'";
				$agm = getSQLresult($dbconn, $agm_sql);

				if(!is_string($agm_sql) && !empty($agm_sql)) {
					for($d=0; $d<count($agm); $d++) {
						$agm_id = $agm[$d]['group_id'];
						$agm2_sql = " delete from address_group where main_id = '" .dbSafe($agm_id). "' ";
						$agm2 = doSQLcmd($dbconn, $agm2_sql);
					}
				}

				$ag_sql = "delete from address_group_main where user_id in (" .dbSafe($get_str). ") or department = '".dbSafe($_SESSION['department'])."' ";
				$mt_sql = "delete from message_template where user_id in (" .dbSafe($get_str). ") or department = '".dbSafe($_SESSION['department'])."' ";
				$quota_sql ="delete from quota_mnt where userid != 'useradmin' and userid != 'momadmin' ";
			}
		}
	}
	else
	{
		$sqlcmd = "delete from user_list where department!='0' and userid!='useradmin' and userid != 'momadmin'";
		$ab_sql = "delete from address_book where department !='0'";
		$ag_sql = "delete from address_group_main where department !='0'";
		$mt_sql = "delete from message_template where department !='0'";
		$quota_sql = "delete from quota_mnt where userid!='useradmin' and userid != 'momadmin'";

		$agm_sql = "delete from address_group where department !='0'";
		$agm = doSQLcmd($dbconn, $agm_sql);

		$sql_ldap = "update ldapserver set totalusers=0";
		$sql_role = "update user_role_list set total_users=0";
		doSQLcmd($dbconn, $sql_ldap);
		doSQLcmd($dbconn, $sql_role);
	}

	$row = doSQLcmd($dbconn, $sqlcmd);
	$ab = doSQLcmd($dbconn, $ab_sql);
	$ag = doSQLcmd($dbconn, $ag_sql);
	$mt = doSQLcmd($dbconn, $mt_sql);
	$quota = doSQLcmd($dbconn, $quota_sql);
	
	$campaign_mgnt_sql = "delete from campaign_mgnt where cby = '".dbSafe($userid)."'";
	$campaign_mgnt = doSQLcmd($dbconn, $campaign_mgnt_sql);
	
	$broadcast_sms_file_sql = "delete from broadcast_sms_file where upload_by = '".dbSafe($userid)."'";
	$broadcast_sms_file = doSQLcmd($dbconn, $broadcast_sms_file_sql);
	
	$scheduled_sms_sql = "delete from scheduled_sms where created_by = '".dbSafe($userid)."'";
	$scheduled_sms = doSQLcmd($dbconn, $scheduled_sms_sql);
	
	$scheduled_sms_mim_sql = "delete from scheduled_sms_mim where created_by = '".dbSafe($userid)."'";
	$scheduled_sms_mim = doSQLcmd($dbconn, $scheduled_sms_mim_sql);
	
	$mom_sms_response_sql = "delete from mom_sms_response where cby = '".dbSafe($userid)."' and in_use_status = 'no'";
	$mom_sms_response = doSQLcmd($dbconn, $mom_sms_response_sql);
	
	$message_template_sql = "delete from message_template where user_id in ( select id from user_list where userid = '$userid')";
	$message_template = doSQLcmd($dbconn, $message_template_sql);
		
	updateTotal($dbconn, 'department_list', 'department_id', $_SESSION['department'], 'total_users', '2', $row);
}

function listAssign(){
  global $dbconn;

  $cmd = "SELECT DISTINCT id, userid FROM user_list WHERE lower(userid) != 'useradmin' and lower(userid) != 'momadmin'";
  $res = getSQLresult($dbconn,$cmd);
//error_log("AS".sprintf("1%09d",getSequence($dbconn,'assign_note_assign_id_seq')));
  echo json_encode($res);
}

function checkChangePwd($password,$confirmpwd){
	global $x;
	$status = 1;
	if(strlen($password) == 0 && strlen($confirmpwd) == 0){
		$status = 1;
	}
	elseif(!txvalidator($password,TX_STRING,"ALL")){
		$status = 0;		
	}
	elseif(!validateSize($x->user_pwd,$password,"PWD")){
		$status = 0;
	}
	elseif(strcmp($password,$confirmpwd) != 0){
		$status = 0;
	}

	return $status;
}

?>
