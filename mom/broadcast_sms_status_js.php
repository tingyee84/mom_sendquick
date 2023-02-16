<?php
header("Content-type:text/javascript");
require_once('lib/commonFunc.php');
$x = GetLanguage("file_upload_status",$lang); 
?>
$('#date_from, #date_to').datepicker({format: 'dd-mm-yyyy'});
$('#date_from').val( '<?php echo date( "d-m-Y", ( time() - ( 60 * 60 * 24 ) ) )?>');
$('#date_to').val( '<?php echo date( "d-m-Y", time() )?>');

var table = $('#upload_file_status').DataTable({
	deferRender: false,
	stateSave: true,
	ajax: 'broadcast_sms_status_lib.php?mode=list&date_from='+$('#date_from').val()+'&date_to='+$('#date_to').val(),
	columnDefs: [
		{ "orderable": false, "targets": 4 },
		{ "orderable": false, "targets": 5 },
		{ "width": "15%", "targets": 0 },
		{ "width": "25%", "targets": 1 },
		{ "width": "10%", "targets": 2 },
		{ "width": "30%", "targets": 3 },
		{ "width": "15%", "targets": 4 },
		{ "width": "5%", "targets": 5 }

	],

});

// assmi
$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#upload_file_status").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});

$("#upload_file_status").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#upload_file_status').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});

// assmi


$('#create').click(function () {
	
	window.location = 'broadcast_sms.php';
   
});

window.setInterval(function(){
	
	//alert('1');
	table.ajax.reload();

}, 10000);

// $("#all").change(function () {
// 	var cells = table.cells().nodes();
//     $(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
// });

$('#delete').on('click', function(e){
	
    if(confirm('<?php echo $x->msg1; ?>')) {
		
		$('input[type=checkbox]').each(function() {
			
			if (this.checked && this.value!='on') {
				
				$.post('broadcast_sms_status_lib.php?mode=delete', { id: this.value }, function(data) {
					//alert(data);
					table.ajax.reload();
				});
				
			}
		});
		
		$('#all').prop('checked',false);
		
	}
	
});

$("#date_from, #date_to").change(function(){
	
	var url = 'broadcast_sms_status_lib.php?mode=list&date_from='+$('#date_from').val()+'&date_to='+$('#date_to').val();
	//alert(url);
	table.ajax.url( url ).load();

});
