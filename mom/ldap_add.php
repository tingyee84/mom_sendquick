<?php
	$page_mode = '45';
	$page_title = 'Add New LDAP Server';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("ldap_add",$lang);
	
	//$x->login_mode = "Login Mode and Contact Name";
	//$x->basedn_desc = "Base DN of the location of user or group list.";
	//$x->groups_desc = "It will download all groups and create group into global address book and add members into it.";
	//$x->group_download	= "Download AD Groups and Members";
?>
		<div class="page-header">
			<ol class="breadcrumb">
				<li><?php echo $xml->system_config;?></li>
				<li><a href="ldap_mgnt.php"><?php echo $xml->ldap_mgnt;?></a></li>
				<li class="active"><?php echo $x->title;?></li>
			</ol>
		</div>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div id="status" class="alert alert-info alert-dismissable text-center hidden">
							<button class="close">&times;</button>
							<span id="output">&nbsp;</span>
						</div>
						<form name="add_ldap" id="add_ldap">
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->name;?></b> <span style="color:red">*</span></label>
								</div>
								<div class="col-lg-4 entera">
									<input class="form-control input-sm" type="text" name="l_name" maxlength="100" required/>
								</div>
								<div class="col-lg-5 help-block">
									<?php echo $x->name_desc;?>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->desc;?></b></label>
								</div>
								<div class="col-lg-4 entera">
									<textarea class="form-control input-sm" name="l_desc"></textarea>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->server1;?></b> <span style="color:red">*</span></label>
								</div>
								<div class="col-lg-4">
									<input class="custom-control" type="text" name="l_ip1" id="l_ip1" size="22" placeholder="127.0.0.1" required>&nbsp;
									<span><b><?php echo $x->port; ?></b></span>&nbsp;
									<input class="custom-control" type="text" name="l_port1" id="l_port1" size="4" maxlength="5" pattern="\d+" placeholder="389" required>
								</div>
								<div class="col-lg-5 help-block">
									<?php echo $x->server1_desc; ?>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->server2;?></b></label>
								</div>
								<div class="col-lg-4">
									<input class="custom-control" type="text" name="l_ip2" id="l_ip2" size="22">&nbsp;
									<span><b><?php echo $x->port; ?></b></span>&nbsp;
									<input class="custom-control" type="text" name="l_port2" id="l_port2" size="4" maxlength="5" pattern="\d+"/>
								</div>
								<div class="col-lg-5 help-block">
									<?php echo $x->server2_desc; ?>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->type;?></b></label>
								</div>
								<div class="col-lg-4">
									<select name="l_type">
										<option value="ad">Active Directory</option>
										<option value="ldap">LDAP</option>
									</select>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->domain_name?></b> <span style="color:red">*</span></label>
								</div>
								<div class="col-lg-4 entera">
									<input class="form-control input-sm" type="text" name="l_domainname" id="l_domainname" required>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->service_bind;?></b> <span style="color:red">*</span></label>
								</div>
								<div class="col-lg-4 entera flex">
									<input class="form-control input-sm" type="text" name="l_loginname"  id="l_loginname" required>
								</div>
								<div class="col-lg-5">
									<input class="btn btn-info btn-sm" type="button" id="checkLDAPConn" value="<?php echo $x->test_service;?>">
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->account_passwd;?></b> <span style="color:red">*</span></label>
								</div>
								<div class="col-lg-4 entera">
									<input class="form-control input-sm" type="password" name="l_loginpwd" id="l_loginpwd" required>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->login_mode;?></b></label>
								</div>
								<div class="col-lg-4">
									<select name="l_loginmode">
										<option value="loginid"><?php echo $x->login_id?></option>
										<option value="email"><?php echo $x->email?></option>
										<option value="displayname"><?php echo $x->display_name?></option>
									</select>
								</div>
								<div class="col-lg-5">
									<a data-target="#loginmode" data-toggle="modal-popover"><i class="fa fa-question-circle"></i></a>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->mobile_attr;?></b></label>
								</div>
								<div class="col-lg-4 entera">
									<input class="form-control input-sm" type="text" name="l_mobile" placeholder="mobile">
								</div>
								<div class="col-lg-5 help-block">
									<?php echo $x->mobile_desc; ?>
									<a data-target="#mobileattr" data-toggle="modal-popover" data-placement="left"><i class="fa fa-question-circle"></i></a>	
								</div>
							</div>
							<hr>
							
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->mail_attr;?></b></label>
								</div>
								<div class="col-lg-4 entera">
									<input class="form-control input-sm" type="text" name="l_mail" placeholder="mail">
								</div>
								<div class="col-lg-5 help-block">
									<?php echo $x->mail_desc; ?>
									<a data-target="#mobileattr" data-toggle="modal-popover" data-placement="left"><i class="fa fa-question-circle"></i></a>	
								</div>
							</div>
							<hr>
							
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->basedn;?></b></label>
								</div>
								<div class="col-lg-4 entera">
									<input class="form-control input-sm" type="text" name="l_basedn" placeholder="dc=testserver,dc=com"/>
								</div>
								<div class="col-lg-5 help-block">
									<?php echo $x->basedn_desc;?>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->search_scope;?></b></label>
								</div>
								<div class="col-lg-4">
									<select name="l_scope">
										<option value="sub">Sub</option>
										<option value="base">Base</option>
										<option value="one">One</option>
									</select>
								</div>
								<div class="col-lg-5">
									<a data-target="#scope" data-toggle="modal-popover"><i class="fa fa-question-circle"></i></a>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->group_download; ?></b></label>
								</div>
								<div class="col-lg-4">
									<input type="checkbox" name="download_group" id="download_group" value="t">
								</div>
								<div class="col-lg-5 help-block">
									<?php echo $x->groups_desc;?>
								</div>
							</div>
							<hr>
							<div class="row" id = "l_filter_div">
								<div class="col-lg-3">
									<label class="control-label"><b><?php echo $x->filter;?></b></label>
								</div>
								<div class="col-lg-4 entera">
									<textarea class="form-control input-sm" name="l_filter" id="l_filter" placeholder="memberOf=CN=VPNusers,DC=testserver,DC=com"></textarea>
								</div>
								<div class="col-lg-5 help-block">
									<?php echo $x->filter_desc;?>
								</div>
							</div>
							<hr id = "l_filter_hr">
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><?php echo $x->sync;?></label>
								</div>
								<div class="col-lg-9">
									<table class="table-condensed">
									<tr>
										<td class="text-left"><?php echo $x->frequency; ?></td><td>&nbsp;</td>
										<td class="text-left">
											<select name="sync_frequency">
											<option value="0"><?php echo $x->never;?></option>
											<option value="1"><?php echo $x->daily;?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td class="text-left"><?php echo $x->time;?></td><td>&nbsp;</td>
										<td class="text-left">
											<select name="sync_time">
											<option value="">-Hour-</option>
											<?php
											$currhour = strftime("%H", time());
											for($i=0;$i<24;$i++){
												$display = ($i<10)? '0'.$i:$i;
												if ($i == $currhour) {
													echo "<option value=\"$i\" selected>$display</option>";
												} else {
													echo "<option value=\"$i\">$display</option>";
												}
											}
											?>
											</select>
										</td>
									</tr>
									<tr id = "sync_with_tr">
										<td class="text-left" valign="top"><?php echo $x->sync_with;?></td><td>&nbsp;</td>
										<td class="text-left">
											<input type="checkbox" name="l_sync_ul" id="l_sync_ul" value="t">&nbsp;<?php echo $x->user_list;?>
											<div id="div_ul" class="hidden">
												<table class="table-bordered table-condensed">
												<tr>
													<td class="text-left"><?php echo $x->dept_new;?></td>
													<td class="text-left">
														<select name="user_dept" class="department">
														<option value="0"><?php echo $x->no_department;?></option>
														</select>
													</td>
												</tr>
												<tr>
													<td class="text-left"><?php echo $x->role_new;?></td>
													<td class="text-left">
														<select name="user_role" id="user_role">
														<option value="0"><?php echo $x->user_role_not_specify;?></option>
														</select>
													</td>
												</tr>
												</table>
											</div>
											<hr>	
											<input type="checkbox" name="l_sync_gab" id="l_sync_gab" value="t">&nbsp;<?php echo $x->addr_book;?>
											<div id="div_gab" class="hidden">
												<table class="table-bordered table-condensed">
												<tr>
													<td><?php echo $x->dept_contact; ?></td>
													<td><select name="gab_dept" class="department">
														<option value="0"><?php echo $x->no_department;?></option>
														</select>
													</td>
												</tr>
												</table>
											</div>
										</td>
									</tr>
									</table>
								</div>
							</div>
							<hr>
							<div class="row text-center">
								<input type="hidden" name="mode" value="addLDAPServer">
								<button type="submit" class="btn btn-primary"><?php echo $xml_common->save;?></button>
								<button type="button" class="btn btn-default" id="cancel"><?php echo $xml_common->cancel;?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div id="loginmode" class="popover">
			<div class="popover-content"><small>
				<table class="table table-bordered table-condensed">
					<tr><th><?php echo $x->login_mode;?></th><th><?php echo $x->ad_attr;?></th><th><?php echo $x->ldap_attr;?></th></tr>
					<tr><td><?php echo $x->login_id;?></td><td>sAMAccountName</td><td>mail</td></tr>
					<tr><td><?php echo $x->email;?></td><td>uid</td><td>mail</td></tr>
					<tr><td><?php echo $x->display_name;?></td><td>displayName</td><td>displayName</td></tr>
				</table></small>
			</div>
		</div>
		<div id="mobileattr" class="popover">
			<div class="popover-content"><small>
				<table class="table table-bordered table-condensed">
					<tr><th><?php echo $x->name_in_outlook?></th><th><?php echo $x->ldap_attr?></th></tr>
					<tr><td>Business</td><td>telephoneNumber</td></tr>
					<tr><td>Assistant</td><td>telephoneAssistant</td></tr>
					<tr><td>Home</td><td>homePhone</td></tr>
					<tr><td>Pager</td><td>pager</td></tr>
					<tr><td>Mobile</td><td>mobile</td></tr>
					<tr><td>Email</td><td>mail</td></tr>
					<tr><td>Fax</td><td>facsimileTelephoneNumber</td></tr>
					<tr><td>IP Phone</td><td>ipPhone</td></tr>
				</table></small>
			</div>
		</div>
		<div id="scope" class="popover">
			<div class="popover-content"><small>
				<table class="table table-bordered table-condensed">
					<tr><th><?php echo $x->search_scope?></th><th><?php echo $x->desc?></th></tr>
					<tr><td>Sub</td><td><?php echo $x->search_scope_sub?></td></tr>
					<tr><td>Base</td><td><?php echo $x->search_scope_base?></td></tr>
					<tr><td>One</td><td><?php echo $x->search_scope_one?></td></tr>
				</table></small>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<script nonce="<?php echo session_id();?>">
	var strsuccess = '<?php echo $x->success_msg1." ".$x->success_msg2;?>';
	var ldapchk_blk = '<?php echo $x->ldapchk_blk;?>';
	var ldapchk_failed = '<?php echo $x->ldapchk_failed;?>';
	</script>
	<script src="ldap_add_js.php"></script>
	<script src="js/bootstrap-modal-popover.js"></script>
</body>
</html>
