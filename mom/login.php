<?php
// TODO maybe here can add header 401 status code upon failed login
require_once('lib/commonFunc.php');
// $weburl = "http://192.168.1.52";
$weburl = "http://localhost";

function getBUAdmin($deptid) {
    // there is a case that a bu will have many admins, but only one bu return first
    global $dbconn;
    $sqlcmd = "SELECT id,userid FROM user_list where user_list.department = '$deptid' AND user_type = 'bu' ORDER BY 1 LIMIT 1";

    if ($result = pg_query($dbconn,$sqlcmd)) {
        if ($row = pg_fetch_array($result)) {
            $temp = array($row["id"],$row["userid"]);
        }
        pg_free_result($result);
    }
    return isset($temp) ? $temp : false;
}

switch ($_POST['mode']) {
	case 'login':
		if (isset($_POST["userCaptchaInput"])) {
			if ($_SESSION["captcha_text"] != strtoupper($_POST["userCaptchaInput"])) {
				$data['status'] = "2";
				$data['redirect'] = "process.php";
				$data['refreshcaptcha'] = "1";

				$_SESSION["userid"] = "LOGIN SYSTEM";
				insertAuditTrail("{$_POST[username]} has entered the wrong captcha");
				unset($_SESSION["userid"]);

				echo json_encode($data);
				break;
			} else if (time() - $_SESSION["captcha_time"] > 120) {
				$data['status'] = "Captcha is timeout. Please enter again";
				$data['refreshcaptcha'] = "1";
				echo json_encode($data);
				break;
			}
		}
		if (isUserAdmin(trim(filter_input(INPUT_POST,'username'))) ) {
			
			if( trim(filter_input(INPUT_POST,'username')) == "momadmin" ){
				loginv2(trim(filter_input(INPUT_POST,'username')),
				trim(filter_input(INPUT_POST,'password')));
			}else{
				login(trim(filter_input(INPUT_POST,'username')),
				trim(filter_input(INPUT_POST,'password')));
			}
        	
		} else {
			loginv2(trim(filter_input(INPUT_POST,'username')),
			trim(filter_input(INPUT_POST,'password')));
		}
		break;
	case 'update':
		changeUser(filter_input(INPUT_POST,'id'),
					filter_input(INPUT_POST,'change_userid'),
					trim(filter_input(INPUT_POST,'change_mobile_numb')),
					trim(filter_input(INPUT_POST,'old_password')),
					trim(filter_input(INPUT_POST,'change_password')),
					trim(filter_input(INPUT_POST,'confirm_password')));
		break;
	case 'resendcode':
		resend(trim(filter_input(INPUT_POST,'sessionid')),trim(filter_input(INPUT_POST,'username')));
		break;
	case '2falogin':
		check2FA(trim(filter_input(INPUT_POST,'otp')),trim(filter_input(INPUT_POST,'sessionid')),trim(filter_input(INPUT_POST,'username')));
		break;
	default:
        die('Unknown request');
}

