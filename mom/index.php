<?php

require_once('lib/commonFunc.php');
require_once("/home/msg/conf/server_mode.php");
$xml =  GetLanguage("index","EN");
$xml_talariax = $xml->talariax;
$xml_header = $xml->header;
$xml_user = $xml->user;
$xml_password = $xml->password;
$xml_submit = $xml->submit;
$xml_reset = $xml->reset;
$xml_admin_login = $xml->admin_login;
$xml_copyright = $xml->copyright;
$xml_all_rights = $xml->all_rights;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $xml_header; ?></title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <script src="js/jquery.min.js"></script>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
		<script src="js/jquery-1.12.4.min.js">
		<script src="js/html5shiv.min.js"></script>
		<script src="js/respond.min.js"></script>
    <![endif]-->
	<script src="js/bootstrap.min.js"></script>
    <script src="js/sb-admin-2.js"></script>
</head>
<body class="index-body container-fluid">
	<?php if( $system_server_mode == 3 ){ ?>
	<div class="page-content row col-lg-6 offset-lg-3">
		<div class="panel panel-default text-center">
			<div class="panel-heading"><h5 class="panel-title">Notice</h5></div>
			<div class="panel-body">
				<p>Sorry, system in secondary mode. Please use primary server.</p>
				<p><a href="/appliance/index.php">System Configuration</a></p>
			</div>
		</div>
	</div>
	<?php } else if(getWebappMode()){?>
	<div class="row">
		<div class="col-md-6 offset-md-3">
			<div class="text-center">
				<p><?php
					$xml = simplexml_load_file('/home/msg/conf/image_header.xml');
					$__image = $xml->image;
					if($__image!='' && file_exists("images/$__image")){
						echo "<img src=\"images/$__image\" style=\"max-height:100px\">";
					}else{
						echo "<img src=\"images/TalariaX-Logo.png\" alt=\"TalariaX\">";
					}
					?>
				</p>
				<br>
				<p><img src="images/sendQuickMessaging.png" alt="SendQuick Messaging Portal"></p>
			</div>
			<div id="error" class="alert alert-danger alert-dismissable text-center" style="display:none;">
				<h4 id="err_msg" style="display: inline-block; margin-top:8px" class="text-danger"></h4>
				<button type="button" class="btn-close" aria-label="Close" style="float:right;margin-top:8px"></button>
			</div>
			<div class="login-panel"><br>
<?php
$result = getSQLresult($dbconn,"SELECT * FROM blockingip WHERE ipaddress = '".$_SERVER["REMOTE_ADDR"]."'");
if (!is_string($result) && count($result[0]) > 0) {
	?>
	<div class="row">
		<div class="col-lg-8 offset-lg-2 text-center"><h4>Access Denied</h4>Your Login has been denied. Please contact Administrator for assistance.<br><br>
		</div>
	</div>
<?php
} else { 
?>
				
				<p class="panel-title text-center">LOGIN TO YOUR ACCOUNT</p>
				<div class="panel-body">
					<form id="login" name="login" method="post" autocomplete="off">
						<div class="row">
							<div class="col-lg-8 offset-lg-2">
								<div class="form-group">
									<div class="input-group input-group-sm mb-3">
										<input type="text" class="form-control" placeholder="Username" aria-label="Username" id="username" name="username" maxlength="20" aria-describedby="check0" autofocus required tabindex="1">
										<div class="input-group-append">
											<span class="input-group-text" id="check0"><i style='color:red' class="fa fa-remove"></i></span>
										</div>
									</div>
									<div class="input-group input-group-sm">
										<input type="password" class="form-control" placeholder="Password" aria-label="Password" id="password" name="password" maxlength="20" aria-describedby="check1" autofocus required tabindex="2">
										<div class="input-group-append">
											<span class="input-group-text" id="check1"><i style='color:red' class="fa fa-remove"></i></span>
										</div>
									</div>
									<a style="padding-top:-10px;" href="forgot_password.php" tabindex="-1">Forgot Password?</a>
									<div class="input-group input-group-sm">
										<img src='./captcha.php' id='imgcaptcha'><button type="button" class='btn btn-light'><i style='color:red;cursor:pointer' class='fa fa-refresh' id='refreshcaptcha'></i></button>
										<br>Type the code you see above!
									</div>

									<div class="input-group input-group-sm">
										<input class="form-control" name="userCaptchaInput" id="userCaptchaInput" type="text" required tabindex="3">
										<div class="input-group-append">
											<span class="input-group-text" id="check2"><i style='color:red' class="fa fa-remove"></i></span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-lg-8 offset-lg-2 text-center">
								<input id="mode" name="mode" type="hidden" value="login"/>
								<button class="btn btn-secondary" type="submit" id="btn_login">Login</button><br><br>
							</div>
						</div>
					</form>

					<div class="row" id='otp_row' style="display:none">
						<div class="col-md-12" style=" text-align:center;">
							<p>We have sent you a SMS with OTP code to your mobile number for verification.</p>
							<p><b><span id='censorednumber'></span></b>
							<p>One Time Password:</p>
							<p><span id="sessionidtext" style="font-size:16px"></span><span style="font-size:16px">-</span>
<?php for ($i = 0 ; $i < 6 ; $i++) { ?>
							<input name="otp[]" type="text" maxlength="1" size="1" style="text-align: center;font-size: large;height: 35px;width: 35px;">
<?php } ?>
							</p>
						</div>
					</div>
					<form id="otp_form" name="otp_form" method="post" autocomplete="off" style="display:none">
						<div class="row">
							<div class="col-md-8 offset-md-2" style='text-align:center'>
								<input type="hidden" name="sessionid" id="sessionid" value=""/>
								<input type="hidden" name="otp"  id="otp" value=""/>
								<input type="hidden" name="username"  id="otpusername" value=""/>
								<input type="hidden" name="mode"  id="otpmode" value="2falogin"/>
								<button class="btn btn-primary" type="submit" id="otp_submit">Submit</button><br>
								<p>Didn't receive the OTP?</p>
								<button class="btn btn-danger" type="button" id="otp_resend">Wait 120s</button>
							</div>
						</div>
					</form>
				</div>
<?php } ?>
			</div>
		</div>
	</div>
	<?php }else{?>
	<div class="page-content row col-lg-6 offset-lg-3">
		<div class="panel panel-default text-center">
			<div class="panel-heading"><h5 class="panel-title">Notice</h5></div>
			<div class="panel-body">
				<p>Webapp Module is not enabled by default. Please contact Sendquick for details. Thanks</p>
			</div>
		</div>
	</div>
	<?php } ?>
	<div class="row">
		<div class="login-footer">
			<p>Copyright &#169; 2002-<?php echo strftime("%Y", time()); ?>, TalariaX Pte Ltd, Singapore. All Rights Reserved. <?php $datestr = strftime("%a, %d %b %Y %H:%M", time());echo "$datestr";?></p>
		</div>
	</div>

	<script>
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
			$("#check0").html("<i style='color:green' class='fa fa-check'></i>");
		} else {
			$("#check0").html("<i style='color:red' class='fa fa-remove'></i>");
		}
		if ($("#password").val().length >= 11 && result >= 2) {
			$("#check1").html("<i style='color:green' class='fa fa-check'></i>");
		} else {
			$("#check1").html("<i style='color:red' class='fa fa-remove'></i>");
		}
		if ($("#userCaptchaInput").val().length >= 6) {
			$("#check2").html("<i style='color:green' class='fa fa-check'></i>");
		} else {
			$("#check2").html("<i style='color:red' class='fa fa-remove'></i>");
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

<?php if (isset($_GET["autologout"])) { ?>
		$("#err_msg").html("You have been logout due to session timeout.");
		$("#error").slideDown(100);
<?php } else if (isset($_GET["redirect"])) { ?>
		$("#err_msg").html("Please login first before accessing the pages");
		$("#error").slideDown(100);
<?php } else if (isset($_GET["logoutsuccess"])) { ?>
		$("#err_msg").html("Successfully logout");
		$("#error").slideDown(100);
<?php } ?>
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
	
	</script>
</body>
</html>
