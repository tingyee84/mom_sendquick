<?php
	$page_mode = '14';
	$parent_mode = '500';
	$page_title = 'Queue Log';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("queue_log",$lang);
	if(in_array('16', $access_arr)) {
		$delete = 1;
	} else {
		$delete = 0;
	}
	if(in_array('15', $access_arr)) {
		$export = 1;
	} else {
		$export = 0;
	}
?>
		<div class="page-header" style="padding-top:10px">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->logs_mgnt;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->queue_log;?></li>
				</ol>
			</nav>
		</div>

		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="table-responsive">
							<form id="queueForm" name="queueForm">
								<table style="border:none">
								<tr>
									<td><b><?php echo $x->date_from;?></b>&nbsp;</td>
									<td><input class="form-control input-sm" type="text" id="from" name="from" size="10" required/></td>
									<td>&nbsp;</td>
									<td><b><?php echo $x->date_to;?></b>&nbsp;</td>
									<td><input class="form-control input-sm" type="text" id="to" name="to" size="10" required/></td>
								</tr>
								</table>
								<input name="mode" type="hidden" value="listLog"/>
							</form>
							<br>
							<table class="table table-striped table-bordered table-sm" id="queue">
								<thead>
									<tr>
										<th><?php echo $x->date_time;?></th>
										<th><?php echo $x->mobile_number;?></th>
										<th><?php echo $x->message_text;?></th>
										<th><?php echo $x->status;?></th>
										<th><input type="checkbox" id="all"></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="5">
											<div id="export"></div>
											<?php if ($delete == "1") { ?>
											<span class="pull-right">
												<button id="truncate" type="submit" class="btn btn-danger btn-sm"><?php echo $x->empty_str;?></button>
												<button id="delete" type="submit" class="btn btn-warning btn-sm"><?php echo $xml_common->delete;?></button>
											</span>
											<?php } ?>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<script src="js/bootstrap-datepicker.min.js"></script>
    <script src="js/moment.min.js"></script>
	<script src="js/dataTables.buttons.min.js"></script>
	<script src="js/buttons.html5.min.js"></script>
	<script src="js/datetime-moment.js"></script>
	<script nonce="<?php echo session_id();?>">
	$('#from').val(moment().subtract(1,'week').format('DD/MM/YYYY'));
	$('#to').val(moment().format('DD/MM/YYYY'));
	$('#from, #to').datepicker({format: 'dd/mm/yyyy'});
	$.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');
	var table =  $('#queue').DataTable({
		autoWidth: false,
		deferRender: true,
		processing: true,
		stateSave: true,
		pageLength: 100,
		ajax:{type: 'POST',
			url: 'queue_log_lib.php',
			data: function(){return $('#queueForm').serialize();}
		},
		columnDefs: [{ 'orderable':false,'targets':4},
					 { 'width':'50%','targets':2}]
	});

	// assmi
	$("#all").change(function(){
		$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
	});

	$("#queue").on('change',"input[type='checkbox']",function(e){
		if($(this).prop("checked") == false){
			$('#all').prop('checked', false);
		}
	});

	$("#queue").on('change',"input[type='checkbox']",function(e){
		if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
			$('#all').prop('checked', true);
		}

		if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
			$('#all').prop('checked', true);
		}
	});

	$('#queue').on( 'draw.dt', function () {
		if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
			$('#all').prop('checked', true);
		}else{
			$('#all').prop('checked', false);
		}
	});
	// assmi

	<?php if ($export == "1") { ?>
	function toCallDate(filename,ndate) {
		return  filename+moment(ndate).format("YYYY-MM-DD hhmmss");
	}
	var date = $.now();
	new $.fn.dataTable.Buttons( table, {
	buttons: [
		{
			extend:'csv',
			text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export.' CSV'; ?>',
			exportOptions: {columns: ':visible'},
			filename: function() {
				return toCallDate('<?php echo $_SESSION['userid']; ?>_QueueLog_',new Date());
			},
			init: function(api,node,config){
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			}},
		{
			extend:'excel',
			text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export.' Excel'; ?>',
			exportOptions: {columns: ':visible'},
			filename: function() {
				return toCallDate('<?php echo $_SESSION['userid']; ?>_QueueLog_',new Date());
			},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-success btn-sm");
			}}
		]
	} );
	
	table.buttons().container().appendTo('#export');
	<?php } ?>
	$('#from, #to').on('changeDate', function() {
		$('#from, #to').datepicker('hide');
		table.ajax.reload();
	});
	$('#all').change(function () {
		var cells = table.cells().nodes();
		$(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
	});
	$('#delete').on('click', function(e) {
		if(confirm('<?php echo $x->alert_4; ?>')) {
			$('input[type=checkbox]').each(function() {     
				if (this.checked && this.value!='on') {
					$.post('queue_log_lib.php',{mode:'delete',idx:this.value},function(data) {
						table.ajax.reload();
					});
				}
			});
			$('#all').prop('checked',false);
		}
	});
	$('#truncate').on('click', function(e) {
		if(confirm('<?php echo $x->alert_5; ?>')) {
			$.post('queue_log_lib.php',{mode:'emptyLog'},function(data) {
				table.ajax.reload();
			});
		}
	});
	</script>
</body>
</html>
