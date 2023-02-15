<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once('lib/commonFunc.php');

$id = @$_REQUEST['id'];
$mode = @$_REQUEST['mode'];
$userid = strtolower($_SESSION['userid']);
$id_of_user = getUserID($userid);
$department = $_SESSION['department'];
$contact = @$_REQUEST['contact'];
$mobile = @$_REQUEST['mobile'];
$email = @$_REQUEST['email'];
$group = @$_REQUEST['group'];
$modem = @$_REQUEST['modem'];
$access_type = @$_REQUEST['access_type'];
$file = @$_FILES['file']['tmp_name'];

switch ($mode) {
	case "listAddressBook":
        listAddressBook($userid);
        break;
	case "addAddressBook":
        addAddressBook($userid, $id_of_user, $department, $contact, $mobile, $group, $modem, $email);
        break;
	case "editAddressBook":
        editAddressBook($id);
        break;
	case "saveAddressBook":
        saveAddressBook($userid, $id_of_user, $department, $id, $contact, $mobile, $group, $modem, $email);
        break;
	case "deleteAddressBook":
        deleteAddressBook($userid, $id);
        break;
	case "listGlobalBook":
        listGlobalBook($userid);
        break;
	case "addGlobalBook":
        addGlobalBook($userid, $id_of_user, $department, $contact, $mobile, $group, $modem, $email);
        break;
	case "editGlobalBook":
		editGlobalBook($id);
		break;
	case "saveGlobalBook":
        saveGlobalBook($userid, $id_of_user, $department, $id, $contact, $mobile, $email, $group, $modem);
        break;
	case "deleteGlobalBook":
		deleteGlobalBook($id);
		break;
	case "getAddressGroup":
        getAddressGroup($userid);
        break;
	case "getGlobalGroup":
		getGlobalGroup($userid, $department);
		break;
	case 'insertContacts':
		insertContacts($userid, $file, $department, $access_type, @$_FILES);
		break;
	case 'listContacts':
		listContacts($userid, $department, $access_type);
		break;
	case 'deleteContacts':
		deleteContacts($userid, $access_type);
		break;
	case 'addContacts':
		addContacts($userid, $department, $id_of_user, $access_type);
		break;
	case 'emptyGlobalBook':
		emptyGlobalBook($userid);
		break;
	case 'emptyAddressBook':
		emptyAddressBook($userid);
		break;
	default:
		die('Invalid Command');
}

