<?php 
header("Content-type:text/javascript");
include_once("../lib/commonFunc.php");
$x = GetLanguage("unsubscribe_list",$lang);
?>

var table =  $('#unsub_keyword').DataTable({
  	autoWidth: false,
	deferRender: true,
	processing: true,
	stateSave: true,
	ajax: {type:'POST',url:'unsubscribe_lib.php',data:{mode:'listKeyword'}},
	columnDefs:[{"orderable":false,"targets":2}]
});
// assmi
// $('#all').change(function () {
// 	var cells = table.cells().nodes();
//     $(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
// });

$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#unsub_keyword").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});

$("#unsub_keyword").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#unsub_keyword').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});

// assmi
$('#myCreate').on('show.bs.modal', function(e){
	$("#myCreate_header").show();
});
$('#myCreate').on('submit', function(e) {
	if(!txvalidator($("#keyword").val(),"TX_STRING","-")){
	   	$("#keyword").addClass("is-invalid");
	}
	else if(!validateSize($("#keyword").val(),"KEY")){
		$("#keyword").addClass("is-invalid");
	}
	else{
			$.post('unsubscribe_lib.php',$('#kwd_form').serialize(),function(res) {
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
	$('#keyword').removeClass("is-invalid");
	$(this).find('form').trigger('reset');
});
$('#delete').on('click', function(e) {
	if(confirm('<?php echo $x->alert_2;?>')) {
		$('input[type=checkbox]').each(function() {     
			if (this.checked && this.value!='on') {
				$.post('unsubscribe_lib.php',{mode:'deleteKeyword',id:this.value},function() {
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e) {
	if(confirm('<?php echo $x->alert_3;?>')) {
		$.post('unsubscribe_lib.php',{mode:'emptyKeyword'},function() {
			table.ajax.reload();
		});
	}
});
$('#myResponse').on('show.bs.modal', function(e){
	$("#myResponse_header").show();
});
$('#myResponse').on('show.bs.modal', function(e) {
	$("#resp_msgstatusbar").removeClass("alert-success");
	$("#resp_msgstatusbar").removeClass("alert-warning");
	$("#resp_msgstatusbar").hide();
	$('#unsub_resp').removeClass("is-invalid");
	var modal = $(this);
	$.post('unsubscribe_lib.php',{mode:'getResponseMessage'},function(val) {
		modal.find('#unsub_resp').html(val);
		var length = val.length;
		$('#textcount').val(160-length);
	},"json");
});
$('#myResponse').on('submit', function(e) {	
	if(!validateSize($("#unsub_resp").val(),"SHORTMSG")){
		$("#unsub_resp").addClass("is-invalid");
	}else{
		$.post('unsubscribe_lib.php',$("#response_form").serialize(),function(res) {
			if(res.flag == 0){	
				$("#resp_msgstatusbar").removeClass("alert-success");
				$("#resp_msgstatusbar").addClass("alert-warning");
				$("#resp_msgstatustext").html(res.status);
				$("#resp_msgstatusbar").show();
				$('#'+res.field).focus();
			}else if(res.flag == 2){
				$("#resp_msgstatusbar").removeClass("alert-success");
				$("#resp_msgstatusbar").addClass("alert-warning");
				$("#resp_msgstatustext").html(res.status);
				$("#resp_msgstatusbar").show();												
			}else{
				$('#myResponse').modal('hide');
			}		
		},'json');
	}	
	e.preventDefault();
});
$('#keyword').on('change keyup', function(e){
	$('#keyword').removeClass("is-invalid");
});
$('#unsub_resp').on('change keyup', function(e){
	$('#unsub_resp').removeClass("is-invalid");
});
$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});
$('#unsub_resp').on('change keyup', function(e){
	var length = $('#unsub_resp').val().length;
	$('#textcount').val(160-length);
	if (length > 160) {
		//alert('Sorry, you are over the limit of 160 characters');
		$('#unsub_resp').addClass("is-invalid");
		var substr = $('#unsub_resp').val().substring(0,160);
		$('#unsub_resp').val(substr);
		var length2 = substr.length;
		$('#textcount').val(160-length2);
		this.focus();
	}else{
		$('#unsub_resp').removeClass("is-invalid");
	}
});