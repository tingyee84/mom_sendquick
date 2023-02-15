<?php
require("./lib/commonFunc.php");
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
require_once("printpdf.php");

// TODO : 2020-08-11 Summary report to view Total WhatsApp Template Message and MAU Message, User Guide on cross check function

// get statistic into the billing
// every department, totalsms, totalmim, totalmim-nottemplate group by date.
// check department and date, if empty, then create; if exist, create
// retrieve the setting from table
// get provided name from user_list
// only leave filename
// manual call / crontab
// insertAuditTrail("Generating Invoice for ...");

// TODO Gather detail
// make 0 value pdf even no data
// pending zin to add column
// first part to get outgoing
// select to_char(created_dtm,'YYYYMM') created_format, sum(case when message_status in ('Y','R') then cast(totalsms as integer) else 0 end) tb, sum(case when bot_types_id > 0 AND mim_tpl_id != null then 1 else 0 end) tc, sum(case when bot_types_id > 0 AND mim_tpl_id = null then 1 else 0 end) td, department from outgoing_logs WHERE created_dtm >= '2020-04-01 00:00:00' group by department, created_format order by 1 desc

// JS Wong said
// sms count like normal
// incoming need as well
// mim count like sms
// mau / mim without template count unique number

// second part to get appn_outgoing


