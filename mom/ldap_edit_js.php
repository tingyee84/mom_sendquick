$(document).ready(function(){
	
	loadlist('.department','user_department_lib.php?mode=getDepartmentList','department_id','department');
	loadlist('#user_role','user_role_lib.php?mode=getUserRoleList','role_id','user_role');
	
	setTimeout(function(){
		
		$.post('ldap_lib.php',{mode:'findLDAPServerInfo',ldapid:target_lid},function(val) {
			//console.log(val.l_mail);
			//alert(val.l_mail);
			$('#l_name').val(val.l_name);
			$('#l_desc').val(val.l_desc);
			$('#l_ip1').val(val.l_ip1);
			$('#l_port1').val(val.l_port1);
			$('#l_ip2').val(val.l_ip2);
			$('#l_port2').val(val.l_port2);
			$('#l_domainname').val(val.l_domain);
			$('#l_loginname').val(val.l_loginname);
			$('#l_loginpwd').val(val.l_loginpwd);
			$('#l_mobile').val(val.l_mobile);
			$('#l_mail').val(val.l_mail);
			$('#l_basedn').val(val.l_basedn);
			$('input[name=download_group][value='+val.download_group+']').prop('checked',true);
			$('#l_filter').val(val.l_filter);
			$('#l_type').val(val.l_type);
			$('#l_loginmode').val(val.l_loginmode);
			$('#l_scope').val(val.l_scope);
			$('#sync_frequency').val(val.sync_frequency);
			$('#sync_time').val(val.sync_hour);
			$('input[name=l_sync_ul][value='+val.sync_ul+']').prop('checked',true);
			if(val.sync_ul == 't'){
			
				$('#div_ul').removeClass('hidden');
				
				$('#user_dept  option[value="'+val.user_dept+'"]').prop("selected", true);
				$('#user_role  option[value="'+val.user_role+'"]').prop("selected", true);
				
				document.getElementById('user_dept').value = val.user_dept;
				document.getElementById('user_role').value = val.user_role;
				
				//$('#user_dept option[value='+val.user_dept+']').attr('selected','selected');
				//$('#user_role option[value='+val.user_role+']').attr('selected','selected');
				
				//$('#user_dept').val(val.user_dept);
				//$('#user_role').val(val.user_role);
				
			} else{
				$('#div_ul').addClass('hidden');
			}
			//alert(val.sync_ul);
			//alert( val.user_dept );
			//alert( val.user_role );
			$('input[name=l_sync_gab][value='+val.sync_gab+']').prop('checked',true);
			if(val.sync_gab == 't'){
				
				$('#div_gab').removeClass('hidden');
				
				$('#gab_dept').val(val.gab_dept);
				document.getElementById('gab_dept').value = val.gab_dept;
				
			} else{
				$('#div_gab').addClass('hidden');
			}
			
			//alert(val.download_group);
			if(val.download_group == 't'){
				//alert('1');
				$('#l_filter_div,#l_filter_hr,#sync_with_tr').hide();
				$('#l_filter').attr('disabled', true);
				$('#gab_dept').attr('disabled', true);
			}else{
				//alert('2');
				$('#l_filter_div,#l_filter_hr,#sync_with_tr').show();
			}
		},"json")
		.fail(function() {
			alert('Failed To Retrieve Ldap Server');
		});
	
	}, 1000);
	
	$('#edit_ldap').on('submit', function(e)
	{
		if( !$("#download_group").is(":checked") && !$("#l_sync_ul").is(":checked") && !$("#l_sync_gab").is(":checked") ) {
			
			alert('You must select Sync with User List or Global Address Book.');
			return false;
			
		}else{
			
			//alert('no alert');
			//return false;
			
			$.post('ldap_lib.php',$("#edit_ldap").serialize(),function(data) {
				if(data!='') {
					$('#output').html(data);
					$('#status').removeClass('hidden');
					window.scrollTo(0, 0);
				} else {
					alert(strsuccess);
					$(this).find('form').trigger('reset');
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
		window.scrollTo(0,0);
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
	
});
