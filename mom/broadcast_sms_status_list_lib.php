<?php
require_once('lib/commonFunc.php');

$userid = strtolower($_SESSION['userid']);
$mode = @$_REQUEST['mode'];
$file_id = @$_REQUEST['id'];
$data_type = @$_REQUEST['data_type'];
$data_id = @$_REQUEST['data_id'];

//below is for datatable paging
$draw = @$_REQUEST['draw'];
$start = @$_REQUEST['start'];//0 = first record
$length = @$_REQUEST['length'];
$search_str = @$_REQUEST['search']['value'];

$x = GetLanguage("file_upload_status",$lang);

switch ($mode) {
	case "list":
        list_data( $file_id, $draw, $start, $length, $data_type, $search_str );
        break;
	case "delete":
        delete_data( $data_id );
        break;
	case "TotalSMS":
        getTotalSMS( $file_id );
        break;
	case "cfm_send":
        cfm_send( $file_id );
        break;
	case "updateCfmSend":
        updateCfmSend( $file_id );
        break;
    default:
        die('Invalid Command');
}

function list_data( $file_id, $draw, $start, $length, $data_type, $search_str ){
	
	global $dbconn, $x, $userid;
	
	(integer)$draw;
	
	if( $draw > 0 ){
		$draw = $draw + 1;
	}else{
		$draw = 1;
	}
	
	if( $data_type == "valid" ){
		$table = "broadcast_sms_temp_valid";
		$font_color = "#5cb85c";
	}else{
		$table = "broadcast_sms_temp_invalid";
		$font_color = "#ac2925";
	}
	
	#get total
	$sqlcmd0 = "select count(*) as recordsTotal from $table where file_id = '".dbSafe($file_id)."' and file_id in ( select id from broadcast_sms_file where upload_by = '".dbSafe($userid)."' )";
	$result0 = pg_query($dbconn, $sqlcmd0);
	if($result0) {
		
		$row0 = pg_fetch_array($result0);
		$recordsTotal = $row0[0];
		
	}else{
		
		$recordsTotal = 0;
	}
	
	//$no = ( $length * $start );
	$no = $start;
	
	if( $start > 1 ){
		$offset = $start;
	}else{
		$offset = 0;
	}
	
	if( $search_str != "" ){
		$where_sql = " and ( mobile_numb like '%$search_str%' or message like '%$search_str%')";
	}
	
	$sqlcmd = "select * from $table where file_id = '".dbSafe($file_id)."' $where_sql order by id asc limit $length offset $offset";
	
	$result = pg_query($dbconn, $sqlcmd);
	if(!$result) {
		echo $x->failed_list_file;
	} else {
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			
			$no++;
			$file_id = $row['file_id'];
			$mobile_numb = $row['mobile_numb'];
			$message = $row['message'];
			$mim_message = $row['mim_message'];
			
			$encoding = mb_detect_encoding($message, "UTF-8");
			if( $encoding ){//is utf-8
				$max_str = 100;
			}else{
				$max_str = 200;
			}
			
			$sub_msg = mb_strlen( $message ) > $max_str ? mb_substr( $message, 0, $max_str ) . " .....": $message;
			//$sub_msg = $message;
			$mim_sub_msg = mb_strlen( $mim_message ) > $max_str ? mb_substr( $mim_message, 0, $max_str ) . " .....": $mim_message;
			
			if( mb_strlen( $message ) > $max_str ){
				$show_msg = '<a id = "'.$no.'" href="#" data-bs-toggle="tooltip" data-bs-html="true" title="'.$message.'" data-original-title="" data-bs-placement="right">'.$sub_msg.'</a>';
			}else{
				$show_msg = $message;
			}

			if( mb_strlen( $mim_message ) > $max_str ){
				$mim_show_msg = '<a id = "'.$no.'" href="#" data-bs-toggle="tooltip" data-bs-html="true" title="'.$mim_message.'" data-original-title="" data-bs-placement="right">'.$mim_sub_msg.'</a>';
			}else{
				$mim_show_msg = $mim_message;
			}
			
			$invalid_code = 'invalid_code_msg_' . ( $row['invalid_code'] > 0 ? $row['invalid_code'] : "0" );
			$error_msg = $x->$invalid_code;
			
			$total_sms = $row['total_sms'];
			
			//get file id to check sms or sms_mim
			$send_mode = '';
			$sql0 = "select send_mode from broadcast_sms_file where id = '$file_id'";
			$result0 = pg_query($dbconn, $sql0);
			if( $row0 = pg_fetch_array($result0) ){
				$send_mode = $row0['send_mode'];
			}
			
			if( $send_mode == "sms_mim" || $send_mode == "mim" ){
				$total_mim = "1";
			}else{
				$total_mim = "0";//sms only
			}
			
			if( $send_mode == "mim" ){
				$total_sms = 0;//unset
			}
			
			if( $data_type == "invalid" ){
				$total_mim = "0";
			}
			
			$id_string = $row['id'] . "@" . $table; 
			
			array_push($result_array,Array(
				$no,
				'<font color = "'.$font_color.'">' . $mobile_numb . "</font>",
				//'<a id = "'.$no.'" href="#" data-toggle="tooltip" data-html="true" title="" data-original-title="'.$message.'" data-placement="right">'.$sub_msg.'</a>',
				//"draw: " . $draw . " | length: " . $length . " | offset: " . $offset . " | start:" . $start,
				$show_msg,
				$mim_show_msg,
				htmlspecialchars( $error_msg ),
				$total_sms,
				$total_mim
				//'<input type="checkbox" id="no" name="no" value="'.$id_string.'">'
			));
		}
		
		//print_r( $result_array );
		//die;
		echo json_encode(Array( "draw"=>$draw, "recordsTotal"=>$recordsTotal, "recordsFiltered"=>$recordsTotal, "data"=>$result_array));
	}
}

