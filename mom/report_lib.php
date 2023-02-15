<?php
require_once('lib/commonFunc.php');

// begin parameters that needs dbsafe
$datefrom = @$_REQUEST["datefrom"];
$dateto = @$_REQUEST["dateto"];
// end parameters check
$mode = @$_REQUEST["mode"];
$user = @$_REQUEST["user"];
$id = @$_REQUEST["id"];
$dept = @$_REQUEST["dept"];
$access = explode(",",$_SESSION["access_string"]);
$x = GetLanguage("report",$lang);
function lblcont($lbl,$content,$style = "") {
    return "\n<tr".($style != "" ? " class='table-$style'" : "")."><th width='300px'>$lbl</th><td width='150px'>$content</td></tr>";
}
function stat_msg($status) {
    switch ($status) {
        case "U":
        case "F":
            //return "<span class='label label-danger'>Failed</span>";
            return "Failed";
        break;
        case "D":
            return "Deleted";
            //return "<span class='label label-info'>Deleted</span>";
        break;
        case "Y":
            return "Sent";
            //return "<span class='label label-success'>Completed</span>";
        break;
        case "R":
            return "Delivered";
            // return "<span class='label label-success'>Delivered</span>";
        break;
        case "P":
            return "Pending";
            // return "<span class='label label-warning'>Pending</span>";
        break;
    }
}
function sqldaterange($datefrom,$dateto) {
    $temp = urldecode($datefrom);
    $_SESSION["report_datefrom"] = $temp;
    $datefrom = date("Y-m-d H:i:s",mktime(0,0,0,substr($temp,3,2),substr($temp,0,2),substr($temp,6)));

    $temp = urldecode($dateto);
    $_SESSION["report_dateto"] = $temp;
    $dateto = date("Y-m-d H:i:s",mktime(23,59,59,substr($temp,3,2),substr($temp,0,2),substr($temp,6)));

    return "created_dtm >= '$datefrom' and created_dtm <= '$dateto'";
}

