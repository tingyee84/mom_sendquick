<?php
require_once('lib/commonFunc.php');

$id = @$_REQUEST['id'];
$mode = @$_REQUEST['mode'];
$userid = strtolower($_SESSION['userid']);
// $id_of_user = getApiAcctId($serviceid);
// $id_of_user = getUserID($userid);
// $department = $_SESSION['department'];
$dept = @$_REQUEST['api_dept'];
$appntype = @$_REQUEST['api_appntype'];
$statusurl = @$_REQUEST['api_statusurl'];
$pwd = @$_REQUEST['api_password'];
$repwd = @$_REQUEST['api_reenter_password'];
$serviceid = @$_REQUEST['api_serviceid'];
$agencyid = @$_REQUEST['api_agencyid'];
$name = @$_REQUEST['api_name'];
$quota = @$_REQUEST['api_quota'];
$clientid = @$_REQUEST['api_clientid'];
$sftp_status  = @$_REQUEST['sftp_subscribe'] == 0 ? 0 : $_REQUEST['sftp_swt'];
// $keyword = @$_REQUEST['api_keyword'];
// $keywordurl = @$_REQUEST['api_keyword_url'];

switch ($mode){
    case "listApiAccts":
        listApiAccts($userid);
        break;
    case "addApiAcct":
        // addApiAcct($userid, $name, $agencyid, $serviceid, $pwd, $statusurl, $dept, $clientid, $appntype, $quota, $repwd, $keyword, $keywordurl);
        addApiAcct($userid, $name, $agencyid, $serviceid, $pwd, $statusurl, $dept, $clientid, $appntype, $quota, $sftp_status, $repwd);
        break;
    case "editApiAcct":
        editApiAcct($id);
        break;
    case "saveApiAcct":
        // saveApiAcct($pwd, $repwd, $name, $agencyid, $statusurl, $dept, $appntype, $quota, $id, $keyword, $keywordurl);
        saveApiAcct($pwd, $repwd, $name, $agencyid, $statusurl, $dept, $appntype, $quota, $sftp_status, $id);
        break;
    case "deleteApiAcct":
        deleteApiAcct($userid, $id);
        break;
    case "listDepts":
        listDepts($userid);
        break;
    case "listAppnTypes":
        listAppnTypes($userid);
        break;
    case "listApplications":
        listApplications();
        break;
    default:
        die("Invalid Command");
}

