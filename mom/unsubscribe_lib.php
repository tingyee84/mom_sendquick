<?php
require "lib/commonFunc.php";

$id = filter_input(INPUT_POST,'id');
$mode = filter_input(INPUT_POST,'mode');
$userid = strtolower($_SESSION['userid']);
$mobile = filter_input(INPUT_POST,'number');
$keyword = filter_input(INPUT_POST,'keyword');
$response = filter_input(INPUT_POST,'unsub_resp');
$msgstr = GetLanguage("lib_unsubscribe",$lang);
$x = GetLanguage("unsubscribe_list",$lang);

switch ($mode) {
	case "listUnsubscribe":
		listUnsubscribe();
		break;
	case "saveNumber":
		saveNumber($userid,$mobile);
		break;
	case "listKeyword":
		listKeyword($userid);
		break;
	case "saveKeyword":
		saveKeyword($userid, $keyword);
		break;
	case "deleteUnsubscribe":
		deleteUnsubscribe($id);
		break;
	case "deleteKeyword":
		deleteKeyword($id);
		break;
	case "emptyUnsubscribe":
		emptyUnsubscribe();
		break;
	case "getResponseMessage":
		getResponseMessage();
		break;
	case "saveResponse":
		saveResponse($userid, $response);
		break;
	case "uploadMobile":
		uploadMobile($userid, $_FILES['unsub_file']['tmp_name'],@$_FILES);
		break;
	default:
		die("Invalid Command");
}

