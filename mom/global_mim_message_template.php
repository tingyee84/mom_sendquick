<?php
	$page_mode = '7';
	$page_title = 'Global MIM Message Template';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("mim_message_template",$lang);

?>
	<link href="css/style1.css" rel="stylesheet">

		<div class="page-header page-header2">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->msg_tmpl;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->global_mim_msg_tmpl;?></li>
				</ol>
			</nav>
		</div>
		
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body table-responsive">
						<table class="table table-striped table-bordered table-condensed" id="tbl_tmpl">
							<thead>
								<tr>
									<th><?php echo $x->template_name;?></th>
									<th><?php echo $x->msg_template;?></th>
									<th><?php echo $x->mim_tpl_id;?></th>
									<th><?php echo $x->department;?></th>
									<th><input type="checkbox" id="all"></th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td colspan="5">
										<span class="pull-left">
											<button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myCreate"><?php echo $xml_common->add_new_record;?></button>
						
										</span>
										<div id="export"></div>
										<span class="pull-right">
											<button id="truncate" type="submit" class="btn btn-warning btn-sm"><?php echo $x->empty_str;?></button>
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
		<!-- Modal -->
		<div class="modal fade" id="myCreate" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header" id="myCreate_header">
						<h5 class="modal-title" id="header">&nbsp;</h5>
						<button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
							<i class="fa fa-times"></i>
						</button>
						
					</div>
					<form id="template_form" name="template_form">
					<div class="modal-body">
						<div class="row">
								<div class="col-md-10">
									<div id="msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2 msgstatusbar" role="alert">
										<span id="msgstatustext">A</span>	
										<button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close" id="msgstatusbar_close">
											<i class="fa fa-times"></i>
										</button>
																				
									</div>
								</div>											
						</div>
						<div class="row">
							<div class="col-md-8 offset-md-2">
								<label class="control-label"><?php echo $x->new_tpl_name_text;?></label>
							</div>
						</div>
						<div class="row">
							<div class="col-md-8 offset-md-2">
								<input type = "text" name = "template_name" id = "template_name" class = "form-control input-sm" maxlength="30" required>
								<div id="invalid_template_name" class="invalid-feedback">
									<?php echo $x->invalid_template_name; ?>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-8 offset-md-2">
								<label class="control-label"><?php echo $x->new_msg_text;?></label>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-8 offset-md-2">
								<textarea class="form-control input-sm" name="template" id="template" rows="5" required></textarea>
								<?php echo $x->characters; ?>&nbsp;<strong><span id="count_chars2">0</span></strong><br>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-8 offset-md-2">
								<label class="control-label"><?php echo $x->mim_tpl_id;?></label>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-8 offset-md-2">
								<input type = "text" class="form-control input-sm" name = "mim_tpl_id" id = "mim_tpl_id" value = "" maxlength="100" required>
								<div id="invalid_mim_tpl_id" class="invalid-feedback">
									<?php echo $x->invalid_mim_tpl_id; ?>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-8 offset-md-2">
								<label class="control-label"><i>Template ID must be in lowercase. Otherwise, system will auto convert to lowercase.</i></label>
							</div>
						</div>
						
					</div>
					<div class="modal-footer">
						<input type="hidden" name="id" id="id">
						<input type="hidden" name="mode" id="mode"/>
						<input type="hidden" name="count_chars" id="count_chars">
						<button type="submit" class="btn btn-primary"><?php echo $xml_common->save;?></button>
						<button type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
					</div>
					</form>
				</div>
			</div>
		</div>
		<div class="modal fade" id="myUpload" tabindex="1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title"><?php echo $x->add_upload;?></h4>
						<button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
							<i class="fa fa-times"></i>
						</button>
						
					</div>
					<form id="upload_form" name="upload_form">
					<div class="modal-body">
						<div class="row">
							<div class="col-md-3 offset-md-1">
								<label class="control-label"><?php echo $x->upload_file;?></label>
							</div>
							<div class="col-md-7">
								<label class="btn btn-default">
									<input type="file" class="file" name="template_file" required>
								</label>
								<p class="help-block">File Uploaded Must Be In CSV Format</p>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="access_type" value="1">
						<input type="hidden" name="mode" value="insertTemplate">
						<button type="submit" class="btn btn-primary"><?php echo $xml_common->upload;?></button>
						<button type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
					</div>
					</form>
				</div>
			</div>
		</div>
		<div class="modal fade" id="prevUpload" tabindex="1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title"><?php echo $x->preview;?></h4>
						<button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
							<i class="fa fa-times"></i>
						</button>
						
					</div>
					<form id="upload_view" name="upload_view">
					<div class="modal-body table-responsive">
						<table class="table table-striped table-bordered" id="upload_table">
							<thead>
								<tr>
									<th><?php echo $x->no;?></th>
									<th><?php echo $x->msg_template;?></th>
									<th><?php echo $x->department;?></th>
								</tr>
							</thead>
						</table>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="access_type" value="1">
						<input type="hidden" name="mode" value="addTemplate">
						<button type="submit" class="btn btn-primary"><?php echo $xml_common->save;?></button>
						<button type="button" class="btn btn-default" id="preCancel"><?php echo $xml_common->cancel;?></button>
					</div>
					</form>
				</div>
			</div>
		</div>
		<!-- Modal End -->
		<?php include('footnote.php'); ?>
	</div>
	<script src="js/dataTables.buttons.min.js"></script>
	<script src="js/buttons.html5.min.js"></script>
	<script language="javascript" src="js/txvalidator.js"></script>
	<script type="application/javascript" src="global_mim_message_template_js.php"></script>
</body>
</html>
