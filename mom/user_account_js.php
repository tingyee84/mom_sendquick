<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.html5.min.js"></script>
<script src="js/txvalidator.js"></script>
<script src="js/txcommon.js"></script>
<script nonce="<?php echo session_id();?>">
$("#access_start, #access_end").datepicker( {
	format: 'dd-mm-yyyy',
	todayHighlight:'TRUE',
	//startDate: new Date(),
	 
} ).on('show.bs.modal', function(event) {
	
    // prevent datepicker from firing bootstrap modal "show.bs.modal"
    event.stopPropagation();
	
}).on('hide',function(event) {
	let a = moment($(this).val()+" 00:00:00",'DD-MM-YYYY HH:mm:ss');
	let b = moment({h:0, m:0, s:0, ms:0});
	if (a < b) {
		$(this).val(moment().format("DD-MM-YYYY"));
	}
});
$('#access_start').on("changeDate",function() {
	let a = moment($('#access_start').val()+" 00:00:00",'DD-MM-YYYY HH:mm:ss'); <?php // dont mix up format with datepicker & moment, just don't ?>
	let b = moment({h:0, m:0, s:0, ms:0});
	if (a < b) {
		$('#access_start').val(moment().format("DD-MM-YYYY"));
		alert("Past date cannot be set as Access Date.");
	} else {
		$('#access_start').datepicker("hide");
	}
});
$('#access_end').on("changeDate",function() {
	let a = moment($('#access_end').val()+" 00:00:00",'DD-MM-YYYY HH:mm:ss');
	let b = moment({h:0, m:0, s:0, ms:0});
	if (a < b) {
		$('#access_end').val(moment().format("DD-MM-YYYY"));
		alert("Past date cannot be set as End Date.");
	} else {
		$('#access_end').datepicker("hide");
	}
});

var table = $('#account').DataTable({
	deferRender: false,
	stateSave: true,
	ajax: 'user_account_lib.php?mode=listUserAccount',
	columnDefs: [{ "orderable": false, "targets": 4 }],
});


// assmi
$("#allbox").change(function () {
	table.$('input[type="checkbox"]').prop('checked', $(this).prop("checked"));
	$('#all').prop('checked', $(this).prop("checked"));
});

$("#account").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#allbox').prop('checked', true);
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#account').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});

$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#account").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#allbox').prop('checked', false);
		$('#all').prop('checked', false);
	}
});

// assmi

$('#access_list').load('user_role_lib.php?mode=getAccessRightsList');<?php // FIXME should this be done only when modal is loaded? ?>

$("#user_type").change(function () {
	$('#access_list').load('user_role_lib.php?mode=getAccessRightsList&user_type='+$("#user_type").val() );
});

$("#all").change(function () {
	var cells = table.cells().nodes();
    $(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
});

$('#myUser').on('show.bs.modal', function(e)
{
	$("#myUser_header").show();
	var modal = $(this), id = $(e.relatedTarget).data('id');
	if(typeof id === "undefined") {
		
		modal.find('#header').html('<?php echo $x->create_new; ?>');
		modal.find('#mode').val('addUserAccount');
		modal.find('#username').prop('disabled', false);
		$("input:checkbox").prop('checked', false);
		modal.find("#pwd_chgonfirst").prop("checked", 1);
		modal.find('#access_start, #access_end').val('<?php echo date("d-m-Y")?>');
	} else {

		modal.find('#header').html('<?php echo $x->edit_acc; ?>');
		modal.find('#mode').val('saveUserAccount');
		$.ajax({
			cache: false,
			url: 'user_account_lib.php',
			data:'mode=editUserAccount&id='+id,
			type:'POST',
			dataType:'json',
			success: function(val)
			{
				modal.find('#id').val(id);
				modal.find('#username').val(val.userid);
				modal.find('#username').prop('disabled', true);
				modal.find('#new_password').keyup();
				modal.find('#mobile_numb').val(val.mobile_numb);
				modal.find('#department').val(val.department);
				modal.find('#user_role').val(val.user_role);
				modal.find('#access_start').val(val.access_start);
				modal.find('#access_end').val(val.access_end);
				modal.find('#email').val(val.email);
				modal.find('#user_type').val(val.user_type);
				modal.find('#pwd_expire').val(val.pwd_expire);
				modal.find('#pwd_threshold').val(val.pwd_threshold);
				modal.find('#session_timeout').val(val.timeout);
				modal.find("#pwd_chgonfirst").prop("checked", val.chg_onlogon);
				
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
					$("input:checkbox").prop('checked', false);
				}
			}
		})
	}
});

