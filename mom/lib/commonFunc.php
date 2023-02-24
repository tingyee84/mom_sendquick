<?php
ini_set('session.save_handler','files');
require_once('class.cluster.php');
require_once('class.session.php');
require_once('db_sync.php');
require_once('db_webapp.php');
require_once('db_spool.php');
require_once('db_sq.php');
require_once('db_log.php');
require_once('txvalidator.php');
$isSecure = false;
$validateSizeMsg = "";
if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off') {
	$isSecure = true;
} else if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] !='off') {
	$isSecure = true;
} else if (isset($_SERVER['HTTP_X_SSLREQ_HEADER']) && $_SERVER['HTTP_X_SSLREQ_HEADER'] == 'Stunnel-HAProxy') {
	$isSecure = true;
}

session_set_save_handler(new EncryptedSessionHandler('Shinjitsu wa Itsumo Hitotsu'),true);
// session_set_cookie_params(10800,'/mom2/',null,$isSecure,true);
// ini_set('session.gc_maxlifetime', 10800);
session_start();

$lang = (isset($_SESSION['language'])?$_SESSION['language']:'EN');

$sftp_path = "/home/sqguest/sftp/";

if(!in_array(basename($_SERVER['PHP_SELF']),Array('index.php','login.php','forgot_password.php','error.php','process.php','sftp_action.php'))){
	if(!isset($_SESSION['userid']) || $_SESSION['userid'] == '')
	{
		die(header('Location: index.php?redirect'));
	}
}

if( basename($_SERVER['PHP_SELF']) == "index.php" && isset($_SESSION["userid"])){
	session_destroy();
}

function getWebappMode(){
	$flag = `cat /home/msg/conf/webapp_mode`;
	$flag = trim($flag);
	if ($flag == '1' || strtolower($flag) == 'y' || strtolower($flag) == 'yes'){
		return 1;
	}else{
		return 0;
	}
}

function GetLanguage($mode,$language,$path='lang_file/')
{
	global $dir;
	$x = simplexml_load_file('/home/msg/www/htdocs/mom/'.$path.$language.'.xml','SimpleXMLElement',LIBXML_COMPACT);

	if(isset($mode) && strlen(trim($mode))>0){
		if($mode == 'NA'){
			return $x;
		}else{
			return $x->$mode;
		}
	}
	return $x;
}

function getQuota()
{
	global $dbconn;

	$sql = "select quota_left, unlimited_quota from quota_mnt where userid='".$_SESSION['userid']."'";
	$result = pg_query($dbconn,$sql);
	$q_arr = pg_fetch_row($result);

	if($q_arr[1] == 1){
		return "unlimited";
	} else {
		return $q_arr[0];
	}
}

function getSQLresult($dbconn, $sqlcmd)
{
	global $lang;

	$result = pg_query($dbconn, $sqlcmd);
	if(!$result) {
		$mainmsgstr = GetLanguage("lib",$lang);
		$main_db_err = (string)$mainmsgstr->db_err;
		return $main_db_err. " (" .$sqlcmd. ") -- " .pg_last_error($dbconn);
	} else {
		$row = pg_fetch_all($result);
		return $row;
	}
}

function getDataSource($username)
{
	global $dbconn;

	$sqlcmd = "select data_source_id from user_list where userid='$username' ";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row)){
		error_log("ERR: Failed to retrieve data source id for $username .");
	} else {
		return $row[0]['data_source_id'];
	}
}

function dbSafe($raw_string)
{
	$tmpbuf = $raw_string;
	$len = strlen($tmpbuf);
	$resbuf = "";

	for($i=0; $i<$len; $i++)
	{
		$elem = substr($tmpbuf, 0, 1);
		$next = substr($tmpbuf, 1);
		$tmpbuf = $next;

		$tmp = "";
		if(strcmp($elem, "\\") == 0){
			$tmp = '\\\\';
		} else if(strcmp($elem, "'") == 0){
			$tmp = "''";
		} else {
			$tmp = $elem;
		}

		if(strlen($resbuf) == 0) {
			$resbuf = $tmp;
		} else {
			$resbuf .= $tmp;
		}
	}
	return $resbuf;
}

