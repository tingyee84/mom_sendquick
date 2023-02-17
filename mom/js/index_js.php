<?php 
header("Content-type:text/javascript");

?>
function verification() {
	let result = 0;
	let re0 = new RegExp ("[a-z]+");
	let re1 = new RegExp ("[A-Z]+");
	let re2 = new RegExp ("[0-9]+");
	let re3 = new RegExp ("[!-/:-@\[-`{-~]+");

	result += re0.test($("#password").val()) ? 1 : 0;
	result += re1.test($("#password").val()) ? 1 : 0;
	result += re2.test($("#password").val()) ? 1 : 0;
	result += re3.test($("#password").val()) ? 1 : 0;

	if ($("#username").val().length >= 8) {
		$("#check0").html("<i class='fa fa-check text-success'></i>");
	} else {
		$("#check0").html("<i class='fa fa-remove text-danger'></i>");
	}
	if ($("#password").val().length >= 11 && result >= 2) {
		$("#check1").html("<i class='fa fa-check text-success'></i>");
	} else {
		$("#check1").html("<i class='fa fa-remove text-danger'></i>");
	}
	if ($("#userCaptchaInput").val().length >= 6) {
		$("#check2").html("<i class='fa fa-check text-success'></i>");
	} else {
		$("#check2").html("<i class='fa fa-remove text-danger'></i>");
	}
	if ($("#username").val().length < 8 || $("#password").val().length < 11 || $("#userCaptchaInput").val().length < 6 || result < 2) {
		$("#btn_login").attr("disabled",true);
		$("#btn_login").removeClass("btn-primary").addClass("btn-secondary");
	} else {
		$("#btn_login").attr("disabled",false);
		$("#btn_login").removeClass("btn-secondary").addClass("btn-primary");
	}
}

$(document).ready(function() {
	$("#btn_login").attr("disabled",1);
	$(".btn-close").on("click",function(evt) {
		$(".alert-dismissable").slideUp();
	});
	$("#username, #password, #userCaptchaInput").on("keyup",function() {
		verification() ;
	});

	$("#otp_form").css("display","none");
	$("#otp_row").css("display","none");
	$("input[name='otp[]']").each(function(){
		$(this).on('focusin',function(e) {
			$(this).select();
		});
		$(this).on('input',function(e) {
			if ($(this).next("input")[0]) {
				$(this).next("input").focus();
			} else {
				$("#otp_submit").focus();
			}
		});
	});
	$("#otp_resend").click(function () {
		$("#otpmode").val("resendcode");
		$.ajax({
			cache:!1,url:"login.php",
			data: $("#otp_form").serialize(),
			type:"POST",
			dataType:"json",
			success:function(r) {
				if (r.status == "205") {
					$("input[name='otp[]']").val("");
					$("input[name='otp[]']:first").focus();
					$("#sessionidtext").text(r.sessionid);
					$("#sessionid").val(r.sessionid);
					$("#censorednumber").text(r.mobileno);
					$("#otpmode").val("2falogin");
				} else {
					$("#err_msg").html(r.status);
					$("#error").stop().slideDown();
				}
			},
			error:function(){
				alert("System Error");
			}
		});
			waitThenActive();
	});

	$("#otp_submit").on("click",function (r) {
		$("#error").slideUp(100);
		let temp = "";
		$("input[name='otp[]']").each(function() {
			temp += $(this).val();
		});
		$("input[name='otp']").val(temp);
		$.ajax({
			cache:!1,url:"login.php",
			data:$("#otp_form").serialize(),
			type:"POST",
			dataType:"json",
			success:function(r) {
				if (r.status == "1") {
					window.location.href=r.redirect;
				} else {
					$("#err_msg").html(r.status);
					$("#error").stop().slideDown();
				}
			},
			error:function(){
				alert("System Error");
			}
		});
		r.preventDefault();
	});

	$("#refreshcaptcha").on('click',function() {
		var d = new Date();
		$("#imgcaptcha").attr("src","captcha.php?"+d.getTime());
	});
	$("#login").on("submit",function(r){
		$("#error").slideUp(100);
		$.ajax({
			cache:!1,url:"login.php",
			data:$("#login").serialize(),
			type:"POST",
			dataType:"json",
			success:function(r){
				if (r.status == "205") {
					$("#login").slideUp();
					$("#otp_row").slideDown();
					$("#otp_form").slideDown(400,function() {
						$("input[name='otp[]']:first").focus();
					});
					$("#otpusername").val($("#username").val());
					$("#sessionidtext").text(r.sessionid);
					$("#sessionid").val(r.sessionid);
					$("#censorednumber").text(r.mobileno);
					waitThenActive();
				} else if (r.status == 1 || r.status == 2 || r.status == 3) {
					window.location.href=r.redirect;
				} else {
					$("#err_msg").html(r.status);
					if (r.refreshcaptcha == "1") {
						var d = new Date();
						$("#imgcaptcha").attr("src","captcha.php?"+d.getTime());
					}
					$("#error").stop().slideDown();
				}
			},
			error:function(){
				alert("System Error");
			}
		});
		r.preventDefault();
	});
	

<?php
if (isset($_GET["autologout"])) { 
	?>
	$("#err_msg").html("You have been logout due to session timeout.");
	$("#error").slideDown(100);
<?php
} else if (isset($_GET["redirect"])) { ?>
	$("#err_msg").html("Please login first before accessing the pages");
	$("#error").slideDown(100);
<?php
} else if (isset($_GET["logoutsuccess"])) { ?>
	$("#err_msg").html("Successfully logout");
	$("#error").slideDown(100);
<?php
}
?>

});

var activetime = 0;
	function waitThenActive () {
		activetime = 120;
		$("#otp_resend").prop("disabled",true);
		var timer = setInterval(function () {
			if (activetime != 0) {
				$("#otp_resend").html("Wait " + (activetime--) + "s");
			} else {
				$("#otp_resend").html("Resend Code");
				$("#otp_resend").prop("disabled",false);
				clearInterval(timer);
			}
		}, 1000);
	}