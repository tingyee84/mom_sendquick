<?php
require_once('lib/commonFunc.php');
$mode = @$_REQUEST["mode"];
$dept = @$_SESSION["department"]; // long id

function lblcont($lbl,$content) {
    return "\n<tr><th width='250px'>$lbl</th><td width='150px'>$content</td></tr>";
}
function sqldaterange($datefrom,$dateto) {
    $temp = urldecode($datefrom);
    $datefrom = date("Y-m-d H:i:s",mktime(0,0,0,substr($temp,3,2),substr($temp,0,2),substr($temp,6)));

    $temp = urldecode($dateto);
    $dateto = date("Y-m-d H:i:s",mktime(23,59,59,substr($temp,3,2),substr($temp,0,2),substr($temp,6)));

    return "received_dtm >= '$datefrom' and received_dtm <= '$dateto'";
}

switch($mode) {
    case "summary":
        $datefrom = isset($_REQUEST["datefrom"]) ? filter_input(INPUT_POST,'datefrom') : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
        $dateto = isset($_REQUEST["dateto"]) ? filter_input(INPUT_POST,'dateto') : date("d/m/Y");
        $range = sqldaterange($datefrom,$dateto);

        if ($dept != 0) {
            $sqlcmd = "SELECT count(*) as c FROM incoming_logs WHERE $range AND department = '".dbSafe($dept)."'";
            $result = pg_query($dbconn,$sqlcmd);

            if (!$result) {
                echo json_encode(Array("error"));
            } else {
                $txt = "";
                if ($row = pg_fetch_array($result)) {
                    $txt .= lblcont("Total Incoming Message",$row[0]);
                }
                echo $txt;
            }
        } else {
            $sqlcmd = "SELECT count(*) as c FROM incoming_logs WHERE $range";
            $result = pg_query($dbconn,$sqlcmd);

            if (!$result) {
                echo json_encode(Array("error"));
            } else {
                $txt = "";
                if ($row = pg_fetch_array($result)) {
                    $txt .= lblcont("Total Incoming Message",$row[0]);
                }
                echo $txt;
            }
        }
    break;
    case "listmessage":
        $datefrom = isset($_REQUEST["datefrom"]) ? filter_input(INPUT_POST,'datefrom') : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
        $dateto = isset($_REQUEST["dateto"]) ? filter_input(INPUT_POST,'dateto') : date("d/m/Y");
        $range = sqldaterange($datefrom,$dateto);

        if ($dept != 0) {
            $sqlcmd = "SELECT incoming_id, mobile_numb, message, to_char(received_dtm,'yyyy-mm-dd HH24:MI') as aaa, matched_keyword FROM incoming_logs WHERE $range AND department = '".dbSafe($dept)."' ORDER BY 3 DESC";
            $result = pg_query($dbconn,$sqlcmd);
            if (!$result) {
                echo json_encode(Array("error"));
            } else {
                $data = array();
                while ($row = pg_fetch_array($result)) {
                    array_push($data,Array(
                        "datetime" => $row[3],
                        "mobile_numb" => $row[1],
                        "message" => $row[2],
                        "keyword"=> $row[4]
                    ));
                }
                echo json_encode(Array("data"=>$data));
            }
        } else {
            $sqlcmd = "SELECT incoming_id, mobile_numb, message, to_char(received_dtm,'yyyy-mm-dd HH24:MI') as aaa, matched_keyword,department_list.department FROM incoming_logs LEFT JOIN department_list ON department_list.department_id = incoming_logs.department WHERE $range ORDER BY 3 DESC";
            $result = pg_query($dbconn,$sqlcmd);
            if (!$result) {
                echo json_encode(Array("error".$sqlcmd));
            } else {
                $data = array();
                while ($row = pg_fetch_array($result)) {
                    array_push($data,Array(
                        "datetime" => $row[3],
                        "mobile_numb" => $row[1],
                        "message" => $row[2],
                        "dept"=> $row[5],
                        "keyword"=> $row[4]
                    ));
                }
                echo json_encode(Array("data"=>$data));
            }
        }
    break;
}
?>