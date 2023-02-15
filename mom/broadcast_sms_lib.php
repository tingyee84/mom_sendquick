<?php
//error_reporting(E_ALL);
//ini_set('display_errors', TRUE);

require_once('lib/commonFunc.php');
require_once('lib/Classes/PHPExcel.php');

//$mode = filter_input(INPUT_POST,'mode');
$mode = @$_REQUEST['mode'];
$userid = strtolower($_SESSION['userid']);
$dept = $_SESSION['department'];
//$mode = "UploadFile";

switch ($mode) {
	case "insertBroadCast":
		insertBroadCast($userid,$dept,
			filter_input(INPUT_POST,'priority',FILTER_SANITIZE_NUMBER_INT), 
			@$_FILES['upload_file']['tmp_name'], 
			filter_input(INPUT_POST,'file_format'), 
			filter_input(INPUT_POST,'character_set'), 
			filter_input(INPUT_POST,'content_type'), 
			filter_input(INPUT_POST,'smstext'), 
			filter_input(INPUT_POST,'scheduled'), 
			$_POST['sms_date']." ".$_POST['sms_hour'].":".$_POST['sms_min']);
		break;
	case "UploadFile":
		UploadFileV2($userid,$dept,
			filter_input(INPUT_POST,'priority',FILTER_SANITIZE_NUMBER_INT), 
			@$_FILES['upload_file']['tmp_name'], 
			filter_input(INPUT_POST,'file_format'), 
			filter_input(INPUT_POST,'character_set'), 
			filter_input(INPUT_POST,'content_type'), 
			filter_input(INPUT_POST,'smstext'),
			filter_input(INPUT_POST,'mimtext'), 
			filter_input(INPUT_POST,'scheduled'), 
			@$_POST['sms_date']." ".@$_POST['sms_hour'].":".@$_POST['sms_min'],
			filter_input(INPUT_POST,'campaign_id'),
			filter_input(INPUT_POST,'callerid'),
			filter_input(INPUT_POST,'sendmode'),
			
			filter_input(INPUT_POST,'bot_id'),
			filter_input(INPUT_POST,'mim_tpl'),
			filter_input(INPUT_POST,'mim_tpl_id'),
			filter_input(INPUT_POST,'mim_params'),
			@$_FILES,
			filter_input(INPUT_POST,'mim_file_type')
			);
		break;
	case "listBroadCast":
		listBroadCast($userid, $dept);
		break;
	case "sendBroadCast":
		sendBroadCast($userid, $dept);
		break;
	case "deleteBroadCast":
		deleteBroadCast($userid);
		break;
	case "select_callerid":
        select_callerid();
        break;
    default:
        die('Invalid Command');
}

function select_callerid(){
	
	global $spdbconn;
	
	//$sqlcmd = "select callerid, label from asp_route where enable_flag = 1";
	$sqlcmd = "select label, label as callerid from direct_conn where enableflag = 1";//replace, use same value with label
	
	$row = getSQLresult($spdbconn, $sqlcmd);

	if(empty($row)) {
		echo $x->failed_list_campaign;
	} else {
		echo json_encode($row);
	}
	
}

