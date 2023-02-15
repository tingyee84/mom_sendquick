<?php
	$page_mode = '77';
	$dbl_mode = '7';
	$page_title = 'Scheduled SMS';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("scheduled_sms",$lang);
?>
		<div class="page-header">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item active" aria-current="page"><?php echo $x->title;?></li>
				</ol>
			</nav>
		</div>
		
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body table-responsive">
						<table class="table table-striped table-bordered table-condensed" id="schedule">
							<thead>
								<tr>
									<th><?php echo $x->mobile_number;?></th>
									<th><?php echo $x->message_text;?></th>
									<th><?php echo $x->scheduled_date;?></th>
									<th><?php echo $x->priority_status;?></th>
									<th><?php echo $x->send_type;?></th>
									<th><input type="checkbox" id="all"></th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td colspan="6">
										<span class="pull-right">
											<button id="truncate" type="submit" class="btn btn-warning btn-sm"><?php echo $x->empty_str;?></button>
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
		ajax: {type:'POST',url:'scheduled_sms_lib.php',data:{mode:'listScheduled'}},
		columnDefs: [{ "orderable":false,"targets":5},
					 { "width":"50%","targets":1}]
	});
	$('#all').change(function () {
		var cells = table.cells().nodes();
		$(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
	});
	$('#delete').on('click', function(e) {
		if(confirm('<?php echo $x->alert_2; ?>')) {
			$('input[type=checkbox]').each(function() {     
				if (this.checked && this.value!='on') {
					$.post('scheduled_sms_lib.php',{mode:'deleteScheduled',idx:this.value},function(data) {
						
						//alert(data);
						//return false;
						table.ajax.reload();
					});
				}
			});
			$('#all').prop('checked',false);
		}
	});
	$('#truncate').on('click', function(e) {
		if(confirm('<?php echo $x->alert_3; ?>')) {
			$.post('scheduled_sms_lib.php',{mode:'emptyScheduled'},function(data) {
				table.ajax.reload();
			});
		}
	});
	</script>
</body>
</html>
