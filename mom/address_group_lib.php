<?php
require "lib/commonFunc.php";

$id = @$_REQUEST['id'];
$mode = @$_REQUEST['mode'];
$userid = strtolower($_SESSION['userid']);
$group = @$_REQUEST['group'];
$id_of_user = @$_REQUEST['id_of_user'];
$department = @$_REQUEST['department'];
$page = @$_REQUEST['page'];

switch ($mode) {
	case "listAddressGroup":
        listAddressGroup($userid);
        break;
	case "addAddressGroup":
        addAddressGroup($userid, $id_of_user, $department, $group);
        break;
	case "deleteAddressGroup":
        deleteAddressGroup($userid, $id);
        break;
	case "listGlobalGroup":
        listGlobalGroup($userid, $department);
        break;
	case "addGlobalGroup":
        addGlobalGroup($userid, $id_of_user, $department, $group);
        break;
	case "deleteGlobalGroup":
        deleteGlobalGroup($id);
        break;
	case "emptyAddressGroup":
		emptyAddressGroup($userid);
		break;
	case "emptyGlobalGroup":
		emptyGlobalGroup($userid);
		break;
	case "loadGroupMember":
		loadGroupMember(@$_REQUEST['gidx']);
		break;
	case "loadContactJSON":
		loadContactJSON($userid,0);
		break;
	case "loadGlobalContactJSON":
		loadContactJSON($userid,1);
			break;
    default:
        die('Invalid Command');
}

function listAddressGroup($userid)
{
	global $dbconn;
	$result_array = array();

	$sqlcmd = "select group_id, group_name from address_group_main where created_by='".dbSafe($userid)."' and access_type='0' order by group_name";
	$result = pg_query($dbconn, $sqlcmd);

	for ($i=1; $row = pg_fetch_array($result); $i++){
		array_push($result_array,Array(
			htmlspecialchars($row['group_name'], ENT_QUOTES),
			// '<input type="checkbox" id="no" name="no" value="'.$row['group_id'].'">'
			// assmi
			'<input type="checkbox" class="user_checkbox" id="no" name="no" value="'.$row['group_id'].'">'
			// assmi
		));
	}

	echo json_encode(Array("data"=>$result_array));
}

