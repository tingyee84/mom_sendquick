<?php
	$page_mode = '7';
	$page_title = 'File Upload Status';
	include('header.php');
	include('checkAccess.php');
	
?>
		<div class="page-header">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->send_sms;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->file_upload_status;?></li>
				</ol>
			</nav>
		</div>
		
		<?php $x = GetLanguage("file_upload_status",$lang); ?>
		
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="table-responsive" style = "overflow-x:hidden;">
							
							<div style = "padding-bottom:10px;">
								Date From&nbsp;&nbsp;<input type = "text" name = "date_from" id = "date_from" value = "">
								Date To&nbsp;&nbsp;<input type = "text" name = "date_to" id = "date_to" value = "">
							</div>
							
							<table class="table table-striped table-bordered table-condensed dataTable" id="upload_file_status" style = "width:100%">
								<thead>
									<tr>
										<th><?php echo $x->file_name;?></th>
										<th><?php echo $x->dtm;?></th>
										<!--<th><?php echo $x->upload_dtm;?></th>-->
										<th><?php echo $x->callerid;?></th>
										
										<!--<th><?php echo $x->process_dtm; ?></th>
										<th><?php echo $x->end_process_dtm; ?></th>-->
										<th><?php echo $x->all_status; ?></th>
										<th><?php echo $x->info; ?></th>
										<!--<th><?php echo $x->process_status; ?></th>-->
										<!--<th><?php echo $x->total_row; ?></th>
										<th><?php echo $x->current_row; ?></th>
										<th><?php echo $x->invalid_row; ?></th>
										<th><?php echo $x->valid_row; ?></th>-->
										<!--<th><?php echo $x->cfm_send; ?></th>
										<th><?php echo $x->send_sms_status; ?></th>-->
										<th><input type="checkbox" name="all" id="all"></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="6">
											<span class="pull-left">
												<button id="create" type="submit" class="btn btn-primary btn-sm"><?php echo $xml_common->add_new_record;?></button>
											</span>
											<span class="pull-right">
												<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete; ?></button>
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
		<?php include('footnote.php'); ?>
	</div>
	<script nonce="<?php echo session_id();?>" src="js/bootstrap-datepicker.min.js"></script>
	<script type="application/javascript" src="broadcast_sms_status_js.php"></script>
</body>
</html>
