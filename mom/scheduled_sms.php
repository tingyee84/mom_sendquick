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
	<script type="application/javascript" src="scheduled_sms_js.php"></script>
</body>
</html>