function doSQLcmd($dbconn, $sqlcmd)
{
	global $system_server_mode;

	$result = pg_query($dbconn, $sqlcmd);
	if(!$result)
	{
		error_log($sqlcmd. " -- " .pg_last_error($dbconn));
		return 0;
	}

	if( $system_server_mode != 1 ){ #Not standalone system
		UpdateDBSync($dbconn, $sqlcmd);
	}

	return pg_affected_rows($result);
}

function getEncryptedPassword($password)
{
	$key = $_SESSION['cryptkey'];
	//error_log("xxxxxxxxxx key: " . $key);

	$l = strlen($key);
	if ($l < 16)
		$key = str_repeat($key, ceil(16/$l));

	if ($m = strlen($password)%8)
		$password .= str_repeat("\x00",  8 - $m);
	if (function_exists('mcrypt_encrypt'))
		//DEPRECATED as of PHP 7.1.0 and REMOVED as of PHP 7.2.0
		$val = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $password, MCRYPT_MODE_ECB);
	else
		$val = openssl_encrypt($password, 'BF-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);

	return base64_encode($val);
}

function getDecryptedPassword($password)
{
	$key = $_SESSION['cryptkey'];
	$encrypted_password = base64_decode($password);

	$l = strlen($key);
	if ($l < 16)
		$key = str_repeat($key, ceil(16/$l));

	if (function_exists('mcrypt_encrypt'))
		//DEPRECATED as of PHP 7.1.0 and REMOVED as of PHP 7.2.0
		$val = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $encrypted_password, MCRYPT_MODE_ECB);
	else
		$val = openssl_decrypt($encrypted_password, 'BF-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);

	return html_entity_decode($val);
}

function UpdateDBSync($conn, $sqlstr)
{
	global $dbuser;
	global $dbpass;

	$constr = "host=localhost port=5432 dbname=syncdb user=msg password=msg!@#$%";
	$dbc = pg_connect($constr);

	if( !($dbc) ){
		error_log($constr . ' ' .pg_last_error($dbc));
	}

	$dbname = pg_dbname($conn);

	$pattern1 = "/insert\s+into\s+([\w\d_]+)\s+/i";
	$res = preg_match($pattern1, $sqlstr, $matches, PREG_OFFSET_CAPTURE);
	$tbname = "";
	if( $res == 0 ){
		// Not match!
		$pattern1 = "/update\s+([\w\d_]+)\s+/i";
		$res = preg_match($pattern1, $sqlstr, $matches,PREG_OFFSET_CAPTURE);

		if( $res == 0 ){
			// Still not match!
			$pattern1 = "/delete\s+from\s+([\w\d_]+)\s+/i";
			$res = preg_match($pattern1, $sqlstr, $matches,PREG_OFFSET_CAPTURE);

			if( $res == 0 ){
				// Really not sure what else can it be...
				$tbname = "No tbname found";
			} else {
				$tbname = $matches[1][0];
			}
		} else {
			$tbname = $matches[1][0];
		}
	} else {
		$tbname = $matches[1][0];
	}

	$sqlcmd = "insert into dbsync_queue ".
			"(sqlstring, dblink, dbuser, dbpass) " .
			"values " .
			"('" . rawurlencode($sqlstr) . "', " .
			"'dbi:Pg:dbname=$dbname', " .
			"'" . $dbuser . "', " .
			"'" . $dbpass . "')";
	$result = pg_query($dbc, $sqlcmd);

	if( !$result ){
		error_log($sqlcmd . ' --ERROR-- ' .pg_last_error($dbc));
		return 0;
	}

	$affected = pg_affected_rows($result);

	if( $affected == 0 ){
		error_log($sql . ' -- ' ."NULL Effect");
	}

	return $affected;
}

function getADIntegration(){
	$flag = `cat /home/msg/conf/ad_integration`;
	$flag = trim($flag);
	if ($flag == '1' || strtolower($flag) == 'y' || strtolower($flag) == 'yes'){
		return 1;
	}else{
		return 0;
	}
}

function isUserAdmin($userid)
{
	if(strcmp(strtolower($userid), "useradmin") == 0 || strcmp(strtolower($userid), "momadmin") == 0 )
	{
		return 1;
	}
	else
	{
		return 0;
	}
}

function getUserID($userid)
{
	global $dbconn;

	$sqlcmd = "select id from user_list where userid = '" .dbSafe(strtolower($userid)). "' ";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row))
	{
		error_log("Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		return 0;
	}
	else
	{
		if(empty($row))
		{
			error_log("System Error -- User '" .dbSafe($userid). "' Cannot Be Found In Table 'user_list'");
			return 0;
		}
		else
		{
			return $row[0]['id'];
		}
	}
}