function checkExpiredPassword($a,$b,$c) {
	// $a true or false, $b days $c date_diff (calculated by postgresql)
	if ($a === "t")
		return true;
	else if (intval($c) == -1)
		return false;	// not suppose to happen, check later
	else if (intval($c) > intval($b))
		return true;
	return false;
}
function foo1($username) {
	global $dbconn;
	$sqlcmd = "SELECT userid from user_list where user_type = 'bu' and department = (select department from user_list where userid = '".dbSafe($username)."') order by userid LIMIT 1";

	$row = getSQLresult($dbconn,$sqlcmd);
	if (is_string($row)) {
		// cannot found, possible is no bu assigned
		return false;
	} else {
		return $row;
	}
}
function otpdeduct($username) {
	// check available quota
	global $dbconn;
	$sqlcmd = "SELECT quota_left,unlimited_quota FROM quota_mnt WHERE userid = '".dbSafe($username)."'";
	$row = getSQLresult($dbconn,$sqlcmd);
	if (is_string($row)) {
		// no user found...?
		return false;
	} else {
		if ($row[0]["unlimited_quota"] == 1) {
			$msgid = date("YmdHIs") . mt_rand(100000, 999999);
			$outid_prefix = "CO".strftime('%y%j%H%M', time());
			$row1 = getSQLresult($dbconn,"SELECT department,mobile_numb FROM user_list WHERE userid = '".dbSafe($username)."' LIMIT 1");	
			// insert the 'message' to outgoing_logs
			$sqlcmd = "INSERT INTO outgoing_logs (outgoing_id,trackid,msgid,sent_by,department,mobile_numb,message,message_status,sent_dtm,completed_dtm,created_dtm,modified_by,modified_dtm,priority,callerid,totalsms,modem_label,inc_id,new_totalsms,bc_id,campaign_id,bot_message_status_id,status_code,delivered_dtm,bot_types_id,file_location,mim_tpl_id,is_deleted) VALUES (
				concat('$outid_prefix',nextval('outgoing_logs_outgoing_id_seq')),concat('C',nextval('message_trackid')),'$msgid','$username','".$row1[0]["department"]."','".$row1[0]["mobile_numb"]."','OTP Code sent to User','Y',now(),now(),now(),null,null,5,null,1,null,null,null,null,0,0,null,null,null,null,'',false)";
            $row = getSQLresult($dbconn,$sqlcmd);
			return true;
		} else if ($row[0]["quota_left"] > 0) {
			// deduct a quota from user
			$sqlcmd = "UPDATE quota_mnt SET quota_left = quota_left - 1 WHERE userid = '".dbSafe($username)."' AND unlimited_quota != 1";
			$row = getSQLresult($dbconn,$sqlcmd);
			$msgid = date("YmdHIs") . mt_rand(100000, 999999);
			$outid_prefix = "CO".strftime('%y%j%H%M', time());
			$row1 = getSQLresult($dbconn,"SELECT department,mobile_numb FROM user_list WHERE userid = '".dbSafe($username)."' LIMIT 1");	
			// insert the 'message' to outgoing_logs
			$sqlcmd = "INSERT INTO outgoing_logs (outgoing_id,trackid,msgid,sent_by,department,mobile_numb,message,message_status,sent_dtm,completed_dtm,created_dtm,modified_by,modified_dtm,priority,callerid,totalsms,modem_label,inc_id,new_totalsms,bc_id,campaign_id,bot_message_status_id,status_code,delivered_dtm,bot_types_id,file_location,mim_tpl_id,is_deleted) VALUES (
				concat('$outid_prefix',nextval('outgoing_logs_outgoing_id_seq')),concat('C',nextval('message_trackid')),'$msgid','$username','".$row1[0]["department"]."','".$row1[0]["mobile_numb"]."','OTP Code sent to User','Y',now(),now(),now(),null,null,5,null,1,null,null,null,null,0,0,null,null,null,null,'',false)";
            $row = getSQLresult($dbconn,$sqlcmd);
			return true;
		} else {
			// error_log($_SERVER['REMOTE_ADDR']."$username unable to deduct");
			return false;
		}
	}
}
function loginv2($username,$password) {	// modify from login
	global $dbconn,$weburl;
	$data = array();
	
	if(empty($username) || empty($password)) {
		$data['status'] = 'Sorry, Please Complete The Login Form';
	} else {
		$cmd = "SELECT password,data_source_id,retry_attempt,last_login_dtm,mobile_numb,pwd_threshold FROM user_list WHERE userid='".pg_escape_string($username)."';";
		$res = pg_query($dbconn, $cmd);
		$num = pg_num_rows($res);
		
		if($num){
			$row = pg_fetch_row($res);
		
			if (isLocked($row[2],$row[3],$row[5])) {
				// $data['status'] = 'Your account has been locked, please try again in 30 minutes'; // Ty's comment: not sure stick to 30 minutes on screen.
				$data['status'] = 3;
				$data['redirect'] = "index.php";
				
				$_SESSION["userid"] = "LOGIN SYSTEM";
				insertAuditTrail("$username has been locked due trying to login failed multiple times");

				insertAuditTrail($_SERVER["REMOTE_ADDR"]." has been blocked");
				unset($_SESSION["userid"]);

				$sqlcmd = "INSERT INTO blockingip (ipaddress) VALUES('".$_SERVER["REMOTE_ADDR"]."')";
				$result = doSQLcmd($dbconn,$sqlcmd);	// function has logged if sql failed

			} else {
				
				//check access end date
				$userExpired = checkAccessDate( $username );
				
				if( !$userExpired['expired'] ){
					
					if(checkUser($username, $password, $row[0], $row[1])) {
						unset($_SESSION["loginattempt"]);
						if (otpdeduct($username)) {
							/**
							 * @param id webotp login username
							 * @param passwd webotp login password
							 * @param username uses from form... ?
							 * @param mobile retrieve from mom database
							 * @param session_id when resend otp, use this
							 * @param resend default 0 to request new otp, 1 to resend otp when has session_id
							 * @desc retrieve login from login form, pass the info to inner website. form method is POST 
							 */
							
							// http://192.168.1.52/webotp/otp_http.php?id=test&passwd=123&mobile=97525363&username=default
							// other param session_id, resend, 
							$ch = curl_init();
							$mobilenum = urlencode(validateMno($row[4]));
							curl_setopt($ch,CURLOPT_URL,"$weburl/webotp/otp_http.php?id=momappotp&passwd=M0M@pp0tp&mobile=$mobilenum&username=$username");

							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							$server_output = curl_exec($ch);
							curl_close ($ch);

							// if success connect (response code is 205), get a sessionid as output and return to index.php
							// other failed response code as following:
							// 102 : no or invalid id
							// 108 : id exists but no or invalid password
							// 104 : mobile number is required
							// 112 : username is required

							if (substr($server_output,0,3) == "205") {
								$temp = explode(",",$server_output);
								$data['status'] = $temp[0];
								$data['sessionid'] = $temp[1];
								$data['mobileno'] = "****".substr($row[4],-4);
								
							} else {
								$data['status'] = "Error. Unable to retrieve OTP, please try again later. Error CODE: ".$server_output;
								// leave log here
							}
						} else {
							$data['status'] = "Insufficient Quota. Contact your admin to top up.";
						}
					} else {
						updateReTryAttempt($username,'F');

						$_SESSION["userid"] = "LOGIN SYSTEM";
						insertAuditTrail("$username has entered the wrong password");
						unset($_SESSION["userid"]);
						
						$data['status'] = 2;
						$data['redirect'] = "process.php";
						error_log($_SERVER['REMOTE_ADDR']." Invalid $username password");
					}
				}else{
					updateReTryAttempt($username,'F');
					$data['status'] = "Unable to login. Account access date blocked or expired.";
					error_log($_SERVER['REMOTE_ADDR']."$username access date blocked or expired");
				}
			}
		} else {
			$data['status'] = 2;
			$data['redirect'] = "process.php";
			$_SESSION["userid"] = "LOGIN SYSTEM";
			insertAuditTrail("$username has entered the wrong password");
			unset($_SESSION["userid"]);
		}
	}
	echo json_encode($data);
}
function resend($sessionid,$username) {
	global $dbconn, $weburl;
	if (otpdeduct($username)) {
		$res = pg_query($dbconn,"SELECT mobile_numb FROM user_list WHERE userid='".pg_escape_string($username)."';");
		if (!res) {
			$data['status'] = "Unexpected error occured (DB)";
		} else {
			if ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
				$ch = curl_init();
				
				$mobilenum = urlencode(validateMno($row["mobile_numb"]));
				curl_setopt($ch,CURLOPT_URL,"$weburl/webotp/otp_http.php?id=momappotp&passwd=M0M@pp0tp&mobile=$mobilenum&username=$username&session_id=$sessionid&resend=1");

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$server_output = curl_exec($ch);
				curl_close ($ch);

				if (substr($server_output,0,3) == "205") {
					$temp = explode(",",$server_output);
					$data['status'] = $temp[0];
					$data['sessionid'] = $temp[1];
					$data['mobileno'] = "****".substr($row["mobile_numb"],-4);

				} else {
					if ($server_output == "122") {
						switch($server_output) {
							case "122":
								$data['status'] = "Error Code 122, Invalid Session ID. Possible Session Timeout. Recommended to refresh the page";
								break;
							default:
								$data['status'] = "Error. Unable to retrieve OTP, please try again later. Error CODE: ".$server_output;
							}
					}
					// leave log here
				}
			} else {
				$data['status'] = "Unexpected error occured (DB) - 2";
			}
		}
		echo json_encode($data);
	} else {
		$data['status'] = "Insufficient Quota. Contact your admin to top up.";
	}
}
function check2FA($otp,$sessionid,$username) {
	global $dbconn,$weburl, $sqdbconn;
	$data = array();
	// connect db to obtain mobile number first
	$res = pg_query($dbconn,"SELECT access_string,user_role,department,language,mobile_numb,timeout,chg_onlogon,pwd_expire,case when pwd_lastchg is not NULL then DATE_PART('day', now()::timestamp - pwd_lastchg::timestamp) else -1 END AS daypass FROM user_list WHERE userid='".pg_escape_string($username)."';");
	if (!$res) {
		$data['status'] = "Unexpected error occured (DB)";
	} else {
		if ($row = pg_fetch_row($res)) {
			$ch = curl_init();
			$mobilenum = urlencode(validateMno($row[4]));
			curl_setopt($ch,CURLOPT_URL,"$weburl/webotp/session_http.php?username=$username&session_id=$sessionid&token=$otp&mobile=$mobilenum");
			// ex: http://192.168.1.52/webotp/session_http.php?username=default&mobile=97525363&token=165362&session_id=ztnH 

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$server_output = curl_exec($ch);
			curl_close ($ch);
			if ($server_output == "201") {
				// partial copy from original login function
				$_SESSION['userid'] = $username;
				$_SESSION['access_string'] = $row[0];
				$_SESSION['user_role'] = $row[1];
				$_SESSION['department'] = $row[2];
				$_SESSION['language'] = $row[3];
				$_SESSION['timeout'] = intval($row[5]) < 2 ? 2 : intval($row[5]);
				$_SESSION['timestamp'] = time();

				//Get Maximum Number Of SMS
				$xml_file = simplexml_load_file('/home/msg/conf/app_config.xml');
				$_SESSION['max_sms'] = (int)$xml_file['sms_per_email'];;
				$_SESSION['long_sms'] = (int)$xml_file['long_sms'];
				
				//replace
				$sqlcmd0 = "SELECT config_value FROM system_config where config_key = 'max_long_sms_part'";
				$result0 = pg_query($sqdbconn, $sqlcmd0);
				$num = pg_num_rows($result0);
				if($num){
					$row0 = pg_fetch_row($result0);
					
					$_SESSION['max_sms'] = (int)$row0[0] ;

				}

				$cluster_file = simplexml_load_file('/home/msg/conf/clusterconfig.xml');
				$system_server_mode = $cluster_file['system_mode'];

				if($system_server_mode == '2'){ #primary server
					$_SESSION['server_prefix'] = 'P';
				}else if($system_server_mode == '3'){ #secondary server
					$_SESSION['server_prefix'] = 'S';
				}else if($system_server_mode == '4' || $system_server_mode == '5'){ #data sync mode
					$_SESSION['server_prefix'] = (string)$cluster_file['prefix'];
				}else{
					$_SESSION['server_prefix'] = 'C';
				}

				if($system_server_mode == '2'){ #primary server
					$_SESSION['server_prefix_num'] = '3';
				}else if($system_server_mode == '3'){ #secondary server
					$_SESSION['server_prefix_num'] = '4';
				}else if($system_server_mode == '4' || $system_server_mode == '5'){ #data sync mode
					$_SESSION['server_prefix_num'] = getServerPrefixNumber($_SESSION['server_prefix']);
				}else{
					$_SESSION['server_prefix_num'] = '9';
				}

				if (checkExpiredPassword($row[6],$row[7],$row[8])) {
					$_SESSION['needchgpwd'] = "yes";
				} else {
					$_SESSION['needchgpwd'] = "no";
				}

				session_regenerate_id();
				session_write_close();
				updateReTryAttempt($username,'S');

				$user_agent = $_SERVER['HTTP_USER_AGENT'];
				if(strlen($user_agent) > 100)
				{
					$user_agent = substr($user_agent, 0, 100);
				}

				$sqlcmd2 = "insert into access_log (userid,remote_ip,user_agent) values ('".pg_escape_string($username)."','".$_SERVER['REMOTE_ADDR']. "','".$user_agent."');";
				doSQLcmd($dbconn, $sqlcmd2);

				$access_arr = explode(',',$_SESSION['access_string']);
				if(empty($access_arr))
				{
					$data['status'] = 'You Do Not Have Access Rights To Any Of The System Functions. Please Contact System/Department Administrator With Regards To Your System Access Rights';
				} 
				else 
				{
					if(in_array('7', $access_arr))
					{
						$data['status'] = "1";
						//Check if there's a custom send sms page
						if(file_exists('project/send_sms.php')){
							$data['redirect'] = 'project/send_sms.php';
						} else {
							$data['redirect'] = 'send_sms.php';
						}
					} else {
						
						for($i=0; $i<count($access_arr); $i++)
						{
							$mode = $access_arr[$i];

							if( strlen($mode) == 0 ){
								continue;
							}

							if(($mode != 1) && ($mode != 8) && ($mode != 15) && ($mode != 16) && ($mode != 21) && ($mode != 22) && ($mode != 23) && ($mode != 24) && ($mode != 41) && ($mode != 42) && ($mode != 43)
							&& ($mode != 44) && ($mode != 45))
							{
								$sqlcmd3 = "select page_address from system_functions where function_id='".pg_escape_string($mode)."';";
								$result3 = pg_query($dbconn, $sqlcmd3);
								if(!$result3)
								{
									$data['status'] = "Database Error ".pg_last_error($dbconn);
								}
								
								$page_arr = pg_fetch_all($result3);
								if(empty($page_arr))
								{
									$data['status'] = 'System Error: Cannot Retrieve Page Address';
									error_log($page_arr);
								}
								else
								{
									$page = $page_arr[0]['page_address'];
								}
								
								if($page != "")
								{
									$data['status'] = "1";
									$data['redirect'] = $page;
								}
							} 
						}
					}
				}
			} else {
				$data['status'] = "Failed to authenticate OTP. Error Code: ".$server_output;
				switch($server_output) {
					case "103":
						$data['status'] .= " (Missing Parameter)";
						break;
					case "109":
						$data['status'] .= " (Max Resend Reached)";
						break;
					case "120":
						$data['status'] .= " (Invalid Token)";
						break;
					case "121":
						$data['status'] .= " (Session Expired)";
						break;
					case "122":
						$data['status'] .= " (Invalid Session ID)";
						break;
					default:
						$data['status'] .= "Unexpected error on otp side (code: $server_output)";
						error_log('Unable show expected error by webotp\'s session_http.php. Error code:'.$server_output);
				}
			}
		} else {
			$data['status'] = "Unexpected error occured (DB) -2";
		}
	}
	echo json_encode($data);
}

