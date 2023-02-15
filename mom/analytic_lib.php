<?php
require_once('lib/commonFunc.php');
$range = @$_REQUEST['range'];

function sqldaterange($datefrom,$dateto) {
    $temp = urldecode($datefrom);
    $datefrom = date("Y-m-d H:i:s",mktime(0,0,0,substr($temp,3,2),substr($temp,0,2),substr($temp,6)));

    $temp = urldecode($dateto);
    $dateto = date("Y-m-d H:i:s",mktime(23,59,59,substr($temp,3,2),substr($temp,0,2),substr($temp,6)));

    return "created_dtm >= '$datefrom' and created_dtm <= '$dateto'";
}
function foo($month) {
    global $dbconn;
    if(isUserAdmin($_SESSION["userid"])) {
        $limit = date("Y-m-d 00:00:00",mktime(0,0,0,$month,1,date("Y")));
        $limit1 = date("Y-m-d 00:00:00",mktime(0,0,0,$month+1,1,date("Y")));
        $month1 = date("Ym",mktime(0,0,0,$month,1,date("Y")));
        $sqlcmd = "SELECT department_list.department,
                    sum(CASE WHEN message_status IN ('Y','R') THEN cast(totalsms as integer) ELSE 0 END) sumyr,
                    sum(CASE WHEN message_status IN ('F','U') THEN cast(totalsms as integer) ELSE 0 END) sumF,
                    to_char(outgoing_logs.created_dtm,'YYYYMM') as ym
                    FROM outgoing_logs,department_list
                    WHERE outgoing_logs.department = department_list.department_id AND outgoing_logs.created_dtm >= '$limit' AND outgoing_logs.created_dtm < '$limit1' GROUP BY department_id, ym ORDER BY sumyr DESC";
        $result = pg_query($dbconn,$sqlcmd);
        if (!$result) {
        } else {
            $txt = "";
            $array = Array();

            while ($row = pg_fetch_array($result)) {
                $array[$row[0]]["name"] = $row[0];
                $array[$row[0]][$row[3]]["sumyr"] = $row[1];
                $array[$row[0]][$row[3]]["sumf"] = $row[2];
            }
            $i = 1;
            foreach($array as $bu) {
                $txt .= "\n<tr><td>$i</td><td>".$bu["name"]."</td>";
                if (isset($bu[$month1])) {
                    $txt .= "<td>".($bu[$month1]["sumyr"]+$bu[$month1]["sumf"])."</td><td>".$bu[$month1]["sumyr"]."</td><td>".$bu[$month1]["sumf"]."</td>";
                } else {
                    $txt .= "<td>0</td><td>0</td><td>0</td>";
                }
                $i++;
            }
            return strlen($txt) > 0 ? $txt : "<td colspan='5'>No Data</td>";
        }
    }
}
switch($_GET["mode"]) {
    case "top10user":
        // within department bu admin can see
        $currmonth = date("Y-m-d 00:00:00",mktime(0,0,0,date("n"),1,date("Y")));
        $txt_ym = "DATE_TRUNC('month',created_dtm) = '$currmonth'";
        $sqlcmd = "SELECT sent_by, sum(CASE WHEN message_status IN ('Y','R') THEN cast(totalsms as integer) ELSE 0 END) sumyr, sum(CASE WHEN message_status IN ('F','U') THEN cast(totalsms as integer) ELSE 0 END) sumF FROM outgoing_logs WHERE department = '{$_SESSION["department"]}' AND $txt_ym GROUP BY sent_by,totalsms ORDER BY sumyr DESC LIMIT 10";
        $result = pg_query($dbconn,$sqlcmd);
        if (!$result) {
            // error
        } else {
            $txt = "";
            $i = 1;
            while ($row = pg_fetch_array($result)) {
                $txt .= "<tr><td>$i<td>".$row["sent_by"]."</td><td>".($row["sumyr"]+$row["sumf"])."</td><td>".$row["sumyr"]."</td><td>".$row["sumf"]."</td></tr>";
                $i++;
            }
            echo $txt;
        }
    break;
    case "top10user_lastmonth":
        // within department bu admin can see
        $currmonth = date("Y-m-d 00:00:00",mktime(0,0,0,date("n")-1,1,date("Y")));
        $txt_ym = "DATE_TRUNC('month',created_dtm) = '$currmonth'";
        $sqlcmd = "SELECT sent_by, sum(CASE WHEN message_status IN ('Y','R') THEN cast(totalsms as integer) ELSE 0 END) sumyr, sum(CASE WHEN message_status IN ('F','U') THEN cast(totalsms as integer) ELSE 0 END) sumF FROM outgoing_logs WHERE department = '{$_SESSION["department"]}' AND $txt_ym GROUP BY sent_by,totalsms ORDER BY sumyr DESC LIMIT 10";
        $result = pg_query($dbconn,$sqlcmd);
        if (!$result) {
            // echo $sqlcmd;
        } else {
            $txt = "";
            $i = 1;
            while ($row = pg_fetch_array($result)) {
                $txt .= "<tr><td>$i<td>".$row["sent_by"]."</td><td>".($row["sumyr"]+$row["sumf"])."</td><td>".$row["sumyr"]."</td><td>".$row["sumf"]."</td></tr>";
                $i++;
            }
            echo $txt;
        }
    break;
    case "top10user_mom":
        // mom can see all users
        $currmonth = date("Y-m-d H:i:s",mktime(0,0,0,date("n"),1,date("Y")));
        $txt_ym = "AND DATE_TRUNC('month',outgoing_logs.created_dtm) = '$currmonth'";
        $sqlcmd = "SELECT sent_by, department_list.department, sum(CASE WHEN message_status IN ('Y','R') THEN cast(totalsms as integer) ELSE 0 END) sumyr, sum(CASE WHEN message_status IN ('F','U') THEN cast(totalsms as integer) ELSE 0 END) sumF FROM outgoing_logs,department_list WHERE outgoing_logs.department = department_list.department_id $txt_ym GROUP BY sent_by,department_id ORDER BY sumyr DESC LIMIT 10";
        $result = pg_query($dbconn,$sqlcmd);
        if (!$result) {
            // error
            // echo $sqlcmd;
        } else {
            $txt = "";
            $i = 1;
            while ($row = pg_fetch_array($result)) {
                $txt .= "<tr><td>$i<td>".$row["sent_by"]."(<b>".$row["department"]."</b>)</td><td>".($row["sumyr"]+$row["sumf"])."</td><td>".$row["sumyr"]."</td><td>".$row["sumf"]."</td></tr>";
                $i++;
            }
            echo $txt;
        }
    break;
    case "top10user_mom_lastmonth":
        // mom can see all users
        $currmonth = date("Y-m-d H:i:s",mktime(0,0,0,date("n")-1,1,date("Y")));
        $txt_ym = "AND DATE_TRUNC('month',outgoing_logs.created_dtm) = '$currmonth'";
        $sqlcmd = "SELECT sent_by, department_list.department, sum(CASE WHEN message_status IN ('Y','R') THEN cast(totalsms as integer) ELSE 0 END) sumyr, sum(CASE WHEN message_status IN ('F','U') THEN cast(totalsms as integer) ELSE 0 END) sumF FROM outgoing_logs,department_list WHERE outgoing_logs.department = department_list.department_id $txt_ym GROUP BY sent_by,department_id ORDER BY sumyr DESC";
        $result = pg_query($dbconn,$sqlcmd);
        if (!$result) {
            // error
            echo $sqlcmd;
        } else {
            $txt = "";
            $i = 1;
            while ($row = pg_fetch_array($result)) {
                $txt .= "<tr><td>$i<td>".$row["sent_by"]."(<b>".$row["department"]."</b>)</td><td>".($row["sumyr"]+$row["sumf"])."</td><td>".$row["sumyr"]."</td><td>".$row["sumf"]."</td></tr>";
                $i++;
            }
            echo $txt;
        }
    break;
    case "top10bu_0month":
        if(isUserAdmin($_SESSION["userid"])) {
            echo foo(date("n"));
        }
    break;
    case "top10bu_1month":
        if(isUserAdmin($_SESSION["userid"])) {
            echo foo(date("n")-1);
        }
    break;
    case "top10bu_2month":
        if(isUserAdmin($_SESSION["userid"])) {
            echo foo(date("n")-2);
        }
    break;
    case "top10bu_3month":
        if(isUserAdmin($_SESSION["userid"])) {
            echo foo(date("n")-3);
        }
    break;
    default:
        // invalid command
    break;
}
?>