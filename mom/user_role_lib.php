<?php
require_once('lib/commonFunc.php');

$userid = strtolower($_SESSION['userid']);
$id = @$_REQUEST['id'];
$mode = @$_REQUEST['mode'];
$role = @$_REQUEST['user_role'];
$department = @$_REQUEST['department'];
$access = @$_REQUEST['access'];
$user_type = @$_REQUEST['user_type'];
$x = GetLanguage("user_account",$lang);
$x_role = GetLanguage("user_role",$lang);
$ThisUserUserType = getUserType( $userid );

//$mode = "getUserRoleList";

switch ($mode) {
	case "getUserRoleList":
        getUserRoleList($department, $ThisUserUserType);
        break;
	case "listUserRole":
        listUserRole($userid);
        break;
	case "addUserRole":
        addUserRole($userid, $role, $department, $access);
        break;
	case "editUserRole":
        editUserRole($id);
        break;
	case "saveUserRole":
        saveUserRole($userid, $id, $role, $department, $access);
        break;
	case "deleteUserRole":
        deleteUserRole($id);
        break;
	case "emptyUserRole":
        emptyUserRole($userid, $department);
        break;
	case "getAccessRightsList":
        getAccessRightsList($lang, $user_type);
        break;
	case "retrieveAccessRights":
        retrieveAccessRights($role);
        break;
    default:
        die('Invalid Command');
}

function getUserRoleList($department, $ThisUserUserType )
{
	global $dbconn, $x;

	$getUserRoleList_msg1 = (string)$x->getUserRoleList_msg1;
	$getUserRoleList_msg2 = (string)$x->getUserRoleList_msg2;
	
	if( $ThisUserUserType == "admin" ){
		$dis_allowed = array();
	}else{
		$dis_allowed = array( "bu_admin_user_role" );
	}
	
	//print_r( $dis_allowed );
	//die;
	if($department == 0) {
		$sqlcmd = "select role_id, user_role, department_id, department_list.department as department from user_role_list left outer join department_list on (user_role_list.department = department_list.department_id) order by user_role asc ";
	} else {
		$sqlcmd = "select role_id, user_role, department_id, department_list.department as department from user_role_list left outer join department_list on (user_role_list.department = department_list.department_id) 
					where user_role_list.department = '0' or user_role_list.department='".dbSafe($department)."'";
	}

	$row = getSQLresult($dbconn, $sqlcmd);
	
	foreach( $row as $no => $data ){
		
		if( in_array( $data['user_role'], $dis_allowed ) ){
			unset( $row[$no] );
		}
		
	}
	
	//print_r( $row );
	//die;
	
	if(empty($row)) {
		error_log($getUserRoleList_msg1. " '".dbSafe($_SESSION['userid']). "' \n".$getUserRoleList_msg2);
	} else {
		echo json_encode($row);
	}
}