function login($username,$password) // old version before 2fa
{
	global $dbconn, $sqdbconn;
	$data = array();
	
	if(empty($username) || empty($password)) {
		$data['status'] = 'Sorry, Please Complete The Login Form';
	} else {
		$cmd = "select password,data_source_id,retry_attempt,last_login_dtm,access_string,user_role,department,language,timeout,chg_onlogon,pwd_expire,case when pwd_lastchg is not NULL then DATE_PART('day', now()::timestamp - pwd_lastchg::timestamp) else -1 end AS daypass
				from user_list where userid='".pg_escape_string($username)."';";
		$res = pg_query($dbconn, $cmd);
		$num = pg_num_rows($res);
		
		if($num){
			$row = pg_fetch_row($res);
		
			if (isLocked($row[2],$row[3],$row[5])) {
				$data['status'] = 'Your account has been locked, please try again in 30 minutes';
			} else {
				
				//check access end date
				$userExpired = checkAccessDate( $username );
				
				if( !$userExpired['expired'] ){
					
					if( checkUser($username, $password, $row[0], $row[1]) )
					{
						$_SESSION['userid'] = $username;
						$_SESSION['access_string'] = trim($row[4]);
						$_SESSION['user_role'] = trim($row[5]);
						$_SESSION['department'] = trim($row[6]);
						$_SESSION['language'] = trim($row[7]);
						$_SESSION['timeout'] = intval($row[8]) < 2 ? 2 : intval($row[8]);
						$_SESSION['timestamp'] = time();

						//Get Maximum Number Of SMS
						$xml_file = simplexml_load_file('/home/msg/conf/app_config.xml');
						$_SESSION['max_sms'] = (int)$xml_file['sms_per_email'];;
						$_SESSION['long_sms'] = (int)$xml_file['long_sms'];
						
						//replace
						$sqlcmd0 = "SELECT config_value FROM system_config where config_key = 'max_long_sms_part'";
						$result0 = pg_query($sqdbconn, $sqlcmd0);
						$num = pg_num_rows($result0);
						if($num){
							$row = pg_fetch_row($result0);
							
							$_SESSION['max_sms'] = (int)$row[0] ;

						}

						$cluster_file = simplexml_load_file('/home/msg/conf/clusterconfig.xml');
						$system_server_mode = $cluster_file['system_mode'];

						if($system_server_mode == '2'){ #primary server
							$_SESSION['server_prefix'] = 'P';
						}else if($system_server_mode == '3'){ #secondary server
							$_SESSION['server_prefix'] = 'S';
						}else if($system_server_mode == '4' || $system_server_mode == '5'){ #data sync mode
							$_SESSION['server_prefix'] = (string)$cluster_file['prefix'];
						}else{
							$_SESSION['server_prefix'] = 'C';
						}

						if($system_server_mode == '2'){ #primary server
							$_SESSION['server_prefix_num'] = '3';
						}else if($system_server_mode == '3'){ #secondary server
							$_SESSION['server_prefix_num'] = '4';
						}else if($system_server_mode == '4' || $system_server_mode == '5'){ #data sync mode
							$_SESSION['server_prefix_num'] = getServerPrefixNumber($_SESSION['server_prefix']);
						}else{
							$_SESSION['server_prefix_num'] = '9';
						}

						if (checkExpiredPassword($row[9],$row[10],$row[11])) {
							$_SESSION['needchgpwd'] = "yes";
						} else {
							$_SESSION['needchgpwd'] = "no";
						}

						session_regenerate_id();
						session_write_close();
						updateReTryAttempt($username,'S');

						$user_agent = $_SERVER['HTTP_USER_AGENT'];
						if(strlen($user_agent) > 100)
						{
							$user_agent = substr($user_agent, 0, 100);
						}

						$sqlcmd2 = "insert into access_log (userid,remote_ip,user_agent) values ('".pg_escape_string($username)."','".$_SERVER['REMOTE_ADDR']. "','".$user_agent."');";
						doSQLcmd($dbconn, $sqlcmd2);

						$access_arr = explode(',',$_SESSION['access_string']);
						if(empty($access_arr))
						{
							$data['status'] = 'You Do Not Have Access Rights To Any Of The System Functions. Please Contact System/Department Administrator With Regards To Your System Access Rights';
						} 
						else 
						{
							if(in_array('7', $access_arr))
							{
								$data['status'] = "1";
								//Check if there's a custom send sms page
								if(file_exists('project/send_sms.php')){
									$data['redirect'] = 'project/send_sms.php';
								} else {
									$data['redirect'] = 'send_sms.php';
								}
							} else {
								for($i=0; $i<count($access_arr); $i++)
								{
									$mode = $access_arr[$i];

									if( strlen($mode) == 0 ){
										continue;
									}

									if(($mode != 1) && ($mode != 8) && ($mode != 15) && ($mode != 16) && ($mode != 21) && ($mode != 22) && ($mode != 23) && ($mode != 24) && ($mode != 41) && ($mode != 42) && ($mode != 43)
									&& ($mode != 44) && ($mode != 45))
									{
										$sqlcmd3 = "select page_address from system_functions where function_id='".pg_escape_string($mode)."';";
										$result3 = pg_query($dbconn, $sqlcmd3);
										if(!$result3)
										{
											$data['status'] = "Database Error ".pg_last_error($dbconn);
										}
										
										$page_arr = pg_fetch_all($result3);
										if(empty($page_arr))
										{
											$data['status'] = 'System Error: Cannot Retrieve Page Address';
											error_log($page_arr);
										}
										else
										{
											$page = $page_arr[0]['page_address'];
										}
										
										if($page != "")
										{
											$data['status'] = "1";
											$data['redirect'] = $page;
										}
									} 
								}
							}
						}
					} else {
						updateReTryAttempt($username,'F');
						$data['status'] = 2;
						$data['redirect'] = "process.php";
						error_log($_SERVER['REMOTE_ADDR']." Invalid $username password");
					}
				
				}else{
					updateReTryAttempt($username,'F');
					$data['status'] = "Unable to login. Account access date blocked or expired.";
					error_log($_SERVER['REMOTE_ADDR']."$username access date blocked or expired");
				}
				
			}
		} else {
			$data['status'] = 2;
			error_log($_SERVER['REMOTE_ADDR']." User $username does not exist");
		}
	}
	echo json_encode($data);
}

