<script src="js/moment.min.js"></script>
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/datetime-moment.js"></script>
<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.html5.min.js"></script>
<script src="js/common_inbox_js_ext.php" defer></script>
<!-- <script nonce="<?php //echo session_id();?>">
var strdelete = '<?php //echo $x->alert_2;?>';
var stralert = '<?php //echo $x->alert_3; ?>';	
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
		url: 'common_inbox_lib.php',
		data: function(){return $('#inboxForm').serialize();}
	},
	columns: [
	{data:0,width:"150px"},
	{data:1,width:"125px",render:function(data,type,row) {
		if (type==='display')
				return data+"<a href='send_sms.php?mobile_numb="+encodeURIComponent(data)+"'><i class='fa fa-comment' title='Reply'></i></a>";
			else
				return data;
	}},
	{data:2},
	{data:3,orderable:false,width:"10px"}
	]
});

function toCallDate(filename,ndate) {
	return  filename+moment(ndate).format("YYYY-MM-DD hhmmss");
}
var date = $.now();
new $.fn.dataTable.Buttons( table, {
	buttons: [
		{
			extend:'csv',
			text: '<i class="fa fa-file-text-o"></i> <?php //echo $xml_common->export.' CSV'; ?>',
			exportOptions: {columns: ':visible'},
			filename: function() {
				return toCallDate('<?php //echo $_SESSION['userid']; ?>_Common_',new Date());
			},
			init: function(api,node,config){
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			}},
		{
			extend:'excel',
			text: '<i class="fa fa-file-excel-o"></i> <?php //echo $xml_common->export.' Excel'; ?>',
			exportOptions: {columns: ':visible'},
			filename: function() {
				return toCallDate('<?php //echo $_SESSION['userid']; ?>_Common_',new Date());
			},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-success btn-sm");
			}}
	]
} );
var filename = '<?php //echo $_SESSION['userid']; ?>_Common_'+toCallDate(new Date());

table.buttons().container().appendTo('#export');
$('#from, #to').on('changeDate', function() {
	$('#from, #to').datepicker('hide');
	table.ajax.reload();
});
$('#all').change(function () {
	var cells = table.cells().nodes();
    $(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
});
$('#delete').on('click', function(e) {
    if(confirm(strdelete)) {
		$('input[type=checkbox]').each(function() {     
			if (this.checked && this.value!='on') {
				$.post('common_inbox_lib.php',{mode:'delete',idx:this.value},function(data) {
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e) {
	if(confirm(stralert)) {
		$.post('common_inbox_lib.php',{mode:'truncate'},function(data) {
			table.ajax.reload();
		});
	}
});
</script> -->