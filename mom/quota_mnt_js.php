<script language="javascript" src="js/txvalidator.js"></script>
<script nonce="<?php echo session_id();?>">
loadUser();
var table = $('#quota').DataTable({
	autoWidth: false,
	deferRender: true,
	processing: true,
	stateSave: true,
	ajax: {type:'POST',url:'quota_mnt_lib.php',data:{mode:'view'}},
	columnDefs:[{"orderable":false,"targets":9}]
});

// assmi
// $('#all').change(function () {
// 	var cells = table.cells().nodes();
//     $(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
// });

$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#quota").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});

$("#quota").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#quota').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});

// assmi


$('#myQuota').on('show.bs.modal', function(e)
{	
	$("#myQuota_header").show();
	$( "#current_bal_p" ).text( "Quota Left: N/A" );	
	var modal = $(this), id = $(e.relatedTarget).data('id');
	if(typeof id === "undefined") {
		modal.find('#header').html('<?php echo $x->add_quota_profile;?>');
		modal.find('#mode').val('create');
		$('#frequency').prop('disabled',false);
		$('#topup_value').prop('disabled',false);
		$('#quota_left').prop('disabled',false);
		$('#quota_left').prop('required',true);
		$('#topup_value').prop('required',false);
		modal.find('#topup_value').prop('disabled',true);
		$('#user_div').removeClass('hidden');		
		
		modal.find( "#current_bal_p" ).text( "" );
		modal.find( "#current_bal_p" ).hide();
		
	} else {
		modal.find('#header').html('<?php echo $x->edit_quota_profile;?>');
		modal.find('#mode').val('update');
		modal.find('#id').val(id);
		$.post('quota_mnt_lib.php',{mode:'retrieve',id:id},function(val) {
			modal.find('#frequency').val(val.frequency);
			modal.find('#topup_value').val(val.limit);
			modal.find('#quota_left').val(val.left);
			if(val.unlimited == '1'){
				$('#enable_unlimited').prop('checked',true);
				$('#frequency').prop('disabled',true);
				$('#topup_value').prop('disabled',true);
				$('#quota_left').prop('disabled',true);
				$('#quota_left').prop('required',false);
			} else {
				$('#enable_unlimited').prop('checked',false);
				$('#frequency').prop('disabled',false);
				$('#topup_value').prop('disabled',false);
				$('#quota_left').prop('disabled',false);
				$('#quota_left').prop('required',true);
			}
			if(val.frequency == '3'){
				$('#topup_value').prop('required',false);	
				modal.find('#topup_value').prop('disabled',true);
			}else{
				$('#topup_value').prop('required',true);
				modal.find('#topup_value').prop('disabled',false);
			}
			
			modal.find( "#current_bal_p" ).show();
			modal.find( "#current_bal_p" ).text( "<?php echo $x->QuotaProfile_msg2;?>: " + ( val.left ? val.left  : '0' ) );
		
		},"json");
		$('#user_div').addClass('hidden');
		
	}
});
$('#myQuota').on('submit', function(e)
{
	if(!$('#enable_unlimited').is(":checked") && !txvalidator($("#quota_left").val(),"TX_INTEGER")){
	   	$("#quota_left").addClass("is-invalid");
	}else if($('#frequency').val() != 3 && !txvalidator($("#topup_value").val(),"TX_INTEGER")){
		$("#topup_value").addClass("is-invalid");
	}else{
		$.post('quota_mnt_lib.php',$("#quota_form").serialize(),function(res) {
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
				$('#myQuota').modal('hide');
			}			
		},'json');
	}	
	e.preventDefault();
});
$('#myQuota').on('hidden.bs.modal', function () {
	$("#msgstatusbar").removeClass("alert-success");
	$("#msgstatusbar").removeClass("alert-warning");
	$("#msgstatusbar").hide();
	$('#quota_left').removeClass("is-invalid");
	$('#topup_value').removeClass("is-invalid");
	$(this).find('form').trigger('reset');
});

$('#quota_left').on('change keyup', function(e){
	$('#quota_left').removeClass("is-invalid");
});

$('#topup_value').on('change keyup', function(e){
	$('#topup_value').removeClass("is-invalid");
});

$('#myAlert').on('show.bs.modal', function(e)
{
	$("#myAlert_header").show();
	var modal = $(this);
	$.post('quota_mnt_lib.php',{mode:'getAlert'},function(val) {
		$("input[name=alert_type][value='"+val.alert_type+"']").prop("checked",true);
		modal.find('#alert_email').val(val.alert_email);
		modal.find('#alert_credit').val(val.alert_credit);
	},"json");
});

