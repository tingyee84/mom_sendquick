loadlist('.department','user_department_lib.php?mode=getDepartmentList','department_id','department');
loadlist('#user_role','user_role_lib.php?mode=getUserRoleList','role_id','user_role');
$('#add_ldap').on('submit', function(e)
{
	if( !$("#download_group").is(":checked") && !$("#l_sync_ul").is(":checked") && !$("#l_sync_gab").is(":checked") ) {
		
		alert('You must select Sync with User List or Global Address Book.');
		return false;
	
	}else{
		
		$.post('ldap_lib.php',$("#add_ldap").serialize(),function(data) {
			if(data!='') {
				$('#output').html(data);
				$('#status').removeClass('hidden');
				window.scrollTo(0, 0);
			} else {
				alert(strsuccess);
				$(location).attr('href','ldap_mgnt.php');
			}
		});
		e.preventDefault();
		
	}
	
});
$('#checkLDAPConn').on('click', function(e){
	$('#output').html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
	$('#status').removeClass('hidden');
	if ((!l_ip1) || (!l_port1) || (!l_loginname) || (!l_loginpwd)){
		$('#output').html(ldapchk_blk);
	} else{
		var obj = $.param({'mode':'checkLDAPServAccConn',
						'l_ip1': $("#l_ip1").val(), 
						'l_port1': $("#l_port1").val(), 
						'l_ip2': $("#l_ip2").val(), 
						'l_port2': $("#l_port2").val(), 
						'l_domainname': $("#l_domainname").val(), 
						'l_loginname': $("#l_loginname").val(), 
						'l_loginpwd': $("#l_loginpwd").val()});
		$.post('ldap_lib.php',obj,function(data) {
			$('#output').html(data.status);
			if(data.flag == 1) {
				$("#status").removeClass("alert-danger").addClass("alert-info");
			} else {
				$("#status").removeClass("alert-info").addClass("alert-danger");
			}
		},"json")
		.fail(function() {
			$('#output').html(ldapchk_failed);
		});
	}
	window.scrollTo(0, 0);
});
$('#l_sync_ul').change(function(){
    if($(this).is(":checked")) {
		$('#div_ul').removeClass('hidden');
    } else {
		$('#div_ul').addClass('hidden');
	}
});
$('#l_sync_gab').change(function(){
    if($(this).is(":checked")) {
		$('#div_gab').removeClass('hidden');
    } else {
		$('#div_gab').addClass('hidden');
	}
});
$("#cancel").click(function() {
	history.back(1);
});
$('.close').click(function() {
	$('#status').addClass('hidden');
});
function loadlist(selobj,url,val,name)
{
	$('#selobj option:gt(0)').remove();
	$.getJSON(url,function(data)
	{
		$.each(data, function(index, value) {
			$(selobj).append("<option value="+value[val]+">"+value[name]+"</option>");
		});
	});
}
$('#download_group').on('click', function(e){
	if($(this).is(":checked")) {
		
		$('#l_filter_div,#l_filter_hr,#sync_with_tr').hide();
		//$('#l_filter').attr('disabled', true);
		//$('#gab_dept').attr('disabled', true);
		
    } else {
		
		$('#l_filter_div,#l_filter_hr,#sync_with_tr').show();
		//$('#l_filter').attr('disabled', false);
		//$('#gab_dept').attr('disabled', false);
	}
	
});
