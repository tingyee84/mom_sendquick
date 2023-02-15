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
		<div class="page-header" style="padding-top:10px">
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
										<table style="border:none">
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
								<div class="tab-pane fade" id="api" role="tabpanel" aria-labelledby="api-tab" style="width:99%">
									<form id="sentapiForm" name="sentapiForm">
										<table style="border:none">
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
	<script nonce="<?php echo session_id();?>">
	function status_code(sta) {
		switch(sta) {
			case "U0000":
				return "Delivered Successfully";
				break;
			case "U0001":
				return "Failed to Delivered to Telco";
				break;
			case "U0002":
				return "Telco Unavailable";
				break;
			case "U0003":
				return "Telco Failed to Deliver";
				break;
			case "U0004":
				return "Mobile Number out of coverage";
				break;
			case "U0005":
				return "Mobile Number Unreachable";
				break;
			case "U0006":
				return "Mobile Number is unsubscribed";
				break;
			case "U0007":
				return "Mobile Number is deactivated";
				break;
			case "U0008":
				return "Sent to Telco";
				break;
			default:
				return "Unknown Status Code";
				break;
		}
	}
	$('#from, #to, #from_api, #to_api').val(moment().format('DD/MM/YYYY'));
	$('#from, #to, #from_api, #to_api').datepicker({format: 'dd/mm/yyyy'});
	$.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');
	var table =  $('#sent').DataTable({
		autoWidth: false,
		deferRender: true,
		processing: true,
		stateSave: true,
		pageLength: 100,
		ajax:{type: 'POST',
			url: 'sent_log_lib.php',
			data: function(){return $('#sentForm').serialize();}
		},
		columnDefs: [{ 'orderable':false,'targets':8},
					 { 'width':'50%','targets':5},
					{'width':'130px','targets':[0,1]},
					{'render': function(data,type,row) {
						if (data == "Y")
							return "Sent";
						else if (data == "R")
							return "Delivered";
						else
							return "Undelivered<br/>"+"<i class='fa fa-info-circle' class='btn btn-secondary btn-sm' data-toggle='tooltip' data-placement='top' title='"+status_code(data)+"'></i>";
					},'targets' : 6}]
	});
	var table_api = $('#sent_api').DataTable({
		autoWidth: false,
		deferRender: true,
		processing: true,
		stateSave: true,
		pageLength: 100,
		ajax:{type: 'POST',
			url: 'sent_log_lib.php',
			data: function(){return $('#sentapiForm').serialize();}
		},
		columnDefs: [{ 'orderable':false,'targets':7},
					 { 'width':'50%','targets':4},
					{'width':'130px','targets':[0,1]},
					{'render': function(data,type,row) {
						if (data == "Y")
							return "Sent";
						else if (data == "R")
							return "Delivered";
						else
							return "Undelivered<br/>"+"<i class='fa fa-info-circle' class='btn btn-secondary btn-sm' data-toggle='tooltip' data-placement='top' title='"+status_code(data)+"'></i>";
					},'targets' : 5}]
	});
	<?php /* TODO keep in view $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		console.log("tab active" +)
	});*/ ?>
	<?php if ($export == "1") { ?>
	function toCallDate(filename,ndate) {
		return  filename+moment(ndate).format("YYYY-MM-DD hhmmss");
	}	
	var date = $.now();
	new $.fn.dataTable.Buttons( table, {
	buttons: [
		{
			extend:'csv',
			text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export.' CSV'; ?>',
			exportOptions: {columns: ':visible'},
			filename: function() {
				return toCallDate('<?php echo $_SESSION['userid']; ?>_GlobalSent_',new Date());
			},
			init: function(api,node,config){
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			}},
		{
			extend:'excel',
			text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export.' Excel'; ?>',
			exportOptions: {columns: ':visible'},
			filename: function() {
				return toCallDate('<?php echo $_SESSION['userid']; ?>_GlobalSent_',new Date());
			},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-success btn-sm");
			}}
		]
	} );	
	table.buttons().container().appendTo('#export');

	new $.fn.dataTable.Buttons( table_api, {
	buttons: [
		{
			extend:'csv',
			text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export.' CSV'; ?>',
			exportOptions: {columns: ':visible'},
			filename: function() {
				return toCallDate('<?php echo $_SESSION['userid']; ?>_GlobalSentApi_',new Date());
			},
			init: function(api,node,config){
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			}},
		{
			extend:'excel',
			text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export.' Excel'; ?>',
			exportOptions: {columns: ':visible'},
			filename: function() {
				return toCallDate('<?php echo $_SESSION['userid']; ?>_GlobalSentApi_',new Date());
			},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-success btn-sm");
			}}
		]
	} );	
	table_api.buttons().container().appendTo('#export_api');
	<?php } ?>
	$('#from, #to').on('changeDate', function() {
		$('#from, #to').datepicker('hide');
		table.ajax.reload();
	});
	$('#from_api, #to_api').on('changeDate', function() {
		$('#from_api, #to_api').datepicker('hide');
		table_api.ajax.reload();
	});
	$('#all').change(function () {
		var cells = table.cells().nodes();
		$(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
	});
	$('#all_api').change(function () {
		var cells = table_api.cells().nodes();
		$(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
	});
	$('#delete').on('click', function(e) {
		if(confirm('<?php echo $x->alert_4; ?>')) {
			$('#sent input[type=checkbox]').each(function() {     
				if (this.checked && this.value!='on') {
					$.post('sent_log_lib.php',{mode:'delete',idx:this.value},function(data) {
						table.ajax.reload();
					});
				}
			});
			$('#all_api').prop('checked',false);
		}
	});
	$('#truncate').on('click', function(e) {
		if(confirm('<?php echo $x->alert_5; ?>')) {
			$.post('sent_log_lib.php',{mode:'emptyGlobalLog'},function(data) {
				table.ajax.reload();
			});
		}
	});

	$('#delete_api').on('click', function(e) {
		if(confirm('<?php echo $x->alert_4; ?>')) {
			$('sent_api input[type=checkbox]').each(function() {     
				if (this.checked && this.value!='on') {
					$.post('sent_log_lib.php',{mode:'delete_api',idx:this.value},function(data) {
						table_api.ajax.reload();
					});
				}
			});
			$('#all').prop('checked',false);
		}
	});
	$('#truncate_api').on('click', function(e) {
		if(confirm('<?php echo $x->alert_5; ?>')) {
			var cells = table_api.column(7).nodes();
			$(cells).find(':checkbox').prop('checked',1);
			$('sent_api input[type=checkbox]').each(function() {     
				if (this.checked && this.value!='on') {
					$.post('sent_log_lib.php',{mode:'delete_api',idx:this.value},function(data) {
					});
				}
			});
			table_api.ajax.reload();
		}
	});
	<?php if(!isUserAdmin(strtolower($_SESSION['userid']))) { ?>
		table.column(3).visible(false);
		table_api.column(3).visible(false);
	<?php } ?>
	</script>
</body>
</html>
