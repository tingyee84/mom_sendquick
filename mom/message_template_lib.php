<?php
require "lib/commonFunc.php";

$id = filter_input(INPUT_POST,'id');
$mode = filter_input(INPUT_POST,'mode');
$text = filter_input(INPUT_POST,'template');
$access_type = filter_input(INPUT_POST,'access_type');
$template_name = filter_input(INPUT_POST,'template_name');

$userid = strtolower($_SESSION['userid']);
$id_of_user = getUserID($userid);
$department = $_SESSION['department'];
$msgstr = GetLanguage("lib_message_template",$lang);
$xml_common = GetLanguage("common",$lang);
$x = GetLanguage("message_template",$lang);
$mim = GetLanguage("mim_message_template",$lang);

$mim_tpl_id = filter_input(INPUT_POST,'mim_tpl_id');

$max_sms = $_SESSION['max_sms'];
//$mode = "listMIMTemplate";//for test only

//echo $mode;
//die;

switch ($mode) {
	case "listMessageTemplate":
        listMessageTemplate($userid);
        break;
	case "listGlobalTemplate":
        listGlobalTemplate($userid);
        break;
	case "listMIMTemplate":
        listMIMTemplate($userid);
        break;
	case "listGlobalMIMTemplate":
        listGlobalMIMTemplate($userid, $id_of_user );
        break;
	case "addMessageTemplate":
        addMessageTemplate($userid, $id_of_user, $text, $department, $template_name);
        break;
	case "addGlobalTemplate":
        addGlobalTemplate($userid, $id_of_user, $text, $department, $template_name);
        break;
	case "addMIMTemplate":
        addMIMTemplate($userid, $id_of_user, $text, $department, $mim_tpl_id, $template_name);
        break;
	case "addGlobalMIMTemplate":
        addGlobalMIMTemplate($userid, $id_of_user, $text, $department, $mim_tpl_id, $template_name);
        break;
	case "editMessageTemplate":
        editMessageTemplate($id);
        break;
	case "editGlobalTemplate":
		editGlobalTemplate($id);
		break;
	case "editMIMTemplate":
		editMIMTemplate($id);
		break;
	case "editGlobalMIMTemplate":
		editGlobalMIMTemplate($id);
		break;
	case "saveMessageTemplate":
        saveMessageTemplate($userid, $id, $text, $department, $template_name);
        break;
	case "saveGlobalTemplate":
        saveGlobalTemplate($userid, $id, $text, $department, $template_name);
        break;
	case "saveMIMTemplate":
        saveMIMTemplate($userid, $id, $text, $department, $mim_tpl_id, $template_name);
        break;
	case "saveGlobalMIMTemplate":
        saveGlobalMIMTemplate($userid, $id, $text, $department, $mim_tpl_id, $template_name);
        break;
	case "deleteMessageTemplate":
        deleteMessageTemplate($id);
        break;
	case "deleteGlobalTemplate":
        deleteGlobalTemplate($userid, $id);
        break;
	case "deleteMIMTemplate":
        deleteMIMTemplate($userid, $id);
        break;
	case "deleteGlobalMIMTemplate":
        deleteGlobalMIMTemplate($userid, $id);
        break;
	case "emptyMessageTemplate":
		emptyMessageTemplate($userid);
		break;
	case "emptyGlobalTemplate":
		emptyGlobalTemplate($userid);
		break;
	case "emptyMIMTemplate":
		emptyMIMTemplate($userid);
		break;
	case "insertTemplate":
		insertTemplate($userid,$_FILES['template_file']['tmp_name'],$department,$access_type,$_FILES['template_file'],$max_sms);
		break;
	case "listTemplate":
		listTemplate($userid, $department, $access_type);
		break;
	case "addTemplate":
		addTemplate($userid, $department, $id_of_user, $access_type);
		break;
	case "deleteTemplate":
		deleteTemplate($userid, $access_type);
		break;
    default:
		die("Invalid Command");
}