function getUserDepartment($userid)//DO NOT USE THIS ANYMORE, get from session
{
	global $dbconn;

	$sqlcmd = "select department from user_list where userid = '" .dbSafe(strtolower($userid)). "' ";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row))
	{
		error_log("Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		return 0;
	}
	else
	{
		if(empty($row))
		{
			error_log("System Error -- User '" .dbSafe($userid). "' Cannot Be Found In Table 'user_list'");
			return 0;
		}
		else
		{
			return $row[0]['department'];
		}
	}
}

function getSequence($conn,$seqname)
{
	$sqlcmd = "select nextval('$seqname')";
	$res = pg_query($conn,$sqlcmd);
	$c = pg_fetch_array($res,0);
	return $c[0];
}

function getSequenceID($conn,$seqname)
{
	$seq = getSequence($conn,"message_trackid");
	$server_prefix = $_SESSION['server_prefix'];
	$code_prefix = "X";

	if( strcmp($seqname, "user_list_id_seq") == 0 ){
			$code_prefix = "A";
	} else if ( strcmp($seqname, "user_role_list_role_id_seq") == 0 ){
			$code_prefix = "B";
	} else if ( strcmp($seqname, "department_list_department_id_seq") == 0 ){
			$code_prefix = "C";
	} else if ( strcmp($seqname, "quota_mnt_idx_seq") == 0 ){
			$code_prefix = "D";
	} else if ( strcmp($seqname, "outgoing_logs_outgoing_id_seq") == 0 ){
			$code_prefix = "E";
	} else if ( strcmp($seqname, "address_book_contact_id_seq") == 0 ){
			$code_prefix = "F";
	} else if ( strcmp($seqname, "address_group_group_id_seq") == 0 ){
			$code_prefix = "G";
	} else if ( strcmp($seqname, "address_group_main_group_id_seq") == 0 ){
			$code_prefix = "H";
	} else if ( strcmp($seqname, "message_template_template_id_seq") == 0 ){
			$code_prefix = "I";
	} else if ( strcmp($seqname, "user_sub_id_seq") == 0 ){
			$code_prefix = "J";
	} else {
			$code_prefix = "X";
	}

	return $server_prefix . $code_prefix . strftime('%y%j%H%M', time()) . sprintf("%06d", $seq);
}

function readOptNetXMLFile()
{
	$xml_file = simplexml_load_file('/home/msg/conf/optnet.xml');
	$_SESSION['def_webport'] = (string)$xml_file['defwebport'];
	$_SESSION['new_webport'] = (string)$xml_file['webport'];
}

function updateTotal($dbconn, $table, $column, $id, $modified_column, $type, $num)
{
	$new_total = 0;
	$getsql = " select ".dbSafe($modified_column)." from ".dbSafe($table)." where ".dbSafe($column)."='".dbSafe($id)."';";
	$getresult = pg_query($dbconn, $getsql);
	if(!$getresult)
	{
		error_log("Database Error (" .dbSafe($getsql). ") -- " .dbSafe(pg_last_error($dbconn)));
		return 0;
	}
	else
	{
		$get = pg_fetch_all($getresult);
		if(empty($get))
		{
			error_log("ID '" .dbSafe($id). "' Not Found In Table '" .dbSafe($table). "'");
			return 0;
		}
		else
		{
			$total_users = $get[0]["$modified_column"];
			if($total_users != "")
			{
				if($type == 1)
				{
					$new_total = $total_users + $num;
				}
				else if($type == 2)
				{
					if(($total_users != 0) && ($total_users >= $num))
					{
						$new_total = $total_users - $num;
					}
					else
					{
						$new_total = 0;
					}
				}
			}
			else
			{
				if($type == 1)
				{
					$new_total = 1;
				}
				else if($type == 2)
				{
					$new_total = 0;
				}
			}

			$sqlcmd = "update ".dbSafe($table)." set ".dbSafe($modified_column)."='".dbSafe((int)$new_total)."' where ".dbSafe($column)."='".dbSafe($id)."';";
			$row = doSQLcmd($dbconn, $sqlcmd);
			return $row;
		}
	}
}

