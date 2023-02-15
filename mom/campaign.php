<?php
	$page_mode = '1';
	$page_title = 'Campaign Management';
	include('header.php');
	include('checkAccess.php');
	
?>
		<div class="page-header" style="padding-top:10px">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->campaign_mgnt;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->campaign;?></li>
				</ol>
			</nav>
		</div>
		
		<?php $x = GetLanguage("campaign_mgnt",$lang); ?>
		
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="table-responsive">
							<!-- <table class="table table-striped table-bordered table-condensed" id="campaign"> -->
							<table class="table table-striped table-bordered table-sm dt-responsive" id="campaign">
								<thead>
									<tr>
										<th><?php echo $x->name;?></th>
										<th><?php echo $x->type; ?></th>
										<th><?php echo $x->status; ?></th>
										<th><?php echo $x->info; ?></th>
										<th><?php echo $x->cby; ?></th>
										<th><?php echo $x->department; ?></th>
										<th><input type="checkbox" name="all" id="all"></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="7">
											<span class="pull-left">
												<button id="create" type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myModal"><?php echo $xml_common->add_new_record;?></button>
											</span>
											<span class="pull-right">
												<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete; ?></button>
											</span>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
						<!-- Modal -->
						<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header" id="myModal_header">
										<h5 class="modal-title" id="header"></h5>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
											<!-- <span aria-hidden="true">&times;</span> -->
										</button>
									</div>
									<form id="campaign_form" name="campaign_form" method="post" autocomplete="off">
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
												<label for="name" class="control-label"><?php echo $x->name; ?></label>
											</div>
											<div class="col-md-5">
												<input type="text" class="form-control input-sm" name="campaign_name" id="campaign_name" maxlength="30" required>
												<div id="invalid_campaign_name" class="invalid-feedback">
													<?php echo $x->invalid_campaign_name; ?>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="name" class="control-label"><?php echo $x->type; ?></label>
											</div>
											<div class="col-md-5">
												<p>
													<input type="radio" name="campaign_type" id = "campaign_type_1" value="1" checked="checked"><?php echo $x->broadcast; ?>
													<input type="radio" name="campaign_type" id = "campaign_type_2" value="2"><?php echo $x->interactive; ?>
												</p>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="name" class="control-label"><?php echo $x->status; ?></label>
											</div>
											<div class="col-md-5">
												<p>
													<select name = "campaign_status" id = "campaign_status">
														<option value = "active"><?php echo $x->active; ?></option>
														<option value = "pause"><?php echo $x->pause; ?></option>
														<option value = "cancel"><?php echo $x->cancel; ?></option>
													</select>
												</p>
											</div>
										</div>
										<div class="row" id = "start_date_div">
											<div class="col-md-3 offset-md-1">
												<label for="name" class="control-label"><?php echo $x->campaign_start_date; ?></label>
											</div>
											<div class="col-md-5">												
												<input type="text" class="form-control input-sm" name="campaign_start_date" id="campaign_start_date" onkeydown="return false">
												<div id="invalid_campaign_start_date" class="invalid-feedback">
													<?php echo $x->invalid_campaign_start_date; ?>
												</div>
											</div>
										</div>
										<div class="row" id = "end_date_div">
											<div class="col-md-3 offset-md-1">
												<label for="name" class="control-label"><?php echo $x->campaign_end_date; ?></label>
											</div>
											<div class="col-md-5">
												<input type="text" class="form-control input-sm" name="campaign_end_date" id="campaign_end_date" onkeydown="return false">
												<div id="invalid_campaign_end_date" class="invalid-feedback">
													<?php echo $x->invalid_campaign_end_date; ?>
												</div>
											</div>
										</div>
										
										<div class="row" id = "keyword_div">
											<div class="col-md-3 offset-md-1">
												<label for="name" class="control-label"><?php echo $x->keyword; ?></label>
											</div>
											<div class="col-md-5">
												<p id = "keyword_div_p">
													
												</p>
											</div>
										</div>
										
									</div>
									<div class="modal-footer">
										<input type="hidden" name="mode" id="mode"/>
										<input type="hidden" name="id" id="id"/>
										<button type="submit" class="btn btn-primary" id = "save_btn_id"><?php echo $xml_common->save; ?></button>
										<button type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo $xml_common->cancel; ?></button>
									</div>
									</form>
								</div>
							</div>
						</div>
						<!-- End Modal -->
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<?php include("campaign_js.php");?>
</body>
</html>
