<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.html5.min.js"></script>
<script language="javascript" src="js/txvalidator.js"></script>
<script src="js/unsubscribe_list_js_ext.php"></script>
<!-- <script nonce="<?php //echo session_id();?>">
$("#unsub_file").change(function () {
	var fileExtension = ['csv', 'txt'];
	if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
		alert("Only formats are allowed : "+fileExtension.join(', '));
		$('#unsub_file').val('');
	}
	
});

var table = $('#unsubscribe').DataTable({
	autoWidth: false,
	deferRender: true,
	processing: true,
	stateSave: true,
	ajax: {type:'POST',url:'unsubscribe_lib.php',data:{mode:'listUnsubscribe'}},
	columnDefs: [{"orderable":false,"targets":4}]
});

// assmi

$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#unsubscribe").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});

$("#unsubscribe").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#unsubscribe').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});

// assmi

var date = $.now();
new $.fn.dataTable.Buttons( table, {
	// buttons: [
	// 	{extend:'csv', text: '<?php //echo $xml_common->export.' CSV'; ?>', exportOptions: {columns: [ 0, 1, 2, 3]}, filename:'<?php //echo $_SESSION['userid']; ?>_unsubscribe_list_'+date}
	// 	//{extend:'excel', text: '<?php //echo $xml_common->export.' Excel'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php //echo $_SESSION['userid']; ?>_unsubscribe_list_'+date}
	// ]
	buttons: [
		{
			extend:'csv', 
			text: '<i class="fa fa-file-text-o"></i> <?php //echo $xml_common->export.' CSV'; ?>', 
			exportOptions: {columns: [ 0, 1, 2, 3]}, 
			filename:'<?php //echo $_SESSION['userid']; ?>_unsubscribe_list_'+date,
			init: function(api,node,config){
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");				
			}
		}
		//{extend:'excel', text: '<?php //echo $xml_common->export.' Excel'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php //echo $_SESSION['userid']; ?>_unsubscribe_list_'+date}
	]
} );
table.buttons().container().appendTo('#export');

$('#all').change(function () {
	var cells = table.cells().nodes();
    $(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
});

$('#myCreate').on('show.bs.modal', function(e){
	$("#myCreate_header").show();
});

$('#myCreate').on('submit', function(e) {
    if(!txvalidator($("#number").val(),"TX_SGMOBILEPHONE")){
	   	$("#number").addClass("is-invalid");
	}else{
		$.post('unsubscribe_lib.php',$('#unsub_form').serialize(),function(res) {
				if(res.flag == 0){	
					$("#msgstatusbar").removeClass("alert-success");
					$("#msgstatusbar").addClass("alert-warning");
       				$("#msgstatustext").html(res.status);
					$("#msgstatusbar").show();
					$('#'+res.field).focus();
				}else if(res.flag == 2){
					$("#msgstatusbar").removeClass("alert-success");
					$("#msgstatusbar").addClass("alert-warning");
       				$("#msgstatustext").html(res.status);
					$("#msgstatusbar").show();												
				}else{
					table.ajax.reload();
					$('#myCreate').modal('hide');
				}
		},'json');
	}
	
	e.preventDefault();
});
$('#myCreate').on('hidden.bs.modal', function () {
	$("#msgstatusbar").removeClass("alert-success");
	$("#msgstatusbar").removeClass("alert-warning");
	$("#msgstatusbar").hide();
	$('#number').removeClass("is-invalid");
	$(this).find('form').trigger('reset');
});
$('#delete').on('click', function(e) {
	if(confirm('<?php //echo $x->alert_2;?>')) {
		$('input[type=checkbox]').each(function() {     
			if (this.checked && this.value!='on') {
				$.post('unsubscribe_lib.php',{mode:'deleteUnsubscribe',id:this.value},function() {
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e) {
	if(confirm('<?php //echo $x->alert_3;?>')) {
		$.post('unsubscribe_lib.php',{mode:'emptyUnsubscribe'},function() {
			table.ajax.reload();
		});
	}
});
$('#myUpload').submit(function() {
    var formData = new FormData($('#upload_form')[0]);
    $.ajax({
        url: 'unsubscribe_lib.php',
        type: 'POST',
        data: formData,
        async: false,
        contentType: false,
        processData: false,
        success: function(res){
			if(res==0) {
				alert('<?php //echo $x->alert_4; ?>');
			} 
			else if(res==2){
				alert('<?php //echo $x->alert_5; ?>');
			}
			else if(res==3){
				alert('<?php //echo $msgstr->invalid_number; ?>');
			}	
			else if(res==4){				
				alert('<?php //echo $msgstr->unsubexist; ?>');
			}			
			else {
				table.ajax.reload();
				$('#myUpload').modal('hide');
			}
		}
  	});
    return false;
});
$('#myUpload').on('hidden.bs.modal', function () {
	$(this).find('form').trigger('reset');
});
$('#number').on('change keyup', function(e){
	$('#number').removeClass("is-invalid");
});
$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});
</script> -->

