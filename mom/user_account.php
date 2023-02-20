<?php
	$page_mode = '200';
	$chk_mode = '27';
	$page_title = 'User Account Management';
	include('header.php');
	include('checkAccess.php');

// Default value
$rez = pg_query($dbconn,"SELECT * FROM setting where variable in ('pwdexpiry','sessiontime','accthreshold')");
$defaultval = array();
while ($row = pg_fetch_array($rez)) {
	$defaultval[$row["variable"]] = $row["value"];
}
?>
		<link href="css/assmi.css" rel="stylesheet">
		<div class="page-header padding-top-10">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li></li>
					<li class="breadcrumb-item"><?php echo $xml->user_mgnt?></li>
    				<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->user_acc_mgnt?></li>
				</ol>
			</nav>
		</div>
		<?php $x = GetLanguage("user_account",$lang); ?>
		<?php
		$UserType = getUserType();
	
		if( $UserType == "admin" ){
				// <!--<option value="admin">'.$x->user_type_admin.'</option>-->
			$UserType_select = '
											
											<option value="bu">'.$x->user_type_bu.'</option>
											<option value="user">'.$x->user_type_user.'</option>
											';
		}elseif( $UserType == "bu" ){
			$UserType_select = '
											<option value="user">'.$x->user_type_user.'</option>
											';
		}else{
			$UserType_select = '';
		}
		?>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table table-striped table-bordered table-condensed width-100" id="account">
								<thead>
									<tr>
										<th><?php echo $x->username;?></th>
										<th><?php echo $x->dept; ?></th>
										<th><?php echo $x->user_type; ?></th>
										<!--<th><?php echo $x->datasrc; ?></th>-->
										<th><?php echo $x->access_status; ?></th>
										<th><input type="checkbox" name="all" id="all"></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="5">
											<span class="pull-left padding-right-10">
												<button id="create" type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myUser"><?php echo $xml_common->add_new_record;?></button>
											</span>
											<div id="export"></div>
											<span class="pull-right">
											<!-- assmi -->
												<input id="allbox" type="checkbox" value="Select All">
											<!-- assmi -->
												<button id="truncate" type="submit" class="btn btn-warning btn-sm"><?php echo $x->empty_str; ?></button>
												<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete; ?></button>
											</span>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
						<!-- Modal -->
						<div class="modal fade" id="myUser" tabindex="-1" role="dialog">
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<div class="modal-header" id="myUser_header">
										<h5 class="modal-title" id="header"></h5>
										<button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
											<i class="fa fa-times"></i>
										</button>
									</div>									
									<form id="user_form" name="user_form" method="post" autocomplete="off">
									<div class="modal-body">
										<div class="row">
											<div class="col-md-10">
												<div id="msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2 display-none-assmi" role="alert">
													<span id="msgstatustext">A</span>
													<button type="button" class="btn-close" id="msgstatusbar_close" aria-label="Close"></button>																								
												</div>
											</div>											
										</div>	
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="username" class="control-label"><?php echo $x->user_acc_name; ?></label>
											</div>
											<div class="col-md-5">
												<input type="text" class="form-control input-sm" name="username" id="username" maxlength="15" required>
												<div id="invalid_username" class="invalid-feedback">
													<?php echo $x->invalid_username; ?>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="new_password" class="control-label"><?php echo $x->user_pwd; ?></label>
											</div>
											<div class="col-md-5">
												<div class="input-group mb-3">
														<span class="input-group-text" id="pwdresult"><i class="fa fa-remove color-red"></i></span>
													<input id="new_password" type="password" name="new_password" class="form-control input-sm" aria-label="New Password" aria-describedby="pwdresult" maxlength="128">
													<div id="invalid_new_password" class="invalid-feedback">
														<?php echo $x->invalid_new_password; ?>
													</div>
												</div>
											</div>
											<div class="col-md-3"><a href="#" title="<?php echo $x->min_8; ?>"><i class="fa fa-2x fa-question-circle"></i></a>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="confirmpwd" class="control-label"><?php echo $x->user_pwd_cfm; ?></label>
											</div>
											<div class="col-md-5">
												<div class="input-group mb-3">
														<span class="input-group-text" id="cfmpwdresult"><i class="fa fa-remove color-red"></i></span>
														<input id="confirmpwd" type="password" name="confirmpwd" class="form-control input-sm" aria-label="Confirm New Password" aria-describedby="cfmpwdresult" maxlength="128">
														<div id="invalid_confirmpwd" class="invalid-feedback">
															<?php echo $x->invalid_confirmpwd; ?>
														</div>
												</div>
											</div>
											<div class="col-md-3">
												<a href="#" data-toggle="tooltip" data-html="true" title="<?php echo $x->must_match; ?>"><i class="fa fa-2x fa-question-circle"></i></a>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="mobile_numb" class="control-label"><?php echo $x->user_mobile;?></label>
											</div>
											<div class="col-md-5">
												<input type="text" class="form-control input-sm" name="mobile_numb" id="mobile_numb" maxlength="11" pattern="(\+65)?[8,9]\d{7}">
												<div id="invalid_mobile_numb" class="invalid-feedback">
															<?php echo $x->invalid_mobile_numb; ?>
												</div>
											</div>
											<div class="col-md-3">
												<a href="#" data-toggle="tooltip" data-html="true" title="<?php echo $x->numbers_only; ?>"><i class="fa fa-2x fa-question-circle"></i></a>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="email" class="control-label"><?php echo $x->user_email?></label>
											</div>
											<div class="col-md-5">
												<input type="email" class="form-control input-sm" name="email" id="email" pattern="[A-Za-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
												<div id="invalid_email" class="invalid-feedback">
															<?php echo $x->invalid_email; ?>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="access_start" class="control-label"><?php echo $x->access_start?></label>
											</div>
											<div class="col-md-5">
												<input type="text" class="form-control input-sm" name="access_start" id="access_start" required onkeydown="return false">
												<div id="invalid_access_start" class="invalid-feedback">
															<?php echo $x->invalid_access_start; ?>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="access_end" class="control-label"><?php echo $x->access_end?></label>
											</div>
											<div class="col-md-5">
												<input type="text" class="form-control input-sm" name="access_end" id="access_end" required onkeydown="return false">
												<div id="invalid_access_end" class="invalid-feedback">
															<?php echo $x->invalid_access_end; ?>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="session_timeout" class="control-label"><?php echo $x->sessiontimeout; ?></label>
											</div>
											<div class="col-md-2">
												<input type="number" pattern="[0-9]{1,3}" class="form-control input-sm" name="session_timeout" id="session_timeout" min=2 max=180 value="<?php echo $defaultval["sessiontime"]; ?>" required />
												<div id="invalid_session_timeout" class="invalid-feedback">
															<?php echo $x->invalid_session_timeout; ?>
												</div>
											</div>
											<div class="col-md-3">
											<?php echo $x->minutes; ?>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="pwd_threshold" class="control-label"><?php echo $x->lockoutthreshold; ?></label>
											</div>
											<div class="col-md-2">
												<input type="number" pattern="[0-9]{1,2}" class="form-control input-sm" name="pwd_threshold" id="pwd_threshold" min=0 max=10 value="<?php echo $defaultval["accthreshold"]; ?>" required />
												<div id="invalid_pwd_threshold" class="invalid-feedback">
															<?php echo $x->invalid_pwd_threshold; ?>
												</div>
											</div>
											<div class="col-md-3"><?php echo $x->invalidattempt; ?></div>
											<div class="col-md-3"><a href="#" data-toggle="tooltip" data-html="true" title="To disable lockout, set '0'"><i class="fa fa-2x fa-question-circle" ></i></a></div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="pwd_expire" class="control-label"><?php echo $x->pwdexpiry; ?></label>
											</div>
											<div class="col-md-2">
												<input type="number" pattern="[0-9]{1,3}" class="form-control input-sm" name="pwd_expire" id="pwd_expire" min=0 max=365 value="<?php echo $defaultval["pwdexpiry"]; ?>" required />
												<div id="invalid_pwd_expire" class="invalid-feedback">
															<?php echo $x->invalid_pwd_expire; ?>
												</div>
											</div>
											<div class="col-md-3"><?php echo $x->days; ?>
											</div>
											<div class="col-md-3"><a href="#" data-toggle="tooltip" data-html="true" title="To disable password expiry, set to '0'"><i class="fa fa-2x fa-question-circle"></i></a></div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="pwd_chgonfirst" class="control-label"><?php echo $x->pwdchglogon; ?></label>
											</div>
											<div class="col-md-2">
												<input type="checkbox" value="y" name="pwd_chgonfirst" id="pwd_chgonfirst" />
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="access_end" class="control-label"><?php echo $x->user_type?></label>
											</div>
											<div class="col-md-5">
												<p>
													<select name="user_type" id="user_type" required>
														<option value=""><?php echo $x->no_user_type;?></option>
														<?php
														echo $UserType_select;
														?>
													</select>
												</p>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="department" class="control-label"><?php echo $x->user_dept?></label>
											</div>
											<div class="col-md-5">
												<p>
													<select name="department" id="department" required>
														<option value=""><?php echo $x->no_dept;?></option>
													</select>
												</p>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="user_role" class="control-label"><?php echo $x->user_role;?></label>
											</div>
											<div class="col-md-5">
												<p>
													<select name="user_role" id="user_role">
														<option value=""><?php echo $x->user_role_not_specify; ?></option>
													</select>
												</p>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6 offset-md-4">
												<table class="table table-condensed table-sm" id="access_list">
												</table>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<input type="hidden" name="mode" id="mode"/>
										<input type="hidden" name="id" id="id"/>
										<button type="submit" class="btn btn-primary"><?php echo $xml_common->save; ?></button>
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $xml_common->cancel; ?></button>
									</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<?php include("user_account_js.php");?>
</body>
</html>
