<?php
	$page_mode = '48';
	$page_title = 'Quota Management';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("quota_mnt",$lang);

?>
	<link href="css/style1.css" rel="stylesheet">

		<div class="page-header page-header2">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->quota_mgnt;?></li>
				</ol>
			</nav>
		</div>
		
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body table-responsive table-responsive2">
						<table class="table table-striped table-bordered table-condensed dataTable upload_table" id="quota">
							<thead>
								<tr>
									<th><?php echo $x->userid;?></th>
									<th><?php echo $x->dept_quota_left;?></th>
									<th><?php echo $x->quota_left;?></th>
									<th><?php echo $x->auto_topup;?></th>
									<th><?php echo $x->topup_value;?></th>
									<th><?php echo $x->last_topup;?></th>
									<th><?php echo $x->next_topup;?></th>
									<th><?php echo $x->update_dtm;?></th>
									<th><?php echo $x->update_by;?></th>
									<th><input type="checkbox" id="all"></th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td colspan="10">
										<span class="pull-left">
											<!-- User will be added to Quota List when account is created.-->
											<!--
											<button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myQuota"><?php echo $xml_common->add_new_record;?></button>
											-->
											<button type="submit" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#myAlert"><?php echo $x->quota_alert_config;?></button>
										</span>
										<span class="pull-right">
											<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete;?></button>
										</span>
									</td>
								</tr>
							</tfoot>
						</table>
						<p></p><hr>
						
						<form id="global" name="global">
						
							<table>
								
								<tr>
									<td>Set all Message Quota to:</td>
									<td>
										<select name="option" id="option">
											<option value="1"><?php echo $xml_common->unlimited;?></option>
											<option value="2">New Value</option>
										</select>
									</td>
									<td>
										<input class="custom-control" type="number" id="value" name="value" size="3" pattern="\d+" disabled>										
										<div id="invalid_value" class="invalid-feedback">
											<?php echo $x->invalid_value; ?>
										</div>
									</td>
									<td><input type="hidden" name="mode" value="global"></td>
									<td><button class="btn btn-sm btn-primary" type="submit"><?php echo $xml_common->save;?></button></td>
								</tr>								
							</table>
							<div class="row">
									<div class="col-md-10">
										<div id="all_quota_msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2 msgstatusbar" role="alert">
											<span id="all_quota_msgstatustext">A</span>	
											<button type="button" class="btn-close" id="all_quota_msgstatusbar_close" aria-label="Close"></button>											
										</div>
									</div>											
							</div>
						</form>
					</div>
				</div>
				<!-- Quota Profile -->
				<div class="modal fade" id="myQuota" tabindex="-1" role="dialog">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header" id="myQuota_header">
								<h5 class="modal-title" id="header">&nbsp;</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
									<!-- <span aria-hidden="true">&times;</span> -->
								</button>
							</div>
							<form id="quota_form" name="quota_form">
							<div class="modal-body">
								<div class="row">
									<div class="col-md-10">
										<div id="msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2 msgstatusbar" role="alert">
											<span id="msgstatustext">A</span>	
											<button type="button" class="btn-close" id="msgstatusbar_close" aria-label="Close"></button>											
										</div>
									</div>											
								</div>
								<div id="user_div" class="row hidden">
									<div class="col-md-4 offset-md-1">
										<label for="userid" class="control-label"><?php echo $x->userid;?> <span class = "contact_cls">*</span></label>
									</div>
									<div class="col-md-6">
										<p><select id="userid" name="userid"></select></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-4 offset-md-1">
										<label for="enable_unlimited" class="control-label"><?php echo $x->enable_unlimited;?></label>
									</div>
									<div class="col-md-3">
										<p><input type="checkbox" id="enable_unlimited" name="enable_unlimited" value="1"></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-4 offset-md-1">
										<label for="quota_left" class="control-label"><?php echo $x->quota_left;?></label>
									</div>
									<div class="col-md-3">										
											<input class="form-control input-sm" type="number" id="quota_left" name="quota_left">
											<div id="invalid_quota_left" class="invalid-feedback">
												<?php echo $x->invalid_quota_left; ?>
											</div>
										<p>
											<i class = "help-block">
												<?php
												echo $x->QuotaProfile_msg1;
												?>
											</i>
										</p>
									</div>
									<div class="col-md-3">
										<p id = "current_bal_p"></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-4 offset-md-1">
										<label for="frequency" class="control-label"><?php echo $x->auto_topup;?></label>
									</div>
									<div class="col-md-6">
										<p><select name="frequency" id="frequency">
											<option value="3"><?php echo $xml_common->disable;?></option>
											<option value="1"><?php echo $xml_common->weekly;?></option>
											<option value="2"><?php echo $xml_common->monthly;?></option>
										</select></p>										
									</div>
								</div>
								<div class="row">
									<div class="col-md-4 offset-md-1">
										<label for="topup_value" class="control-label"><?php echo $x->topup_value;?></label>
									</div>
									<div class="col-md-3">
										<input class="form-control input-sm" type="number" id="topup_value" name="topup_value">
										<div id="invalid_topup_value" class="invalid-feedback">
												<?php echo $x->invalid_topup_value; ?>
										</div>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<input type="hidden" name="id" id="id"/>
								<input type="hidden" name="mode" id="mode"/>
								<button type="submit" class="btn btn-primary"><?php echo $xml_common->save;?></button>
								<button type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
							</div>
							</form>
						</div>
					</div>
				</div>
				<!-- Alert Config -->
				<div class="modal fade" id="myAlert" tabindex="-1" role="dialog">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header" id="myAlert_header">
								<h5 class="modal-title"><?php echo $x->add_quota_config;?></h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
									<!-- <span aria-hidden="true">&times;</span> -->
								</button>
							</div>
							<form id="alert_form" name="alert_form">
							<div class="modal-body">
								<div class="row">
									<div class="col-md-10">
										<div id="alert_msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2 msgstatusbar" role="alert">
											<span id="alert_msgstatustext">A</span>	
											<button type="button" class="btn-close" id="alert_msgstatusbar_close" aria-label="Close"></button>											
										</div>
									</div>											
								</div>
								<div class="row">
									<div class="col-md-3 offset-md-1">
										<label class="control-label"><?php echo $x->alert_status;?></label>
									</div>
									<div class="col-md-6">
										<p><input type="radio" value="1" name="alert_type">&nbsp;<?php echo $xml_common->enable;?>&emsp;
										<input type="radio" value="0" name="alert_type">&nbsp;<?php echo $xml_common->disable;?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-3 offset-md-1">
										<label class="control-label"><?php echo $x->alert_email;?></label>
									</div>
									<div class="col-md-7">
										<input class="form-control input-sm" type="email" name="alert_email" id="alert_email"/>
										<div id="invalid_alert_email" class="invalid-feedback">
											<?php echo $x->invalid_alert_email; ?>
										</div>
										<p class="help-block"><?php echo $x->alert_email_desc;?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-3 offset-md-1">
										<label class="control-label"><?php echo $x->credit_alert;?></label>
									</div>
									<div class="col-md-3">
										<input class="form-control input-sm" type="number" name="alert_credit" id="alert_credit">
										<div id="invalid_alert_credit" class="invalid-feedback">
											<?php echo $x->invalid_alert_credit; ?>
										</div>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<input type="hidden" name="mode" value="updateAlert"/>
								<button type="submit" class="btn btn-primary"><?php echo $xml_common->save;?></button>
								<button type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
							</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php');?>
	</div>
	<script language="javascript" src="js/txvalidator.js"></script>
	<script type="application/javascript" src="quota_mnt_js.php"></script>
</body>
</html>