function checkAccessDate( $username ){

	global $dbconn;
	$datas['access_end'] = "";
	$datas['expired'] = 0;
	$datas['now'] = date("Y-m-d 00:00:00");
	
	if( $username != "useradmin" && $username != "momadmin" ){
		
		$datas['sql1'] = "select end_dtm from user_sub where userid = '".dbSafe( $username )."' and start_dtm <= '".dbSafe( $datas['now'] )."'";
		
		$result1 = pg_query($dbconn, $datas['sql1'] );
		for ($i=1; $row1 = pg_fetch_array($result1); $i++){
			$datas['access_end'] = $row1['end_dtm'];
		}

		if( ( strtotime( $datas['access_end'] ) <= strtotime( $datas['now'] ) ) || strlen( $datas['access_end'] ) == 0 ){
			$datas['expired'] = 1;
		}
	
	}
	
	return $datas;
}

function checkUser($username, $password, $user_password, $data_source_id)
{
	global $lang;

	$mainmsgstr = GetLanguage("lib",$lang);
	$main_user_str = (string)$mainmsgstr->user;
	$main_checkUser_msg1 = (string)$mainmsgstr->checkUser_msg1;
	$main_getUserPassword_msg1 = (string)$mainmsgstr->getUserPassword_msg1;

	if(empty($data_source_id)) {
		$_SESSION['cryptkey'] = 'Shinjitsu wa Itsumo Hitotsu';
		/* Somehow, there is an extra \x00 (null) characters at the end
		   of the string, not sure why... This due to encrypt, decrypt? */
		/* $pattern = "/\\x00/";
		$replace = "";
		$user_password = preg_replace($pattern, $replace, getDecryptedPassword($user_password)); */

		if(strcmp(getEncryptedPassword($password), $user_password) == 0) // replace plain compare plain compare to encrypted compare encrypted
		{
			return 1;
		} else {
			return 0;
		}
	} else {
		$l_info = getLdapInfo($data_source_id);
		$l_loginmode = $l_info['l_loginmode'];
		$l_domain  = $l_info['l_domain'];
		$ad_userid = $username;

		if($l_loginmode == 'loginid') {
			$ad_userid = strtoupper($l_domain)."\\".$username;
		}

		$l_ip1 = trim($l_info['l_ip1']);
		$l_port1 = trim($l_info['l_port1']);

		$l_ip2 = trim($l_info['l_ip2']);
		$l_port2 = trim($l_info['l_port2']);

		$l_port1 = strlen($l_port1)==0? 389:$l_port1;

		if($l_ip1 == '' or $l_port1=='') {
			error_log("No LDAP Server AND port defined.");
			return 0;
		}

		$ds = ldap_connect($l_ip1, $l_port1);
		$anon = @ldap_bind( $ds );
		if(!$anon)
		{
			error_log("Can't connect to primary server.");
			if($l_ip2 != '') {
				if($l_port2 == '') { $l_port2 = 389;}
				$ds=ldap_connect($l_ip2, $l_port2);
				$anon = @ldap_bind( $ds );
			}
		}

		if(!$anon) {
			error_log("LDAP connection fail");
			return 0;
		}

		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
		ldap_set_option($ds, LDAP_OPT_SIZELIMIT, 0);

		$r = ldap_bind($ds, $ad_userid, $password);
		if( !$r ){
			error_log('Unable to bind with AD Server.. for user '.$username.'.. '.ldap_error($ds));
			return 0;
		} else {
			return 1;
		}
	}
}

