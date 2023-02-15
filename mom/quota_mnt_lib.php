<?php
require "lib/commonFunc.php";

$id = filter_input(INPUT_POST,'id');
$mode = filter_input(INPUT_POST,'mode');
$userid = filter_input(INPUT_POST,'userid');
$left  = filter_input(INPUT_POST,'quota_left');
$limit = filter_input(INPUT_POST,'topup_value');
$frequency = filter_input(INPUT_POST,'frequency');
$alert_type = filter_input(INPUT_POST,'alert_type');
$alert_email = filter_input(INPUT_POST,'alert_email');
$alert_credit = filter_input(INPUT_POST,'alert_credit');
$unlimited = filter_input(INPUT_POST,'enable_unlimited');
$msgstr = GetLanguage("quota_mnt",$lang);

switch ($mode) {
    case "view":
        listQuota();
        break;
	case "create":
        addQuotaProfile($userid,$frequency,$limit,$left,$unlimited);
        break;
	case "retrieve":
        getQuotaDetails($id);
        break;
	case "update":
        updateQuotaProfile($id,$frequency,$limit,$left,$unlimited);
        break;
	case "delete":
        deleteQuota($id);
        break;
	case "getAlert":
        getAlertConfig();
        break;
	case "updateAlert":
        updateAlertConfig($alert_type,$alert_email,$alert_credit);
        break;
	case "listUser":
        listUser();
        break;
	case "global":
        updateGlobal(filter_input(INPUT_POST,'option'), 
					filter_input(INPUT_POST,'value'));
        break;
    default:
        die("Invalid Command");
}

function listQuota()
{
	global $dbconn;
	
	$sqlWhere = "";
	$getUserType = getUserType();
	if( $getUserType == "admin" ){
		$sqlWhere = "";
	}elseif( $getUserType == "bu" ){
		$sqlWhere = " and userid in ( select userid from user_list where department = '".dbSafe( $_SESSION['department'] )."' ) and userid != '".$_SESSION['userid']."'";
	}
	
	//only show own department user for a BU and show all for admin
	$sqlcmd = "select idx,userid,topup_frequency,quota_limit,quota_left,
				to_char(next_topup_dtm, 'DD/MM/YYYY') as next_topup_dtm,
				to_char(last_topup_dtm, 'DD/MM/YYYY') as last_topup_dtm,
				to_char(updated_dtm, 'DD/MM/YYYY HH24:MI:SS') as updated_dtm,
				updated_by,unlimited_quota from quota_mnt where 1=1 $sqlWhere order by userid asc";
	$result = pg_query($dbconn,$sqlcmd);

	if(!$result){
		echo "Database Error";
		error_log("listQuota: ".$sqlcmd.' -- '.pg_last_error($dbconn));
	} else {
		$arr_res = Array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			
			list ($quota_left, $topup_frequency, $quota_limit) = getFreqDesc($row['quota_left'],$row['topup_frequency'],$row['quota_limit'],$row['unlimited_quota']);
			
			$Dept_QuotaLeft = getUserDeptQuota( $row['userid'] );
			$Dept = getUserDepartment2( $row['userid'] );
			
			array_push($arr_res,Array(
				'<a href="#myQuota" data-bs-toggle="modal" data-id="'.$row['idx'].'">'.htmlspecialchars($row['userid']).' <i class="fa fa-pencil-square-o fa-fw"></i></a>',
				$Dept_QuotaLeft . " (" . $Dept . ")",
				trim($quota_left),
				trim($topup_frequency),
				trim($quota_limit),
				$row['last_topup_dtm'],
				$row['next_topup_dtm'],
				$row['updated_dtm'],
				$row['updated_by'],
				'<input type="checkbox" class="user_checkbox" name="no" value="'.$row['idx'].'">'
			));
		}
		echo json_encode(Array("data"=>$arr_res));
	}
}

