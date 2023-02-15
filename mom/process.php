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
function blockip () {
	global $dbconn;
	if (!isset($_SESSION["loginattempt"])) {
		$_SESSION["loginattempt"] = 0;
	}
	if ($_SESSION["loginattempt"] < 10) {
		$_SESSION["loginattempt"] += 1;
	} else {
		$sqlcmd = "INSERT INTO blockingip (ipaddress) VALUES('".$_SERVER["REMOTE_ADDR"]."')";
		$_SESSION["userid"] = "LOGIN SYSTEM";
		insertAuditTrail($_SERVER["REMOTE_ADDR"]." has been blocked");
		unset($_SESSION["userid"]);
        $result = doSQLcmd($dbconn,$sqlcmd);	// function has logged if sql failed
        header("Location: index.php");
	}
}
blockip();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self';font-src 'self' https:;script-src 'self' 'unsafe-eval' 'unsafe-inline';style-src 'self' 'unsafe-inline';object-src 'none';img-src * data:">
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
    <script src="js/metisMenu.min.js"></script>
    <script src="js/sb-admin-2.js"></script>
</head>
<body class="index-body">
    <div class="container">
        <div class="row">
            <div class="col-md-12 login-header">
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
		</div>
		<div class="row">
			<div class="col-md-6 offset-md-3 login-panel">
                    <br>
				<div class="mx-auto" style="width:400px">Either Username, Password or Captcha is invalid. Please login again. - <?php echo $_SESSION["loginattempt"]; ?></div>
                <br>
                <center><button class="btn btn-primary" onclick="window.location='index.php'">Back to Login Page</button></center>
                <br>
			</div>
		</div>
    </div>
    
	<div class="row">
		<div class="login-footer">
			<p>Copyright &#169; 2002-<?php echo strftime("%Y", time()); ?>, TalariaX Pte Ltd, Singapore. All Rights Reserved. <?php $datestr = strftime("%a, %d %b %Y %H:%M", time());echo "$datestr";?></p>
		</div>
	</div>
</body>
</html>