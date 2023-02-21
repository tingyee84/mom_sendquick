<?php
	$page_mode = '200';
	$chk_mode = '29';
	$page_title = 'User Role Management';
	include('header.php');
	include('checkAccess.php');
?>
		<link href="css/assmi.css" rel="stylesheet">
		<div class="page-header padding-top-10">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->user_mgnt;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->user_role_mgnt;?></li>
				</ol>
			</nav>
		</div>
	
		<?php $x = GetLanguage("user_role",$lang); ?>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table table-striped table-bordered table-condensed" id="role">
								<thead>
									<tr>
										<th><?php echo $x->userrole;?></th>
										<th><?php echo $x->dept; ?></th>
										<th><input type="checkbox" name="all" id="all"></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="3">
											<span class="pull-left">
												<button id="create" type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myRole"><?php echo $xml_common->add_new_record;?></button>
											</span>
											<span class="pull-right">
												<button id="truncate" type="submit" class="btn btn-warning btn-sm"><?php echo $x->empty_str;?></button>
												<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete; ?></button>
											</span>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
						<!-- Modal -->
						<div class="modal fade" id="myRole" tabindex="-1" role="dialog">
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<div class="modal-header" id="myRole_header">
										<h4 class="modal-title" id="header"></h4>
										<button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
											<i class="fa fa-times"></i>
										</button>
									</div>
									<form id="role_form" name="role_form" method="post">
									<div class="modal-body">
										<div class="row pt-2">
											<div class="col-md-10">
												<div id="msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2 display-none-assmi" role="alert">
													<span id="msgstatustext">A</span>		
													<button type="button" class="btn-close" id="msgstatusbar_close" aria-label="Close"></button>																																	
												</div>
											</div>											
										</div>	
										<div class="row pt-2">
											<div class="col-md-2 offset-md-1">
												<label for="user_role" class="control-label"><?php echo $x->userrole; ?></label>
												<span class="color-red">*</span>
											</div>
											<div class="col-md-6">
												<input type="text" class="form-control input-sm" name="user_role" id="user_role" maxlength="30" required>
												<div id="invalid_user_role" class="invalid-feedback">
													<?php echo $x->invalid_user_role; ?>
												</div>
											</div>
										</div>
										<div class="row pt-2">
											<div class="col-md-2 offset-md-1">
												<label for="department" class="control-label"><?php echo $x->dept;?></label>
											</div>
											<div class="col-md-6">
												<p>
													<select name="department" id="department">
														<option value="0"><?php echo $x->access_all_dept; ?></option>
													</select>
												</p>
											</div>
										</div>
										<div class="row pt-2">
											<div class="col-md-6 offset-md-3">
												<table class="table table-condensed" id="access_list">
												</table>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<input type="hidden" name="mode" id="mode"/>
										<input type="hidden" name="id" id="id"/>
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
	<?php include("user_role_js.php");?>
</body>
</html>