/*for ($i = 1 ; $i <= 3 ; $i++) {
    // only check last 3 months not including current month
    $t = date("F Y", mktime(0,0,0,$currentmonth - $i,1,$currentyr));
    $z = date("Ym", mktime(0,0,0,$currentmonth - $i,1,$currentyr));
}*/
// idea of array is [dept][yyyymm][totalsms/totalmim/totalmim_without_tpl/appn_sms/appn_mim]
// sqlcmd = "SELECT to_char(created_dtm,'YYYYMM') created_format, department, count(DISTINCT CASE WHEN bot_types_id IS NOT NULL then mobile_numb ELSE NULL END) u_mb, sum(CASE WHEN bot_types_id IS NULL then cast(totalsms as integer) else 0 end) totalsms, sum(CASE when bot_types_id > 0 AND mim_tpl_id IS NOT NULL then 1 else 0 end) totalmim, sum(case when bot_types_id > 0 AND mim_tpl_id IS NULL then 1 else 0 end) totalmimnt from outgoing_logs WHERE created_dtm >= '$monthofthree' AND message_status in ('Y','R') group by 2, 1 order by 1 desc";
$sqlcmd = "SELECT * FROM setting";
$temp = getSQLresult($dbconn, $sqlcmd);
$setting = array();
if(!empty($temp) && !is_string($temp)) {
    foreach($temp as $row) {
        $setting[$row["variable"]] = $row["value"];
    }
}
// how long didn't update
if ($setting["specifyym"] == "") {
    $diff = (int)date("ym") - (int)date("ym",strtotime($setting["invoicelastupdate"]));
    // let's check date  $diff > 0
    if ($diff > 0) {
        $currentmonth = (int)date("n");
        $currentyr = date("y");

        $monthofthree = date("Y-m-d H:i:s",mktime(0,0,0,$currentmonth-$diff,1,$currentyr));

        $sqlcmd = "SELECT to_char(created_dtm,'YYYYMM') created_format, department, count(DISTINCT CASE WHEN bot_types_id IS NOT NULL AND (mim_tpl_id IS NULL OR mim_tpl_id = '') THEN mobile_numb ELSE NULL END) u_mb, sum(CASE WHEN bot_types_id IS NULL AND message_status in ('Y','R','U') then cast(totalsms as integer) else 0 end) totalsms, sum(CASE when bot_types_id > 0 AND mim_tpl_id IS NOT NULL AND message_status in ('Y','R') then 1 else 0 end) totalmim FROM outgoing_logs WHERE created_dtm >= '$monthofthree' group by 2, 1 order by 1 desc";
        $get = getSQLresult($dbconn, $sqlcmd);
        $array = array();
        $array["ui"] = array();
        if(!empty($get) && !is_string($get)) {
            foreach($get as $row) {
                $array["ui"][$row['department']][$row['created_format']]['totalsms'] = intval($row['totalsms']);
                $array["ui"][$row['department']][$row['created_format']]['totalmim'] = intval($row['totalmim']);
                $array["ui"][$row['department']][$row['created_format']]['u_mb'] = intval($row['u_mb']);
            }
        } else {
            echo "Failed".$sqlcmd;
        }
        // incoming message for ui user
        // TODO just wait Zin to add column, until then just count
        $sqlcmd = "SELECT to_char(created_dtm,'YYYYMM') created_format , department, COUNT(*) totalin FROM incoming_logs WHERE created_dtm >= '$monthofthree' group by 2, 1 order by 1 desc";
        $get = getSQLresult($dbconn, $sqlcmd);
        if(!empty($get) && !is_string($get)) {
            foreach($get as $row) {
                $array["ui"][$row['department']][$row['created_format']]['totalin'] = intval($row['totalin']);
            }
        }
        
        // var_dump($array["ui"]);
        // echo "---";
        $sqlcmd = "SELECT to_char(a.created_dtm,'YYYYMM') created_format, b.dept, count(DISTINCT CASE WHEN send_mode = 'mim' AND is_template IS NULL then mobile_numb ELSE NULL END) u_mb, sum(CASE when message_status in ('Y','R') then cast(totalsms as integer) else 0 end) totalsms, sum(CASE when send_mode = 'mim' AND is_template IS NOT NULL then 1 else 0 end) totalmim FROM appn_outgoing_logs a LEFT JOIN appn_list b ON a.clientid = b.clientid WHERE created_dtm >= '$monthofthree' AND message_status in ('Y','R') group by 2, 1 order by 1 desc";
        $get = getSQLresult($dbconn, $sqlcmd);
        
        $array["api"] = array();
        if(!empty($get) && !is_string($get)) {
            foreach($get as $row) {
                $array["api"][$row['dept']][$row['created_format']]['totalsms'] = intval($row['totalsms']);
                $array["api"][$row['dept']][$row['created_format']]['totalmim'] = intval($row['totalmim']);
                $array["api"][$row['dept']][$row['created_format']]['u_mb'] = intval($row['u_mb']);
            }
        }
        // incoming message for api user
        $sqlcmd = "SELECT to_char(a.created_dtm,'YYYYMM') created_format, b.dept, sum(CASE WHEN totalsms IS NOT NULL AND send_mode = 'sms' THEN CAST(totalsms as integer) ELSE 0 END) totalin_sms, sum(CASE WHEN totalsms IS NOT NULL AND send_mode = 'mim' THEN 1 ELSE 0 END) totalin_mim FROM appn_incoming_logs a LEFT JOIN appn_list b ON a.clientid = b.clientid  WHERE created_dtm >= '$monthofthree' AND a.mobile_numb SIMILAR TO '\+\d{7,15}' group by 2, 1 order by 1 desc";
        $get = getSQLresult($dbconn, $sqlcmd);
        if(!empty($get) && !is_string($get)) {
            foreach($get as $row) {
                $array["api"][$row['dept']][$row['created_format']]['totalin_sms'] = intval($row['totalin_sms']);
                $array["api"][$row['dept']][$row['created_format']]['totalin_mim'] = intval($row['totalin_mim']);
            }
        }
        // var_dump($array["api"]);
        $userid = 1;
        $sqlcmd = "SELECT department_id,department FROM department_list";
        $get = getSQLresult($dbconn,$sqlcmd);
        $seqno = (int) $setting["invoicenextseq"];
        if(!empty($get) && !is_string($get)) {

            $invoice = new INVOICE();
            foreach($get as $department) {
                if (isset($array["api"][$department["department_id"]]) || isset($array["ui"][$department["department_id"]])) {
                    for ($i = 1 ; $i <= $diff ; $i++) {
                        $z = date("Ym", mktime(0,0,0,$currentmonth - $i,1,$currentyr));
                        $fd = date("M-Y", mktime(0,0,0,$currentmonth - $i,1,$currentyr));
                        $a1 = @$array["ui"][$department["department_id"]][$z];
                        $a2 = @$array["api"][$department["department_id"]][$z];
                        $a1['totalsms'] = isset($a1['totalsms']) ? $a1['totalsms'] : 0;
                        $a1['totalmim'] = isset($a1['totalmim']) ? $a1['totalmim'] : 0;
                        $a1['u_mb'] = isset($a1['u_mb']) ? $a1['u_mb'] : 0;
            
                        $a1['totalin'] = isset($a1['totalin']) ? $a1['totalin'] : 0;
            
                        $a2['totalsms'] = isset($a2['totalsms']) ? $a2['totalsms'] : 0;
                        $a2['totalmim'] = isset($a2['totalmim']) ? $a2['totalmim'] : 0;
                        $a2['u_mb'] = isset($a2['u_mb']) ? $a2['u_mb'] : 0;
                        $a2['totalin_sms'] = isset($a2['totalin_sms']) ? $a2['totalin_sms'] : 0;
                        $a2['totalin_mim'] = isset($a2['totalin_mim']) ? $a2['totalin_mim'] : 0;

                        $str_ui = "{$a1["totalsms"]},{$a1["totalmim"]},{$a1['totalin']},0,{$a1["u_mb"]}";
                        $str_api = "{$a2["totalsms"]},{$a2["totalmim"]},{$a2["totalin_sms"]},{$a2["totalin_mim"]},{$a2["u_mb"]}";
                        $invno = $setting["invoiceprefix"].sprintf("%05d",$seqno);
                        $filename = $setting["invoiceprefix"].sprintf("%05d",$seqno)."_".$department["department"]."_$z.pdf";
                        $sqlcmd2 = "INSERT INTO invoice (departmentid,dept_name,for_ym,invoiceno,created_by,username,created_dtm,totalsms,totalmim,totalsms_in,totalmim_in,mau,totalsmsapi,totalmimapi,totalsmsapi_in,totalmimapi_in,mauapi,filename,gst) VALUES(
                            '{$department["department_id"]}','{$department["department"]}','$z','$invno','$userid','',now(),$str_ui,$str_api,'$filename',{$setting["gst"]}
                        )";
                        $result = pg_query($dbconn,$sqlcmd2);
                        if (pg_affected_rows($result)) {
                            $invoice->detail("Person in charge",$department["department"],$invno,$fd);
                            $invoice->information_gen($a1['totalsms'],$a1['totalin'],$a1['totalmim'],$a1['u_mb']);
                            $invoice->information_api($a2['totalsms'],$a2['totalin_sms']+$a2['totalin_mim'],$a2['totalmim'],$a2['u_mb']);
                            $invoice->outputas($filename);
                            $invoice->print();
                            $seqno++;
                        }
                        // echo $sqlcmd2;
                    }
                } else {
                    // js wong said fill the empty data for BU
                    for ($i = 1 ; $i <= $diff ; $i++) {
                        $str_ui = "0,0,0,0,0";
                        $str_api = "0,0,0,0,0";
                        $z = date("Ym", mktime(0,0,0,$currentmonth - $i,1,$currentyr));
                        $fd = date("M-Y", mktime(0,0,0,$currentmonth - $i,1,$currentyr));
                        $invno = $setting["invoiceprefix"].sprintf("%05d",$seqno);
                        $filename = $setting["invoiceprefix"].sprintf("%05d",$seqno)."_".$department["department"]."_$z.pdf";
                        $sqlcmd2 = "INSERT INTO invoice (departmentid,dept_name,for_ym,invoiceno,created_by,username,created_dtm,totalsms,totalmim,totalsms_in,totalmim_in,mau,totalsmsapi,totalmimapi,totalsmsapi_in,totalmimapi_in,mauapi,filename,gst) VALUES(
                            '{$department["department_id"]}','{$department["department"]}','$z','$invno','$userid','',now(),$str_ui,$str_api,'$filename',{$setting["gst"]}
                        )";
                        $result = pg_query($dbconn,$sqlcmd2);
                        if (pg_affected_rows($result)) {
                            $invoice->detail("Person in charge",$department["department"],$invno,$fd);
                            $invoice->information_gen(0,0,0,0);
                            $invoice->information_api(0,0,0,0);
                            $invoice->outputas($filename);
                            $invoice->print();
                            $seqno++;
                        }
                    }
                }
            }
        }
        $sqlcmd = "UPDATE setting SET value = now() WHERE variable = 'invoicelastupdate'";
        pg_query($dbconn,$sqlcmd);
        $sqlcmd = "UPDATE setting SET value = $seqno WHERE variable = 'invoicenextseq'";
        pg_query($dbconn,$sqlcmd);
    } else {
        // nothing update, if you wish to update, kindly change the invoicelastupdate date in setting table to past at least one month before current, delete the data in invoice table then reload this page. btw, delete the invoice.
        // TODO Create an automatic delete script that old invoice will be deleted cascade when deleting invoice table
    }
} else {
    // specify the year and month oct 23,2020 as JS Wong asked can generate the month he wanted
    $sqlcmd_delete = "DELETE FROM invoice WHERE for_ym = '".$setting["specifyym"]."'";
    pg_query($dbconn,$sqlcmd_delete);

    $yr = (int) substr($setting["specifyym"],0,4);
    $mth = (int) substr($setting["specifyym"],4,2);

    $month_start = date("Y-m-d H:i:s",mktime(0,0,0,$mth,1,$yr));
    $month_end = date("Y-m-d H:i:s",mktime(0,0,0,($mth == 12 ? 1 : $mth+1),1,$yr+($mth == 12 ? 1 : 0)));
    
    $sqlcmd = "SELECT to_char(created_dtm,'YYYYMM') created_format, department, count(DISTINCT CASE WHEN bot_types_id IS NOT NULL AND (mim_tpl_id IS NULL OR mim_tpl_id = '') THEN mobile_numb ELSE NULL END) u_mb, sum(CASE WHEN bot_types_id IS NULL AND message_status in ('Y','R','U') then cast(totalsms as integer) else 0 end) totalsms, sum(CASE when bot_types_id > 0 AND mim_tpl_id IS NOT NULL AND message_status in ('Y','R') then 1 else 0 end) totalmim FROM outgoing_logs WHERE created_dtm >= '$month_start' AND created_dtm < '$month_end' group by 2, 1 order by 1 desc";
    // below should be same as above. so next time simply this code
    $get = getSQLresult($dbconn, $sqlcmd);
    $array = array();
    $array["ui"] = array();
    if(!empty($get) && !is_string($get)) {
        foreach($get as $row) {
            $array["ui"][$row['department']][$row['created_format']]['totalsms'] = intval($row['totalsms']);
            $array["ui"][$row['department']][$row['created_format']]['totalmim'] = intval($row['totalmim']);
            $array["ui"][$row['department']][$row['created_format']]['u_mb'] = intval($row['u_mb']);
        }
    } else {
        echo "Failed".$sqlcmd;
    }
    $sqlcmd = "SELECT to_char(created_dtm,'YYYYMM') created_format , department, COUNT(*) totalin FROM incoming_logs WHERE created_dtm >= '$month_start' AND created_dtm < '$month_end' group by 2, 1 order by 1 desc";
    $get = getSQLresult($dbconn, $sqlcmd);
    if(!empty($get) && !is_string($get)) {
        foreach($get as $row) {
            $array["ui"][$row['department']][$row['created_format']]['totalin'] = intval($row['totalin']);
        }
    }
    
    $sqlcmd = "SELECT to_char(a.created_dtm,'YYYYMM') created_format, b.dept, count(DISTINCT CASE WHEN send_mode = 'mim' AND is_template IS NULL then mobile_numb ELSE NULL END) u_mb, sum(CASE when message_status in ('Y','R') then cast(totalsms as integer) else 0 end) totalsms, sum(CASE when send_mode = 'mim' AND is_template IS NOT NULL then 1 else 0 end) totalmim FROM appn_outgoing_logs a LEFT JOIN appn_list b ON a.clientid = b.clientid WHERE created_dtm >= '$month_start' AND created_dtm < '$month_end' AND message_status in ('Y','R') group by 2, 1 order by 1 desc";
    $get = getSQLresult($dbconn, $sqlcmd);
    
    $array["api"] = array();
    if(!empty($get) && !is_string($get)) {
        foreach($get as $row) {
            $array["api"][$row['dept']][$row['created_format']]['totalsms'] = intval($row['totalsms']);
            $array["api"][$row['dept']][$row['created_format']]['totalmim'] = intval($row['totalmim']);
            $array["api"][$row['dept']][$row['created_format']]['u_mb'] = intval($row['u_mb']);
        }
    }

    $sqlcmd = "SELECT to_char(a.created_dtm,'YYYYMM') created_format, b.dept, sum(CASE WHEN totalsms IS NOT NULL AND send_mode = 'sms' THEN CAST(totalsms as integer) ELSE 0 END) totalin_sms, sum(CASE WHEN totalsms IS NOT NULL AND send_mode = 'mim' THEN 1 ELSE 0 END) totalin_mim FROM appn_incoming_logs a LEFT JOIN appn_list b ON a.clientid = b.clientid WHERE created_dtm >= '$month_start' AND created_dtm < '$month_end' AND a.mobile_numb SIMILAR TO '\+\d{7,15}' group by 2, 1 order by 1 desc";
    $get = getSQLresult($dbconn, $sqlcmd);
    if(!empty($get) && !is_string($get)) {
        foreach($get as $row) {
            $array["api"][$row['dept']][$row['created_format']]['totalin_sms'] = intval($row['totalin_sms']);
            $array["api"][$row['dept']][$row['created_format']]['totalin_mim'] = intval($row['totalin_mim']);
        }
    }

    $userid = 1;
    $sqlcmd = "SELECT department_id,department FROM department_list";
    $get = getSQLresult($dbconn,$sqlcmd);
    $seqno = (int) $setting["invoicenextseq"];
    if(!empty($get) && !is_string($get)) {

        $invoice = new INVOICE();
        foreach($get as $department) {
            if (isset($array["api"][$department["department_id"]]) || isset($array["ui"][$department["department_id"]])) {
                
                    $z = date("Ym", mktime(0,0,0,$mth,1,$yr));
                    $fd = date("M-Y", mktime(0,0,0,$mth,1,$yr));
                    $a1 = @$array["ui"][$department["department_id"]][$z];
                    $a2 = @$array["api"][$department["department_id"]][$z];
                    $a1['totalsms'] = isset($a1['totalsms']) ? $a1['totalsms'] : 0;
                    $a1['totalmim'] = isset($a1['totalmim']) ? $a1['totalmim'] : 0;
                    $a1['u_mb'] = isset($a1['u_mb']) ? $a1['u_mb'] : 0;
        
                    $a1['totalin'] = isset($a1['totalin']) ? $a1['totalin'] : 0;
        
                    $a2['totalsms'] = isset($a2['totalsms']) ? $a2['totalsms'] : 0;
                    $a2['totalmim'] = isset($a2['totalmim']) ? $a2['totalmim'] : 0;
                    $a2['u_mb'] = isset($a2['u_mb']) ? $a2['u_mb'] : 0;
                    $a2['totalin_sms'] = isset($a2['totalin_sms']) ? $a2['totalin_sms'] : 0;
                    $a2['totalin_mim'] = isset($a2['totalin_mim']) ? $a2['totalin_mim'] : 0;

                    $str_ui = "{$a1["totalsms"]},{$a1["totalmim"]},{$a1['totalin']},0,{$a1["u_mb"]}";
                    $str_api = "{$a2["totalsms"]},{$a2["totalmim"]},{$a2["totalin_sms"]},{$a2["totalin_mim"]},{$a2["u_mb"]}";
                    $invno = $setting["invoiceprefix"].sprintf("%05d",$seqno);
                    $filename = $setting["invoiceprefix"].sprintf("%05d",$seqno)."_".$department["department"]."_$z.pdf";
                    $sqlcmd2 = "INSERT INTO invoice (departmentid,dept_name,for_ym,invoiceno,created_by,username,created_dtm,totalsms,totalmim,totalsms_in,totalmim_in,mau,totalsmsapi,totalmimapi,totalsmsapi_in,totalmimapi_in,mauapi,filename,gst) VALUES(
                        '{$department["department_id"]}','{$department["department"]}','$z','$invno','$userid','',now(),$str_ui,$str_api,'$filename',{$setting["gst"]}
                    )";
                    $result = pg_query($dbconn,$sqlcmd2);
                    if (pg_affected_rows($result)) {
                        $invoice->detail("Person in charge",$department["department"],$invno,$fd);
                        $invoice->information_gen($a1['totalsms'],$a1['totalin'],$a1['totalmim'],$a1['u_mb']);
                        $invoice->information_api($a2['totalsms'],$a2['totalin_sms']+$a2['totalin_mim'],$a2['totalmim'],$a2['u_mb']);
                        $invoice->outputas($filename);
                        $invoice->print();
                        $seqno++;
                    }
                
            } else {
                
                    $str_ui = "0,0,0,0,0";
                    $str_api = "0,0,0,0,0";
                    $z = date("Ym", mktime(0,0,0,$mth,1,$yr));
                    $fd = date("M-Y", mktime(0,0,0,$mth,1,$yr));
                    $invno = $setting["invoiceprefix"].sprintf("%05d",$seqno);
                    $filename = $setting["invoiceprefix"].sprintf("%05d",$seqno)."_".$department["department"]."_$z.pdf";
                    $sqlcmd2 = "INSERT INTO invoice (departmentid,dept_name,for_ym,invoiceno,created_by,username,created_dtm,totalsms,totalmim,totalsms_in,totalmim_in,mau,totalsmsapi,totalmimapi,totalsmsapi_in,totalmimapi_in,mauapi,filename,gst) VALUES(
                        '{$department["department_id"]}','{$department["department"]}','$z','$invno','$userid','',now(),$str_ui,$str_api,'$filename',{$setting["gst"]}
                    )";
                    $result = pg_query($dbconn,$sqlcmd2);
                    if (pg_affected_rows($result)) {
                        $invoice->detail("Person in charge",$department["department"],$invno,$fd);
                        $invoice->information_gen(0,0,0,0);
                        $invoice->information_api(0,0,0,0);
                        $invoice->outputas($filename);
                        $invoice->print();
                        $seqno++;
                    }
                
            }
        }
    }

    $sqlcmd = "UPDATE setting SET value = '' WHERE variable = 'specifyym'";
    pg_query($dbconn,$sqlcmd);

    $sqlcmd = "UPDATE setting SET value = $seqno WHERE variable = 'invoicenextseq'";
    pg_query($dbconn,$sqlcmd);
}

?>