function changeUser($id,$userid,$mobile_numb,$old_password,$password,$confirm_password)
{
	global $dbconn,$lang;

	$mainmsgstr = GetLanguage("lib",$lang);
	$main_changeUserDetails_msg1 = (string)$mainmsgstr->changeUserDetails_msg1;
	$main_changeUserDetails_msg2 = (string)$mainmsgstr->changeUserDetails_msg2;
	$main_changeUserDetails_msg3 = (string)$mainmsgstr->changeUserDetails_msg3;
	
	$exist_pwds = getOldPasswords($userid);
	
	//Should be handled by js
	if(strcmp($password, $confirm_password) !== 0){
		echo '<i class="fa fa-exclamation fa-lg fa-fw"></i>Confirm password is not identical to new password.';
	} else if(!valid_pass($password)){
		echo '<i class="fa fa-exclamation fa-lg fa-fw"></i>The password is not meet the requirement, please check them';
		/*echo "Password must meet the following requirements. \n".
				"(1) Minimun password length: 8 \n".
				"(2) Maximum password length: 16 \n".
				"(3) At least one character from this group [A-Z] \n".
				"(4) At least one character from this group [a-z] \n".
				"(5) At least one character from this group [0-9]";*/
	} else if(strcmp(getEncryptedPassword($old_password), $exist_pwds[0]) !== 0){
		echo '<i class="fa fa-exclamation fa-lg fa-fw"></i>The old password entered is incorrect! Please check again!';
	} else {
		
		$password = getEncryptedPassword($password);

		if(strcmp($password, $exist_pwds[0]) == 0 || 
			strcmp($password, $exist_pwds[1]) == 0 || 
			strcmp($password, $exist_pwds[2]) == 0) {
			echo '<i class="fa fa-exclamation fa-lg fa-fw"></i>New password can not be the same as previous 3 old passwords.';
		} else {
			$sqlcmd = "update user_list set 
					password='".$password."',
					mobile_numb='".pg_escape_string($mobile_numb)."',
					old_pwd1='".pg_escape_string($exist_pwds[0])."',
					old_pwd2='".pg_escape_string($exist_pwds[1])."',
					modified_dtm=now(), pwd_lastchg=now(), chg_onlogon=FALSE
					where userid='".pg_escape_string(strtolower($userid))."' and id='".pg_escape_string($id)."';";
			$row = doSQLcmd($dbconn, $sqlcmd);
			
			if($row == 0)
			{
				echo $main_changeUserDetails_msg1;
			}
			else if($row == 1)
			{
				echo $main_changeUserDetails_msg2;
				unset($_SESSION['needchgpwd']);
			}
			else
			{
				echo $main_changeUserDetails_msg3;
			}
		}
	}
}

