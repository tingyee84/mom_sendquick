<?php
	$page_mode = '7';
	$page_title = 'Broadcast Success Page';
	include('header.php');
	include('checkAccess.php');
?>
<div class="page-header">
  <ol class="breadcrumb">
    <li class="active"><?php echo $page_title; ?></li>
  </ol>
</div>
<div class="page-content">
  <div class="col-lg-12">
    <div class="panel panel-default">
			<div class="panel-heading">
				Broadcast MIM Messaging
			</div>
      <div class="panel-body">
        <p class="text-center"><span id="output"></span></p>
      </div>
			<div class="panel-footer">
				<a href="broadcast_mim.php">
					<span><i class="fa fa-arrow-circle-left">&nbsp;<b>Back</b></i></span>
				</a>
			</div>
    </div>
  </div>
</div>
<?php include('footnote.php'); ?>
</div>
<script nonce="<?php echo session_id();?>">
	var error = '<?php echo $_REQUEST['error']; ?>';
	var output = '<?php echo $_REQUEST['output']; ?>';

	$('#output').html(output);
	if(error == 0){
		$(".panel").addClass("panel-primary").removeClass("panel-red");
		$(".panel-body").addClass("alert-info").removeClass("alert-danger");
	} else {
		$(".panel").addClass("panel-red").removeClass("panel-primary");
		$(".panel-body").addClass("alert-danger").removeClass("alert-info");
	}
</script>
</body>
</html>
