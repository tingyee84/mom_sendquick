<?php
	$page_mode = '56';
	$page_title = 'Edit Keyword';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("edit_keyword",$lang);
?>
		<div class="page-header" style="padding-top:10px">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="keyword_management.php"><?php echo $xml->keyword_mgnt; ?></a></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $x->title; ?></li>
				</ol>	
			</nav>
		</div>

		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="row">
								<div class="col-md-10">
									<div id="msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2" role="alert" style="display:none">
										<span id="msgstatustext">A</span>	
										<button type="button" class="btn-close" id="msgstatusbar_close" aria-label="Close"></button>											
									</div>
								</div>											
						</div>	
						<form name="edit_keyword" id="edit_keyword">
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
									<input class="form-control input-sm" type="text" name="description" id="description" maxlength="100">
									<div id="invalid_description" class="invalid-feedback">
										<?php echo $x->invalid_description; ?>
									</div>
								</div>
							</div>
							<hr>
							
							<div class="row">
								<div class="col-lg-2">
									<label for="autoreply" class="control-label"><?php echo $x->auto_msg; ?></label>
								</div>
								<div class="col-lg-4">
									<input type="checkbox" name="autoreply" id="autoreply" value="1">
								</div>
								<!--
								<div class="col-lg-6">
									<p class="help-block"><?php echo $x->auto_msg_desc; ?></p>
								</div>
								-->
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-2">
									<label for="message" class="control-label"><?php echo $x->standard; ?></label>
								</div>
								<div class="col-lg-4">
									<textarea class="form-control input-sm" name="message" id="message" rows="4"></textarea>
									<div id="invalid_message" class="invalid-feedback">
										<?php echo $x->invalid_message; ?>
									</div>
									<input class="custom-control" type="text" name="textcount" id="textcount" size="3" value="160" readonly>
									<?php echo $x->char_left; ?>
								</div>
								<!--
								<div class="col-lg-6">
									<p class="help-block"><?php echo $x->standard_desc; ?></p>
								</div>
								-->
							</div>
							<hr>
							
							<div id="progress" class="row text-center" style="display:none">
								<div class="col-md-4 offset-md-4">
									<div class="progress">
										<div id="bar" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="min-width:0.5em;">
											Processing ... <span id="percent"></span>
										</div>
									</div>
								</div>
							</div>
							<!-- <div class="row text-center">
								<input name="mode" type="hidden" value="editKeyword2">
								<button class="btn btn-primary" type="submit" name="save"><?php echo $xml_common->save; ?></button>
								<button class="btn btn-default" type="button" id="cancel"><?php echo $xml_common->cancel; ?></button>
							</div> -->
							<div class="d-grid gap-2 d-md-block">
								<input name="mode" type="hidden" value="editKeyword2">
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