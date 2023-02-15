<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.html5.min.js"></script>
<script language="javascript" src="js/txvalidator.js"></script>
<script language="javascript" src="js/txcommon.js"></script>
<script nonce="<?php echo session_id();?>">

var changePwdVar = 0;

var table = $('#api_accts').DataTable({
	deferRender: true,
	stateSave: true,
	ajax: 'api_list_lib.php?mode=listApiAccts',
	columnDefs: [{ "orderable": false, "targets": [3,9] }]
});
var date = $.now();
new $.fn.dataTable.Buttons( table, {
	buttons: [
		{extend:'csv', text: '<?php echo $xml_common->export.' CSV'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php echo $_SESSION['userid']; ?>_AddressBook_'+date},
		{extend:'excel', text: '<?php echo $xml_common->export.' Excel'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php echo $_SESSION['userid']; ?>_AddressBook_'+date}
	]
} );
table.buttons().container().appendTo('#export');
$("#all").change(function () {
	var cells = table.cells().nodes();
	$(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
});

$('#myApiAccts').on('show.bs.modal', function(e)
{
	$("#myApiAccts_header").show();
	var modal = $(this), id = $(e.relatedTarget).data('id');
	if(typeof id === "undefined") {
		// modal.find('#header').html('<?php echo $x->create_new; ?>');
		modal.find('#header').html('<?php echo "Create New Application" ?>');
		modal.find('#mode').val('addApiAcct');
		modal.find('#api_serviceid').prop('disabled', false);
		modal.find('#api_clientid').prop('disabled', false);
		$('#access_token_div').addClass('hidden');
		$('#change_pwd_div').addClass('hidden');
		$('#reenter_password_div').removeClass('hidden');
		$('#password_div').removeClass('hidden');
		$('#api_reenter_password').prop('required',true);
		$('#api_password').prop('required',true);
		$("#inlineCheckbox1").prop("checked",true);
		$("#inlineCheckbox3").prop("checked",true);

		$('#inlineCheckbox1').attr("disabled", false);
		$('#inlineCheckbox2').attr("disabled", false);
		$('#inlineCheckbox3').attr("disabled", true);
		$('#inlineCheckbox4').attr("disabled", true);

		$( "#inlineCheckbox1" ).unbind('click').click(function() {
			$("#inlineCheckbox3").prop("checked",true);
			$("#inlineCheckbox4").prop("checked",false);
			$('#inlineCheckbox3').attr("disabled", true);
			$('#inlineCheckbox4').attr("disabled", true);
			return true;
		});
		$( "#inlineCheckbox2" ).unbind('click').click(function() {
			$('#inlineCheckbox3').attr("disabled", false);
			$('#inlineCheckbox4').attr("disabled", false);
			return true;
		});
		$( "#inlineCheckbox3" ).unbind('click').click(function() {
			if($( "#inlineCheckbox2:checked" ).length  > 0){
				return true;
			}else{
				return false;
			}
		});
		$( "#inlineCheckbox4" ).unbind('click').click(function() {
			if($( "#inlineCheckbox2:checked" ).length > 0){
				return true;
			}else{
				return false;
			}
		});
		// $('#keyword_div').removeClass('hidden');
		// $('#keyword_url_div').removeClass('hidden');
		// $('#api_keyword').prop('required',true);
		// $('#api_keyword_url').prop('required',true);
	} else {
		$.ajax({
			cache: false,
			url: 'api_list_lib.php',
			data:'mode=editApiAcct&id='+id,
			type:'POST',
			dataType:'json',
			success: function(val)
			{
				// modal.find('#header').html('<?php echo $x->edit_contact; ?>');
				modal.find('#header').html('<?php echo "Edit Application"; ?>');
				modal.find('#id').val(id);
				modal.find('#mode').val('saveApiAcct');
				modal.find('#api_name').val(val.name);
				modal.find('#api_agencyid').val(val.agencyid);
				modal.find('#api_serviceid').val(val.serviceid);
				modal.find('#api_serviceid').prop('disabled', true);
				modal.find('#api_statusurl').val(val.status_url);
				modal.find('#api_quota').val(val.quota);
				modal.find('#api_clientid').val(val.clientid);
				modal.find('#api_clientid').prop('disabled', true);
				modal.find('#api_appntype').val(val.appn_type);

				// if(val.appn_type == "3"){
				// 	modal.find('#api_keyword').val(val.keyword);
				// 	modal.find('#api_keyword_url').val(val.url);
				// 	$('#keyword_div').removeClass('hidden');
				// 	$('#keyword_url_div').removeClass('hidden');
				// }

				modal.find('#api_dept').val(val.dept);
				modal.find('#api_access_token').val(val.access_token);
				modal.find('#api_access_token').prop('disabled', true);
				$('#access_token_div').removeClass('hidden');
				$('#change_pwd_div').removeClass('hidden');
				$('#reenter_password_div').addClass('hidden');
				$('#password_div').addClass('hidden');
				$('#api_reenter_password').prop('required',false);
				$('#api_password').prop('required',false);
				if (val.sftp_status == 0) {
					$("#inlineCheckbox1").prop("checked",true);
					$('#inlineCheckbox1').attr("disabled", false);
					$('#inlineCheckbox2').attr("disabled", false);
					$("#inlineCheckbox3").prop("checked",true);
					$('#inlineCheckbox3').attr("disabled", true);
					$('#inlineCheckbox4').attr("disabled", true);

					$( "#inlineCheckbox1" ).unbind('click').click(function() {
						$("#inlineCheckbox3").prop("checked",true);
						$("#inlineCheckbox4").prop("checked",false);
						$('#inlineCheckbox3').attr("disabled", true);
						$('#inlineCheckbox4').attr("disabled", true);
						return true;
					});
					$( "#inlineCheckbox2" ).unbind('click').click(function() {
						$('#inlineCheckbox3').attr("disabled", false);
						$('#inlineCheckbox4').attr("disabled", false);
						return true;
					});

					$( "#inlineCheckbox3" ).unbind('click').click(function() {
						if($( "#inlineCheckbox2:checked" ).length  > 0){
							return true;
						}else{
							return false;
						}
					});
					$( "#inlineCheckbox4" ).unbind('click').click(function() {
						if($( "#inlineCheckbox2:checked" ).length > 0){
							return true;
						}else{
							return false;
						}
					});
				} else if (val.sftp_status == 1) {
					$("#inlineCheckbox2").prop("checked",true);
					$("#inlineCheckbox4").prop("checked",true);
					$('#inlineCheckbox1').attr("disabled", true);
					$('#inlineCheckbox3').attr("disabled", false);
					$('#inlineCheckbox4').attr("disabled", false);

					$( "#inlineCheckbox1" ).unbind('click').click(function() {
						return false;
					});
					$( "#inlineCheckbox2" ).unbind('click').click(function() {
						return false;
					});
				} else if (val.sftp_status == 2) {
					$("#inlineCheckbox2").prop("checked",true);
					$("#inlineCheckbox3").prop("checked",true);
					$('#inlineCheckbox1').attr("disabled", true);
					$('#inlineCheckbox3').attr("disabled", false);
					$('#inlineCheckbox4').attr("disabled", false);

					$( "#inlineCheckbox1" ).unbind('click').click(function() {
						return false;
					});
					$( "#inlineCheckbox2" ).unbind('click').click(function() {
						return false;
					});
				}

				// modal.find('#modem').val(val.modem_label);
				//Edited by Wafie @ 11/11/2016
				// if(val.group_string.length > 0) {
				// 	if (val.group_string.indexOf(",") >= 0) {
				// 		var group_arr = val.group_string.split(',');
				// 		for (var i=0; i < group_arr.length; i++) {
				// 			modal.find('input:checkbox[value='+group_arr[i]+']').prop("checked", true);
				// 		}
				// 	} else {
				// 		modal.find('input:checkbox[value='+val.group_string+']').prop("checked", true);
				// 	}
				// } else {
				// 	$("input:checkbox").prop('checked', false);
				// }
				//Wafie End
			}
		})
	}
});
$('#myApiAccts').on('submit', function(e)
{	
	var pwdHidden = $("#password_div").is(":hidden");
	if(pwdHidden){
		console.log("hidden yes");
		$('#api_reenter_password').val("");
		$('#api_password').val("");
	}
	if(!txvalidator($("#api_name").val(),"TX_STRING","SPACE")){
		$('#api_name').addClass("is-invalid");
	}else if(!validateSize($("#api_name").val(),"NAME")){
		$('#api_name').addClass("is-invalid");
	}else if(!txvalidator($("#api_agencyid").val(),"TX_STRING")){
		$('#api_agencyid').addClass("is-invalid");
	}else if(!validateSize($("#api_agencyid").val(),"AID")){
		$('#api_agencyid').addClass("is-invalid");
	}else if(!txvalidator($("#api_serviceid").val(),"TX_STRING")){
		$('#api_serviceid').addClass("is-invalid");
	}else if(!validateSize($("#api_serviceid").val(),"SID")){
		$('#api_serviceid').addClass("is-invalid");
	}else if(!pwdHidden && !txvalidator($("#api_password").val(),"TX_STRING","ALL")){
		$('#api_password').addClass("is-invalid");
	}else if(!pwdHidden && !validateSize($("#api_password").val(),"PWD")){
		$('#api_password').addClass("is-invalid");
	}else if(!txvalidator($("#api_clientid").val(),"TX_STRING")){
		$('#api_clientid').addClass("is-invalid");
	}else if(!validateSize($("#api_clientid").val(),"ID")){
		$('#api_clientid').addClass("is-invalid");
	}else if(!txvalidator($("#api_statusurl").val(),"TX_URL")){
		$('#api_statusurl').addClass("is-invalid");
	}else if(!txvalidator($("#api_quota").val(),"TX_INTEGER")){
		$('#api_quota').addClass("is-invalid");
	}
	else{
		$.ajax({
			cache: false,
			// url: 'api_list_lib.php?lang='+lang,
			url: 'api_list_lib.php',
			data: $("#api_acct_form").serialize(),
			type: 'POST',
			dataType:'json',
			success: function(res){
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
					$("#alert-msg").html("Successfully Updated.");
					$("#alert-top").removeClass("alert-warning").addClass("alert-success").show();
					table.ajax.reload();
					$('#myApiAccts').modal('hide');
				}
				// else if (data == "SFTPFOLDERCREATEFAILED") {
				// 	alert("Detail is updated but failed to create folder.");
				// 	$("#alert-top").removeClass("alert-success").addClass("alert-warning").show();
				// 	table.ajax.reload();
				// 	$('#myApiAccts').modal('hide');
				// } else if (data == "SFTPFOLDERCREATESUCCESS") {
				// 	$("#alert-msg").html("SFTP has been created.");
				// 	$("#alert-top").removeClass("alert-warning").addClass("alert-success").show();
				// 	table.ajax.reload();
				// 	$('#myApiAccts').modal('hide');
				// } else if (data == "NEWAPICREATESUCCESS") {
				// 	$("#alert-msg").html("New API has been successfully created.");
				// 	$("#alert-top").removeClass("alert-warning").addClass("alert-success").show();
				// 	table.ajax.reload();
				// 	$('#myApiAccts').modal('hide');
				// } else if (data == "") {
				// 	$("#alert-msg").html("Successfully Updated.");
				// 	$("#alert-top").removeClass("alert-warning").addClass("alert-success").show();
				// 	table.ajax.reload();
				// 	$('#myApiAccts').modal('hide');
				// } else {
				// 	alert(data);
				// }
			}
		});
	}  
	e.preventDefault();
});
$("#alert-close").on('click',function() {
	$(this).parent().hide();
});

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});