function insertBroadCast($userid,$department,$priority_mode,$uploadfile,$file_format,$sms_mode,$content_type,$sms_text,$scheduled,$scheduled_dtm)
{
	global $dbconn;

	$i = 0;
	$pattern = "/^\+?\d+$/";
	$sms_text = urldecode(trim($sms_text));
	$sms_text = htmlspecialchars_decode($sms_text);

	$file = fopen($uploadfile, 'r');
	fseek($file, 0);

	//Clear first if some old record exist in the table somehow
	pg_query($dbconn, "delete from broadcast_sms where lower(userid)='".$userid."';");

	while(!feof($file))
	{
		$i = $i + 1;
		if($file_format == 1)
		{
			$curr_arr = fgetcsv($file, 1000, ",");
		}
		else if($file_format == 2)
		{
			$curr_arr = fgetcsv($file, 1000, "\t");
		}
		
		$correct_format = 0;
		$mobile_numb = $curr_arr[0];
		$mobile_numb = preg_replace("/^[\n\r\s\t]+/", "", $mobile_numb);
		$mobile_numb = preg_replace("/[\n\r\s\t]+$/", "", $mobile_numb);
		
		if(checkUnsubMobile($mobile_numb)){
			continue;
		}

		if($content_type == 1) 
		{
			
			if(count($curr_arr) < 1)
			{
				$correct_format = 1;
			}
			if(!empty($mobile_numb))
			{
				if(preg_match($pattern, $mobile_numb))
				{
					
					$sms_text = preg_replace("/^[\n\r\s\t]+/", "", $sms_text);
					$sms_text = preg_replace("/[\n\r\s\t]+$/", "", $sms_text);
					$sms_text_insert = pg_escape_literal($sms_text);
					$broadcast_id = getSequenceID($dbconn,'broadcast_sms_broadcast_id_seq');

					if($scheduled == 1) {
						$sqlcmd = "insert into broadcast_sms (broadcast_id,priority,userid,department,mobile_numb,message,mode,scheduled_dtm,file_format)
									values ('".$broadcast_id."','".$priority_mode."','".$userid."','".$department."','".$mobile_numb."',{$sms_text_insert},'".$sms_mode."',to_timestamp('".$scheduled_dtm."','DD-MM-YYYY HH24:MI'),'".$correct_format."');";
					} else {
						$sqlcmd = "insert into broadcast_sms (broadcast_id,priority,userid,department,mobile_numb,message,mode,file_format)
									values ('".$broadcast_id."','".$priority_mode."','".$userid."','".$department."','".$mobile_numb."',{$sms_text_insert},'".$sms_mode."','".$correct_format."');";
					}
					
					pg_query($dbconn, $sqlcmd);
				}
			}
		} 
		else if($content_type == 2) 
		{
			if(count($curr_arr) < 2) {
				$correct_format = 1;
			}
			if(!empty($mobile_numb))
			{
				if(preg_match($pattern, $mobile_numb))
				{
					$no_of_field = count($curr_arr) - 1;
					for($a=0; $a<$no_of_field; $a++)
					{
						$col_no = $a + 1;
						$search_arr[$a] = "/<data" .$col_no. ">/";
						$replace_arr[$a] = $curr_arr[$col_no];
					}
					if($search_arr != '') {
						ksort($search_arr);
						ksort($replace_arr);
						$replace_text = preg_replace($search_arr, $replace_arr, $sms_text);
					}

					$replace_text = preg_replace("/^[\n\r\s\t]+/", "", $replace_text);
					$replace_text = preg_replace("/[\n\r\s\t]+$/", "", $replace_text);
					$replace_text_insert = pg_escape_literal($replace_text);
					$broadcast_id = getSequenceID($dbconn,'broadcast_sms_broadcast_id_seq');
					
					if($scheduled == 1) {
						$sqlcmd = "insert into broadcast_sms (broadcast_id,priority,userid,department,mobile_numb,message,mode,scheduled_dtm,file_format) 
									values ('".$broadcast_id."','".$priority_mode."','".$userid."','".$department."','".$mobile_numb."',{$replace_text_insert},'".$sms_mode."',to_timestamp('".$scheduled_dtm."','DD-MM-YYYY HH24:MI'),'".$correct_format."');";
					} else {
						$sqlcmd = "insert into broadcast_sms (broadcast_id,priority,userid,department,mobile_numb,message,mode,file_format) 
									values ('".$broadcast_id."','".$priority_mode."','".$userid."','".$department."','".$mobile_numb."',{$replace_text_insert},'".$sms_mode."','".$correct_format."');";
					}
					
					pg_query($dbconn, $sqlcmd);
				}
			}
		} 
		else if($content_type == 3) 
		{
			if(count($curr_arr) < 2)
			{
				$correct_format = 1;
			}
			if(!empty($mobile_numb) && !empty($curr_arr[1]))
			{
				if(preg_match($pattern, $mobile_numb))
				{
					$sms_text = '';
					$arr_count = count($curr_arr);
					if($arr_count > 2) {
						for($i=1; $i<count($curr_arr); $i++){
							if($sms_text != '') {
								$sms_text .= ", ".$curr_arr[$i];
							} else {
								$sms_text .= $curr_arr[$i];
							}
						}
					} else {
						$sms_text = $curr_arr[1];
					}

					$sms_text = preg_replace("/^[\n\r\s\t]+/", "", $sms_text);
					$sms_text = preg_replace("/[\n\r\s\t]+$/", "", $sms_text);
					$sms_text_insert = pg_escape_literal($sms_text);
					$broadcast_id = getSequenceID($dbconn,'broadcast_sms_broadcast_id_seq');
					
					if($scheduled == 1) {
						$sqlcmd = "insert into broadcast_sms (broadcast_id,priority, userid, department, mobile_numb, message, mode, scheduled_dtm, file_format) 
									values ('".$broadcast_id."','".$priority_mode."','" .$userid. "','".$department."','".$mobile_numb."',{$sms_text_insert},'".$sms_mode."',to_timestamp('".$scheduled_dtm."','DD-MM-YYYY HH24:MI'),'".$correct_format."');";
					} else {
						$sqlcmd = "insert into broadcast_sms (broadcast_id,priority, userid, department, mobile_numb, message, mode, file_format) 
									values ('".$broadcast_id."','".$priority_mode."','" .$userid. "','".$department."','".$mobile_numb."',{$sms_text_insert},'".$sms_mode."','".$correct_format."');";
					}
					
					pg_query($dbconn, $sqlcmd);
				}
			}
		}
	}
	
	if(feof($file)) {
		echo 1;
	} else {
		echo 0;
	}
}