$('#myAlert').on('submit', function(e)
{
	if(!txvalidator($("#alert_email").val(),"TX_EMAILADDR")){
	   	$("#alert_email").addClass("is-invalid");		 
	}
	else if(!txvalidator($("#alert_credit").val(),"TX_INTEGER")){
		$("#alert_credit").addClass("is-invalid");	  
	}
	else{
		$.post('quota_mnt_lib.php',$("#alert_form").serialize(),function(res) {
			if(res.flag == 0){	
				$("#alert_msgstatusbar").removeClass("alert-success");
				$("#alert_msgstatusbar").addClass("alert-warning");
				$("#alert_msgstatustext").html(res.status);
				$("#alert_msgstatusbar").show();
				$('#'+res.field).focus();
			}else if(res.flag == 2){
				$("#alert_msgstatusbar").removeClass("alert-success");
				$("#alert_msgstatusbar").addClass("alert-warning");
				$("#alert_msgstatustext").html(res.status);
				$("#alert_msgstatusbar").show();												
			}else{
				$('#myAlert').modal('hide');
			}
		},'json');
	}	
	e.preventDefault();
});
$('#delete').on('click', function(e){
    if(confirm('<?php echo $x->alert_3 ?>?')) {
		$('input[type=checkbox]').each(function() {     
			if (this.checked && this.value!='on') {
				$.post('quota_mnt_lib.php',{mode:'delete',id:this.value}, function() {
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
function loadUser(){
	$.post('quota_mnt_lib.php',{mode:'listUser'},function(data) {
		//alert(data);
		
		$.each(data, function(index, value) {
			$('#userid').append("<option value="+value['userid']+">"+value['userid']+"</option>");
		});
		
	},"json");
}
$('#enable_unlimited').click(function(){
	if($(this).is(':checked')) {
		$('#frequency').val('3');
		$('#frequency').prop('disabled',true);
		$('#topup_value').prop('disabled',true);
		$('#quota_left').prop('disabled',true);
		$('#quota_left').prop('required',false);
	} else {
		$('#frequency').prop('disabled',false);
		$('#topup_value').prop('disabled',false);
		$('#quota_left').prop('disabled',false);
		$('#quota_left').prop('required',true);
	}
});
$('#frequency').change(function() {
	if($(this).val()=='3'){
		$('#topup_value').prop('required',false);	
		$('#topup_value').prop('disabled',true);
	}else{
		$('#topup_value').prop('required',true);
		$('#topup_value').prop('disabled',false);
	}
});
$('#global').on('submit', function(e)
{
	if(!txvalidator($("#value").val(),"TX_INTEGER")){
		$("#value").addClass("is-invalid");	  
	}
	else{
		$.post('quota_mnt_lib.php',$('#global').serialize(), function(res) {
		if(res.flag == 0){	
				$("#all_quota_msgstatusbar").removeClass("alert-success");
				$("#all_quota_msgstatusbar").addClass("alert-warning");
				$("#all_quota_msgstatustext").html(res.status);
				$("#all_quota_msgstatusbar").show();
				$('#'+res.field).focus();
			}else if(res.flag == 2){
				$("#all_quota_msgstatusbar").removeClass("alert-success");
				$("#all_quota_msgstatusbar").addClass("alert-warning");
				$("#all_quota_msgstatustext").html(res.status);
				$("#all_quota_msgstatusbar").show();												
			}else{
				table.ajax.reload();
			}		
		},"json");
	}	
	e.preventDefault();
});

$('#option').change(function() {
	if($(this).val()=='1'){
		$('#value').val('');
		$('#value').prop('disabled',true);	
	}else{
		$('#value').prop('disabled',false);
		$('#value').prop('required',true);
	}
});

$('#alert_email').on('change keyup', function(e){
	$('#alert_email').removeClass("is-invalid");
});

$('#alert_credit').on('change keyup', function(e){
	$('#alert_credit').removeClass("is-invalid");
});

$('#value').on('change keyup', function(e){
	$('#value').removeClass("is-invalid");
});

$('#myAlert').on('hidden.bs.modal', function () {
	$("#alert_msgstatusbar").removeClass("alert-success");
	$("#alert_msgstatusbar").removeClass("alert-warning");
	$("#alert_msgstatusbar").hide();
	$('#alert_email').removeClass("is-invalid");
	$('#alert_credit').removeClass("is-invalid");
	$(this).find('form').trigger('reset');
});

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});
</script>