$('#api_name').on('change keyup', function(e){
	$('#api_name').removeClass("is-invalid");
});

$('#api_agencyid').on('change keyup', function(e){
	$('#api_agencyid').removeClass("is-invalid");
});

$('#api_serviceid').on('change keyup', function(e){
	$('#api_serviceid').removeClass("is-invalid");
});

$('#api_password').on('change keyup', function(e){
	$('#api_password').removeClass("is-invalid");
});

$('#api_clientid').on('change keyup', function(e){
	$('#api_clientid').removeClass("is-invalid");
});

$('#api_statusurl').on('change keyup', function(e){
	$('#api_statusurl').removeClass("is-invalid");
});

$('#api_quota').on('change keyup', function(e){
	$('#api_quota').removeClass("is-invalid");
});

$('#myApiAccts').on('hidden.bs.modal', function () {
	$("#msgstatusbar").removeClass("alert-success");
	$("#msgstatusbar").removeClass("alert-warning");
	$("#msgstatusbar").hide();
	$('#api_name').removeClass("is-invalid");
	$('#api_agencyid').removeClass("is-invalid");
	$('#api_serviceid').removeClass("is-invalid");
	$('#api_password').removeClass("is-invalid");
	$('#api_clientid').removeClass("is-invalid");
	$('#api_statusurl').removeClass("is-invalid");
	$('#api_quota').removeClass("is-invalid");
	$(this).find('form').trigger('reset');
});

