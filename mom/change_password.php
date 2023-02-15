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
										<label class="control-label"><?php echo $x->mobile;?> <span style="color:red">*</span></label>
									</div>
									<div class="col-md-6">
										<p><input class="form-control input-sm" type="text" name="change_mobile_numb" value="<?php echo $details_arr[0]['mobile_numb']; ?>" pattern="\+?\d+" required></p>
									</div>
								</div>
								<?php } ?>
								<div class="row">
									<div class="col-md-6">
										<label class="control-label"><?php echo $x->old_pass;?> <span style="color:red">*</span></label>
									</div>
									<div class="col-md-6">
										<p><input class="form-control input-sm" type="password" name="old_password" required></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label for="change_password" class="control-label"><?php echo $x->new_pass;?> <span style="color:red">*</span></label>
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
										<label for="confirm_password" class="control-label"><?php echo $x->cfm_pass;?> <span style="color:red">*</span></label>
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
	<script nonce="<?php echo session_id();?>">
	$("#status").hide();
	var password = $('#change_password')[0],confirm_password = $('#confirm_password')[0];
	function validatePassword(){
		if(password.value != confirm_password.value) {
			$("#cfmpwdresult").html('<i style="color:red" class="fa fa-remove"></i>');
			confirm_password.setCustomValidity('<?php echo $x->alert_3; ?>');
		} else {
			$("#cfmpwdresult").html('<i style="color:green" class="fa fa-check"></i>');
			confirm_password.setCustomValidity('');
		}
	}
	password.onchange = validatePassword;
	confirm_password.onkeyup = validatePassword;

	password.onkeyup = function() {
		let result = 0;
		let re0 = new RegExp ("[a-z]+");
		let re1 = new RegExp ("[A-Z]+");
		let re2 = new RegExp ("[0-9]+");
		let re3 = new RegExp ("[!-/:-@\[-`{-~]+");
		let re4 = new RegExp ("^[^\ ]{12,}$");
		let re5 = new RegExp ("^<?php echo $_SESSION['userid']; ?>",'i');

		result += re0.test($(this).val()) ? 1 : 0;
		result += re1.test($(this).val()) ? 1 : 0;
		result += re2.test($(this).val()) ? 1 : 0;
		result += re3.test($(this).val()) ? 1 : 0;

		if (re4.test($(this).val()) == false || result < 2 || re5.test($(this).val()) == true) {
			$("#pwdresult").html('<i style="color:red" class="fa fa-remove"></i>');
			password.setCustomValidity('Please follow the guideline on the right.');

		} else {
			$("#pwdresult").html('<i style="color:green" class="fa fa-check"></i>');
			password.setCustomValidity('');
		}
	}
	$('#change_details_form').on('submit', function(e)
	{
		$('#status').stop().slideUp();

		let result = 0;
		let re0 = new RegExp ("[a-z]+");
		let re1 = new RegExp ("[A-Z]+");
		let re2 = new RegExp ("[0-9]+");
		let re3 = new RegExp ("[!-/:-@\[-`{-~]+");
		let re4 = new RegExp ("^[^\ ]{12,}$");
		let re5 = new RegExp ("^<?php echo $_SESSION['userid']; ?>",'i');

		result += re0.test($("#change_password").val()) ? 1 : 0;
		result += re1.test($("#change_password").val()) ? 1 : 0;
		result += re2.test($("#change_password").val()) ? 1 : 0;
		result += re3.test($("#change_password").val()) ? 1 : 0;

		if (re4.test($("#change_password").val()) == false || result < 2 || re5.test($("#change_password").val()) == true) {
			$('#output').html("Password must be followed as guideline on the right.");
			$("#change_password").focus();
			$('#status').stop().slideDown();
		} else {
			$.ajax({
				url: 'login.php',
				data: $('#change_details_form').serialize(),
				type: 'POST',
				success: function(data){
					$('#output').html(data);
					$('#status').stop().slideDown();
				},
				error: function(){
					alert('Failed To Change Personal Password & Mobile Number');
				}
			});
		}
		e.preventDefault();
	});
	$('.close').on("click",function() {
		$('#status').stop().slideUp();
	});
	$('#cancel').on("click",function() {
		history.back(1);
	});
	$("button.btn-close").on("click",function(event) {
    	$(this).parent().hide();
	});
	<?php if (isset($_GET['required'])) {
		echo '$("#output").html("<i class=\"fa fa-exclamation-circle\"></i>Password need to be changed before proceed");$("#status").slideDown();';
	} ?>
	</script>
</body>
</html>
