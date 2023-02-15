<?php $page_title = "Conversation Export";
include("header.php");
$inc_id = $_REQUEST['inc_id'];
?>
<div class="page-header">
  <ol class="breadcrumb">
    <li class="active"><?php echo "Conversation Export"; ?></li>
  </ol>
</div>
<div class="page-content">
  <div class="col-lg-12" id="export_div">
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="table-responsive">
          <form id="exportForm" name="exportForm" method="post">
            <table style="border:none">
            <tr>
              <td><?php echo "From :";?></td>
              <td><input class="form-control input-sm" type="text" id="from_dt" name="from_dt" size="10" required/></td>
              <td>&nbsp;&nbsp;&nbsp;</td>
              <td><?php echo "To :";?></td>
              <td><input class="form-control input-sm" type="text" id="to_dt" name="to_dt" size="10" required/></td>
            </tr>
            </table>
            <input name="mode2" type="hidden" value="exportchat"/>
            <input type="hidden" id="inc_id2" name="inc_id2" value="<?php echo $inc_id; ?>">
          </form>
          <br>
          <table class="table table-striped table-bordered table-condensed" id="exporttbl" style="width:100%;">
            <thead>
              <tr>
                <th><?php echo "Incoming ID";?></th>
                <th><?php echo "Mobile";?></th>
                <th><?php echo "Message";?></th>
                <th><?php echo "Date";?></th>
                <th><?php echo "Message Type";?></th>
                <th><?php echo "Sender";?></th>
                <th><?php echo "Broadcast ID";?></th>
                <th><?php echo "Display Name";?></th>
                <th><?php echo "Channel";?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <td colspan="9">
                  <div id="export2" style="float:left"></div>
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
<?php include('conv_export_js.php'); ?>
</body>
</html>