function totalRecord($tbname, $condition, $conn)
{
	$sql = "select count(*) from $tbname";

	if( strcmp($condition, "NA") != 0 ){
		$sql .= " where $condition";
	}

	$result = pg_query($conn, $sql);

	if( !$result ){
		error_log($sql . ' -- ' . pg_last_error($conn));
		return 0;
	}

	$arr = pg_fetch_array($result, 0);
	return $arr[0];
}

function getAccessString($userid)
{
	global $dbconn;

	$sqlcmd = "select access_string from user_list where userid='" .dbSafe(strtolower($userid)). "' ";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row))
	{
		error_log("Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		return;
	}

	if(empty($row))
	{
		error_log("System Error -- User '" .dbSafe($userid). "' Cannot Be Found In Table 'user_list'");
		return;
	}

	return $row[0]['access_string'];
}

function getPage($id)
{
	global $dbconn;
	$id = (int)$id;

	$sqlcmd = "select page_address, header_name from system_functions where function_id='" .dbSafe($id). "' ";
	$row = getSQLresult($dbconn, $sqlcmd);
	if(is_string($row))
	{
		return;
	}
	else
	{
		if(empty($row))
		{
			return;
		}
		else
		{
			$arr[0] = $row[0]['page_address'];
			$arr[1] = $row[0]['header_name'];
			return implode(",", $arr);
		}
	}
}

function getUserDetails($userid)
{
	global $dbconn;

	$sqlcmd = "select id, mobile_numb, password from user_list where userid='".dbSafe(strtolower($userid))."'";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row))
	{
		error_log("Database Error (".dbSafe($sqlcmd).") -- ".dbSafe(pg_last_error($dbconn)));
		return;
	}
	else
	{
		if(empty($row))
		{
			error_log("System Error -- User '".dbSafe($userid)."' Cannot Be Found In Table 'user_list'");
			return;
		}
		else
		{
			return $row;
		}
	}
}

function getServerPrefixNumber($server_prefix)
{
	$server_prefix_num = 9;
	if(strlen($server_prefix) > 0){
		if($server_prefix == 'A'){
			$server_prefix_num = 1;
		}else if($server_prefix == 'B'){
			$server_prefix_num = 2;
		}
	}

	return $server_prefix_num;
}

//Get config from XML
function Get_ConfigElem($file,$field)
{
    $elem = "";
	$xml = simplexml_load_file($file,'SimpleXMLElement',LIBXML_COMPACT);
	if ($xml === false) {
		foreach(libxml_get_errors() as $error) {
			$elem .= $error->message;
		}
	} else {
		if(isset($xml->$field)) {
			foreach($xml->$field as $list)
			{
				$elem .= $list;
			}
		} else {
			$elem = $xml[$field];
		}
	}

	if( strcmp($field, "threshold_alert_mobile" ) == 0 ){
		return trim($elem);
	}

	return trim(urldecode($elem));
}
function getSQLObj($dbconn, $sqlcmd){								
	
	$result = pg_query($dbconn, $sqlcmd);
	if(!$result)
	{
		$_SESSION['error_msg'] = "Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn));
		return $_SESSION['error_msg'];
	}
	else
	{	
		$obj = pg_fetch_object($result);
	}
	return $obj;		
}
function getUserType( ){
	
	global $dbconn;
	$user_type = "user";
	$sqlcmd = "select user_type from user_list where userid = '".dbSafe( $_SESSION['userid'] )."'";
	
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$user_type = $row['user_type'];
	}

	return strtolower($user_type);
}
function getUserDeptQuota( $userid ){
	
	global $dbconn;
	$Dept_quota_left = 0;
	
	if( strlen($userid) > 0 ){
		
		$sqlcmd = "select quota_left from department_list where department_id in ( select department from user_list where userid = '".dbSafe( $userid )."' )";
	
		$result = pg_query($dbconn, $sqlcmd);
		for ($i=1; $row = pg_fetch_array($result); $i++){
			$Dept_quota_left = $row['quota_left'];
		}
	
	}
	
	return $Dept_quota_left;
}
function getUserDepartment2($userid)
{
	global $dbconn;

	$sqlcmd = "select department from department_list where department_id in ( select department from user_list where userid = '" .dbSafe(strtolower($userid)). "' ) ";
	
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row))
	{
		error_log("Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		return 0;
	}
	else
	{
		if(empty($row))
		{
			error_log("System Error -- User '" .dbSafe($userid). "' Cannot Be Found In Table 'user_list'");
			return 0;
		}
		else
		{
			return $row[0]['department'];
		}
	}
}

