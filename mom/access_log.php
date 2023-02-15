<?php
	$page_mode = '32';
	$page_title = 'Access Log';
	include('header.php');
	include('checkAccess.php');
?>
		<div class="page-header" style="padding-top:10px">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->user_mgnt;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->access_log;?></li>
				</ol>
			</nav>
		</div>
		
		<?php $x = GetLanguage("access_log",$lang); ?>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body table-responsive">
						<form id="accessForm" name="accessForm" method="post">
							<table style="border:none">
							<tr>
								<td><strong><?php echo $x->date_from;?></strong>&nbsp;</td>
								<td><input class="form-control input-sm" type="text" id="from" name="from" size="10" required/></td>
								<td>&nbsp;</td>
								<td><strong><?php echo $x->date_to;?></strong>&nbsp;</td>
								<td><input class="form-control input-sm" type="text" id="to" name="to" size="10" required/></td>
							</tr>
							</table>
							<input id="mode" name="mode" type="hidden" value="getAccessLog"/>
						</form>
						<br>
						<table class="table table-striped table-bordered table-condensed" id="access">
							<thead>
								<tr>
									<th><?php echo $x->login; ?></th>
									<th><?php echo $x->username; ?></th>
									<th><?php echo $x->remoteip; ?></th>
									<th><?php echo $x->agent; ?></th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<script src="js/moment.min.js"></script>
	<script src="js/bootstrap-datepicker.min.js"></script>
	<script nonce="<?php echo session_id();?>">
	$("#from").val(moment().format('DD/MM/YYYY'));
	$("#to").val(moment().format('DD/MM/YYYY'));
	$('#from, #to').datepicker({format: 'dd/mm/yyyy'});
	var table = $('#access').DataTable({
		deferRender: true,
		responsive: true,
		stateSave: true,
		ajax:{type: 'POST',
			url: 'access_log_lib.php',
			data: function () { return $('#accessForm').serialize(); }
		},
		columns: [
            { "data": "login_dtm_new" },
            { "data": "userid" },
            { "data": "remote_ip" },
            { "data": "user_agent" }
        ]
	});
	$('#from, #to').on('changeDate', function() {
		$('#from, #to').datepicker('hide');
		table.ajax.reload();
	});
	</script>
</body>
</html>