function UploadFileV2( $userid,$department,$priority_mode,$uploadfile,$file_format,$sms_mode,$content_type,$sms_text, $mim_text ,$scheduled,$scheduled_dtm, $campaign_id, $callerid, $send_mode, $bot_id, $tpl_type, $tpl_id, $mim_params, $files, $mim_file_type)
{
	global $dbconn;
	$ori_sms_text = $sms_text;
	$ori_mim_text = $mim_text;
	
	if( $mim_file_type == "1" ){
		$file_type_txt = "image";
	}elseif( $mim_file_type == "2" ){
		$file_type_txt = "pdf";
	}else{
		$file_type_txt = "image";
	}
	
	if( isset($files["mim_image1"]) ){
				
		$location_to_save = "images/mim_uploaded/";
		$uploaded_files_url = check_upload_file( $files['mim_image1'], $file_type_txt, $location_to_save );//new version from 11 August 2021
	
		//echo '<pre>'; print_r( $files['mim_image1'] ); echo '</pre>';
		//echo '<pre>'; print_r($uploaded_files_url); echo '</pre>';
		//echo "mim_file_type edwin ==" . $mim_file_type;
		//die;
			
		//$uploaded_files_url = upload_files( $files, $mim_file_type );//old version
		
		if( $uploaded_files_url['status'] == "1" ){
			$file_location = $uploaded_files_url['file_location'];
		}else{
			$file_location = "";
		}
	
	}
	
	//echo "edwin.......";
	//die;
	
	if( $tpl_type == "yes" ){//mean is mim template
		
		$split1 = explode( "@@", $mim_params );
		
		foreach( $split1 as $key => $value ){
			
			$split2 = explode( "==", $value );
			
			$ParamName = "<" . $split2[0] . ">";
			$ParamValue = $split2[1];
			
			$mim_text = str_replace( $ParamName, $ParamValue ,$mim_text );
			
			if( $ParamValueOnly == "" ){
				$ParamValueOnly = $split2[1];
			}else{
				$ParamValueOnly = $ParamValueOnly . "@@@". $split2[1];
			}
	
		}
		
	}
	
	//start excel csv
	$uplodaStatus = 0;
	
	$location_to_save_2 = "../broadcast/images/";
	$uploaded_files_url_2 = check_upload_file( $files['upload_file'], "excel_csv", $location_to_save_2 );//new version from 11 August 2021
	//echo '<pre>'; print_r($files); echo '</pre>';
	//echo '<pre>'; print_r($uploaded_files_url_2); echo '</pre>';
	//die;
			
	//get callerid label name
	//$label = getCallerIDLabel( $callerid );//not use for MOM
	$label = $callerid;//MOM label is same callerid
	
	if( $uploaded_files_url_2['status'] == "1" ){
		
		$uplodaStatus = 1;
		
		$uploadname = $uploaded_files_url_2['new_file_name'];
		$uploadfile = "../broadcast/images/".$uploadname;
		
		$excel_ext = array( "xls", "xlsx" );
		$FileType = strtolower(pathinfo($_FILES['upload_file']['name'],PATHINFO_EXTENSION));
		
		if( in_array( $FileType, $excel_ext ) ){
			$total_row = getExcelTotalRow( $uploadfile );//method for excel
		}else{
			//$total_row = count(file( $uploadfile ));//method for csv, old method
			$total_row = getExcelTotalRow( $uploadfile );//method for excel, use PHPExcel to get csv total row
		}
		
		//echo "total_row: " . $total_row;
		//die;
	
		//unset un-used value
		/*
		// 2021-10-19 Split up
		if( $send_mode == "sms_mim" || $send_mode == "mim" ){
			
			if( $content_type == "1" ){//message base on smstext and param( for mim template) key in by user
				
			}elseif( $content_type == "2" ){//message base on smstext and param( for mim template) from csv file
				
				$mim_params = $ParamValueOnly = "";
				
				$sms_text = $ori_sms_text;
				
			}elseif( $content_type == "3" ){//dont have smstext and param, full msg from csv file. mean not is mim template selected
				
				$sms_text = $tpl_type = $tpl_id = $mim_params = $ParamValueOnly = "";
				
			}
			
			if( $tpl_id ){
				
				$tpl_detail = getTplDetail( $tpl_id );
				if( $tpl_detail['mim_tpl_id'] ){
					$mim_tpl_id = $tpl_detail['mim_tpl_id'];
				}else{
					$mim_tpl_id = "";
				}
			
			}
		}
		*/
		if( $send_mode == "sms_mim"){
			if( $content_type == "1" ){//message base on smstext and param( for mim template) key in by user
				
			}elseif( $content_type == "2" ){//message base on smstext and param( for mim template) from csv file
				$mim_params = $ParamValueOnly = "";
				$sms_text = $ori_sms_text;
				$mim_text = $ori_mim_text;
			}elseif( $content_type == "3" ){//dont have smstext and param, full msg from csv file. mean not is mim template selected
				$mim_text = $sms_text = $tpl_type = $tpl_id = $mim_params = $ParamValueOnly = "";
			}
		} else if($send_mode == "mim" ){
			$sms_text = "";
			if( $content_type == "1" ){//message base on smstext and param( for mim template) key in by user
				
			}elseif( $content_type == "2" ){//message base on smstext and param( for mim template) from csv file
				$mim_params = $ParamValueOnly = "";
				$mim_text = $ori_mim_text;
			}elseif( $content_type == "3" ){//dont have smstext and param, full msg from csv file. mean not is mim template selected
				$mim_text = $tpl_type = $tpl_id = $mim_params = $ParamValueOnly = "";
			}
			if( $tpl_id ){
				$tpl_detail = getTplDetail( $tpl_id );
				if( $tpl_detail['mim_tpl_id'] ){
					$mim_tpl_id = $tpl_detail['mim_tpl_id'];
				}else{
					$mim_tpl_id = "";
				}
			}
		} else{
			$ParamValueOnly = $mim_params = $tpl_id = $tpl_type = $bot_id = "";

			if( $content_type == "1" ){

			}elseif( $content_type == "2" ){
			
			}elseif( $content_type == "3" ){
				$mim_text = $sms_text = "";
			}
			
		}

		$sms_text = preg_replace("/^[\n\r\s\t]+/", "", $sms_text);
		$sms_text = preg_replace("/[\n\r\s\t]+$/", "", $sms_text);
		$sms_text_insert = pg_escape_literal($sms_text);

		$mim_text = preg_replace("/^[\n\r\s\t]+/", "", $mim_text);
		$mim_text = preg_replace("/[\n\r\s\t]+$/", "", $mim_text);
		$mim_text_insert = pg_escape_literal($mim_text);
			
		if($scheduled == 1) {
			
			$sql1 = "insert into broadcast_sms_file ( file_name, upload_by, department, message, mim_message, mode, file_format, scheduled_dtm, priority, callerid, modem_label, campaign_id, content_type, total_row, send_mode, template_id, mim_tpl_id, tpl_params_value, bot_id, file_location ) values ( '$uploadname', '".dbSafe($userid)."', '".dbSafe($department)."', {$sms_text_insert}, {$mim_text_insert}, '".dbSafe($sms_mode)."', '".dbSafe($file_format)."', to_timestamp('".$scheduled_dtm."','DD-MM-YYYY HH24:MI'), '".dbSafe($priority_mode)."', '".dbSafe($callerid)."', '".dbSafe($label)."', '".dbSafe($campaign_id)."', '".dbSafe($content_type)."', '".dbSafe($total_row)."', '".dbSafe($send_mode)."', '".$tpl_id."', '".strtolower($mim_tpl_id)."', '".$ParamValueOnly."', '".dbSafe($bot_id)."', '$file_location'  )";
			
		} else {
			
			$sql1 = "insert into broadcast_sms_file ( file_name, upload_by, department, message, mim_message, mode, file_format, priority, callerid, modem_label, campaign_id, content_type, total_row, send_mode, template_id, mim_tpl_id, tpl_params_value, bot_id, file_location ) values ( '$uploadname', '".dbSafe($userid)."', '".dbSafe($department)."', {$sms_text_insert}, {$mim_text_insert}, '".dbSafe($sms_mode)."', '".dbSafe($file_format)."', '".dbSafe($priority_mode)."', '".dbSafe($callerid)."', '".dbSafe($label)."', '".dbSafe($campaign_id)."', '".dbSafe($content_type)."', '".dbSafe($total_row)."', '".dbSafe($send_mode)."', '".$tpl_id."', '".strtolower($mim_tpl_id)."', '".$ParamValueOnly."', '".dbSafe($bot_id)."', '$file_location'  )";
			
		}
		
		//echo $sql1;
		//die;
		
		$result1 = pg_query($dbconn, $sql1);
	
		if( $result1 ){
			
			//insertBroadCast_temp($userid,$department,$priority_mode,$uploadfile,$file_format,$sms_mode,$content_type,$sms_text,$scheduled,$scheduled_dtm, $broadcast_sms_file_id);
			
		}
		
	}else{
		
		$uplodaStatus = 0;
		$uploadname = "NA";
	}
	
	insertAuditTrail( "Send Message By File Upload V2".$uploaded_files_url_2['status']);

	echo $uplodaStatus;
  
}
function UploadFile( $userid,$department,$priority_mode,$uploadfile,$file_format,$sms_mode,$content_type,$sms_text,$scheduled,$scheduled_dtm, $campaign_id, $callerid, $send_mode, $bot_id, $tpl_type, $tpl_id, $mim_params, $files, $mim_file_type )
{
	global $dbconn;
	$ori_sms_text = $sms_text;
	
	if( $mim_file_type == "1" ){
		$file_type_txt = "image";
	}elseif( $mim_file_type == "2" ){
		$file_type_txt = "pdf";
	}else{
		$file_type_txt = "image";
	}
	
	if( isset($files["mim_image1"]) ){
				
		$location_to_save = "images/mim_uploaded/";
		$uploaded_files_url = check_upload_file( $files['mim_image1'], $file_type_txt, $location_to_save );//new version from 11 August 2021
	
		//echo '<pre>'; print_r( $files['mim_image1'] ); echo '</pre>';
		//echo '<pre>'; print_r($uploaded_files_url); echo '</pre>';
		//echo "mim_file_type edwin ==" . $mim_file_type;
		//die;
			
		//$uploaded_files_url = upload_files( $files, $mim_file_type );//old version
		
		if( $uploaded_files_url['status'] == "1" ){
			$file_location = $uploaded_files_url['file_location'];
		}else{
			$file_location = "";
		}
	
	}
	
	//echo "edwin.......";
	//die;
	
	if( $tpl_type == "yes" ){//mean is mim template
		
		$split1 = explode( "@@", $mim_params );
		
		foreach( $split1 as $key => $value ){
			
			$split2 = explode( "==", $value );
			
			$ParamName = "<" . $split2[0] . ">";
			$ParamValue = $split2[1];
			
			$sms_text = str_replace( $ParamName, $ParamValue ,$sms_text );
			
			if( $ParamValueOnly == "" ){
				$ParamValueOnly = $split2[1];
			}else{
				$ParamValueOnly = $ParamValueOnly . "@@@". $split2[1];
			}
	
		}
		
	}
	
	//start excel csv
	$uplodaStatus = 0;
	
	$location_to_save_2 = "../broadcast/images/";
	$uploaded_files_url_2 = check_upload_file( $files['upload_file'], "excel_csv", $location_to_save_2 );//new version from 11 August 2021
	//echo '<pre>'; print_r($files); echo '</pre>';
	//echo '<pre>'; print_r($uploaded_files_url_2); echo '</pre>';
	//die;
			
	//get callerid label name
	//$label = getCallerIDLabel( $callerid );//not use for MOM
	$label = $callerid;//MOM label is same callerid
	
	if( $uploaded_files_url_2['status'] == "1" ){
		
		$uplodaStatus = 1;
		
		$uploadname = $uploaded_files_url_2['new_file_name'];
		$uploadfile = "../broadcast/images/".$uploadname;
		
		$excel_ext = array( "xls", "xlsx" );
		$FileType = strtolower(pathinfo($_FILES['upload_file']['name'],PATHINFO_EXTENSION));
		
		if( in_array( $FileType, $excel_ext ) ){
			$total_row = getExcelTotalRow( $uploadfile );//method for excel
		}else{
			//$total_row = count(file( $uploadfile ));//method for csv, old method
			$total_row = getExcelTotalRow( $uploadfile );//method for excel, use PHPExcel to get csv total row
		}
		
		//echo "total_row: " . $total_row;
		//die;
	
		//unset un-used value
		if( $send_mode == "sms_mim" || $send_mode == "mim" ){
			
			if( $content_type == "1" ){//message base on smstext and param( for mim template) key in by user
				
			}elseif( $content_type == "2" ){//message base on smstext and param( for mim template) from csv file
				
				$mim_params = $ParamValueOnly = "";
				
				$sms_text = $ori_sms_text;
				
			}elseif( $content_type == "3" ){//dont have smstext and param, full msg from csv file. mean not is mim template selected
				
				$sms_text = $tpl_type = $tpl_id = $mim_params = $ParamValueOnly = "";
				
			}
			
			if( $tpl_id ){
				
				$tpl_detail = getTplDetail( $tpl_id );
				if( $tpl_detail['mim_tpl_id'] ){
					$mim_tpl_id = $tpl_detail['mim_tpl_id'];
				}else{
					$mim_tpl_id = "";
				}
			
			}
			
		}else{
			
			$ParamValueOnly = $mim_params = $tpl_id = $tpl_type = $bot_id = "";
			
			if( $content_type == "1" ){
				
			}elseif( $content_type == "2" ){
			
			}elseif( $content_type == "3" ){
				
				$sms_text = "";
			}
			
		}
		
		//echo "bot_id: $bot_id | tpl_type: $tpl_type | tpl_id: $tpl_id | mim_params: $mim_params | send_mode: $send_mode | content_type: $content_type | ParamValueOnly: $ParamValueOnly";
		//die;
		
		$sms_text = preg_replace("/^[\n\r\s\t]+/", "", $sms_text);
		$sms_text = preg_replace("/[\n\r\s\t]+$/", "", $sms_text);
		$sms_text_insert = pg_escape_literal($sms_text);
			
		if($scheduled == 1) {
			
			$sql1 = "insert into broadcast_sms_file ( file_name, upload_by, department, message, mode, file_format, scheduled_dtm, priority, callerid, modem_label, campaign_id, content_type, total_row, send_mode, template_id, mim_tpl_id, tpl_params_value, bot_id, file_location ) values ( '$uploadname', '".dbSafe($userid)."', '".dbSafe($department)."', {$sms_text_insert}, '".dbSafe($sms_mode)."', '".dbSafe($file_format)."', to_timestamp('".$scheduled_dtm."','DD-MM-YYYY HH24:MI'), '".dbSafe($priority_mode)."', '".dbSafe($callerid)."', '".dbSafe($label)."', '".dbSafe($campaign_id)."', '".dbSafe($content_type)."', '".dbSafe($total_row)."', '".dbSafe($send_mode)."', '".$tpl_id."', '".strtolower($mim_tpl_id)."', '".$ParamValueOnly."', '".dbSafe($bot_id)."', '$file_location'  )";
			
		} else {
			
			$sql1 = "insert into broadcast_sms_file ( file_name, upload_by, department, message, mode, file_format, priority, callerid, modem_label, campaign_id, content_type, total_row, send_mode, template_id, mim_tpl_id, tpl_params_value, bot_id, file_location ) values ( '$uploadname', '".dbSafe($userid)."', '".dbSafe($department)."', {$sms_text_insert}, '".dbSafe($sms_mode)."', '".dbSafe($file_format)."', '".dbSafe($priority_mode)."', '".dbSafe($callerid)."', '".dbSafe($label)."', '".dbSafe($campaign_id)."', '".dbSafe($content_type)."', '".dbSafe($total_row)."', '".dbSafe($send_mode)."', '".$tpl_id."', '".strtolower($mim_tpl_id)."', '".$ParamValueOnly."', '".dbSafe($bot_id)."', '$file_location'  )";
			
		}
		
		//echo $sql1;
		//die;
		
		$result1 = pg_query($dbconn, $sql1);
	
		if( $result1 ){
			
			//insertBroadCast_temp($userid,$department,$priority_mode,$uploadfile,$file_format,$sms_mode,$content_type,$sms_text,$scheduled,$scheduled_dtm, $broadcast_sms_file_id);
			
		}
		
	}else{
		
		$uplodaStatus = 0;
		$uploadname = "NA";
	}
	
	insertAuditTrail( "Send Message By File Upload" );

	echo $uplodaStatus;
  
}