$('#username').on('change keyup', function(e){
	$('#username').removeClass("is-invalid");
});

$('#new_password').on('change keyup', function(e){
	$('#new_password').removeClass("is-invalid");
});

$('#confirmpwd').on('change keyup', function(e){
	$('#confirmpwd').removeClass("is-invalid");
});

$('#mobile_numb').on('change keyup', function(e){
	$('#mobile_numb').removeClass("is-invalid");
});

$('#email').on('change keyup', function(e){
	$('#email').removeClass("is-invalid");
});

$('#session_timeout').on('change keyup', function(e){
	$('#session_timeout').removeClass("is-invalid");
});

$('#pwd_threshold').on('change keyup', function(e){
	$('#pwd_threshold').removeClass("is-invalid");
});

$('#pwd_expire').on('change keyup', function(e){
	$('#pwd_expire').removeClass("is-invalid");
});

$('#myUser').on('submit', function(e)
{	
	if(!validateSize($("#username").val(),"UID")){
		$('#username').addClass("is-invalid");	
	}
	else if(!txvalidator($("#username").val(),"TX_STRING")){
		$('#username').addClass("is-invalid");		
	}
	else if($("#mode").val() == 'saveUserAccount' && !checkChangePwd($("#new_password").val(), $("#confirmpwd").val())){
		$("#new_password").addClass("is-invalid");
	}
	else if($("#mode").val() == 'addUserAccount' && !checkNewPwd($("#new_password").val(), $("#confirmpwd").val())){
		$("#new_password").addClass("is-invalid");		
	}	 
	else if(!txvalidator($("#mobile_numb").val(),"TX_SGMOBILEPHONE")){		
		$("#mobile_numb").addClass("is-invalid");			
	}
	else if(!txvalidator($("#email").val(),"TX_EMAILADDR")){
		$("#email").addClass("is-invalid");			
	}
	else if(!txvalidator($("#session_timeout").val(),"TX_INTEGER")){
		$("#session_timeout").addClass("is-invalid");	
	}
	else if(!txvalidator($("#pwd_threshold").val(),"TX_INTEGER")){
		$("#pwd_threshold").addClass("is-invalid");	
	}	
	else if(!txvalidator($("#pwd_expire").val(),"TX_INTEGER")){
		$("#pwd_expire").addClass("is-invalid");	
	}	
	else if(pwdpatterncheck($("#new_password").val()) >= 2 || $("#new_password").val() == ""){	
		//alert("OK");
		$.ajax({
				cache: false,
				url: 'user_account_lib.php',
				data: $("#user_form").serialize(),
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
					}else{
						table.ajax.reload();
						$('#myUser').modal('hide');
					}					
				}
			});				
	}	
	e.preventDefault();
});
$('#delete').on('click', function(e)
{
    if(confirm('<?php echo $x->alert_7; ?>')) {
		$('input[type=checkbox]').each(function() {
			if (this.checked && this.value!='on') {
				$.post('user_account_lib.php?mode=deleteUserAccount', { id: this.value }, function(data) {
					table.ajax.reload();
				});
			}
		});
		$('#allbox').prop('checked',false);
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e)
{
    if(confirm('<?php echo $x->alert_8; ?>')) {
		$.post('user_account_lib.php?mode=emptyUserAccount');
		table.ajax.reload();
	}
});
$('#user_role').change(function() {
	$("input:checkbox[name='access[]']").prop('checked', false);
	showhideLevel("1", false);
	showhideLevel("2", false);
	if (this.value) {
		$.getJSON('user_role_lib.php?mode=retrieveAccessRights&user_role='+this.value,function(val)
		{
			if (val.access_string.indexOf(",") >= 0) {
				var group_arr = val.access_string.split(',');
				for (var i=0; i < group_arr.length; i++) {
					$('input:checkbox[value='+group_arr[i]+']').prop("checked", true);
					if(group_arr[i] == 1) {
						showhideLevel("1", true);
					}
					if(group_arr[i] == 2) {
						showhideLevel("2", true);
					}
				}
			} else {
				$('input:checkbox[value='+val.group+']').prop("checked", true);
			}
		});
	}
});
$('#myUser').on('hidden.bs.modal', function () {
	$(this).find('form').trigger('reset');
	showhideLevel("1", false);
	showhideLevel("2", false);
	$('#userid').prop('disabled', false);
	$("#strlabl").text("");
	clearAlertMsg();
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
function pwdpatterncheck(pwdstr) {
	let result = 0;
	let patterns = new Array();
	
	patterns[0] = new RegExp ("[a-z]+");
	patterns[1] = new RegExp ("[A-Z]+");
	patterns[2] = new RegExp ("[0-9]+");
	patterns[3] = new RegExp ("[!-/:-@\[-`{-~]+");
	let re0 = new RegExp ("^[^\ ]{12,}$");

	if (re0.test(pwdstr) == true && pwdstr != "<?php echo $_SESSION['userid']; ?>")
		for (let i = 0 ; i < patterns.length ; i++) 
			result += patterns[i].test(pwdstr) ? 1 : 0;

	return result;
}
$(document).ready(function() {
	$("#confirmpwd").on("keyup",function() {
		if($("#new_password").val() != $(this).val()) {
			$("#cfmpwdresult").html('<i style="color:red" class="fa fa-remove"></i>');
		}else{
			$("#cfmpwdresult").html('<i style="color:green" class="fa fa-check"></i>');
		}
	});
	$("#new_password").on("keyup",function() {
		if ($(this).val() != "") {
			if (pwdpatterncheck($(this).val())<2) {
				$("#pwdresult").html('<i style="color:red" class="fa fa-remove"></i>');
				$("#new_password").get(0).setCustomValidity('Please follow the guideline on the right.');

			} else {
				$("#pwdresult").html('<i style="color:green" class="fa fa-check"></i>');
				$("#new_password").get(0).setCustomValidity('');
			}
			$("#confirmpwd").keyup();
		} else {
			$("#pwdresult").html('<i style="color:grey" class="fa fa-asterisk"></i>');
			$("#cfmpwdresult").html('<i style="color:grey" class="fa fa-asterisk"></i>');
		}
	});
});
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
loadlist('#department','user_department_lib.php?mode=getDepartmentList','department_id','department');
loadlist('#user_role','user_role_lib.php?mode=getUserRoleList','role_id','user_role');
loadlist('#l_dept','user_department_lib.php?mode=getDepartmentList','department_id','department');
loadlist('#l_user_role','user_role_lib.php?mode=getUserRoleList','role_id','user_role');
function toCallDate(filename,ndate) {
	return  filename+moment(ndate).format("YYYY-MM-DD hhmmss");
}
var date = $.now();
new $.fn.dataTable.Buttons( table, {
	buttons: [
		{
			extend:'csv',
			text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export.' CSV'; ?>',
			exportOptions: {columns: ':visible'},
			filename: function() {
				return toCallDate('<?php echo $_SESSION['userid']; ?>_UserAccount_',new Date());
			},
			init: function(api,node,config){
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			}},
		{
			extend:'excel',
			text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export.' Excel'; ?>',
			exportOptions: {columns: ':visible'},
			filename: function() {
				return toCallDate('<?php echo $_SESSION['userid']; ?>_UserAccount_',new Date());
			},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-success btn-sm");
			}}
	]
} );
var filename = '<?php echo $_SESSION['userid']; ?>_UserAccount_'+toCallDate(new Date());
table.buttons().container().appendTo('#export');

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});

