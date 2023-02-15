<?php
require('lib/db_log.php');
require('lib/commonFunc.php');

switch ($_POST['mode']) {
	case "view":
        view($_POST['from'],$_POST['to']);
        break;
    default:
        die("Unknown request");
}

function view($from,$to)
{
	global $logdbconn;
	$arr_res = Array();
	// $limit = Get_Config("/home/msg/conf/totalrecord");

	// error_log("============================== comes in here");
	// error_log("============================== from: " . $from);
	// error_log("============================== to: " . $to);
	
	$cmd = "SELECT to_char(audit_dtm, 'DD/MM/YYYY HH24:MI:SS') as dtm_formatted, 
			user_name, remote_ip, action, mode FROM mom_audit_log WHERE 
			audit_dtm >= to_date('".$from."','DD/MM/YYYY') AND  
			audit_dtm <= to_date('".$to."','DD/MM/YYYY') + interval '1 day'  
			ORDER BY audit_dtm desc";
	$res = pg_query($logdbconn, $cmd);
	$rows = pg_num_rows($res);
	
	// if ( $rows > $limit ) {
	// 	echo json_encode(Array(
	// 		"error"=>"The search returned more than the maximum number of rows (".trim($limit)."). Please refine your search criteria.",
	// 		"data"=>"")
	// 	);
	// } else {
	for ($i=1; $row = pg_fetch_array($res); $i++){
		
		if($row['mode'] == "1"){
			$mode = "API";
		}else{
			$mode = "Portal";
		}

		array_push($arr_res,Array(
			$i,
			$row['dtm_formatted'],	
			htmlspecialchars($row['user_name']),
			htmlspecialchars($row['remote_ip']),
			htmlspecialchars($row['action']),
			htmlspecialchars($mode)
		));
	}
	echo json_encode(Array("data"=>$arr_res));
	// }
}
?>