function delete_data( $data_id ){
	
	global $dbconn, $x, $userid;
	
	$data_id = explode( "@", $data_id );
	$id_to_delete = $data_id[0];
	$table = $data_id[1];
	
	#get this data_id's file id 1st
	$file_id = getFileID( $id_to_delete, $table );

	$sqlcmd = "delete from $table where id = '".dbSafe($id_to_delete)."' and file_id in ( select id from broadcast_sms_file where upload_by = '".dbSafe($userid)."' )";
	
	$result = pg_query($dbconn, $sqlcmd);
	
	if(!$result) {
		echo $x->failed_delete_data;
	}else{
		
		//after delete, update total row
		if( $table == "broadcast_sms_temp_invalid" ){
			
			$sql2 = "update broadcast_sms_file set invalid_row = invalid_row - 1 where id = '".$file_id."'";
			$result = pg_query($dbconn, $sql2);
			
		}
		
		if( $table == "broadcast_sms_temp_valid" ){
			
			$sql2 = "update broadcast_sms_file set valid_row = valid_row - 1 where id = '".$file_id."'";
			$result = pg_query($dbconn, $sql2);
			
		}
		
	}
	
	insertAuditTrail( "deleted File Upload Message" );
	
	return 1;
	
}

function getTotalSMS( $file_id ){
	
	global $dbconn, $x, $userid;
	
	//get file id to check sms or sms_mim
	$send_mode = '';
	$sql0 = "select send_mode from broadcast_sms_file where id = '$file_id'";
	$result0 = pg_query($dbconn, $sql0);
	if( $row0 = pg_fetch_array($result0) ){
		$send_mode = $row0['send_mode'];
	}

	$total_valid = 0;
	$sqlcmd = "select sum(total_sms) as total_valid from broadcast_sms_temp_valid where file_id = '".dbSafe($file_id)."' and file_id in ( select id from broadcast_sms_file where upload_by = '".dbSafe($userid)."' )";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$total_valid = $row['total_valid'];
	}
	
	if( $send_mode == "sms_mim" || $send_mode == "mim" ){
		
		$sqlcmd = "select count(*) as total_mim from broadcast_sms_temp_valid where file_id = '".dbSafe($file_id)."' and file_id in ( select id from broadcast_sms_file where upload_by = '".dbSafe($userid)."' )";
		$result = pg_query($dbconn, $sqlcmd);
		for ($i=1; $row = pg_fetch_array($result); $i++){
			$total_mim = $row['total_mim'];
		}
		
	}else{
		
		$total_mim = 0;
		
	}
	
	$total_invalid = 0;
	$sqlcmd = "select sum(total_sms) as total_invalid from broadcast_sms_temp_invalid where file_id = '".dbSafe($file_id)."' and file_id in ( select id from broadcast_sms_file where upload_by = '".dbSafe($userid)."' )";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$total_invalid = $row['total_invalid'];
	}
	
	$total_row = $valid_row = $invalid_row = 0;
	$process_status = 1;
	$send_sms_status = 0;
	$cfm_send = 0;
	
	$sqlcmd = "select total_row, valid_row, invalid_row, process_status, cfm_send, send_sms_status, campaign_id, send_mode from broadcast_sms_file where id = '".dbSafe($file_id)."' and upload_by = '".dbSafe($userid)."'";
	
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$total_row = $row['total_row'];
		$valid_row = $row['valid_row'];
		$invalid_row = $row['invalid_row'];
		$process_status = $row['process_status'];
		$cfm_send = $row['cfm_send'];
		$send_sms_status = $row['send_sms_status'];
		$campaign_id = $row['campaign_id'];
		$send_mode = $row['send_mode'];
	}
	
	$process_status_msg_code = "process_status_" . $process_status;
	$process_status_msg = $x->$process_status_msg_code;
	
	$send_sms_status_code = "send_sms_status_" . $send_sms_status;
	$send_sms_status_msg = $x->$send_sms_status_code;
	
	$cfm_send_msg = ( $cfm_send == 0 ? "No" : "Yes" );
	
	$quota_left = $unlimited_quota = 0;
	$sqlcmd = "select quota_left, unlimited_quota from quota_mnt where userid = '".dbSafe($userid)."'";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$quota_left = $row['quota_left'];
		$unlimited_quota = $row['unlimited_quota'];
	}
	
	#get reserved_quota
	$reserved_quota = 0;
	$sqlcmd = " select sum(reserved_quota) as reserved_quota from broadcast_sms_file where upload_by = '".dbSafe($userid)."'";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$reserved_quota = $row['reserved_quota'];
	}
	
	//get campaign_id status
	$sqlcmd = "select campaign_name, campaign_status from campaign_mgnt where campaign_id = '".dbSafe($campaign_id)."'";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$campaign_name = $row['campaign_name'];
		$campaign_status = $row['campaign_status'];
	}
	
	if( $unlimited_quota == "0" ){
		
		if( ( $total_valid + $total_mim + $reserved_quota ) > $quota_left ){
			
			$quota_string .=  "<font color = '#ac2925'>" . $x->quota_left . ": " . ( $unlimited_quota ? "Unlimited" : $quota_left ) . " (" . $x->insufficient_quota . " )</font>";
			
		}else{
			
			$quota_string = $x->quota_left . ": " . ( $unlimited_quota ? "Unlimited" : $quota_left );
		
		}
	
	}else{
		
		$quota_string = $x->quota_left . ": " . ( $unlimited_quota ? "Unlimited" : $quota_left );
		
	}
	
	$reserved_quota_string = $x->reserved_quota.": $reserved_quota";
	
	if( $cfm_send == 1 ){#already confirm send, no need show quota again, overwrite it
		
		$quota_section = '';
		
	}else{
		
		$quota_section = 
		
									'
									<div class = "col-md-6">
												
										<table class="table">
											
											<tbody>
											  <tr class = "table-active">
												<td>'.$x->reserved_quota.'</td>
												<td>'.$reserved_quota.'</td>
											  </tr>     
											 <tr class = "table-active">
												<td>'.$x->quota_required.'</td>
												<td>'. ($reserved_quota+$total_valid+$total_mim)  .'</td>
											  </tr>   
											  <tr class = "table-active">
												<td>'.$x->quota_left.'</td>
												<td>'. $quota_string  .'</td>
											  </tr>    
											</tbody>
											
										  </table>
									
									</div>
									';
									
	}
	
	if( !$reserved_quota > 0 ){
		$reserved_quota = 0;
	}
	
	if( $send_mode == "sms" || $send_mode == "sms_mim" ){
		
		if( !$total_valid > 0 ){
			$total_valid = 0;
		}
		
		if( !$total_invalid > 0 ){
			$total_invalid = 0;
		}
	
	}else{
		
		$total_valid = 0;//mim only dont have sms counted
		$total_invalid = 0;
		
	}
	
	if( !$total_mim > 0 ){
		$total_mim = 0;
	}

	if( $send_mode == "sms" ){
		$send_mode_txt = "SMS";
	}else if( $send_mode == "sms_mim" ){
		$send_mode_txt = "SMS & MIM";
	}else if( $send_mode == "mim" ){
		$send_mode_txt = "MIM";
	}

	$return .= '
					<div class = "col-md-6">
					
						<table class="table">
							
							<tbody>
							  <tr>
								<td>'.$x->total_row.'</td>
								<td>'.$total_row.'</td>
							  </tr>      
							  <tr class="table-success">
								<td>'.$x->valid_row.'</td>
								<td>'.$valid_row.'</td>
							  </tr>
							  <tr class="table-danger">
								<td>'.$x->invalid_row.'</td>
								<td>'. $invalid_row .'</td>
							  </tr>
							</tbody>
							
						  </table>
					
					</div>
					
					<div class = "col-md-6">
					
						<table class="table">
							
							<tbody>
							  <tr>
								<td>'.$x->total_sms_to_send . " & " . $x->total_mim .'</td>
								<td>'.$total_valid . " + " . $total_mim .'</td>
							  </tr>      
							  <tr class="table-success">
								<td>'.$x->total_sms_valid . " & " . $x->total_mim .'</td>
								<td>'.$total_valid . " + " . $total_mim.'</td>
							  </tr>
							  <tr class="table-danger">
								<td>'.$x->total_sms_invalid.'</td>
								<td>'.$total_invalid.'</td>
							  </tr>
							</tbody>
							
						  </table>
					
					</div>
					
					<div class = "col-md-6">
					
						<table class="table">
							
							<tbody>
							  <tr class = "table-active">
								<td>'.$x->process_status.'</td>
								<td>'.$process_status_msg.'</td>
							  </tr>      
							  <tr class = "table-active">
								<td>'.$x->cfm_send.'</td>
								<td>'.$cfm_send_msg.'</td>
							  </tr>   
							  <tr class = "table-active">
								<td>'.$x->send_sms_status.'</td>
								<td>'.$send_sms_status_msg.'</td>
							  </tr>   
							</tbody>
							
						  </table>
					
					</div>
					
					'.$quota_section.'
					
					<div class = "col-md-6">
					
						<table class="table">
							
							<tbody>
							  <tr class = "table-active">
								<td>'.$x->campaign_name.'</td>
								<td>'.$campaign_name.'</td>
							  </tr>      
							  <tr class = "table-active">
								<td>'.$x->campaign_status.'</td>
								<td>'.$campaign_status.'</td>
							  </tr>  
							 <tr class = "table-active">
								<td>'.$x->send_mode.'</td>
								<td>'. ( $send_mode_txt )  .'</td>
							 </tr>   
							</tbody>
							
						  </table>
					
					</div>
				
					' ;
	
	echo $return;
}

