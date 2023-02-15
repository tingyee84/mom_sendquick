<?php
require "lib/commonFunc.php";

$userid = $_SESSION['userid'];
$mode = @$_REQUEST['mode'];
$id = @$_REQUEST['ldapid'];
$name = @$_REQUEST['l_name'];
$desc = @$_REQUEST['l_desc'];
$ip1 = @$_REQUEST['l_ip1'];
$port1 = @$_REQUEST['l_port1'];
$ip2 = @$_REQUEST['l_ip2'];
$port2 = @$_REQUEST['l_port2'];
$domainname = @$_REQUEST['l_domainname'];
$loginname = @$_REQUEST['l_loginname'];
$loginpwd = @$_REQUEST['l_loginpwd'];
$loginmode = @$_REQUEST['l_loginmode'];
$basedn = @$_REQUEST['l_basedn'];
$filter = @$_REQUEST['l_filter'];
$scope = @$_REQUEST['l_scope'];
$download_group = @$_REQUEST['download_group'];
$mobile = @$_REQUEST['l_mobile'];
$mail = @$_REQUEST['l_mail'];
$sync_frequency = @$_REQUEST['sync_frequency'];
$sync_time = @$_REQUEST['sync_time'];
$sync_ul = @$_REQUEST['l_sync_ul'];
$sync_gab = @$_REQUEST['l_sync_gab'];
$user_dept = @$_REQUEST['user_dept'];
$user_role = @$_REQUEST['user_role'];
$gab_dept = @$_REQUEST['gab_dept'];
$type = @$_REQUEST['l_type'];
$l_id = @$_REQUEST['l_profile_id'];
$dept_id = @$_REQUEST['l_dept'];
$role_id = @$_REQUEST['l_user_role'];
$group_ids = @$_REQUEST['group'];
$modem_label = @$_REQUEST['modem'];
$id_of_user = @$_REQUEST['id_of_user'];
$department = @$_REQUEST['department'];
$department4 = @$_REQUEST['department4'];

//$_REQUEST['grpid'] = "CH193181507018234";//for test
//$mode = "syncGroupMember";

//$mode = "dlLdapGroups";
//$l_id = "15729398411";

//error_log("ldap_lib: mode:".$mode);

switch ($mode) {
	case "listLDAPServer":
		listLDAPServer($userid);
		break;
	case "deleteLDAPServer":
		deleteLDAPServer($userid, $id);
		break;
	case "findLDAPServerInfo":
		findLDAPServerInfo($id);
		break;
	case "addLDAPServer":
		addLDAPServer($name, $desc, $ip1, $port1, $ip2, $port2, $type, $domainname, $loginname, $loginpwd, $loginmode, $basedn, $download_group, $filter, $scope, $mobile, $mail, $sync_frequency, $sync_time, $sync_ul, $sync_gab, $user_dept, $user_role, $gab_dept, $userid);
		break;
	case "editLDAPServer":
		editLDAPServer($id, $name, $desc, $ip1, $port1, $ip2, $port2, $type, $domainname, $loginname, $loginpwd, $loginmode, $basedn, $download_group, $filter, $scope, $mobile, $mail, $sync_frequency, $sync_time, $sync_ul, $sync_gab, $user_dept, $user_role, $gab_dept, $userid);
		break;
	case "downloadLDAPusers":
		downloadLDAPusers($userid, $l_id, $dept_id, $role_id);
		break;
	case "downloadContacts":
		downloadContacts($userid, $id_of_user, $department4, $l_id, $group_ids, $modem_label);
		break;
	case "getLDAPOption":
		getLDAPOption(@$_REQUEST['dlgrp']);
		break;
	case "checkLDAPServAccConn":
		checkLDAPServAccConn($ip1,$port1,$ip2,$port2,$domainname, $loginname,$loginpwd);
		break;
	case "dlLdapGroups":
		downloadLDAPGroups($userid, $l_id);
		break;
	case "syncGroupMember":
		syncGroupMember($userid, @$_REQUEST['grpid']);
		break;
	default:
		die("Invalid Mode");
}

function checkLDAPServAccConn($l_ip1,$l_port1,$l_ip2,$l_port2,$l_domainname,$l_loginname,$l_loginpwd)
{
	putenv('LDAPTLS_REQCERT=never');
	$flag = 0;
	$returnmsg = "";
	
	if(empty($l_port1)){ $l_port1 = 389;}
	
	$ds = ldap_connect($l_ip1, $l_port1);
	$anon = @ldap_bind($ds);
	
	if(!$ds || !$anon)
	{
		$returnmsg  = "Cannot connect to Primary Server ($l_ip1:$l_port1)";
		if (!empty($l_ip2)) {
			if($l_port2 == ''){ $l_port2 = 389; }
			$ds = ldap_connect($l_ip2, $l_port2);
			$anon = @ldap_bind($ds);
			if(!$anon) {
				$returnmsg  .= ", Cannot connect to Secondary Server ($l_ip2:$l_port2)";
				if (ldap_get_option($ds, 0x0032, $extended_error)) {
					$returnmsg .= "<br>".$extended_error;
				}
			}
		}
	} else {
		$username = strtoupper($l_domainname)."\\".$l_loginname;
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
		ldap_set_option($ds, LDAP_OPT_SIZELIMIT, 0);
		
		$r=ldap_bind($ds, $username, $l_loginpwd);
		if( !$r ){
			$returnmsg .= "Failed to bind Service Account: $l_loginname";
			if (ldap_get_option($ds, 0x0032, $extended_error)) {
				$returnmsg .= "<br>".$extended_error;
			}
		} else {
			$flag = 1;
			$returnmsg .= "Successfully bind to Service Account: $l_loginname";
		}
	}

	$data['flag'] = trim($flag);
	$data['status'] = trim($returnmsg);
	
	echo json_encode($data);
}

function addLDAPServer($l_name, $l_desc, $l_ip1, $l_port1,$l_ip2,$l_port2,$l_type,
						$l_domainname,$l_loginname,$l_loginpwd,$l_loginmode,$l_basedn, $download_group,
						$l_filter,$l_scope,$l_mobile,$l_mail, $sync_frequency, $sync_time, $l_sync_ul, 
						$l_sync_gab, $user_dept, $user_role, $gab_dept, $created_by)
{
	global $dbconn;

	if( checkRecordExist("ldapserver","l_name='$l_name'",$dbconn) ){
		echo "LDAP Server: <i>$l_name</i> already exist.";
	} else {
		
		$data = array(
			'l_id' => time().getSequence($dbconn,'ldap_id'),
			'l_name' => trim($l_name),
			'l_desc' => trim($l_desc),
			'l_ip1' => trim($l_ip1), 
			'l_port1' => (empty($l_port1)?'389':$l_port1),
			'l_ip2' => trim($l_ip2), 
			'l_port2' => trim($l_port2), 
			'l_type' => trim($l_type), 
			'l_domain' => trim($l_domainname),
			'l_loginname' => trim($l_loginname),
			'l_loginpwd' => trim($l_loginpwd),
			'l_loginmode' => trim($l_loginmode),
			'l_basedn' => trim($l_basedn),
			'l_filter' => trim($l_filter),
			'l_scope' => trim($l_scope),
			'l_mobile' => (empty($l_mobile)?'mobile':$l_mobile),
			'l_mail' => (empty($l_mail)?'mail':$l_mail),
			'sync_frequency' => $sync_frequency,
			'sync_hour' => $sync_time,
			'sync_ul' => (empty($l_sync_ul)?'f':$l_sync_ul),
			'sync_gab' => (empty($l_sync_gab)?'f':$l_sync_gab),
			'user_dept' => trim($user_dept),
			'user_role' => trim($user_role),
			'gab_dept' => trim($gab_dept),
			'l_createdtm' => 'now()',
			'l_createby' => $created_by,
			'download_group' => (empty($download_group)?'f':$download_group),
		);

		$columns = implode(",",array_keys($data));
		$values = implode("','",array_map('pg_escape_string', array_values($data)));
			
		$cmd = "INSERT INTO ldapserver ($columns) VALUES ('$values')";
		$res = doSQLcmd($dbconn, $cmd);
		
		if (!$res) { 
			echo "Database Error: ".$res;
		}
	}
}

