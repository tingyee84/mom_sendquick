<script language="javascript" src="js/txvalidator.js"></script>
<script language="javascript" src="js/txcommon.js"></script>
<script src="js/user_department_js_ext.php" defer></script>
<!-- <script nonce="<?php //echo session_id();?>">
var table = $('#dept').DataTable({
	deferRender: true,
	stateSave: true,
	ajax: 'user_department_lib.php?mode=listDepartment',
	columnDefs: [{'targets': 3,'searchable': false,'orderable': false,'width':'10px'}]
});

// assmi
// $("#all").change(function () {
// 	var cells = table.cells().nodes();
//     $(cells).find(':checkbox:enabled').prop('checked', $(this).is(':checked'));
// });

$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#dept").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});

$("#dept").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#dept').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});

// assmi

$('#myDept').on('submit', function(e)
{
	if(!txvalidator($("#department").val(),"TX_STRING")){
		$('#department').addClass("is-invalid");
	}
	else if(!validateSize($("#department").val(),"NAME")){
		$('#department').addClass("is-invalid");
	}
	else if(!$('#enable_unlimited').is(":checked") && !txvalidator($("#quota_left").val(),"TX_INTEGER")){
		$('#quota_left').addClass("is-invalid");
	}
	else if($('#frequency').val() != 3 && !txvalidator($("#topup_value").val(),"TX_INTEGER")){
		$('#topup_value').addClass("is-invalid");
	}
	else{
		$.ajax({
			cache: false,
			url: 'user_department_lib.php',
			data: $("#department_form").serialize(),
			type: 'POST',
			dataType:'json',
			success: function(res){
				//alert(res.status);
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
				}
				else{
					table.ajax.reload();
					$('#myDept').modal('hide');
				}			
			}
		});
	}	
	e.preventDefault();
});

$('#department').on('change keyup', function(e){
	$('#department').removeClass("is-invalid");	
});

$('#quota_left').on('change keyup', function(e){
	$('#quota_left').removeClass("is-invalid");	
});

$('#topup_value').on('change keyup', function(e){
	$('#topup_value').removeClass("is-invalid");
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

//Clear Modal : Added by Wafie @ 08/12/2016
$('#myDept').on('hidden.bs.modal', function () {
	$(this).find('form').trigger('reset');	
	$("#msgstatusbar").removeClass("alert-success");
	$("#msgstatusbar").removeClass("alert-warning");
	$("#msgstatusbar").hide();
	$('#department').removeClass("is-invalid");
	$('#quota_left').removeClass("is-invalid");
	$('#topup_value').removeClass("is-invalid");
});
//Clear Modal End
$('#delete').on('click', function(e)
{
	if(confirm('<?php //echo $x->alert_3; ?>')) {
		$('input[type=checkbox]').each(function() {
			if (this.checked && this.value!='on') {
				$.post('user_department_lib.php?mode=deleteDepartment', { id: this.value }, function(data) {
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e)
{
    if(confirm('<?php //echo $x->alert_4;?>')) {
		$.post('user_department_lib.php?mode=emptyDepartment', function(data) {
			if(data!='') {
				alert(data);
			} else {
				table.ajax.reload();
			}
		});
	}
});

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});

function loadcheck(chkobj,url,val,name)
{
	$('#chkobj input[type=checkbox]').remove();
	$.getJSON(url,function(data)
	{
		$.each(data, function(index, value) {
			var index1 = index+1;
			$(chkobj).append('<tr><td>'+index1+'</td><td>'+value['description']+'</td><td>'+value['name']+'</td><td><input type="checkbox" name="mimroute[]" value="'+value['id']+'"/></td></tr>');
		});
	});
}

loadcheck('#mimroute','user_department_lib.php?mode=getMIMRoute','id','description');

$('#myDept').on('show.bs.modal', function(e)
{	
	$("#myDept_header").show();
	var modal = $(this), id = $(e.relatedTarget).data('id');
	$( "#quota_left_remark" ).text( "Quota Left: N/A" );
	
	if(typeof id === "undefined") {
		modal.find('#header').html('<?php //echo $x->create_new; ?>');
		modal.find('#mode').val('addDepartment');
		modal.find('#department').prop('readonly', false);
		
		modal.find('#enable_unlimited').prop( 'checked', false );
		modal.find('#quota_left').val('');
		modal.find('#frequency').val('3');
		modal.find('#topup_value').val('');
		modal.find('#topup_value').prop('disabled',true);
		
		modal.find( "#quota_left_remark" ).text( "" );
		modal.find( "#quota_left_remark" ).hide();				
	} else {
		
		$.ajax({
			cache: false,
			url: 'user_department_lib.php',
			data:'mode=editDepartment&id='+id,
			type:'POST',
			dataType:'json',
			success: function(val)
			{
				console.log( val );
				modal.find('#id').val(id);
				modal.find('#header').html('<?php //echo $x->edit; ?>');
				modal.find('#mode').val('saveDepartment');
				modal.find('#department').val(val.department);
				modal.find('#department').prop('readonly', true);
				if(val.bot_string.length > 0) {
					if (val.bot_string.indexOf(",") >= 0) {
						var bot_arr = val.bot_string.split(',');
						for (var i=0; i < bot_arr.length; i++) {
							modal.find('input:checkbox[value='+bot_arr[i]+']').prop("checked", true);
						}
					} else {
						modal.find('input:checkbox[value='+val.bot_string+']').prop("checked", true);
					}
				} else {
					$("input:checkbox").prop('checked', false);
				}
				
				if( val.unlimited_quota == '1' ){
					modal.find('#enable_unlimited').prop( 'checked', true );
				}else{
					modal.find('#enable_unlimited').prop( 'checked', false );
				}

				if(val.frequency == '3'){					
					modal.find('#topup_value').prop('disabled',true);
				}else{
					modal.find('#topup_value').prop('disabled',false);
				}
			
				modal.find('#quota_left').val(val.quota_left);
				modal.find('#frequency').val(val.frequency);
				modal.find('#topup_value').val(val.quota_limit);
				
				modal.find( "#quota_left_remark" ).show();
				modal.find( "#quota_left_remark" ).text( "Quota Left: " + val.quota_left );
				
			}
		});
	}
});

</script> -->
