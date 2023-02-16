<?php
	$page_mode = '3';
	$dbl_mode = '3';
	$page_title = 'Global Address Book';
	include('header.php');
	include('checkAccess.php');
?>
		<div class="page-header page-header">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->address_book;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->global_address_book;?></li>
				</ol>
			</nav>
		</div>
	
		<?php $x = GetLanguage("global_address_book",$lang); //$x->email='Email';?>
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
										<th><?php echo $x->department; ?></th>
										<th><input type="checkbox" name="all" id="all"></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="6">
											<span class="pull-left">
												<button id="create" type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myGlbk"><?php echo $xml_common->add_new_record;?></button>
												<button id="upload" type="submit" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#myUpload"><?php echo $x->upload_button;?></button>

												&nbsp;
											</span>
											<div id="export"></div>
											<span class="pull-right">
												<!--<input type="button" value="Send MIM" class="btn btn-primary btn-sm" id="send_mim" name="send_mim">-->
												<button id="truncate" type="submit" class="btn btn-warning btn-sm"><?php echo $x->empty_str;?></button>
												<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete; ?></button>
											</span>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
						<div class="modal fade" id="myGlbk" tabindex="-1" role="dialog">
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="header"></h5>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
											<!-- <span aria-hidden="true">&times;</span> -->
										</button>
									</div>
									<form id="group_form" name="group_form" method="post">
									<div class="modal-body">
										<div class="row">
											<div class="col-md-10">
												<div id="msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2 msgstatusbar" role="alert">
													<span id="msgstatustext">A</span>	
													<button type="button" class="btn-close" id="msgstatusbar_close" aria-label="Close"></button>											
												</div>
											</div>											
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="contact" class="control-label"><?php echo $x->contact_name;?> <span class = "contact_cls">*</span></label>
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
												<label for="mobile" class="control-label"><?php echo $x->mobile_number;?> <span class = "contact_cls">*</span></label>
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
												<label for="email" class="control-label"><?php echo $x->email;?> </label>
											</div>
											<div class="col-md-6">
												<input class="form-control input-sm" type="text" name="email" id="email" >
												<div id="invalid_email" class="invalid-feedback">
													<?php echo $x->invalid_email; ?>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label class="control-label"><?php echo $x->modem_label; ?></label>
											</div>
											<div class="col-md-4">
												<p><select name="modem" class="modem">
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
											<div class="col-md-6" id="grouplist">
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<input type="hidden" name="id" id="id">
										<input type="hidden" name="mode" id="mode">
										<input type="hidden" name="id_of_user" value="<?php echo $id_of_user; ?>"/>
										<input type="hidden" name="department" id="department" value="<?php echo $department; ?>"/>
										<input type="hidden" name="all_departments" value="<?php echo $x->all_departments; ?>"/>
										<button id="save" type="submit" class="btn btn-primary"><?php echo $xml_common->save; ?></button>
										<button id="cancel" type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $xml_common->cancel; ?></button>
									</div>
									</form>
								</div>
							</div>
						</div>
						<div class="modal fade" id="myUpload" tabindex="-1" role="dialog">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title"><?php echo $x->add_upload; ?></h5>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
											<!-- <span aria-hidden="true">&times;</span> -->
										</button>
									</div>
									<form id="upload_form" name="upload_form">
										<div class="modal-body">
											<div class="row">
												<div class="col-md-3 offset-md-1">
													<label class="control-label"><?php echo $x->upload_file; ?></label>
												</div>
												<div class="col-md-8">
													<div class="form-group">
														<label class="btn btn-default" for="file">
															<input id="file" type="file" name="file" class="file" required/>
														</label>
														<p class="help-block"><?php echo $x->csv;?></p>
													</div>
												</div>
											</div>
										</div>
										<div class="modal-footer">
											<input type="hidden" name="mode" value="insertContacts">
											<input type="hidden" name="access_type" value="1">
											<input type="hidden" name="id_of_user" value="<?php echo $id_of_user; ?>"/>
											<input type="hidden" name="department2" value="<?php echo $department; ?>"/>
											<button type="submit" class="btn btn-primary"><?php echo $xml_common->upload;?></button>
											<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="modal fade" id="prevUpload" tabindex="1" role="dialog">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title"><?php echo $x->preview; ?></h5>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
											<!-- <span aria-hidden="true">&times;</span> -->
										</button>
									</div>
									<form id="upload_view" name="upload_view" method="post">
										<div class="modal-body">
											<div class="table-responsive">
												<table class="table table-striped table-bordered table-hover table-sm upload_table" id="upload_table">
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
										</div>
										<div class="modal-footer">
											<input type="hidden" name="mode" value="addContacts">
											<input type="hidden" name="access_type" value="1">
											<input type="hidden" name="id_of_user" value="<?php echo $id_of_user; ?>"/>
											<input type="hidden" name="department3" value="<?php echo $department; ?>"/>
											<button type="submit" class="btn btn-primary"><?php echo $xml_common->add; ?></button>
											<button id="preCancel" type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $xml_common->cancel; ?></button>
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
	<script src="js/dataTables.buttons.min.js"></script>
	<script src="js/buttons.html5.min.js"></script>
	<script src="js/jquery.redirect.js"></script>
	<script language="javascript" src="js/txvalidator.js"></script>
	<script language="javascript" src="js/txcommon.js"></script>
	<script type="application/javascript" src="global_address_book_js.php"></script>
</body>
</html>
