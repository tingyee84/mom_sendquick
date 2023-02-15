<?php
	$page_mode = '10';
	$page_title = 'Common Inbox';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("common_inbox",$lang);
?>
		<div class="page-header" style="padding-top:10px">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->common_inbox;?></li>
				</ol>
			</nav>
		</div>
	
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body table-responsive">
						<form id="inboxForm" name="inboxForm">
						<table style="border:none">
							<tr>
								<td><b><?php echo $xml_common->date_from;?></b>&nbsp;</td>
								<td><input class="form-control input-sm" type="text" id="from" name="from" size="10" required/></td>
								<td>&nbsp;</td>
								<td><b><?php echo $xml_common->date_to;?></b>&nbsp;</td>
								<td><input class="form-control input-sm" type="text" id="to" name="to" size="10" required/></td>
							</tr>
						</table>
						<input name="mode" type="hidden" value="view"/>
						</form>
						<br>
						<table class="table table-striped table-bordered table-sm" id="inbox">
							<thead>
								<tr>
									<th><?php echo $x->date_time;?></th>
									<th><?php echo $x->mobile_number;?></th>
									<th><?php echo $x->unmatched_keyword;?></th>
									<th><input type="checkbox" id="all"></th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td colspan="4">
										<div id="export"></div>
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
		<?php include('footnote.php');?>
	</div>	
	<?php include("common_inbox_js.php");?>	
</body>
</html>