$('#change_pwd').on('click', function(e){
	if(changePwdVar == 0){
		$('#reenter_password_div').removeClass('hidden');
		$('#password_div').removeClass('hidden');

		$('#api_reenter_password').prop('required',true);
		$('#api_password').prop('required',true);

		changePwdVar = 1;
	}else{
		$('#reenter_password_div').addClass('hidden');
		$('#password_div').addClass('hidden');

		$('#api_reenter_password').prop('required',false);
		$('#api_password').prop('required',false);

		changePwdVar = 0;
	}
});

// $('#api_appntype').on('change', function(e){
// 	var appnTypeVal = this.value;
// 	if(appnTypeVal == "1"){
// 		$('#keyword_div').addClass('hidden');
// 		$('#keyword_url_div').addClass('hidden');
// 		$('#api_keyword').prop('required',false);
// 		$('#api_keyword_url').prop('required',false);
// 	}else if(appnTypeVal == "3"){
// 		$('#keyword_div').removeClass('hidden');
// 		$('#keyword_url_div').removeClass('hidden');
// 		$('#api_keyword').prop('required',true);
// 		$('#api_keyword_url').prop('required',true);
// 	}
// });

$('#delete').on('click', function(e)
{
	// if(confirm('<?php echo $x->alert_5; ?>')) {
	if(confirm('<?php echo "Delete selected?" ?>')) {
		$('input[type=checkbox]').each(function() {
			if (this.checked) {
				var thisVal = this.value;
				console.log("this value: " + thisVal);
				$.post('api_list_lib.php?mode=deleteApiAcct', { id: this.value }, function(data) {
					if (data.indexOf("DELETEAPISUCCESS") === 0) {
						temp = data.split(":");
						$("#alert-msg").html("Successfully deleted" + temp[1]);
						$("#alert-top").removeClass("alert-warning").addClass("alert-success").show();
					}
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});

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

// for dept and appntype
function loadlist(selobj,url,val,name)
{
	$('#selobj option:gt(0)').remove();
	$.getJSON(url,function(data)
	{
		$.each(data, function(index, value) {
			if(value[name].length!=0) {
				$(selobj).append('<option value="' + value[val] + '">' + value[name] + '</option>');
			}
		});
	});
}

// load depts
loadlist('#api_dept','api_list_lib.php?mode=listDepts','department_id','department');
// load appn types
// loadlist('#api_appntype','api_list_lib.php?mode=listAppnTypes','','');


</script>