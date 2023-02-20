<?php
	$page_mode = '47';
	$page_title = 'Unsubscribe List';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("unsubscribe_list",$lang);
	$msgstr = GetLanguage("lib_unsubscribe",$lang);
?>
		<link href="css/assmi.css" rel="stylesheet">
		<div class="page-header padding-top-10">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->unsub_list;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->unsub_mobile;?></li>
				</ol>
			</nav>
		</div>
	
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="panel-body table-responsive">
							<table class="table table-striped table-bordered table-condensed" id="unsubscribe">
								<thead>
									<tr>
										<th><?php echo $x->mobile_number; ?></th>
										<th><?php echo $x->source; ?></th>
										<th><?php echo $x->created_by; ?></th>
										<th><?php echo $x->date_time; ?></th>
										<th><input type="checkbox" id="all"></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="5">
											<span class="pull-left">
												<button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myCreate"><?php echo $xml_common->add_new_record;?></button>
												<button type="submit" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#myUpload"><?php echo $xml_common->upload;?></button>&emsp;
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
					</div>
					<div class="modal fade" id="myCreate" tabindex="-1" role="dialog">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header" id="myCreate_header">
									<h5 class="modal-title"><?php echo $x->createtitle4;?></h5>
									<button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
										<!-- <span aria-hidden="true">&times;</span> -->
										<i class="fa fa-times"></i>
									</button>
								</div>
								<form id="unsub_form" name="unsub_form">
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
										<div class="col-md-5 offset-md-1">
											<label for="number" class="control-label"><?php echo $x->createtitle5;?> <span class="color-red">*</span></label>
										</div>
										<div class="col-md-4">
											<input class="form-control input-sm" type="text" name="number" id="number" pattern="\+?\d+" required>
											<div id="invalid_number" class="invalid-feedback">
												<?php echo $msgstr->invalid_number; ?>
											</div>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<input type="hidden" name="mode" value="saveNumber">
									<button type="submit" class="btn btn-primary"><?php echo $xml_common->save;?></button>
									<button type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
								</div>
								</form>
							</div>
						</div>
					</div>
					<div class="modal fade" id="myUpload" tabindex="-1" role="dialog">
						<div class="modal-dialog modal-lg" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title"><?php echo $x->upload_header;?></h5>
									<button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
										<!-- <span aria-hidden="true">&times;</span> -->
										<i class="fa fa-times"></i>
									</button>
								</div>
								<form id="upload_form" name="upload_form">
								<div class="modal-body">
									<div class="row">
										<div class="col-md-3 offset-md-1">
											<label for="unsub_file" class="control-label"><?php echo $x->uploadtitle;?></label>
										</div>
										<div class="col-md-7">
											<label class="btn btn-default" for="unsub_file">
												<input type="file" name="unsub_file" id="unsub_file" required>
											</label>
											<p class="help-block"><?php echo $x->uploadtitle_desc;?></p>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<input type="hidden" name="mode" value="uploadMobile">
									<button type="submit" class="btn btn-primary"><?php echo $xml_common->upload;?></button>
									<button type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
								</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php');?>
	</div>
	<?php include('unsubscribe_list_js.php');?>
</body>
</html>
