<?php
	$page_mode = '7';
	$page_title = 'File Upload Status';
	include('header.php');
	include('checkAccess.php');
	$id = $_GET['id'];
?>
		<div class="page-header">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->send_sms_upload;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->file_upload_status;?></li>
				</ol>
			</nav>
		</div>

		<?php $x = GetLanguage("file_upload_status",$lang); ?>
		
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						
						<div id="status" class="alert alert-dismissable alert-sm text-center hidden">
						
							<span id="output"></span>
							<button class="close">&times;</button>
						</div>
						
						<div class="row" id = "add_on_div">
							
							<table class="table">
									
								<tr class = "table-danger" id = "add_on_tr">
									<td id = "add_on_td"></td>
								</tr>
								
							 </table>
						
						</div>
							
						<div class="row">
							
							<div class="col-md-10">
					
								<div style = "padding-bottom:10px;">
								
									<ul class="nav nav-tabs">
										<li class="nav-item">
											<a class="nav-link active" href="#" id = "valid_mobile_btn"><?php echo $x->valid_mobile;?></a>
										</li>
										<li class="nav-item">
											<a class="nav-link" href="#" id = "invalid_mobile_btn"><?php echo $x->invalid_mobile;?></a>
										</li>
									</ul>
								
								</div>
							
							</div>
							
							<div class="col-md-2" align = "right">
								<button id="cfm_send" type="submit" class="btn btn-primary btn-sm"><?php echo $x->confirm_send;?></button>
								<button id="back" type="submit" class="btn btn-light btn-sm"><?php echo $x->back; ?></button>
							</div>
							
						</div>
						
						<div class="table-responsive" style = "overflow-x:hidden;margin-top:20px;">
							
							<!--
							<div style = "padding-bottom:10px;">
								<button id="valid_mobile_btn" type="submit" class="btn btn-success btn-sm"><?php echo $x->valid_mobile;?></button>
								<button id="invalid_mobile_btn" type="submit" class="btn btn-danger btn-sm"><?php echo $x->invalid_mobile;?></button>
								<button id="cfm_send" type="submit" class="btn btn-primary btn-sm"><?php echo $x->confirm_send;?></button>
								<button id="back" type="submit" class="btn btn-default btn-sm"><?php echo $x->back; ?></button>
							</div>
							-->

							<div class="row" id = "total_sms_div"></div>
					
							<table class="table table-striped table-bordered table-condensed dataTable" id="upload_file_status" style = "width:100%;">
								<thead>
									<tr>
										<th><?php echo $xml_common->no;?></th>
										<th><?php echo $x->mobile_numb;?></th>
										<th><?php echo $x->message;?></th>
										<th><?php echo $x->mim_message;?></th>
										<th><?php echo $x->error_msg;?></th>
										<th><?php echo $x->total_sms;?></th>
										<th><?php echo $x->total_mim;?></th>
										<!--<th><input type="checkbox" name="all" id="all"></th>-->
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="7">
											<span class="pull-left">
												<input type = "hidden" name = "data_type" id = "data_type" value = "valid">
											</span>
											<span class="pull-right">
												<!--<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete; ?></button>-->
											</span>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal" id="confirmModal" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true" data-backdrop="false">
			<div class="vertical-alignment-helper">
				<div class="modal-dialog modal-sm vertical-align-center">
					<div class="modal-content">
						<div class="modal-body" id = "btn_close_modal_info">

							
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
	<?php
	$get_list = "";
	foreach( $_GET as $get_name => $get_value ){
		if( $get_list == "" ){
			$get_list = $get_name . "=" . $get_value;
		}else{
			$get_list = $get_list . "&" . $get_name . "=" . $get_value;
		}
	}

	if($get_list != ""){
		$get_list = "?" . $get_list;
	}
	?>
	<script src="js/bootstrap-datepicker.min.js"></script>
	<script src="js/bootstrap_confirm_dialog.js"></script>
	<script type="application/javascript" src="broadcast_sms_status_list_js.php<?php echo $get_list;?>"></script>
</body>
</html>
