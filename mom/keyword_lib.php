<?php
//Temporary increase in case of large file
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 0);
require_once "lib/commonFunc.php";
include("lib/db_keyword.php");
$x = GetLanguage("add_keyword",$lang);
$userid = $_SESSION['userid'];
$lang = $_SESSION['language'];
$mode = filter_input(INPUT_POST,'mode');
$keyword = filter_input(INPUT_POST,'keyword');
$desc = filter_input(INPUT_POST,'description');
$email = filter_input(INPUT_POST,'email');
$mobile_no = filter_input(INPUT_POST,'mobile_numb');
$url = filter_input(INPUT_POST,'url');
$xml_url = filter_input(INPUT_POST,'xml_url');
$soap_url = filter_input(INPUT_POST,'soap_url');
$soap_service = filter_input(INPUT_POST,'soap_service');
$json_url = filter_input(INPUT_POST,'json_url');
$message = filter_input(INPUT_POST,'message');
$textcount = filter_input(INPUT_POST,'textcount');
$reply_email =filter_input(INPUT_POST,'reply_email');
$subject = filter_input(INPUT_POST,'subject');
$content = filter_input(INPUT_POST,'content');
$sender = filter_input(INPUT_POST,'sender');
$sender_checklist = filter_input(INPUT_POST,'sender_checklist');
$autoreply = filter_input(INPUT_POST,'autoreply') ? filter_input(INPUT_POST,'autoreply') : "0";
$brochure = filter_input(INPUT_POST,'brochure');
$imei_no = filter_input(INPUT_POST,'imei_no');
$current_email_file = filter_input(INPUT_POST,'current_email_file');
$remove_curr_file = filter_input(INPUT_POST,'remove_current_email_file');

$serviceid = filter_input(INPUT_POST,'api_name');//value

switch ($mode) {
	case "listKeyword":
		listKeyword();
		break;
	case "listKeyword2":
		listKeyword2();
		break;
	case "addKeyword":
		addKeyword($keyword, $autoreply, $message, $brochure, $reply_email, $subject, $content, 
					$desc, $url, $xml_url, $soap_url, $soap_service, $json_url, $email, $mobile_no, 
					$sender, $sender_checklist, $_FILES["email_file"]["name"], $imei_no);
		break;
	case "addKeyword2":
		addKeyword2($keyword, $desc, $autoreply, $message);
		break;
	case "readKeyword":
		readKeyword($keyword);
		break;
	case "readKeyword2":
		readKeyword2($keyword);
		break;
	case "editKeyword":
		editKeyword($keyword, $autoreply, $message, $brochure, $reply_email, $subject, $content, $desc, 
					$url, $xml_url, $soap_url, $soap_service, $json_url, $email, $mobile_no, $sender, 
					$sender_checklist, $_FILES["email_file"]["name"], $current_email_file, $remove_curr_file, $imei_no);
		break;
	case "editKeyword2":
		editKeyword2($keyword, $autoreply, $message, $desc);
		break;
	case "deleteKeyword":
		deleteKeyword($keyword);
		break;
	case "emptyKeyword":
		emptyKeyword();
		break;
	// case "listApplications":
	// 	listApplications();
	// 	break;
	case "addApiKeyword":
		addApiKeyword($keyword, $desc, $url, $serviceid, $userid);
		break;
	case "editApiKeyword":
		editApiKeyword($keyword, $desc, $url, $serviceid, $userid);
		break;
	default:
		die("Unknown request");
}

function getSQLresultParams($dbconn, $sqlcmd, $args_array){
    global $lang; 

    $result = pg_query_params($dbconn, $sqlcmd, $args_array);
    if(!$result){
        $mainmsgstr = GetLanguage("lib",$lang);
		$main_db_err = (string)$mainmsgstr->db_err;
		return $main_db_err. " (" .$sqlcmd. ") -- " .pg_last_error($dbconn);
    }else{
        $row = pg_fetch_all($result);
		return $row;
    }
}

