<?php
	$page_mode = '7';
	$page_title = 'Email Log';
	include('header.php');
	include('checkAccess.php');
?>
<div class="page-header">
  <ol class="breadcrumb">
    <li><?php echo $xml->logs_mgnt; ?></li>
    <li class="active"><?php echo $xml->email_log; ?></li>
  </ol>
</div>
<?php 
	$x = GetLanguage("global_sent_log",$lang); 
	//$x->alert_4 = "Confirm To Delete Selected Email Log(s)? This Is A Irreversible Action - Deleted Email Log(s) Will Be Permanently Removed From The System!";
	//$X->alert_5 = "Confirm To Empty All Email Log(s)? This Is A Irreversible Action - Deleted Email Log(s) Will Be Permanently Removed From The System"; 
?>
<div class="page-content">
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="table-responsive">
          <form id="LogForm" name="LogForm">
            <table style="border:none">
            <tr>
              <td><?php echo $x->date_from;?></td>
              <td><input class="form-control input-sm" type="text" id="from" name="from" size="10" required/></td>
              <td>&nbsp;</td>
              <td><?php echo $x->date_to;?></td>
              <td><input class="form-control input-sm" type="text" id="to" name="to" size="10" required/></td>
            </tr>
            </table>
            <input name="mode" type="hidden" value="list"/>
          </form>
          <br>
          <table class="table table-striped table-bordered table-condensed" id="bclog" style="width:100%;">
            <thead>
              <tr>
                <th><?php echo $x->date_time;?></th>
                <th><?php echo $x->sender;?></th>
                <th><?php echo $x->department;?></th>
                <th><?php echo $x->from;?></th>
                <th><?php echo $x->to;?></th>
                <th><?php echo $x->subject;?></th>
                <th style="width: 30%;"><?php echo $x->message_text;?></th>
				<th><?php echo $x->comment;?></th>
				<th><input type="checkbox" id="all"></th>
              </tr>
            </thead>
            <?php if(isUserAdmin(strtolower($_SESSION['userid']))) { ?>
            <tfoot>
              <tr>
                <td colspan="9">
                  <div id="export" style="float:left"></div>
                  <span style="float:right">
                    <button id="truncate" type="submit" class="btn btn-warning btn-sm"><?php echo $x->empty_email_log;;?></button>
                    <button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete;?></button>
                  </span>
                </td>
              </tr>
            </tfoot>
            <?php } ?>
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
<script src="js/datetime-moment.js"></script>
<script nonce="<?php echo session_id();?>">
$("#from").val(moment().subtract(1,'week').format('DD/MM/YYYY'));
$("#to").val(moment().format('DD/MM/YYYY'));
$('#from, #to').datepicker({
  format: 'dd/mm/yyyy',
  autoclose: true
});
$.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');


var table = $('#bclog').DataTable({
  deferRender: true,
  responsive: true,
  pageLength: 100,
  ajax:{type: 'POST',
    url: 'email_log_lib.php',
    data: function () { return $('#LogForm').serialize(); }
  },
  columnDefs: [{ "orderable": false, "targets": 8 }]
});

$('#from, #to').on('changeDate', function() {
  $('#from, #to').datepicker('hide');
  table.ajax.reload();//table.draw();
});

$('#all').change(function () {
	var cells = table.cells().nodes();
	$(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
});

$('#delete').on('click', function(e) {
		if(confirm('<?php echo $x->alert_4; ?>')) {
			$('input[type=checkbox]').each(function() {     
				if (this.checked && this.value!='on') {
					$.post('email_log_lib.php',{mode:'delete',idx:this.value},function(data) {
						table.ajax.reload();
					});
				}
			});
			$('#all').prop('checked',false);
		}
	});
	$('#truncate').on('click', function(e) {
		if(confirm('<?php echo $x->alert_5; ?>')) {
			$.post('email_log_lib.php',{mode:'empty'},function(data) {
				table.ajax.reload();
			});
		}
	});
	<?php if(!isUserAdmin(strtolower($_SESSION['userid']))) { ?>
		table.column(8).visible(false);
	<?php } else { ?>
		table.column(8).visible(true);
	<?php } ?>
	
</script>

</body>
</html>