function editLDAPServer($l_id,$l_name, $l_desc, $l_ip1, $l_port1,$l_ip2,$l_port2,$l_type,
						$l_domainname,$l_loginname,$l_loginpwd,$l_loginmode,$l_basedn,$download_group,$l_filter,
						$l_scope,$l_mobile, $l_mail, $sync_frequency, $sync_time, $l_sync_ul, $l_sync_gab,
						$user_dept, $user_role, $gab_dept, $modified_by)
{
	global $dbconn;

	if( checkRecordExist("ldapserver","l_name='$l_name' and l_id!='$l_id'",$dbconn) ){
		echo "LDAP Server: <i>$l_name</i> already exist.";
	} else {
		
		$data = array(
			'l_name' => trim($l_name),
			'l_desc' => trim($l_desc),
			'l_ip1' => trim($l_ip1), 
			'l_port1' => (empty($l_port1)?'389':$l_port1),
			'l_ip2' => trim($l_ip2), 
			'l_port2' =>trim( $l_port2), 
			'l_type' => trim($l_type), 
			'l_domain' => trim($l_domainname),
			'l_loginname' => trim($l_loginname),
			'l_loginpwd' => trim($l_loginpwd),
			'l_loginmode' => trim($l_loginmode),
			'l_basedn' => trim($l_basedn),
			'l_filter' => trim($l_filter),
			'l_scope' => trim($l_scope),
			'l_mobile' => (empty($l_mobile)?'mobile':$l_mobile),
			'l_mail' => (empty($l_mail)?'mail':$l_mail),
			'sync_frequency' => $sync_frequency,
			'sync_hour' => $sync_time,
			'sync_ul' => (empty($l_sync_ul)?'f':$l_sync_ul),
			'sync_gab' => (empty($l_sync_gab)?'f':$l_sync_gab),
			'user_dept' => trim($user_dept),
			'user_role' => trim($user_role),
			'gab_dept' => trim($gab_dept),
			'l_modifydtm' => 'now()',
			'l_modifyby' => $modified_by,
			'download_group' => (empty($download_group)?'f':$download_group),
		);
		
		$update_data = '';
		foreach($data as $key => $value) {
			$update_data .= $key."='".pg_escape_string($value)."',";
		}
		$update_data = substr($update_data, 0, -1);
		
		$cmd = "UPDATE ldapserver SET ".$update_data." WHERE l_id='".$l_id."'";
		$res = doSQLcmd($dbconn, $cmd);
		
		if (!$res) { 
			echo "Database Error: ".$res;
		}
		
		//echo $cmd
	}
}

function listLDAPServer($userid)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_ldap",$lang);
	$err_msg1 = (string)$msgstr->err_msg1;

	$result_array = array();
	$userid = stripslashes($userid);
	$lower_userid = strtolower($userid);

	$sqlcmd = "select * from ldapserver where l_id!='0' order by l_name";
	$result = pg_query($dbconn, $sqlcmd);

	if(!$result){
		echo $sqlcmd . ' -- ' . pg_last_error($dbconn);
	}else{
		$ld_arr = pg_fetch_all($result);
	}

	for ($i=1; $row = pg_fetch_array($result); $i++){
		$server = $row['l_ip1'].":".$row['l_port1'];
		if($row['l_ip2'] != ''){
			$server .= "<br>".$row['l_ip2'].":".$row['l_port2'];
		}
		
		$dlgrp = 'No';
		if($row['download_group'] == 't'){
			$dlgrp = 'Yes';
		}
		array_push($result_array,Array(
			$i,
			'<a href="ldap_edit.php?ldapid='.$row['l_id'].'">'.$row['l_name'].'<i class="fa fa-pencil-square-o fa-fw"></i></a>',
			$row['l_desc'],
			$server,
			getLDAPLoginModeLabel($row['l_loginmode']),
			$row['l_mobile'],
			$row['l_basedn'],
			ucfirst($row['l_scope']),
			$dlgrp,
			getLDAPSyncInfo($row['sync_frequency'],$row['sync_hour'],$row['sync_ul'],$row['sync_gab'],$row['user_dept'],$row['user_role'],$row['gab_dept']),
			'<input type="checkbox" id="no" name="no" value="'.$row['l_id'].'">'
		));
	}
	
	echo json_encode(Array("data"=>$result_array));
}

function checkRecordExist($tablename, $condition, $conn)
{
	$sql = "select count(*) from $tablename where $condition";
	$result = pg_query($conn, $sql);
	$count = 0;
	
	if( !$result ){
		error_log($sql . ' -- ' . pg_last_error($conn));
	} else {
		$arr = pg_fetch_array($result, 0);
		$count = $arr[0];
	}
	
	return $count;
}

function totalLDAPServer($conn)
{
	$sqlcmd = "select count(*) from ldapserver";
	$result = pg_query($conn, $sqlcmd);
	$count = 0;

	if( !$result ){
		error_log($sql . ' -- ' . pg_last_error($conn));
	} else {
		$arr = pg_fetch_array($result, 0);
		$count = $arr[0];
	}
	
	return $count;
}

function getLDAPLoginModeLabel($type){
	if($type == "loginid"){
		return "Login ID";
	}elseif($type == "displayname"){
		return "Display Name";
	}elseif($type == "email"){
		return "Email";
	}
}

function getUserRoleLabel($type){
	if($type == "U"){
		return "User";
	}elseif($type == "A"){
		return "Admin";
	}
}

function findLDAPServerInfo($target_lid)
{
	global $dbconn;
	
	$sqlcmd = "select * from ldapserver where l_id='".pg_escape_string($target_lid)."'";
	$result = pg_query($dbconn, $sqlcmd);

	if (!$result){
		error_log($sqlcmd . ' -- ' .pg_last_error($dbconn));
	} else {
		$arr = pg_fetch_all($result);
		echo json_encode($arr[0]);
	}
}

function getDepartment($dept_id)
{
	global $dbconn;
	
	if(empty($dept_id)) {
		return "All Department";
	} else {
		$sqlcmd = "select department from department_list where department_id='".pg_escape_string($dept_id)."'";
		$row = getSQLresult($dbconn, $sqlcmd);

		return trim($row[0]['department']);
	}
}

function getRole($role_id)
{
	global $dbconn;

	$sqlcmd = " select user_role from user_role_list where role_id='".pg_escape_string($role_id)."'";
	$row = getSQLresult($dbconn, $sqlcmd);

	return $row[0]['user_role'];
}