function getDepartmentName($department)
{
	global $dbconn;

	$sqlcmd = "select department from department_list where department_id = '$department'";
	
	$row = getSQLresult($dbconn, $sqlcmd);

	return $row[0]['department'] ? $row[0]['department'] : "NA";
	//return $sqlcmd;
}

function insertAuditTrail($action)
{
	global $logdbconn;
	
	$sqlcmd = "insert into mom_audit_log (audit_dtm, user_name, remote_ip, action) ";
	$sqlcmd .= "values (now(),'".pg_escape_string($_SESSION['userid'])."','".pg_escape_string($_SERVER['REMOTE_ADDR'])."','".pg_escape_string($action)."');";
	$result = pg_query($logdbconn, $sqlcmd);

	if(!$result){
		error_log('--AUDIT FAILED-- ' .pg_last_error($logdbconn));
	} else {
		$syslog = explode(',',Get_ConfigElem('/home/msg/conf/reportconfig.xml','syslog_server'));
		foreach($syslog as $server){
			if (!empty($server) && $server!='NA') {
				SysLog::$local = false;
				SysLog::$hostname = trim($server);
				SysLog::send($_SESSION['userid'].' '.trim($action));
			}
		}
	}
}

function getuseridByID($id)
{
	global $dbconn;

	$sqlcmd = "select userid from user_list where id = '" .dbSafe(($id)). "' ";
	
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row))
	{
		error_log("Database Error (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		return 0;
	}
	else
	{
		if(empty($row))
		{
			error_log("System Error -- User '" .dbSafe($userid). "' Cannot Be Found In Table 'user_list'");
			return 0;
		}
		else
		{
			return $row[0]['userid'];
		}
	}
}

function check_upload_file( $file_datas, $file_type, $location_to_save="" ){
	
	if( $file_type == "image" ){
		$allowed_ext = array( "jpg", "jpeg", "png" );
		$allowed_file_size = 5000000;//5mb
	}elseif( $file_type == "pdf" ){
		$allowed_ext = array( "pdf" );
		$allowed_file_size = 5000000;//5mb
	}elseif( $file_type == "excel_csv" ){
		$allowed_ext = array( "xls", "xlsx","csv" );
		$allowed_file_size = 5000000;//5mb
	}elseif( $file_type == "csv" ){
		$allowed_ext = array( "csv" );
		$allowed_file_size = 5000000;//5mb
	}elseif( $file_type == "txt_csv" ){
		$allowed_ext = array( "txt", "csv" );
		$allowed_file_size = 5000000;//5mb
	}
	error_log("check_upload_file: $file_type");
	error_log("name: ".$file_datas['name']);
	error_log("size: ".$file_datas["size"]);
	$getcwd = basename(getcwd());
	$mainURL = "https://" . $_SERVER['HTTP_HOST'] . "/$getcwd/";
	$FileType = strtolower(pathinfo($file_datas['name'],PATHINFO_EXTENSION));
	$returns = array();
	$returns['file_location'] = "";
	
	//echo "file size==" . $file_datas["size"];
	//echo "allowed_file_size==" . $allowed_file_size;
	//die;
	
	if ( $file_datas["size"] < $allowed_file_size ) {
		
		//echo "FileType==" . $FileType;
		//echo '<pre>'; print_r($allowed_ext); echo '</pre>';
		//die;
		
		if( in_array( $FileType, $allowed_ext ) ){
			
			//default is success get new file name
			$returns['new_file_name'] = date("YmdHIs") . mt_rand(100000, 999999) . "." . $FileType;
			$returns['status'] = "1";
			$returns['message'] = "File valid";
			
			//check this file name is exit or not
			if( strlen( $location_to_save ) > 0 ){
				
				$path_to_check = $location_to_save . $returns['new_file_name'];
				$duplicated = file_exists( $path_to_check );
				$max = 3;
				$current_no = 0;
				
				do {
					
					//keep change file name if duplicated file or reached the max tried
					$current_no++;
					
					if( $current_no >= $max ){//reach max tried, skip
						
						//replace to error
						$returns['new_file_name'] = "";
						$returns['status'] = "5";
						$returns['message'] = "Rename file failed.";
						
						break;
			
					}else{
					
						$returns['new_file_name'] = date("YmdHIs") . mt_rand(100000, 999999) . "." . $FileType;//rename for next loop checking
						$returns['status'] = "1";
						$returns['message'] = "File valid";
					
					}
					
					$path_to_check = $location_to_save . $returns['new_file_name'];
					$duplicated = file_exists( $path_to_check );//for next loop if have
						
				} while ( $duplicated && $current_no < $max );
				
				if( $returns['status'] == "1" ){
					
					//save file
					if ( move_uploaded_file( $file_datas["tmp_name"], $location_to_save . $returns['new_file_name'] ) ) {//saved
					
						//no error
						$returns['file_location'] = $mainURL . $location_to_save . $returns['new_file_name'];
						
					}else{
					
						$returns['new_file_name'] = "";
						$returns['status'] = "2";
						$returns['message'] = "Failed save file.";
			
					}
					
				}
				
			}
			
		}else{
			
			$returns['new_file_name'] = "";
			$returns['status'] = "3";
			$returns['message'] = "Invalid file type";
		}
		
	}else{
	
		$returns['new_file_name'] = "";
		$returns['status'] = "4";
		$returns['message'] = "Invalid file size";
	}
	
	return $returns;
}

