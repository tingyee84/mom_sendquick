<?php 
header("Content-type:text/javascript");
include_once("../lib/commonFunc.php");
$x = GetLanguage("user_role",$lang);
?>

var table = $('#role').DataTable({
	deferRender: true,
	stateSave: true,
	ajax: 'user_role_lib.php?mode=listUserRole',
	columnDefs: [{'targets': 2,'searchable': false,'orderable': false}]
});
// assmi
// $('#all').change(function () {
// 	var cells = table.cells().nodes();
//     $(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
// });

$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#role").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});

$("#role").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#role').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});

// assmi



loadlist('#department','user_department_lib.php?mode=getDepartmentList','department_id','department');
$('#access_list').load('user_role_lib.php?mode=getAccessRightsList');
$('#myRole').on('show.bs.modal', function(e)
{
	$("#myRole_header").show();
	var modal = $(this), id = $(e.relatedTarget).data('id');
	if(typeof id === "undefined") {
		modal.find('#header').html('<?php echo $x->create_new; ?>');
		modal.find('#mode').val('addUserRole');
		modal.find('#department').val(0);
	} else {
		$.ajax({
			cache: false,
			url: 'user_role_lib.php',
			data:'mode=editUserRole&id='+id,
			type:'POST',
			dataType:'json',
			success: function(val)
			{
				modal.find('#header').html('<?php echo $x->edit_role; ?>');
				modal.find('#mode').val('saveUserRole');
				modal.find('#id').val(id);
				modal.find('#user_role').val(val.user_role);
				modal.find('#user_role').prop('disabled', true);
				modal.find('#department').val(val.department);
				if(val.access_string.length > 0) {
					if (val.access_string.indexOf(",") >= 0) {
						var group_arr = val.access_string.split(',');
						for (var i=0; i < group_arr.length; i++) {
							modal.find('input:checkbox[value='+group_arr[i]+']').prop("checked", true);
							if(group_arr[i] == 1) {
								showhideLevel("1", true);
							}
							if(group_arr[i] == 2) {
								showhideLevel("2", true);
							}
						}
					} else {
						modal.find('input:checkbox[value='+val.group+']').prop("checked", true);
					}
				} else {
					$('input:checkbox').prop('checked', false);
				}
			}
		})
	}
});
$('#myRole').on('submit', function(e)
{
	if(!txvalidator($("#user_role").val(),"TX_STRING","_")){
		$('#user_role').addClass("is-invalid");
	}
	else if(!validateSize($("#user_role").val(),"NAME")){
		$('#user_role').addClass("is-invalid");
	}
	else{
		$.ajax({
			cache: false,
			url: 'user_role_lib.php',
			data: $("#role_form").serialize(),
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
					} else{
						table.ajax.reload();
						$('#myRole').modal('hide');
					}			
			}
		});
	}

	e.preventDefault();
});

$('#user_role').on('change keyup', function(e){
	$('#user_role').removeClass("is-invalid");	
});

//Clear Modal : Added by Wafie @ 08/12/2016
$('#myRole').on('hidden.bs.modal', function () {
	$(this).find('form').trigger('reset');
	showhideLevel("1", false);
	showhideLevel("2", false);
	$('#user_role').prop('disabled', false);
	$("#msgstatusbar").removeClass("alert-success");
	$("#msgstatusbar").removeClass("alert-warning");
	$("#msgstatusbar").hide();
	$('#user_role').removeClass("is-invalid");
});
//Clear Modal End
//Added by Wafie @ 25/11/2016
$('#delete').on('click', function(e)
{
	if(confirm('<?php echo $x->alert_3; ?>')) {
		$('input[type=checkbox]').each(function() {
			if (this.checked) {
				$.post('user_role_lib.php?mode=deleteUserRole', { id: this.value }, function(data) {
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e)
{
    if(confirm('<?php echo $x->alert_4;?>')) {
		$.post('user_role_lib.php?mode=emptyUserRole');
		table.ajax.reload();
	}
});
$(document).on('change', '#access1', function() {
    if(this.checked) { 
		showhideLevel("1",true);
	} else {
		showhideLevel("1",false);
	}
});
$(document).on('change', '#access2', function() {
    if(this.checked) { 
		showhideLevel("2",true);
	} else {
		showhideLevel("2",false);
	}
});

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});

function showhideLevel(function_type, option)
{
	var div = "";
	var div_td = "";

	if(function_type == 1)
	{
		var tmp = new Array('3', '5', '11', '12', '13', '14', '15', '16');
	}
	else if(function_type == 2)
	{
		var tmp = new Array('2');
	}

	$.each(tmp, function(index, value) {
		div = "div_" + value;
		div_td = div + "_td";
		clearLevelChecked(function_type);
		$("#" + div).toggle(option);
		$("#" + div_td).toggle(option);
	});
}
function clearLevelChecked(function_type)
{
	if(function_type == 1)
	{
		var tmp = new Array('17', '18', '19', '20', '21', '22', '23', '24');
	}
	else if(function_type == 2)
	{
		var tmp = new Array('26', '27', '28', '29', '30', '31', '32');
	}

	$('input:checkbox[name="access[]"]').each(function() {
		$.each(tmp, function(index, value) {
			if(this.value == value && this.checked)
			{
				$(this).prop('checked',false);
			}
		});
	});
}
function loadlist(selobj,url,val,name)
{
	$('#selobj option:gt(0)').remove();
	$.getJSON(url,function(data)
	{
		$.each(data, function(index, value) {
			$(selobj).append("<option value=" + value[val] + ">" + value[name] + "</option>");
		});
	});
}