function addQuotaProfile($target_userid,$topup_frequency,
						$quota_limit,$quota_left,$unlimited_quota)
{
	global $dbconn, $msgstr;
	$data = array();
	$addQuotaProfile_msg1 = (string)$msgstr->addQuotaProfile_msg1;
	$addQuotaProfile_msg2 = (string)$msgstr->addQuotaProfile_msg2;

	if(!txvalidator($quota_limit,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$msgstr->invalid_topup_value;
		$data['field'] = "topup_value";	
		echo json_encode($data);	
		die;
	}else if(!txvalidator($quota_left,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$msgstr->invalid_quota_left;
		$data['field'] = "quota_left";		
		echo json_encode($data);
		die;
	}
	
	$Dept_QuotaLeft = getUserDeptQuota( $target_userid );
	$Dept = getUserDepartment2( $target_userid );
	$Dept_ID = getUserDepartment( $target_userid );
	
	if( $quota_left > $Dept_QuotaLeft ){
		$data['flag'] = 0;
		$data['status'] = $addQuotaProfile_msg2." - ".$target_userid; //insufficient department quota
		$data['field'] = "quota_left";		
		error_log($addQuotaProfile_msg2." - ".$target_userid);
	}
	else if( $quota_limit > $Dept_QuotaLeft ){
		$data['flag'] = 0;
		$data['status'] = $addQuotaProfile_msg2." - ".$target_userid; //insufficient department quota
		$data['field'] = "topup_value";		
		error_log($addQuotaProfile_msg2." - ".$target_userid);
	}
	else{
		
		if(checkQuotaExist($target_userid)){
			$data['flag'] = 0;
			$data['status'] = $addQuotaProfile_msg1." - ".$target_userid; //Quota Profile Exists.
			$data['field'] = "quota_left";			
			error_log("addQuotaProfile: ".$addQuotaProfile_msg1." - ".$target_userid);
		} else {
			
			if(empty($topup_frequency)) { $topup_frequency = 3; }
			if(empty($quota_limit)) { $quota_limit = 0; }
			if(empty($quota_left)) { $quota_left = 0; }
			if(empty($unlimited_quota)) { $unlimited_quota = 0; }
			
			$data = array(
				'idx' => getSequenceID($dbconn,'quota_mnt_idx_seq'),
				'userid' => $target_userid,
				'topup_frequency' => $topup_frequency,
				'quota_limit' => $quota_limit,
				'quota_left' => $quota_left,
				'updated_dtm' => 'now()',
				'updated_by' => $_SESSION['userid'],
				'unlimited_quota' => $unlimited_quota
			);
			
			if($topup_frequency == 1){
				$last_topup_dtm = "now()";
				$next_topup_dtm = "now() + '1 week'";
			}else if($topup_frequency == 2){
				$last_topup_dtm = "now()";
				$next_topup_dtm = "now() + '1 month'";
			} else {
				$last_topup_dtm = "null";
				$next_topup_dtm = "null";
			}
			
			$columns = implode(",",array_keys($data));
			$values = implode("','",array_map('pg_escape_string',array_values($data)));
				
			$cmd = "INSERT INTO quota_mnt ($columns,last_topup_dtm,next_topup_dtm) 
					VALUES ('$values',$last_topup_dtm,$next_topup_dtm)";
			$res = doSQLcmd($dbconn,$cmd);
			
			if (!$res) { 
				$data['flag'] = 2;
				$data['status'] = "Database Error";
				error_log("addQuotaProfile: ".$cmd.' -- '.pg_last_error($dbconn));
			}else{				
				//deduct department quota
				$cmd2 = "update department_list set quota_left = quota_left - $quota_left where department_id = '".dbSafe($Dept_ID)."'";
				$res2 = doSQLcmd($dbconn,$cmd2);
				$data['flag'] = 1;
			}
			
		}
	}
	
	insertAuditTrail("Add Quota Profile");
	echo json_encode($data);
}

function updateQuotaProfile($idx,$topup_frequency,$quota_limit,$quota_left,$unlimited_quota)
{
	global $dbconn, $msgstr;
	$data = array();
	
	$addQuotaProfile_msg2 = (string)$msgstr->addQuotaProfile_msg2;
	$target_userid = getQuotaMngtUserID($idx);
	$Dept_QuotaLeft = getUserDeptQuota( $target_userid );
	
	if(($topup_frequency == 1 || $topup_frequency == 2 ) && !txvalidator($quota_limit,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$msgstr->invalid_topup_value;
		$data['field'] = "topup_value";	
		echo json_encode($data);	
		die;
	}else if(!$unlimited_quota && !txvalidator($quota_left,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$msgstr->invalid_quota_left;
		$data['field'] = "quota_left";		
		echo json_encode($data);
		die;
	}
	
	if( $quota_left > $Dept_QuotaLeft ){
		$data['flag'] = 0;
		$data['status'] = $addQuotaProfile_msg2." - ".htmlspecialchars($target_userid,ENT_QUOTES); //insufficient department quota
		$data['field'] = "quota_left";
		error_log($addQuotaProfile_msg2." - ".$target_userid);
	}
	else if( $quota_limit > $Dept_QuotaLeft ){
		$data['flag'] = 0;
		$data['status'] = $addQuotaProfile_msg2." - ".htmlspecialchars($target_userid,ENT_QUOTES); //insufficient department quota
		$data['field'] = "topup_value";
		error_log($addQuotaProfile_msg2." - ".$target_userid);
	}
	else{
		
		if(empty($topup_frequency)) { $topup_frequency = 3; }
		if(empty($quota_limit)) { $quota_limit = 0; }
		if(empty($quota_left)) { $quota_left = 0; }
		if(empty($unlimited_quota)) { $unlimited_quota = 0; }
		
		$data = array(
			'topup_frequency' => $topup_frequency,
			'quota_limit' => $quota_limit,
			'quota_left' => $quota_left,
			'updated_dtm' => 'now()',
			'updated_by' => $_SESSION['userid'],
			'unlimited_quota' => $unlimited_quota
		);
		
		if($topup_frequency == 1){
			$last_topup_dtm = "now()";
			$next_topup_dtm = "now() + '1 week'";
		}else if($topup_frequency == 2){
			$last_topup_dtm = "now()";
			$next_topup_dtm = "now() + '1 month'";
		} else {
			$last_topup_dtm = "null";
			$next_topup_dtm = "null";
		}
		
		$update_data = '';
		foreach($data as $key => $value) {
			
			if( $key == "quota_left" ){
				$update_data .= $key."= $key + '".pg_escape_string($value)."',";
			}else{
				$update_data .= $key."='".pg_escape_string($value)."',";
			}
			
		}
		$update_data = substr($update_data, 0, -1);
		
		$cmd = "UPDATE quota_mnt SET ".$update_data.",
				last_topup_dtm=".$last_topup_dtm.",
				next_topup_dtm=".$next_topup_dtm." WHERE idx='".$idx."'";
		$res = doSQLcmd($dbconn,$cmd);
		
		if (!$res) { 
			$data['flag'] = 2;
			$data['status'] = "Database Error";
			error_log("updateQuotaProfile: ".$cmd.' -- '.pg_last_error($dbconn));
		}else{			
			//deduct department quota
			$Dept_ID = getUserDepartment( $target_userid );
			$cmd2 = "update department_list set quota_left = quota_left - $quota_left where department_id = '".dbSafe($Dept_ID)."'";
			$res2 = doSQLcmd($dbconn,$cmd2);
			$data['flag'] = 1;		
		}
	}
	echo json_encode($data);
	insertAuditTrail("Edit Quota Profile");
}

function getQuotaMngtUserID($idx)
{
	global $dbconn;
	
	$sqlcmd = "select userid
				from quota_mnt where idx='".$idx."'";
	$result = pg_query($dbconn,$sqlcmd);
	$arr = pg_fetch_row($result);
	
	return trim($arr[0]);
	
}

function getQuotaDetails($idx)
{
	global $dbconn;
	
	$sqlcmd = "select topup_frequency,quota_limit,quota_left,unlimited_quota
				from quota_mnt where idx='".$idx."'";
	$result = pg_query($dbconn,$sqlcmd);

	if(!$result){
		error_log("getQuotaDetails: ".$sqlcmd.'--'.pg_last_error($dbconn));
		echo "Database Error";
	} else {
		$arr = pg_fetch_row($result);

		$val['frequency'] = trim($arr[0]);
		$val['limit'] = (empty($arr[1]) ? "" : trim($arr[1]));
		$val['left'] = (empty($arr[2]) ? "" : trim($arr[2]));
		$val['unlimited'] = trim($arr[3]);

		echo json_encode($val);
	}
}

function deleteQuota($idx)
{
	global $dbconn;
	
	$sqlcmd = "delete from quota_mnt where idx='".dbSafe($idx)."'";
	$result = doSQLcmd($dbconn,$sqlcmd);
	
	if( !$result ){
		echo "Database Error";
		error_log("deleteQuota: ".$sqlcmd . ' -- ' . pg_last_error($dbconn));
	}
	
	insertAuditTrail("Delete Quota Profile");
}

function listUser()
{
	global $dbconn;
	
	$UserType = getUserType();
	
	if( $UserType == "admin" ){
		$sqlcmd = "select userid from user_list order by userid asc";
	}else{
		$sqlcmd = "select userid from user_list where department = '".dbSafe($_SESSION['department'])."' order by userid asc";
	}
	
	$result = pg_query($dbconn, $sqlcmd);
	
	if(!$result) {
		error_log("listUser: Database Error (" .$sqlcmd. ") -- " .pg_last_error($dbconn));
		echo "Database Error";
	} else {
		$data = array();
		while($row = pg_fetch_assoc($result)) {
			$data[] = $row;
		}
		
		//$data = $sqlcmd;
		echo json_encode($data);
	}
}

function getAlertConfig()
{
	$config_file = "/home/msg/conf/quota_conf.xml";
	$val = array();

	$xml = simplexml_load_file($config_file);
	$val['alert_type'] = (string)$xml->alert_type;
	$val['alert_credit'] = (string)$xml->alert_credit;
	$val['alert_email'] = (string)$xml->alert_email;

	echo json_encode($val);
}

function updateAlertConfig($type,$email,$credit)
{
	global $msgstr;
	$data = array();

	$config_file = "/home/msg/conf/quota_conf.xml";

	if(!txvalidator($email,TX_EMAILADDR)){
		$data['flag'] = 0;
		$data['status'] = (string)$msgstr->invalid_alert_email;
		$data['field'] = "alert_email";
		echo json_encode($data);
		die;
	}
	else if(!txvalidator($credit,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$msgstr->invalid_alert_credit;
		$data['field'] = "alert_credit";
		echo json_encode($data);
		die;
	}

	$xml = simplexml_load_file($config_file);
	$xml->alert_type = trim($type);
	$xml->alert_email = trim($email);
	$xml->alert_credit = trim($credit);
	$xml->asXml($config_file);
	$data['flag'] = 1;
	echo json_encode($data);
}

function checkQuotaExist($userid)
{
	global $dbconn;
	
	$cmd = "select idx from quota_mnt where userid='".pg_escape_string($userid)."'";
	$res = pg_query($dbconn,$cmd);
	$rows = pg_num_rows($res);

	return $rows;
}

function getFreqDesc($quota_left, $topup_frequency, $quota_limit, $unlimited_quota)
{
	global $lang;
	$xml_common = GetLanguage("common",$lang);
	
	if($topup_frequency == 1){
		$topup_frequency = $xml_common->weekly;
	} elseif ($topup_frequency == 2){
		$topup_frequency = $xml_common->monthly;
	} else {
		$topup_frequency = $xml_common->disable;
		$quota_limit = $xml_common->disable;
		if ($unlimited_quota) {
			$quota_left = $xml_common->unlimited;
		}
	}

	return array ($quota_left, $topup_frequency, $quota_limit);
}

function updateGlobal($option, $value)
{
	global $dbconn, $msgstr;
	$data = array();

	if(!txvalidator($value,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$msgstr->invalid_value;
		$data['field'] = "value";
		echo json_encode($data);
		die;
	}
	
	if($option == 1){
		$cmd = "UPDATE quota_mnt SET topup_frequency='3',
				quota_limit='0',quota_left='0',next_topup_dtm=null,
				last_topup_dtm=null,updated_dtm=now(),
				updated_by='".$_SESSION['userid']."',unlimited_quota='1'";
	} else {
		$cmd = "UPDATE quota_mnt SET quota_left='".$value."',updated_dtm=now(),
				updated_by='".$_SESSION['userid']."',unlimited_quota='0'";
	}

	$res = doSQLcmd($dbconn,$cmd);
	if(!$res) {
		$data['flag'] = 2;
		$data['status'] = "Database Error";
		error_log("updateGlobal: Database Error (" .$cmd. ") -- " .pg_last_error($dbconn));
		echo json_encode($data);
		die;
	}
	
	insertAuditTrail("Update Global Quota Profile");
}
?>
