<?php
	$page_mode = '300';
	$chk_mode = '4';
	$page_title = 'Address Book';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("address_book",$lang);
	//$x->email = 'Email';
?>
		<div class="page-header" style="padding-top:10px">
		<!-- <div class="page-header navbar navbar-light" style="margin-top:10px; background-color: #e9ecef;"> -->
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->address_book;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->address_book;?></li>
				</ol>
			</nav>
		</div>
	
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table table-striped table-bordered table-sm" id="address" width="100%">
								<thead>
									<tr>
										<th><?php echo $x->contact_name;?></th>
										<th><?php echo $x->mobile_number; ?></th>
										<th><?php echo $x->email; ?></th>
										<th><?php echo $x->group_list; ?></th>
										<th><input type="checkbox" id="all"></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="5">
											<span class="pull-left">
												<button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myAdbk"><?php echo $xml_common->add_new_record;?></button>
												<button type="submit" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#myUpload"><?php echo $x->upload_button;?></button>&emsp;
												<button type="button" class="btn btn-link btn-sm" id = "example_upload_file">Example <?php echo $x->upload_button;?></button>&emsp;
											</span>
											<div id="export"></div>
											<span class="pull-right">
												<button id="truncate" type="submit" class="btn btn-warning btn-sm"><?php echo $x->empty_str;?></button>
												<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete;?></button>
											</span>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
						<div class="modal fade" id="myAdbk" tabindex="-1" role="dialog">
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<div class="modal-header" id="myAdbk_header">
										<h5 class="modal-title" id="header">&nbsp;</h5>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
											<!-- <span aria-hidden="true">&times;</span> -->
										</button>
									</div>
									<form id="contact_form" name="contact_form">
									<div class="modal-body">
										<div class="row">
											<div class="col-md-10">
												<div id="msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2" role="alert" style="display:none">
													<span id="msgstatustext">A</span>	
													<button type="button" class="btn-close" id="msgstatusbar_close" aria-label="Close"></button>											
												</div>
											</div>											
										</div>	
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="contact" class="control-label"><?php echo $x->contact_name;?> <span style="color:red">*</span></label>
											</div>
											<div class="col-md-6">
												<input class="form-control input-sm" type="text" name="contact" id="contact" maxlength="30" required>
												<div id="invalid_contact" class="invalid-feedback">
													<?php echo $x->invalid_contact; ?>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="mobile" class="control-label"><?php echo $x->mobile_number;?> <span style="color:red">*</span></label>
											</div>
											<div class="col-md-4">
												<input class="form-control input-sm" type="text" name="mobile" id="mobile" pattern="\+?\d+" required>
												<div id="invalid_mobile" class="invalid-feedback">
													<?php echo $x->invalid_mobile; ?>
												</div>
											</div>
											<div class="col-md-3">
												<a href="#" data-toggle="tooltip" data-html="true" title="<?php echo $x->numbers_only; ?>"><i class="fa fa-2x fa-question-circle"></i></a>
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="email" class="control-label"><?php echo $x->email;?> <span style="color:red">*</span></label>
											</div>
											<div class="col-md-4">
												<input class="form-control input-sm" type="text" name="email" id="email" required>
												<div id="invalid_email" class="invalid-feedback">
													<?php echo $x->invalid_email; ?>
												</div>
											</div>
											<div class="col-md-3">
												
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="modem" class="control-label"><?php echo $x->modem_label; ?></label>
											</div>
											<div class="col-md-4">
												<p><select name="modem" id="modem">
													<option value="None">None</option>
												</select></p>
											</div>
											<div class="col-md-3">
												<a href="#" data-toggle="tooltip" data-html="true" title="<?php echo $x->modem_desc; ?>"><i class="fa fa-2x fa-question-circle"></i></a>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label class="control-label"><?php echo $x->list_add_group; ?></label>
											</div>
											<div class="col-md-6" id="grouplist"></div>
										</div>										
									</div>
									<div class="modal-footer">										
										<input type="hidden" name="id" id="id">
										<input type="hidden" name="mode" id="mode">
										<button id="save" type="submit" class="btn btn-primary"><?php echo $xml_common->save;?></button>
										<button id="cancel" type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
									</div>
									</form>
								</div>
							</div>
						</div>
						<div class="modal fade" id="myUpload" tabindex="1" role="dialog">
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title"><?php echo $x->add_upload;?></h5>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
											<!-- <span aria-hidden="true">&times;</span> -->
										</button>
									</div>
									<form id="upload_form" name="upload_form">
										<div class="modal-body">
											<div class="row">
												<div class="col-md-3 offset-md-1">
													<label class="control-label"><?php echo $x->upload_file;?></label>
												</div>
												<div class="col-md-8">
													<div class="form-group">
														<label class="control-label" for="file">
															<input id="file" type="file" name="file" class="file" required/>
														</label>
														<p class="help-block"><?php echo $x->csv;?></p>
													</div>
												</div>
											</div>
										</div>
										<div class="modal-footer">
											<input type="hidden" name="mode" value="insertContacts">
											<input type="hidden" name="access_type" value="0">
											<button type="submit" class="btn btn-primary"><?php echo $xml_common->upload;?></button>
											<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="modal fade" id="movegrp" tabindex="-1" role="dialog"><?php // 8Apr20 added by Ty ?>
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<form id="move_group_form" name="move_group_form" method="post">
										<div class="modal-header">
											<h5 class="modal-title">Move to Group</h5>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
												<!-- <span aria-hidden="true">&times;</span> -->
											</button>
										</div>
										<div class="modal-body">
											<div class="row">
												<div class="col-md-11 offset-md-1"><label class="control-label">Selected contact will be moved to:</label></div>
												<div class="col-md-11 offset-md-1" id="grouplist2"></div>
											</div>
											<div class="row">
												<div class="col-md-11 offset-md-1">
													<label class="control-label">Create new group name:</label><input type="text" name="newgroupname" placeholder="New Group name">
												</div>
											</div>
										</div>
										<div class="modal-footer">
											<input type="hidden" name="mode" value="moveGroup">
											<input type="hidden" name="access_type" value="0">
											<button type="submit" class="btn btn-primary">Move</button>
											<button id="moveGrpCancel" type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="modal fade" id="prevUpload" tabindex="1" role="dialog" data-backdrop="static" data-keyboard="false">
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title"><?php echo $x->preview;?></h5>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
											<!-- <span aria-hidden="true">&times;</span> -->
										</button>
									</div>
									<form id="upload_view" name="upload_view" method="post">
										<div class="modal-body">
											<table class="table table-striped table-bordered table-hover table-sm" id="upload_table" style="width: 100%;">
												<thead>
													<tr>
														<th><?php echo $x->no;?></th>
														<th><?php echo $x->contact_name;?></th>
														<th><?php echo $x->mobile_number; ?></th>
														<th><?php echo $x->group_list; ?></th>
														<th><?php echo $x->file_format; ?></th>
													</tr>
												</thead>
											</table>
										</div>
										<div class="modal-footer">
											<input type="hidden" name="mode" value="addContacts">
											<input type="hidden" name="access_type" value="0">
											<button type="submit" class="btn btn-primary"><?php echo $xml_common->add;?></button>
											<button id="preCancel" type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
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
	<script src="js/bootstrap-datepicker.min.js"></script>
	<script src="js/moment.min.js"></script>
	<script src="js/dataTables.buttons.min.js"></script>
	<script src="js/buttons.html5.min.js"></script>
	<script language="javascript" src="js/txvalidator.js"></script>
	<script language="javascript" src="js/txcommon.js"></script>
	<script type="application/javascript" src="address_book_js.php"></script>
</body>
</html>