function listApplications()
{
	global $dbconn, $lang;
	$data = array();

	// $msgstr = GetLanguage("lib_address_book",$lang);
	// $db_err = (string)$msgstr->db_err;

    // $sqlcmd = "select department_id, department from department_list where created_by='".dbSafe($userid)."' and access_type='0'";
    // $sqlcmd = "select serviceid, name from appn_list where appn_type = '3'";
    $sqlcmd = "select a.serviceid as serviceid, a.name as name from appn_list a inner join department_list d on a.dept = d.department_id and a.appn_type = '3'";
    // $result = 
	$result = pg_query($dbconn, $sqlcmd);
	if(!$result)
	{
		error_log("listApplications: ".$db_err." (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
		echo "Database Error";
	}
	else
	{
		while($row = pg_fetch_assoc($result)) {
			$data[] = $row;
		}
		echo json_encode($data);
	}
}

function listDepts($userid)
{
	global $dbconn, $lang;
	$data = array();

	$msgstr = GetLanguage("lib_address_book",$lang);
	$db_err = (string)$msgstr->db_err;

    // $sqlcmd = "select department_id, department from department_list where created_by='".dbSafe($userid)."' and access_type='0'";
    $sqlcmd = "select department_id, department from department_list";
    // $result = 
	$result = pg_query($dbconn, $sqlcmd);
	if(!$result)
	{
		echo "Database Error";
        error_log("listDepts: ".$db_err." (" .dbSafe($sqlcmd). ") -- " .dbSafe(pg_last_error($dbconn)));
	}
	else
	{
		while($row = pg_fetch_assoc($result)) {
			$data[] = $row;
		}
		echo json_encode($data);
	}
}

function listApiAccts($userid){
    global $dbconn, $lang;
    $result_array = array();

	// $msgstr = GetLanguage("lib_address_book",$lang);
    // $listAddressBook_msg1 = (string)$msgstr->listAddressBook_msg1;
	// $db_err = (string)$msgstr->db_err;
    
    $sqlcmd = "select a.name, a.agencyid, a.serviceid, a.clientid, a.appn_type, a.url, a.quota, d.department as dept, sftp_status from appn_list a inner join department_list d on a.dept = d.department_id" ;
    
    $result = pg_query($dbconn, $sqlcmd);

    for ($i=1; $row = pg_fetch_array($result); $i++){
        $appn_type = "";
        if($row['appn_type'] == 1){
            $appn_type = 'One Way';
        }else{
            $appn_type = 'Two Way';
        }

        $url = "";
        if(isset($row['url'])){
            $url = htmlspecialchars($row['url']);
        }
        if ($row['sftp_status'] == 0) {
            $ftpstatus = "Not Subscribed";
        } else if ($row['sftp_status'] == 1) {
            $ftpstatus = "Subscribed";
        } else if ($row['sftp_status'] == 2) {
            $ftpstatus = "Service Disabled";
        }
        array_push($result_array, Array(
            '<a href="#myApiAccts" data-bs-toggle="modal" data-id="'.$row['serviceid'].'">'.
            htmlspecialchars($row['name']).'<i class="fa fa-pencil-square-o fa-fw"></i></a>',
            htmlspecialchars($row['agencyid']),
            htmlspecialchars($row['serviceid']),
            htmlspecialchars($row['clientid']),
            $appn_type,            
            htmlspecialchars($row['dept']),
            $url,
            $row['quota'],
            $ftpstatus,
            '<input type="checkbox" name="no" value="'.$row['serviceid'].'">'
        ));        
    }

    echo json_encode(Array("data"=>$result_array));
}

function addApiAcct($userid, $name, $agencyid, $serviceid, $pwd, $url, $dept, $clientid, $appntype, $quota, $sftp_status, $repwd){
    global $dbconn, $lang, $sftp_path;
    $data = array();
    $x = GetLanguage("api_list",$lang);
    // $msgstr = GetLanguage("lib_address_book",$lang);
	// $addAddressBook_msg1 = (string)$msgstr->addAddressBook_msg1;
	// $addAddressBook_msg2 = (string)$msgstr->addAddressBook_msg2;
	// $addAddressBook_msg3 = (string)$msgstr->addAddressBook_msg3;
	// $contact_str = (string)$msgstr->contact;
    // $db_err = (string)$msgstr->db_err;

    if(trim($pwd) != trim($repwd)){
        $data['flag'] = 0;
		$data['status'] = "The passwords don't match";
		$data['field'] = "api_password";
        echo json_encode($data);
        die;
    }else if(!txvalidator($name,TX_STRING,"SPACE")){
        $data['flag'] = 0;
		$data['status'] = (string)$x->invalid_api_name;
		$data['field'] = "api_name";
        echo json_encode($data);
        die;
    }else if(!validateSize($x->api_name,$name,"NAME")){
        $data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "api_name";
        echo json_encode($data);
        die;
    }else if(!txvalidator($agencyid,TX_STRING)){
        $data['flag'] = 0;
		$data['status'] = (string)$x->invalid_api_agencyid;
		$data['field'] = "api_agencyid";
        echo json_encode($data);
        die;
    }else if(!validateSize($x->api_agencyid,$agencyid,"AID")){
        $data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "api_agencyid";
        echo json_encode($data);
        die;
    }else if(!txvalidator($serviceid,TX_STRING)){
        $data['flag'] = 0;
		$data['status'] = (string)$x->invalid_api_serviceid;
		$data['field'] = "api_serviceid";
        echo json_encode($data);
        die;
    }else if(!validateSize($x->api_serviceid,$serviceid,"SID")){
        $data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "api_serviceid";
        echo json_encode($data);
        die;
    }else if(!txvalidator($pwd,TX_STRING,"ALL")){
        $data['flag'] = 0;
		$data['status'] = (string)$x->invalid_api_password;
		$data['field'] = "api_password";
        echo json_encode($data);
        die;
    }else if(!validateSize($x->api_password,$pwd,"PWD")){
        $data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "api_password";
        echo json_encode($data);
        die;
    }else if(!txvalidator($clientid,TX_STRING)){
        $data['flag'] = 0;
		$data['status'] = (string)$x->invalid_api_clientid;
		$data['field'] = "api_clientid";
        echo json_encode($data);
        die;
    }else if(!validateSize($x->api_clientid,$clientid,"ID")){
        $data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "api_clientid";
        echo json_encode($data);
        die;
    }else if(!txvalidator($url,TX_URL)){
        $data['flag'] = 0;
		$data['status'] = (string)$x->invalid_api_statusurl;
		$data['field'] = "api_statusurl";
        echo json_encode($data);
        die;
    }else if(!txvalidator($quota,TX_INTEGER)){
        $data['flag'] = 0;
		$data['status'] = (string)$x->invalid_api_quota;
		$data['field'] = "api_quota";
        echo json_encode($data);
        die;
    }
    
    $result_array = array();
    $name = trim($name);
    $lower_name = strtolower($name);
    $lower_serviceid = strtolower($serviceid);
    $lower_clientid = strtolower($clientid);
    $agencyid = trim($agencyid);
    $serviceid = trim($serviceid);
    $pwd = trim($pwd);
    $dept = trim($dept);
    $clientid = trim($clientid);

    // hash and base64 password
    $hashed_pwd = getBase64EncHash($pwd);

    // service
    $service = "";

    $query_sql = "select name from appn_list where lower(name) = $1 ";
    $query_row = getSQLresultParams($dbconn, $query_sql, array($lower_name));

    $query_sql2 = "select serviceid from appn_list where lower(serviceid) = $1 ";
    $query_row2 = getSQLresultParams($dbconn, $query_sql2, array($lower_serviceid));

    $query_sql3 = "select clientid from appn_list where lower(clientid) = $1 ";
    $query_row3 = getSQLresultParams($dbconn, $query_sql3, array($lower_clientid));

    // $query_sql4 = "select keyword from mom_sms_response where keyword = $1 ";
    // $query_row4 = getSQLresultParams($dbconn, $query_sql4, array($keyword));
    
    if(is_string($query_row) || is_string($query_row2) || is_string($query_row3)){
        $data['flag'] = 2;
		$data['status'] = "Database Error";       
        error_log("addApiAcct: ".$db_err." (".dbSafe($query_sql).") -- ".dbSafe(pg_last_error($dbconn)));
    }else{
        if(!empty($query_row)){
            $data['flag'] = 0;
		    $data['status'] ="Name already exists";
            $data['field'] = "api_name";
        }else if(!empty($query_row2)){
            $data['flag'] = 0;
            $data['status'] = "Service ID already exists";
            $data['field'] = "api_serviceid";
        }else if(!empty($query_row3)){
            $data['flag'] = 0;
            $data['status'] = "Client ID already exists";
            $data['field'] = "api_clientid";
        }else{
            $new_access_token = generateAccessToken();

            $sqlcmd = "insert into appn_list (agencyid,serviceid,password,name,dept,appn_type,url,clientid,access_token,service, quota, sftp_status) values ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12) ";
            
            $sqlcmdstr = "insert into appn_list (agencyid,serviceid,password,name,dept,appn_type,url,clientid,access_token,service,quota,sftp_status) values ".
            "('".dbSafe($agencyid)."','".dbSafe($serviceid)."','".dbSafe($hashed_pwd)."','".
            dbSafe($name)."','".dbSafe($dept)."',".$appntype.",'".dbSafe($url)."','".
            dbSafe($clientid)."','".dbSafe($new_access_token)."','".dbSafe($service)."','".$quota."',".dbSafe($sftp_status).")";

            $row = doSQLcmdParams($dbconn, $sqlcmd, array($agencyid, $serviceid, $hashed_pwd, $name, $dept, $appntype, $url, $clientid, $new_access_token, $service,$quota,$sftp_status), $sqlcmdstr);

            // if($row != 0){
            //     $sqlcmdkeyword = "insert into mom_sms_response (keyword, descr, cby, department, type, clientid, url, in_use_status, serviceid) values ($1, $2, $3, $4, $5, $6, $7, $8, $9) ";

            //     $sqlcmdkeywordstr = "insert into mom_sms_response (keyword, descr, cby, department, type, clientid, url, in_use_status, serviceid) values ".
            //     "('".dbSafe($keyword)."','".dbSafe($keyword)."','".dbSafe($userid)."','".
            //     dbSafe($dept)."','".dbSafe("1")."','".dbSafe($clientid)."','".
            //     dbSafe($keywordurl)."','".dbSafe("yes")."','".dbSafe($serviceid)."')";
    
            //     $row2 = doSQLcmdParams($dbconn, $sqlcmdkeyword, array($keyword, $keyword, $userid, $dept, "1", $clientid, $keywordurl, "yes",$serviceid), $sqlcmdkeywordstr);
            // }            
            
            if ($sftp_status == 1 || $sftp_status == 2) {
                // TODO if 1 check department has service active. change sftp_status before insert query
                $path = $sftp_path.getDepartmentName($dept);
                if (!file_exists($path)) {
                    if (mkdir ($path)) {
                        mkdir ($path."/upload");
                        mkdir ($path."/download");
                        mkdir ($path."/report");
                        mkdir ($path."/outbox");
                    } else {
                        // echo "SFTPFOLDERCREATEFAILED";
                    }
                }
            }
            insertAuditTrail("$name is created.");
            // NEWAPICREATESUCCESS
            $data['flag'] = 1;
            $data['status'] = "New API has been successfully created.";
        }
    }
    echo json_encode($data);
}

function getBase64EncHash($pwd){
    return base64_encode(hash('sha256', $pwd));
}

function generateAccessToken(){
    $length = 30; 
    return str_shuffle(substr(str_repeat(md5(mt_rand()), 2+$length/32), 0, $length));
}

function editApiAcct($serviceid){
    global $dbconn, $lang;

    // $msgstr = GetLanguage("lib_address_book",$lang);
	// $editAddressBook_msg1 = (string)$msgstr->editAddressBook_msg1;
	// $editAddressBook_msg2 = (string)$msgstr->editAddressBook_msg2;
    // $db_err = (string)$msgstr->db_err;
    // $newhashedpwd = $newpwd; 
    // $lower_name = strtolower($name);
    
    $result_array = array();

    $sqlcmd = "select * from appn_list where serviceid = $1";

    $row = getSQLresultParams($dbconn, $sqlcmd, array($serviceid));

    if(is_string($row)){
        echo "error";
    }else{
        if(empty($row)){
            echo "error";
        }else{
            $result_array['name'] = $row[0]['name'];
            $result_array['agencyid'] = $row[0]['agencyid'];
            $result_array['serviceid'] = $row[0]['serviceid'];
            $result_array['status_url'] = $row[0]['url'];
            $clientid = $row[0]['clientid'];
            $result_array['clientid'] = $clientid;
            $result_array['quota'] = $row[0]['quota'];
            $result_array['dept'] = $row[0]['dept'];
            $result_array['appn_type'] = $row[0]['appn_type'];
            $result_array['access_token'] = $row[0]['access_token'];
            $result_array['sftp_status'] = $row[0]['sftp_status'];
            // echo json_encode($result_array);

            $sqlcmd = "select keyword, url from mom_sms_response where clientid = $1 and type = $2 ";
            $row2 = getSQLresultParams($dbconn, $sqlcmd, array($clientid, "1"));

            if(!empty($row2)){
                $result_array['keyword'] = $row2[0]['keyword'];
                $result_array['url'] = $row2[0]['url'];
            }else{
                $result_array['keyword'] = "";
                $result_array['url'] = "";
            }

            echo json_encode($result_array);
        }
    }

}


function saveApiAcct($pwd, $repwd, $name, $agencyid, $url, $dept, $type, $quota, $sftp_status, $serviceid){
    global $dbconn, $lang, $sftp_path;
    $data = array();
    $x = GetLanguage("api_list",$lang);
    // $msgstr = GetLanguage("lib_address_book",$lang);
	// $saveAddressBook_msg1 = (string)$msgstr->saveAddressBook_msg1;//Changes Made to Contact
	// $saveAddressBook_msg2 = (string)$msgstr->saveAddressBook_msg2;//Successfully Saved!
    // $saveAddressBook_msg3 = (string)$msgstr->saveAddressBook_msg3;//Unsuccessfull!
    
    $result_array = array();
    $name = trim($name);
    $lower_name = strtolower($name);

    // TODO if 1 check department has service active.  change sftp_status
    $sqlupdate = "update appn_list set agencyid = '" .dbSafe($agencyid). "', dept = '" .dbSafe($dept). "', url = '".$url."', quota = " .$quota . ", sftp_status = ".dbSafe($sftp_status);

    // $sqlupdatekeyword = "update mom_sms_response set keyword = '".dbSafe($keyword)."', url = '".dbSafe($keywordurl)."' where serviceid = '".dbSafe($serviceid)."'";

    if(isset($pwd) && isset($repwd) && !empty($pwd)){
        if($pwd != $repwd){
            $data['flag'] = 0;
		    $data['status'] = "The passwords don't match";
		    $data['field'] = "api_password";
            echo json_encode($data);
            die;
        }else if(!txvalidator($pwd,TX_STRING,"ALL")){
            $data['flag'] = 0;
            $data['status'] = (string)$x->invalid_api_password;
            $data['field'] = "api_password";
            echo json_encode($data);
            die;
        }else if(!validateSize($x->api_password,$pwd,"PWD")){
            $data['flag'] = 0;
            $data['status'] = (string)getValidateSizeMsg();
            $data['field'] = "api_password";
            echo json_encode($data);
            die;
        }else{
            $hashed_pwd = getBase64EncHash($pwd);
            $sqlupdate .= ", password = '".dbSafe($hashed_pwd)."'";
        }
    }

    if(!txvalidator($name,TX_STRING,"SPACE")){        
        $data['flag'] = 0;
		$data['status'] = (string)$x->invalid_api_name;
		$data['field'] = "api_name";
        echo json_encode($data);
        die;
    }else if(!validateSize($x->api_name,$name,"NAME")){
        $data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "api_name";
        echo json_encode($data);
        die;
    }else if(!txvalidator($agencyid,TX_STRING)){
        $data['flag'] = 0;
		$data['status'] = (string)$x->invalid_api_agencyid;
		$data['field'] = "api_agencyid";
        echo json_encode($data);
        die;
    }else if(!validateSize($x->api_agencyid,$agencyid,"AID")){
        $data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "api_agencyid";
        echo json_encode($data);
        die;
    }else if(!txvalidator($url,TX_URL)){
        $data['flag'] = 0;
		$data['status'] = (string)$x->invalid_api_statusurl;
		$data['field'] = "api_statusurl";
        echo json_encode($data);
        die;
    }else if(!txvalidator($quota,TX_INTEGER)){
        $data['flag'] = 0;
		$data['status'] = (string)$x->invalid_api_quota;
		$data['field'] = "api_quota";
        echo json_encode($data);
        die;
    }


    $query_sql = "select name from appn_list where lower(name) = $1 and serviceid <> $2";
    $query_row = getSQLresultParams($dbconn, $query_sql, array($lower_name, $serviceid));

    if(is_string($query_row)){
        $data['flag'] = 2;
		$data['status'] = $db_err;
        error_log("saveApiAcct: ".$db_err." (".dbSafe($query_sql).") -- ".dbSafe(pg_last_error($dbconn)));
    }else{
        if(!empty($query_row)){
            $data['flag'] = 0;
            $data['status'] = "Name already exists";
            $data['field'] = "api_name";
        }else{
            $sqlupdate .= ", name = '" .dbSafe($name). "' ";
            $sqlupdate .= " where serviceid = '" .dbSafe($serviceid). "'";

            $row = doSQLcmd($dbconn, $sqlupdate);
            if ($sftp_status == 1 || $sftp_status == 2) {
                $path = $sftp_path.getDepartmentName($dept);
                if (!file_exists($path)) {
                    if (mkdir ($path)) {
                        mkdir ($path."/upload");
                        mkdir ($path."/download");
                        mkdir ($path."/report");
                        mkdir ($path."/outbox");
                        // SFTPFOLDERCREATESUCCESS
                        $data['flag'] = 1;
                        $data['status'] = "SFTP has been created.";
                    } else
                    // SFTPFOLDERCREATEFAILED
                    $data['flag'] = 2;
                    $data['status'] = "Detail is updated but failed to create folder.";
                }
            }else{
                //Successfully Updated.
                $data['flag'] = 1;
                $data['status'] = "Successfully Updated.";
            }           
            insertAuditTrail("$lower_name is updated");
        }
        echo json_encode($data);
    }

    // $query_sql2 = "select keyword from mom_sms_response where lower(keyword) = $1 and clientid <> $2";
    // $query_sql2 = "select keyword from mom_sms_response where keyword = $1 and serviceid <> $2";
    // $query_row2 = getSQLresultParams($dbconn, $query_sql2, array($keyword, $serviceid));

    // if(is_string($query_row2)){
    //     echo $db_err." (".dbSafe($query_sql2).") -- ".dbSafe(pg_last_error($dbconn));
    // }else{
    //     if(!empty($query_row2)){
    //         echo "Keyword is already being used by another user or application";
    //     }else{
    //         $row2 = doSQLcmd($dbconn, $sqlupdatekeyword);
    //     }
    // }
    
    // $sqlcmd = "update appn_list set name = $1, dept = $2, appn_type = $3, status_url = $4,".
    //         "clientid = $5 where serviceid = $6 ";

    // $sqlcmdstr = "update appn_list set name = '".dbSafe($lower_name)."', dept = '".dbSafe($dept)."', appn_type = ".$appntype.", status_url = '".dbSafe($statusurl)."', clientid = '".dbSafe($clientid)."' where serviceid = '".dbSafe($serviceid)."'";

    // $row = doSQLcmdParams($dbconn, $sqlcmd, array($lower_name, $dept, $appntype, $statusurl, $clientid,$serviceid), $sqlcmdstr);

}

function deleteApiAcct($userid, $serviceid){
    global $dbconn,$sftp_path;
    // check if exist
    $sqlcmd = "SELECT department from appn_list left join department_list on dept = department_id WHERE serviceid = '".dbSafe($serviceid)."'";

    $result = pg_query($dbconn,$sqlcmd);
    if ($row = pg_fetch_array($result)) {
        $sqlcmd = "delete from appn_list where serviceid = $1";
        $sqlcmdstr = "delete from appn_list where serviceid = '".dbSafe($serviceid)."'";

        $res = doSQLcmdParams($dbconn, $sqlcmd, array($serviceid), $sqlcmdstr);

        if(!empty($res)){
            echo "Database Error";
            error_log("deleteApiAcct: Database Error: " . $res);
        }else{
            $sqlcmdkeyword = "delete from mom_sms_response where serviceid = $1";
            $sqlcmdstrkeyword = "delete from mom_sms_response where serviceid = '".dbSafe($serviceid)."'";

            $res2 = doSQLcmdParams($dbconn, $sqlcmdkeyword, array($serviceid), $sqlcmdstrkeyword);

            $tgtfolder = $sftp_path.$row["department"]."/report/";
            $arr = scandir($tgtfolder,1);
            foreach($arr as $file) {
                if ($file != "." && $file != "..") {
                    if(strpos($file,$serviceid) === 0) {
                        rename($tgtfolder.$file,$tgtfolder."delete_".$file);
                    }
                }
            }

            $tgtfolder = $sftp_path.$row["department"]."/download/";
            $arr = scandir($tgtfolder,1);
            foreach($arr as $file) {
                if ($file != "." && $file != "..") {
                    if(strpos($file,$serviceid) === 0) {
                        rename($tgtfolder.$file,$tgtfolder."delete_".$file);
                    }
                }
            }

            echo "DELETEAPISUCCESS:$serviceid";
            insertAuditTrail("$serviceid has been deleted");

        }
    } else {
        echo "$serviceid not found";
    }
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

function doSQLcmdParams($dbconn, $sqlcmd, $args_array, $sqlcmdstr)
{
    global $system_server_mode;
    
    $result = pg_query_params($dbconn, $sqlcmd, $args_array);

	if(!$result)
	{
		error_log($sqlcmd. " -- " .pg_last_error($dbconn));
		return 0;
	}

	if( $system_server_mode != 1 ){ #Not standalone system
		UpdateDBSync($dbconn, $sqlcmdstr);
	}

	return pg_affected_rows($result);
}

?>