function validDate($pdate){
	#dd-mm-yyyy
	#error_log("Date: $pdate");
	$dateArr = explode("-",$pdate);
	if(checkdate($dateArr[1],$dateArr[0],$dateArr[2])){
		return 1;
	}else{
		return 0;
	}
}

function checkDateDiff($sdate,$edate){
	$stime = strtotime($sdate);
	$etime = strtotime($edate);
	$diff = $etime-$stime;
	error_log("stime: $stime, etime: $etime, Diff: $diff");
	if($diff>=0){
		return 1;
	}else{
		return 0;
	}
}

function checkTodayDate($sdate){
	$tdate = date("d-m-Y");
	$ttime = strtotime($tdate);
	$stime = strtotime($sdate);
	$diff = $ttime-$stime;
	error_log("ttime: $ttime, stime: $stime, Diff: $diff");
	if($diff<=0){
		return 1;
	}else{
		return 0;
	}
}

function validateSize($pName,$pVal,$pType){
	global $validateSizeMsg;
	// pType : UID, PWD, ID, NAME, DESC, MSG, KEY, AID, SID
    $defineMin = array ("UID" => 8, "PWD" => 12, "ID" => 1, "NAME" => 1, "DESC" => 0, "SHORTMSG" => 0, "LONGMSG" => 1, "MIMMSG" => 1, "KEY" => 1, "AID" => 1, "SID" => 1);
    $defineMax = array ("UID" => 15, "PWD" => 128, "ID" => 15, "NAME" => 30, "DESC" => 100, "SHORTMSG" => 160, "LONGMSG" => 1530, "MIMMSG" => 4096, "KEY" => 15, "AID" => 32, "SID" => 50);

	$min = $defineMin[$pType];
    $max = $defineMax[$pType];

    $len = strlen($pVal);
	if($len < $min){
		$validateSizeMsg = "Sorry, \"".$pName."\" must be in at least ".$min ;
        return 0;
    }else if($len > $max){
        $validateSizeMsg = "Sorry, \"".$pName."\" reach maximim limit of ".$max;
        return 0;
    }else if($len >= $min && $len <= $max){
        return 1;
    }
}

function getValidateSizeMsg(){
	global $validateSizeMsg;
    return $validateSizeMsg;
}

function validateMno($mno){

	$mno = trim($mno);
	$mno_len = strlen($mno);
	
	if (!preg_match('/^[0-9+]*$/', $mno)){//only digits
		return "-1";
	}
	
	if(!($mno_len == 11 || $mno_len == 10 || $mno_len == 8)){
		return "-1";
	}
	
	if($mno_len == 11){
	
		if( substr($mno,0,4) == "+658" || substr($mno,0,4) == "+659" ){
			return $mno;
		}else{
			return "-1";
		}
	
	}
	
	if($mno_len == 10){
	
		if( substr($mno,0,3) == "658" || substr($mno,0,3) == "659" ){
			return "+" . $mno;
		}else{
			return "-1";
		}
	
	}
	
	if($mno_len == 8){
		if( substr($mno,0,1) == "9" || substr($mno,0,1) == "8" ){
			return "+65" . $mno;
		}else{
			return "-1";
		}
	}
}


?>