function valid_pass2($candicate) { // 2nd checkup by php after javascript
	$r1='/[A-Z]/'; //Uppercase
	$r2='/[a-z]/'; //lowercase
	$r3='/[0-9]/'; //numbers

	$result = 0;

	if (strlen($candidate) < 12 ) {
		return FALSE;
	} else {
		$result += (preg_match_all($r1,$candidate,$o)>0) ? 1 : 0;
		$result += (preg_match_all($r2,$candidate,$o)>0) ? 1 : 0;
		$result += (preg_match_all($r3,$candidate,$o)>0) ? 1 : 0;

		return $result < 2 ? FALSE : TRUE;
	}
	return TRUE;
}
function valid_pass($candidate) 
{
   $r1='/[A-Z]/'; //Uppercase
   $r2='/[a-z]/'; //lowercase
   $r3='/[0-9]/'; //numbers

   if(preg_match_all($r1,$candidate, $o)<1) return FALSE;

   if(preg_match_all($r2,$candidate, $o)<1) return FALSE;

   if(preg_match_all($r3,$candidate, $o)<1) return FALSE;

   if(strlen($candidate)<8 || strlen($candidate) > 16 ) return FALSE;

   return TRUE;
}

function updateReTryAttempt($userid, $type)
{
	global $dbconn;
	
	if($type == 'S') {
		$set_retry = "retry_attempt=0";
	} else {
		$set_retry = "retry_attempt=retry_attempt+1";
	}
	
	$cmd = "update user_list set ".$set_retry.",last_login_dtm=now() where userid='".$userid."';";
	$res = pg_query($dbconn, $cmd);
	
	if (!$res) { 
		error_log(pg_last_error());
	}
}