if (in_array(59,$access) || in_array(61,$access) || in_array(62,$access)) {
    switch ($mode) {
        case "detail_mim":
            // list of msgs, MIM
            // ensure check user first
            $range = "";
            $datefrom = isset($_REQUEST["datefrom"]) ? dbSafe($_REQUEST["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
            $dateto = isset($_REQUEST["dateto"]) ? dbSafe($_REQUEST["dateto"]) : date("d/m/Y");
            $range = sqldaterange($datefrom,$dateto);
            
            // try check permission before show up
            $sqlcmd = "SELECT trackid,mobile_numb,message_status,campaign_name,sent_by,to_char(created_dtm,'dd/mm/yy HH24:MI') as aaa, message,bot_message_status_id,bot_types_id,outgoing_id,file_location FROM outgoing_logs LEFT JOIN campaign_mgnt ON campaign_mgnt.campaign_id = outgoing_logs.campaign_id WHERE $range AND sent_by = '".dbSafe($user)."' AND message_status in ('Y','R','F','U') AND bot_message_status_id > 0 AND is_deleted = FALSE ORDER by 5 DESC";
            $result = pg_query($dbconn,$sqlcmd);
            if (!$result) {
                //$data["errcode"] = 2;
                echo json_encode(Array("error"));
            } else {
                $data = array();
                while ($row = pg_fetch_array($result)) {
                    array_push($data,Array(
                        "created" => $row[5],
                        "campaign_name" => $row[3],
                        "mobile_numb" => $row[1],
                        "message" => str_replace("\n","<br>",$row[6]),
                        "stat" => stat_msg($row[2]),
                        "trackid" => $row[0],
                        "mim" => ($row[7] > 0 ? $row[8] : 0),
                        "msgid" => $row[9],
                        "file_location" => ($row[10] != null ? $row[10] : "")
                    ));
                }
                echo json_encode(Array("data"=>$data));
            }
        break;
        
        case "detail":
            // list of msgs
            // ensure check user first
            $datefrom = isset($_REQUEST["datefrom"]) ? dbSafe($_REQUEST["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
            $dateto = isset($_REQUEST["dateto"]) ? dbSafe($_REQUEST["dateto"]) : date("d/m/Y");
            $range = sqldaterange($datefrom,$dateto);

            // try check permission before show up
            $sqlcmd = "SELECT trackid,mobile_numb,message_status,campaign_name,sent_by,to_char(created_dtm,'dd/mm/yy HH24:MI') as aaa, totalsms,message,bot_message_status_id,bot_types_id,outgoing_id,file_location FROM outgoing_logs LEFT JOIN campaign_mgnt ON campaign_mgnt.campaign_id = outgoing_logs.campaign_id WHERE $range AND sent_by = '".dbSafe($user)."' AND message_status in ('Y','R','F','U') AND bot_message_status_id = 0 AND is_deleted = FALSE ORDER by 5 DESC";
            $result = pg_query($dbconn,$sqlcmd);
            if (!$result) {
                //$data["errcode"] = 2;
                echo json_encode(Array("error"));
            } else {
                $data = array();
                while ($row = pg_fetch_array($result)) {
                    array_push($data,Array(
                        "created" => $row[5],
                        "campaign_name" => $row[3],
                        "mobile_numb" => $row[1],
                        "message" => str_replace("\n","<br>",$row[7]),
                        "stat" => stat_msg($row[2]),
                        "totalsms" => $row[6],
                        "trackid" => $row[0],
                        "mim" => ($row[8] > 0 ? $row[9] : 0),
                        "msgid" => $row[10],
                        "file_location" => ($row[11] != null ? $row[11] : "")
                    ));
                }
                echo json_encode(Array("data"=>$data));
            }
        break;
        case "msgdetail":
            // single message detail with information
            // ensure check user first
            $sqlcmd = "SELECT trackid,mobile_numb,totalsms,message_status,sent_by,message,to_char(sent_dtm,'YYYY-mm-dd HH24:MI:SS'),to_char(completed_dtm,'YYYY-mm-dd HH24:MI:SS'),outgoing_logs.campaign_id,campaign_name,modem_label FROM outgoing_logs LEFT JOIN campaign_mgnt ON campaign_mgnt.campaign_id = outgoing_logs.campaign_id WHERE trackid = '".dbSafe($id)."' AND is_deleted = FALSE";
            $result = pg_query($dbconn,$sqlcmd);
            if (!$result) {
                echo json_encode(Array("error"));
            } else {
                $data = array();
                $row = pg_fetch_array($result);
                $data["trackid"] =  $row[0];
                $data["recipient"] = $row[1];
                $data["totalsms"] = $row[2];
                $data["status"] = stat_msg($row[3]);
                $data["sent_by"] = $row[4];
                $data["message"] = str_replace("\n","<br>",$row[5]);
                $data["sent_dtm"] = $row[6];
                $data["completed_dtm"] = $row[7];
                $data["campaign_id"] = $row[8];
                $data["campaign"] = $row[9];
                $data["callerid"] = $row[10];
                echo json_encode($data);
            }
            
        break;
        case "dept_summary":
            // list of dept, only viewed by MOMadmin
            $sqlcmd = "SELECT department_id,department_list.department, quota_left, count(userid) FROM department_list,user_list WHERE user_list.department = department_list.department_id GROUP BY 1 ORDER BY 2";
            $result = pg_query($dbconn,$sqlcmd);
            if(!$result) {
                echo json_encode(Array("error"));
                error_log("error occur during retrieve the data from department_list at report_lib.php: ".$sqlcmd);
            } else {
                $data = array();
                while($row = pg_fetch_array($result)) {
                    $datefrom = isset($_GET["datefrom"]) ? dbSafe($_GET["datefrom"]) : date("d/m/Y");
                    // error_log("xxxxxxxxxx datefrom: " . $datefrom);
                    $dateto = isset($_GET["dateto"]) ? dbSafe($_GET["dateto"]) : date("d/m/Y");
                    $range = sqldaterange($datefrom,$dateto);
                    $sqlcmd2 = "SELECT message_status,sum(cast(totalsms as integer)) FROM outgoing_logs WHERE $range AND department = '{$row[0]}' AND message_status in ('Y','R','F','U') AND is_deleted = FALSE GROUP BY message_status";
                    $result2 = pg_query($dbconn,$sqlcmd2);
                    
                    $y = 0;
                    $r = 0;
                    $f = 0;
                    $u = 0;
                    if(!$result2) {
                        echo json_encode(Array("error"));
                        error_log("error occured during retreving the data from department_list lv2 at report_lib.php : ".$sqlcmd2);
                    } else {
                        while ($row2 = pg_fetch_array($result2)) {
                            switch($row2[0]) {
                                case "Y":
                                    $y = $row2[1];
                                break;
                                case "R":
                                    $r = $row2[1];
                                break;
                                case "F":
                                    $f = $row2[1];
                                break;
                                case "U":
                                    $f = $row2[1];
                                break;
                            }
                        }
                    }
                    array_push($data,Array("<a href='report.php?view=users&dept={$row[0]}'>{$row[1]}</a>",$row[3],$y+$r+$f+$u,$row[2],$y+$r,$f+$u));
                }
                echo json_encode(Array("data"=>$data));        
            }
        break;
        case "users_list_mim":
            // list of users
            if (in_array(61,$access) || (in_array(62,$access) && $_SESSION["department"] == $dept)) {
                $sqlcmd = "SELECT id,user_list.userid,quota_left,quota_limit,topup_frequency,unlimited_quota FROM user_list,quota_mnt WHERE user_list.userid = quota_mnt.userid AND user_list.department = '".dbSafe($dept)."' ORDER BY 2";
                $result = pg_query($dbconn,$sqlcmd);
                $topup = "";
                if(!$result) {
                    echo json_encode(Array("error2"));
                    error_log("error occured during retreving the data from user_list at report_lib.php : ".$sqlcmd);
                } else {
                    $data = array();                    
                    while($row = pg_fetch_array($result)) {

                        $datefrom = isset($_GET["datefrom"]) ? dbSafe($_GET["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
                        $dateto = isset($_GET["dateto"]) ? dbSafe($_GET["dateto"]) : date("d/m/Y");
                        $range = sqldaterange($datefrom,$dateto);

                        $topup = $row[5] == 1 ? "<i>".$x->unlimited."</i>" : ($row[4] == 3 ? "".$x->quotadisabled."" : ($row[3].($row[4] == "1" ? "<i>/Weekily</i>" : "<i>/Monthly</i>")));
                        $sqlcmd2 = "SELECT message_status,sum(cast(totalsms as integer)), case when length(mim_tpl_id) > 0 then 'hastpl' else 'notpl' END istplornot FROM outgoing_logs WHERE $range AND sent_by = '{$row[1]}' AND message_status IN ('Y','R','F','U')  AND bot_message_status_id > 0 AND is_deleted = FALSE GROUP BY 1,3";
                        $result2 = pg_query($dbconn,$sqlcmd2);
                    
                        $y = 0;
                        $r = 0;
                        $f = 0;
                        if(!$result2) {
                            echo json_encode(Array("error3".$sqlcmd2));
                            error_log("error occured during retreving the data from user_list lv2 at report_lib.php : ".$sqlcmd2);
                        } else {
                            $mim_result = array();
                            $mim_result["hastpl"] = array();
                            $mim_result["hastpl"]["Y"] = 0;
                            $mim_result["hastpl"]["R"] = 0;
                            $mim_result["hastpl"]["F"] = 0;
                            $mim_result["hastpl"]["U"] = 0;

                            $mim_result["notpl"] = array();
                            $mim_result["notpl"]["Y"] = 0;
                            $mim_result["notpl"]["R"] = 0;
                            $mim_result["notpl"]["F"] = 0;
                            $mim_result["notpl"]["U"] = 0;
                            while ($row2 = pg_fetch_array($result2)) {
                                $mim_result[$row2[2]][$row2[0]] = $row2[1];
                            }
                        }
                        $tyr = $mim_result["hastpl"]["Y"] + $mim_result["hastpl"]["R"];
                        $tf  = $mim_result["hastpl"]["F"] + $mim_result["hastpl"]["U"];

                        $nyr = $mim_result["notpl"]["Y"] + $mim_result["notpl"]["R"]; 
                        $nf  = $mim_result["notpl"]["F"] + $mim_result["notpl"]["U"];

                        $sqlcmd3 = "SELECT count(distinct (case when substring(mobile_numb from 1 for 1) = '+' then substring(mobile_numb from 2) else mobile_numb END)) u_mb FROM outgoing_logs WHERE $range AND sent_by = '{$row[1]}' AND message_status in ('Y','R') AND bot_message_status_id > 0";
                        $result3 = getSQLresult($dbconn,$sqlcmd3);
                        $mau = 0;
                        if (!is_string($result3)) {
                            $mau = $result3[0]["u_mb"];
                        }
                        array_push($data,Array("<a href='report.php?view=user&user={$row[1]}'>{$row[1]}</a>",$tyr+$tf,$nyr+$nf,$row[2],$tyr,$tf,$nyr,$nf,$topup));
                    }
                    echo json_encode(Array("data"=>$data));
                }
            } else {
                echo json_encode(Array("error1"));
            }
        break;
        case "users_list":
            // list of users
            if (in_array(61,$access) || (in_array(62,$access) && $_SESSION["department"] == $dept)) {
                $sqlcmd = "SELECT id,user_list.userid,quota_left,quota_limit,topup_frequency,unlimited_quota FROM user_list,quota_mnt WHERE user_list.userid = quota_mnt.userid AND user_list.department = '".dbSafe($dept)."' ORDER BY 2";
                $result = pg_query($dbconn,$sqlcmd);
                $topup = "";
                if(!$result) {
                    echo json_encode(Array("error2"));
                    error_log("error occured during retreving the data from user_list at report_lib.php : ".$sqlcmd);
                } else {
                    $data = array();                    
                    while($row = pg_fetch_array($result)) {

                        $datefrom = isset($_GET["datefrom"]) ? dbSafe($_GET["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
                        $dateto = isset($_GET["dateto"]) ? dbSafe($_GET["dateto"]) : date("d/m/Y");
                        $range = sqldaterange($datefrom,$dateto);

                        $topup = $row[5] == 1 ? "<i>".$x->unlimited."</i>" : ($row[4] == 3 ? "".$x->quotadisabled."" : ($row[3].($row[4] == "1" ? "<i>/Weekily</i>" : "<i>/Monthly</i>")));
                        $sqlcmd2 = "SELECT message_status,sum(cast(totalsms as integer)) FROM outgoing_logs WHERE $range AND sent_by = '{$row[1]}' AND message_status IN ('Y','R','F','U') AND bot_message_status_id = 0 AND is_deleted = FALSE GROUP BY 1";
                        $result2 = pg_query($dbconn,$sqlcmd2);
                    
                        $y = 0;
                        $r = 0;
                        $f = 0;
                        $u = 0;
                        if(!$result2) {
                            echo json_encode(Array("error3"));
                            error_log("error occured during retreving the data from user_list lv2 at report_lib.php : ".$sqlcmd2);
                        } else {
                            while ($row2 = pg_fetch_array($result2)) {
                                switch($row2[0]) {
                                    case "Y":
                                        $y = $row2[1];
                                    break;
                                    case "R":
                                        $r = $row2[1];
                                    break;
                                    case "F":
                                        $f = $row2[1];
                                    break;
                                    case "U":
                                        $u = $row2[1];
                                    break;
                                }
                            }
                        }
                        array_push($data,Array("<a href='report.php?view=user&user={$row[1]}'>{$row[1]}</a>",$y+$r+$f+$u,$row[2],$y+$r,$f+$u,$topup));
                    }
                    echo json_encode(Array("data"=>$data));
                }
            } else {
                echo json_encode(Array("error1"));
            }
        break;
        case "d_summary_mim":
            // if mom, then check if it is has 61
            // if bu check $_dept is same $dept
            $sqlcmd = "SELECT department_id,department_list.department, quota_left, count(userid) FROM department_list,user_list WHERE user_list.department = department_list.department_id";
            if (in_array(61,$access) && isset($_GET["dept"])) {
                $sqlcmd .= " AND user_list.department = '".dbSafe($dept)."' GROUP BY 1";
            } else if (in_array(62,$access)) {
                $sqlcmd .= " AND user_list.department = '{$_SESSION["department"]}' GROUP BY 1";
            }
            $result = pg_query($dbconn,$sqlcmd);
            if (!$result) {
                echo json_encode(Array("error2".$sqlcmd));
                error_log("error occured during retreving the data from department_list at report_lib.php : ".$sqlcmd);
            } else {
                while($row = pg_fetch_array($result)) {
                    $datefrom = isset($_GET["datefrom"]) ? dbSafe($_GET["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
                    $dateto = isset($_GET["dateto"]) ? dbSafe($_GET["dateto"]) : date("d/m/Y");
                    $range = sqldaterange($datefrom,$dateto);
                    $sqlcmd2 = "SELECT message_status,sum(cast(totalsms as integer)), case when length(mim_tpl_id) > 0 then 'hastpl' else 'notpl' END istplornot FROM outgoing_logs WHERE $range AND department = '{$row[0]}' AND message_status in ('Y','R','F','U') AND bot_message_status_id > 0 AND is_deleted = FALSE GROUP BY 1,3";
                    $result2 = pg_query($dbconn,$sqlcmd2);
                    
                    $y = 0; $r = 0; $f = 0;;
                    if(!$result2) {
                        echo "<center>Nothing to display</center>";
                        error_log("error occured during retreving the data from department_list lv2 at report_lib.php : ".$sqlcmd2);
                    } else {
                        $mim_result = array();
                        $mim_result["hastpl"] = array();
                        $mim_result["hastpl"]["Y"] = 0;
                        $mim_result["hastpl"]["R"] = 0;
                        $mim_result["hastpl"]["F"] = 0;
                        $mim_result["hastpl"]["U"] = 0;
                        // $mim_result["hastpl"]["mau"] = 0;

                        $mim_result["notpl"] = array();
                        $mim_result["notpl"]["Y"] = 0;
                        $mim_result["notpl"]["R"] = 0;
                        $mim_result["notpl"]["F"] = 0;
                        $mim_result["notpl"]["U"] = 0;
                        // $mim_result["notpl"]["mau"] = 0;

                        while ($row2 = pg_fetch_array($result2)) {
                            $mim_result[$row2[2]][$row2[0]] = $row2[1];
                        }
                        
                        $sqlcmd3 = "SELECT count(distinct (case when substring(mobile_numb from 1 for 1) = '+' then substring(mobile_numb from 2) else mobile_numb END)) u_mb FROM outgoing_logs WHERE $range AND department = '{$row[0]}' AND message_status in ('Y','R') AND bot_message_status_id > 0 AND is_deleted = FALSE";
                        $result3 = getSQLresult($dbconn,$sqlcmd3);
                        $mau = 0;
                        if (!is_string($result3)) {
                            $mau = $result3[0]["u_mb"];
                        }

                        $txt  = lblcont($x->dept,$row[1]);
                        $txt .= lblcont($x->totaluser,$row[3]);
                        $txt .= lblcont($x->quota,$row[2]);
                        $txt .= lblcont("MIM Template Message - Total Sent", $mim_result["hastpl"]["Y"] + $mim_result["hastpl"]["R"] + $mim_result["hastpl"]["F"] + $mim_result["hastpl"]["U"],"info");
                        $txt .= lblcont("MIM Template Message - Success", $mim_result["hastpl"]["Y"] + $mim_result["hastpl"]["R"],"success");
                        $txt .= lblcont("MIM Template Message - Failed",$mim_result["hastpl"]["F"] + $mim_result["hastpl"]["U"],"danger");

                        $txt .= lblcont("MIM Normal Message - Total Sent", $mim_result["notpl"]["Y"] + $mim_result["notpl"]["R"] + $mim_result["notpl"]["F"] + $mim_result["notpl"]["U"],"info");
                        $txt .= lblcont("MIM Normal Message - Success",$mim_result["notpl"]["Y"] + $mim_result["notpl"]["R"],"success");
                        $txt .= lblcont("MIM Normal Message - Failed",$mim_result["notpl"]["F"] + $mim_result["notpl"]["U"],"danger");
                        $txt .= lblcont("MAU",$mau);
                        
                        echo strlen($txt) > 0 ? $txt : "<center>Nothing to display</center>";
                    }
                }
            }
        break;
        case "d_summary":
            // if mom, then check if it is has 61
            // if bu check $_dept is same $dept
            $sqlcmd = "SELECT department_id,department_list.department, quota_left, count(userid) FROM department_list,user_list WHERE user_list.department = department_list.department_id";
            if (in_array(61,$access) && isset($_GET["dept"])) {
                $sqlcmd .= " AND user_list.department = '".dbSafe($dept)."' GROUP BY 1";
            } else if (in_array(62,$access)) {
                $sqlcmd .= " AND user_list.department = '{$_SESSION["department"]}' GROUP BY 1";
            }
            $result = pg_query($dbconn,$sqlcmd);
            if (!$result) {
                echo json_encode(Array("error2".$sqlcmd));
                error_log("error occured during retreving the data from department_list at report_lib.php : ".$sqlcmd);
            } else {
                while($row = pg_fetch_array($result)) {
                    $datefrom = isset($_GET["datefrom"]) ? dbSafe($_GET["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
                    $dateto = isset($_GET["dateto"]) ? dbSafe($_GET["dateto"]) : date("d/m/Y");
                    $range = sqldaterange($datefrom,$dateto);
                    $sqlcmd2 = "SELECT message_status,sum(cast(totalsms as integer)) FROM outgoing_logs WHERE $range AND department = '{$row[0]}' AND message_status in ('Y','R','F','U') AND bot_message_status_id = 0 AND is_deleted = FALSE GROUP BY 1";
                    $result2 = pg_query($dbconn,$sqlcmd2);
                    
                    $y = 0; $r = 0; $f = 0;$u = 0;
                    if(!$result2) {
                        echo "<center>Nothing to display</center>";
                        error_log("error occured during retreving the data from department_list lv2 at report_lib.php : ".$sqlcmd2);
                    } else {
                        while ($row2 = pg_fetch_array($result2)) {
                            switch($row2[0]) {
                                case "Y":
                                    $y = $row2[1];
                                break;
                                case "R":
                                    $r = $row2[1];
                                break;
                                case "F":
                                    $f = $row2[1];
                                break;
                                case "U":
                                    $u = $row2[1];
                                break;
                            }
                        }

                        $txt  = lblcont($x->dept,$row[1]);
                        $txt .= lblcont($x->totaluser,$row[3]);
                        $txt .= lblcont($x->totalsent,$y + $r + $f + $u);
                        $txt .= lblcont($x->quota,$row[2]);
                        $txt .= lblcont($x->delivered,$y + $r);
                        $txt .= lblcont($x->undelivered,$f + $u);
                        echo strlen($txt) > 0 ? $txt : "<center>Nothing to display</center>";
                    }
                }
            }
        break;
        case "u_summary":
            // if bu, then check if it is same dept or not allowed chek this user
            // if self, ensure $_session["userid"] is same as $_GET["user]
            if (in_array(61,$access) || in_array(62,$access) || in_array(59,$access)) {
                $sqlcmd = "SELECT id,user_list.userid,quota_left,quota_limit,topup_frequency,unlimited_quota FROM user_list,quota_mnt WHERE user_list.userid = quota_mnt.userid";
                if (in_array(61,$access) && isset($_GET["user"])) {
                    $sqlcmd .= " AND user_list.userid = '".dbSafe($user)."'";
                } else if (in_array(62,$access) && isset($_GET["user"])) {
                    $sqlcmd .= " AND user_list.userid = '".dbSafe($user)."' AND user_list.department = '".dbSafe($_SESSION["department"])."'";
                } else if (in_array(59,$access)){
                    $sqlcmd .= " AND user_list.id = '{$_SESSION["userid"]}'";
                }
                $result = pg_query($dbconn,$sqlcmd);
                $topup = "";
                if(!$result) {
                    echo json_encode(Array("error2".$sqlcmd));
                    error_log("error occured during retreving the data from user_list at report_lib.php : ".$sqlcmd);
                } else {                  
                    while($row = pg_fetch_array($result)) {
                        $datefrom = isset($_GET["datefrom"]) ? dbSafe($_GET["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
                        $dateto = isset($_GET["dateto"]) ? dbSafe($_GET["dateto"]) : date("d/m/Y");
                        $range = sqldaterange($datefrom,$dateto);

                        $topup = $row[5] == 1 ? "<i>".$x->unlimited."</i>" : ($row[4] == 3 ? "".$x->quotadisabled."" : ($row[3].($row[4] == "1" ? "<i>/Weekily</i>" : "<i>/Monthly</i>")));
                        $sqlcmd2 = "SELECT message_status,sum(cast(totalsms as integer)) FROM outgoing_logs WHERE $range AND sent_by = '{$row[1]}' AND message_status in ('Y','R','F','U') AND bot_message_status_id = 0 AND is_deleted = FALSE GROUP BY 1";
                        $result2 = pg_query($dbconn,$sqlcmd2);

                        $y = 0;
                        $r = 0;
                        $f = 0;
                        $u = 0;
                        if(!$result2) {
                            echo json_encode(Array("error3".$sqlcmd2));
                            error_log("error occured during retreving the data from user_list lv2 at report_lib.php : ".$sqlcmd2);
                        } else {
                            while ($row2 = pg_fetch_array($result2)) {
                                switch($row2[0]) {
                                    case "Y":
                                        $y = $row2[1];
                                    break;
                                    case "R":
                                        $r = $row2[1];
                                    break;
                                    case "F":
                                        $f = $row2[1];
                                    break;
                                    case "U":
                                        $u = $row2[1];
                                    break;
                                }
                            }
                        }

                        $txt  = lblcont($x->user,$row[1]);
                        $txt .= lblcont($x->quota,$row[2]);
                        $txt .= lblcont($x->autorefresh,$topup);
                        $txt .= lblcont($x->totalsent,$f + $y + $r + $u);
                        $txt .= lblcont($x->delivered,$y + $r);
                        $txt .= lblcont($x->undelivered,$f + $u);

                        echo $txt;
                    }
                    
                }
            } else {
                echo json_encode(Array("error: Cannot Load Summary for User"));
            }
        break;

        case "u_summary_mim":
            // if bu, then check if it is same dept or not allowed chek this user
            // if self, ensure $_session["userid"] is same as $_GET["user]
            if (in_array(61,$access) || in_array(62,$access) || in_array(59,$access)) {
                $sqlcmd = "SELECT id,user_list.userid,quota_left,quota_limit,topup_frequency,unlimited_quota FROM user_list,quota_mnt WHERE user_list.userid = quota_mnt.userid";
                if (in_array(61,$access) && isset($_GET["user"])) {
                    $sqlcmd .= " AND user_list.userid = '".dbSafe($user)."'";
                } else if (in_array(62,$access) && isset($_GET["user"])) {
                    $sqlcmd .= " AND user_list.userid = '".dbSafe($user)."' AND user_list.department = '".dbSafe($_SESSION["department"])."'";
                } else if (in_array(59,$access)){
                    $sqlcmd .= " AND user_list.id = '{$_SESSION["userid"]}'";
                }
                $result = pg_query($dbconn,$sqlcmd);
                $topup = "";
                if(!$result) {
                    echo json_encode(Array("error2"));
                    error_log("error occured during retreving the data from user_list at report_lib.php : ".$sqlcmd);
                } else {                  
                    while($row = pg_fetch_array($result)) {
                        $datefrom = isset($_GET["datefrom"]) ? dbSafe($_GET["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
                        $dateto = isset($_GET["dateto"]) ? dbSafe($_GET["dateto"]) : date("d/m/Y");
                        $range = sqldaterange($datefrom,$dateto);

                        $topup = $row[5] == 1 ? "<i>".$x->unlimited."</i>" : ($row[4] == 3 ? "".$x->quotadisabled."" : ($row[3].($row[4] == "1" ? "<i>/Weekily</i>" : "<i>/Monthly</i>")));
                        $sqlcmd2 = "SELECT message_status,sum(cast(totalsms as integer)), case when length(mim_tpl_id) > 0 then 'hastpl' else 'notpl' END istplornot FROM outgoing_logs WHERE $range AND sent_by = '{$row[1]}' AND message_status in ('Y','R','F','U') AND bot_message_status_id > 0 AND is_deleted = FALSE GROUP BY 1,3";
                        $result2 = pg_query($dbconn,$sqlcmd2);
                        // SELECT message_status,sum(cast(totalsms as integer)), case when length(mim_tpl_id) > 0 then 'hastpl' else 'notpl' END istplornot FROM outgoing_logs WHERE message_status in ('Y','R','F') AND bot_message_status_id > 0 GROUP BY 1,3
                        // zin: yes. we need to have mim tab under report and need to show summary mim template message - success and failed and mim normal message - success and failed
                        $y = 0;
                        $r = 0;
                        $f = 0;
                        if(!$result2) {
                            echo json_encode(Array("error3"));
                            error_log("error occured during retreving the data from user_list lv2 at report_lib.php : ".$sqlcmd2);
                        } else {
                            $mim_result = array();
                            $mim_result["hastpl"] = array();
                            $mim_result["hastpl"]["Y"] = 0;
                            $mim_result["hastpl"]["R"] = 0;
                            $mim_result["hastpl"]["F"] = 0;
                            $mim_result["hastpl"]["U"] = 0;
                            // $mim_result["hastpl"]["mau"] = 0;

                            $mim_result["notpl"]["Y"] = 0;
                            $mim_result["notpl"]["R"] = 0;
                            $mim_result["notpl"]["F"] = 0;
                            $mim_result["notpl"]["U"] = 0;
                            // $mim_result["notpl"]["mau"] = 0;

                            $mim_result["notpl"] = array();
                            while ($row2 = pg_fetch_array($result2)) {
                                $mim_result[$row2[2]][$row2[0]] = $row2[1];
                            }
                        }

                        $sqlcmd3 = "SELECT count(distinct (case when substring(mobile_numb from 1 for 1) = '+' then substring(mobile_numb from 2) else mobile_numb END)) u_mb FROM outgoing_logs WHERE $range AND sent_by = '{$row[1]}' AND message_status in ('Y','R') AND bot_message_status_id > 0 AND is_deleted = FALSE";
                        $result3 = getSQLresult($dbconn,$sqlcmd3);
                        $mau = 0;
                        if (!is_string($result3)) {
                            $mau = $result3[0]["u_mb"];
                        }
                        $txt  = lblcont($x->user,$row[1]);
                        $txt .= lblcont($x->quota,$row[2]);
                        $txt .= lblcont($x->autorefresh,$topup);
                        $txt .= lblcont("MIM Template Message - Total Sent", $mim_result["hastpl"]["Y"] + $mim_result["hastpl"]["R"] + $mim_result["hastpl"]["F"] + $mim_result["hastpl"]["U"],"info");
                        $txt .= lblcont("MIM Template Message - Success", $mim_result["hastpl"]["Y"] + $mim_result["hastpl"]["R"],"success");
                        $txt .= lblcont("MIM Template Message - Failed",$mim_result["hastpl"]["F"] + $mim_result["hastpl"]["U"],"danger");

                        $txt .= lblcont("MIM Normal Message - Total Sent", $mim_result["notpl"]["Y"] + $mim_result["notpl"]["R"] + $mim_result["notpl"]["F"] + $mim_result["notpl"]["U"],"info");
                        $txt .= lblcont("MIM Normal Message - Success",$mim_result["notpl"]["Y"] + $mim_result["notpl"]["R"],"success");
                        $txt .= lblcont("MIM Normal Message - Failed",$mim_result["notpl"]["F"] + $mim_result["notpl"]["U"],"danger");
                        $txt .= lblcont("MAU",$mau);

                        echo $txt;
                    }
                    
                }
            } else {
                echo json_encode(Array("error: Cannot Load Summary for User"));
            }
        break;

        default:
            //$data["errcode"] = 0;
            //$data["errmsg"] = "Invalid Command";
    }
} else {
    echo json_encode(Array("error"));
}
?>