function getAddressGroup($userid)
{
	global $dbconn, $lang;
	$data = array();

	$msgstr = GetLanguage("lib_address_book",$lang);
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "select group_id, group_name from address_group_main where created_by='".dbSafe($userid)."' and access_type='0'";
	$result = pg_query($dbconn, $sqlcmd);
	if(!$result)
	{
		error_log("getAddressGroup: ".$db_err." (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		echo $db_err;
	}
	else
	{
		while($row = pg_fetch_assoc($result)) {
			$data[] = $row;
		}
		echo json_encode($data);
	}
}

function getAddressGroupName($userid)
{
	global $dbconn;
	global $lang;

	$msgstr = GetLanguage("lib_address_book",$lang);
	$db_err = (string)$msgstr->db_err;

	$userid = stripslashes($userid);
	$lower_userid = strtolower($userid);

	$sqlcmd = " select group_name from address_group_main where created_by = '" .dbSafe($lower_userid). "' and access_type = '0' ";
	$result = pg_query($dbconn, $sqlcmd);

	if(!$result)
	{
		error_log("getAddressGroupName: ".$db_err." (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		$_SESSION['error_msg'] = $db_err;
		return;
	}
	else
	{
		$row = pg_fetch_all_columns($result);
	}
	return $row;
}

function getGlobalGroup($userid, $department)
{
	global $dbconn;
	global $lang;
	$data = array();//Added by Wafie

	$msgstr = GetLanguage("lib_address_book",$lang);
	$db_err = (string)$msgstr->db_err;

	$userid = stripslashes($userid);
	$lower_userid = strtolower($userid);

	if(isUserAdmin($userid)){
		$sqlcmd = "SELECT group_id, group_name FROM address_group_main WHERE access_type = '1' ";
	} else{
		$sqlcmd = "select group_id, group_name from address_group_main where (department = '" .
			dbSafe($department). "' or department = '0') and access_type = '1' ";
	}

	$result = pg_query($dbconn, $sqlcmd);
	if(!$result)
	{
		$_SESSION['error_msg'] = $db_err;
		error_log("getGlobalGroup: ".$db_err." (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
	}
	else
	{
		while($row = pg_fetch_assoc($result)) {
			$data[] = $row;
		}
	}
	echo json_encode($data);
}

function getGlobalGroupName($userid, $department)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_address_book",$lang);
	$db_err = (string)$msgstr->db_err;

	$userid = stripslashes($userid);
	$lower_userid = strtolower($userid);
	if(isUserAdmin($lower_userid))
	{
		$sqlcmd = " select group_name from address_group_main where access_type = '1' ";
	}
	else
	{
		$sqlcmd = " select group_name from address_group_main where (department = '" .
			dbSafe($department). "' or department = '0') and access_type = '1' ";
	}
	$result = pg_query($dbconn, $sqlcmd);
	if(!$result)
	{
		$_SESSION['error_msg'] = $db_err;
		error_log("getGlobalGroupName: ".$db_err." (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		return;
	}
	else
	{
		$row = pg_fetch_all_columns($result);
	}
	return $row;
}

function getGroupName($dbconn, $sqlcmd)
{
	global $lang;

	$msgstr = GetLanguage("lib_address_book",$lang);
	$db_err = (string)$msgstr->db_err;

	$result = pg_query($dbconn, $sqlcmd);
	if(!$result)
	{
		$_SESSION['error_msg'] = $db_err;
		error_log("getGroupName: ".$db_err." (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		return;
	}
	else
	{
		$row = pg_fetch_all($result);
		if(empty($row))
		{
			return;
		}
		else
		{
			return $row[0]['group_name'];
		}
	}
}

function getGroupIDbyName($userid,$groupname,$department,$access_type)
{
	global $dbconn;

	if(isUserAdmin($userid))
	{
		$sqlcmd = "select group_id from address_group_main where group_name='".dbSafe($groupname)."' and access_type='$access_type' ";
	}
	else
	{
		$sqlcmd = "select group_id from address_group_main where group_name='".dbSafe($groupname)."' and (department='".dbSafe($department)."' or department='0') and access_type='$access_type'";
	}

	$result = pg_query($dbconn, $sqlcmd);
	if( !$result ){
		error_log($sqlcmd . ' -- ' . pg_last_error($dbconn));
		return 0;
	}
	$arr = pg_fetch_all($result);

	if(!empty($arr)){
		if (count($arr) > 0 && $arr[0]['group_id'] != "" ){
			$groupid = $arr[0]['group_id'];
		}else{
			$groupid = '0';
		}
	}else{
		$groupid = '0';
	}	

	return $groupid;
}

function listAddressBook($userid)
{
	global $dbconn, $lang;
	$result_array = array();

	$msgstr = GetLanguage("lib_address_book",$lang);
	$listAddressBook_msg1 = (string)$msgstr->listAddressBook_msg1;
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "select contact_id, contact_name, mobile_numb,email, group_string from address_book where created_by='".dbSafe($userid)."' and access_type!='1' order by contact_name";
	$result = pg_query($dbconn, $sqlcmd);

	for ($i=1; $row = pg_fetch_array($result); $i++){
		$tmp = $row['group_string'];
		if($tmp != "")
		{
			$group_string = "------";

			$tmp_arr = explode(",", $tmp);
			for($b=0; $b<count($tmp_arr); $b++)
			{
				$group_sql = "select group_name from address_group_main where group_id='".dbSafe($tmp_arr[$b])."'";
				$group_name = getGroupName($dbconn, $group_sql);
				if($group_name != "")
				{
					if(strcmp($group_string, "------") == 0)
					{
						$group_string = htmlspecialchars($group_name, ENT_QUOTES);
					}
					else
					{
						$group_string .= ", " .htmlspecialchars($group_name, ENT_QUOTES);
					}
				}
			}
		}
		else
		{
			$group_string = "------";
		}

		array_push($result_array,Array(
			'<a href="#myAdbk" data-bs-toggle="modal" data-id="'.$row['contact_id'].'">'.htmlspecialchars($row['contact_name'],ENT_QUOTES).'<i class="fa fa-pencil-square-o fa-fw"></i></a>',
			htmlspecialchars($row['mobile_numb']),
			htmlspecialchars($row['email']),
			$group_string,
			// '<input type="checkbox" name="no" value="'.$row['contact_id'].'">'
			// assmi
			'<input type="checkbox" class="user_checkbox" name="no" value="'.$row['contact_id'].'">'
			// assmi
		));
	}

	echo json_encode(Array("data"=>$result_array));
}
function addAddressBook($userid, $id_of_user, $department, $contact, $mobile_numb, $group, $modem_label, $email)
{
	global $dbconn, $lang;
	error_log("addAddressBook: loginID=$userid, userID= $id_of_user, department=$department, contact=$contact, mobile=$mobile_numb, group=$group, email=$email");

	$msgstr = GetLanguage("lib_address_book",$lang);
	$x = GetLanguage("address_book",$lang);

	$addAddressBook_msg1 = (string)$msgstr->addAddressBook_msg1;
	$addAddressBook_msg2 = (string)$msgstr->addAddressBook_msg2;
	$addAddressBook_msg3 = (string)$msgstr->addAddressBook_msg3;
	$contact_str = (string)$msgstr->contact;
	$db_err = (string)$msgstr->db_err;

	$result_array = array();
	$data = array();
	$lower_contact = strtolower($contact);
	$mobile_numb = trim($mobile_numb);
	
	if(!txvalidator($contact,TX_STRING)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_contact;
		$data['field'] = "contact";
		echo json_encode($data);
		die;
	}else if(!validateSize($x->contact_name,$contact,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "contact";
		echo json_encode($data);
		die;
	}else if(!txvalidator($mobile_numb,TX_SGMOBILEPHONE)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_mobile;
		$data['field'] = "mobile";
		echo json_encode($data);
		die;
	}else if(!empty($email) && !txvalidator($email,TX_EMAILADDR)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_email;
		$data['field'] = "email";
		echo json_encode($data);
		die;
	}

	$validateMno = validateMno( $mobile_numb );
	if( $validateMno == "-1" ){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_mobile;
		$data['field'] = "mobile";
		echo json_encode($data);
		die;
	}
	
	$modem_label = trim($modem_label);
	if (!empty($group)) {
		$group_string = implode(",",$group);
	} else {
		$group_string = "";
	}

	$query_sql = "select contact_id from address_book where lower(contact_name)='".dbSafe($lower_contact)."' and created_by='".dbSafe($userid)."' and access_type='0'";
	$query_row = getSQLresult($dbconn, $query_sql);
	
	if(is_string($query_row))
	{
		$data['flag'] = 2;
		$data['status'] = $db_err;
		error_log("addAddressBook: ".$db_err." (" .dbSafe($query_sql). ") -- " .dbSafe(pg_last_error($dbconn)));
	}
	else
	{
		if(!empty($query_row))
		{			
			$data['flag'] = 0;
			$data['status'] = $contact_str." '" .htmlspecialchars($contact,ENT_QUOTES). "' ".$addAddressBook_msg1;
			$data['field'] = "contact";
		}
		else
		{
			$contact_id = getSequenceID($dbconn,'address_book_contact_id_seq');
			$sqlcmd = "insert into address_book (contact_id, contact_name, mobile_numb, user_id, department, group_string, created_by, modem_label, email) values
						('".dbSafe($contact_id)."','".dbSafe($contact)."','".$validateMno."','".dbSafe($id_of_user)."','".dbSafe($department)."',
						'".dbSafe($group_string)."','".dbSafe($userid)."','".dbSafe($modem_label)."', '".dbSafe($email)."')";

			$row = doSQLcmd($dbconn, $sqlcmd);
			if($row != 0)
			{
				$getsql = "select contact_id from address_book where lower(contact_name)='".dbSafe($lower_contact)."' and access_type='0' and created_by='".dbSafe($userid)."'";
				$get = getSQLresult($dbconn, $getsql);

				if(!is_string($get) && !empty($get))
				{
					if (!empty($group)) {
						foreach ($group as $tmp_id) {
							$group_id = getSequenceID($dbconn,'address_group_group_id_seq');
							$updatesql = "insert into address_group (group_id, main_id, contact_id, department, created_by)
											values ('".dbSafe($group_id)."','".dbSafe($tmp_id)."','".dbSafe($get[0]['contact_id'])."','".dbSafe($department)."','".dbSafe($userid)."')";
							doSQLcmd($dbconn, $updatesql);
						}
					}
				}
				$data['flag'] = 1;
			}
			else
			{
				$data['flag'] = 2;
				$data['status'] = $contact_str." '" .htmlspecialchars($contact,ENT_QUOTES). "' ".$addAddressBook_msg3;
			}
		}
	}
	
	echo json_encode($data);
}

function editAddressBook($id)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_address_book",$lang);
	$editAddressBook_msg1 = (string)$msgstr->editAddressBook_msg1;
	$editAddressBook_msg2 = (string)$msgstr->editAddressBook_msg2;
	$db_err = (string)$msgstr->db_err;

	$result_array = array();

	$sqlcmd = "select contact_name, mobile_numb, group_string, modem_label, email from address_book where contact_id='".dbSafe($id)."' and access_type = '0' ";
	$row = getSQLresult($dbconn, $sqlcmd);
	if(is_string($row))
	{
		error_log("editAddressBook: ".$db_err." (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		echo $db_err;
	}
	else
	{
		if(empty($row))
		{
			echo $editAddressBook_msg1." '" .htmlspecialchars($id,ENT_QUOTES). "' ".$editAddressBook_msg2;
		}
		else
		{
			$result_array['contact_name'] = $row[0]['contact_name'];
			$result_array['mobile_numb'] = $row[0]['mobile_numb'];
			$result_array['email'] = $row[0]['email'];
			$result_array['modem_label'] = $row[0]['modem_label'];
			$result_array['group_string'] = trim($row[0]['group_string']);
			echo json_encode($result_array);
		}
	}
}

function saveAddressBook($userid, $id_of_user, $department, $id, $contact, $mobile_numb, $group, $modem_label, $email)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_address_book",$lang);
	$x = GetLanguage("address_book",$lang);

	$saveAddressBook_msg1 = (string)$msgstr->saveAddressBook_msg1;//Changes Made to Contact
	$saveAddressBook_msg2 = (string)$msgstr->saveAddressBook_msg2;//Successfully Saved!
	$saveAddressBook_msg3 = (string)$msgstr->saveAddressBook_msg3;//Unsuccessfull!

	$result_array = array();
	$contact = stripslashes($contact);
	$mobile_numb = trim($mobile_numb);
	
	if(!txvalidator($mobile_numb,TX_SGMOBILEPHONE)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_mobile;
		$data['field'] = "mobile";
		echo json_encode($data);
		die;
	}else if(!txvalidator($email,TX_EMAILADDR)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_email;
		$data['field'] = "email";
		echo json_encode($data);
		die;
	}


	$validateMno = validateMno( $mobile_numb );
	if( $validateMno == "-1" ){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_mobile;
		$data['field'] = "mobile";
		echo json_encode($data);
		die;
	}
	
	if (!empty($group)) {
		$group_str = implode(",",$group);
	} else {
		$group_str = "";
	}

	$sqlcmd = " update address_book set mobile_numb = '" .dbSafe($validateMno). "', group_string = '" .dbSafe($group_str). "',".
			"modem_label = '" . dbSafe($modem_label) . "', " .
			" modified_by = '" .dbSafe($userid). "', modified_dtm = 'now()', email = '".dbSafe($email)."' where contact_id = '" .dbSafe($id). "' and access_type = '0' ";

	$row = doSQLcmd($dbconn, $sqlcmd);
	if($row != 0)
	{
		$tmp_arr = explode(",", $group_str);
		$getsql = "select main_id from address_group where contact_id = '" .dbSafe($id). "' ";
		$get = getSQLresult($dbconn, $getsql);
		if(!is_string($get))
		{
			$valid_arr = array();
			$invalid_arr = array();
			$update_arr = array();
			if(!empty($get))
			{
				for($i=0; $i<count($get); $i++)
				{
					$tmp_id = $get[$i]['main_id'];
					if(!in_array($tmp_id, $tmp_arr))
					{
						array_push($invalid_arr, $tmp_id);
					}
					else
					{
						array_push($valid_arr, $tmp_id);
					}
				}
			}
			for($j=0; $j<count($tmp_arr); $j++)
			{
				$tmp_id = $tmp_arr[$j];
				if(!in_array($tmp_id, $valid_arr))
				{
					array_push($update_arr, $tmp_id);
				}
			}
			for($k=0; $k<count($invalid_arr); $k++)
			{
				$tmp_id = $invalid_arr[$k];
				if($tmp_id != "")
				{
					$updatesql = "delete from address_group where main_id='".dbSafe($tmp_id)."' and contact_id = '" .dbSafe($id). "' ";
					$update = doSQLcmd($dbconn, $updatesql);
				}
			}
			for($l=0; $l<count($update_arr); $l++)
			{
				$tmp_id = $update_arr[$l];
				if($tmp_id != "")
				{
					$group_id = getSequenceID($dbconn,'address_group_group_id_seq');
					$updatesql = "insert into address_group (group_id, main_id, contact_id, department, created_by, modified_by, modified_dtm)
									values ('".dbSafe($group_id)."','".dbSafe($tmp_id)."','".dbSafe($id)."','".dbSafe($department)."','".dbSafe($userid)."','".dbSafe($userid)."','now()')";
					$update = doSQLcmd($dbconn, $updatesql);
				}
			}
			for($m=0; $m<count($valid_arr); $m++)
			{
				$tmp_id = $valid_arr[$m];
				if($tmp_id != "")
				{
					$updatesql = "update address_group set modified_by='".dbSafe($userid)."', modified_dtm='now()' where main_id='".dbSafe($tmp_id)."' and contact_id='".dbSafe($id)."'";
					$update = doSQLcmd($dbconn, $updatesql);
				}
			}
		}
		$data['flag'] = 1;
	}
	else
	{
		$data['flag'] = 2;
		$data['status'] = $saveAddressBook_msg1 ." '" .htmlspecialchars($contact,ENT_QUOTES). "' ". $saveAddressBook_msg3;
	}

	echo json_encode($data);
}

function deleteAddressBook($userid, $id)
{
	global $dbconn;

	$sqlcmd = "delete from address_book where contact_id='".dbSafe($id)."' and access_type='0' and created_by='".dbSafe($userid)."'";
	$res = doSQLcmd($dbconn,$sqlcmd);

	if (!empty($res)) {
		error_log("deleteAddressBook: Database Error: ".$res);
		echo "Database Error";
	}
}

function emptyAddressBook($userid)
{
	global $dbconn;

	$sqlcmd = "delete from address_book where created_by='".dbSafe($userid)."' and access_type='0'";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if (!empty($res)) {
		error_log("emptyAddressBook: Database Error: ".$res);
		echo "Database Error";
	}
}

function listGlobalBook($userid)
{
	global $dbconn, $lang;
	$result_array = array();
	$disable = "";

	$msgstr = GetLanguage("global_address_book",$lang);
	$all_departments = (string)$msgstr->all_departments;

	if(isUserAdmin($userid))
	{
		$sqlcmd = "SELECT contact_id, contact_name, mobile_numb,ab.email, group_string, ab.inc_id, dl.department AS department, ic.channel AS channel FROM address_book ab
		LEFT OUTER JOIN department_list dl ON (ab.department = dl.department_id) LEFT OUTER JOIN incoming_contact ic ON(ab.inc_id = ic.inc_id) WHERE access_type='1'
		ORDER BY contact_name";
	}
	else
	{
		$department = getUserDepartment($userid);
		$sqlcmd = "SELECT contact_id, contact_name, mobile_numb,ab.email, group_string, ab.inc_id, dl.department AS department, ic.channel AS channel FROM address_book ab
		LEFT OUTER JOIN department_list dl ON (ab.department = dl.department_id) LEFT OUTER JOIN incoming_contact ic ON(ab.inc_id = ic.inc_id)
		WHERE (ab.department='".dbSafe($department)."' or ab.department='0') AND access_type='1' ORDER BY contact_name";
	}

	$result = pg_query($dbconn, $sqlcmd);

	for ($i=1; $row = pg_fetch_array($result); $i++){
		if(!$row['inc_id']){
			$inc_id = '';
		} else{
			$inc_id = 'data-inc="'.$row['inc_id'].'" data-channel="'.$row['channel'].'" data-name="'.$row['contact_name'].'"';
		}

		$tmp = $row['group_string'];
		if($tmp != "")
		{
			$group_string = "------";
			$tmp_arr = explode(",", $tmp);
			for($b=0; $b<count($tmp_arr); $b++)
			{
				$group_sql = "select group_name from address_group_main where group_id = '" .dbSafe($tmp_arr[$b]). "' ";
				$group_name = getGroupName($dbconn, $group_sql);
				if($group_name != "")
				{
					if(strcmp($group_string, "------") == 0)
					{
						$group_string = htmlspecialchars($group_name, ENT_QUOTES);
					}
					else
					{
						$group_string .= ", " .htmlspecialchars($group_name, ENT_QUOTES);
					}
				}
			}
		}
		else
		{
			$group_string = "------";
		}

		if (empty($row['department'])) {
			$dept = $all_departments;
			if(!isUserAdmin($userid)) {
				$disable = "disabled";
			}
		} else {
			$dept = htmlspecialchars($row['department'], ENT_QUOTES);
			$disable = "";
		}

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

		array_push($result_array,Array(
			'<a href="#myGlbk" data-bs-toggle="modal" data-id="'.$row['contact_id'].'">'.htmlspecialchars($row['contact_name'],ENT_QUOTES).'<i class="fa fa-pencil-square-o fa-fw"></i></a> '.$img,
			htmlspecialchars($row['mobile_numb']),
			htmlspecialchars($row['email']),
			$group_string,
			$dept,
			'<input type="checkbox" name="no" value="'.$row['contact_id'].'" '.$inc_id.' '.$disable.'/>'
		));
	}

	echo json_encode(Array("data"=>$result_array));
}

function addGlobalBook($userid, $id_of_user, $department, $contact, $mobile_numb, $group, $modem_label, $email)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_address_book",$lang);
	$x = GetLanguage("address_book",$lang);

	$addGlobalBook_msg1 = (string)$msgstr->addGlobalBook_msg1;
	$addGlobalBook_msg2 = (string)$msgstr->addGlobalBook_msg2;
	$addGlobalBook_msg3 = (string)$msgstr->addGlobalBook_msg3;
	$contact_str = (string)$msgstr->contact;
	$db_err = (string)$msgstr->db_err;

	$result_array = array();
	$data = array();
	$lower_contact = strtolower($contact);
	$mobile_numb = trim($mobile_numb);
	$modem_label = trim($modem_label);

	if(!txvalidator($contact,TX_STRING)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_contact;
		$data['field'] = "contact";
		echo json_encode($data);
		die;
	}else if(!validateSize($x->contact_name,$contact,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_contact;
		$data['field'] = "contact";
		echo json_encode($data);
		die;
	}else if(!txvalidator($mobile_numb,TX_SGMOBILEPHONE)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_mobile;
		$data['field'] = "mobile";
		echo json_encode($data);
		die;
	}else if(!empty($email) && !txvalidator($email,TX_EMAILADDR)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_email;
		$data['field'] = "email";
		echo json_encode($data);
		die;
	}

	$validateMno = validateMno( $mobile_numb );
	if( $validateMno == "-1" ){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_mobile;
		$data['field'] = "mobile";
		echo json_encode($data);
		die;
	}

	if (!empty($group)) {
		$group_string = implode(",",$group);
	} else {
		$group_string = "";
	}

	$query_sql = "select contact_id from address_book where lower(contact_name)='".dbSafe($lower_contact)."' and department='".dbSafe($department)."' and access_type='1'";
	$query_row = getSQLresult($dbconn, $query_sql);

	if(is_string($query_row)) {
		$data['flag'] = 2;
		$data['status'] = $db_err;
		error_log("addGlobalBook: ".$db_err." (" .dbSafe($query_sql). ") -- " .dbSafe(pg_last_error($dbconn)));
	} else {
		if(!empty($query_row)) {
			$data['flag'] = 0;
			$data['status'] =  $contact_str." '" .htmlspecialchars($contact,ENT_QUOTES). "' ".$addGlobalBook_msg1; //Already Exists
			$data['field'] = "contact";
		} else {
			$contact_id = getSequenceID($dbconn,'address_book_contact_id_seq');
			$sqlcmd = " insert into address_book (contact_id,contact_name, mobile_numb, user_id, department, group_string, created_by, access_type, modem_label, email ) values ".
						"('" .dbSafe($contact_id). "', '" .dbSafe($contact). "', '" .dbSafe($validateMno). "', '" .dbSafe($id_of_user). "', ".
						"'" .dbSafe($department). "', '" .dbSafe($group_string). "', '" .dbSafe($userid). "', '1', '".dbSafe($modem_label)."', '".dbSafe($email)."') ";

			$row = doSQLcmd($dbconn, $sqlcmd);
			if($row != 0)
			{
				$getsql = "select contact_id from address_book where lower(contact_name)='".dbSafe($lower_contact)."' and access_type='1' and created_by='".dbSafe($userid)."'";
				$get = getSQLresult($dbconn, $getsql);

				if(!is_string($get) && !empty($get))
				{
					if (!empty($group)) {
						foreach ($group as $tmp_id)
						{
							$group_id = getSequenceID($dbconn,'address_group_group_id_seq');
							$updatesql = "insert into address_group (group_id, main_id, contact_id, department, created_by, access_type)
											values ('".dbSafe($group_id)."','".dbSafe($tmp_id)."','".dbSafe($contact_id)."','".dbSafe($department)."','".dbSafe($userid)."','1')";
							doSQLcmd($dbconn, $updatesql);
						}
					}
				}
				$data['flag'] = 1;
			}
			else
			{
				$data['flag'] = 2;
				$data['status'] =  $contact_str. " '" .htmlspecialchars($contact,ENT_QUOTES). "' ". $addGlobalBook_msg3; //unsuccessful
			}
		}
	}
	echo json_encode($data);
}

function editGlobalBook($id)
{
	global $dbconn, $lang;
	$result_array = array();

	$msgstr = GetLanguage("lib_address_book",$lang);
	$editGlobalBook_msg1 = (string)$msgstr->editGlobalBook_msg1;
	$editGlobalBook_msg2 = (string)$msgstr->editGlobalBook_msg2;
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "select contact_name, mobile_numb, email,group_string, modem_label from address_book where contact_id='".dbSafe($id)."' and access_type='1'";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row))
	{
		echo $db_err;
		error_log("editGlobalBook: ".$db_err." (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
	}
	else
	{
		if(empty($row))
		{
			echo $editGlobalBook_msg1." '" .htmlspecialchars($id,ENT_QUOTES). "' ".$editGlobalBook_msg2;
		}
		else
		{
			$result_array['contact_name'] = $row[0]['contact_name'];
			$result_array['mobile_numb'] = $row[0]['mobile_numb'];
			$result_array['email'] = $row[0]['email'];
			$result_array['modem_label'] = $row[0]['modem_label'];
			$result_array['group_string'] = $row[0]['group_string'];
			echo json_encode($result_array);
		}
	}
}

function saveGlobalBook($userid, $id_of_user, $department, $id, $contact, $mobile_numb, $email, $group, $modem_label)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_address_book",$lang);
	$x = GetLanguage("address_book",$lang);

	$saveGlobalBook_msg1 = (string)$msgstr->saveGlobalBook_msg1;
	$saveGlobalBook_msg2 = (string)$msgstr->saveGlobalBook_msg2;
	$saveGlobalBook_msg3 = (string)$msgstr->saveGlobalBook_msg3;
	if (!empty($group)) {
		$group_str = implode(",",$group);
	} else {
		$group_str = "";
	}

	$result_array = array();
	$data = array();
	$contact = stripslashes($contact);
	$mobile_numb = trim($mobile_numb);

	if(!txvalidator($mobile_numb,TX_SGMOBILEPHONE)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_mobile;
		$data['field'] = "mobile";
		echo json_encode($data);
		die;
	}else if(!txvalidator($email,TX_EMAILADDR)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_email;
		$data['field'] = "email";
		echo json_encode($data);
		die;
	}

	$validateMno = validateMno( $mobile_numb );
	if( $validateMno == "-1" ){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_mobile;
		$data['field'] = "mobile";
		echo json_encode($data);
		die;
	}

	$sqlcmd = "update address_book set mobile_numb='".dbSafe($validateMno)."', email='".dbSafe($email)."', group_string='".dbSafe($group_str)."',
				modified_by='".dbSafe($userid)."', modem_label='".dbSafe($modem_label)."',
				modified_dtm='now()' where contact_id='".dbSafe($id)."' and access_type='1'";
	$row = doSQLcmd($dbconn, $sqlcmd);

	if($row != 0)
	{
		$tmp_arr = explode(",", $group_str);
		$getsql = "select main_id from address_group where contact_id='".dbSafe($id)."'";
		$get = getSQLresult($dbconn, $getsql);

		if(!is_string($get))
		{
			$valid_arr = array();
			$invalid_arr = array();
			$update_arr = array();
			if(!empty($get))
			{
				for($i=0; $i<count($get); $i++)
				{
					$tmp_id = $get[$i]['main_id'];
					if(!in_array($tmp_id, $tmp_arr))
					{
						array_push($invalid_arr, $tmp_id);
					}
					else
					{
						array_push($valid_arr, $tmp_id);
					}
				}
			}
			for($j=0; $j<count($tmp_arr); $j++)
			{
				$tmp_id = $tmp_arr[$j];
				if(!in_array($tmp_id, $valid_arr))
				{
					array_push($update_arr, $tmp_id);
				}
			}

			for($k=0; $k<count($invalid_arr); $k++)
			{
				$tmp_id = $invalid_arr[$k];
				if($tmp_id != "")
				{
					$updatesql = "delete from address_group where main_id='".dbSafe($tmp_id)."' and contact_id='".dbSafe($id)."'";
					$update = doSQLcmd($dbconn, $updatesql);
				}
			}

			for($l=0; $l<count($update_arr); $l++)
			{
				$tmp_id = $update_arr[$l];
				if($tmp_id != "")
				{
					$group_id = getSequenceID($dbconn,'address_group_group_id_seq');
					$updatesql = "insert into address_group (group_id, main_id, contact_id, department, created_by, access_type, modified_by, modified_dtm)
								values ('".dbSafe($group_id)."','".dbSafe($tmp_id)."','".dbSafe($id)."','".dbSafe($department)."','".dbSafe($userid)."','1','".dbSafe($userid)."','now()')";
					$update = doSQLcmd($dbconn, $updatesql);
				}
			}

			for($m=0; $m<count($valid_arr); $m++)
			{
				$tmp_id = $valid_arr[$m];
				if($tmp_id != "")
				{
					$updatesql = "update address_group set modified_by='".dbSafe($userid)."', modified_dtm='now()' where main_id='".dbSafe($tmp_id)."' and contact_id='".dbSafe($id)."'";
					$update = doSQLcmd($dbconn, $updatesql);
				}
			}
		}
		$data['flag'] = 1;
	}
	else
	{
		$data['flag'] = 2;
		$data['status'] = $saveGlobalBook_msg1." '" .htmlspecialchars($contact,ENT_QUOTES). "' ".$saveGlobalBook_msg3; //unsuccessful
	}

	echo json_encode($data);
}

function deleteGlobalBook($id)
{
	global $dbconn;

	$sqlcmd = "delete from address_book where contact_id='".dbSafe($id)."' and access_type='1'";
	$res = doSQLcmd($dbconn,$sqlcmd);

	if (!empty($res)) {
		error_log("deleteGlobalBook: Database Error: ".$res);
		echo "Database Error";
	}
}

function emptyGlobalBook($userid)
{
	global $dbconn;

	if(isUserAdmin($userid)) {
		$sqlcmd = "delete from address_book where access_type='1'";
	} else {
		$department = getUserDepartment($userid);
		$sqlcmd = "delete from address_book where department='".dbSafe($department)."' and access_type='1'";
	}
	$res = doSQLcmd($dbconn, $sqlcmd);

	if (!empty($res)) {
		error_log("emptyGlobalBook: Database Error: ".$res);
		echo "Database Error";
	}
}

function insertContacts($userid, $uploadfile, $department2, $access_type, $file)
{
	global $dbconn;
	
	//delete 1st old preview data
	deleteContacts($userid, '0');

	$i = 0;
	$err_contact = 0;
	$err_mno = 0;
	$valid_count = 0;
	$pattern = "/^\+?\d+$/";

	//File controls added by Zin @ 11-Aug-2021
	if($uploadfile ){
		$file_type = "csv";		
		$chk_status = check_upload_file( $file['file'], $file_type );
	}

	if($chk_status['status'] == "1"){
		
		$file = fopen($uploadfile, 'r');
		fseek($file, 0);

		while(!feof($file) && $i < 50)
		{
			$i = $i + 1;
			$curr_arr = fgetcsv($file, 1000, ",");
			$correct_format = 0;

			if(count($curr_arr) < 2)
			{
				$correct_format = 1;
			}

			$contact = $curr_arr[0];
			$contact = preg_replace("/^[\n\r\s\t]+/", "", $contact);
			$contact = preg_replace("/[\n\r\s\t]+$/", "", $contact);
			$lower_contact = strtolower($contact);

			if(count($curr_arr) > 1)
			{
				$mobile_numb = $curr_arr[1];
				$mobile_numb = preg_replace("/^[\n\r\s\t]+/", "", $mobile_numb);
				$mobile_numb = preg_replace("/[\n\r\s\t]+$/", "", $mobile_numb);
			}
			else
			{
				$mobile_numb = "------";
			}

			$x = GetLanguage("address_book",$lang);
			if(!txvalidator($contact,TX_STRING) ||
			!validateSize($x->contact_name,$contact,"NAME")){
				$err_contact = 1;
				continue;
			}
			if(!txvalidator($mobile_numb,TX_SGMOBILEPHONE)){
				$err_mno = 1;
				continue;
			}

			#added by jorain, to check group column
			if(count($curr_arr) > 2)
			{
				$group = '';
				for($i=2;$i<count($curr_arr);$i++){
					if($group == ''){
						$group = $curr_arr[$i];
					}else{
						$group .= ','.$curr_arr[$i];
					}
				}
				$group = preg_replace("/^[\n\r\s\t]+/", "", $group);
				$group = preg_replace("/[\n\r\s\t]+$/", "", $group);
				
				if($access_type == '0'){ #personal address group
					$address_group_selection = getAddressGroupName($userid);
				}else{
					$address_group_selection = getGlobalGroupName($userid,$department2);
				}
			
				if($address_group_selection == ""){
				
					#no group created under this login userid, not a valid group name
					$group = "------";
				}else{
				
					$group_str = '';
					$upload_group_arr = explode(',',$group);
				
					for($i=0;$i<count($upload_group_arr);$i++){
						$grp = $upload_group_arr[$i];
						$grp = preg_replace("/^[\n\r\s\t]+/", "", $grp);
						$grp = preg_replace("/[\n\r\s\t]+$/", "", $grp);
						if(in_array($grp,$address_group_selection)){
							if($group_str == ''){
								$group_str = $upload_group_arr[$i];
							}else{
								$group_str .= ','.$upload_group_arr[$i];
							}
						}
					}
					if($group_str == ''){
						$group = "------";
					}else{
						$group = $group_str;
					}
				}
		
			}
			else
			{
				$group = "------";
			}
			#end of added by jorain, to check group column

			$chksql = "select contact_id from address_book where lower(contact_name)='".dbSafe($lower_contact)."' and created_by='".dbSafe($userid)."' and access_type='".dbSafe($access_type)."'";
			$chk = getSQLresult($dbconn, $chksql);
			if(!is_string($chk))
			{
				if(!empty($chk))
				{
					continue;
				}
			}

			if($mobile_numb != "")
			{
				if(preg_match($pattern, $mobile_numb))
				{
					$contacts_id = getSequenceID($dbconn,'contacts_list_contacts_id_seq');
					$sqlcmd = "insert into contacts_list (contacts_id, contact_name, mobile_numb, department, group_str, userid, access_type, file_format)
								values ('".dbSafe($contacts_id)."','".dbSafe($contact)."','".dbSafe($mobile_numb)."','".dbSafe($department2)."','".dbSafe($group)."','".dbSafe($userid)."','".dbSafe($access_type)."','".dbSafe($correct_format)."')";
					$row = doSQLcmd($dbconn, $sqlcmd);
					$valid_count++;
				}
			}
		}

		if(feof($file)) {
			if($valid_count > 0){
				echo 1;
			}
			else if($err_contact){
				echo 3;
			}else if($err_mno){
				echo 4;
			}		
		} else {
			echo 0;
		}	
	}else{
		$chk_status['status'];
	}
}

function listContacts($userid, $department, $access_type)
{
	global $dbconn;
	$result_array = array();

	$sqlcmd = "select contacts_id, contact_name, mobile_numb, group_str, file_format from contacts_list where userid='".dbSafe($userid)."' and access_type='".dbSafe($access_type)."' order by contact_name asc";
	$result = pg_query($dbconn, $sqlcmd);

	if(!$result) {
		echo "Database Error";
		error_log("listContacts: Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
	} else {
		$row = pg_fetch_all($result);

		for ($i=1; $row = pg_fetch_array($result); $i++){
			if($row['file_format']==0){
				$file_format = 'Correct';
			}else{
				$file_format = 'Incorrect';
			}
			array_push($result_array,Array(
				$i,
				htmlspecialchars($row['contact_name'],ENT_QUOTES),
				htmlspecialchars($row['mobile_numb']),
				htmlspecialchars($row['group_str'],ENT_QUOTES),
				$file_format
			));
		}

		echo json_encode(Array("data"=>$result_array));
	}
}

function deleteContacts($userid, $access_type)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_address_book",$lang);
	$db_err = (string)$msgstr->db_err;
	$access_type = (int)$access_type;

	$sqlcmd = " delete from contacts_list where userid='".dbSafe($userid)."' and access_type='".dbSafe($access_type)."'";
	$row = doSQLcmd($dbconn, $sqlcmd);

	if(is_string($row))
	{
		error_log($db_err." (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		return 1;
	}
	else
	{
		return 0;
	}
}

function addContacts($userid, $department3, $id_of_user, $access_type) 
{
	global $dbconn;
	$sqlcmd = "select contacts_id, contact_name, mobile_numb, group_str, access_type from contacts_list where userid='".dbSafe($userid)."' and access_type='".dbSafe($access_type)."'";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row))
	{
		error_log("addContacts: Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		echo "Database Error";
	}
	else
	{
		for($a=0; $a<count($row); $a++)
		{
			$group_str = $row[$a]['group_str'];
			$group_arr = explode(',',$group_str);
			$groupid_str = array();

			for($i=0;$i<count($group_arr);$i++){
				$grp = $group_arr[$i];
				$grp = preg_replace("/^[\n\r\s\t]+/", "", $grp);
				$grp = preg_replace("/[\n\r\s\t]+$/", "", $grp);
				$groupid = getGroupIDbyName($userid,$grp,$department3,$access_type);

				if($groupid != '0'){
					array_push($groupid_str, $groupid);
				}
			}

			if($access_type == '0'){ #personal address book
				addAddressBook($userid, $id_of_user, $department3, $row[$a]['contact_name'], $row[$a]['mobile_numb'], $groupid_str, 'None',"");				
			}else{
				addGlobalBook($userid, $id_of_user, $department3, $row[$a]['contact_name'], $row[$a]['mobile_numb'], $groupid_str, 'None',"");
			}

			$deletesql = "delete from contacts_list where contacts_id = '" .dbSafe($row[$a]['contacts_id']). "' ";
			$delete = doSQLcmd($dbconn, $deletesql);
		}
	}
}

function getModem($idx)
{
	global $dbconn;

	$sqlcmd = "select modem_label from address_book where contact_id='".dbSafe($idx)."' limit 1";
	$result = pg_query($dbconn, $sqlcmd);
	if(!$result)
	{
		error_log("Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		return;
	}
	else
	{
		$row = pg_fetch_array($result);
	}
	return $row['modem_label'];
}
?>
