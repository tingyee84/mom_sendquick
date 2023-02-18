<?php header("Content-type: text/javascript");
require_once('lib/commonFunc.php');
$xml_common = GetLanguage("common",$lang);
$x = GetLanguage("inbox",$lang);
?>

$('#from').val(moment().format('DD/MM/YYYY'));
$('#to').val(moment().format('DD/MM/YYYY'));
$('#from, #to').datepicker({format: 'dd/mm/yyyy'});
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
		{data:0,width:"125px"},
		{data:1,width:"125px",render:function(data,type,row) {
			if (type==='display')
					return data+"<a href='send_sms.php?mobile_numb="+encodeURIComponent(data)+"'><i class='fa fa-comment' title='Reply'></i></a>";
				else
					return data;
		}},
		{data:2},
		{data:3,orderable:false,width:"50px"}
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
			return toCallDate('<?php echo $_SESSION['userid']; ?>_Inbox_',new Date());
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
			return toCallDate('<?php echo $_SESSION['userid']; ?>_Inbox_',new Date());
		},
		init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-success btn-sm");
		}}
	]
} );
var filename = '<?php echo $_SESSION['userid']; ?>_Inbox_'+toCallDate(new Date());
table.buttons().container().appendTo('#export');
}
$('#from, #to').on('changeDate', function() {
	$('#from, #to').datepicker('hide');
	table.ajax.reload();
});
// assmi
// $('#all').change(function () {
// 	var cells = table.cells().nodes();
// 	$(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
// });	
$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});
$("#inbox").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});
$("#inbox").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});
$('#inbox').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});
// assmi
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
		$.post('inbox_lib.php',{mode:'emptyInbox'},function(data) {
			table.ajax.reload();
		});
	}
});