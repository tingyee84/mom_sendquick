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
	<link href="css/tychang.css" rel="stylesheet">
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
						echo "<img src=\"images/$__image\" class=\"definedimgheight\">";
					}else{
						echo "<img src=\"images/TalariaX-Logo.png\" alt=\"TalariaX\">";
					}
					?>
				</p>
				<br>
				<p><img src="images/sendQuickMessaging.png" alt="SendQuick Messaging Portal"></p>
			</div>
			<div id="error" class="alert alert-danger alert-dismissable text-center dnone">
				<h4 class="text-danger d-block mt-2"><span id="err_msg"></span><button type="button" id="btnalertclose" role="button" class="btn float-end p-0" aria-label="Close"><i class="fa fa-times"></i></button></h4>
				
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
				<div class="panel-body px-2">
					<form id="login" name="login" method="post" autocomplete="off">
						<div class="row">
							<div class="col-lg-8 offset-lg-2">
								<div class="form-group">
									<div class="input-group input-group-sm mb-3">
										<input type="text" class="form-control" placeholder="Username" aria-label="Username" id="username" name="username" maxlength="20" aria-describedby="check0" autofocus required tabindex="1">
										<span class="input-group-text" id="check0"><i class="fa fa-remove text-danger"></i></span>
									</div>
									<div class="input-group input-group-sm">
										<input type="password" class="form-control" placeholder="Password" aria-label="Password" id="password" name="password" maxlength="20" aria-describedby="check1" autofocus required tabindex="2">
										<span class="input-group-text" id="check1"><i class="fa fa-remove text-danger"></i></span>
									</div>
									<a href="forgot_password.php" class="pt-4" tabindex="-1">Forgot Password?</a>
									<div class="input-group input-group-sm">
										<img src='./captcha.php' id='imgcaptcha'><button type="button" class='btn btn-light'><i class='fa fa-refresh  text-danger' role="button" id='refreshcaptcha'></i></button>
										<br>Type the code you see above!
									</div>

									<div class="input-group input-group-sm">
										<input class="form-control" name="userCaptchaInput" id="userCaptchaInput" type="text" required tabindex="3">
										<span class="input-group-text" id="check2"><i class="fa fa-remove text-danger"></i></span>

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

					<div class="row dnone" id='otp_row'>
						<div class="col-md-12 text-center">
							<p>We have sent you a SMS with OTP code to your mobile number for verification.</p>
							<p><b><span id='censorednumber'></span></b>
							<p>One Time Password:</p>
							<p><span id="sessionidtext" class="fs-4"></span><span class="fs-4">-</span>
<?php for ($i = 0 ; $i < 6 ; $i++) { ?>
							<input name="otp[]" type="text" maxlength="1" size="1" class="otpnumberfield">
<?php } ?>
							</p>
						</div>
					</div>
					<form id="otp_form" name="otp_form" method="post" autocomplete="off">
						<div class="row">
							<div class="col-md-8 offset-md-2 text-center">
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

	<!-- <script type="application/x-httpd-php" src="js/index_js.php"></script> -->
	<script src="js/index_js.php<?php
	echo isset($_GET["autologout"]) ? "?autologout" : "";
	echo isset($_GET["redirect"]) ? "?redirect" : "";
	echo isset($_GET["logoutsuccess"]) ? "?logoutsuccess" : "";
	?>" defer></script>
</body>
</html>