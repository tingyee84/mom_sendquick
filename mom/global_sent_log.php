<?php
	$page_mode = '18';
	$parent_mode = '500';
	$page_title = 'Global Sent Log';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("global_sent_log",$lang);
	if(in_array('22', $access_arr)) {
		$delete = 1;
	} else {
		$delete = 0;
	}
	if(in_array('21', $access_arr)) {
		$export = 1;
	} else {
		$export = 0;
	}
?>
	<link href="css/logmgt.css" rel="stylesheet">
		<div class="page-header lmdiv">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->logs_mgnt;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->global_sent;?></li>
				</ol>
			</nav>
		</div>
	
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
							<ul class="nav nav-tabs" id="gsentlogTab" role="tablist">
                                <li class="nav-item" role="presentation">
									<button class="nav-link active" id="sms-tab" data-bs-toggle="tab" data-bs-target="#sms" type="button" role="tab" aria-controls="sms" aria-selected="true">Portal</button>									
								</li>
                                <li class="nav-item" role="presentation">
									<button class="nav-link" id="api-tab" data-bs-toggle="tab" data-bs-target="#api" type="button" role="tab" aria-controls="api" aria-selected="true">API</button>									
								</li>
                            </ul>
							<div class="tab-content clearfix" id="gsentlogTabContent">
                                <div class="tab-pane fade show active" id="sms" role="tabpanel" aria-labelledby="sms-tab">
									<form id="sentForm" name="sentForm">
										<table class="lmtable">
										<tr>
											<td><b><?php echo $x->date_from;?></b>&nbsp;</td>
											<td><input class="form-control input-sm" type="text" id="from" name="from" size="10" required/></td>
											<td>&nbsp;</td>
											<td><b><?php echo $x->date_to;?></b>&nbsp;</td>
											<td><input class="form-control input-sm" type="text" id="to" name="to" size="10" required/></td>
										</tr>
										</table>
										<input name="mode" type="hidden" value="listGlobalLog"/>
									</form>
									<br>
									<table class="table table-striped table-bordered table-sm" id="sent" width="100%">
										<thead>
											<tr>
												<th><?php echo $x->date_time;?></th>
												<th><?php echo $x->mobile_number;?></th>
												<th><?php echo $x->sender;?></th>
												<th><?php echo $x->department;?></th>
												<th><?php echo $x->campaignname;?></th>
												<th><?php echo $x->message_text;?></th>
												<th><?php echo $x->status;?></th>
												<th><?php echo $x->totalsms;?></th>
												<th><input type="checkbox" id="all"></th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<td colspan="9">
													<div id="export"></div>
													<?php if ($delete == "1") { ?>
													<span class="pull-right">
														<button id="truncate" type="submit" class="btn btn-warning  btn-sm"><?php echo $x->empty_str;?></button>
														<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete;?></button>
													</span>
													<?php } ?>
												</td>
											</tr>
										</tfoot>
									</table>
								</div> <!-- end of sms tab -->
								<div class="tab-pane fade lmdivsbd" id="api" role="tabpanel" aria-labelledby="api-tab">
									<form id="sentapiForm" name="sentapiForm">
										<table class="lmtable">
											<tr>
												<td><b><?php echo $x->date_from;?></b>&nbsp;</td>
												<td><input class="form-control input-sm" type="text" id="from_api" name="from" size="10" required/></td>
												<td>&nbsp;</td>
												<td><b><?php echo $x->date_to;?></b>&nbsp;</td>
												<td><input class="form-control input-sm" type="text" id="to_api" name="to" size="10" required/></td>
											</tr>
										</table>
										<input name="mode" type="hidden" value="listapiLog"/>
									</form>
									<br>
									<table class="table table-striped table-bordered table-sm" id="sent_api" width="100%">
										<thead>
											<tr>
												<th><?php echo $x->date_time;?></th>
												<th><?php echo $x->mobile_number;?></th>
												<th>Service ID</th>
												<th><?php echo $x->department;?></th>
												<th><?php echo $x->message_text;?></th>
												<th><?php echo $x->status;?></th>
												<th><?php echo $x->totalsms;?></th>
												<th><input type="checkbox" id="all_api"></th>
											</tr>
										</thead>
										<tbody>
										</tbody>
										<tfoot>
											<tr>
												<td colspan="8">
													<div id="export_api"></div>
													<?php if ($delete == "1") { ?>
													<span class="pull-right">
														<button id="truncate_api" type="submit" class="btn btn-warning btn-sm"><?php echo $x->empty_str;?></button>
														<button id="delete_api" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete;?></button>
													</span>
													<?php } ?>
												</td>
											</tr>
										</tfoot>
									</table>
								</div> <!-- end of api tab -->
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
	<script src="js/datetime-moment.js"></script>
	<input type="hidden" id="export_flag" name="export_flag" value="<?php echo $export;?>" />
	<script src="global_sent_log_js.php"></script>	
</body>
</html>
