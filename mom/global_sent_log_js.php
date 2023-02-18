<?php header("Content-type: text/javascript");
require_once('lib/commonFunc.php');
$xml_common = GetLanguage("common",$lang);
$x = GetLanguage("global_sent_log",$lang);
?>		
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
if ($('#export_flag').val() == "1") { 
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
}
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
					$.post('sent_log_lib.php?mode=delete',{idx:this.value},function(data) {
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
			$('#sent_api input[type=checkbox]').each(function() {  				   
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