function listBroadCast($userid, $department)
{
	global $dbconn,$lang;

	$msgstr = GetLanguage("upload_broadcast_sms",$lang);
	$correct = (string)$msgstr->correct;
	$wrong = (string)$msgstr->wrong;

	$sqlcmd = "select priority,broadcast_id,mobile_numb,message,file_format,to_char(scheduled_dtm, 'DD/MM/YYYY HH24:MI') as scheduled_dtm from broadcast_sms
				where lower(userid)='".pg_escape_string($userid)."' order by file_format, broadcast_id";
	$result = pg_query($dbconn, $sqlcmd);

	if(is_string($result)) {
		error_log("Database Error (" .$sqlcmd. ") -- " .pg_last_error($dbconn));
	} else {
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			array_push($result_array,Array(
				$i,
				htmlspecialchars($row['mobile_numb']),
				htmlspecialchars($row['message']),
				($row['file_format'] == '0' ? $correct : $wrong),
				$row['priority'],
				$row['scheduled_dtm']
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function sendBroadCast($userid, $department)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$sendSMS_msg1 = (string)$msgstr->sendSMS_msg1;
	$sendSMS_msg2 = (string)$msgstr->sendSMS_msg2;
	$db_err = (string)$msgstr->db_err;

	$error = 0;
	$sent_sms = 0;
	$quota_msg="";
	$label = getLabel($department);
	$msg_from = $userid. " (".$_SERVER['REMOTE_ADDR'].")";

	$sqlcmd = "select priority,broadcast_id,mobile_numb,message,mode,scheduled_dtm from broadcast_sms where lower(userid)='".$userid."'";
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row)) {
		error_log($db_err. " (".$sqlcmd.") -- ".pg_last_error($dbconn));
		$error++;
	} else {
		for($a=0; $a<count($row); $a++)
		{
			$mobile_numb = trim($row[$a]['mobile_numb']);
			$message = trim($row[$a]['message']);
			$mode = trim($row[$a]['mode']);
			$priority = trim($row[$a]['priority']);
			$id = trim($row[$a]['broadcast_id']);
			$scheduled_dtm = trim($row[$a]['scheduled_dtm']);

			$match = checkUnsubMobile($mobile_numb);
			if($match){
				if($unsub != "") {
					$unsub .= ", ".$mobile_numb;
				} else {
					$unsub = $mobile_numb;
				}
			}

			//Check Quota
			$check_quota_type = checkQuotaType($userid, $dbconn);
			$check_unlimited_quota = checkQuotaUnlimited($userid, $dbconn);
			$check_quota = 0;

			if($check_unlimited_quota != 1) {
				$check_quota = checkQuota($userid, $dbconn);
				if( $check_quota == 0 ) {
					pg_query($dbconn,"delete from broadcast_sms where broadcast_id='".$id."'");
				 	$quota_msg = $sendSMS_msg1;
			 		$error++;
				} else {
					$new_quota = $check_quota - 1;
					$sql = "update quota_mnt set quota_left='".$new_quota."' where userid='".$userid."'";
					$result = doSQLcmd($dbconn,$sql);
		  			
		  			if(!$result){
						error_log($sqlcmd.' -- '.pg_last_error($dbconn));
						$error++;
					}
				}
			}

			if( ($check_quota > 0 || $check_unlimited_quota == 1) && $match == 0 ){

				$t = getSQLresult($dbconn, "select nextval('message_trackid') as trackid");
				$trackid = $_SESSION['server_prefix'].date('His').$t[0]['trackid'];
				$message = pg_escape_literal($message);

				if (empty($scheduled_dtm)) {
					$outgoing_id = getSequenceID($dbconn,'outgoing_logs_outgoing_id_seq');
					$sqlcmd = "insert into outgoing_logs (outgoing_id,priority,trackid,sent_by,department,mobile_numb,message,message_status,modem_label)
								values ('".$outgoing_id."',".$priority.",'".$trackid."','".$userid."','".$department."','".$mobile_numb."',{$message},'P','".$label."')";
					$res = doSQLcmd($dbconn, $sqlcmd);

					if($res > 0) {
						$response = internal_post($mobile_numb, $message, $mode, $priority, $msg_from, $trackid, $label);
						
						if(empty($response)) {
							$updatesql = "update outgoing_logs set message_status='F',completed_dtm='now()' where trackid='".$trackid."'";
							$update = doSQLcmd($dbconn, $updatesql);
							$error++;
						} else {
							$sent_sms++;
						}
					} else {
						$error++;
					}
				} else {
					$sqlcmd = "insert into scheduled_sms (scheduled_id,priority_status,trackid,department,mobile_numb,message,character_set,sent_by,scheduled_time,created_by,modem_label) values (
								'".getSequenceID($dbconn,'scheduled_sms_scheduled_id_seq')."','".$priority."','".$trackid."',
								'".$department."','".pg_escape_string($mobile_numb)."',{$message},'".$mode."',
								'".pg_escape_string($msg_from)."',to_timestamp('".$scheduled_dtm."','YYYY-MM-DD HH24:MI'),
								'".$userid."','".pg_escape_string($label)."') ";
					$insert = pg_query($dbconn, $sqlcmd);
					
					if ($insert) {
						$sent_sms++;
					} else {
						$error++;
					}
				}
			}

			pg_query($dbconn,"delete from broadcast_sms where broadcast_id='".$id."';");
		}
	}

	$val = array();
	if($error > 0) {
		$val['output'] = $error. $sendSMS_msg2.(!empty($quota_msg)?"<br>".$quota_msg:"");
		$val['error'] = "1";
	} else {
		$msgstr2 = GetLanguage("send_sms",$lang);
		$val['output'] = $sent_sms." ".(empty($scheduled_dtm)?$msgstr2->alert_7:$msgstr2->alert_8);
		$val['error'] = "0";
	}

	echo json_encode($val);
}