function clearAlertMsg(){
	
	$("#msgstatusbar").removeClass("alert-warning");
	$("#msgstatusbar").removeClass("alert-success");
	$("#msgstatusbar").hide();

	$('#username').removeClass("is-invalid");

	$('#new_password').removeClass("is-invalid");

	$('#confirmpwd').removeClass("is-invalid");

	$('#mobile_numb').removeClass("is-invalid");

	$('#email').removeClass("is-invalid");

	$('#session_timeout').removeClass("is-invalid");

	$('#pwd_threshold').removeClass("is-invalid");

	$('#pwd_expire').removeClass("is-invalid");
}

function checkNewPwd(newPwd, confirmPwd)
{	
	var status = 1;
	if(!validateSize(newPwd,"PWD")){		
		status = 0;
	}
	else if(pwdpatterncheck(newPwd) < 2){		
		status = 0;	
	}
	else if(!txvalidator(newPwd,"TX_STRING","ALL")){		
		status = 0;	
	}
	else if(confirmPwd != newPwd){
		status = 0;	
	}	
	return status;	
}


function checkChangePwd(newPwd, confirmPwd)
{
	var status = 1;
	if ( newPwd.length == 0 && confirmPwd.length == 0 ){
		status = 1;		
	}
	else if(!validateSize(newPwd,"PWD")){		
		status = 0;
	}
	else if(pwdpatterncheck(newPwd) < 2){		
		status = 0;	
	}
	else if(!txvalidator(newPwd,"TX_STRING","ALL")){		
		status = 0;	
	}
	else if(confirmPwd != newPwd){
		status = 0;	
	}
	
	return status;	
}

</script>
