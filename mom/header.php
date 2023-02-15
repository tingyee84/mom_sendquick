<?php

require_once('lib/commonFunc.php');
$xml = GetLanguage("menu",$lang);
$xml_button = GetLanguage("button",$lang);
$xml_common = GetLanguage("common",$lang);
#Language Setup
$access_arr = explode(",",trim($_SESSION['access_string']));
$disable_lang = (in_array('57',$access_arr) ? '' : 'disabled');
#Customized project header
$project_header_lib = "project/header_lib.php";
if(file_exists($project_header_lib)){
	$header_lib = $project_header_lib;
}
if (isset($_SESSION['needchgpwd'])&&$_SESSION['needchgpwd']=='yes' && strpos($_SERVER['PHP_SELF'],"change_password.php") === false && strpos($_SERVER['PHP_SELF'],"logout.php") === false) {
	header("location: change_password.php?required");                
}
header('X-Content-Type-Options: nosniff');

$UserType = getUserType();

if( $UserType == "admin" ){
	$display_UserType = "Admin";
}elseif( $UserType == "bu" ){
	$display_UserType = "BU";
}elseif( $UserType == "user" ){
	$display_UserType = "User";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- <meta http-equiv="Content-Security-Policy" content="default-src 'self';font-src 'self' https:;script-src 'self' 'unsafe-eval' 'unsafe-inline' 'nonce-<?php echo session_id();?>';style-src 'self' 'unsafe-inline';object-src 'none';img-src * data:;"> -->
	<title><?php echo $page_title;?></title>
	
	
	<!--  -->
	<script src="js/popper.min.js"></script>
	<script src="js/jquery.min.js"></script>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<!--  -->
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    
	<!--  -->
	<!-- <link href="css/dataTables.bootstrap.min.css" rel="stylesheet"> -->
	<link href="css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="css/dataTables.responsive.css" rel="stylesheet">
	<link href="css/buttons.dataTables.min.css" rel="stylesheet">
	<!--  -->
    
	<link href="css/bootstrap-datepicker.min.css" rel="stylesheet">
	<link href="css/sqentera.css" rel="stylesheet" type="text/css"/>
	
	<!--  -->
	<!-- <script src="js/jquery.min.js"></script> -->
	<script src="js/jquery.dataTables.min.js"></script>
	

	<script src="js/bootstrap.min.js"></script>
	<!-- <script src="js/bootstrap.bundle.min.js"></script> -->


    <script src="js/dataTables.bootstrap.min.js"></script>
	<!--  -->
	<script src="js/jszip.min.js"></script>
	<script src="js/js.cookie.js"></script>
	<script src="js/jquery.redirect.js"></script>
	<script src="js/bootstrap-session-timeout.min.js"></script>
</head>
<body>
<div id="wrapper">
	<div class="sidebar">
	
		<div class="navbar-header">
			<div class="navbar-brand">
				<?php echo $xml_button->welcome; ?>
				<span class="quota">
					<?php echo $xml_button->quota_aval.': '.str_replace('unlimited',$xml_button->unlimited,getQuota());?>
				</span>
				<h4 class="sidebar-search"><b>
				<?php
				if (in_array('8',$access_arr)) {
					echo '<a href="change_password.php" style="color:currentColor;" title="Change Password"><i class="fa fa-unlock-alt"></i> '.ucwords(@$_SESSION['userid']) . ' (' . $display_UserType .')</a>';
				} else {
					echo ucwords(@$_SESSION['userid']) . " (" . $display_UserType . ")";
				}
				?>
				</b></h4>
			</div>
		</div>
			<a id="web_menu"></a>
	</div>
	<div id="page-wrapper">
		<div id="page-wrapper-header">
			<!-- <div class="navbar-left"> -->
			<div class="navbar navbar-expand-lg navbar-light bg-light">
				<div class="container-fluid">
					<?php
						$xml_image = simplexml_load_file('/home/msg/conf/image_header.xml');
						$__image = $xml_image->image;
						if(empty($__image) || $__image == ""){
							echo "";
						}else{
							echo '<img src="images/'.$__image.'" height="40" border="0" alt="Logo">';
						}
					?>
					<a href="send_sms.php"><img src="images/sendQuickMessaging.png" alt="SQ"></a>

					<ul class="navbar-nav ml-auto">
						<!-- <ul class="navbar-top-links"> -->
						<?php
						$allowed = array( "useradmin" );
						
						if( in_array( $_SESSION['userid'], $allowed ) ){
						?>
						<li class="nav-item ml-sm-2">
							<i class="fa fa-language text-info"></i>
							<select id="lang" name="lang" <?php echo $disable_lang;?>>
								<option value="EN"><?php echo $xml_button->lang_en;?></option>
								<option value="CU"><?php echo $xml_button->lang_cu;?></option>
								<option value="CB"><?php echo $xml_button->lang_cb;?></option>
							</select>
						</li>
						<?php
						}
						?>
						<li class="nav-item ml-sm-2">
							<a href="logout.php"><i class="fa fa-sign-out fa-fw"></i><?php echo $xml_button->logout;?></a>
						</li class="nav-item"><li>&nbsp;</li>
					</ul>
				</div>
			</div>
			<!-- <div class="navbar-right">
				<ul class="navbar-top-links">
				<ul class="navbar-nav mr-auto">
					<li class="nav-item">
						<i class="fa fa-language text-info"></i><select id="lang" name="lang" <?php echo $disable_lang;?>><option value="EN"><?php echo $xml_button->lang_en;?></option><option value="CU"><?php echo $xml_button->lang_cu;?></option><option value="CB"><?php echo $xml_button->lang_cb;?></option></select>
					</li>
					<li class="nav-item">
						<a href="logout.php"><i class="fa fa-sign-out fa-fw"></i><?php echo $xml_button->logout;?></a>
					</li class="nav-item"><li>&nbsp;</li>
				</ul>
			</div> -->
			
		</div>
	<script src="js/header_js.php"></script>