function addAddressGroup($userid, $id_of_user, $department, $group)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_address_group",$lang);
	$x = GetLanguage("address_group",$lang);

	$group_str = (string)$msgstr->group;
	$addAddressGroup_msg1 = (string)$msgstr->addAddressGroup_msg1; // already exist
	$addAddressGroup_msg2 = (string)$msgstr->addAddressGroup_msg2; // success
	$addAddressGroup_msg3 = (string)$msgstr->addAddressGroup_msg3; // unsuccess
	$db_err = (string)$msgstr->db_err;

	$data = array();
	if(!txvalidator($group,TX_STRING)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_group;
		$data['field'] = "group";
	}
	else if(!validateSize($x->new_group_name,$group,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "group";		
	}
	else{

		$result_array = array();
		$lower_group = strtolower(stripslashes($group));

		$query_sql = "SELECT group_id from address_group_main where lower(group_name)='".dbSafe($lower_group)."' and created_by='".dbSafe($userid)."' and access_type='0'";
		$query_row = getSQLresult($dbconn, $query_sql);

		if(is_string($query_row))
		{
			$data['flag'] = 2;
			$data['status'] = $db_err. " (" .dbSafe($query_sql). ") -- " .dbSafe(pg_last_error($dbconn));
		}
		else
		{
			if(!empty($query_row))
			{
				$data['flag'] = 2;
				$data['status'] = $group_str. " '" .dbSafe($group). "' ". $addAddressGroup_msg1; // already exist
			}
			else
			{
				$addgroup_main_id = getSequenceID($dbconn,'address_group_main_group_id_seq');
				$sqlcmd = "INSERT into address_group_main (group_id, group_name, user_id, department, created_by) 
							values ('".dbSafe($addgroup_main_id)."','".dbSafe(trim($group))."','".dbSafe($id_of_user)."','".dbSafe($department)."','".dbSafe($userid)."')";
				$row = doSQLcmd($dbconn, $sqlcmd);
				
				if($row == 0)
				{
					$data['flag'] = 2;
					$data['status'] = $group_str. " '" .dbSafe($group). "' ". $addAddressGroup_msg3;
				}
				else
				{
					// Added by Ty 9Apr2020, Modify Done in 15Apr2020
					if (isset($_REQUEST["selectedcontactid"])) {
						$arr_contact = explode(",",$_REQUEST["selectedcontactid"]);
						$updatesql = "INSERT INTO address_group (group_id,main_id,contact_id,department,created_by,modified_by,created_dtm,modified_dtm,access_type) VALUES ('%s','%s','%s','%s','%s','%s','now()','now()',0)";
						foreach ($arr_contact as $contact) {
							// proceed each contact no
							$group_id = getSequenceID($dbconn,'address_group_group_id_seq'); // Generated Uniquie ID, not using contactid and the groupid
							$update = doSQLcmd($dbconn, sprintf($updatesql,dbSafe($group_id),dbSafe($addgroup_main_id),dbSafe($contact),dbSafe($department),dbSafe($userid),dbSafe($userid)));
							// update into contact
							$update2 = doSQLcmd($dbconn,sprintf("UPDATE address_book SET
										group_string = CASE WHEN group_string = '' THEN '%s' ELSE CONCAT(group_string,',%s') END,
										modified_by = '%s', modified_dtm = 'now()' WHERE contact_id = '%s'",dbSafe($addgroup_main_id),dbSafe($addgroup_main_id),dbSafe($userid),dbSafe($contact)));
						}
					}
					$data['flag'] = 1;
				}
			}
		}
	}
	echo json_encode($data);
}

function deleteAddressGroup($userid, $id)
{
	global $dbconn;

	$sqlcmd = "delete from address_group_main where group_id='".dbSafe($id)."' and access_type='0' and created_by='".dbSafe($userid)."'";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if (!empty($res)) { 
		echo "Database Error: ".$res;
	}
}

function emptyAddressGroup($userid)  // remind developer to add special so user won't call this script directly
{
	global $dbconn;

	$sqlcmd = "delete from address_group_main where created_by='".dbSafe($userid)."' and access_type='0'";
	$res = doSQLcmd($dbconn, $sqlcmd);
	
	if (!empty($res)) { 
		echo "Database Error: ".$res;
	}
}

function listGlobalGroup($userid)
{
	global $dbconn, $lang;
	$result_array = array();
	$disable = "";
	
	$msgstr = GetLanguage("global_address_group",$lang);
	$all_departments = (string)$msgstr->all_departments;

	if(isUserAdmin($userid)) {
		$sqlcmd = "SELECT group_id, group_name, department_list.department as department, ldap_id, ldap_location from address_group_main left outer join department_list on (address_group_main.department = department_list.department_id) 
					where access_type='1' order by group_name";
		
	} else {
		$department = getUserDepartment($userid);
		$sqlcmd = "SELECT group_id, group_name, department_list.department as department, ldap_id, ldap_location from address_group_main left outer join department_list on (address_group_main.department = department_list.department_id) 
					where (address_group_main.department='".dbSafe($department)."' or address_group_main.department = '0') and access_type = '1' order by group_name";
	}

	$result = pg_query($dbconn, $sqlcmd);

	for ($i=1; $row = pg_fetch_array($result); $i++){
		
		if (empty($row['department'])) {
			$dept = $all_departments;
			if(!isUserAdmin($userid)) {
				$disable = "disabled";
			}
		} else {
			$dept = htmlspecialchars($row['department'], ENT_QUOTES);
			$disable = "";
		}
		
		$ldap_location = '';
		if($row['ldap_location'] != ''){
			$ldap_location = htmlspecialchars($row['ldap_location'], ENT_QUOTES).' <a href="#" class="grpsync" id="'.$row['group_id'].'"><i class="fa fa-refresh text-success" style="font-size: 1.5rem; cursor: pointer;"></i></a>';
		}
		
		array_push($result_array,Array(
			htmlspecialchars($row['group_name'], ENT_QUOTES),
			$dept,
			$ldap_location,
			'<input type="checkbox" id="no" name="no" value="'.$row['group_id'].'" '.$disable.'/>',
			$row['group_id'],
		));

	}

	echo json_encode(Array("data"=>$result_array));
}

function addGlobalGroup($userid, $id_of_user, $department, $group)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_address_group",$lang);
	$x = GetLanguage("global_address_group",$lang);

	$group_str = (string)$msgstr->group;
	$address_group_str = (string)$msgstr->address_group;
	$addGlobalGroup_msg1 = (string)$msgstr->addGlobalGroup_msg1;
	$addGlobalGroup_msg2 = (string)$msgstr->addGlobalGroup_msg2;
	$addGlobalGroup_msg3 = (string)$msgstr->addGlobalGroup_msg3;
	$db_err = (string)$msgstr->db_err;

	if(!txvalidator($group,TX_STRING)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_group;
		$data['field'] = "group";
		echo json_encode($data);
		die;		
	}
	else if(!validateSize($x->new_group_name,$group,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "group";	
		echo json_encode($data);
		die;	
	}

	$result_array = array();
	$lower_group = strtolower(stripslashes($group));

	$query_sql = "select group_id from address_group_main where lower(group_name)='".dbSafe($lower_group)."' and department='".dbSafe($department)."' and access_type='1'";
	$query_row = getSQLresult($dbconn, $query_sql);
	
	if(is_string($query_row))
	{
		$data['flag'] = 2;
		$data['status'] = $db_err. " (" .dbSafe($query_sql). ") -- " .dbSafe(pg_last_error($dbconn));
	}
	else
	{
		if(!empty($query_row))
		{
			$data['flag'] = 0;
			$data['status'] = $group_str. " '" .dbSafe($group). "' ".$addGlobalGroup_msg1;
			$data['field'] = "group";
		}
		else
		{
			$addgroup_main_id = getSequenceID($dbconn,'address_group_main_group_id_seq');
			$sqlcmd = "insert into address_group_main (group_id, group_name, user_id, department, created_by, access_type) 
						values ('".dbSafe($addgroup_main_id)."','".dbSafe(trim($group))."','".dbSafe($id_of_user)."','".dbSafe($department). "','".dbSafe($userid)."','1')";
			$row = doSQLcmd($dbconn, $sqlcmd);
			
			if($row == 0)
			{
				$data['flag'] = 2;
				$data['status'] = $address_group_str. " '" .dbSafe($group). "' ". $addGlobalGroup_msg3;
			}
			else
			{
				// Added by Ty 9Apr2020, Modify Done in 15Apr2020
				if (isset($_REQUEST["selectedcontactid"])) {
					$arr_contact = explode(",",$_REQUEST["selectedcontactid"]);
					$updatesql = "INSERT INTO address_group (group_id,main_id,contact_id,department,created_by,modified_by,created_dtm,modified_dtm,access_type) VALUES ('%s','%s','%s','%s','%s','%s','now()','now()',1)";
					foreach ($arr_contact as $contact) {
						// proceed each contact no
						$group_id = getSequenceID($dbconn,'address_group_group_id_seq'); // Generated Uniquie ID, not using contactid and the groupid
						$update = doSQLcmd($dbconn, sprintf($updatesql,dbSafe($group_id),dbSafe($addgroup_main_id),dbSafe($contact),dbSafe($department),dbSafe($userid),dbSafe($userid)));
						// update into contact
						$update2 = doSQLcmd($dbconn,sprintf("UPDATE address_book SET
									group_string = CASE WHEN group_string = '' THEN '%s' ELSE CONCAT(group_string,',%s') END,
									modified_by = '%s', modified_dtm = 'now()' WHERE contact_id = '%s'",dbSafe($addgroup_main_id),dbSafe($addgroup_main_id),dbSafe($userid),dbSafe($contact)));
					}
				}
				$data['flag'] = 1;
			}
		}
	}
	echo json_encode($data);
}

function deleteGlobalGroup($id)
{
	global $dbconn;

	$sqlcmd = "delete from address_group_main where group_id='".dbSafe($id)."' and access_type='1'";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if (!empty($res)) { 
		echo "Database Error: ".$res;
	}
}

function emptyGlobalGroup($userid)
{
	global $dbconn;

	if(isUserAdmin($userid)) {
		$sqlcmd = "DELETE from address_group_main where access_type = '1'";
	} else {
		$department = getUserDepartment($userid);
		$sqlcmd = "DELETE from address_group_main where department='".dbSafe($department)."' and access_type='1'";
	}
	$res = doSQLcmd($dbconn, $sqlcmd);
	
	if (!empty($res)) { 
		echo "Database Error: ".$res;
	}
}
/* added by Ty 08/04/2020 */
function loadContact($id_of_user,$page = 1,$access_type = 0) {
	global $dbconn;

	$result_array = array();

	$itemperpage = 10;
	$offset = (dbSafe($page) - 1) * $itemperpage; 

	$sqlcmd = "SELECT contact_id, contact_name,mobile_numb FROM address_book WHERE created_by = '" .dbSafe($id_of_user). "' AND access_type='".dbSafe($access_type)."' ORDER BY contact_name OFFSET $offset LIMIT $itemperpage";

	$result = pg_query($dbconn, $sqlcmd);

	for ($i = 1 ; $row = pg_fetch_array($result) ; $i++) {
		array_push($result_array, Array(
			htmlspecialchars($row['contact_id'], ENT_QUOTES),
			htmlspecialchars($row['contact_name'], ENT_QUOTES),
			htmlspecialchars($row['mobile_numb'], ENT_QUOTES),
			$i + $offset
		));

	}
	echo json_encode($result_array);
}
// make use of jquery datatable
function loadContactJSON ($userid,$access_type = 0) {
	global $dbconn;

	$result_array = array();

	$sqlcmd = "SELECT contact_id, contact_name,mobile_numb FROM address_book WHERE created_by = '" .dbSafe($userid). "' AND access_type='".dbSafe($access_type)."' ORDER BY contact_name";

	$result = pg_query($dbconn, $sqlcmd);
	
	$i = 1;
	while($row = pg_fetch_array($result)) {
		array_push($result_array, Array(
			$i++,
			htmlspecialchars($row['contact_name'], ENT_QUOTES),
			htmlspecialchars($row['mobile_numb'], ENT_QUOTES),
			"<input type='checkbox' name='contact[]' value='".htmlspecialchars($row['contact_id'], ENT_QUOTES)."'>"
		));
	}
	echo json_encode(Array("data"=>$result_array));
}
function loadTotalContacts($userid, $access_type) {
	global $dbconn;
	$sqlcmd = "SELECT count(*) AS total FROM address_book WHERE created_by = '".dbSafe($userid)."' AND access_type = ".dbSafe($access_type);

	$result = pg_query($dbconn, $sqlcmd);
	if ($row = pg_fetch_array($result)) {
		echo $row['total'];
	}
}
function loadGroupMember($gidx)
{
	global $dbconn;
	
	$result_array = array();

	$sqlcmd = "select contact_name, mobile_numb, b.email, to_char(g.created_dtm, 'DD/MM/YYYY HH24:MI') as cdtm, g.created_by ".
			  "from address_group g left outer join address_book b on (g.contact_id=b.contact_id) ".
			  "where g.main_id='".dbSafe($gidx)."' order by b.contact_name";
	
	$result = pg_query($dbconn, $sqlcmd);

	for ($i=1; $row = pg_fetch_array($result); $i++){
		
		array_push($result_array,
		Array(
			$i,
			htmlspecialchars($row['contact_name'], ENT_QUOTES),
			htmlspecialchars($row['mobile_numb'], ENT_QUOTES),
			htmlspecialchars($row['email'], ENT_QUOTES),
			htmlspecialchars($row['cdtm'], ENT_QUOTES),
			htmlspecialchars($row['created_by'], ENT_QUOTES),
		));
	}

	echo json_encode(Array("data"=>$result_array));
}
?>