function internal_post($mobile_numb, $msg_content, $mode, $priority, $msg_from, $trackid, $label)
{
	global $dbconn;

  	$sqlcmd = "INSERT INTO webapp_sms (msgid, mobile_numb, msg_content, mode, priority, msg_from, msg_status, label)
				VALUES ('$trackid', '$mobile_numb', {$msg_content}, '$mode', '$priority', '$msg_from', 'W','$label') ";
	$result = pg_query($dbconn, $sqlcmd);
	
	if(!$result) {
		error_log("Database Error (" .$sqlcmd. ") -- ".pg_last_error($dbconn));
		return 0;
	} else {
		return pg_affected_rows($result);
	}
}

function deleteBroadCast($userid)
{
	global $dbconn, $lang;

	$msgstr = GetLanguage("lib_send_sms",$lang);
	$db_err = (string)$msgstr->db_err;

	$sqlcmd = "delete from broadcast_sms where lower(userid)='".$userid."'";
	$row = pg_query($dbconn, $sqlcmd);

	if(is_string($row)) {
		error_log($db_err. " (" .$sqlcmd. ") -- " .pg_last_error($dbconn));
	}
}

function getUnsubList()
{
	global $dbconn;

	$sqlcmd = "select mobile_numb from unsubscribe_list order by unsubscribe_id";
	$result = getSQLresult($dbconn, $sqlcmd);
	if(!$result) {
		return;
	}
	return $result;
}

