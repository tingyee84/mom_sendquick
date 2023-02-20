<?php
	$page_mode = '200';
	$chk_mode = '31';
	$page_title = 'Department Management';
	include('header.php');
	include('checkAccess.php');
?>
		<link href="css/assmi.css" rel="stylesheet">
		<div class="page-header padding-top-10">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->user_mgnt;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->department_mgnt;?></li>
				</ol>
			</nav>
		</div>
		
		<?php 
			$x = GetLanguage("user_department",$lang); 
			$x2 = GetLanguage("quota_mnt",$lang); 
		?>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table table-striped table-bordered table-sm" id="dept" width="100%">
								<thead>
									<tr>
										<th><?php echo $x->dept; ?></th>
										<th><?php echo $x->created_by; ?></th>
										<th><?php echo $x->mim_route; ?></th>
										<th><input type="checkbox" name="all" id="all"></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="4">
											<span class="float-left">
												<button id="create" type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myDept"><?php echo $xml_common->add_new_record;?></button>
											</span>
											<span class="float-right">
												<button id="truncate" type="submit" class="btn btn-warning btn-sm"><?php echo $x->empty_str;?></button>
												<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete; ?></button>
											</span>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
						<!-- Modal -->
						<div class="modal fade" id="myDept" tabindex="-1" role="dialog">
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<div class="modal-header" id="myDept_header">
										<h5 class="modal-title" id="header"></h5>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<form id="department_form" name="department_form" method="post">
									<div class="modal-body">
										<div class="row">
											<div class="col-md-10">
													<div id="msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2 display-none-assmi" role="alert">
														<span id="msgstatustext">A</span>	
														<button type="button" class="btn-close" id="msgstatusbar_close" aria-label="Close"></button>																									
													</div>
											</div>											
										</div>
										<div class="row">
											<div class="col-md-4 offset-md-1">
												<label for="department" class="control-label"><?php echo $x->new_dept; ?></label>
												<span class="color-red">*</span>
											</div>
											<div class="col-md-6">
												<input class="form-control input-sm" type="text" id="department" name="department" maxlength="30" required>
												<div id="invalid_department" class="invalid-feedback">
													<?php echo $x->invalid_department; ?>
												</div>
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-4 offset-md-1">
												<label for="enable_unlimited" class="control-label"><?php echo $x2->enable_unlimited;?></label>
											</div>
											<div class="col-md-3">
												<p><input type="checkbox" id="enable_unlimited" name="enable_unlimited" value="1"></p>
											</div>
										</div>
										<div class="row">
											<div class="col-md-4 offset-md-1">
												<label for="quota_left" class="control-label"><?php echo $x2->quota_left;?></label>
											</div>
											<div class="col-md-2">
												<input class="form-control input-sm" type="number" id="quota_left" name="quota_left">
												<div id="invalid_quota_left" class="invalid-feedback">
													<?php echo $x->invalid_quota_left; ?>
												</div>
											</div>
											
											<div class="col-md-4">
												<p class="margin-top-6">	
													<span id = "quota_left_remark"></span>
												</p>
											</div>
										</div>
										<div class="row">
											<div class="col-md-4 offset-md-1">
												<label for="frequency" class="control-label"><?php echo $x2->auto_topup;?></label>
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
												<label for="topup_value" class="control-label"><?php echo $x2->topup_value;?></label>
											</div>
											<div class="col-md-3">
												<input class="form-control input-sm" type="number" id="topup_value" name="topup_value">
												<div id="invalid_topup_value" class="invalid-feedback">
													<?php echo $x->invalid_topup_value; ?>
												</div>
											</div>
										</div>
								
										<div class="row">
											<div class="col-md-4 offset-md-1">
												<label for="mimroute" class="control-label"><?php echo $x->mim_channel; ?></label>
											</div>
											<div class="col-md-6 table-custom-overflow">
												<table id="mimroute">
													<tr>
														<th><?php echo $x->no; ?></th>
														<th><?php echo $x->description; ?></th>
														<th><?php echo $x->mim_type; ?></th>
														<th><input type="checkbox" id="m_dept"></th></tr>
												</table>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<input type="hidden" name="id" id="id">
										<input type="hidden" name="mode" id="mode"/>
										<button id="save" type="submit" class="btn btn-primary"><?php echo $xml_common->save; ?></button>
										<button id="cancel" type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $xml_common->cancel; ?></button>
									</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<?php include('user_department_js.php'); ?>
</body>
</html>
