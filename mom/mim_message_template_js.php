<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.html5.min.js"></script>
<script language="javascript" src="js/txvalidator.js"></script>
<script nonce="<?php echo session_id();?>">
var table = $('#tbl_tmpl').DataTable({
	autoWidth: false,
	processing: true,
	stateSave: true,
	ajax: {type:'POST',url:'message_template_lib.php',data:{mode:'listMIMTemplate'}},
	columnDefs: [
		{'orderable':false,'targets':3},
		{'width':"30%",'targets':0},
		{'width':"40%",'targets':1},
		{'width':"30%",'targets':2},
		{'width':"5%",'targets':3},
	]
});
var date = $.now();

/*
new $.fn.dataTable.Buttons( table, {
	buttons: [
		{extend:'csv',text:'<?php echo $xml_common->export.' CSV';?>',filename:'<?php echo $_SESSION['userid'];?>_MIMTemplateMessage_'+date},
		{extend:'excel',text:'<?php echo $xml_common->export.' Excel';?>',filename:'<?php echo $_SESSION['userid'];?>_MIMTemplateMessage_'+date}
	]
} );
*/

//table.buttons().container().appendTo('#export');

// assmi
// $('#all').change(function () {
// 	var cells = table.cells().nodes();
//     $(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
// });

$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#tbl_tmpl").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});

$("#tbl_tmpl").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#tbl_tmpl').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});

// assmi

$('#myCreate').on('show.bs.modal', function(e)
{
	$("#myCreate_header").show();
	var modal = $(this), id = $(e.relatedTarget).data('id');
	if(typeof id === "undefined") {
		modal.find('#header').html('<?php echo $x->create_new;?>');
		modal.find('#mode').val('addMIMTemplate');
	} else {
		modal.find('#header').html('<?php echo $x->edit_template;?>');
		modal.find('#mode').val('saveMIMTemplate');
		modal.find('#id').val(id);
		$.post('message_template_lib.php',{mode:'editMIMTemplate',id:id},function(val) {
			modal.find('#template').val(val.text);
			modal.find('#mim_tpl_id').val(val.mim_tpl_id);
			modal.find('#template_name').val(val.template_name);
			modal.find('#count_chars2').html(val.count_chars2);
		},"json");
	}
});
$('#myCreate').on('submit', function(e)
{
	if(!txvalidator($("#template_name").val(),"TX_STRING","SPACE")){
	   	$("#template_name").addClass("is-invalid");
	}
	else if(!txvalidator($("#mim_tpl_id").val(),"TX_STRING","-_")){
		$("#mim_tpl_id").addClass("is-invalid");
	}
	else{
		$.post('message_template_lib.php',$('#template_form').serialize(),function(res) {
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
	$('#template_name').removeClass("is-invalid");
	$('#mim_tpl_id').removeClass("is-invalid");
	$("#msgstatusbar").removeClass("alert-success");
	$("#msgstatusbar").removeClass("alert-warning");
	$("#msgstatusbar").hide();
	$(this).find('form').trigger('reset');
});
$('#delete').on('click', function(e)
{
	if(confirm('<?php echo $x->alert_3; ?>')) {
		$('input[type=checkbox]').each(function() {     
			if (this.checked && this.value!='on') {
				$.post('message_template_lib.php',{mode:'deleteMIMTemplate',id:this.value},function() {
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e)
{
	if(confirm('<?php echo $x->alert_4; ?>')) {
		$.post('message_template_lib.php',{mode:'emptyMIMTemplate'},function() {
			table.ajax.reload();
		});
	}
});
$('#myUpload').submit(function(){
	var formData = new FormData($('#upload_form')[0]);
    $.ajax({
        url: 'message_template_lib.php',
        type: 'POST',
        data: formData,
        async: false,
        contentType: false,
        processData: false,
        success: function(res){
			if(res==0) {
				alert('<?php echo $x->alert_5; ?>');
			} else {
				$('#myUpload').modal('hide');
				$('#prevUpload').modal('show');
			}
		}
  	});
    return false;
});
$('#myUpload').on('hidden.bs.modal', function(e){
	$(this).find('form').trigger('reset');
});
$('#prevUpload').on('show.bs.modal', function(e)
{
	$('#upload_table').DataTable({
		deferRender: true,
		processing: true,
		autoWidth: false,
		ajax: {type:'POST',url:'message_template_lib.php',data:{mode:'listTemplate',access_type:'2'}}
	});
});
$('#prevUpload').on('hidden.bs.modal', function(){
	$('#upload_table').dataTable().fnDestroy();
});
$('#preCancel').on('click', function(e){
	if(confirm('<?php echo $x->alert_6; ?>')) {
		$.post('message_template_lib.php',{mode:'deleteTemplate',access_type:'1'},function() {
			$('#prevUpload').modal('hide');
		});
	}
});
$('#prevUpload').on('submit', function(e)
{
	$.post('message_template_lib.php',$('#upload_view').serialize(),function(data) {
		if(data!='') {
			alert(data);
		} else {
			table.ajax.reload();
			$('#prevUpload').modal('hide');
		}
	});
	e.preventDefault();
});

$('#template').on('keyup change',function() {
		updateCounter();
});

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});

$('#template_name').on('change keyup', function(e){
	$('#template_name').removeClass("is-invalid");
});

$('#mim_tpl_id').on('change keyup', function(e){
	$('#mim_tpl_id').removeClass("is-invalid");
});

function updateCounter() {
		var message_text = $('#template').val();
		//checkUnicodeChar(message_text);
		var code_type = "text";		
		// var text_length = message_text.length;
		var text_length = message_text.replace(/\r(?!\n)|\n(?!\r)/g, "\r\n").length;					
		$("#count_chars").val(text_length);
		$("#count_chars2").html(text_length);		
}
</script>
