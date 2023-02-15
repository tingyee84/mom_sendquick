<?php
require("lib/commonFunc.php");
// TODO please add right so only can be access by allow BU or MOM
$datefrom = @$_REQUEST["datefrom"];
$dateto = @$_REQUEST["dateto"];
$mode = @$_REQUEST["mode"];
$campaignid = filter_input(INPUT_POST,"id");
function sqldaterange($datefrom,$dateto,$columndate) {
    $temp = urldecode($datefrom);
    $datefrom = date("Y-m-d H:i:s",mktime(0,0,0,substr($temp,3,2),substr($temp,0,2),substr($temp,6)));

    $temp = urldecode($dateto);
    $dateto = date("Y-m-d H:i:s",mktime(23,59,59,substr($temp,3,2),substr($temp,0,2),substr($temp,6)));

    return "$columndate >= '$datefrom' and $columndate <= '$dateto'";
}
function lblcont($lbl,$content) {
    return "\n<tr><th width='250px'>$lbl</th><td width='250px'>$content</td></tr>";
}
$tbl_m = "campaign_mgnt";
$tbl_i = "campagin_survey_inbox";
$tbl_o = "campagin_survey_outbox";
switch($mode) {
    case "listcampaigns":
        $range = "";
        $datefrom = isset($_REQUEST["datefrom"]) ? dbSafe($_REQUEST["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
        $dateto = isset($_REQUEST["dateto"]) ? dbSafe($_REQUEST["dateto"]) : date("d/m/Y");
        $range = sqldaterange($datefrom,$dateto,"$tbl_m.cdtm");
        // part main
        $sqlcmd = "SELECT campaign_id, campaign_name, cby FROM $tbl_m WHERE $range and campaign_type = '2'";
        $result = pg_query($dbconn,$sqlcmd);

        if (!$result) {
            echo json_encode(Array("Can't retrieve the result from Database"));
            error_log("Error Retrieving from $tbl_m");
        } else {
            $data = Array();
            while ($row = pg_fetch_array($result)) {
                $in  = 0;
                $out = 0;
                $sqlcmd_o = "SELECT count(id) as totalout FROM $tbl_o WHERE campagin_id = {$row[0]}";
                $result_o = pg_query($dbconn,$sqlcmd_o);
                if ($row_o = pg_fetch_array($result_o)) {
                    //echo $sqlcmd_o ;
                    $out = $row_o[0];
                }
                $sqlcmd_i = "SELECT count(id) as totalin FROM $tbl_i WHERE campagin_id = {$row[0]}";
                $result_i = pg_query($dbconn,$sqlcmd_i);
                if ($row_i = pg_fetch_array($result_i)) {
                    //echo $sqlcmd_i ;
                    $in = $row_i[0];
                }
                array_push($data,Array($row[0],$row[1],$row[2],$out,$in));
            }

        }
        echo json_encode(Array("data"=>$data));
        
    break;
    case "listresponses":
        $range = "";
        $datefrom = isset($_REQUEST["datefrom"]) ? dbSafe($_REQUEST["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
        $dateto = isset($_REQUEST["dateto"]) ? dbSafe($_REQUEST["dateto"]) : date("d/m/Y");
        $range = sqldaterange($datefrom,$dateto,"$tbl_i.cdtm");

        $sqlcmd = "SELECT to_char(cdtm,'dd/mm/yy HH24:MI'),mobile_no,full_msg_received FROM $tbl_i WHERE campagin_id = '".dbSafe($campaignid)."' AND $range";
        $result = pg_query($dbconn,$sqlcmd);
        if (!$result) {
            echo json_encode(Array("can't retrieve the result from table"));
            error_log("Error Retrieve from $tbl_i");
        } else {
            $data = Array();
            while ($row = pg_fetch_array($result)) {
                array_push($data,Array($row[0],$row[1],$row[2]));
            }
            echo json_encode(Array("data"=>$data));
        }
    break;
    case "summaryresponses":
        $range = "";
        $datefrom = isset($_REQUEST["datefrom"]) ? dbSafe($_REQUEST["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
        $dateto = isset($_REQUEST["dateto"]) ? dbSafe($_REQUEST["dateto"]) : date("d/m/Y");
        $range = sqldaterange($datefrom,$dateto,"cdtm");

        $sqlcmd = "SELECT campaign_name FROM $tbl_m WHERE campaign_id = '".dbSafe($campaignid)."'";
        $result = pg_query($dbconn,$sqlcmd);

        if (!$result) {
            echo json_encode(Array("Can't retrieve the result from Database"));
            error_log("Error Retrieving from $tbl_m");
        } else {
            $data = Array();
            if ($row = pg_fetch_array($result)) {
                $in  = 0;
                $out = 0;
                $sqlcmd_o = "SELECT count(id) as totalout FROM $tbl_o WHERE $range and campagin_id = '".dbSafe($campaignid)."'";
                $result_o = pg_query($dbconn,$sqlcmd_o);
                if ($row_o = pg_fetch_array($result_o)) {
                    //echo $sqlcmd_o ;
                    $out = $row_o[0];
                }
                $sqlcmd_i = "SELECT count(id) as totalin FROM $tbl_i WHERE $range and campagin_id = '".dbSafe($campaignid)."'";
                $result_i = pg_query($dbconn,$sqlcmd_i);
                if ($row_i = pg_fetch_array($result_i)) {
                    //echo $sqlcmd_i ;
                    $in = $row_i[0];
                }
                array_push($data,$row[0]);
                array_push($data,$out);
                array_push($data,$in);
                //$txt .= lblcont("Campaign Name",$row[0]);
                //$txt .= lblcont("Total Message Sent Out",$out);
                //$txt .= lblcont("Total Response Message",$in);
                echo json_encode($data);
            } else {
                echo "Data is not available. Please change the date range";
            }

        }

    break;
    case "summarycampaigns":
        $range = "";
        $datefrom = isset($_REQUEST["datefrom"]) ? dbSafe($_REQUEST["datefrom"]) : date("d/m/Y",mktime(0,0,0,date("m"),1,date("Y")));
        $dateto = isset($_REQUEST["dateto"]) ? dbSafe($_REQUEST["dateto"]) : date("d/m/Y");
        $range = sqldaterange($datefrom,$dateto,"$tbl_i.cdtm");

        $sqlcmd = "SELECT campaign_name, count(id) FROM $tbl_m, $tbl_i WHERE $tbl_i.campagin_id = $tbl_m.campaign_id AND $tbl_m.campaign_id = '".dbSafe($campaignid)."' AND $range";
        $result = pg_query($dbconn,$sqlcmd);
        if(!$result) {
            echo json_encode(Array("can't retrieve the result for table for summary".$sqlcmd));
            error_log("Error retrieve from $tbl_i");
        } else {
            $txt = "";
            if ($row = pg_fetch_array($result)) {
                $txt .= lblcont("Campaign Name",$row[0]);
                $txt .= lblcont("Total Message Received",$row[1]);
            }
            echo $txt;
        }
    break;
    default:
        echo json_encode(Array("Unknown Command"));
}
?>