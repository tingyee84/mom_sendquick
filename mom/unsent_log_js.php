<?php header("Content-type: text/javascript");
require_once('lib/commonFunc.php');
$xml_common = GetLanguage("common",$lang);
$x = GetLanguage("global_inbox",$lang);
?>

$('#from').val(moment().format('DD/MM/YYYY'));
$('#to').val(moment().format('DD/MM/YYYY'));
$('#from, #to').datepicker({format: 'dd/mm/yyyy'});
$.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');

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

function resend_check () {
	$("#unsent button.btn-resend").each(function() {
		$(this).on("click",function (ev) {
			$.redirect('send_sms.php',{'msgid':$(this).attr("data")});
		});

		$('#unsent [data-toggle="tooltip"]').each(function() {
			  $(this).tooltip();
		});
	});
}
var table =  $('#unsent').DataTable({
	autoWidth: false,
	deferRender: true,
	processing: true,
	stateSave: true,
	pageLength: 100,
	ajax:{type: 'POST',
		url: 'unsent_log_lib.php',
		data: function(){return $('#unsentForm').serialize();}
	},
	columnDefs: [{'orderable':false,'targets':[4,5]},
				 {'width':'50%','targets':2},
				 {'render':function(data,type,row) {
					if (type==='display')
						return "<button class='btn btn-secondary btn-sm btn-resend' data='"+data+"'>Resend</button>";
					return data;
				 },'targets':4},
				 {'render':function(data,type,row) {
						if (type==='display' && data.substr(0,1).toUpperCase() == "U") 
							return "U<br/>"+"<i class='fa fa-info-circle' class='btn btn-secondary' data-toggle='tooltip' data-placement='top' title='"+status_code(data)+"'></i>";
						return "Failed";
					 },'targets':3}],
	initComplete: function(settings,json) {
		resend_check();
	}
});


// assmi
$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#unsent").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});

$("#unsent").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#unsent').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});

// assmi

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
				return toCallDate('<?php echo $_SESSION['userid']; ?>_Unsent_',new Date());
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
				return toCallDate('<?php echo $_SESSION['userid']; ?>_Unsent_',new Date());
			},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-success btn-sm");
			}}
	]
} );
table.buttons().container().appendTo('#export');
}
$('#from, #to').on('changeDate', function() {
	$('#from, #to').datepicker('hide');
	table.ajax.reload(resend_check);

});
$('#all').change(function () {
	var cells = table.cells().nodes();
	$(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
});
$('#delete').on('click', function(e) {
	if(confirm('<?php echo $x->alert_4; ?>')) {
		$('input[type=checkbox]').each(function() {     
			if (this.checked && this.value!='on') {
				$.post('unsent_log_lib.php',{mode:'delete',idx:this.value},function(data) {
					table.ajax.reload(resend_check);
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e) {
	if(confirm('<?php echo $x->alert_5; ?>')) {
		$.post('unsent_log_lib.php',{mode:'emptyGlobalLog'},function(data) {
			table.ajax.reload(resend_check);
		});
	}
});

