<?php
header("Content-type:text/javascript");
require_once('lib/commonFunc.php');
$x = GetLanguage("file_upload_status",$lang);
$id = $_GET['id'];
?>
$( document ).ready(function() {
	//document.getElementById("valid_mobile_btn").style.color = "black";
	//document.getElementById("invalid_mobile_btn").style.color = "white";
});

var table = $('#upload_file_status').DataTable({
	
	//pageLength:50,
	//bLengthChange: false,
	//lengthMenu: [[10,50, 100], [10,50,100]],
	lengthMenu: [[100,500,1000], [100,500,1000]],
	pageLength: 100,
	processing: true,
	serverSide: true,
	deferRender: false,
	stateSave: true,
	ajax: 'broadcast_sms_status_list_lib.php?mode=list&id=<?php echo $id;?>&data_type='+$('#data_type').val(),
	columnDefs: [
		{ "width": "50px", "targets": 0 },
		{ "width": "100px", "targets": 1 },
		{ "width": "300px", "targets": 2 },
		{ "width": "300px", "targets": 3 },
		{ "width": "50px", "targets": 4 },
		{ "width": "50px", "targets": 5 },
	],
	
	initComplete: function( settings, json ) {
		$('[data-bs-toggle="tooltip"]').tooltip({ 
		
			container: 'body'
			
		});
		
	},
	
	drawCallback: function ( settings ) {
	   
	   $('[data-bs-toggle="tooltip"]').tooltip({ 
		
			container: 'body'
			
		});
		
	}

});

//table.page.len( 100 ).draw();

$('#cfm_send').click(function () {
	
	show_confirm( '<?php echo $x->cfm_send_msg1;?>' );
	
});

function show_confirm(message) {
	
	var url = "broadcast_sms_status_list_lib.php?mode=cfm_send";
	
	show_confirm_message({
		
		message: message,
	
		executeYes: function() {
			
			var jqxhr = $.post( url, {id: <?php echo $_GET['id'];?>}, function( data ) {
				
				//alert(data);
				//return false;
				if( data == '1' ){
					
					$('#output').html('<?php echo $x->processing_send_sms?>');
					$("#status").addClass("alert-info").removeClass("alert-danger");
					$('#status').removeClass('hidden');
					$('#cfm_send').prop('disabled', true);
					
				}else{
					
					$('#output').html('<?php echo $x->process_send_sms_failed?>');
					$("#status").addClass("alert-danger").removeClass("alert-info");
					$('#status').removeClass('hidden');
				
				}
				
			})
			.done(function() {
				//alert( "second success" );
			})
			.fail(function() {
				alert( "error" );
			})
			.always(function() {
				//alert( "finished" );
			});
			
		},
		executeNo: function() {
			//nothing to do
		}
	
	});
	
}

$('#valid_mobile_btn').click(function ( event ) {
	
	event.preventDefault();
	
	$("#invalid_mobile_btn").removeClass("active");
	$("#valid_mobile_btn").addClass("active");
	
	//alert('valid');
	//document.getElementById("valid_mobile_btn").style.color = "black";
	//document.getElementById("invalid_mobile_btn").style.color = "white";
	
	$('#data_type').val('valid');
	var url = 'broadcast_sms_status_list_lib.php?mode=list&id=<?php echo $id;?>&data_type='+$('#data_type').val();
	//alert(url);
	table.ajax.url( url ).load();
	
});

$('#invalid_mobile_btn').click(function ( event ) {
	
	event.preventDefault();
	
	$("#valid_mobile_btn").removeClass("active");
	$("#invalid_mobile_btn").addClass("active");
	
	//document.getElementById("valid_mobile_btn").style.color = "white";
	//document.getElementById("invalid_mobile_btn").style.color = "black";
	
	$('#data_type').val('invalid');
	var url = 'broadcast_sms_status_list_lib.php?mode=list&id=<?php echo $id;?>&data_type='+$('#data_type').val();
	//alert(url);
	table.ajax.url( url ).load();
	
});

$('#back').click(function () {
	
	window.location = 'broadcast_sms_status.php';
   
});

$("#all").change(function () {
	var cells = table.cells().nodes();
    $(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
});

$('#delete').on('click', function(e){
	
    if(confirm('<?php echo $x->msg1; ?>')) {
		
		$('input[type=checkbox]').each(function() {
			
			if (this.checked && this.value!='on') {
				
				$.post('broadcast_sms_status_list_lib.php?mode=delete', { data_id: this.value }, function(data) {
					//alert(data);
					table.ajax.reload();
				});
				
			}
		});
		
		$('#all').prop('checked',false);
		
	}
	
});

function getTotalSMS(){
	
	$.post('broadcast_sms_status_list_lib.php?mode=TotalSMS', { id: '<?php echo $id;?>' }, function(data) {
		//alert(data);
		$('#total_sms_div').html(data);
	});
				
}

function updateCfmSend(){
	
	$.post('broadcast_sms_status_list_lib.php?mode=updateCfmSend', { id: '<?php echo $id;?>' }, function(data) {
		
		//alert( data );
		//return false;
		
		var return_data = JSON.parse( data );
		var status = return_data.status;
		var msg = return_data.msg;
	
		if( status == '1' ){
			$('#cfm_send').prop('disabled', true);
			
			$("#add_on_tr").addClass("table-danger").removeClass("table-info");
			$('#add_on_td').html( msg );
			
		}else{
			$('#cfm_send').prop('disabled', false);
			
			$("#add_on_tr").addClass("table-info").removeClass("table-danger");
			$('#add_on_td').html( msg );
		}
		
	});
	
}

window.setInterval(function(){
	
	getTotalSMS();
	updateCfmSend();

}, 5000);

getTotalSMS();
updateCfmSend();

$('.close').click(function() {
	$('#status').addClass('hidden');
});