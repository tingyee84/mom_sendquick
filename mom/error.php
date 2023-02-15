<?php
	$page_title = "Error";
	$php_name = "error.php";
	include('header.php');

	$xml = GetLanguage("error",$lang);
	$xml_title = $xml->title;
	$xml_op_error = $xml->op_error;
	$xml_click = $xml->click;
	$xml_here = $xml->here;
	$xml_back = $xml->back;
	$xml_or = $xml->str_or;
	$xml_to = $xml->to;
	$xml_login = $xml->login;
	$xml_usermgnt = $xml->usermgnt;
	$xml_addressbook = $xml->addressbook;
	$xml_msgtemplate = $xml->msgtemplate;
	$xml_sendsms = $xml->sendsms;
	$xml_scheduledsms = $xml->scheduledsms;
	$xml_commoninbox = $xml->commoninbox;
	$xml_logsmgnt = $xml->logsmgnt;
	$xml_unsublist = $xml->unsublist;
	$xml_quotamgnt = $xml->quotamgnt;
	$xml_keywordmgnt = $xml->keywordmgnt;
	$xml_sysconfig = $xml->sysconfig;
	$xml_languagesetup = $xml->languagesetup;
	$xml_changepassword = $xml->changepassword;
	$xml_httplog = $xml->httplog;
	$xml_report = $xml->report;

	$page_mode = $_SESSION['page_mode'];

	if(isset($_REQUEST['error_code'])) {
		$error_code = $_REQUEST['error_code'];
	} else {
		$error_code = 0;
	}
	
	if($error_code > 0) {
		header("Location : index.php");
		exit;
	} else {
		$error_code = $error_code + 1;
	}

	if(isset($_SESSION['error_msg'])) {
		$error_msg = $_SESSION['error_msg'];
	} else {
		$error_msg = $xml_op_error;
	}

	if($page_mode == "" || $_SESSION['userid'] == "") {
		$link = $xml_click. ' <a id="back" href="#">'.$xml_here.'</a> '.$xml_back;
	} else {
		$data = getPage($page_mode);
		
		if($data == "") {
			$link = $xml_click. ' <a id="back" href="#">'.$xml_here.'</a> '.$xml_back;
		} else {
			$arr = explode(",", $data);
			$page_address = $arr[0];
			$header_name = $arr[1];
			
			if($header_name != "")
			{
				if ($header_name == "User Management"){
				$header_name = $xml_usermgnt;}
				if ($header_name == "Address Book"){
				$header_name = $xml_addressbook;}
				if ($header_name == "Message Template"){
				$header_name = $xml_msgtemplate;}
				if ($header_name == "Send SMS"){
				$header_name = $xml_sendsms;}
				if ($header_name == "Scheduled SMS"){
				$header_name = $xml_scheduledsms;}
				if ($header_name == "Common Inbox"){
				$header_name = $xml_commoninbox;}
				if ($header_name == "Inbox/Logs Management"){
				$header_name = $xml_logsmgnt;}
				if ($header_name == "Unsubscribe List"){
				$header_name = $xml_unsublist;}
				if ($header_name == "Quota Management"){
				$header_name = $xml_quotamgnt;}
				if ($header_name == "Keyword Management"){
				$header_name = $xml_keywordmgnt;}
				if ($header_name == "System Configuration"){
				$header_name = $xml_sysconfig;}
				if ($header_name == "Language Setup"){
				$header_name = $xml_languagesetup;}
				if ($header_name == "Change Password"){
				$header_name = $xml_changepassword;}
				if ($header_name == "HTTP Log"){
				$header_name = $xml_httplog;}
				if ($header_name == "Report"){
				$header_name = $xml_report;}
				$header_name = $xml_to . $header_name;
			}
			$link = $xml_click ." <b><a href=\"" .$page_address. "\">".$xml_here."</a></b> ". $xml_back ."<b>".$header_name."</b>";
		}
	}

	if( !isset($page_address) ){
		$page_address = "";
	}

	if( !isset($page_mode) ){
		$page_mode = "";
	}
?>
		<div class="page-header">
			<ol class="breadcrumb">
				<li class="active"><?php echo $xml->title; ?></li>
			</ol>
		</div>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body table-responsive text-center">
						<div class="alert alert-danger">
							<?php echo htmlspecialchars($error_msg);?>
						</div>
						<?php echo $link; ?>
						<?php if(isset($data)) { ?>
						<h5><b><?php echo $xml_or; ?></b></h5>
						<?php echo $xml_click; ?> <b><a id="back" href="#"><?php echo $xml_here; ?></a></b> <?php echo $xml_back; ?>
						<?php } ?>
						<h5><b><?php echo $xml_or; ?></b></h5>
						<?php echo $xml_click; ?> <b><a href="index.php"><?php echo $xml_here; ?></a></b><?php echo $xml_login; ?>
					</div>
				</div>
			</div>
		</div>
	<?php include "footnote.php";?>
	</div>
	<script nonce="<?php echo session_id();?>">
	function IEKey(evt)
	{
		var key = (evt.which) ? evt.which : event.keyCode;
		var mode = '<?php echo $page_mode; ?>';
		var page = '<?php echo $page_address; ?>';
		if(mode == 25)
		{
			if(key == 13)
			{
				window.location = "index.php";
			}
		}
		else
		{
			if(key == 13)
			{
				if(mode != "")
				{
					window.location = page;
				}
				else
				{
					window.history.go(-1);
				}
			}
		}
	}
	$("#back").click(function(event) {
		event.preventDefault();
		history.back(1);
	});
	</script>
</body>
</html>
