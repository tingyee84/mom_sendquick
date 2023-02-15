<?php
require_once('lib/commonFunc.php');

$mode = @$_REQUEST["mode"];

function sqldaterange($datefrom,$dateto) {
    $temp = urldecode($datefrom);
    $datefrom = date("Y-m-d H:i:s",mktime(0,0,0,substr($temp,3,2),substr($temp,0,2),substr($temp,6)));

    $temp = urldecode($dateto);
    $dateto = date("Y-m-d H:i:s",mktime(23,59,59,substr($temp,3,2),substr($temp,0,2),substr($temp,6)));

    return "appn_outgoing_logs.created_dtm >= '$datefrom' and appn_outgoing_logs.created_dtm <= '$dateto'";
}

function stat_msg($status) {    // check report.php too
    switch ($status) {
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

function lblcont($lbl,$content,$style = "") {
    return "\n<tr".($style != "" ? " class='table-$style'" : "")."><th width='300px'>$lbl</th><td width='150px'>$content</td></tr>";
}
switch($mode) {
    case "listapi":
        // mom level to view
        $range = "";
        $datefrom = isset($_REQUEST["datefrom"]) ? dbSafe($_REQUEST["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
        $dateto = isset($_REQUEST["dateto"]) ? dbSafe($_REQUEST["dateto"]) : date("d/m/Y");
        $range = sqldaterange($datefrom,$dateto);

        $sqlcmd = "SELECT department, department_id, count(serviceid) FROM department_list, appn_list WHERE department_id = dept GROUP BY department_id,department ORDER BY 1";
        $result = pg_query($dbconn,$sqlcmd);
        if (!$result) {
            echo json_encode(Array("error"));
        } else {
            $data = Array();
            while ($row = pg_fetch_array($result)) {
                $sqlcmd2 = "SELECT sum(CASE WHEN message_status in ('Y','R') THEN 1 ELSE 0 END) as countX, sum(CASE WHEN message_status in ('Y','R') THEN cast(totalsms as integer) ELSE 0 END) as countY, sum(CASE WHEN message_status = 'F' THEN cast(totalsms as integer) ELSE 0 END) as countF
                    FROM appn_outgoing_logs LEFT JOIN appn_list on appn_list.clientid = appn_outgoing_logs.clientid
                    WHERE $range AND dept = '{$row[1]}' GROUP BY dept ORDER BY 1";
                $result2 = pg_query($dbconn,$sqlcmd2);
                if (!$result2) {
                    echo json_encode(Array("error"));
                } else {
                    if (pg_num_rows($result2) == 0) {
                       array_push($data, Array($row[0],$row[2],0,0,0,$row[1]));
                    } else {
                        if ($row2 = pg_fetch_array($result2)) {
                            array_push($data, Array($row[0],$row[2],$row2[0],$row2[1],$row2[2],$row[1]));
                        }
                    }
                    
                }
            }
            echo json_encode(Array("data"=>$data));
        }

    break;
    case "listmsg":
        // bu level, mom can view too
        $range = "";
        $datefrom = isset($_REQUEST["datefrom"]) ? dbSafe($_REQUEST["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
        $dateto = isset($_REQUEST["dateto"]) ? dbSafe($_REQUEST["dateto"]) : date("d/m/Y");
        $range = sqldaterange($datefrom,$dateto);
        $dept = isset($_REQUEST["dept"]) ? $_REQUEST["dept"] : $_SESSION["department"];

        $sqlcmd = "SELECT date_trunc('second',appn_outgoing_logs.created_dtm) as aaa, serviceid, mobile_numb,id,message,message_status,totalsms,is_template,template_id FROM appn_outgoing_logs, appn_list WHERE message_status IN ('Y','R','F') AND dept = '".dbSafe($dept)."' AND appn_list.clientid = appn_outgoing_logs.clientid AND $range AND send_mode = 'sms'";
        $result = pg_query($dbconn,$sqlcmd);
        if (!$result) {
            echo json_encode(Array("error"));
        } else {
            $data = Array();
            while ($row = pg_fetch_array($result)) {
                array_push($data, Array($row[0],$row[1],$row[2],$row[4],stat_msg($row[5]),$row[6],$row[7],$row[8]));
            }
            echo json_encode(Array("data"=>$data));
        }
    break;
    case "listmsg_mim":
        // bu level, mom can view too
        $range = "";
        $datefrom = isset($_REQUEST["datefrom"]) ? dbSafe($_REQUEST["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
        $dateto = isset($_REQUEST["dateto"]) ? dbSafe($_REQUEST["dateto"]) : date("d/m/Y");
        $range = sqldaterange($datefrom,$dateto);
        $dept = isset($_REQUEST["dept"]) ? $_REQUEST["dept"] : $_SESSION["department"];

        $sqlcmd = "SELECT date_trunc('second',appn_outgoing_logs.created_dtm) as aaa, serviceid, mobile_numb,id,message,message_status,totalsms,is_template,template_id FROM appn_outgoing_logs, appn_list WHERE message_status IN ('Y','R','F') AND dept = '".dbSafe($dept)."' AND appn_list.clientid = appn_outgoing_logs.clientid AND $range AND send_mode = 'mim'";
        $result = pg_query($dbconn,$sqlcmd);
        if (!$result) {
            echo json_encode(Array("error"));
        } else {
            $data = Array();
            while ($row = pg_fetch_array($result)) {
                array_push($data, Array($row[0],$row[1],$row[2],$row[4],stat_msg($row[5]),$row[6],$row[7],$row[8]));
            }
            echo json_encode(Array("data"=>$data));
        }
    break;
    case "summarymsg":
        $range = "";
        $datefrom = isset($_REQUEST["datefrom"]) ? dbSafe($_REQUEST["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
        $dateto = isset($_REQUEST["dateto"]) ? dbSafe($_REQUEST["dateto"]) : date("d/m/Y");
        $range = sqldaterange($datefrom,$dateto);
        $dept = isset($_REQUEST["dept"]) ? $_REQUEST["dept"] : $_SESSION["department"];

        $sqlcmd = "SELECT count(distinct appn_outgoing_logs.clientid), sum(case when message_status in ('Y','R') then cast(totalsms as integer) else 0 end) as A, sum(case when message_status = 'F' then cast(totalsms as integer) else 0 end) as B, sum(case when message_status in ('Y','R') then 1 else 0 end) as C FROM appn_outgoing_logs, appn_list WHERE appn_outgoing_logs.clientid= appn_list.clientid AND dept = '".dbSafe($dept)."' AND $range and send_mode = 'sms'";
        $result = pg_query($dbconn,$sqlcmd);
        if (!$result) {
            echo json_encode(Array("error".$sqlcmd));
        } else {
            $data = Array();
            $str = "";
            if ($row = pg_fetch_array($result)) {
                $str .= lblcont("Total Service",$row[0]);
                $str .= lblcont("Total SMS Sent",$row[1] + $row[2]);
                $str .= lblcont("Total Message Count",intval($row[3]));
                $str .= lblcont("Total Message Fail",intval($row[2]));
                
            }
            echo $str;
        }
    break;
    case "summarymsg_mim":
        $range = "";
        $datefrom = isset($_REQUEST["datefrom"]) ? dbSafe($_REQUEST["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
        $dateto = isset($_REQUEST["dateto"]) ? dbSafe($_REQUEST["dateto"]) : date("d/m/Y");
        $range = sqldaterange($datefrom,$dateto);
        $dept = isset($_REQUEST["dept"]) ? $_REQUEST["dept"] : $_SESSION["department"];

        $sqlcmd = "SELECT count(distinct appn_outgoing_logs.clientid), sum(case when message_status in ('Y','R') and is_template = '1' then 1 else 0 end) as A, sum(case when message_status = 'F'  and is_template = '1' then 1 else 0 end) as B, sum(case when message_status in ('Y','R')  and is_template != '1' then 1 else 0 end) as C, sum(case when message_status = 'F'  and is_template != '1' then 1 else 0 end) as D FROM appn_outgoing_logs, appn_list WHERE appn_outgoing_logs.clientid= appn_list.clientid AND dept = '".dbSafe($dept)."' AND $range AND send_mode = 'mim'";
        $result = pg_query($dbconn,$sqlcmd);
        if (!$result) {
            echo json_encode(Array("error".$sqlcmd));
        } else {
            $data = Array();
            $str = "";
            if ($row = pg_fetch_array($result)) {
                $str .= lblcont("Total Service",$row[0]);
                $str .= lblcont("MIM Template Message - Total Sent",$row[1]+$row[2],"info");
                $str .= lblcont("MIM Template Message - Success",$row[1],"success");
                $str .= lblcont("MIM Template Message - Failed",$row[2],"danger");
                $str .= lblcont("MIM Normal Message - Total Sent",$row[3]+$row[4],"info");
                $str .= lblcont("MIM Normal Message - Success",$row[3],"success");
                $str .= lblcont("MIM Normal Message - Failed",$row[4],"danger");
                
            }
            echo $str;
        }
    default:
        json_encode(Array("Unknown Command"));
}
?>