function listMessageTemplate($userid)
{
	global $dbconn;

	$sqlcmd = "select template_name, template_id, template_text from message_template where created_by='".dbSafe($userid)."' and access_type='0' order by template_text";
	$result = pg_query($dbconn, $sqlcmd);
	
	if(!$result) {
		error_log("listMessageTemplate: ".$db_err." (" .$sqlcmd. ") -- " .pg_last_error($dbconn));
		echo $db_err;
	} else {
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			array_push($result_array,Array(
				$row['template_name'],
				'<a href="#myCreate" data-bs-toggle="modal" data-id="'.$row['template_id'].'">'.htmlspecialchars($row['template_text'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>',
				'<input type="checkbox" name="no" value="'.$row['template_id'].'">'
			));			
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function addMessageTemplate($userid, $user_id, $text, $department, $template_name)
{
	global $dbconn, $msgstr, $x;
	$data = array();
		
	$addMessageTemplate_msg1 = (string)$msgstr->addMessageTemplate_msg1;
	$addMessageTemplate_msg2 = (string)$msgstr->addMessageTemplate_msg2;
	
	if(!txvalidator($template_name,TX_STRING,"SPACE")){
		$data['flag'] = 0;
		$data['status'] = (string) $x->invalid_template_name;
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!validateSize($x->new_tpl_name_text,$template_name,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}

	$template_id = getSequenceID($dbconn,'message_template_template_id_seq');
	$sqlcmd = "insert into message_template (template_id, template_text, department, user_id, created_by, template_name ) 
				values ('".dbSafe($template_id)."','".dbSafe(trim($text))."','".dbSafe($department)."','".dbSafe($user_id)."','".dbSafe($userid)."', '".dbSafe($template_name)."')";
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if(empty($row))
	{
		$data['flag'] = 2;
		$data['status'] = $addMessageTemplate_msg2;
	}else{
		$data['flag'] = 1;	
	}	
	
	echo json_encode($data);
}

function addMessageTemplate_UPLOAD($userid, $user_id, $text, $department, $template_name)
{
	global $dbconn, $msgstr, $x;
	$data = array();
		
	$addMessageTemplate_msg1 = (string)$msgstr->addMessageTemplate_msg1;
	$addMessageTemplate_msg2 = (string)$msgstr->addMessageTemplate_msg2;
	
	if(!txvalidator($template_name,TX_STRING,"SPACE")){
		$data['flag'] = 0;
		$data['status'] = (string) $x->invalid_template_name;
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!validateSize($x->new_tpl_name_text,$template_name,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}

	$template_id = getSequenceID($dbconn,'message_template_template_id_seq');
	$sqlcmd = "insert into message_template (template_id, template_text, department, user_id, created_by, template_name ) 
				values ('".dbSafe($template_id)."','".dbSafe(trim($text))."','".dbSafe($department)."','".dbSafe($user_id)."','".dbSafe($userid)."', '".dbSafe($template_name)."')";
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if(empty($row))
	{
		$data['flag'] = 2;
		$data['status'] = $addMessageTemplate_msg2;
	}else{
		$data['flag'] = 1;	
	}	
	
	// echo json_encode($data);
	return json_encode($data);
}

function editMessageTemplate($id)
{
	global $dbconn, $msgstr, $xml_common;

	$editMessageTemplate_msg1 = (string)$msgstr->editMessageTemplate_msg1;
	$editMessageTemplate_msg2 = (string)$msgstr->editMessageTemplate_msg2;
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "select template_name, template_text from message_template where template_id='".dbSafe($id)."' and access_type='0'";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row))
	{
		echo $db_err;
		error_log("editMessageTemplate: ".$db_err. " (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
	}
	else
	{
		
		if(mb_detect_encoding($row[0]['template_text'], 'ASCII', true)){
			$charset_html = "<option value=\"text\" selected>$xml_common->ascii</option>".
							"<option value=\"utf8\">$xml_common->utf8</option>";
			$count_chars2 = mb_strlen($row[0]['template_text']);
		}else{
			$charset_html = "<option value=\"text\">$xml_common->ascii</option>".
							"<option value=\"utf8\" selected>$xml_common->utf8</option>";
			$count_chars2 = mb_strlen($row[0]['template_text'], "UTF-8");
		}
				
		$sms_num = getSMSNeeded($row[0]['template_text']);

		$result_array = array();
		$result_array['text'] = $row[0]['template_text'];
		$result_array['template_name'] = $row[0]['template_name'];
		$result_array['charset'] = $charset_html;
		$result_array['count_chars2'] = $count_chars2;
		$result_array['sms_num'] = $sms_num." / 10";
		echo json_encode($result_array);
	}
}

function saveMessageTemplate($userid, $id, $text, $department, $template_name)
{
	global $dbconn, $msgstr, $x;
	$data = array();

	$saveMessageTemplate_msg1 = (string)$msgstr->saveMessageTemplate_msg1;
	$saveMessageTemplate_msg2 = (string)$msgstr->saveMessageTemplate_msg2;

	if(!txvalidator($template_name,TX_STRING,"SPACE")){
		$data['flag'] = 0;
		$data['status'] = (string) $x->invalid_template_name;
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!validateSize($x->new_tpl_name_text,$template_name,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}

	$sqlcmd = "update message_template set template_name = '".dbSafe($template_name)."', template_text='".dbSafe(trim($text))."',department='".dbSafe($department)."',
				modified_by='".dbSafe($userid)."',modified_dtm= 'now()' 
				where template_id='".dbSafe($id)."' and access_type='0'";
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if($row == 0)
	{
		$data['flag'] = 2;
		$data['status'] = $saveMessageTemplate_msg2; //unsuccessful
	}else{
		$data['flag'] = 1;
	}
	
	echo json_encode($data);
}

function deleteMessageTemplate($id)
{
	global $dbconn;

	$sqlcmd = "delete from message_template where template_id='".dbSafe($id)."' and access_type='0'";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if (!empty($res)) { 
		error_log("deleteMessageTemplate: Database Error: ".$res);
		echo "Database Error";
	}
}

function emptyMessageTemplate($userid)
{
	global $dbconn;

	$sqlcmd = " delete from message_template where created_by='".dbSafe($userid)."' and access_type='0'";
	$res = doSQLcmd($dbconn, $sqlcmd);
	
	if (!empty($res)) { 
		error_log("emptyMessageTemplate: Database Error: ".$res);
		echo "Database Error";
	}
}

function listGlobalTemplate($userid)
{
	global $dbconn;
	$UserType = getUserType( $userid );

	if(isUserAdmin($userid))
	{
		$sqlcmd = "select message_template.created_by, template_name, template_id, template_text, department_list.department as department, department_id from message_template left outer join department_list on (message_template.department = department_list.department_id) 
					where access_type='1' order by template_text";
	}
	else
	{
		$department = getUserDepartment($userid);
		$sqlcmd = "select message_template.created_by, template_name, template_id, template_text, department_list.department as department, department_id from message_template left outer join department_list on (message_template.department = department_list.department_id) 
					where message_template.department='".dbSafe($department)."' and access_type='1' order by template_text";
	}

	$result = pg_query($dbconn, $sqlcmd);
	
	if(!$result) {		
		error_log("listGlobalTemplate: ".$db_err." (" .$sqlcmd. ") -- " .pg_last_error($dbconn));
		echo $db_err;
	} else {
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			
			if( $row['created_by'] == $userid ){
				$edit = '<a href="#myCreate" data-bs-toggle="modal" data-id="'.$row['template_id'].'">'.htmlspecialchars($row['template_text'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>';
				$delete = '<input type="checkbox" name="no" value="'.$row['template_id'].'">';
			}else{
				
				if(isUserAdmin($userid)){
					$edit = '<a href="#myCreate" data-bs-toggle="modal" data-id="'.$row['template_id'].'">'.htmlspecialchars($row['template_text'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>';
					$delete = '<input type="checkbox" name="no" value="'.$row['template_id'].'">';
				}elseif( strtolower($UserType) == "bu" ){
					$edit = '<a href="#myCreate" data-bs-toggle="modal" data-id="'.$row['template_id'].'">'.htmlspecialchars($row['template_text'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>';
					$delete = '<input type="checkbox" name="no" value="'.$row['template_id'].'">';
				}else{
					$edit = htmlspecialchars($row['template_text'],ENT_QUOTES);
					$delete = '';
				}
		
			}
			
			array_push($result_array,Array(
				htmlspecialchars($row['template_name'],ENT_QUOTES),
				$edit,
				(empty($row['department']) ? 'No Department' : htmlspecialchars($row['department'],ENT_QUOTES)),
				$delete
			));
			
			/*
			array_push($result_array,Array(
				$row['template_name'],
				'<a href="#myCreate" data-bs-toggle="modal" data-id="'.$row['template_id'].'">'.htmlspecialchars($row['template_text']).' <i class="fa fa-pencil-square-o fa-fw"></i></a>',
				(empty($row['department']) ? 'No Department' : htmlspecialchars($row['department'])),
				'<input type="checkbox" name="no" value="'.$row['template_id'].'">'
			));
			*/
			
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function addGlobalTemplate($userid, $user_id, $text, $department, $template_name)
{
	global $dbconn, $msgstr, $x;
	$data = array();
	$addMessageTemplate_msg1 = (string)$msgstr->addMessageTemplate_msg1;
	$addMessageTemplate_msg2 = (string)$msgstr->addMessageTemplate_msg2;

	if(!txvalidator($template_name,TX_STRING,"SPACE")){
		$data['flag'] = 0;
		$data['status'] = (string) $x->invalid_template_name;
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!validateSize($x->new_tpl_name_text,$template_name,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}

	$template_id = getSequenceID($dbconn,'message_template_template_id_seq');
	$sqlcmd = "insert into message_template (template_id, access_type, template_text, department, user_id, created_by, template_name) 
				values ('" .dbSafe($template_id). "','1','".dbSafe(trim($text))."','".dbSafe($department)."','".dbSafe($user_id)."','".dbSafe($userid)."', '".dbSafe($template_name)."')";
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if(empty($row))
	{
		$data['flag'] = 2;
		$data['status'] = $addMessageTemplate_msg2; //unsuccessful
	}else{
		$data['flag'] = 1;
	}

	$dataEncoded = json_encode($data);
	error_log("dataEncoded: " . $dataEncoded);
	
	echo $dataEncoded;
}

function addGlobalTemplate_UPLOAD($userid, $user_id, $text, $department, $template_name)
{
	global $dbconn, $msgstr, $x;
	$data = array();
	$addMessageTemplate_msg1 = (string)$msgstr->addMessageTemplate_msg1;
	$addMessageTemplate_msg2 = (string)$msgstr->addMessageTemplate_msg2;

	if(!txvalidator($template_name,TX_STRING,"SPACE")){
		$data['flag'] = 0;
		$data['status'] = (string) $x->invalid_template_name;
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!validateSize($x->new_tpl_name_text,$template_name,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}

	$template_id = getSequenceID($dbconn,'message_template_template_id_seq');
	$sqlcmd = "insert into message_template (template_id, access_type, template_text, department, user_id, created_by, template_name) 
				values ('" .dbSafe($template_id). "','1','".dbSafe(trim($text))."','".dbSafe($department)."','".dbSafe($user_id)."','".dbSafe($userid)."', '".dbSafe($template_name)."')";
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if(empty($row))
	{
		$data['flag'] = 2;
		$data['status'] = $addMessageTemplate_msg2; //unsuccessful
	}else{
		$data['flag'] = 1;
	}

	$dataEncoded = json_encode($data);
	error_log("dataEncoded2: " . $dataEncoded);
	return $dataEncoded;
	
	// echo $dataEncoded;
}

function editGlobalTemplate($id)
{
	global $dbconn, $msgstr, $xml_common;

	$editMessageTemplate_msg1 = (string)$msgstr->editMessageTemplate_msg1;
	$editMessageTemplate_msg2 = (string)$msgstr->editMessageTemplate_msg2;
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "select template_name, template_text from message_template where template_id='".dbSafe($id)."' and access_type='1'";
	$row = getSQLresult($dbconn, $sqlcmd);
	
	if(is_string($row))
	{
		error_log($db_err. " (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		echo $db_err;
	}
	else
	{
		if(empty($row))
		{
			echo $editMessageTemplate_msg1. " '" .dbSafe($id). "' ".$editMessageTemplate_msg2;
			error_log($editMessageTemplate_msg1. " '" .htmlspecialchars($id). "' ".$editMessageTemplate_msg2);
		}
		else
		{
			if(mb_detect_encoding($row[0]['template_text'], 'ASCII', true)){
				$charset_html = "<option value=\"text\" selected>$xml_common->ascii</option>".
								"<option value=\"utf8\">$xml_common->utf8</option>";
				$count_chars2 = mb_strlen($row[0]['template_text']);								
			}else{
				$charset_html = "<option value=\"text\">$xml_common->ascii</option>".
								"<option value=\"utf8\" selected>$xml_common->utf8</option>";								
				$count_chars2 = mb_strlen($row[0]['template_text'], "UTF-8");
			}			
			$sms_num = getSMSNeeded($row[0]['template_text']);

			$result_array = array();
			$result_array['text'] = $row[0]['template_text'];
			$result_array['template_name'] = $row[0]['template_name'];
			$result_array['charset'] = $charset_html;
			$result_array['count_chars2'] = $count_chars2;
			$result_array['sms_num'] = $sms_num." / 10";
			echo json_encode($result_array);
		}
	}
}

function saveGlobalTemplate($userid, $id, $text, $department, $template_name)
{
	global $dbconn, $msgstr, $x;
	$data = array();

	$saveMessageTemplate_msg1 = (string)$msgstr->saveMessageTemplate_msg1;
	$saveMessageTemplate_msg2 = (string)$msgstr->saveMessageTemplate_msg2;

	if(!txvalidator($template_name,TX_STRING,"SPACE")){
		$data['flag'] = 0;
		$data['status'] = (string) $x->invalid_template_name;
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!validateSize($x->new_tpl_name_text,$template_name,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}

	$sqlcmd = "update message_template set template_name = '".dbSafe($template_name)."', template_text='".dbSafe(trim($text))."', 
				modified_by='".dbSafe($userid)."',modified_dtm='now()' 
				where template_id='".dbSafe($id)."' and access_type='1'";
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if(empty($row))
	{
		$data['flag'] = 2;
		$data['status'] = $saveMessageTemplate_msg2; //unsuccessful
	}else{
		$data['flag'] = 1;
	}	
	echo json_encode($data);
}

function deleteGlobalTemplate($userid, $id)
{
	global $dbconn;

	$sqlcmd = "delete from message_template where template_id='".dbSafe($id)."' and access_type='1'";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if (!empty($res)) { 
		error_log("deleteGlobalTemplate: Database Error: ".$res);
		echo "Database Error";
	}
}

function emptyGlobalTemplate($userid)
{
	global $dbconn;

	if(isUserAdmin($userid))
	{
		$sqlcmd = "delete from message_template where access_type='1'";
	}
	else
	{
		$department = getUserDepartment($userid);
		$sqlcmd = "delete from message_template where department='".dbSafe($department)."' and access_type='1'";
	}
	$es = doSQLcmd($dbconn, $sqlcmd);
	
	if (!empty($res)) { 
		error_log("emptyGlobalTemplate: Database Error: ".$res);
		echo "Database Error";
	}
}

function insertTemplate($userid, $uploadfile, $department, $access_type, $file, $max_sms)
{
	global $dbconn, $x;
	$i = 0;
	$data = array();
	//File controls added by Zin @ 11-Aug-2021
	if(!$uploadfile){
		return 0;
	}

	if($uploadfile ){
		$file_type = "csv";		
		$chk_status = check_upload_file( $file, $file_type );
	}

	if($chk_status['status'] == "1"){
		$file = fopen($uploadfile, 'r');
		fseek($file, 0);
		
		while(!feof($file))
		{
			$i = $i +1;
			$curr_arr = fgetcsv($file, 1024);

			// test assmi
			if (count($curr_arr) == 1 && $curr_arr[0] === null){
				error_log("XXXX curr arr is empty. Continue to next record");
				continue;
			}
			// test assmi
			
			$template_name = $curr_arr[0];
			$template = $curr_arr[1];
			$template = preg_replace("/^[\n\r\s\t]+/", "", $template);
			$template = preg_replace("/[\n\r\s\t]+$/", "", $template);
			$lower_template = strtolower($template);

			$err_tmpl_name = 0;
			$err_tmpl = 0;	
			$tmpl_existed = 0;
			$valid_count = 0;		
			$max_char_st = checkSMSMaxChar($template,$max_sms);
			if( !txvalidator($template_name,TX_STRING,"SPACE") || 
			!validateSize($x->new_tpl_name_text,$template_name,"NAME")){					
				$data['flag'] = 0;
				$data['status'] = (string)$x->invalid_template_name;				
				continue;
			}else if($max_char_st['st']){				
				$data['flag'] = 0;
				$data['status'] = (string)$x->alert_9." ".$max_char_st['max']." ".$x->alert_10;				
				continue;
			}
		
			//checking for existing template
			$chksql = "select template_id from message_template where lower(template_text)='".dbSafe($lower_template)."' 
						and created_by='".dbSafe($userid)."' and access_type='".dbSafe($access_type)."'";
			$chk = getSQLresult($dbconn, $chksql);
			
			if( $chk ){
				//echo "AA";
				//die;				
				$data['flag'] = 0;
				$data['status'] = (string)$x->alert_11;
				continue;
			}else{
				//echo "BB";
				//die;
				
			}
			
			/*
			if(!is_string($chk))
			{
				if(!empty($chk) || $i==1)
				{
					continue;
				}
			}
			*/
			
			$template_id = getSequence($dbconn,'template_list_template_id_seq');
			
			if( $template != "" && $template_name != "" ){
				
				$sqlcmd = "insert into template_list (template_id, template_text, department, userid, access_type, template_name ) values ('".dbSafe($template_id)."','".dbSafe($template)."','".dbSafe($department)."','".dbSafe($userid)."','".dbSafe($access_type). "', '".dbSafe($template_name)."')";
				$row = doSQLcmd($dbconn, $sqlcmd);
				$valid_count++;
			}
			
		}
		if(feof($file)) {
			if($valid_count > 0){
				$data['flag'] = 1;
			}
		}else {
			$data['flag'] = 0;
			$data['status'] = (string)$x->alert_5;
		}
	}else {
		$data['flag'] = 0;		
		$data['status'] = (string)$chk_status['message'];
	}
	echo json_encode($data);
}

function listTemplate($userid, $department, $access_type)
{
	global $dbconn;

	$sqlcmd = "select template_name, template_text from template_list where userid='".dbSafe($userid)."' and access_type='".dbSafe($access_type)."' order by template_text";
	$result = pg_query($dbconn, $sqlcmd);
	
	if(!$result)
	{
		echo "Database Error";
		error_log("listTemplate: Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
	}
	else
	{
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			array_push($result_array,Array(
				htmlspecialchars($row['template_name'],ENT_QUOTES),
				htmlspecialchars($row['template_text'],ENT_QUOTES)
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function addTemplate($userid, $department, $id_of_user, $access_type)
{
	global $dbconn, $msgstr;
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "select template_id, template_text, access_type, template_name from template_list 
				where userid='".dbSafe($userid)."' and access_type='".dbSafe($access_type)."'";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row))
	{
		echo "Database Error";
		error_log("addTemplate: Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
	} 
	else
	{
		for($a=0; $a<count($row); $a++)
		{
			$template_text = $row[$a]['template_text'];
			$access_type = $row[$a]['access_type'];
			$id = $row[$a]['template_id'];
			$template_name = $row[$a]['template_name'];
			
			if($access_type == '0'){
				$dataFlag = addMessageTemplate_UPLOAD($userid, $id_of_user, $template_text, $department, $template_name );
			}else{
				// $dataFlag = addGlobalTemplate($userid, $id_of_user, $template_text, $department, $template_name);
				$dataFlag = addGlobalTemplate_UPLOAD($userid, $id_of_user, $template_text, $department, $template_name);
			}
		
			$deletesql = "delete from template_list where template_id='".dbSafe($id)."'";
			$delete = doSQLcmd($dbconn, $deletesql);
		}

		echo $dataFlag;
	}
}

function deleteTemplate($userid, $access_type)
{
	global $dbconn;

	$sqlcmd = "delete from template_list where userid='".dbSafe($userid)."' and access_type='".dbSafe($access_type)."'";
	$res = doSQLcmd($dbconn, $sqlcmd);
	
	if (!empty($res)) { 
		error_log("deleteTemplate: Database Error: ".$res);
		echo "Database Error";
	}
}

function listMIMTemplate($userid)
{
	global $dbconn;

	if(isUserAdmin($userid))
	{
		if( strtolower( $userid ) == "momadmin" ){
			$sqlcmd = "select template_name,mim_tpl_id,template_id, template_text, department_list.department as department, department_id from message_template left outer join department_list on (message_template.department = department_list.department_id) where message_template.access_type='3' and message_template.user_id is not null and message_template.created_by != 'useradmin' order by message_template.template_text";
		}else{
			$sqlcmd = "select template_name,mim_tpl_id,template_id, template_text, department_list.department as department, department_id from message_template left outer join department_list on (message_template.department = department_list.department_id) where message_template.access_type='3' and message_template.user_id is not null order by message_template.template_text";
		}
		
	}
	else
	{
		$department = getUserDepartment($userid);
		$sqlcmd = "select template_name,mim_tpl_id, template_id, template_text, department_list.department as department, department_id from message_template left outer join department_list on (message_template.department = department_list.department_id) 
					where message_template.access_type='3'  and message_template.user_id in ( select id from user_list where userid = '".$_SESSION["userid"]."') order by message_template.template_text";
	}
	
	$result = pg_query($dbconn, $sqlcmd);
	
	if(!$result) {
		echo $db_err;
		error_log("listMIMTemplate: ".$db_err." (" .$sqlcmd. ") -- " .pg_last_error($dbconn));
	} else {
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			array_push($result_array,Array(
				$row['template_name'],
				'<a href="#myCreate" data-bs-toggle="modal" data-id="'.$row['template_id'].'">'.htmlspecialchars($row['template_text'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>',
				$row['mim_tpl_id'],
				// assmi
				// '<input type="checkbox" name="no" value="'.$row['template_id'].'">'
				'<input type="checkbox" class="user_checkbox" name="no" value="'.$row['template_id'].'">'
				// assmi
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function listGlobalMIMTemplate($userid, $id_of_user )
{
	global $dbconn;
	$UserType = getUserType( $userid );
	$department = getUserDepartment($userid);
	
	if(isUserAdmin($userid))
	{
		$sqlcmd = "select template_name, message_template.created_by,message_template.mim_tpl_id,message_template.template_id, message_template.template_text, department_list.department as department, department_id from message_template left outer join department_list on (message_template.department = department_list.department_id) 
					where access_type='4' order by template_text";
	}
	else
	{		
		$sqlcmd = "select template_name, message_template.created_by,message_template.mim_tpl_id, message_template.template_id, message_template.template_text, department_list.department as department, department_id from message_template left outer join department_list on (message_template.department = department_list.department_id) 
					where message_template.department='".dbSafe($department)."' and access_type='4' order by template_text";
	}

	$result = pg_query($dbconn, $sqlcmd);
	
	if(!$result) {
		echo $db_err;
		error_log("listGlobalMIMTemplate: ".$db_err." (" .$sqlcmd. ") -- " .pg_last_error($dbconn));
	} else {
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			
			$department_is = getDepartmentName( $row['department_id'] );
			
			if( $row['created_by'] == $userid ){
				$edit = '<a href="#myCreate" data-bs-toggle="modal" data-id="'.$row['template_id'].'">'.htmlspecialchars($row['template_text'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>';
				$delete = '<input type="checkbox" name="no" value="'.$row['template_id'].'">';
			}else{
				
				if(isUserAdmin($userid)){
					$edit = '<a href="#myCreate" data-bs-toggle="modal" data-id="'.$row['template_id'].'">'.htmlspecialchars($row['template_text'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>';
					$delete = '<input type="checkbox" name="no" value="'.$row['template_id'].'">';
				}elseif( strtolower($UserType) == "bu" ){
					$edit = '<a href="#myCreate" data-bs-toggle="modal" data-id="'.$row['template_id'].'">'.htmlspecialchars($row['template_text'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>';
					$delete = '<input type="checkbox" name="no" value="'.$row['template_id'].'">';
				}else{
					$edit = htmlspecialchars($row['template_text']);
					$delete = '';
				}
		
			}
			
			array_push($result_array,Array(
				$row['template_name'],
				$edit,
				//'<a href="#myCreate" data-toggle="modal" data-id="'.$row['template_id'].'">'.htmlspecialchars($row['template_text']).' <i class="fa fa-pencil-square-o fa-fw"></i></a>',
				$row['mim_tpl_id'],
				$department_is,
				$delete,
				//'<input type="checkbox" name="no" value="'.$row['template_id'].'">'
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function addMIMTemplate($userid, $user_id, $text, $department, $mim_tpl_id, $template_name )
{
	global $dbconn, $msgstr, $mim;

	$addMessageTemplate_msg1 = (string)$msgstr->addMessageTemplate_msg1;
	$addMessageTemplate_msg2 = (string)$msgstr->addMessageTemplate_msg2;

	if(!txvalidator($template_name,TX_STRING,"SPACE")){
		$data['flag'] = 0;
		$data['status'] = (string) $mim->invalid_template_name;
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!validateSize($mim->new_tpl_name_text,$template_name,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!txvalidator($mim_tpl_id,TX_STRING,"-_")){
		$data['flag'] = 0;
		$data['status'] = (string) $mim->invalid_mim_tpl_id;
		$data['field'] = "mim_tpl_id";
		echo json_encode($data);
		die;
	}else if(!validateSize($mim->mim_tpl_id,$mim_tpl_id,"DESC")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "mim_tpl_id";
		echo json_encode($data);
		die;
	}

	$template_id = getSequenceID($dbconn,'message_template_template_id_seq');
	$sqlcmd = "insert into message_template (template_id, access_type, template_text, user_id, created_by, mim_tpl_id, template_name) 
				values ('" .dbSafe($template_id). "','3','".dbSafe(trim($text))."', '".dbSafe($user_id)."','".dbSafe($userid)."', '".dbSafe(strtolower($mim_tpl_id))."', '".dbSafe($template_name)."')";
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if(empty($row))
	{
		$data['flag'] = 2;
		$data['status'] = $addMessageTemplate_msg2; //unsuccessful
	}else{
		$data['flag'] = 1;
	}
	
	insertAuditTrail( "Add new MIM Template" );
	echo json_encode($data);
}

function addGlobalMIMTemplate($userid, $user_id, $text, $department, $mim_tpl_id, $template_name)
{
	global $dbconn, $msgstr, $mim;
	$data = array();

	$addMessageTemplate_msg1 = (string)$msgstr->addMessageTemplate_msg1;
	$addMessageTemplate_msg2 = (string)$msgstr->addMessageTemplate_msg2;

	if(!txvalidator($template_name,TX_STRING,"SPACE")){
		$data['flag'] = 0;
		$data['status'] = (string) $mim->invalid_template_name;
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!validateSize($mim->new_tpl_name_text,$template_name,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!txvalidator($mim_tpl_id,TX_STRING,"-_")){
		$data['flag'] = 0;
		$data['status'] = (string) $mim->invalid_mim_tpl_id;
		$data['field'] = "mim_tpl_id";
		echo json_encode($data);
		die;
	}else if(!validateSize($mim->mim_tpl_id,$mim_tpl_id,"DESC")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "mim_tpl_id";
		echo json_encode($data);
		die;
	}

	$template_id = getSequenceID($dbconn,'message_template_template_id_seq');
	$sqlcmd = "insert into message_template (template_id, access_type, template_text, department, created_by, mim_tpl_id, template_name) 
				values ('" .dbSafe($template_id). "','4','".dbSafe(trim($text))."','".dbSafe($department)."','".dbSafe($userid)."', '".dbSafe(strtolower($mim_tpl_id))."', '".dbSafe($template_name)."')";
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if(empty($row))
	{
		$data['flag'] = 0;
		$data['status'] = $addMessageTemplate_msg2; //unsuccessful
	}else{
		$data['flag'] = 1;
	}
	
	insertAuditTrail( "Add new Global MIM Template" );
	echo json_encode($data);
}

function editMIMTemplate($id)
{
	global $dbconn, $msgstr;

	$editMessageTemplate_msg1 = (string)$msgstr->editMessageTemplate_msg1;
	$editMessageTemplate_msg2 = (string)$msgstr->editMessageTemplate_msg2;
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "select template_name, template_text, mim_tpl_id from message_template where template_id='".dbSafe($id)."' and access_type='3'";
	$row = getSQLresult($dbconn, $sqlcmd);
	
	if(is_string($row))
	{
		error_log("editMIMTemplate: ". $db_err. " (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
	}
	else
	{
		if(empty($row))
		{
			echo $editMessageTemplate_msg1. " '" .htmlspecialchars($id). "' ".$editMessageTemplate_msg2;
			error_log("editMIMTemplate: ".$editMessageTemplate_msg1. " '" .dbSafe($id). "' ".$editMessageTemplate_msg2);
		}
		else
		{
			if(mb_detect_encoding($row[0]['template_text'], 'ASCII', true)){
				$count_chars2 = mb_strlen($row[0]['template_text']);
			}else{
				$count_chars2 = mb_strlen($row[0]['template_text'], "UTF-8");
			}
			$result_array = array();
			$result_array['text'] = $row[0]['template_text'];
			$result_array['mim_tpl_id'] = $row[0]['mim_tpl_id'];
			$result_array['template_name'] = $row[0]['template_name'];
			$result_array['count_chars2'] = $count_chars2;
			echo json_encode($result_array);
		}
	}

}

function editGlobalMIMTemplate($id)
{
	global $dbconn, $msgstr;

	$editMessageTemplate_msg1 = (string)$msgstr->editMessageTemplate_msg1;
	$editMessageTemplate_msg2 = (string)$msgstr->editMessageTemplate_msg2;
	$db_err = (string)$msgstr->db_err;
	

	$sqlcmd = "select template_name, template_text, mim_tpl_id from message_template where template_id='".dbSafe($id)."' and access_type='4'";
	$row = getSQLresult($dbconn, $sqlcmd);
	
	if(is_string($row))
	{
		error_log("editGlobalMIMTemplate: ".$db_err. " (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
	}
	else
	{
		if(empty($row))
		{
			echo $editMessageTemplate_msg1. " '" .htmlspecialchars($id). "' ".$editMessageTemplate_msg2;
			error_log("editGlobalMIMTemplate: ".$editMessageTemplate_msg1. " '" .dbSafe($id). "' ".$editMessageTemplate_msg2);
		}
		else
		{
			if(mb_detect_encoding($row[0]['template_text'], 'ASCII', true)){
				$count_chars2 = mb_strlen($row[0]['template_text']);
			}else{
				$count_chars2 = mb_strlen($row[0]['template_text'], "UTF-8");
			}
			$result_array = array();
			$result_array['text'] = $row[0]['template_text'];
			$result_array['mim_tpl_id'] = $row[0]['mim_tpl_id'];
			$result_array['template_name'] = $row[0]['template_name'];
			$result_array['count_chars2'] = $count_chars2;
			echo json_encode($result_array);
		}
	}

}

function saveMIMTemplate($userid, $id, $text, $department, $mim_tpl_id, $template_name )
{
	global $dbconn, $msgstr, $mim;

	$saveMessageTemplate_msg1 = (string)$msgstr->saveMessageTemplate_msg1;
	$saveMessageTemplate_msg2 = (string)$msgstr->saveMessageTemplate_msg2;

	if(!txvalidator($template_name,TX_STRING,"SPACE")){
		$data['flag'] = 0;
		$data['status'] = (string) $mim->invalid_template_name;
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!validateSize($mim->new_tpl_name_text,$template_name,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!txvalidator($mim_tpl_id,TX_STRING,"-_")){
		$data['flag'] = 0;
		$data['status'] = (string) $mim->invalid_mim_tpl_id;
		$data['field'] = "mim_tpl_id";
		echo json_encode($data);
		die;
	}else if(!validateSize($mim->mim_tpl_id,$mim_tpl_id,"DESC")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "mim_tpl_id";
		echo json_encode($data);
		die;
	}

	$sqlcmd = "update message_template set template_name = '".dbSafe($template_name)."', template_text='".dbSafe(trim($text))."', 
				modified_by='".dbSafe($userid)."',modified_dtm='now()', mim_tpl_id = '".dbSafe(strtolower($mim_tpl_id))."' 
				where template_id='".dbSafe($id)."' and access_type='3'";
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if(empty($row))
	{
		$data['flag'] = 2;
		$data['status'] = $saveMessageTemplate_msg2; //unsuccessful
	}else{
		$data['flag'] = 1;
	}
	
	insertAuditTrail( "Edit MIM Template" );	
	echo json_encode($data);
}

function saveGlobalMIMTemplate($userid, $id, $text, $department, $mim_tpl_id, $template_name )
{
	global $dbconn, $msgstr, $mim;
	$data = array();
	
	$saveMessageTemplate_msg1 = (string)$msgstr->saveMessageTemplate_msg1;
	$saveMessageTemplate_msg2 = (string)$msgstr->saveMessageTemplate_msg2;

	if(!txvalidator($template_name,TX_STRING,"SPACE")){
		$data['flag'] = 0;
		$data['status'] = (string) $mim->invalid_template_name;
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!validateSize($mim->new_tpl_name_text,$template_name,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "template_name";
		echo json_encode($data);
		die;
	}else if(!txvalidator($mim_tpl_id,TX_STRING,"-_")){
		$data['flag'] = 0;
		$data['status'] = (string) $mim->invalid_mim_tpl_id;
		$data['field'] = "mim_tpl_id";
		echo json_encode($data);
		die;
	}else if(!validateSize($mim->mim_tpl_id,$mim_tpl_id,"DESC")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "mim_tpl_id";
		echo json_encode($data);
		die;
	}

	$sqlcmd = "update message_template set template_name = '".dbSafe($template_name)."', template_text='".dbSafe(trim($text))."', 
				modified_by='".dbSafe($userid)."',modified_dtm='now()', mim_tpl_id = '".dbSafe(strtolower($mim_tpl_id))."' 
				where template_id='".dbSafe($id)."' and access_type='4'";
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if(empty($row))
	{
		$data['flag'] = 2;
		$data['status'] = $saveMessageTemplate_msg2; //unsuccessful
	}else{
		$data['flag'] = 1;
	}
	
	insertAuditTrail( "Edit Global MIM Template" );	
	echo json_encode($data);
}

function deleteMIMTemplate($userid, $id)
{
	global $dbconn;

	$sqlcmd = "delete from message_template where template_id='".dbSafe($id)."' and access_type='3'";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if (!empty($res)) { 
		echo "Database Error: ".$res;
	}
	
	insertAuditTrail( "Delete MIM Template" );
}

function deleteGlobalMIMTemplate($userid, $id)
{
	global $dbconn;

	$sqlcmd = "delete from message_template where template_id='".dbSafe($id)."' and access_type='4'";
	$res = doSQLcmd($dbconn, $sqlcmd);

	if (!empty($res)) { 
		error_log("deleteGlobalMIMTemplate: Database Error: ".$res);
		echo "Database Error";
	}
	
	insertAuditTrail( "Delete MIM Template" );
}

function emptyMIMTemplate($userid)
{
	global $dbconn;

	if(isUserAdmin($userid))
	{
		$sqlcmd = "delete from message_template where access_type='3'";
	}
	else
	{
		$department = getUserDepartment($userid);
		$sqlcmd = "delete from message_template where department='".dbSafe($department)."' and access_type='3'";
	}
	$es = doSQLcmd($dbconn, $sqlcmd);
	
	if (!empty($res)) { 
		error_log("emptyMIMTemplate: Database Error: ".$res);
		echo "Database Error";
	}
	
	insertAuditTrail( "Empty MIM Template" );
}

function getSMSNeeded($sms_text){
	// Default charset is ASCII	
	$character_set = 0;
	if(mb_detect_encoding($sms_text, 'ASCII', true)){
				$character_set = 1;
	}			
	if ($character_set == 1)
	{
		//ASCII		
		$total_length = 670;
		$max_length = 153;
		if ( strlen($sms_text) > 160 )
		{
			$max_length = 153;
		} 
		else 
		{
			$max_length = 160;
		}
		
		//total_length = max_length * 4;		
		$number_of_sms_needed = ceil(strlen($sms_text) / $max_length);
	} 
	else 
	{
		// UTF-8		
		$total_length = 670;		
		$max_length = 70;
		if ( strlen($sms_text)  > 70 )
		{
			$max_length = 67;
		} 
		else 
		{
			$max_length = 70;
		}				
		$number_of_sms_needed = ceil(mb_strlen($sms_text, "UTF-8") / $max_length);
	}	
	error_log("NoOfSMS: $number_of_sms_needed");
	return $number_of_sms_needed;
}

function checkSMSMaxChar($sms_text,$max_sms){
	$character_set = 0;
	$isMaxChar = 0;
	$result = array();
	if(mb_detect_encoding($sms_text, 'ASCII', true)){
		$character_set = 1;
	}
	if ($character_set == 1){
		$max_length = 153;
		$max_char_allow = $max_length * $max_sms;
		$msg_length = strlen($sms_text);		
	}else{
		$max_length = 67;
		$max_char_allow = $max_length * $max_sms;
		$msg_length = mb_strlen($sms_text, "UTF-8");		
	}
	if( $msg_length > $max_char_allow){
		$isMaxChar = 1;
	}
	$result['st'] = $isMaxChar;
	$result['max'] = $max_char_allow;
	return $result;
}
?>