function matchNumb($target, $num)
{
	if( strlen($target) == strlen($num) ){
		if( $target == $num ){
			return 1;
		}
	} else if ( strlen($num) < strlen($target) ){
		$pos = strlen($target) - strlen($num);
		$tmp = substr($target, $pos, strlen($num));

		if( $num ==  $tmp ){
			return 1;
		}
	} else if ( strlen($num) > strlen($target) ){
		$pos = strlen($num) - strlen($target);
		$tmp = substr($num, $pos, strlen($target));

		if( $target == $tmp ){
			return 1;
		}
	}
	return 0;
}

function checkUnsubMobile($mobile_numb)
{
	$unsub_list = getUnsubList();
	$match = 0;
	if( count($unsub_list) >= 1 && isset($unsub_list[0]['mobile_numb'])){
		foreach($unsub_list as $key){
			$target = $key['mobile_numb'];
			$match = matchNumb($target, $mobile_numb);
			if($match == 1) {
				return 1;
			}
		}
	}
	return 0;
}

function checkQuotaType($userid,$conn)
{
	$sqlcmd = "select topup_frequency from quota_mnt where lower(userid)='".$userid."'";
	$result = pg_query($conn,$sqlcmd);
	$arr = pg_fetch_all($result);
	return $arr[0]['topup_frequency'];
}

