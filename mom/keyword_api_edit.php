<?php
	$page_mode = '56';
	$page_title = 'Edit Application Keyword';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("edit_keyword",$lang);
?>
		<link href="css/assmi.css" rel="stylesheet">
		<div class="page-header padding-top-10">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="keyword_management.php"><?php echo $xml->keyword_mgnt; ?></a></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $x->api_title; ?></li>
				</ol>
			</nav>
		</div>

		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="row">
								<div class="col-md-10">
									<div id="msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2 display-none-assmi" role="alert">
										<span id="msgstatustext">A</span>	
										<button type="button" class="btn-close" id="msgstatusbar_close" aria-label="Close"></button>											
									</div>
								</div>											
						</div>
						<form name="edit_api_keyword" id="edit_api_keyword">
                            <div class="row">
                                <div class="col-lg-2">
                                    <label for="api_name" class="control-label"><?php echo "Service ID:" ?></label>
                                </div>
                                <div class="col-lg-4">
                                    <p><select class="form-select" name="api_name" id="api_name">
                                        <!-- <option value="None">None</option> -->
                                    </select></p>
                                </div>
                                <!-- <div class="col-md-3">
                                </div> -->
                            </div>
							<div class="row">
								<div class="col-lg-2">
									<label for="keyword" class="control-label"><?php echo $x->keyword; ?></label>
								</div>
								<div class="col-lg-4">
									<input class="form-control input-sm" type="text" name="keyword" id="keyword" maxlength="15" readonly="true">
								</div>
								<!--
								<div class="col-lg-6">
									<p class="help-block"><?php echo $x->keyword_desc; ?></p>
								</div>
								-->
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-2">
									<label for="description" class="control-label"><?php echo $x->keyword_description; ?></label>
								</div>
								<div class="col-lg-4">
									<input class="form-control input-sm" type="text" name="description" id="description">
									<div id="invalid_description" class="invalid-feedback">
										<?php echo $x->invalid_description; ?>
									</div>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-2">
									<label for="url" class="control-label"><?php echo "URL"; ?></label>
									<span class="color-red">*</span>
								</div>
								<div class="col-lg-4">
									<input class="form-control input-sm" type="text" name="url" id="url" required>
									<div id="invalid_url" class="invalid-feedback">
										<?php echo $x->invalid_url; ?>
									</div>
								</div>
							</div>
							<hr>
							
							<!-- <div id="progress" class="row text-center" style="display:none">
								<div class="col-md-4 offset-md-4">
									<div class="progress">
										<div id="bar" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="min-width:0.5em;">
											Processing ... <span id="percent"></span>
										</div>
									</div>
								</div>
							</div> -->
							<!-- <div class="row text-center">
								<input name="mode" type="hidden" value="editApiKeyword">
								<button class="btn btn-primary" type="submit" name="save"><?php echo $xml_common->save; ?></button>
								<button class="btn btn-default" type="button" id="cancel"><?php echo $xml_common->cancel; ?></button>
							</div> -->
							<div class="d-grid gap-2 d-md-block">
								<input name="mode" type="hidden" value="editApiKeyword">
								<button class="btn btn-primary" type="submit" name="save"><?php echo $xml_common->save; ?></button>
								<button class="btn btn-default" type="button" id="cancel"><?php echo $xml_common->cancel; ?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<?php include('keyword_edit_js.php'); ?>
</body>
</html>
