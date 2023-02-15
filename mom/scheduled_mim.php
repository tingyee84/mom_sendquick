<?php
	$page_mode = '77';
	$dbl_mode = '7';
	$page_title = 'Scheduled MIM';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("scheduled_sms",$lang);
?>
		<div class="page-header">
			<ol class="breadcrumb">
				<li class="active"><?php echo $page_title ?></li>
			</ol>
		</div>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body table-responsive">
						<table class="table table-striped table-bordered table-condensed" id="schedule">
							<thead>
								<tr>
									<th><?php echo "Profile Name";?></th>
									<th><?php echo $x->message_text;?></th>
									<th><?php echo $x->scheduled_date;?></th>
									<th><input type="checkbox" id="all"></th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td colspan="4">
										<span class="pull-right">
											<button id="truncate" type="submit" class="btn btn-warning btn-sm"><?php echo "Empty Scheduled MIM(s)";?></button>
											<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete;?></button>
										</span>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<script src="js/moment.min.js"></script>
	<script src="js/datetime-moment.js"></script>
	<script nonce="<?php echo session_id();?>">
	$.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');
	var table =  $('#schedule').DataTable({
		autoWidth: false,
		deferRender: true,
		processing: true,
		stateSave: true,
		ajax: {type:'POST',url:'scheduled_mim_lib.php',data:{mode:'listBCScheduled'}},
		columnDefs: [{ "orderable":false,"targets":3},
					 { "width":"50%","targets":1}]
	});
	$('#all').change(function () {
		var cells = table.cells().nodes();
		$(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
	});
	$('#delete').on('click', function(e) {
		if(confirm('<?php echo $x->alert_4; ?>')) {
			$('input[type=checkbox]').each(function() {
				if (this.checked && this.value!='on') {
					$.post('scheduled_mim_lib.php',{mode:'deleteBCScheduled',idx:this.value},function(data) {
						table.ajax.reload();
					});
				}
			});
			$('#all').prop('checked',false);
		}
	});
	$('#truncate').on('click', function(e) {
		if(confirm('<?php echo $x->alert_5; ?>')) {
			$.post('scheduled_mim_lib.php',{mode:'emptyBCScheduled'},function(data) {
				table.ajax.reload();
			});
		}
	});
	</script>
</body>
</html>