function isLocked($retry_attempt, $llin_dtm, $threshold)
{
	$stat = 1;
	
	//Maximum 6 login attempts
	if((int)$retry_attempt < (int)$threshold || $threshold == 0){
		$stat = 0;
	} else {
		//Account locked, wait 30 minutes
		$date1 = new DateTime($llin_dtm);
		$date1->format('U'); 
		$date2 = new DateTime("now");
		$date2->format('U'); 
		
		$interval = $date1->diff($date2);
		
		$days = $interval->days;
		$hours = $interval->h;
		$minutes = $interval->i;
		
		error_log($_SERVER['REMOTE_ADDR']." last_login_time: $days:$hours:$minutes");
		
		if($days >= 1) {
			$stat = 0;
		}
		
		if($hours >= 1) {
			$stat = 0;
		}
		
		if($minutes >= 30){
			$stat = 0;
		}
	}
	return $stat;
}

function getOldPasswords($userid)
{
	global $dbconn;
	
	$cmd = "select password,old_pwd1,old_pwd2 from user_list where userid='".pg_escape_string($userid)."';";
	$res = pg_query($dbconn, $cmd);
	$row = pg_fetch_row($res, 0);
	
	return $row;
}

function getLdapInfo($l_id)
{
	global $dbconn;
	
	$sqlcmd = "select * from ldapserver where l_id='".pg_escape_string($l_id)."';";
	$row = getSQLresult($dbconn, $sqlcmd);
	
	if(is_string($row)){
		error_log("ERR: Failed to retrieve ldap info for lid: ".$l_id);
	} else {
		return $row[0];
	}
}
?>
