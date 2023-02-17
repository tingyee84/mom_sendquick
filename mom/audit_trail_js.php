<!-- <script src="js/moment.min.js"></script> -->
<script src="js/audit_trail_js_ext.php"></script>

<!-- <script nonce="<?php //echo session_id();?>">
$('#from').val(moment().format('DD/MM/YYYY'));
$('#to').val(moment().format('DD/MM/YYYY'));
$('#from, #to').datepicker({format: 'dd/mm/yyyy'});
$.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');
var table = $('#audittrail').DataTable({
    responsive: true,
	deferRender: true,
	processing: true,
	stateSave: true,
	pageLength: 100,
	columnDefs: [ 
		{ "width": "40%", "targets": 4 },
		
		{ responsivePriority: 1, targets: 0 },
		{ responsivePriority: 2, targets: 1 },
		{ responsivePriority: 3, targets: 2 },
	],
	ajax:{type: 'POST',
		url: 'audit_trail_lib.php',
		data: function () { 
			console.log("return");
			return $('#auditTrailForm').serialize(); 
		}
	}
});

new $.fn.dataTable.Buttons( table, {
    // buttons: [
	// 	{extend:'csv', text: '<?php //echo $xml_common->save;?> CSV', filename:'Audit'}, 
	// 	{extend:'excel', text: '<?php //echo $xml_common->save;?> Excel', filename:'Audit'}
	// ]
	buttons: [
		{
			extend:'csv', 
			text: '<i class="fa fa-file-text-o"></i> <?php //echo $xml_common->save.' CSV'; ?>',
			filename:'Audit',
			init: function(api,node,config){
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			}
		}, 
		{
			extend:'excel', 
			text: '<i class="fa fa-file-excel-o"></i> <?php //echo $xml_common->save.' Excel'; ?>',
			filename:'Audit',
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-success btn-sm");
			}
		}
	]
} );

table.buttons().container().appendTo('#export');
$('#from, #to').on('changeDate', function() {
	$('#from, #to').datepicker('hide');
	table.ajax.reload();
});
$('#reload').on("click",function() {
	table.ajax.reload();
});

$.fn.dataTable.ext.errMode = 'none';
table.on('error.dt', function (e,settings,techNote,message) {
	alert(message);
});
</script> -->