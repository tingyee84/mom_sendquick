<?php
	//ini_set('display_errors', 1);
	//ini_set('display_startup_errors', 1);
	//error_reporting(E_ALL);
	
	$page_mode = '1';
	$page_title = 'User Transfer';
	include('header.php');
	include('checkAccess.php');
	$error_msg = $msg = "";
	//print_r( $_SESSION['department'] );
	//die;
	if( $_POST ){
		
		$from_userid = trim($_POST['from_userid']);//id
		$to_userid = trim($_POST['to_userid']);//id
	
		if( $from_userid != "" && $to_userid != "" ){
			
			if( $from_userid != $to_userid ){
				
				/*example:
				CA161441640012994 = $to_userid
				test = $ToUserID
				*/
				
				$ToUsername = getuseridByID( $to_userid );//username
				$FromUsername = getuseridByID( $from_userid );//username
				
				$sql[] = "update address_book set created_by = '$ToUsername' where created_by = '$FromUsername'";
				
				$sql[] = "update address_group set created_by = '$ToUsername' where created_by = '$FromUsername'";
				$sql[] = "update address_group_main set created_by = '$ToUsername' where created_by = '$FromUsername'";
				
				$sql[] = "update mom_sms_response set cby = '$ToUsername' where cby = '$FromUsername'";
			
				$sql[] = "update message_template set user_id = '$to_userid' where user_id = '$from_userid'";
				
				$sql[] = "update message_template set created_by = '$ToUsername' where created_by = '$FromUsername'";
				
				$sql[] = "update campaign_mgnt set cby = '$ToUsername' where cby = '$FromUsername'";
				
				insertAuditTrail( "User transfer" );
				
				//print_r( $sql );
				//die;
				
				foreach( $sql as $key => $sql_is ){
					
					$row_is = doSQLcmd($dbconn, $sql_is);
					
					/*
					if( $row_is != 0 ){
						
					}else{
						
						$error_msg = "SQL: $sql_is <br><br>Error: " . pg_last_error($dbconn);
						//error, stop
						break;
					}
					*/
					
				}
				
				if( $error_msg == "" ){
					$msg = "Transfer completed.";
				}
			
			}else{
				$error_msg = "Invalid user account.";
			}
			
		}else{
			
			$error_msg = "Invalid user account.";
		}
		
	}
	
	//die;
?>
		<div class="page-header" style="padding-top:10px">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->user_mgnt;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->user_transfer;?></li>
				</ol>
			</nav>
		</div>
		
		<?php 
		$x = GetLanguage("user_transfer",$lang); 
		?>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div id="status" class="alert alert-dismissable alert-sm text-center hidden">
							<button class="close">&times;</button>
							<span id="output"></span>
						</div>
						
						<form id="transferForm" name="transferForm" method = "post" action = "user_transfer.php">
							
							<?php
							if( $error_msg != ""){
							?>
							<div class="row">
								<div class="col-lg-2"><label class="control-label"></label></div>
								<div class="col-lg-2">
									<font style = "color:red;"><?php echo $error_msg;?></font>
								</div>
							</div>
							<hr/>
							<?php
							}
							?>
							
							<?php
							if( $msg != ""){
							?>
							<div class="row">
								<div class="col-lg-2"><label class="control-label"></label></div>
								<div class="col-lg-2">
									<font style = "color:blue;"><?php echo $msg;?></font>
								</div>
							</div>
							<hr/>
							<?php
							}
							?>
							
							<div class="row">
								<div class="col-lg-2"><label class="control-label"><?php echo $x->from_userid; ?></label></div>
								<div class="col-lg-2">
									<select name="from_userid" id="from_userid" required>
										<option value=""><?php echo $x->no_user;?></option>
									<?php
									$sql1 = "select id,userid from user_list where user_type = 'user' and department = '". $_SESSION['department']."'";
									$result1 = pg_query($dbconn, $sql1);
									for ($i=1; $row = pg_fetch_array($result1); $i++){
									?>
										<option value="<?php echo $row['id'];?>" <?php echo ( $from_userid == $row['id'] ? "selected" : "" ); ?>><?php echo $row['userid'];?></option>
									<?php
									}
									?>
									</select>
								</div>
							</div>
							<hr/>
							
							<div class="row">
								<div class="col-lg-2"><label class="control-label"><?php echo $x->to_userid; ?></label></div>
								<div class="col-lg-2">
									<select name="to_userid" id="to_userid" required>
										<option value=""><?php echo $x->no_user;?></option>
									<?php
									$sql1 = "select id,userid from user_list where user_type = 'user' and department = '". $_SESSION['department']."'";
									$result1 = pg_query($dbconn, $sql1);
									for ($i=1; $row = pg_fetch_array($result1); $i++){
									?>
										<option value="<?php echo $row['id'];?>" <?php echo ( $to_userid == $row['id'] ? "selected" : "" ); ?>><?php echo $row['userid'];?></option>
									<?php
									}
									?>
									</select>
								</div>
							</div>
							<hr/>
							
							<div class="row">
								<div class="col-lg-2"><label class="control-label"></label></div>
								<div class="col-lg-2">
									<button id="transfer_btn" name = "transfer_btn" type="button" class="btn btn-primary"><?php echo $x->transfer;?></button>
								</div>
							</div>

						</form>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal" id="confirmModal" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true" data-backdrop="false">
			<div class="vertical-alignment-helper">
				<div class="modal-dialog modal-sm vertical-align-center">
					<div class="modal-content">
						<div class="modal-body">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close" id="btn_close_modal_info"><span aria-hidden="true">&times;	</span></button>
							<div id="confirmContent" style="font-weight: normal;"></div>
						</div>
						<div class="modal-footer bg-warning text-center" id="footer_modal">
							<button type="button" class="btn btn-primary btn_yes_confirm">Yes</button>
							<button type="button" class="btn btn-primary btn_no_confirm" data-dismiss="modal" aria-label="Close">No</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<?php include('footnote.php'); ?>
	</div>
	<?php include("user_transfer_js.php");?>
	<?php //include('send_sms_js.php'); ?>
</body>
</html>