function getFileID( $data_id, $table ){
	
	global $dbconn, $x, $userid;
	
	$file_id = 0;
	$sqlcmd = "select file_id from $table where id = '".dbSafe($data_id)."' and file_id in ( select id from broadcast_sms_file where upload_by = '".dbSafe($userid)."' )";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$file_id = $row['file_id'];
	}
	
	return $file_id;
	
}

function cfm_send( $file_id ){
	
	global $dbconn, $userid;
	
	#check again
	$total_valid = 0;
	$sqlcmd = "select sum(total_sms) as total_valid from broadcast_sms_temp_valid where file_id = '".dbSafe($file_id)."' and file_id in ( select id from broadcast_sms_file where upload_by = '".dbSafe($userid)."' )";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$total_valid = $row['total_valid'];
	}
	
	$total_record = 0;
	$sqlcmd = "select count(*) as total_record from broadcast_sms_temp_valid where file_id = '".dbSafe($file_id)."' and file_id in ( select id from broadcast_sms_file where upload_by = '".dbSafe($userid)."' )";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$total_record = $row['total_record'];
	}
	
	#get reserved_quota
	$reserved_quota = 0;
	$sqlcmd = " select sum(reserved_quota) as reserved_quota from broadcast_sms_file where upload_by = '".dbSafe($userid)."'";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$reserved_quota = $row['reserved_quota'];
	}
	
	$sql1 = "select campaign_id, send_mode from broadcast_sms_file where id = '".dbSafe( $file_id )."' and upload_by = '".dbSafe($userid)."'";
	$result = pg_query($dbconn, $sql1);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$campaign_id = $row['campaign_id'];
		$send_mode = $row['send_mode'];
	}
	
	//get campaign_id status
	$sqlcmd = "select campaign_status from campaign_mgnt where campaign_id = '".dbSafe($campaign_id)."'";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$campaign_status = $row['campaign_status'];
	}

	#get quota_left
	$quota_left = $unlimited_quota = 0;
	$sqlcmd = "select quota_left, unlimited_quota from quota_mnt where userid = '".dbSafe($userid)."'";
	$result = pg_query($dbconn, $sqlcmd);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$quota_left = $row['quota_left'];
		$unlimited_quota = $row['unlimited_quota'];
	}
	
	if( $send_mode == "sms" ){
		
	}elseif( $send_mode == "sms_mim" ){
		$total_valid = $total_valid + $total_record;	
	}elseif( $send_mode == "mim" ){
		$total_valid = $total_record;
	}
	
	if( $campaign_status == "active" ){
		
		if( $unlimited_quota == "0" ){
			
			//echo "total_valid: $total_valid | reserved_quota: $reserved_quota | quota_left: $quota_left | total_valid: $quota_left";
			//die;
			
			if( ( $total_valid + $reserved_quota ) > $quota_left ){
				
				//echo "not enough quota";
				#not enough quota
				$continue = false;
				
			}else{
				//echo "enough quota";
				
				#enough quota
				$continue = true;
			
			}
			
			//die;
			
		}else{
			#unlimited
			$continue = true;
		}
	
	}else{
		
		#campaign is pause or cancel
		$continue = false;
	}
	
	if( $continue ){
	
		$sql1 = "update broadcast_sms_file set cfm_send = '1', reserved_quota = $total_valid where id = '".dbSafe( $file_id )."' and upload_by = '".dbSafe( $userid )."'";
		//echo $sql1;
		//die;
		$result = pg_query($dbconn, $sql1);

		if( $result ){
			echo 1;
		}else{
		
			echo 0;
		}

	}else{
		
		echo 0;
	}
	
	insertAuditTrail( "Send File Upload Message" );
}