function checkQuotaUnlimited($userid, $dbconn)
{
	$sqlcmd = "select unlimited_quota from quota_mnt where lower(userid)='".$userid."'";
	$result = pg_query($dbconn, $sqlcmd);
	$arr = pg_fetch_all($result);
	return $arr[0]['unlimited_quota'];
}

function checkQuota($userid,$conn)
{
	$sqlcmd = "select quota_left from quota_mnt where lower(userid)='".$userid."'";
	$result = pg_query($conn,$sqlcmd);
	$arr = pg_fetch_all($result);
	return $arr[0]['quota_left'];
}

function getLabel($department)
{
	global $dbconn;
	$sqlcmd = "select modem_label from modem_dept where dept='".$department."'";
	$result = pg_query($dbconn,$sqlcmd);
	$arr = pg_fetch_all($result);
	return trim($arr[0]['modem_label']);
}

function getCallerIDLabel($callerid)
{
	global $spdbconn;
	$sqlcmd = "select label from asp_route where callerid='".$callerid."'";
	$result = pg_query($spdbconn,$sqlcmd);
	$arr = pg_fetch_all($result);
	return trim($arr[0]['label']);
}

function getTplDetail( $tpl_id ){
	
	global $dbconn;
	
	$sqlcmd = "select * from message_template where template_id = '$tpl_id'";
	
	$row = getSQLresult($dbconn, $sqlcmd);
	
	return $row[0];
	
}

