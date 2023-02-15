<?php
require_once('lib/commonFunc.php');

$userid = strtolower($_SESSION['userid']);
$campaign_id = @$_REQUEST['id'];
$mode = @$_REQUEST['mode'];
$campaign_name = @$_REQUEST['campaign_name'];
$campaign_type = @$_REQUEST['campaign_type'];
$campaign_status = @$_REQUEST['campaign_status'];
$campaign_start_date = @$_REQUEST['campaign_start_date'];
$campaign_end_date = @$_REQUEST['campaign_end_date'];
$keyword = @$_REQUEST['keyword'];

if( $campaign_type == "2" ){
		
	if( $keyword ){
		$keyword = implode( ",", $keyword );
	}
	
}else{
	
	$keyword = "";
}

$x = GetLanguage("campaign_mgnt",$lang);

//$mode = "select";//hardcode for test

switch ($mode) {
	case "list":
        list_campaign();
        break;
	case "add":
        add_campaign($campaign_name, $campaign_type, $campaign_status, $campaign_start_date, $campaign_end_date, $keyword);
        break;
	case "edit":
        edit_campaign($campaign_id);
        break;
	case "save":
		 save_campaign($campaign_id,$campaign_name, $campaign_type, $campaign_status, $campaign_start_date, $campaign_end_date, $keyword);
        break;
	case "delete":
        delete_campaign($campaign_id);
        break;
	case "get":
        get_campaign();
        break;
	case "select":
        select_campaign();
        break;
	case "KeywordList":
		KeywordList( $campaign_id );
        break;
    default:
        die('Invalid Command');
}

function KeywordList( $campaign_id ){
	
	global $dbconn, $x, $userid;
	$returns = "";
	
	$sqlcmd = "select * from mom_sms_response where department = '".dbSafe( $_SESSION["department"] )."' order by keyword asc";
	$result = pg_query($dbconn, $sqlcmd);
	if( $result ){
		
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
		
			if( $campaign_id > 0 ){
				
				//find this campagin_id's keyword
				$current_keyword_id = "";
				$sql1 = "select keyword from campaign_mgnt where campaign_id = '".$campaign_id."'";
				$row1 = getSQLresult($dbconn, $sql1);
				$current_keyword_id = explode( ",", $row1[0]['keyword'] );
				
				if( in_array( $row['id'], $current_keyword_id ) ){
					$show = "yes";
				}else{
					
					if( $row['in_use_status'] == "yes" ){
						$show = "no";//in use, can not show to select
					}else{
						$show = "yes";
					}
				
				}
				
			}else{
				
				if( $row['in_use_status'] == "yes" ){
					$show = "no";//in use, can not show to select
				}else{
					$show = "yes";
				}
			
			}
			
			//echo $show . " | " . $row['keyword'] . " | " . $sql1;
			//die;
			
			if( $show == "yes" ){
				
				if( $returns == "" ){
					
					$returns = "<input type = 'checkbox' name = 'keyword[]' id = 'keyword[]' value = '".$row['id']."'>&nbsp;&nbsp;" . $row['keyword'];
					
				}else{
					
					$returns = $returns . "<br>" . "<input type = 'checkbox' name = 'keyword[]' id = 'keyword[]' value = '".$row['id']."'>&nbsp;&nbsp;" . $row['keyword'];
					
				}
			
			}
			
		}
	
	}
	
	if( $returns == "" ){
		$returns = "-";
	}
	
	echo $returns;
	
}