function listUnsubscribe()
{
	global $dbconn;
	$source = '';

	$sqlcmd = "select unsubscribe_id, mobile_numb, to_char(created_dtm, 'YYYY-MM-DD HH24:MI:SS') as created_dtm,
				created_by,source from unsubscribe_list order by mobile_numb";
	$result = pg_query($dbconn, $sqlcmd);

	if(!$result) {
		error_log("listUnsubscribe: Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		echo "Database Error";
	} else {
		$arr_res = Array();

		for ($i=1; $row = pg_fetch_array($result); $i++){
			if($row['source'] == '2'){
				$source = 'Manual';
			} else if($row['source'] == '3'){
				$source = 'File Upload';
			}
			array_push($arr_res,Array(
				$row['mobile_numb'],
				$source,
				$row['created_by'],
				$row['created_dtm'],
				'<input type="checkbox" class="user_checkbox" name="no" value="'.$row['unsubscribe_id'].'">'
			));
		}
		echo json_encode(Array("data"=>$arr_res));
	}
}

function deleteUnsubscribe($id)
{
	global $dbconn;

	$sqlcmd = "delete from unsubscribe_list where unsubscribe_id='".dbSafe($id)."'";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if (!empty($res)) {
		error_log("deleteUnsubscribe: Database Error: ".$res);
		echo "Database Error";
	}
}

function emptyUnsubscribe()
{
	global $dbconn;

	$sqlcmd = "truncate unsubscribe_list";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if (!empty($res)) {
		error_log("emptyUnsubscribe: Database Error: ".$res);
		echo "Database Error";
	}
	
	insertAuditTrail( "Empty Unsubscribe Mobile Number" );
}

function saveNumber($userid,$mobile)
{
	global $dbconn, $msgstr;
	$data = array();

	if(checkNumExist($mobile)){		
		$data['flag'] = 0;
		$data['status'] = (string)$msgstr->unsubexist." - ".$mobile;
		$data['field'] = "number";
		error_log($msgstr->unsubexist." - ".$mobile);
	} else {
		
		$mobile_verify = validateMno( $mobile );
		
		if( $mobile_verify != "-1" ){
			
			$mobile = $mobile_verify;//updated mobile
			$unsubscribe_id = getSequence($dbconn,'unsubscribe_list_unsubscribe_id_seq');
			$sqlcmd = "INSERT INTO unsubscribe_list (unsubscribe_id, mobile_numb, created_by, created_dtm, source)
						values ('".$unsubscribe_id."', '".pg_escape_string(trim($mobile))."', '".$userid."', 'now()','2')";
			$res = doSQLcmd($dbconn,$sqlcmd);

			if (!$res) {
				$data['flag'] = 2;
				$data['status'] =  "Database Error";
				error_log("saveNumber: ".$sqlcmd.' -- '.pg_last_error($dbconn));
			}else{
				$data['flag'] = 1;
			}
		}else{
			$data['flag'] = 0;
			$data['status'] = (string)$msgstr->invalid_number;
			$data['field'] = "number";
		}		
	}
	
	insertAuditTrail( "Add Unsubscribe Mobile Number" );
	echo json_encode($data);
}

function saveKeyword($userid, $keyword)
{
	global $dbconn,$msgstr;
	$data = array();

	if(checkKwdExist($keyword)){
		$data['flag'] = 0;
		$data['status'] = (string) $msgstr->keywordfailed." - ".$keyword;
		$data['field'] = "keyword";
		echo json_encode($data);
		error_log($msgstr->keywordfailed." - ".$keyword);
		die;
	} 
	else if(!txvalidator($keyword,TX_STRING,"-")){
		$data['flag'] = 0;
		$data['status'] = (string) $msgstr->invalid_keyword;
		$data['field'] = "keyword";
		echo json_encode($data);		
		die;
	}
	else if(!validateSize($x->createtitle2,$keyword,"KEY")){
		$data['flag'] = 0;
		$data['status'] = (string) $msgstr->invalid_keyword;
		$data['field'] = "keyword";
		echo json_encode($data);		
		die;
	}
	else {
		$unkeyword_id = getSequence($dbconn,'unsub_keyword_id_seq');
		$sqlcmd = "Insert into unsub_keyword (id, keyword, created_by, created_dtm)
					values ('".$unkeyword_id."', '".pg_escape_string(trim($keyword))."', '".$userid."', 'now()' )";
		$res = doSQLcmd($dbconn,$sqlcmd);

		if (!$res) {
			$data['flag'] = 2;
			$data['status'] = "Database Error";
			error_log("saveKeyword: ".$sqlcmd.' -- '.pg_last_error($dbconn));
		}else{
			$data['flag'] = 1;
		}		
	}
	
	insertAuditTrail( "Add Unsubscribe keyword" );
	echo json_encode($data);
}

function listKeyword($userid)
{
	global $dbconn;

	$sqlcmd = "select id,keyword,created_by from unsub_keyword where
				created_by='".$userid."' order by keyword";
	$result = pg_query($dbconn, $sqlcmd);

	if(!$result) {
		error_log("listKeyword: Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		echo "Database Error";
	} else {
		$arr_res = Array();

		for ($i=1; $row = pg_fetch_array($result); $i++){
			array_push($arr_res,Array(
				htmlspecialchars($row['keyword'],ENT_QUOTES),
				htmlspecialchars($row['created_by']),
				'<input type="checkbox" class="user_checkbox" name="no" value="'.$row['id'].'">'
			));
		}
		echo json_encode(Array("data"=>$arr_res));
	}
}

function saveResponse($userid, $response)
{
	global $dbconn,$x;
	$data = array();

	if(!validateSize($x->respmsgtitle2,$response,"SHORTMSG")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "unsub_resp";
		echo json_encode($data);
		die;
	}

	$total = totalRecord("unsub_response","NA",$dbconn);
	if($total > 0) {
		$sqlcmd = "Update unsub_response set response='".dbSafe($response)."' where created_by='".$userid."'";
	} else {
		$unresp_id = getSequence($dbconn,'unsub_response_id_seq');
		$sqlcmd = "Insert into unsub_response (id,response,created_by)
					values ('".$unresp_id."','".dbSafe($response)."','".$userid."')";
	}

	$res = doSQLcmd($dbconn, $sqlcmd);

	if($res == 1){
		$data['flag'] = 1;
	}else{
		$data['flag'] = 2;
		$data['status'] = "Database Error";	
		error_log("saveResponse: Database Error: ($res) $sqlcmd ".pg_last_error($dbconn));
	}
	echo json_encode($data);
}

function getResponseMessage()
{
	global  $dbconn;

	$sqlcmd = "select response from unsub_response";
	$result = getSQLresult($dbconn, $sqlcmd);

	if(!empty($result)) {
		$response = trim($result[0]['response']);
	} else {
		$response = "Thanks";
	}

	echo json_encode($response);
}

function deleteKeyword($id)
{
	global $dbconn;

	$sqlcmd = "delete from unsub_keyword where id='".dbSafe($id)."'";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if (!empty($res)) {
		echo "Database Error";
		error_log("deleteKeyword: Database Error: ".$res);
	}
	
	insertAuditTrail( "Delete Unsubscribe Keyword" );
}

function uploadMobile($userid, $uploadfile, $file)
{
	global $dbconn;

	if(!$uploadfile){
		return 0;
	} 
	
	if($uploadfile ){
		$file_type = "txt_csv";		
		$chk_status = check_upload_file( $file['unsub_file'], $file_type);
	}

	if($chk_status['status'] == "1"){
		$err_mno = 0;
		$mno_exist = 0;
		$valid_count = 0;
		$file = fopen($uploadfile,'r');
		fseek($file, 0);

		while(!feof($file))
		{
			$mobile = trim(fgets($file));
			$validateMno = validateMno( $mobile );
			if(!is_numeric($mobile) || empty($mobile) || $validateMno == -1) { 
				$err_mno = 1; 
				continue; 
			}
			if(checkNumExist($mobile)){ 
				$mno_exist = 1;
				continue; 
			}

			$sqlcmd = "Insert into unsubscribe_list (mobile_numb,created_by,created_dtm,source)
						values ('".pg_escape_string(trim($validateMno))."','".$userid."',now(),'3')";
			doSQLcmd($dbconn, $sqlcmd);
			$valid_count++;
		}
		insertAuditTrail( "Upload Unsubscribe Mobile Number" );
		
		if($valid_count){
			echo 1;
		}else if($err_mno){
			echo 3;
		}else if($mno_exist){
			echo 4;
		}else{
			echo 0;
		}		
	}else{
		echo 2;
	}	
}

function checkNumExist($mobile)
{
	global $dbconn;

	$cmd = "select unsubscribe_id from unsubscribe_list where mobile_numb='".trim($mobile)."'";
	$res = pg_query($dbconn,$cmd);
	$rows = pg_num_rows($res);

	return $rows;
}

function checkKwdExist($keyword)
{
	global $dbconn;

	$cmd = "select id from unsub_keyword where lower(keyword)='".strtolower(trim($keyword))."'";
	$res = pg_query($dbconn,$cmd);
	$rows = pg_num_rows($res);

	return $rows;
}
?>