/*
function upload_files( $files, $mim_file_type ){

	$file = $files['mim_image1'];
	$FileType = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
	
	$getcwd = basename(getcwd());
	$mainURL = "https://" . $_SERVER['HTTP_HOST'] . "/$getcwd/";
	//$mainURL = "https://stagingmom.sendquickasp.com/mom/";//for test, bcoz dont have domain

	$target_dir = "images/mim_uploaded/";
	$target_file = $target_dir . date("YmdHIs") . mt_rand(100000, 999999) . "." . $FileType;
	
	if( $mim_file_type == "1" ){
		$allowed_image = array( "jpg", "jpeg", "png" );
	}elseif( $mim_file_type == "2" ){
		$allowed_image = array( "pdf" );
	}else{
		$allowed_image = array( "jpg", "jpeg", "png" );
	}
	
	//print_r( $allowed_image );
	//die;
	
	$check = getimagesize( $file["tmp_name"] );
	
	//if( $check ) {//is image
		
		if ( $file["size"] < 5000000 ) {//5mb
		//if ( $file["size"] < 500000 ) {//500kb
			
			//check extension
			if( in_array( $FileType, $allowed_image ) ){//valid extension
				
				if ( move_uploaded_file( $file["tmp_name"], $target_file ) ) {//saved
					
					//echo "uploaded";
					//die;
					
					$returns['status'] = "1";
					$returns['message'] = "Uploaded";
					$returns['file_location'] = $mainURL . $target_file;
					
				}else{//failed save
					
					//echo "failed";
					//die;
					
					$returns['status'] = "2";
					$returns['message'] = "Fail upload file";
					
				}
				
			}else{//invalid extension
				
				$returns['status'] = "3";
				$returns['message'] = "Invalid file extension";
					
			}
		
		}else{//not allowed file size
			
			$returns['status'] = "3";
			$returns['message'] = "Invalid file size";
				
		}
		
	//} else {//not image
		
		//$returns['status'] = "3";
		//$returns['message'] = "Only image jpeg and png allowed.";
				
	//}
	
	return $returns;
	
}
*/

function getExcelTotalRow( $file ){
	
	$FileType = strtolower(pathinfo($file,PATHINFO_EXTENSION));
	if( $FileType == "xlsx" ){
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
	}else{
		
		if( $FileType == "csv" ){
			$objReader = PHPExcel_IOFactory::createReader('CSV');
		}else{
			$objReader = PHPExcel_IOFactory::createReader('Excel5');
		}
		
	}
	
	$objReader->setReadDataOnly(true);

	//$objPHPExcel = $objReader->load("test.xlsx");
	$objPHPExcel = $objReader->load( $file );
	$objWorksheet = $objPHPExcel->getActiveSheet();

	$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
	$highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
	
	return $highestRow;
}
?>
