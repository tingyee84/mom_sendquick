<?php
	$page_mode = '300';
	$chk_mode = '4';
	$page_title = 'Audit Trail';
	include('header.php');
	include('checkAccess.php');
?>
		<div class="page-header" style="padding-top:10px">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->audit_trail; ?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo "Audit Trail";?></li>
				</ol>
			</nav>
		</div>
		
		<?php $x = GetLanguage("MESSAGELOG_Audit_Log",$lang);?>
        <div class="page-content">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-body table-responsive" style = "overflow-x:hidden;">
						<form id="auditTrailForm" name="auditTrailForm" method="post">
                        <table style="border:none">
						<tr>
							<td><?php echo $x->search_from;?></td>
							<td><input class="form-control input-sm" type="text" id="from" name="from" size="10" required/></td>
							<td>&nbsp;</td>
							<td><?php echo $x->search_to;?></td>
							<td><input class="form-control input-sm" type="text" id="to" name="to" size="10" required/></td>
						</tr>
						</table>
						<input id="mode" name="mode" type="hidden" value="view"/>
						</form>
						<br>
						
						<table class="table table-striped table-bordered table-sm dataTable" style = "width:100%;" id="audittrail">
                
                            <thead>
                                <tr>
									<th><?php echo $xml_common->no;?></th>
                                    <th><?php echo $x->date_time; ?></th>
                                    <th><?php echo $x->username; ?></th>
                                    <th><?php echo $x->ip; ?></th>
                                    <th><?php echo $x->action; ?></th>
                                    <th><?php echo "From"; ?></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td colspan="6">
                                        <div id="export"></div>
                                        <span class="pull-right">
                                            <input id="reload" type="button" class="btn btn-primary btn-sm" value="<?php echo $x->refresh;?>">
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
    <script src="js/bootstrap-datepicker.min.js"></script>
    <script src="js/moment.min.js"></script>
	<script src="js/dataTables.buttons.min.js"></script>
	<script src="js/buttons.html5.min.js"></script>
	<script src="js/datetime-moment.js"></script>
    <?php 
        include("audit_trail_js.php");
    ?>

</body>
</html>