function editApiKeyword($keyword, $desc, $url, $serviceid, $userid)
{
	global $dbconn, $x;
	$data = array();
	//edit no need check duplicate, bcoz keyword name disabled and not changeable
	//list ($flag,$status,$field) = validateKeyword2($keyword);

	//if($flag!=1) {
		//$data['flag'] = $flag;
		//$data['status'] = $status;
		//$data['field'] = $field;
	//} else {
	
	if(!validateSize($x->keyword_description,$desc,"DESC")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "description";
	} else if(!txvalidator($url,TX_URL)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_url;
		$data['field'] = "url";
	} else{
		if(empty($desc)) {
			$desc = "NA";
		}
		
		$sqlcmd = " UPDATE mom_sms_response SET " .
				// "autoreply = '" .dbSafe($autoreply). "', " .
				// "autoreply_msg = '" .dbSafe($message). "', " .
				"descr = '" .dbSafe($desc). "'";
	
		
		if(!empty($serviceid)){
			$clientIdSql = "select dept, clientid from appn_list where serviceid = $1 ";
			$query_row = getSQLresultParams($dbconn, $clientIdSql, array($serviceid));
			if(count($query_row) > 0){
				$dept = $query_row[0]['dept'];
				$clientId = $query_row[0]['clientid'];
				$sqlcmd .= ", serviceid = '" . dbSafe($serviceid) . "', clientid = '" . dbSafe($clientId) . "', department = '" .dbSafe($dept) . "'";
			}
		}
	
		if(!empty($url)){
			$sqlcmd .= ", url = '" . dbSafe($url) . "' ";
		}
				
		$sqlcmd .= " WHERE keyword = '" .dbSafe($keyword). "' ";
		$row = doSQLcmd($dbconn, $sqlcmd);
		
		if(empty($row)) {
			$data['flag'] = 0;
			$data['status'] = "Database Error";
			error_log("editApiKeyword: ".pg_last_error());
		} else {
			$data['flag'] = 1;
		}
	}
			
	//}
	
	echo json_encode($data);
}