function getLDAPSyncInfo($sync_frequency,$sync_hour,$sync_ul,$sync_gab,$user_dept,$user_role,$gab_dept)
{
	$info = 'Never';
	
	if($sync_frequency) {
		$info = 'Daily(';
		if($sync_hour < 12){
			$info .= $sync_hour." am)";
		}elseif( $sync_hour == 12 ){
			$info .= $sync_hour." pm)";
		} else {
			$info .= $sync_hour - 12 ." pm)";
		}
		
		if($sync_ul == 't') {
			$info .= ", Sync with User List (Dept:".getDepartment($user_dept)."; Role:".getRole($user_role).")";
		} 
			
		if($sync_gab == 't') {
			$info .= ", Sync with Global Address Book (Dept:".getDepartment($gab_dept).")";
		}
	}
	
	return $info;
}

function checkLDAPAttributes($l_id,$userid)
{
	$cmd = 'perl /home/msg/sbin/checkLDAPattributes.pl "' .$l_id. '" "' .$userid. '"';
	ob_start();
	passthru("$cmd");
	$ldapattr = ob_get_contents();
	ob_end_clean();
	
	$ldapattr = str_replace('-SQBR-','<br>',$ldapattr);
	$temp = tmpfile();
	fwrite($temp, $ldapattr);
	return $temp;
}

function deleteLDAPServer($userid, $id_str)
{
	global $dbconn;

	$id_arr = explode(",", $id_str);

	$id_string ='';
	foreach($id_arr as $id){
		$id_string .= ($id_string == '')?"'".$id."'":",'".$id."'";
	}

	$total_user = totalRecord("user_list","data_source_id in ($id_string) ",$dbconn);

	if($total_user > 0) {
		$sqlcmd = "select id from user_list where data_source_id in ($id_string)";
		$rows = getSQLresult($dbconn, $sqlcmd);

		$uid_string = '';
		foreach($rows as $row) {
			$uid_string .= ($uid_string == '')? $row['id']:",".$row['id'];
		}
		$response = deleteUserAccount($userid, $uid_string);

		if($response[0] == 1) {
			$sqlcmd = "delete from ldapserver where l_id in ($id_string) ";
			$delete = doSQLcmd($dbconn, $sqlcmd);

			if($delete) {
				$result_array[0] = 1;
				return $result_array;
			}
		}
	} else {
		$sqlcmd = "delete from ldapserver where l_id in ($id_string) ";
		$delete = doSQLcmd($dbconn, $sqlcmd);

		if($delete) {
			$result_array[0] = 1;
			return $result_array;
		}
	}

	$result_array[0] = 0;
	return $result_array;
}

function getLDAPOption($dlgrp)
{
	global $dbconn;
	
	if($dlgrp == ''){ $dlgrp = 'f'; }
	$sqlcmd = "select l_id, l_name from ldapserver where l_id != '0' and download_group = '".$dlgrp."' order by l_name";
	$result = pg_query($dbconn, $sqlcmd);

	if( !$result ){
		error_log($sqlcmd . ' -- ' .pg_last_error($conn));
	} else {
		$arr = pg_fetch_all($result);
		echo json_encode($arr);
	}
}

function downloadContacts($userid, $id_of_user, $department4, $l_id, $group_ids, $modem_label)
{
	global $dbconn,$lang;
	putenv('LDAPTLS_REQCERT=never');
	
	$msgstr = GetLanguage("lib_ldap",$lang);
	$db_err 		= (string)$msgstr->db_err;
	$err_msg1 		= (string)$msgstr->err_msg1;
	$err_msg2 		= (string)$msgstr->err_msg2;
	$err_msg3 		= (string)$msgstr->err_msg3;
	$info_msg3 		= (string)$msgstr->info_msg3;
	$info_msg4 		= (string)$msgstr->info_msg4;
	$xml_outof 		= (string)$msgstr->outof;
	$xml_diab 		= (string)$msgstr->diab;
	$xml_alreadyhas_ab = (string)$msgstr->alreadyhas_ab;
	$xml_nomobile_ab = (string)$msgstr->nomobile_ab;//contact(s) has no mobile number in LDAP Server.

	$sqlcmd = "select * from ldapserver where l_id='$l_id' ";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(!$row) {
		echo $db_err." -- " .dbSafe(pg_last_error($dbconn));
	} else {
		if(empty($row))
		{
			echo $err_msg1; //"No LDAP Server Profile Found!";
		}
		else
		{
			
			//LDAP information about AD Domain
			$ad_userid = trim($row[0]['l_loginname']);
			$ad_password = trim($row[0]['l_loginpwd']);
			$base_DN = trim($row[0]['l_basedn']);
			$userid_type = $row[0]['l_type'];
			$l_filter = $row[0]['l_filter'];
			$l_scope = $row[0]['l_scope'];
			$l_loginmode = $row[0]['l_loginmode'];
			$l_type = $row[0]['l_type'];
			$l_mobile = $row[0]['l_mobile'];
			$l_mail = $row[0]['l_mail'];
			$l_domain = $row[0]['l_domain'];

			if (strpos($ad_userid, '\\') == FALSE) {
				$ad_userid = strtoupper($l_domain)."\\".$ad_userid;
			}

			$l_ip1 = trim($row[0]['l_ip1']);
			$l_port1 = trim($row[0]['l_port1']);
			$l_ip2 = trim($row[0]['l_ip2']);
			$l_port2 = trim($row[0]['l_port2']);

			if($l_port1 == ''){
				$l_port1 = 389;
			}

			$ds=ldap_connect($l_ip1, $l_port1);
			$anon = @ldap_bind( $ds );

			//note: $ds is always a resource even if primary is down
			//try anonymous login to test connection
			if(!$anon)
			{
				if($l_port2 == ''){ $l_port2 = 389; }
				$ds = ldap_connect($l_ip2, $l_port2);
				$anon = @ldap_bind( $ds );
				if(!$anon) {
					echo $err_msg2; //"Sorry, LDAP connection fail. ";
				}

			}

			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
			ldap_set_option($ds, LDAP_OPT_SIZELIMIT, 0);
			$r=ldap_bind($ds, $ad_userid, $ad_password);// this is an "anonymous" bind, typically
	  		// read-only access
			if( !$r ){
				echo $err_msg3; //"Unable to bind to remote server....";
			}
			$userlist = array();
			$usercount = 0;

			if($l_filter == '') {
				$l_filter = "(&(objectCategory=user)(samaccountname=*))";
			}

			$justthese = array("samaccountname","displayname", "uid",$l_mobile);
			if($l_type != 'ad') {
				$justthese = array("displayname", $l_mail,$l_mobile);
			}

			$r;

			if($l_scope == 'sub') {
				$r = ldap_search($ds, $base_DN, $l_filter, $justthese);	//LDAP_SCOPE_SUBTREE.
			} else if($l_scope == 'one') {
				$r = ldap_list($ds, $base_DN, $l_filter, $justthese);	//LDAP_SCOPE_ONELEVEL.
			} else if($l_scope == 'base') {
				$r = ldap_read($ds, $base_DN, $l_filter, $justthese);	//LDAP_SCOPE_BASE
			}
			if( isset($r) ){
			
				$info = ldap_get_entries($ds, $r);

				if( $info == null || $info=="" ){
					echo $info_msg3; //"No contacts are downloaded from Active Directory. ";
				}
																													
				list($downloadcount, $already_has, $errorcount, $nomobile) = addIntoGAddressBook($info, $userid, $id_of_user, $department4, $l_mobile,$l_mail,$l_loginmode,$l_type,$group_ids,$modem_label);

				$total = count($info);
				$total--;

				if($total == 0 ) {
					echo $info_msg4; //"No new contacts are found in Active Directory. ";
				}

				$result_array[0] = 1;

				$success_msg = '';
				if($downloadcount != 0) {
					//$add_user ." out of ". $total ." downloaded into global address book.";
					$success_msg = $downloadcount ." ". $xml_outof ." ". $total ." ". $xml_diab;
				}
				if($already_has != 0) {
					$success_msg .= ($success_msg != '')? "\n":"";
					//contact(s) already exist in the global address book
					$success_msg .= $already_has ." ". $xml_alreadyhas_ab;
				}
				if($nomobile != 0) {
					$success_msg .= ($success_msg != '')? "\n":"";
					//contact(s) has no mobile number in LDAP Server
					$success_msg .= $nomobile ." ". $xml_nomobile_ab;
				}
				
				//echo "downloadcount: $downloadcount | already_has: $already_has | nomobile: $nomobile";
				//die;
				
			}
		}
	}
}

