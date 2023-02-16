<?php header("Content-type:text/javascript");
include("./lib/commonFunc.php");
?>
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