function addApiKeyword($keyword, $desc, $url, $serviceid, $userid )
{
	global $dbconn, $lang, $userid, $x;
	$data = array();

	list ($flag,$status,$field) = validateApiKeyword($keyword);
	
	if(checkKeyword($keyword)!=1) {
		$msgstr = GetLanguage("add_keyword",$lang);
		$data['flag'] = 0;
		$data['status'] = (string)$msgstr->alert_4;
		$data['field'] = "keyword";
	} else if($flag!=1) { 
		$data['flag'] = $flag;		
		$data['status'] = $status;
		$data['field'] = $field;
	} else if(!validateSize($x->keyword,$keyword,"KEY")) {
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "keyword";
	} else if(!validateSize($x->keyword_description,$desc,"DESC")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "description";
	} else if(!txvalidator($url,TX_URL)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_url;
		$data['field'] = "url";
	}else {
		if(empty($desc)) {
			$desc = "NA";
		}

		$querydept = "select dept, clientid from appn_list where serviceid = $1 ";
		$query_row = getSQLresultParams($dbconn, $querydept, array($serviceid));
		$department = $query_row[0]['dept'];
		$clientid = $query_row[0]['clientid'];
		
		$sqlcmd = "insert into mom_sms_response  
				(keyword, descr, cby, department, serviceid, clientid, url, in_use_status, type ) values
				( '".dbSafe($keyword)."', '".dbSafe($desc)."', '".dbSafe($userid)."','".dbSafe($department)."', '".dbSafe($serviceid)."', '".dbSafe($clientid)."', ".
				"'" .dbSafe($url). "','yes','1' )";
		
		$row = doSQLcmd($dbconn, $sqlcmd);
	
		if(empty($row)) {
			$data['flag'] = 2;
			$data['status'] = "Database Error";
			error_log("addApiKeyword: ".pg_last_error($dbconn));
		} else {
			$data['flag'] = 1;
		}
		
	}
	
	echo json_encode($data);
}

function listKeyword()
{
	global $keydbconn, $lang;
	$arr_res = Array();
	
	$sqlcmd = "SELECT keyword, keyword_desc, email, redirect_mobile, url, check_sender_mobile, modem_imei FROM sms_response order by keyword";
	$result = pg_query($keydbconn, $sqlcmd);

	if(!$result){
		$msgstr = GetLanguage("lib_keyword",$lang);
		$db_err = (string)$msgstr->db_err;
		echo $db_err;
		error_log("listKeyword: ".$db_err . " (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($keydbconn)));
	} else {
		for ($i=1; $row = pg_fetch_array($result); $i++){
			array_push($arr_res,Array(
				'<a href="keyword_edit.php?keyword='.$row['keyword'].'">'.htmlspecialchars($row['keyword'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>',
				htmlspecialchars($row['keyword_desc'],ENT_QUOTES),
				htmlspecialchars($row['email']),
				$row['redirect_mobile'],
				htmlspecialchars($row['url']),
				($row['check_sender_mobile'] == 'f' ? "No" : "Yes"),
				$row['modem_imei'],
				'<input type="checkbox" name="no" value="'.$row['keyword'].'">'
			));
		}
		echo json_encode(Array("data"=>$arr_res));
	}
}

function listKeyword2()
{
	global $dbconn, $lang, $userid;
	$arr_res = Array();
	
	//$sqlcmd = "SELECT id,keyword, descr, cby, department, type, serviceid, url FROM mom_sms_response order by keyword";
	$sqlcmd = "SELECT a.* FROM mom_sms_response a, user_list b where a.cby = b.userid and b.department = '".$_SESSION['department']."'";
	
	$result = pg_query($dbconn, $sqlcmd);

	if(!$result){
		$msgstr = GetLanguage("lib_keyword",$lang);
		$db_err = (string)$msgstr->db_err;
		echo $db_err;
		error_log("listKeyword2: ".$db_err . " (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
	} else {

		if( $_SESSION['userid'] == "useradmin" || $_SESSION['userid'] == "momadmin"){
			for ($i=1; $row = pg_fetch_array($result); $i++){
				
				if($row['type'] == "1"){
					$edit = ( strtolower($row['cby']) == strtolower($_SESSION['userid']) ? '<a href="keyword_api_edit.php?keyword='.$row['keyword'].'">'.htmlspecialchars($row['keyword'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>' : $row['keyword'] );
					$delete =  ( strtolower($row['cby']) == strtolower($_SESSION['userid']) ? '<input type="checkbox" class="user_checkbox" name="no" value="'.$row['id'].'">' : '' );
				}else{
					$edit = ( strtolower($row['cby']) == strtolower($_SESSION['userid']) ? '<a href="keyword_edit.php?keyword='.$row['keyword'].'">'.htmlspecialchars($row['keyword'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>' : $row['keyword'] );
					$delete =  ( strtolower($row['cby']) == strtolower($_SESSION['userid']) ? '<input type="checkbox" name="no" class="user_checkbox" value="'.$row['id'].'">' : '' );
				}
				
				$from = $row['type'] == "0" ? "Portal" : "API";
				$department = getDepartmentName( $row['department'] );
				
				array_push($arr_res,Array(
					$edit,
					htmlspecialchars($row['descr'],ENT_QUOTES),
					$from,
					$department,
					htmlspecialchars($row['serviceid']),
					htmlspecialchars($row['url']),
					$delete
				));
			}
		}else{
			for ($i=1; $row = pg_fetch_array($result); $i++){
			
				if( strtolower( $_SESSION['department'] ) == strtolower( $row['department'] ) ){
					$edit = ( strtolower($row['cby']) == strtolower($_SESSION['userid']) ? '<a href="keyword_edit.php?keyword='.$row['keyword'].'">'.htmlspecialchars($row['keyword'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>' : $row['keyword'] );
					$delete = ( strtolower($row['cby']) == strtolower($_SESSION['userid']) ? '<input type="checkbox" name="no" class="user_checkbox" value="'.$row['id'].'">' : '' );
				}else{
					$edit = htmlspecialchars($row['keyword'],ENT_QUOTES);
					$delete = '';
				}
				
				$from = $row['type'] == "0" ? "Portal" : "API";
				$department = getDepartmentName( $row['department'] );
				
				array_push($arr_res,Array(
					$edit,
					htmlspecialchars($row['descr']),
					$from,
					$department,
					htmlspecialchars($row['serviceid']),
					htmlspecialchars($row['url']),
					$delete
				));
			}
		}

		
		
	}
	
	echo json_encode(Array("data"=>$arr_res));
}

function readKeyword($keyword)
{
	global $keydbconn, $lang;

	$msgstr = GetLanguage("lib_keyword",$lang);
	$readKeyword_msg1 = (string)$msgstr->readKeyword_msg1;
	$readKeyword_msg2 = (string)$msgstr->readKeyword_msg2;
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "SELECT keyword,keyword_desc,email,redirect_mobile,url,xml_url,soap_url,soap_service,
				json_url,autoreply,autoreply_msg,email_brochure,file,reply_address,subject,email_body,
				check_sender_mobile,mobile_allowed_list,modem_imei 
				FROM sms_response WHERE keyword='".dbSafe($keyword)."'";
	$row = getSQLresult($keydbconn, $sqlcmd);

	if(is_string($row))
	{
		echo $db_err;
		error_log("readKeyword: ".$db_err . " (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($keydbconn)));
	}
	else
	{
		if(empty($row))
		{
			echo $readKeyword_msg1. " '" .htmlspecialchars($id). "' ". $readKeyword_msg2;
		}
		else
		{
			echo json_encode($row);
		}
	}
}

function readKeyword2($keyword)
{
	global $dbconn, $lang, $userid;

	$msgstr = GetLanguage("lib_keyword",$lang);
	$readKeyword_msg1 = (string)$msgstr->readKeyword_msg1;
	$readKeyword_msg2 = (string)$msgstr->readKeyword_msg2;
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "SELECT keyword, descr as keyword_desc, autoreply, autoreply_msg, department, serviceid,
				url, type FROM mom_sms_response WHERE keyword='".dbSafe($keyword)."'";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row))
	{
		echo $db_err;
		error_log("readKeyword2: ".$db_err . " (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
	}
	else
	{
		if(empty($row))
		{
			echo $readKeyword_msg1. " '" .htmlspecialchars($id). "' ". $readKeyword_msg2;
		}
		else
		{
			echo json_encode($row);
		}
	}
}

function addKeyword($keyword, $autoreply, $message, $brochure, $reply_email, $subject, 
					$content, $desc, $url, $xml_url, $soap_url, $soap_service, $json_url, 
					$email, $mobile_no, $sender, $sender_checklist, $uploadname, $imei_no)
{
	global $keydbconn, $lang;
	$data = array();
	
	list ($flag,$status,$field) = validateKeyword($email,$reply_email,$mobile_no,$sender_checklist,$url,$xml_url,$soap_url,$json_url);
	
	if(checkKeyword($keyword)!=1) {
		$msgstr = GetLanguage("add_keyword",$lang);
		$data['flag'] = 0;
		$data['status'] = (string)$msgstr->alert_4;
		$data['field'] = "keyword";
	} else if($flag!=1) { 
		$data['flag'] = $flag;
		$data['status'] = $status;
		$data['field'] = $field;
	} else {
		if(empty($desc)) {
			$desc = "NA";
		}
		if(empty($unsub)){
			$unsub = "0";
		}
		if(empty($email)) {
			$email = "NA";
		}
		if(empty($mobile_no)) {
			$mobile_no = "NA";
		}
		if(empty($url)) {
			$url = "NA";
		}
		if(empty($xml_url)) {
			$xml_url = "NA";
		}
		if(empty($soap_url)) {
			$soap_url = "NA";
		}
		if(empty($json_url)) {
			$json_url = "NA";
		}
		if(empty($autoreply)) {
			$autoreply = 0;
		}
		if(empty($reply_email)) {
			$reply_email = "NA";
		}
		if(empty($subject)) {
			$subject = "NA";
		}
		if(empty($content)) {
			$content = "NA";
		}
		if(empty($sender)) {
			$sender = "false";
		}
		
		$file_data = "";
		if(!empty($brochure)) {
			if(!empty($_FILES['email_file']['tmp_name'])) {
				$uploadname = preg_replace("/[^A-Za-z0-9-_\.]+/", "", $uploadname);
				$uploadfile = "/home/msg/tmp/".$uploadname;
			
				if(is_uploaded_file($_FILES["email_file"]["tmp_name"])) {
					if (!move_uploaded_file($_FILES["email_file"]["tmp_name"], $uploadfile)) {
						error_log("Moved failed - ".$_FILES["email_file"]["error"]);
					} else {
						$imagedata = file_get_contents($uploadfile);
						$file_data = base64_encode($imagedata);
					}
				}  else {
					error_log("Upload failed - ".$_FILES["email_file"]["error"]);
				}
			} else {
				$uploadname = "NA";
			}
		} else {
			$brochure = 0;	
		}
		
		$sqlcmd = "insert into sms_response  
				(keyword, autoreply, autoreply_msg, email_brochure, reply_address, subject, email_body, keyword_desc, url, email, redirect_mobile, 
				file, unsub_flag,file_data, xml_url, soap_url, soap_service, json_url,  check_sender_mobile, mobile_allowed_list, modem_imei) values 
				('".dbSafe($keyword)."','".dbSafe($autoreply)."','".dbSafe($message)."','".dbSafe($brochure)."', 
				'".dbSafe($reply_email)."','".dbSafe($subject)."','".dbSafe($content)."','".dbSafe($desc)."', 
				'".dbSafe($url)."','".dbSafe($email)."','".dbSafe($mobile_no)."','".dbSafe($uploadname)."', 
				'0','".$file_data."','".dbSafe($xml_url )."','".dbSafe($soap_url)."','".dbSafe($soap_service)."',
				'".dbSafe($json_url)."','".dbSafe($sender)."','". dbSafe($sender_checklist)."','".dbSafe($imei_no)."')";
		$row = doSQLcmd($keydbconn, $sqlcmd);
	
		if(empty($row)) {
			$data['flag'] = 0;
			$data['status'] = "Database Error";
			error_log("addKeyword: ".pg_last_error());
		} else {
			$data['flag'] = 1;
		}
	}
	
	echo json_encode($data);
}

function addKeyword2($keyword, $desc, $autoreply, $message )
{
	global $dbconn, $lang, $userid, $x;
	$data = array();
	
	list ($flag,$status,$field) = validateKeyword2($keyword,$_SESSION['department']);
	// flag: 1 - ok, 0 - data error, 2 - db error	
	if(checkKeyword($keyword)!=1) {
		$msgstr = GetLanguage("add_keyword",$lang);
		$data['flag'] = 0;
		$data['status'] = (string)$msgstr->alert_4;
		$data['field'] = "keyword";
	} else if($flag!=1) { 
		$data['flag'] = $flag;
		$data['status'] = $status;
		$data['field'] = $field;		
	} else if(!validateSize($x->keyword,$keyword,"KEY")) {
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "keyword";
	} else if(!validateSize($x->keyword_description,$desc,"DESC")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "description";
	} else if(!validateSize($x->standard,$message,"SHORTMSG")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "message";
	}
	else {
		if(empty($desc)) {
			$desc = "NA";
		}
		
		$sqlcmd = "insert into mom_sms_response  
				(keyword, descr, autoreply, autoreply_msg, cby, department ) values 
				( '".dbSafe($keyword)."', '".dbSafe($desc)."', '".dbSafe($autoreply)."','".dbSafe($message)."', '".$userid."', '".$_SESSION['department']."' )";
		
		$row = doSQLcmd($dbconn, $sqlcmd);
	
		if(empty($row)) {
			$data['flag'] = 2;
			$data['status'] = "Database Error";
			error_log("addKeyword2: ".pg_last_error($dbconn));
		} else {
			$data['flag'] = 1;
			insertAuditTrail( "New keyword <$keyword> added" );
		}
		
	}
	
	//$data['flag'] = 0;
	//$data['status'] = $sqlcmd;
	
	echo json_encode($data);
}

function editKeyword($keyword, $autoreply, $message, $brochure, $reply_email, $subject, $content, 
					$desc, $url, $xml_url, $soap_url, $soap_service, $json_url, $email, $mobile_no, $sender, 
					$sender_checklist, $uploadname, $current_email_file, $remove_curr_file, $imei_no)
{
	global $keydbconn;
	$data = array();
	list ($flag,$status,$field) = validateKeyword($email,$reply_email,$mobile_no,$sender_checklist,$url,$xml_url,$soap_url,$json_url);

	if($flag!=1) {
		$data['flag'] = $flag;
		$data['status'] = $status;
		$data['field'] = $field;
	} else {
		if(empty($desc)) {
			$desc = "NA";
		}
		if(empty($unsub)){
			$unsub = "0";
		}
		if(empty($email)) {
			$email = "NA";
		}
		if(empty($mobile_no)) {
			$mobile_no = "NA";
		}
		if(empty($url)) {
			$url = "NA";
		}
		if(empty($xml_url)) {
			$xml_url = "NA";
		}
		if(empty($soap_url)) {
			$soap_url = "NA";
		}
		if(empty($json_url)) {
			$json_url = "NA";
		}
		if(empty($autoreply)) {
			$autoreply = 0;
		}
		if(empty($reply_email)) {
			$reply_email = "NA";
		}
		if(empty($subject)) {
			$subject = "NA";
		}
		if(empty($content)) {
			$content = "NA";
		}
		if(empty($sender)) {
			$sender = "false";
		}
		
		if($remove_curr_file == 1){
			$curr_file = "/home/msg/tmp/".$current_email_file;
			if(file_exists($curr_file)) {
				unlink($curr_file);
			}
		}
		
		$file_data = "";
		if(!empty($brochure)) {
			if(!empty($_FILES['email_file']['tmp_name'])) {
				$uploadname = preg_replace("/[^A-Za-z0-9-_\.]+/", "", $uploadname);
				$uploadfile = "/home/msg/tmp/".$uploadname;
			
				if(is_uploaded_file($_FILES["email_file"]["tmp_name"])) {
					if (!move_uploaded_file($_FILES["email_file"]["tmp_name"], $uploadfile)) {
						error_log("Moved failed - ".$_FILES["email_file"]["error"]);
					} else {
						$imagedata = file_get_contents($uploadfile);
						$file_data = base64_encode($imagedata);
					}
				}  else {
					error_log("Upload failed - ".$_FILES["email_file"]["error"]);
				}
			} else {
				$uploadname = "NA";
			}
		} else {
			$brochure = 0;	
		}
		
		$sqlcmd = " UPDATE sms_response SET " .
				"autoreply = '" .dbSafe($autoreply). "', " .
				"autoreply_msg = '" .dbSafe($message). "', " .
				"email_brochure = '" .dbSafe($brochure). "', " .
				"reply_address = '" .dbSafe($reply_email). "', " .
				"subject = '" .dbSafe($subject). "', " .
				"email_body = '" .dbSafe($content). "', " .
				"keyword_desc = '" .dbSafe($desc). "', " .
				"url = '" .dbSafe($url). "', " .
				"xml_url = '" .dbSafe($xml_url). "', " .
				"soap_url = '" .dbSafe($soap_url). "', " .
				"soap_service = '" .dbSafe($soap_service). "', " .
				"json_url = '" .dbSafe($json_url). "', " .
				"email = '" .dbSafe($email). "', " .
				"redirect_mobile = '" .dbSafe($mobile_no). "', ".
				"check_sender_mobile = '" .dbSafe($sender). "', " .
				"mobile_allowed_list = '" .dbSafe($sender_checklist). "', " .
				"modem_imei = '" .dbSafe($imei_no). "'";
				
		if( $remove_curr_file == 1 || (strlen($uploadname) > 0 && strcmp(strtoupper($uploadname), "NA") != 0 )){
			$sqlcmd .= ", file='" .dbSafe($uploadname). "' ,file_data='".$file_data."'";
		}
		
		$sqlcmd .= " WHERE keyword = '" .dbSafe($keyword). "' ";
		$row = doSQLcmd($keydbconn, $sqlcmd);
		
		if(empty($row)) {
			$data['flag'] = 0;
			$data['status'] = "Database Error";
			error_log("editKeyword: ".pg_last_error());
		} else {
			$data['flag'] = 1;
		}
	}
	
	echo json_encode($data);
}

function editKeyword2($keyword, $autoreply, $message, $desc)
{
	global $dbconn, $x;
	$data = array();
	//edit no need check duplicate, bcoz keyword name disabled and not changeable
	//list ($flag,$status,$field) = validateKeyword2($keyword);

	//if($flag!=1) {
		//$data['flag'] = $flag;
		//$data['status'] = $status;
		//$data['field'] = $field;
	//} else {
		
		if(!validateSize($x->keyword,$keyword,"KEY")) {
			$data['flag'] = 0;
			$data['status'] = (string)getValidateSizeMsg();
			$data['field'] = "keyword";
		} else if(!validateSize($x->keyword_description,$desc,"DESC")){
			$data['flag'] = 0;
			$data['status'] = (string)getValidateSizeMsg();
			$data['field'] = "description";
		} else if(!validateSize($x->standard,$message,"SHORTMSG")){
			$data['flag'] = 0;
			$data['status'] = (string)getValidateSizeMsg();
			$data['field'] = "message";
		}else{

			if(empty($desc)) {
				$desc = "NA";
			}
			
			$sqlcmd = " UPDATE mom_sms_response SET " .
					"autoreply = '" .dbSafe($autoreply). "', " .
					"autoreply_msg = '" .dbSafe($message). "', " .
					"descr = '" .dbSafe($desc). "'";
					
			$sqlcmd .= " WHERE keyword = '" .dbSafe($keyword). "' ";
			$row = doSQLcmd($dbconn, $sqlcmd);
			
			if(empty($row)) {
				$data['flag'] = 2;
				$data['status'] = "Database Error";
				error_log("editKeyword2: ".pg_last_error($dbconn));
			} else {
				$data['flag'] = 1;
				insertAuditTrail( "Edited keyword: $keyword" );
			}
		}	
		
	//}
	
	echo json_encode($data);
}

function deleteKeyword($keyword_id)
{
	global $dbconn;

	$sqlcmd = "DELETE FROM mom_sms_response WHERE id='".dbSafe($keyword_id)."'";
	$res = doSQLcmd($dbconn, $sqlcmd);
	
	if (!empty($res)) { 
		error_log("deleteKeyword: ".pg_last_error());
		echo "Database Error";		
	}else{
		insertAuditTrail( "Keyword ID <$keyword_id> deleted" );
	}
	
}

function emptyKeyword()
{
	global $keydbconn;

	$sqlcmd = "DELETE FROM sms_response";
	$res = doSQLcmd($keydbconn, $sqlcmd);
	
	if (!empty($res)) { 
		error_log("emptyKeyword: ".pg_last_error());
		echo "Database Error";  
	}
}

function checkKeyword($keyword)
{
	global $keydbconn;
	
	$lower_keyword = stripslashes(strtolower($keyword));
	$sqlcmd = "SELECT keyword AS id FROM sms_response WHERE lower(keyword)='".dbSafe($lower_keyword)."'";
	$row = getSQLresult($keydbconn, $sqlcmd);
	
	if(is_string($row))
	{
		return 2;
	}
	else
	{
		if(!empty($row))
		{
			return 0;
		}
		else
		{
			return 1;
		}
	}
}

function validateKeyword($email,$reply_email,$mobile_no,$sender_checklist,$url,$xml_url,$soap_url,$json_url)
{
	$flag = "0";
	$status = "";
	$field = "";
	
	if(validateType("email",$email)) {
		$status = "Invalid Email! Please insert 'NA' if no email address available.";
		$field = "email";
	} else if(validateType("mobile",$mobile_no)) {
		$status = "Invalid Mobile Number! Please insert 'NA' if no mobile number available.";
		$field = "mobile_numb";
	} else if(validateType("mobile",$sender_checklist)) {
		$status = "Invalid Sender Mobile Checklist! Please insert 'NA' if no mobile number available.";
		$field = "sender_checklist";
	} else if (validateType("url",$url)) {
		$status = "Invalid URL address! Please insert 'NA' if no URL available.";
		$field = "url";
	} else if (validateType("url",$xml_url)) {
		$status = "Invalid XML URL! Please insert 'NA' if no XML URL available.";
		$field = "xml_url";
	} else if (validateType("url",$soap_url)) {
		$status = "Invalid SOAP URL! Please insert 'NA' if no SOAP URL available.";
		$field = "soap_url";
	} else if (validateType("url",$json_url)) {
		$status = "Invalid JSON URL! Please insert 'NA' if no JSON URL available.";
		$field = "json_url";
	} else if(validateType("email",$reply_email)) {
		$status = "Invalid Reply Email! Please insert 'NA' if no reply email address available.";
		$field = "reply_email";
	} else {
		$flag = "1";
	}
	
	return array ($flag,$status,$field);
}

function validateApiKeyword($keyword)
{
	global $dbconn, $x;
	
	$flag = "0";
	$status = "";
	$field = "";
	
	$sqlcmd = "SELECT count(*) as found FROM mom_sms_response WHERE lower(keyword)='".dbSafe( strtolower($keyword) )."'";
	$appn_keyword_found1 = getSQLresult($dbconn, $sqlcmd);

	$sqlcmd2 = "select count(*) as found2 from unsub_keyword where lower(keyword)='".dbSafe( strtolower($keyword) )."'";
	$found2 = getSQLresult($dbconn, $sqlcmd2);
	
	if( $keyword == "" ){
		$status = "Invalid keyword";
		$field = "keyword";
	}else if(!txvalidator($keyword,TX_STRING,"-")){		
		$status = (string)$x->invalid_keyword;
		$field = "keyword";
	}else if( $appn_keyword_found1[0]['found'] ){
		$status = "Duplicated keyword ";
		$field = "keyword";
	}else if( $found2[0]['found2'] ){
		$status = "Duplicated keyword in Unsubscribe Keyword ";
		$field = "keyword";
	}else{
		//no error
		$flag = "1";
	}
	
	return array ($flag,$status,$field);
}

function validateKeyword2($keyword, $department)
{
	global $dbconn,$x;
	
	$flag = "0";
	$status = "";
	$field = "";
	
	$sqlcmd = "SELECT count(*) as found FROM mom_sms_response WHERE lower(keyword)='".dbSafe( strtolower($keyword) )."' and department = '".$department."'";
	$appn_keyword_found1 = getSQLresult($dbconn, $sqlcmd);
	
	$sqlcmd2 = "select count(*) as found2 from unsub_keyword where lower(keyword)='".dbSafe( strtolower($keyword) )."'";
	$found2 = getSQLresult($dbconn, $sqlcmd2);
	
	if( $keyword == "" ){
		$status = "Invalid keyword";
		$field = "keyword";
	}else if(!txvalidator($keyword,TX_STRING,"-")){
		$status = $x->invalid_keyword;
		$field = "keyword";
	}
	else if( $appn_keyword_found1[0]['found'] ){
		$status = "Duplicated keyword ";
		$field = "keyword";
	}else if( $found2[0]['found2'] ){
		$status = "Duplicated keyword in Unsubscribe Keyword ";
		$field = "keyword";
	}else{
		//no error
		$flag = "1";
	}
	
	return array ($flag,$status,$field);
}

function validateType($type,$value)
{
	if (!empty($value) && $value!="NA")
	{
		switch ($type) {
			case 'email':
				$pattern = FILTER_VALIDATE_EMAIL;
				break;
			case 'url':
				$pattern = FILTER_VALIDATE_URL;
				break;
			case 'mobile':
				$pattern = FILTER_VALIDATE_INT;
				break;
		}
		$line = explode(PHP_EOL, trim($value));
		foreach($line as $list){
			if (!empty(trim($list)) && !filter_var(trim($list), $pattern)) {
				return 1;
			}
		}
	}
	return 0;
}
?>
