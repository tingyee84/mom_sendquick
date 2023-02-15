<?php
	$page_title = 'Logout Confirmation';
	if(isset($_GET['yes']))
	{
		session_start();
		session_destroy();
		if (isset($_GET['sessionend']))
			header("Location: index.php?autologout");
		else
			header("Location: index.php?logoutsuccess");
		exit;
	} else {
		include('header.php');

		$xml = GetLanguage("logout",$lang);
		$xml_title = $xml->title;
		$xml_user = (string)$xml->user;
		$xml_yes = $xml->yes;
		$xml_no = $xml->no;
		$xml_message_1 = (string)$xml->message_1;
		$xml_message_2 = (string)$xml->message_2;
	}
?>
		<div class="page-header">
			<ol class="breadcrumb">
				<li class="active"><?php echo $xml_title; ?></li>
			</ol>
		</div>
		<div class="page-content">
			<div class="col-lg-12">
				<div id="panel" class="card">
					<div class="card-header  bg-warning">
						<b><?php echo $xml_title; ?></b>
					</div>
					<div class="card-body">
						<p class="text-center"><b><?php echo $xml_user; ?> '<?php echo $_SESSION['userid']; ?>' <?php echo $xml_message_2; ?></b></p>
					</div>
					<div class="card-footer text-center">
						<button id="logout" name="logout" type="submit" class="btn btn-primary"><?php echo $xml->yes;?></button>
						<button id="cancel" type="button" class="btn btn-secondary"><?php echo $xml->no;?></button>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<script nonce="<?php echo session_id();?>">
	$('#logout').click(function(){
		window.location = 'logout.php?yes';
	});
	$('#cancel').click(function() {
		history.back(1);
	});
	</script>
</body>
</html>
