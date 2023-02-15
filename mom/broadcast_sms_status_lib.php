<?php
require_once('lib/commonFunc.php');

$userid = strtolower($_SESSION['userid']);
$mode = @$_REQUEST['mode'];
$id = @$_REQUEST['id'];
$date_from = @$_REQUEST['date_from'];
$date_to = @$_REQUEST['date_to'];

$x = GetLanguage("file_upload_status",$lang);

switch ($mode) {
	case "list":
        list_files( $date_from, $date_to );
        break;
	case "delete":
        delete_files( $id );
        break;
	case "select_bot":
        select_bot();
        break;
    default:
        die('Invalid Command');
}

function select_bot(){
	
	global $spdbconn, $x;
	
	$sqlcmd = "select id as bot_id,description as bot_name from bot_route where bot_type_id = '13'";//refer 23 Sep 2020 zin sqoope
	
	$row = getSQLresult($spdbconn, $sqlcmd);
	
	echo json_encode($row);
}


function list_files( $date_from, $date_to ){
	
	global $dbconn, $x, $userid;

	$sqlcmd = "select * from broadcast_sms_file where upload_by = '".dbSafe($userid)."' and upload_dtm >= '".date( "Y-m-d 00:00:00", strtotime( $date_from ) )."' and upload_dtm <= '".date( "Y-m-d 23:59:59", strtotime( $date_to ) )."' order by upload_dtm desc";

	$result = pg_query($dbconn, $sqlcmd);
	if(!$result) {
		echo $x->failed_list_file;
	} else {
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			
			if( $row['process_status'] == 1 ){
				$process_status = $x->process_status_1;
			}else if( $row['process_status'] == 2 ){
				$process_status = $x->process_status_2;
			}else if( $row['process_status'] == 3 ){
				$process_status = $x->process_status_3;
			}
			
			$file_location = $row['file_location'];
			$file_id = $row['id'];
			$upload_dtm = date( "d-m-Y H:i:s", strtotime( $row['upload_dtm'] ) );
			$process_dtm = $row['process_dtm'] ? date( "d-m-Y H:i:s", strtotime( $row['process_dtm'] ) ) : "-";
			$end_process_dtm = $row['end_process_dtm'] ? date( "d-m-Y H:i:s", strtotime( $row['end_process_dtm'] ) ) : "-";
			$total_row = $row['total_row'];
			$current_row = $row['current_row'];
			$invalid_row = $row['invalid_row'];
			$valid_row = $row['valid_row'];
			
			$callerid = $row['callerid'];
			
			if( $row['process_status'] == 3 ){
				$link = '<a href="broadcast_sms_status_list.php?id='.$file_id.'">'.htmlspecialchars($row['file_name'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>';
			}else{
				$link = htmlspecialchars($row['file_name'],ENT_QUOTES);
			}
			
			$send_sms_status = "send_sms_status_" . $row['send_sms_status'];
			$send_sms_status_msg = $x->$send_sms_status;
			
			$cfm_send = "cfm_send_" . $row['cfm_send'];
			$cfm_send_msg = $x->$cfm_send;
			
			$CampaignDetail = getCampaignDetail( $row['campaign_id'] );
			$campaign_status_msg = $CampaignDetail['campaign_status'];
			//$campaign_status_msg = print_r( $CampaignDetail, true );
			
			$dtm = $x->upload_dtm . ": " . $upload_dtm . "<br>" . $x->process_dtm . ": " . $process_dtm . "<br>" . $x->end_process_dtm . ": " . $end_process_dtm . "<br>";
			$all_status = $x->process_status . ": ". $process_status . "<br>" . $x->cfm_send . ": " . $cfm_send_msg . "<br>" . $x->send_sms_status . ": " . $send_sms_status_msg . "<br>" . $x->campaign_status . ": " . $campaign_status_msg . "<br>";
			$all_record = $x->total_row . ": ". $total_row . "<br>" . $x->current_row . ": " . $current_row . "<br>" . $x->invalid_row . ": " . $invalid_row . "<br>" . $x->valid_row . ": " . $valid_row . "<br>";
			
			if( strlen( $file_location ) > 0 ){
				$all_record = $all_record . "<a href = '$file_location' target = '_blank'>".$x->mim_image . "/" . $x->mim_doc ."</a><br>" ;
			}
			
			array_push($result_array,Array(
				$link,
				$dtm,
				//htmlspecialchars($upload_dtm),
				htmlspecialchars($callerid,ENT_QUOTES),
				
				//htmlspecialchars($process_dtm),
				//htmlspecialchars($end_process_dtm),
				$all_status,
				$all_record,
				//htmlspecialchars($process_status),
				//htmlspecialchars($total_row),
				//htmlspecialchars($current_row),
				//htmlspecialchars($invalid_row),
				//htmlspecialchars($valid_row),
				//htmlspecialchars($cfm_send_msg),
				//htmlspecialchars($send_sms_status_msg),
				'<input type="checkbox" class="user_checkbox" id="no" name="no" value="'.$row['id'].'">'
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function delete_files( $id ){
	
	global $dbconn, $x, $userid;
	
	#get file name first
	$file_name = getFileName( $id );

	$sqlcmd = "delete from broadcast_sms_file where id = '".dbSafe($id)."' and upload_by = '".dbSafe($userid)."'";

	$result = pg_query($dbconn, $sqlcmd);
	if(!$result) {
		echo $x->failed_delete_data;
	}else{
		
		//only delete file once delete sql ok
		if( $file_name ){
			
			$excel_ext = array( "xls", "xlsx" );
			$FileType = strtolower(pathinfo($file_name,PATHINFO_EXTENSION));
			
			if( in_array( $FileType, $excel_ext ) ){
				$file_name2 = "/home/msg/www/htdocs/broadcast/images/" . strtolower(pathinfo($file_name,PATHINFO_FILENAME)) . ".csv";
				//echo $file_name2;
				//die;
				$unlink = unlink( $file_name2 );
				
				/*
				if( $unlink ){
					echo "ok";
					die;
				}else{
					echo "failed";
					die;
				}
				*/
				
			}
			
			//die;
			$file_name = "/home/msg/www/htdocs/broadcast/images/" . $file_name; 
			unlink( $file_name );
			
			//delete survey record if have
			$sql1 = "delete from campagin_survey_outbox where file_id = '$id'";
			$result1 = pg_query($dbconn, $sql1);
		
		}
	
	}
	
	insertAuditTrail( "Delete File Upload Message" );
	
	return 1;
}

function getFileName( $id ){
	
	global $dbconn, $x, $userid;
	
	$file_name = "";
	$sqlcmd = "select file_name from broadcast_sms_file where id = '".dbSafe($id)."' and upload_by = '".dbSafe($userid)."'";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$file_name = $row['file_name'];
	}
	
	return $file_name;
}

function getCampaignDetail( $campaign_id ){
	
	global $dbconn;
	
	$sqlcmd = "select * from campaign_mgnt where campaign_id = '".dbSafe($campaign_id)."'";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$returns = $row;
	}
	
	return $returns;
}
?>