function downloadLDAPGroups($userid, $l_id)
{
	global $dbconn, $lang;
	putenv('LDAPTLS_REQCERT=never');
	
	//error_log("downloadLDAPGroups: l_id:".$l_id);
	
	$msgstr = GetLanguage("lib_ldap",$lang);
	$db_err 		= (string)$msgstr->db_err;
	$err_msg1 		= (string)$msgstr->err_msg1;
	$err_msg2 		= (string)$msgstr->err_msg2;
	$err_msg3 		= (string)$msgstr->err_msg3;
	$err_msg4 		= (string)$msgstr->err_msg4;
	
	$info_msg3 		= (string)$msgstr->info_msg3;
	//$info_msg3 		= "No groups are downloaded from Active Directory.";
	$xml_outof 		= (string)$msgstr->outof;
	$xml_diul 		= (string)$msgstr->diul;
	$xml_alreadyhas = (string)$msgstr->alreadyhas;
	
	if( $l_id != "" ){
		
		$sqlcmd = "select * from ldapserver where l_id='$l_id' ";
		$row = getSQLresult($dbconn, $sqlcmd);
		
		if(!$row) {
			//echo $db_err." -- " .dbSafe(pg_last_error($dbconn));
			$returns["status"] = "1";
			$returns["msg"] = $db_err." -- " .dbSafe(pg_last_error($dbconn));
		} else {
			
			if(empty($row))
			{
				//echo $err_msg1; //"No LDAP Server Profile Found!";
				$returns["status"] = "1";
				$returns["msg"] = $err_msg1;
			}
			else
			{
				
				//LDAP information about AD Domain
				$ad_userid = trim($row[0]['l_loginname']);
				$ad_password = trim($row[0]['l_loginpwd']);
				$base_DN = trim($row[0]['l_basedn']);
				$userid_type = $row[0]['l_type'];
				$l_filter = $row[0]['l_filter'];
				$l_scope = $row[0]['l_scope'];
				$l_loginmode = $row[0]['l_loginmode'];
				$l_type = $row[0]['l_type'];
				$user_domain = $row[0]['l_domain'];
				$l_mobile = $row[0]['l_mobile'];

				if (strpos($ad_userid, '\\') == FALSE) {
					$ad_userid = strtoupper($user_domain)."\\".$ad_userid;
				}
				
				$l_ip1 = trim($row[0]['l_ip1']);
				$l_port1 = trim($row[0]['l_port1']);
				$l_ip2 = trim($row[0]['l_ip2']);
				$l_port2 = trim($row[0]['l_port2']);

				if($l_port1 == ''){
					$l_port1 = 389;
				}

				$ds=ldap_connect($l_ip1, $l_port1);
				$anon = @ldap_bind( $ds );

				//note: $ds is always a resource even if primary is down
				//try anonymous login to test connection
				if(!$anon)
				{
					if($l_port2 == ''){ $l_port2 = 389; }
					$ds = ldap_connect($l_ip2, $l_port2);
					$anon = @ldap_bind( $ds );
					if(!$anon) {
						//echo $err_msg2; //"Sorry, LDAP connection fail. ";
						$returns["status"] = "1";
						$returns["msg"] = $err_msg2;
					}
				}
				
				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
				ldap_set_option($ds, LDAP_OPT_SIZELIMIT, 0);

				$r=ldap_bind($ds, $ad_userid, $ad_password);    // this is an "anonymous" bind, typically
				// read-only access
				if( !$r ){
					//echo $err_msg3; //"Unable to bind to remote server....";
					$returns["status"] = "1";
					$returns["msg"] = $err_msg3;
				}
				
				$grouplist = array();
				$groupcount = 0;
				
				//empty group quary (&(objectClass=group)(!member=*))
				
				//non-empty groups quary
				$l_filter = "(&(objectClass=group)(member=*))";
				$justthese = array('cn', 'dn', 'grouptype');
				
				$r;

				if($l_scope == 'sub') {
					$r = ldap_search($ds, $base_DN, $l_filter, $justthese);	//LDAP_SCOPE_SUBTREE.
				} else if($l_scope == 'one') {
					$r = ldap_list($ds, $base_DN, $l_filter, $justthese);	//LDAP_SCOPE_ONELEVEL.
				} else if($l_scope == 'base') {
					$r = ldap_read($ds, $base_DN, $l_filter, $justthese);	//LDAP_SCOPE_BASE
				}
				
				if( isset($r) ){
					$info = ldap_get_entries($ds, $r);
					
					if( $info == null || $info=="" ){
						//echo $info_msg3; //"No groups are downloaded from Active Directory. ";
						$returns["status"] = "0";
						$returns["msg"] = $info_msg3;
					} else {
						
						list($add_group, $already_has) = addIntoGAdrGroup($info, $userid, $l_id);
						
						$total = count($info) - 1;
						$success_msg = '';
						if($add_group != 0) {
							//$add_user ." out of ". $total ." downloaded into global address group.";
							$success_msg = $add_group ." ". $xml_outof ." ". $total ." ". "downloaded into global address group";
						}
						if($already_has != 0) {
							$success_msg .= ($success_msg != '')? "\n":"";
							//group(s) already exist in the global address group
							$success_msg .= $already_has ." ".$xml_outof." ".$total. " group(s) already exist in the global address group";
						}
						
						//echo $success_msg;
						$returns["status"] = "0";
						$returns["msg"] = $success_msg;
		
					}
					
					
					
				}
			}
		}
	
	}else{
		
		$returns["status"] = "1";
		$returns["msg"] = $err_msg4;
		//echo "Please select LDAP Server.";
	}
	
	echo json_encode( $returns );
	
}
function addIntoGAdrGroup($info, $created_by, $l_id)
{
	global $dbconn;

	$grpcount = 0;
	$alreadyhas = 0;

	foreach($info as $ldapinfo) {
		if( isset($ldapinfo['cn'][0]) ){
			$cn = trim($ldapinfo['cn'][0]);
			$dn = json_encode($ldapinfo['dn']);
			$dn_noquote = str_replace('"', '', $dn);
			
			$pieces = explode(",", $dn_noquote);
			$cn_1 = array_shift($pieces);
			
			$location = implode(',', $pieces);
			
			if(addADGroup($l_id, $cn, $location, $created_by)){
				$grpcount++;
			} else {
				$alreadyhas++;
			}
		}
	}
	
	return array($grpcount, $alreadyhas);
}
function addADGroup($l_id, $cn, $location, $userid)
{
	global $dbconn;
	
	$tbname = "address_group_main";
	$cond = "lower(group_name) = '".dbSafe(strtolower($cn))."'";
	$count = totalRecord($tbname, $cond, $dbconn);
	
	if($count > 0){
		//already exists.
		return 0;
	}
	
	$id_of_user = 1; // useradmin
	$department = 0; // alldepartment belong
	$access_type = 1; // global group
	
	$addgroup_main_id = getSequenceID($dbconn,'address_group_main_group_id_seq');
	$sqlcmd = "insert into address_group_main (group_id, group_name, user_id, department, ldap_id, ldap_location, access_type, created_by) 
				values ('".dbSafe($addgroup_main_id)."','"
						  .dbSafe(trim($cn))."','"
						  .dbSafe($id_of_user)."','"
						  .dbSafe($department)."','"
						  .dbSafe($l_id)."','"
						  .dbSafe($location)."','"
						  .dbSafe($access_type)."','"
						  .dbSafe($userid)."')";
						  
	//echo $sqlcmd;
	//die;
	
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if($row == 0)
	{
		//error_log("FAIL:".$sqlcmd);
		return 0;
	}
	
	//error_log("SUCCESS: group:".$cn." location:".$location);
	return 1;
}
function downloadLDAPusers($created_by, $l_id, $dept_id, $role_id)
{
	global $dbconn,$lang;
	putenv('LDAPTLS_REQCERT=never');

	$msgstr = GetLanguage("lib_ldap",$lang);
	$db_err 		= (string)$msgstr->db_err;
	$err_msg1 		= (string)$msgstr->err_msg1;
	$err_msg2 		= (string)$msgstr->err_msg2;
	$err_msg3 		= (string)$msgstr->err_msg3;
	$info_msg1 		= (string)$msgstr->info_msg1;
	$info_msg2 		= (string)$msgstr->info_msg2;
	$xml_outof 		= (string)$msgstr->outof;
	$xml_diul 		= (string)$msgstr->diul;
	$xml_alreadyhas = (string)$msgstr->alreadyhas;

	$sqlcmd = "select * from ldapserver where l_id='$l_id' ";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(!$row) {
		echo $db_err." -- " .dbSafe(pg_last_error($dbconn));
	} else {
		if(empty($row))
		{
			echo $err_msg1; //"No LDAP Server Profile Found!";
		}
		else
		{
			//LDAP information about AD Domain
			$ad_userid = trim($row[0]['l_loginname']);
			$ad_password = trim($row[0]['l_loginpwd']);
			$base_DN = trim($row[0]['l_basedn']);
			$userid_type = $row[0]['l_type'];
			$l_filter = $row[0]['l_filter'];
			$l_scope = $row[0]['l_scope'];
			$l_loginmode = $row[0]['l_loginmode'];
			$l_type = $row[0]['l_type'];
			$user_domain = $row[0]['l_domain'];
			$l_mobile = $row[0]['l_mobile'];

			if (strpos($ad_userid, '\\') == FALSE) {
				$ad_userid = strtoupper($user_domain)."\\".$ad_userid;
			}

			$l_ip1 = trim($row[0]['l_ip1']);
			$l_port1 = trim($row[0]['l_port1']);
			$l_ip2 = trim($row[0]['l_ip2']);
			$l_port2 = trim($row[0]['l_port2']);

			if($l_port1 == ''){
				$l_port1 = 389;
			}

			$ds=ldap_connect($l_ip1, $l_port1);
			$anon = @ldap_bind( $ds );

			//note: $ds is always a resource even if primary is down
			//try anonymous login to test connection
			if(!$anon)
			{
				if($l_port2 == ''){ $l_port2 = 389; }
				$ds = ldap_connect($l_ip2, $l_port2);
				$anon = @ldap_bind( $ds );
				if(!$anon) {
					echo $err_msg2; //"Sorry, LDAP connection fail. ";
				}

			}

			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
			ldap_set_option($ds, LDAP_OPT_SIZELIMIT, 0);

			$r=ldap_bind($ds, $ad_userid, $ad_password);    // this is an "anonymous" bind, typically
	  		// read-only access
			if( !$r ){
				echo $err_msg3; //"Unable to bind to remote server....";
			}

			$userlist = array();
			$usercount = 0;

			if($l_filter == '') {
				$l_filter = "(&(objectCategory=user)(samaccountname=*))";
			}

			$justthese = array("samaccountname","displayname","uid",$l_mobile);
			if($l_type != 'ad') {
				$justthese = array("displayname","mail",$l_mobile);
			}

			$r;

			if($l_scope == 'sub') {
				$r = ldap_search($ds, $base_DN, $l_filter, $justthese);	//LDAP_SCOPE_SUBTREE.
			} else if($l_scope == 'one') {
				$r = ldap_list($ds, $base_DN, $l_filter, $justthese);	//LDAP_SCOPE_ONELEVEL.
			} else if($l_scope == 'base') {
				$r = ldap_read($ds, $base_DN, $l_filter, $justthese);	//LDAP_SCOPE_BASE
			}

			if( isset($r) ){
				$info = ldap_get_entries($ds, $r);

				if( $info == null || $info=="" ){
					echo $info_msg1; //"No users are downloaded from Active Directory. ";
				}

				list($add_user, $already_has) = addIntoUserList($info, $created_by, $l_id, $dept_id, $role_id,$l_mobile,$l_loginmode,$l_type);

				$total = count($info);
				$total--;

				if($total == 0 ) {
					echo $info_msg2; //"No new users are found in Active Directory. ";
				}

				$result_array[0] = 1;

				$success_msg = '';
				if($add_user != 0) {
					//$add_user ." out of ". $total ." downloaded into user list.";
					$success_msg = $add_user .' '. $xml_outof .' '. $total .' ' .$xml_diul;
				}
				
				if($already_has != 0) {
					$success_msg .= ($success_msg != '')? "\n":"";
					//$already_has ." user(s) already exist in the user list.";
					$success_msg .= $already_has . $xml_alreadyhas;
				}

				echo $success_msg;
			} else {
				echo $err_msg3; //"Unable to bind to remote server....";
			}
		}
	}
}

function getASuserrole($role_id)
{
	global $dbconn;

	$sqlcmd = "select role_id, access_string from user_role_list where role_id='$role_id'";
	$result = getSQLresult($dbconn, $sqlcmd);

	$access_string = '';
	if(is_string($result)){
		$access_string = '';
	} else {
		$access_string = $result[0]['access_string'];
	}

	return $access_string;
}

function addIntoUserList($info,$created_by,$l_id,$dept_id,$role_id,$l_mobile,$loginmode,$l_type)
{
	global $dbconn;

	$access_string = getASuserrole($role_id);
	$password = 'ldap-authen';
	$usercount = 0;
	$alreadyhas = 0;

	foreach($info as $ldapinfo) {
		$mail = "";
		$loginname ="";
		$mno = "";
		$displayname = "";

		if($l_type == 'ad') {
			if( isset($ldapinfo['uid'][0]) ){
				$mail = trim($ldapinfo['uid'][0]);
			}
			if(isset($ldapinfo['samaccountname'][0])){
				$loginname = trim($ldapinfo['samaccountname'][0]);
				$loginname = trim($loginname);
			}
		} else {
			if( isset($ldapinfo['mail'][0]) ){
				$loginname = $mail = trim($ldapinfo['mail'][0]);
			}
		}

		if(isset($ldapinfo[$l_mobile][0])){
			$mno = $ldapinfo[$l_mobile][0];
		}

		if( isset($ldapinfo['displayname'][0]) ){
			$displayname = ($ldapinfo['displayname'][0]);
			$displayname = trim($displayname);
		}

		//to check login name is already has or not in the sQ System.
		$check_user = '';
		if($loginmode == 'displayname') {
			$check_user = $displayname;
		} else if ($loginmode == 'email') {
			$check_user = $mail;
		} else if($loginmode == 'loginid') {
			$check_user = $loginname;
		}

		$isHas = 0;
		$check_user = strtolower(trim($check_user));

		if($check_user != ''){
			$userid = pg_escape_literal($check_user);
			$cond = "userid = {$userid} ";
			$isHas = totalRecord('user_list', $cond, $dbconn);

			if(!$isHas) {
				$user_list_id = getSequenceID($dbconn,'user_list_id_seq');
				$mno = urldecode($mno);

				$sqlcmd = " insert into user_list (id,userid,password,access_string,mobile_numb,user_role,department,created_by,data_source_id,language) values
					('" .dbSafe($user_list_id). "', '"
						.dbSafe($check_user). "', '"
						.dbSafe($password). "', '"
						.dbSafe($access_string). "', '"
						.dbSafe($mno). "', '"
						.dbSafe($role_id). "', '"
						.dbSafe($dept_id). "', '"
						.dbSafe($created_by). "', '"
						.dbSafe($l_id)."','EN') ";
				$row = doSQLcmd($dbconn, $sqlcmd);

				if($row == 1) {
					$usercount++;
					
					$qid = getSequenceID($dbconn,'quota_mnt_idx_seq');
					$sqlquota = "insert into quota_mnt (idx, userid, topup_frequency, quota_limit, quota_left, next_topup_dtm, updated_dtm, updated_by, unlimited_quota)".
								"values ('$qid','$check_user','3','0','0',null,'now()','$created_by','0')";
					doSQLcmd($dbconn, $sqlquota);

				} else {
					error_log("ERR: failed to add user: $check_user into user list.");
				}

			} else {
				$alreadyhas++;
			}
		} else {
			#error_log("INFO: downloaded userid is empty.");
		}
	}

	//error_log("INFO: downloaded user $alreadyhas user(s) already exist on user list.");

	if($usercount > 0) {
		updateTotal($dbconn, 'user_role_list', 'role_id', $role_id, 'total_users', '1', $usercount);
		updateTotal($dbconn, 'department_list', 'department_id', $dept_id, 'total_users', '1', $usercount);
		updateTotal($dbconn, 'ldapserver', 'l_id', $l_id, 'totalusers', '1', $usercount);
	}

	return array($usercount, $alreadyhas);
}

function addIntoGAddressBook($info,$created_by,$id_of_user,$department,$l_mobile,$l_mail,$loginmode,$l_type,$group_string,$modem_label)
{
	global $dbconn;
	
	//print_r( $group_string );
	//die;
	$downloadcount = 0;
	$alreadyhas = 0;
	$errorcount = 0;
	$nomobile = 0;

	foreach($info as $ldapinfo) {
		$loginname ="";
		$mno = "";
		$displayname = "";
		$mail = '';

		if($l_type == 'ad') {
			if(isset($ldapinfo['samaccountname'][0])){
				$loginname = trim($ldapinfo['samaccountname'][0]);
				$loginname = trim($loginname);
			}
			if(isset($logininfo[$l_mail][0])){
				//$mail = $ldapinfo['uid'][0];
				$mail = $ldapinfo[$l_mail][0];
				$mail = trim($mail);
			}
		} else {
			if( isset($ldapinfo['mail'][0]) ){
				$loginname = $mail = trim($ldapinfo['mail'][0]);
				$mail = $loginname;
			}
		}

		if(isset($ldapinfo[$l_mobile][0])){
			$mno = $ldapinfo[$l_mobile][0];
		}

		if( isset($ldapinfo['displayname'][0]) ){
			$displayname = ($ldapinfo['displayname'][0]);
			$displayname = trim($displayname);
		}

		$check_user = '';
		if($loginmode == 'displayname') {
			$check_user = $displayname;
		} else if ($loginmode == 'email') {
			$check_user = $mail;
		} else if($loginmode == 'loginid') {
			$check_user = $loginname;
		}

		$check_user = strtolower(trim($check_user));

		if($check_user != ''){
			if($mno != '') {
				$response = addGlobalBook($created_by, $id_of_user, $department, $check_user, $mno, $group_string, $modem_label);
				if($response[0] == 1){
					$downloadcount++;
				} else {
					if($response[2] == 1){
						$alreadyhas++;
					} else {
						$errorcount++;
					}
				}
			} else {
				#error_log("No mobile number found for contact $check_user .");
				$nomobile++;
			}

		} else {
			#error_log("INFO: downloaded userid is empty.");
		}
	}

	//error_log("INFO: downloaded user $alreadyhas user(s) already exist on user list.");

	return array($downloadcount, $alreadyhas, $errorcount, $nomobile);

}

function compareAlreadyHas($info, $loginmode)
{
	global $dbconn;
	$userlist = array();
	$usercount = 0;
	$sqlcmd = " Select userid from user_list ";
	$results = getSQLresult($dbconn, $sqlcmd);

	foreach($info as $ldapinfo) {
		$mail = "";
		$loginname ="";
		$mno = "";
		$displayname = "";
		$name = $ldapinfo['cn'][0];

		if( isset($ldapinfo['mail'][0]) ){
			$mail = trim($ldapinfo['mail'][0]);
		}
		if(isset($ldapinfo['samaccountname'][0])){
			$loginname = trim($ldapinfo['samaccountname'][0]);
			$loginname = trim($loginname);
		}

		$complete_mno = '';
		if(isset($ldapinfo['mobile'][0])){
			$complete_mno = $ldapinfo['mobile'][0];
		}

		if( isset($ldapinfo['displayname'][0]) ){
			$displayname = ($ldapinfo['displayname'][0]);
			$displayname = trim($displayname);
		}

		$check_user = $loginname;

		if($loginmode == 'displayname') {
			$check_user = $displayname;
		} else if ($loginmode == 'email') {
			$check_user = $mail;
		}

		$isHas = 0;
		$check_user = strtolower(trim($check_user));
		foreach($results as $row) {
			if($check_user == strtolower(trim($row['userid']))){
				$isHas = 1;
				break;
			}
		}

		if(!$isHas) {
			if($loginname != "" ) {
				if($check_user != "" ) {
					if(!$isHas) {
						$userlist[$usercount]['displayname'] = htmlspecialchars($displayname, ENT_QUOTES);
						$userlist[$usercount]['login_name'] = htmlspecialchars($check_user, ENT_QUOTES);
						$userlist[$usercount]['mail'] = htmlspecialchars($mail, ENT_QUOTES);
						$userlist[$usercount]['mobile'] = htmlspecialchars($complete_mno, ENT_QUOTES);
						$userlist[$usercount]['samaccountname'] =  htmlspecialchars($loginname, ENT_QUOTES);
					}
				}
			}

			$usercount++;
		}

	}

	return $userlist;
}

function addGlobalBook($userid, $id_of_user, $department, $contact, $mobile_numb, $group_string, $modem_label)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_address_book",$lang);
	$addGlobalBook_msg1 = (string)$msgstr->addGlobalBook_msg1;
	$addGlobalBook_msg2 = (string)$msgstr->addGlobalBook_msg2;
	$addGlobalBook_msg3 = (string)$msgstr->addGlobalBook_msg3;
	$contact_str = (string)$msgstr->contact;
	$db_err = (string)$msgstr->db_err;

	$result_array = array();
	$userid = stripslashes($userid);
	$lower_userid = strtolower($userid);
	$contact = stripslashes($contact);
	$lower_contact = strtolower($contact);
	$mobile_numb = urldecode(trim($mobile_numb));
	
	if( $group_string != "" ){
		$group_string = implode(",",$group_string);
	}else{
		//$group_string = implode(",",$group_string);
	}
	
	$query_sql = " select contact_id from address_book where lower(contact_name) = '" .dbSafe($lower_contact). "' and department = '" .dbSafe($department). "' and access_type = '1' ";
	$query_row = getSQLresult($dbconn, $query_sql);
	
	if(is_string($query_row))
	{
		error_log($db_err." (" .dbSafe($query_sql). ") -- " .dbSafe(pg_last_error($dbconn)));
	}
	else
	{
		if(!empty($query_row))
		{
			#error_log($contact_str." '" .dbSafe($contact). "' ".$addGlobalBook_msg1);
		}
		else
		{

			$contact_id = getSequenceID($dbconn,'address_book_contact_id_seq');
			$sqlcmd = " insert into address_book (contact_id,contact_name, mobile_numb, user_id, department, group_string, created_by, access_type, modem_label) values ".
						"('" .dbSafe($contact_id). "', '" .dbSafe($contact). "', '" .dbSafe($mobile_numb). "', '" .dbSafe($id_of_user). "', ".
						"'" .dbSafe($department). "', '" .dbSafe($group_string). "', '" .dbSafe($lower_userid). "', '1', '".dbSafe($modem_label)."') ";
			$row = doSQLcmd($dbconn, $sqlcmd);
			if($row != 0)
			{
				$getsql = "select contact_id from address_book where lower(contact_name) = '" .dbSafe($lower_contact). "' and access_type = '1' and created_by = '" .dbSafe($lower_userid). "' ";
				$get = getSQLresult($dbconn, $getsql);
				if(!is_string($get) && !empty($get))
				{
					$contact_id = $get[0]['contact_id'];
					$tmp_arr = explode(",", $group_string);
					for($i=0; $i<count($tmp_arr); $i++)
					{
						if($tmp_arr[$i] != "")
						{
							$group_id = getSequenceID($dbconn,'address_group_group_id_seq');
							$updatesql = "insert into address_group (group_id, main_id, contact_id, department, created_by, access_type) values ('" .dbSafe($group_id). "', '" .dbSafe($tmp_arr[$i]). "', '" .dbSafe($contact_id). "', '" .dbSafe($department). "', '" .dbSafe($lower_userid). "', '1') ";
							doSQLcmd($dbconn, $updatesql);
						}
					}
				}
			}
			else
			{
				#error_log($contact_str. " '" .dbSafe($contact). "' ". $addGlobalBook_msg3);
			}
		}
	}
}
function syncGroupMember($userid, $grpid)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_ldap",$lang);
	$db_err 		= (string)$msgstr->db_err;
	$err_msg1 		= (string)$msgstr->err_msg1;
	$err_msg2 		= (string)$msgstr->err_msg2;
	$err_msg3 		= (string)$msgstr->err_msg3;
	$info_msg3 		= (string)$msgstr->info_msg3;
	$info_msg4 		= (string)$msgstr->info_msg4;
	$xml_outof 		= (string)$msgstr->outof;
	$xml_diab 		= (string)$msgstr->diab;
	$xml_alreadyhas_ab = (string)$msgstr->alreadyhas_ab;
	$xml_nomobile_ab = (string)$msgstr->nomobile_ab;//contact(s) has no mobile number in LDAP Server.
	
	$modem_label = '';
	$department4 = 0;
	$id_of_user = 1; // useradmin
	$department = 0; // alldepartment belong
	$access_type = 1; // global contact
	
	//echo $grpid;
	//die;
	
	$data = array();
	$data[0] = 0;
	$grpinfo = getServerInfo($grpid);
	
	if(is_string($grpinfo)){
	
		$data[0] = 1;
		$data['err'] = $grpinfo;
	} else {
		
		$g_obj = $grpinfo[0];
		$l_obj = $grpinfo[1];
		
		$group_desc = 'Group ('.$g_obj->group_name.") Sync: ";
		//error_log(json_encode($g_obj));
		//error_log(json_encode($l_obj));
		
		//LDAP information about AD Domain
		$ad_userid = $l_obj->l_loginname;
		$ad_password = $l_obj->l_loginpwd; 
		$base_DN = trim($l_obj->l_basedn);
		$userid_type = $l_obj->l_type;
		
		$l_scope = 'sub';
		$l_loginmode = $l_obj->l_loginmode;
		$l_type = $l_obj->l_type;
		$l_mobile = $l_obj->l_mobile;
		$l_mail = $l_obj->l_mail;
		$l_domain = $l_obj->l_domain;

		if (strpos($ad_userid, '\\') == FALSE) {
			$ad_userid = strtoupper($l_domain)."\\".$ad_userid;
		}

		$l_ip1 = trim($l_obj->l_ip1);
		$l_port1 = trim($l_obj->l_port1);
		$l_ip2 = trim($l_obj->l_ip2);
		$l_port2 = trim($l_obj->l_port2);

		if($l_port1 == ''){
			$l_port1 = 389;
		}

		$ds=ldap_connect($l_ip1, $l_port1);
		$anon = @ldap_bind( $ds );

		//note: $ds is always a resource even if primary is down
		//try anonymous login to test connection
		if(!$anon)
		{
			if($l_port2 == ''){ $l_port2 = 389; }
			$ds = ldap_connect($l_ip2, $l_port2);
			$anon = @ldap_bind( $ds );
			if(!$anon) {
				$data[0] = 1;
				$data['err'] = $group_desc. $err_msg2; //"Sorry, LDAP connection fail. ";
				echo json_encode($data);
				return;
			}

		}

		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
		ldap_set_option($ds, LDAP_OPT_SIZELIMIT, 0);
		$r=ldap_bind($ds, $ad_userid, $ad_password);// this is an "anonymous" bind, typically
		// read-only access
		if( !$r ){
			$data[0] = 1;
			$data['err'] = $group_desc.$err_msg3; //"Unable to bind to remote server....";
			echo json_encode($data);
			return;
		}
		$userlist = array();
		$usercount = 0;

		$l_filter = "memberOf=CN=".trim($g_obj->group_name).",".$g_obj->ldap_location;
		
		//error_log($l_filter);
		$justthese = array("samaccountname","displayname", "uid","mail", $l_mobile);
		if($l_type != 'ad') {
			$justthese = array("displayname", "mail",$l_mobile);
		}

		$r;

		if($l_scope == 'sub') {
			$r = ldap_search($ds, $base_DN, $l_filter, $justthese);	//LDAP_SCOPE_SUBTREE.
		} else if($l_scope == 'one') {
			$r = ldap_list($ds, $base_DN, $l_filter, $justthese);	//LDAP_SCOPE_ONELEVEL.
		} else if($l_scope == 'base') {
			$r = ldap_read($ds, $base_DN, $l_filter, $justthese);	//LDAP_SCOPE_BASE
		}
	
		if( isset($r) ){
		
			$info = ldap_get_entries($ds, $r);

			if( $info == null || $info=="" ){
				
				$data['msg'] = $info_msg3; //"No contacts are downloaded from Active Directory. ";
			}
			
			$total = count($info);
			$total--;
			if($total > 0){
				list($downloadcount, $already_has, $errorcount, $nomobile) = syncGAddressBook($info, $userid, $id_of_user, $department, $l_mobile,$l_mail, $l_loginmode,$l_type,$g_obj,$modem_label);

				$success_msg = '';
				if($downloadcount != 0) {
					//echo "1";
					//$add_user ." out of ". $total ." downloaded into global address book.";
					$success_msg = $downloadcount ." ". $xml_outof ." ". $total ." ". $xml_diab;
				}
				if($already_has != 0) {
					//echo "2";
					$success_msg .= ($success_msg != '')? "\n":"";
					//contact(s) already exist in the global address book
					$success_msg .= $already_has ." ". $xml_alreadyhas_ab;
					
				}
				if($nomobile != 0) {
					//echo "3";
					$success_msg .= ($success_msg != '')? "\n":"";
					//contact(s) has no mobile number in LDAP Server
					$success_msg .= $nomobile ." ".$xml_outof ." ". $total." ". $xml_nomobile_ab;
				}
				
				$data['msg'] = $group_desc.$success_msg;
				
				//print_r( $data );
				//die;
			} else {
				if($total == 0 ) {
					$data['msg'] = $group_desc.$info_msg4; //"No new contacts are found in Active Directory. ";
				}
			}

			
		}
	}
	
	echo json_encode($data);
}
function syncGAddressBook($info, $userid, $id_of_user, $department, $l_mobile,$l_mail, $loginmode,$l_type,$g_obj,$modem_label)
{
	global $dbconn;

	$downloadcount = 0;
	$alreadyhas = 0;
	$errorcount = 0;
	$nomobile = 0;
	
	foreach($info as $ldapinfo) {
		$loginname ="";
		$mno = "";
		$displayname = "";
		$mail = '';
		
		//print_r( $ldapinfo );
		
		if($l_type == 'ad') {
		
			if(isset($ldapinfo['samaccountname'][0])){
				$loginname = trim($ldapinfo['samaccountname'][0]);
				$loginname = trim($loginname);
			}
			if(isset($logininfo['uid'][0])){
				$mail = $ldapinfo['uid'][0];
				$mail = trim($mail);
			}
			
			if($mail == ''){
				if(isset($logininfo[$l_mail][0])){
				$mail = $ldapinfo[$l_mail][0];
				$mail = trim($mail);
			}
			}
			
		} else {
			
			if( isset($ldapinfo[$l_mail][0]) ){
				$mail = trim($ldapinfo[$l_mail][0]);
				$loginname = $mail;
			}
		}
	
		if(isset($ldapinfo[$l_mobile][0])){
			$mno = $ldapinfo[$l_mobile][0];
		}

		if( isset($ldapinfo['displayname'][0]) ){
			$displayname = ($ldapinfo['displayname'][0]);
			$displayname = trim($displayname);
		}

		$check_contact = '';
		if($loginmode == 'displayname') {
			$check_contact = $displayname;
		} else if ($loginmode == 'email') {
			$check_contact = $mail;
		} else if($loginmode == 'loginid') {
			$check_contact = $loginname;
		} else if($loginmode == 'ad') {
			$check_contact = $displayname;
		}

		$check_contact =trim($check_contact);
		
		//echo "loginmode ==" . $loginmode . "<br>";
		//echo "check_contact ==" . $check_contact . "<br>";
		
		if($check_contact != ''){
			if($mno != '') {
	
			  $searchsql = "select contact_id, mobile_numb, access_type, group_string,inc_id ".
						   "from address_book where lower(contact_name)='".dbSafe(strtolower($check_contact))."'"; 
						   
			  $contact_obj = getSQLObj($dbconn, $searchsql);
			  
			  if(is_object($contact_obj)){
				  #contact already exists
				  //$to_update = 0;
				  $to_update = 1;
				  $alreadyhas++;
				  if($contact_obj->mobile_numb != $mno){
					  //$to_update = 1;
				  }
				  $groups = explode(",", $contact_obj->group_string);
				  $group_string = '';
				  
				  if(!in_array($g_obj->group_id, $groups)){
					 
					  array_push($groups, $g_obj->group_id);
					  $group_string = implode(",", $groups);
					  //$to_update = 1;
					  addIntoGroupLink($contact_obj->contact_id, $g_obj->group_id,$userid);
					  
				  }
				  
				  if($to_update){
					  #error_log("group_string:".$group_string);
					  $params = "mobile_numb='".dbSafe($mno)."'";
					  if($group_string != ''){
						  $params .= ", group_string = '".$group_string."'";
					  }
					  $sqlcmd = "update address_book set ".$params." where contact_id='".$contact_obj->contact_id."'";
					  if(!doSQLcmd($dbconn, $sqlcmd)){
						 error_log("FAIL: ".$sqlcmd); 
					  }
				  }
			  } else {
				  #add new contact
				 // error_log("grpid:".$g_obj->group_id);
				  $contact_id = getSequenceID($dbconn,'address_book_contact_id_seq');
				  $sqlcmd = " insert into address_book (contact_id,contact_name, mobile_numb, user_id, department, group_string, created_by, access_type, modem_label) values ('" 
								.dbSafe($contact_id). "', '" 
								.dbSafe($check_contact). "', '" 
								.dbSafe($mno). "', '" 
								.dbSafe($id_of_user). "', '" 
								.dbSafe($department). "', '" 
								.dbSafe($g_obj->group_id). "', '" 
								.dbSafe($userid). "', '1', '"
								.dbSafe($modem_label)."') ";
				  if(!doSQLcmd($dbconn, $sqlcmd)){
					 error_log("FAIL:".$sqlcmd);
					 $errorcount++;
				  } else {
					$downloadcount++; 
					addIntoGroupLink($contact_id, $g_obj->group_id, $userid);
				  }
				  
			  }
			  
			} else {
				
				#error_log("No mobile number found for contact $check_contact .");
				$nomobile++;
			}

		} else {
			#error_log("INFO: downloaded userid is empty.");
		}
		
	}
	
	//echo "downloadcount ==" . $downloadcount . "<br>";
	//echo "alreadyhas ==" . $alreadyhas . "<br>";
	//echo "errorcount ==" . $errorcount . "<br>";
	//echo "nomobile ==" . $nomobile . "<br>";
	//die;
	
	//error_log("ahs: $alreadyhas dcount:$downloadcount");
	
	return array($downloadcount, $alreadyhas, $errorcount, $nomobile);
}
function addIntoGroupLink($cid, $gid, $userid){
	global $dbconn;
	

	$idx = getSequenceID($dbconn,'address_group_group_id_seq');
	$sqlcmd = "insert into address_group(group_id, contact_id, main_id, department, created_by, access_type) values ('$idx','$cid','$gid','0','$userid',1)";
	$updcount = "update address_group_main set total_contacts= total_contacts + 1 where group_id='$gid'";

	if(!doSQLcmd($dbconn, $sqlcmd)){
		error_log("FAIL: ".$sqlcmd);
	}
	if(!doSQLcmd($dbconn, $updcount)){
		error_log("FAIL: ".$updcount);
	}
	
	return;
}
function getServerInfo($grpid)
{
	global $dbconn;
	
	$cmd = "select group_id, group_name, ldap_id, ldap_location, total_contacts from address_group_main where group_id='".trim($grpid)."'";
	
	$obj = getSQLObj($dbconn, $cmd);
	if(is_object($obj)){
		
		$l_id = $obj->ldap_id;
		
		$sqlcmd = "select * from ldapserver where l_id='$l_id' ";
		$l_obj = getSQLObj($dbconn, $sqlcmd);
		
		if(is_object($l_obj)){
			return array($obj, $l_obj);
		}
	}
	
	return 'Fail to synchronize grop!';
}
?>
