<?php
	$page_mode = '7';
	$page_title = 'Broadcast Log';
	include('header.php');
	include('checkAccess.php');
?>
<div class="page-header">
  <ol class="breadcrumb">
    <li><?php echo $xml->logs_mgnt; ?></li>
    <li class="active"><?php echo $xml->broadcast_log;?></li>
  </ol>
</div>
<?php $x = GetLanguage("global_sent_log",$lang); ?>
<div class="page-content">
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="table-responsive">
          <form id="BCLogForm" name="BCLogForm">
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
                <th style="width: 50%;"><?php echo $x->message_text;?></th>
				<th><?php echo $x->recipient;?></th>
              </tr>
            </thead>
            <!--tfoot>
              <tr>
                <td colspan="5">
                  <div id="export" style="float:left"></div>
                  <span style="float:right">
                    <button id="truncate" type="submit" class="btn btn-warning btn-sm"><?php echo "Empty Broadcast Log";?></button>
                    <button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete;?></button>
                  </span>
                </td>
              </tr>
            </tfoot-->
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
<?php include('footnote.php'); ?>
</div>
<?php include('broadcast_log_js.php'); ?>
</body>
</html>