function updateCfmSend( $file_id ){
	
	global $dbconn;
	
	$cfm_send = 0;
	$process_status = 1;

	$sql1 = "select send_mode,cfm_send, process_status, upload_by, campaign_id, send_sms_status from broadcast_sms_file where id = '".dbSafe( $file_id )."'";
	$result = pg_query($dbconn, $sql1);
	for ($i=1; $row = pg_fetch_array($result); $i++){
		$cfm_send = $row['cfm_send'];
		$process_status = $row['process_status'];
		$upload_by = $row['upload_by'];
		$campaign_id = $row['campaign_id'];
		$send_mode = $row['send_mode'];
		$send_sms_status = $row['send_sms_status'];
	}
	
	if( $send_sms_status == "2" ){
		
		$status = "1";
		$msg = "This batch message has been completed.";
			
	}else{
		
		//get campaign_id status
		$sqlcmd = "select campaign_status from campaign_mgnt where campaign_id = '".dbSafe($campaign_id)."'";
		$result = pg_query($dbconn, $sqlcmd);
		for ($i=1; $row = pg_fetch_array($result); $i++){
			$campaign_status = $row['campaign_status'];
		}
		
		//check quota_left
		$quota_left = $unlimited_quota = 0;
		$sqlcmd = "select quota_left, unlimited_quota from quota_mnt where userid = '".dbSafe($upload_by)."'";
		$result = pg_query($dbconn, $sqlcmd);
		for ($i=1; $row = pg_fetch_array($result); $i++){
			$quota_left = $row['quota_left'];
			$unlimited_quota = $row['unlimited_quota'];
		}
		
		$total_valid = 0;
		$sqlcmd = "select sum(total_sms) as total_valid from broadcast_sms_temp_valid where file_id = '".dbSafe($file_id)."' and file_id in ( select id from broadcast_sms_file where upload_by = '".dbSafe($upload_by)."' )";
		$result = pg_query($dbconn, $sqlcmd);
		for ($i=1; $row = pg_fetch_array($result); $i++){
			$total_valid = $row['total_valid'];
		}
		
		#get reserved_quota
		$reserved_quota = 0;
		$sqlcmd = " select sum(reserved_quota) as reserved_quota from broadcast_sms_file where upload_by = '".dbSafe($upload_by)."'";
		$result = pg_query($dbconn, $sqlcmd);
		for ($i=1; $row = pg_fetch_array($result); $i++){
			$reserved_quota = $row['reserved_quota'];
		}
		
		#add mim total_valid
		$total_valid2 = 0;
		if( $send_mode == "sms_mim" || $send_mode == "mim" ){
		
			$sqlcmd = "select count(*) as total_valid2 from broadcast_sms_temp_valid where file_id = '".dbSafe($file_id)."' and file_id in ( select id from broadcast_sms_file where upload_by = '".dbSafe($upload_by)."' )";
			$result = pg_query($dbconn, $sqlcmd);
			for ($i=1; $row = pg_fetch_array($result); $i++){
				$total_valid2 = $row['total_valid2'];
			}
		
		}
		
		$total_valid = $total_valid + $total_valid2;
		
		if( $campaign_status == "active" && $total_valid > 0 ){
			
			if( $unlimited_quota == "0" ){
				
				if( ( $total_valid + $reserved_quota ) > $quota_left ){
					
					//echo 1;//disable send button
					
					$status = "1";
					$msg = "Insufficient quota to send.";
					
				}else{
					
					//quota enough, check status
					if( $process_status == "3" ){
				
						if( $cfm_send == "1" ){
							//echo 1;//disable send button
							
							$status = "1";
							$msg = "This batch message is sending or sent.";
						
						}else{
							//echo 0;
							
							$status = "0";
							$msg = "This batch message ready to send.";
						}
						
					}else{
						
						//echo 1;//disable send button
						
						$status = "1";
						$msg = "This batch file still in processing.";
						
					}
			
				}
				
			}else{
				
				//unlimited quota, check status only
				if( $process_status == "3" ){
				
					if( $cfm_send == "1" ){
						
						//echo 1;//disable send button
						$status = "1";
						$msg = "This batch message is sending or sent.";
					
					}else{
						//echo 0;
						
						$status = "0";
						$msg = "This batch message ready to send.";
					}
					
				}else{
					
					//echo 1;//disable send button
					$status = "1";
					$msg = "This batch file still in processing.";
			
				}
			
			}
		
		}else{
			
			$status = "1";
			$msg = "No valid mobile number to send or campaign is paused.";
			//echo 1;//disable send button
		}
		
	}
	
	$returns['status'] = $status;
	$returns['msg'] = $msg;
	
	echo json_encode( $returns );
}
?>
