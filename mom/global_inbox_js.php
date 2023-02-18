<?php header("Content-type: text/javascript");
require_once('lib/commonFunc.php');
$xml_common = GetLanguage("common",$lang);
$x = GetLanguage("global_inbox",$lang);
?>

$('#from, #to, #from_api, #to_api').val(moment().format('DD/MM/YYYY'));
$('#from, #to, #from_api, #to_api').datepicker({format: 'dd/mm/yyyy'});
$.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');
var table =  $('#inbox').DataTable({
	autoWidth: false,
	deferRender: true,
	processing: true,
	stateSave: true,
	pageLength: 100,
	ajax:{type: 'POST',
		url: 'inbox_lib.php',
		data: function(){return $('#inboxForm').serialize();}
	},
	columns: [
		{data: 0, width: '125px'},
		{data: 1, width: '125px'},
		{data: 2, render: function(data,type,row) {
			if (type==='display')
				return data+"<a href='send_sms.php?mobile_numb="+encodeURIComponent(data)+"'><i class='fa fa-comment' title='Reply'></i></a>";
			else
				return data;
		}, width: '125px'},
		{data: 3},
		{data: 4, width: '50%'},
		{data: 5, orderable : false, width: '50px'}
	]
});

var table_api =  $('#inboxapi').DataTable({
	autoWidth: false,
	deferRender: true,
	processing: true,
	stateSave: true,
	pageLength: 100,
	ajax:{type: 'POST',
		url: 'inbox_lib.php',
		data: function(){return $('#inboxapiForm').serialize();}
	},
	columns: [
		{data: 0, width: '125px'},
		{data: 1, render: function(data,type,row) {
			if (type==='display')
				return data+"<a href='send_sms.php?mobile_numb="+encodeURIComponent(data)+"'><i class='fa fa-comment' title='Reply'></i></a>";
			else
				return data;
		}, width: '125px'},
		{data: 2, width: '125px'},
		{data: 3},
		{data: 4, width: '50%'},
		{data: 5, width: '50px'},
		{data: 6, orderable : false, width: '50px'}
	]
});
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
			return toCallDate('<?php echo $_SESSION['userid']; ?>_GlobalInbox_',new Date());
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
			return toCallDate('<?php echo $_SESSION['userid']; ?>_GlobalInbox_',new Date());
		},
		init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-success btn-sm");
		}},			
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
				return toCallDate('<?php echo $_SESSION['userid']; ?>_GlobalInboxApi_',new Date());
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
				return toCallDate('<?php echo $_SESSION['userid']; ?>_GlobalInboxApi_',new Date());
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
	var cells = table_api.column(5).nodes();
	$(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
});
$('#delete').on('click', function(e) {
	if(confirm('<?php echo $x->alert_4; ?>')) {
		$('input[type=checkbox]').each(function() {     
			if (this.checked && this.value!='on') {
				$.post('inbox_lib.php',{mode:'delete',idx:this.value},function(data) {
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e) {
	if(confirm('<?php echo $x->alert_5; ?>')) {
		$.post('inbox_lib.php',{mode:'emptyGlobalInbox'},function(data) {
			table.ajax.reload();
		});
	}
});
$('#truncate_api').on('click', function(e) {
	if(confirm('<?php echo $x->alert_5; ?>')) {
		var cells = table_api.column(5).nodes();
		$(cells).find(':checkbox').prop('checked',1);
		$('#inbox_api input[type=checkbox]').each(function() {     
			if (this.checked && this.value!='on') {
				$.post('inbox_lib.php',{mode:'delete_api',idx:this.value},function(data) {
				});
			}
		});
		table.ajax.reload();
	}
});
<?php if(!isUserAdmin(strtolower($_SESSION['userid']))) { ?>
	table.column(3).visible(false);
<?php } else { ?>
	table.column(3).visible(true);
<?php } ?>
