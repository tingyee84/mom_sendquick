<?php
	$page_mode = '8';
	$php_name = "change_password.php";
	$page_title = "Change Password";
	
	include('header.php');
	include('checkAccess.php');

	$details_arr = getUserDetails($_SESSION['userid']);
	$x = GetLanguage("change_password",$lang);
?>
		<div class="page-header">
			<ol class="breadcrumb">
				<li class="active"><?php echo $x->title;?></li>
			</ol>
		</div>
		<div class="page-content">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div id="status" class="alert alert-info alert-sm alert-dismissable text-center">
							<span id="output"></span>
							<button class="btn-close" type="button"></button>
						</div>
						<form id="change_details_form" name="change_details_form">
						<div class="row">
							<div class="col-md-7">
								<div class="row">
									<div class="col-md-6">
										<label class="control-label"><?php echo $x->username; ?></label>
									</div>
									<div class="col-md-6">
										<p><input class="form-control input-sm" type="text" name="change_userid" value="<?php echo $_SESSION['userid']; ?>" readonly></p>
									</div>
								</div>
								<?php if(strcmp(strtolower($_SESSION['userid']),"useradmin") != 0){ ?>
								<div class="row">
									<div class="col-md-6">
										<label class="control-label"><?php echo $x->mobile;?> <span class="text-danger">*</span></label>
									</div>
									<div class="col-md-6">
										<p><input class="form-control input-sm" type="text" name="change_mobile_numb" value="<?php echo $details_arr[0]['mobile_numb']; ?>" pattern="\+?\d+" required></p>
									</div>
								</div>
								<?php } ?>
								<div class="row">
									<div class="col-md-6">
										<label class="control-label"><?php echo $x->old_pass;?> <span class="text-danger">*</span></label>
									</div>
									<div class="col-md-6">
										<p><input class="form-control input-sm" type="password" name="old_password" required></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label for="change_password" class="control-label"><?php echo $x->new_pass;?> <span class="text-danger">*</span></label>
									</div>
									<div class="col-md-6">
										<div class="input-group mb-3">
												<span id="pwdresult" class="input-group-text"><i class="fa fa-remove"></i></span>
											<input class="form-control input-sm" type="password" name="change_password" id="change_password" required aria-describedby="pwdresult">
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label for="confirm_password" class="control-label"><?php echo $x->cfm_pass;?> <span class="text-danger">*</span></label>
									</div>
									<div class="col-md-6">
										<div class="input-group mb-3">
												<span id="cfmpwdresult" class="input-group-text"><i class="fa fa-remove"></i></span>
											<input class="form-control input-sm" type="password" name="confirm_password" id="confirm_password" required aria-describedby="cfmpwdresult">
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-5">
								<div class="row">
									<div class="col">
									Password need to be made of at least <b>12</b>&nbsp;characters and contains characters from at least two of the following 4 categories<br>
									<ul>
										<li>At least one character from Upper Case [A-Z]</li>
										<li>At least one character from Lower Case [a-z]</li>
										<li>At least one character from Number [0-9]</li>
										<li>At least one character from Special Characters [!,@,#,$,% etc]</li>
									</ul>
								</div>
								
								</div>
									<div class="row">
									<div class="col">
									Password cannot contain your username!
								</div>
								</div>
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="col text-center">
							<input type="hidden" name="mode" value="update"/>
							<input type="hidden" name="id" value="<?php echo $details_arr[0]['id'];?>"/>
							<input class="btn btn-primary btn-sm" type="submit" value="<?php echo $x->save; ?>">&nbsp;
							<input class="btn btn-info btn-sm" type="reset" value="<?php echo $xml_common->reset;?>"> &nbsp;
							<input class="btn btn-secondary btn-sm" type="button" id="cancel" value="<?php echo $x->cancel; ?>">
								</div>
						</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<script src="change_password_js.php<?php echo !empty($_GET["required"]) ? "?required=".$_GET["required"] : ""; ?>"></script>
</body>
</html>