function listUserRole($userid)
{
	global $dbconn, $x;

	$listUserRole_msg1 = (string)$x->listUserRole_msg1;
	$db_err = (string)$x->db_err;

	if(strcmp($userid, "useradmin") != 0 && strcmp($userid, "momadmin") != 0 )
	{
		$department = getUserDepartment($userid);
		$sqlcmd = "select role_id, user_role, department_list.department as department, department_id from user_role_list left outer join department_list on (user_role_list.department = department_list.department_id) 
					where user_role_list.department='".dbSafe($department)."' or user_role_list.department='0' order by user_role";
	}
	else
	{
		$sqlcmd = "select role_id, user_role, department_list.department as department, department_id from user_role_list left outer join department_list on (user_role_list.department = department_list.department_id) order by user_role";
	}
	
	$result = pg_query($dbconn, $sqlcmd);
	if(!$result)
	{
		error_log("listUserRole: ".$db_err." (" .$sqlcmd. ") -- " .pg_last_error($dbconn));
		echo $db_err;
	}
	else
	{
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			array_push($result_array,Array(
				'<a href="#myRole" data-bs-toggle="modal" data-id="'.$row['role_id'].'">'.$row['user_role'].'<i class="fa fa-pencil-square-o fa-fw"></i></a>',
				htmlspecialchars($row['department'],ENT_QUOTES),
				// '<input type="checkbox" id="no" name="no" value="'.$row['role_id'].'">'
				// assmi
				'<input type="checkbox" class="user_checkbox" id="no" name="no" value="'.$row['role_id'].'">'
				// assmi
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function addUserRole($userid, $user_role, $department, $access)
{
	global $dbconn, $x, $x_role;
	$data = array();

	$addUserRole_msg1 = (string)$x->addUserRole_msg1;
	$already_exist = (string)$x->already_exist;
	$success_created = (string)$x->success_created;
	$unsuccess_created = (string)$x->unsuccess_created;
	$db_err = (string)$x->db_err;

	if(!txvalidator($user_role,TX_STRING,"_")){
		$data['flag'] = 0;
		$data['status'] = (string)$x_role->invalid_user_role;
		$data['field'] = "user_role";
	}else if(!validateSize($x_role->userrole,$user_role,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "user_role";
	}else{
		if (empty($access)) { $access = array(); }
		$access_string = implode(",", $access);

		$query_sql = "select role_id from user_role_list where user_role='".dbSafe(strtolower($user_role))."'";
		$query_row = getSQLresult($dbconn, $query_sql);
		if(is_string($query_row))
		{
			$data['flag'] = 2;
			$data['status'] = $db_err;
			error_log("addUserRole: ".$db_err." (" .$query_sql. ") -- " .pg_last_error($dbconn));
		}
		else
		{
			if(!empty($query_row))
			{
				$data['flag'] = 2;
				$data['status'] = $addUserRole_msg1." '" .htmlspecialchars($user_role,ENT_QUOTES). "' ".$already_exist;
			}
			else
			{
				$role_id = getSequenceID($dbconn,'user_role_list_role_id_seq');
				$sqlcmd = "insert into user_role_list (role_id, user_role, access_string, department, total_users, created_by) 
							values ('".dbSafe($role_id)."','".dbSafe(strtolower($user_role))."', '".dbSafe($access_string)."', '".dbSafe($department)."', '0', '".dbSafe($userid)."') ";

				$row = doSQLcmd($dbconn, $sqlcmd);
				if($row == 0)
				{
					$data['flag'] = 2;
					$data['status'] = $addUserRole_msg1." '" .htmlspecialchars($user_role,ENT_QUOTES). "' ".$unsuccess_created;					
				}else{
					$data['flag'] = 1;
					insertAuditTrail( "Add New User Role" );
				}
			}
		}		
	}
	echo json_encode($data);
}

function editUserRole($id)
{
	global $dbconn, $x;

	$editUserRole_msg1 = (string)$x->editUserRole_msg1;
	$editUserRole_msg2 = (string)$x->editUserRole_msg2;
	$db_err = (string)$x->db_err;

	$sqlcmd = "select user_role, access_string, department from user_role_list where role_id='".dbSafe($id)."'";
	$row = getSQLresult($dbconn, $sqlcmd);
	
	if(is_string($row))
	{
		error_log("editUserRole: ".$db_err." (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		echo $db_err;
	}
	else
	{
		if(empty($row))
		{
			echo $editUserRole_msg1." '" .htmlspecialchars($id,ENT_QUOTES). "' ".$editUserRole_msg2;
		}
		else
		{
			$result_array = array();
			$result_array['user_role'] = $row[0]['user_role'];
			$result_array['access_string'] = $row[0]['access_string'];
			$result_array['department'] = $row[0]['department'];
			echo json_encode($result_array);
		}
	}
}

function saveUserRole($userid, $id, $user_role, $department, $access)
{
	global $dbconn, $x;
	$data = array();

	$saveUserRole_msg1 = (string)$x->saveUserRole_msg1;
	$saveUserRole_msg2 = (string)$x->saveUserRole_msg2;
	$unsuccess = (string)$x->unsuccess;
	$db_err = (string)$x->db_err;
	
	if (empty($access)) { $access = array(); }
	$access_string = implode(",", $access);

	$sqlcmd = "update user_role_list set access_string='".dbSafe($access_string)."', department='".dbSafe($department)."', modified_by='".dbSafe($userid)."', modified_dtm='now()' where role_id='".dbSafe($id)."'";

	$row = doSQLcmd($dbconn, $sqlcmd);
	if(empty($row))
	{
		$data['flag'] = 2;
		$data['status'] = $saveUserRole_msg1." '" .htmlspecialchars($id,ENT_QUOTES). "' ".$unsuccess;
	}else{
		$data['flag'] = 1;
		insertAuditTrail( "Edit User Role" );
	}
	echo json_encode($data);	
}

function deleteUserRole($id)
{
	global $dbconn, $x;

	$deleteUserRole_msg1 = (string)$x->deleteUserRole_msg1;
	$db_err = (string)$x->db_err;

	$sqlcmd = "delete from user_role_list where role_id='".dbSafe($id)."'";
	$row = doSQLcmd($dbconn, $sqlcmd);
	if(!empty($row))
	{
		$update_sql = "update user_list set user_role='0' where user_role='".dbSafe($id)."'";
		$update = doSQLcmd($dbconn, $update_sql);
	} else {
		echo $deleteUserRole_msg1;
	}
	
	insertAuditTrail( "Delete User Role" );
}

function emptyUserRole($userid, $department)
{
	global $dbconn, $x;
	
	$emptyUserRole_msg1 = (string)$x->emptyUserRole_msg1;
	$db_err = (string)$x->db_err;

	if(strcmp($userid, "useradmin") != 0 && strcmp($userid, "momadmin") != 0 )
	{
		$getsql = "select role_id from user_role_list where department='".dbSafe($department)."'";
		$sqlcmd = "delete from user_role_list where department='".dbSafe($department)."'";
	}
	else
	{
		$getsql = "select role_id from user_role_list";
		$sqlcmd = "delete from user_role_list";
	}

	$getrow = getSQLresult($dbconn, $getsql);
	if(is_string($getrow))
	{
		error_log("emptyUserRole: ".$db_err." (" .$getsql. ") -- " .pg_last_error($dbconn));
		echo $db_err;
	}
	else
	{
		if(!empty($getrow))
		{
			for($i=0; $i<count($getrow); $i++)
			{
				$role_id = $getrow[$i]['role_id'];
				$updatesql = "update user_list set user_role='0' where user_role='".dbSafe($role_id)."'";
				$updaterow = doSQLcmd($dbconn, $updatesql);
			}
		}
	}

	$row = doSQLcmd($dbconn, $sqlcmd);
	updateTotal($dbconn, 'department_list', 'department_id', $department, 'total_users', '2', $row);
	
	insertAuditTrail( "Empty User Role" );
}

function getAccessRightsList($lang, $user_type = '')
{
	global $dbconn, $x;
	$getAccessRightsList_msg1 = (string)$x->getAccessRightsList_msg1;

	$x2 = GetLanguage("f_system_functions",$lang);
	$xml3_department_administrator = $x2->department_administrator;
	$xml3_user_management = $x2->user_management;
	$xml3_global_address_book = $x2->global_address_book;
	$xml3_personal_address_book = $x2->personal_address_book;
	$xml3_global_message_template = $x2->global_message_template;
	$xml3_personal_message_template = $x2->personal_message_template;
	$xml3_mim_message_template = $x2->mim_message_template;
	$xml3_global_mim_message_template = $x2->global_mim_message_template;
	$xml3_send_sms = $x2->send_sms;
	$xml3_send_sms_upload_status = $x2->send_sms_upload_status;
	$xml3_common_inbox = $x2->common_inbox;
	$xml3_personal_inbox = $x2->personal_inbox;
	$xml3_personal_sent_log = $x2->personal_sent_log;
	$xml3_personal_unsent_log = $x2->personal_unsent_log;
	$xml3_personal_queue_log = $x2->personal_queue_log;
	$xml3_export_personal_inboxlogs = $x2->export_personal_inboxlogs;
	$xml3_delete_personal_inboxlogs = $x2->delete_personal_inboxlogs;
	$xml3_unsubscribe_list = $x2->unsubscribe_list;
	$xml3_quota_management = $x2->quota_management;
	$xml3_keyword_management = $x2->keyword_management;
	$xml3_system_configuration = $x2->system_configuration;
	$xml3_language_setup = $x2->language_setup;
	$xml3_change_personal_password = $x2->change_personal_password;
	$xml3_campaign = $x2->campaign;
	$xml3_p_report = $x2->p_report;
	$xml3_dept_report = $x2->dept_report;
	$xml3_users_report = $x2->users_report;
	$xml3_http_log = $x2->http_log;
	$xml3_incident_report = $x2->incident_report;
	$xml3_incoming_report = $x2->incoming_report;
	$xml3_global_api_report = $x2->global_api_report;
	$xml3_api_report = $x2->api_report;
	$xml3_analytic = $x2->analytic;
	$xml3_survey_report = $x2->survey_report;
	$xml3_invoice = $x2->invoice;
	// assmi
	$xml3_application_management = $x2->application_management;
	$xml3_audit_trail = $x2->audit_trail;
	// assmi
	$xml3_shortened_url = $x2->shortened_url;
	$xml3_user_transfer = $x2->user_transfer;
	
	$UserType = getUserType();
	if( strtolower($UserType) == "admin" ){
		//admin can create all right
		$cond = "function_id >= 1 and ( function_id <= 16 or function_id >= 41 )";
	}elseif( strtolower($UserType) == "bu" ){
		//bu only can create user right
		$cond = "function_id >= 1 and ( function_id <= 16 or function_id >= 41 ) and function_id not in ( 1, 2, 16, 45, 47, 48, 57, 61, 65, 68, 73 )";
	}elseif( strtolower($UserType) == "user" ){
		//user cant create user right
		$cond = "function_id == 0";
	}
	
	if( strtolower($UserType) == "admin" && strtolower( $user_type ) == "bu" ){
		$cond .= " and function_id not in ( 45 )";
	}elseif( strtolower($UserType) == "admin" && strtolower( $user_type ) == "user"  ){
		// $cond .= " and function_id not in ( 1, 2, 16, 45, 47, 48, 56, 57 )";
		$cond .= " and function_id not in ( 1, 2, 16, 45, 47, 48, 57, 61, 65, 68, 73 )";
	}
	
	//$cond = "function_id >= 1 and function_id <= 16 or function_id >= 41 ";
	
	$sqlcmd = "select function_id, function, display_order, display_type from system_functions where " .dbSafe($cond). " order by display_order ";

	$row = getSQLresult($dbconn, $sqlcmd);
	if(is_string($row))
	{
		return;
	}
	else
	{
		if(empty($row))
		{
			error_log("SERIOUS System Error -- The List Of Access Rights Is Not Found In Table 'system_functions'");
			echo $getAccessRightsList_msg1;
		}
		else
		{
			$access_rights_output = "";
			for($c=0; $c<count($row); $c++)
			{
				$function_id = $row[$c]['function_id'];
				$function = $row[$c]['function'];
				$display_order = $row[$c]['display_order'];
				$display_type = $row[$c]['display_type'];
				
				$function_list[] = $function;
				
				if($function == "Department Administrator"){
				$function_description1 = $xml3_department_administrator; }
				if($function == "User Management"){
				$function_description1 = $xml3_user_management; }
				if($function == "Global Address Book"){
				$function_description1 = $xml3_global_address_book; }
				if($function == "Personal Address Book"){
				$function_description1 = $xml3_personal_address_book; }
				if($function == "Global Message Template"){
				$function_description1 = $xml3_global_message_template; }
				if($function == "Personal Message Template"){
				$function_description1 = $xml3_personal_message_template; }
				if($function == "MIM Message Template"){
				$function_description1 = $xml3_mim_message_template; }
				if($function == "Global MIM Message Template"){
				$function_description1 = $xml3_global_mim_message_template; }
				if($function == "Send SMS"){
				$function_description1 = $xml3_send_sms; }
				if($function == "File Upload Status"){
				$function_description1 = $xml3_send_sms_upload_status; }
				if($function == "Common Inbox"){
				$function_description1 = $xml3_common_inbox; }
				if($function == "Personal Inbox"){
				$function_description1 = $xml3_personal_inbox; }
				if($function == "Personal Sent Log"){
				$function_description1 = $xml3_personal_sent_log; }
				if($function == "Personal Unsent Log"){
				$function_description1 = $xml3_personal_unsent_log; }
				if($function == "Personal Queue Log"){
				$function_description1 = $xml3_personal_queue_log; }
				if($function == "Export Personal Inbox/Logs"){
				$function_description1 = $xml3_export_personal_inboxlogs; }
				if($function == "Delete Personal Inbox/Logs"){
				$function_description1 = $xml3_delete_personal_inboxlogs; }
				if($function == "Unsubscribe List"){
				$function_description1 = $xml3_unsubscribe_list; }
				if($function == "Quota Management"){
				$function_description1 = $xml3_quota_management; }
				if($function == "Keyword Management"){
				$function_description1 = $xml3_keyword_management; }
				if($function == "System Configuration"){
				$function_description1 = $xml3_system_configuration; }
				if($function == "Language Setup"){
				$function_description1 = $xml3_language_setup; }
				if($function == "Change Personal Password"){
				$function_description1 = $xml3_change_personal_password; }
				if($function == "Campaign"){
				$function_description1 = $xml3_campaign; }
				if($function == "Personal Report"){
				$function_description1 = $xml3_p_report; }
				if($function == "Departments Report"){
				$function_description1 = $xml3_dept_report; }
				if($function == "Users Report"){
				$function_description1 = $xml3_users_report; }
				if($function == "HTTP Log"){
				$function_description1 = $xml3_http_log; }
				if($function == "Incoming Message Report"){
				$function_description1 = $xml3_incoming_report; }
				if($function == "Interactive Campaign Report"){
				$function_description1 = $xml3_survey_report; }
				if($function == "User Transfer"){
				$function_description1 = $xml3_user_transfer; }
				if($function == "API Report"){
				$function_description1 = $xml3_global_api_report; }
				if($function == "View API Report"){
				$function_description1 = $xml3_api_report; }
				if($function == "Analytic"){
				$function_description1 = $xml3_analytic; }
				if($function == "Shortened URL"){
					$function_description1 = $xml3_shortened_url; }
				if($function == "Invoice"){
					$function_description1 = $xml3_invoice; }
				// assmi
				if($function == "Application Management"){
					$function_description1 = $xml3_application_management; }
				if($function == "Audit Trail"){
					$function_description1 = $xml3_audit_trail; }
				// assmi
				if($function == "Incident Report"){
					$function_description1 = $xml3_incident_report; }
				if($display_type == 1)
				{
					$access_rights_output .= "	<tr>
													<td width=\"10%\" class=\"text-center\">
														" .($c+1). "
													</td>
													<td width=\"78%\" class=\"text-left\">
														" .$function_description1. "
													</td>
													<td width=\"10%\" class=\"text-center\">
														<input type=\"checkbox\" name=\"access[]\" value=\"" .$function_id. "\">
													</td>
												</tr>
											";
				}
				else if($display_type == 2)
				{
					$access_rights_output .= "	<tr>
													<td width=\"10%\" class=\"text-center\">
														" .($c+1). "
													</td>
													<td width=\"78%\" class=\"text-left\">
														" .$function_description1. "
													</td>
													<td width=\"10%\" class=\"text-center\">
														<input type=\"checkbox\" name=\"access[]\" id=\"access1\" value=\"" .$function_id. "\">
													</td>
												</tr>
											";
				}
				else if($display_type == 12)
				{
					$access_rights_output .= "	<tr>
													<td width=\"10%\" class=\"text-center\">
														" .($c+1). "
													</td>
													<td width=\"78%\" class=\"text-left\">
														" .$function_description1. "
													</td>
													<td width=\"10%\" class=\"text-center\">
														<input type=\"checkbox\" name=\"access[]\" id=\"access2\" value=\"" .$function_id. "\">
													</td>
												</tr>
												<tr>
													<td class=\"text-center\">
														<div id=\"div_" .$function_id. "_td\" class=\"display-none-assmi\">
															&nbsp;
														</div>
													</td>
													<td colspan=\"2\" class=\"text-center\">
														<div id=\"div_" .$function_id. "\" class=\"display-none-assmi\">
															<table border=\"0\" width=\"100%\">
											" .getDepartmentAccess($function_id,$lang). "
															</table>
														</div>
													</td>
												</tr>
											";
				}
				else if(($display_type == 3) || ($display_type == 6) || ($display_type == 8) || ($display_type == 9))
				{
					$access_rights_output .= "	<tr>
													<td width=\"10%\" class=\"text-center\">
														" .($c+1). "
													</td>
													<td width=\"78%\" class=\"text-left\">
														" .$function_description1. "
													</td>
													<td width=\"10%\" class=\"text-center\">
														<input type=\"checkbox\" name=\"access[]\" value=\"" .$function_id. "\">
													</td>
												</tr>
												<tr>
													<td class=\"text-center\">
														<div id=\"div_" .$function_id. "_td\" class=\"display-none-assmi\">
															&nbsp;
														</div>
													</td>
													<td colspan=\"2\" class=\"text-center\">
														<div id=\"div_" .$function_id. "\" class=\"display-none-assmi\">
															<table border=\"0\" width=\"100%\">
											" .getDepartmentAccess($function_id,$lang). "
															</table>
														</div>
													</td>
												</tr>
											";
				}
			}
		}
	}
	
	//print_r( $function_list );
	//die;
	
	echo $access_rights_output;
}
//End getAccessRightsList

//Get The Access Rights For Department Level
function getDepartmentAccess($parent_id,$lang)
{
	global $dbconn;
	global $x;
	$getDepartmentAccess_msg1 = (string)$x->getDepartmentAccess_msg1;
	
	$x2 = GetLanguage("f_system_functions",$lang);
	$xml1_edit_global_address_book = $x2->edit_global_address_book;
	$xml1_edit_global_message_template = $x2->edit_global_message_template;
	$xml1_global_inbox = $x2->global_inbox;
	$xml1_global_sent_log = $x2->global_sent_log;
	$xml1_global_unsent_log = $x2->global_unsent_log;
	$xml1_global_queue_log = $x2->global_queue_log;
	$xml1_export_global_inboxlogs = $x2->export_global_inboxlogs;
	$xml1_delete_global_inboxlogs = $x2->delete_global_inboxlogs;
	$xml1_create_user_account = $x2->create_user_account;
	$xml1_edit_user_account = $x2->edit_user_account;
	$xml1_create_user_role = $x2->create_user_role;
	$xml1_edit_user_role = $x2->edit_user_role;
	$xml1_create_department = $x2->create_department;
	$xml1_edit_department = $x2->edit_department;
	$xml1_access_log = $x2->access_log;
	$xml1_http_log = $x2->http_log;

	$sqlcmd = "select function_id, function, display_order, display_type from system_functions where parent_id='".dbSafe($parent_id)."' and display_type!='13' order by display_order";
	$row = getSQLresult($dbconn, $sqlcmd);
	if(is_string($row))
	{
		return;
	}
	else
	{
		if(empty($row))
		{
			error_log("SERIOUS System Error -- The List Of Department Level Access Rights Is Not Found\n Please Contact The System Administrator Immediately!");
			echo $getDepartmentAccess_msg1;
		}
		else
		{
			$output = "";
			for($i=0; $i<count($row); $i++)
			{

				if ($row[$i]['function'] == "Create User Account"){
				$function_description = $xml1_create_user_account;}
				if ($row[$i]['function'] == "Edit User Account"){
				$function_description = $xml1_edit_user_account;}
				if ($row[$i]['function'] == "Create User Role"){
				$function_description = $xml1_create_user_role;}
				if ($row[$i]['function'] == "Edit User Role"){
				$function_description = $xml1_edit_user_role;}
				if ($row[$i]['function'] == "Create Department"){
				$function_description = $xml1_create_department;}
				if ($row[$i]['function'] == "Edit Department"){
				$function_description = $xml1_edit_department;}
				if ($row[$i]['function'] == "Access Log"){
				$function_description = $xml1_access_log;}
				if ($row[$i]['function'] == "Edit Global Address Book"){
				$function_description = $xml1_edit_global_address_book;}
				if ($row[$i]['function'] == "Edit Global Message Template"){
				$function_description = $xml1_edit_global_message_template;}
				if ($row[$i]['function'] == "Global Inbox"){
				$function_description = $xml1_global_inbox;}
				if ($row[$i]['function'] == "Global Sent Log"){
				$function_description = $xml1_global_sent_log;}
				if ($row[$i]['function'] == "Global Unsent Log"){
				$function_description = $xml1_global_unsent_log;}
				if ($row[$i]['function'] == "Global Queue Log"){
				$function_description = $xml1_global_queue_log;}
				if ($row[$i]['function'] == "Export Global Inbox/Logs"){
				$function_description = $xml1_export_global_inboxlogs;}
				if ($row[$i]['function'] == "Delete Global Inbox/Logs"){
				$function_description = $xml1_delete_global_inboxlogs;}
				if ($row[$i]['function'] == "HTTP Log"){
				$function_description = $xml1_http_log;}


				$output .= "	<tr>
									<td class=\"text-center\" width=\"10%\">
										-
									</td>
									<td class=\"text-left\" width=\"80%\">
										" .$function_description. "
									</td>
									<td class=\"text-center\" width=\"10%\">
										<input type=\"checkbox\" name=\"access[]\" value=\"" .$row[$i]['function_id']. "\" >
									</td>
								</tr>
							";
			}
			return $output;
		}
	}
}

function retrieveAccessRights($role)
{
	global $dbconn, $x;

	$retrieveAccessRights_msg1 = (string)$x->retrieveAccessRights_msg1;
	$retrieveAccessRights_msg2 = (string)$x->retrieveAccessRights_msg2;
	$db_err = (string)$x->db_err;

	if (strlen($role) == 0 ) {
		echo $retrieveAccessRights_msg1;
	}

	$sqlcmd = "select access_string from user_role_list where role_id='".dbSafe($role)."'";
	$row = getSQLresult($dbconn, $sqlcmd);
	
	if(is_string($row))
	{
		error_log("retrieveAccessRights: ".$db_err." (" .$sqlcmd. ") -- " .pg_last_error($dbconn));
		echo $db_err;
	}
	else
	{
		if(empty($row))
		{
			echo $retrieveAccessRights_msg2; //The Access Rights Of The User Role Not Found
		}
		else
		{
			$result_array = array();
			$result_array['access_string'] = $row[0]['access_string'];
			echo json_encode($result_array);
		}
	}
}
?>
