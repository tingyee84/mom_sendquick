<?php
	$page_mode = '47';
	$page_title = 'Unsubscribe Keyword';
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
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->unsub_kw;?></li>
				</ol>
			</nav>
		</div>
		
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="panel-body table-responsive">
							<table class="table table-striped table-bordered table-condensed" id="unsub_keyword">
								<thead>
									<tr>
										<th><?php echo $x->unsub_keyword; ?></th>
										<th><?php echo $x->created_by; ?></th>
										<th><input type="checkbox" id="all"></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="3">
											<span class="pull-left">
												<button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myCreate"><?php echo $xml_common->add_new_record;?></button>
												<button type="submit" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#myResponse"><?php echo $x->resp_btn;?></button>
											</span>
											<span class="pull-right">
												<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete;?></button>
											</span>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
						<div class="modal fade" id="myCreate" tabindex="-1" role="dialog">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header" id="myCreate_header">
										<h5 class="modal-title"><?php echo $x->createtitle1;?></h5>
										<button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
											<!-- <span aria-hidden="true">&times;</span> -->
											<i class="fa fa-times"></i>
										</button>
									</div>
									<form id="kwd_form" name="kwd_form">
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
												<label for="keyword" class="control-label"><?php echo $x->createtitle2;?> <span class="color-red">*</span></label>
											</div>
											<div class="col-md-6">
												<input class="form-control input-sm" type="text" name="keyword" id="keyword" maxlength="15" required>
												<div id="invalid_keyword" class="invalid-feedback">
													<?php echo $msgstr->invalid_keyword; ?>
												</div>
												<p class="help-block"><?php echo $x->createtitle3; ?></p>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<input type="hidden" name="mode" value="saveKeyword">
										<button type="submit" class="btn btn-primary"><?php echo $xml_common->save;?></button>
										<button type="button" class="btn btn-light" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
									</div>
									</form>
								</div>
							</div>
						</div>
						<div class="modal fade" id="myResponse" tabindex="-1" role="dialog">
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<div class="modal-header" id="myResponse_header">
										<h5 class="modal-title"><?php echo $x->respmsgtitle1;?></h5>
										<button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
											<!-- <span aria-hidden="true">&times;</span> -->
											<i class="fa fa-times"></i>
										</button>
									</div>
									<form id="response_form" name="response_form">
									<div class="modal-body">
										<div class="row">
												<div class="col-md-10">
													<div id="resp_msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2 display-none-assmi" role="alert">
														<span id="resp_msgstatustext">A</span>	
														<button type="button" class="btn-close" id="resp_msgstatusbar_close" aria-label="Close"></button>											
													</div>
												</div>											
										</div>
										<div class="row">
											<div class="col-md-4 offset-md-1">
												<label for="unsub_resp" class="control-label"><?php echo $x->respmsgtitle2;?></label>
											</div>
											<div class="col-md-6">
												<textarea class="form-control input-sm" name="unsub_resp" id="unsub_resp" rows="4" required></textarea>
												<div id="invalid_unsub_resp" class="invalid-feedback">
													<?php echo $msgstr->invalid_unsub_resp; ?>
												</div>
												<input class="custom-control" type="text" name="textcount" id="textcount" size="3" value="160" readonly>
												<?php echo $msgstr->char_left; ?>
												<p class="help-block"><?php echo $x->respmsgtitle3;?></p>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<input type="hidden" name="mode" value="saveResponse">
										<button type="submit" class="btn btn-primary"><?php echo $xml_common->save;?></button>
										<button type="button" class="btn btn-light" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
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
	<?php include('unsubscribe_keyword_js.php'); ?>
</body>
</html>