function list_campaign(){
	
	global $dbconn, $x, $userid;
	
	if( isUserAdmin( $_SESSION['userid'] ) ){
		$sqlcmd = "select * from campaign_mgnt";
	}else{
		$sqlcmd = "select * from campaign_mgnt where cby = '".dbSafe($userid)."'";
	}
	
	$result = pg_query($dbconn, $sqlcmd);
	if(!$result) {
		echo $x->failed_list_campaign;
	} else {
		$result_array = array();
		for ($i=1; $row = pg_fetch_array($result); $i++){
			
			$campaign_type = "N/A";
			if( $row['campaign_type'] == "1" ){
				$campaign_type = "Broadcast";
				$campaign_start_date = $campaign_end_date = "N/A";
			}elseif( $row['campaign_type'] == "2" ){
				$campaign_type = "Interactive";
				$campaign_start_date = date( "d-m-Y", strtotime( $row['campaign_start_date'] ) );
				$campaign_end_date = date( "d-m-Y", strtotime( $row['campaign_end_date'] ) );
			}
			
			$keywords = "";
			if(isset($row['keyword']) && $row['keyword'] != ""){
				//get keyword
				$sql1 = "select keyword from mom_sms_response where id in ( ".$row['keyword'] ." ) order by keyword asc";
				$result1 = pg_query($dbconn, $sql1);
				for ($i=1; $row1 = pg_fetch_array($result1); $i++){
					
					if( $keywords == "" ){
						$keywords = $row1['keyword'];
					}else{
						$keywords = $keywords . "," . $row1['keyword'];
					}
					
				}
			}			
		
			$info = "
						$x->campaign_start_date: $campaign_start_date<br>
						$x->campaign_end_date: $campaign_end_date<br>
						
						$x->keyword: $keywords<br>
						";
			
			//check have in use or not or expired
			$now = date("Y-m-d H:i:s");
			$campaign_start_date1 = $campaign_start_date . " 00:00:00";
			$campaign_end_date1 = $campaign_end_date . " 00:00:00";
			$survey_sent = true;
			
			//check have sent survey
			$total_sent = 0;
			$sql1 = "select count(*) as total from campagin_survey_outbox where campagin_id = '".$row['campaign_id']."'";
			$row1 = getSQLresult($dbconn, $sql1);
			$total_sent = $row1[0]['total'];
			if( $total_sent > 0 ){
				$survey_sent = "yes";
			}else{
				$survey_sent = "no";
			}
			
			if( $row["campaign_type"] == "2" && $row["keyword"] && strtotime( $campaign_start_date1 ) <= strtotime( $now ) && strtotime( $campaign_end_date1 ) >= strtotime( $now ) && $survey_sent == "yes" ){
				
				$edit = '<a href="#myModal" data-bs-toggle="modal" data-id="'.$row['campaign_id'].'">'.htmlspecialchars($row['campaign_name'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>';
				//$edit = $row['campaign_name'];
				$in_use = "Yes";
				

			}else{
				
				$edit = '<a href="#myModal" data-bs-toggle="modal" data-id="'.$row['campaign_id'].'">'.htmlspecialchars($row['campaign_name'],ENT_QUOTES).' <i class="fa fa-pencil-square-o fa-fw"></i></a>';
					
				$in_use = "No";
				
			}
			
			$campaign_status = "Status: " . $row['campaign_status'] . "<br>" . "Keyword In used: " . $in_use . "<br>";
			
			$cby = $row['cby'];
			$department = getUserDepartment2( $row['cby'] );
			if( strtolower($row['cby']) == strtolower($_SESSION["userid"]) ){
				// assmi
				// $delete = '<input type="checkbox" id="no" name="no" value="'.$row['campaign_id'].'">';
				$delete = '<input type="checkbox" class="user_checkbox" id="no" name="no" value="'.$row['campaign_id'].'">';
				// assmi
			}else{
				$delete = "";
			}
			
			array_push($result_array,Array(
				$edit,
				htmlspecialchars($campaign_type),
				$campaign_status,
				$info,
				$cby,
				$department,
				$delete
				//'<input type="checkbox" id="no" name="no" value="'.$row['campaign_id'].'">'
			));
		}
		echo json_encode(Array("data"=>$result_array));
	}
}

function add_campaign($campaign_name, $campaign_type, $campaign_status, $campaign_start_date, $campaign_end_date, $keyword){
	
	global $dbconn, $x, $userid;
	$data = array();

	if(!txvalidator($campaign_type,TX_INTEGER)){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_campaign_type;
		$data['field'] = "campaign_type";
		echo json_encode($data);
		die;
	}

	if(!txvalidator($campaign_name,TX_STRING,"-_")){
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_campaign_name;
		$data['field'] = "campaign_name";
		echo json_encode($data);
		die;
	}

	if(!validateSize($x->name,$campaign_name,"NAME")){
		$data['flag'] = 0;
		$data['status'] = (string)getValidateSizeMsg();
		$data['field'] = "campaign_name";
		echo json_encode($data);
		die;
	}

	if($campaign_type == "2"){		
		if(!validDate($campaign_start_date)){
			$data['flag'] = 0;
			$data['status'] = (string)$x->invalid_campaign_start_date;
			$data['field'] = "campaign_start_date";
			echo json_encode($data);
			die;
		}

		if(!validDate($campaign_end_date)){
			$data['flag'] = 0;
			$data['status'] = (string)$x->invalid_campaign_end_date;
			$data['field'] = "campaign_end_date";
			echo json_encode($data);
			die;
		}

		if(!checkTodayDate($campaign_start_date)){
			$data['flag'] = 0;
			$data['status'] = (string)$x->failed_campaign_setdate;
			$data['field'] = "campaign_start_date";
			echo json_encode($data);			
			die;
		}

		if(!checkTodayDate($campaign_end_date)){
			$data['flag'] = 0;
			$data['status'] = (string)$x->failed_campaign_setdate;
			$data['field'] = "campaign_end_date";
			echo json_encode($data);
			die;
		}

		if(!checkDateDiff($campaign_start_date, $campaign_end_date)){
			$data['flag'] = 0;
			$data['status'] = (string)$x->failed_campaign_date;
			$data['field'] = "campaign_end_date";
			echo json_encode($data);			
			die;
		}
	}
	
	if( $campaign_type == "1"  ){
		$campaign_start_date = $campaign_end_date = "NULL";
	}elseif( $campaign_type == "2"  ){
		$campaign_start_date = "'".date( "Y-m-d", strtotime( $campaign_start_date ) )."'";
		$campaign_end_date = "'".date( "Y-m-d", strtotime( $campaign_end_date ) )."'";
	}
	
	$dup_cam_name_id = 0;
	$dup_cam_name_cby = "";
	$sql0 = "select campaign_id, cby from campaign_mgnt where campaign_name = '".dbSafe($campaign_name)."'";
	$result0 = pg_query($dbconn, $sql0);
	for ($i=1; $row0 = pg_fetch_array($result0); $i++){
		$dup_cam_name_id = $row0['campaign_id'] ? $row0['campaign_id'] : 0;
		$dup_cam_name_cby = $row0['cby'];
		
		$dup_cam_name_cby_dpt = getUserDepartment( $dup_cam_name_cby );
		
	}
	
	if( $dup_cam_name_id > 0 && $dup_cam_name_cby_dpt == $_SESSION['department'] ){
		$data['flag'] = 2;
		$data['status'] =  (string)$x->duplicated_campaign_name;
		//echo $dup_cam_name_cby_dpt;		
	}else{
		
		$sqlcmd = "insert into campaign_mgnt 
					(campaign_name, campaign_type, cby, campaign_status, campaign_start_date, campaign_end_date, keyword ) 
					values ( '".dbSafe($campaign_name)."', '".dbSafe($campaign_type)."', 
					'".dbSafe($userid)."', '".dbSafe($campaign_status)."', $campaign_start_date, $campaign_end_date, '$keyword' )";
		$row = doSQLcmd($dbconn, $sqlcmd);
		
		if($row != 0){
			
			//echo $sqlcmd;
			$sql2 = "update mom_sms_response set in_use_status = 'yes' where id in ( $keyword ) ";
			$row2 = doSQLcmd($dbconn, $sql2);
			$data['flag'] = 1;
		} else {
			//echo $sqlcmd;
			//die;
			$data['flag'] = 2;
			$data['status'] =  (string) $x->failed_create_campaign;
		}
	
		insertAuditTrail( "Add new campaign" );		
	}

	echo json_encode($data);
	
}

function edit_campaign($campaign_id){
	
	global $dbconn, $x, $userid;

	$sqlcmd = "select * from campaign_mgnt where campaign_id = '".dbSafe($campaign_id)."'";
	
	$row = getSQLresult($dbconn, $sqlcmd);

	if(is_string($row)) {
		
		echo $x->failed_list_campaign;
		
	} else {
		
		if(empty($row)){
			
			echo $x->failed_list_campaign;
			
		}
		else{
			
			$result_array = array();
			
			if( strlen( $row[0]['campaign_start_date'] ) > 0 ){
				$campaign_start_date = date( "d-m-Y", strtotime( $row[0]['campaign_start_date'] ) );
			}else{
				$campaign_start_date = "";
			}
			
			if( strlen( $row[0]['campaign_end_date'] ) > 0 ){
				$campaign_end_date = date( "d-m-Y", strtotime( $row[0]['campaign_end_date'] ) );
			}else{
				$campaign_end_date = "";
			}
			
			$keywords = $row[0]['keyword'];
			
			//check have sent survey
			$total_sent = 0;
			$sql1 = "select count(*) as total from campagin_survey_outbox where campagin_id = '".$campaign_id."'";
			$row1 = getSQLresult($dbconn, $sql1);
			$total_sent = $row1[0]['total'];
			if( $total_sent > 0 ){
				$survey_sent = "yes";
			}else{
				$survey_sent = "no";
			}
			
			$result_array['campaign_name'] = $row[0]['campaign_name'];
			$result_array['campaign_type'] = $row[0]['campaign_type'];
			$result_array['campaign_status'] = $row[0]['campaign_status'];
			$result_array['campaign_start_date'] = $campaign_start_date;
			$result_array['campaign_end_date'] = $campaign_end_date;
			$result_array['keywords'] = $keywords;
			$result_array['survey_sent'] = $survey_sent;
			
			echo json_encode($result_array);
		}
	}

}

function save_campaign($campaign_id, $campaign_name, $campaign_type, $campaign_status, $campaign_start_date, $campaign_end_date, $keyword){
	
	global $dbconn, $x, $userid;
	$data = array();
	
	if(!txvalidator($campaign_type,TX_INTEGER)){		
		$data['flag'] = 0;
		$data['status'] = (string)$x->invalid_campaign_type;
		$data['field'] = "campaign_type";
		echo json_encode($data);
		die;
	}

	if($campaign_type == "2"){		
		if(!validDate($campaign_start_date)){
			$data['flag'] = 0;
			$data['status'] = (string)$x->invalid_campaign_start_date;
			$data['field'] = "campaign_start_date";
			echo json_encode($data);
			die;
		}

		if(!validDate($campaign_end_date)){
			$data['flag'] = 0;
			$data['status'] = (string)$x->invalid_campaign_end_date;
			$data['field'] = "campaign_end_date";
			echo json_encode($data);
			die;
		}

		if(!checkTodayDate($campaign_start_date)){
			$data['flag'] = 0;
			$data['status'] = (string)$x->failed_campaign_setdate;
			$data['field'] = "campaign_start_date";
			echo json_encode($data);			
			die;
		}

		if(!checkTodayDate($campaign_end_date)){
			$data['flag'] = 0;
			$data['status'] = (string)$x->failed_campaign_setdate;
			$data['field'] = "campaign_end_date";
			echo json_encode($data);
			die;
		}

		if(!checkDateDiff($campaign_start_date, $campaign_end_date)){
			$data['flag'] = 0;
			$data['status'] = (string)$x->failed_campaign_date;
			$data['field'] = "campaign_end_date";
			echo json_encode($data);			
			die;
		}
	}

	//Broadcast dont have keyword
	if( $campaign_type == "1" ){
		$keyword = "";
	}else{
		
		//get old keyword id to unset
		$old_keyword = "";
		$sql1 = "select keyword from campaign_mgnt where campaign_id = '".dbSafe($campaign_id)."'";
		$row1 = getSQLresult($dbconn, $sql1);
		$old_keyword = $row1[0]['keyword'];
		
		//echo "old_keyword: " . $old_keyword . " | keyword" . $keyword;
		//die;
		
		if( $old_keyword ){
			
			$sql3 = "update mom_sms_response set in_use_status = 'no' where id in ( $old_keyword ) ";
			$row3 = doSQLcmd($dbconn, $sql3);
			
		}
	}
	
	//$sqlcmd = "update campaign_mgnt set campaign_name = '".dbSafe($campaign_name)."', campaign_type = '".dbSafe($campaign_type)."', modified_dtm = now(), modified_by = '".dbSafe($userid)."', campaign_status = '".dbSafe($campaign_status)."', keyword = '".dbSafe($keyword)."', campaign_start_date = '".date( "Y-m-d", strtotime( $campaign_start_date ) )."', campaign_end_date = '".date( "Y-m-d", strtotime( $campaign_end_date ) )."' where campaign_id = '".dbSafe($campaign_id)."'";
	$sqlcmd = "update campaign_mgnt set campaign_type = '".dbSafe($campaign_type)."', modified_dtm = now(), modified_by = '".dbSafe($userid)."', campaign_status = '".dbSafe($campaign_status)."', keyword = '".dbSafe($keyword)."', campaign_start_date = '".date( "Y-m-d", strtotime( $campaign_start_date ) )."', campaign_end_date = '".date( "Y-m-d", strtotime( $campaign_end_date ) )."' where campaign_id = '".dbSafe($campaign_id)."'";
	
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if($row != 0){		
		//echo $sqlcmd;
		$sql2 = "update mom_sms_response set in_use_status = 'yes' where id in ( $keyword ) ";
		$row2 = doSQLcmd($dbconn, $sql2);
		$data['flag'] = 1;
	} else {
		$data['flag'] = 2;
		$data['status'] = (string)$x->failed_edit_campaign;
	}
	
	insertAuditTrail( "Edited campaign" );
	echo json_encode($data);	
}

function delete_campaign($campaign_id){
	
	global $dbconn, $x, $userid;

	$sqlcmd = "delete from  campaign_mgnt where campaign_id = '".dbSafe($campaign_id)."'";
	$row = doSQLcmd($dbconn, $sqlcmd);
	
	if($row != 0){
		
		//echo $sqlcmd;
		
	} else {
		echo $x->failed_delete_campaign;
	}
	
	$sqlcmd2 = "delete from  outgoing_logs where campaign_id = '".dbSafe($campaign_id)."'";
	$row2 = doSQLcmd($dbconn, $sqlcmd2);
	
	insertAuditTrail( "Deleted campaign" );
}

function get_campaign(){
	
	global $dbconn, $x, $userid;

	$sqlcmd = "select * from campaign_mgnt where cby = '".dbSafe($userid)."' and campaign_status = 'active'";
	
	$row = getSQLresult($dbconn, $sqlcmd);

	if(empty($row)) {
		echo $x->failed_list_campaign;
	} else {
		echo json_encode($row);
	}
	
}

function select_campaign(){
	
	global $dbconn, $x, $userid;
	
	$now = date("Y-m-d");
	$sqlcmd = "select * from campaign_mgnt where cby = '".dbSafe($userid)."' and campaign_status = 'active' and ( ( campaign_type = '2' and campaign_start_date <= '".dbSafe($now)."' and campaign_end_date >= '".dbSafe($now)."' ) or ( campaign_type = '1' ) ) and campaign_id not in ( select campaign_id from broadcast_sms_file ) and used_flag = '0'";

	$row = getSQLresult($dbconn, $sqlcmd);

	if(empty($row)) {
		echo $x->failed_list_campaign;
	} else {
		echo json_encode($row);
	}
	
}
?>
