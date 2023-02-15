<?php
include("lib/commonFunc.php");
$range = @$_REQUEST["range"];
$dept = @$_REQUEST["dept"];
$mode = @$_REQUEST["mode"];
switch($mode) {
    case "getMonth":
        $sqlcmd = "SELECT department FROM department_list WHERE department_id = '".dbSafe($dept)."'";
        $result = pg_query($dbconn,$sqlcmd);
        if(!$result) {
            error_log("error getting department name at invoice_lib.php");
        } else {
            if ($row = pg_fetch_array($result)) {
                $txt = "";
                
                $currentmonth = (int)date("n");
                $currentyr = date("y");
                for ($i = 1 ; $i <= 3 ; $i++) {
                    $t = date("F Y", mktime(0,0,0,$currentmonth - $i,1,$currentyr));
                    $z = date("Ym", mktime(0,0,0,$currentmonth - $i,1,$currentyr));
                    $sqlcmd2 = "SELECT filename FROM invoice WHERE departmentid = '".dbSafe($dept)."' AND for_ym = '$z'";
                    $result2 = pg_query($dbconn,$sqlcmd2);
                    if(!$result2) {
                        error_log("Error on getting invoice at invoice_lib.php");
                    } else {
                        $txt .= "\n\r<tr><td><b><u>$t</u></b><br>Detail: Invoice for {$row[0]} - $t</td><td>";
                        if ($row2 = pg_fetch_array($result2)) {
                            // $txt .= "<button class='btn btn-info' data-value='{$row2[0]}'>View PDF</button></td></tr>";
                            $txt .= "<a href='invoices/{$row2[0]}'>View PDF</a>";
                        } else {
                            $txt .= "Data Not Available";
                        }
                    }
                }
                echo $txt;
            }
        }

    break;
    case "getDept":
        $sqlcmd = "SELECT department_id,department FROM department_list ORDER BY 2";
        $result = pg_query($dbconn,$sqlcmd);
        if(!$result) {
            error_log("error getting department list at @invoice_lib.php");
        } else {
            $txt = "";
            while ($row = pg_fetch_array($result)) {
                $txt .= "<option value='".$row[0]."'>".$row[1]."</option>";
            }
            echo $txt;
        }
    break;
    default:
        echo "Unknown Request";
    }
?>