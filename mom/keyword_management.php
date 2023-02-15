<?php
	$page_mode = '56';
	$page_title = 'Keyword Management';
	//include('db_keyword.php');
	include('header.php');
	include('checkAccess.php');
	
?>
		<div class="page-header">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->keyword_mgnt;?></li>
				</ol>
			</nav>
		</div>
		
		<?php $x = GetLanguage("keyword_management",$lang); ?>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<table class="table table-striped table-bordered table-condensed" id="keyword">
							<thead>
								<tr>
									<th><?php echo $x->keyword; ?></th>
									<th><?php echo $x->description; ?></th>
									<th><?php echo $x->from; ?></th>
									<th><?php echo $x->department; ?></th>
									<th><?php echo "Application Service ID"; ?></th>
									<th><?php echo "Application URL"; ?></th>
									<th><input type="checkbox" name="all" id="all"></th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td colspan="7">
										<span style="float:left">
											<!-- <button id="create" type="submit" class="btn btn-primary btn-sm"><?php echo $xml_common->add_new_record;?></button> -->
											<button id="create" type="submit" class="btn btn-primary btn-sm"><?php echo "Add Portal Keyword"; ?></button>
											<?php 

											if($_SESSION['userid'] == "useradmin" || $_SESSION['userid'] == "momadmin"){
												echo '<button id="create_api" type="submit" class="btn btn-primary btn-sm">Add Application Keyword</button>';
											}

											?>
											<!-- <button id="create_api" type="submit" class="btn btn-primary btn-sm"><?php echo "Add Application Keyword";?></button> -->
										</span>
										<span style="float:right">
											<!--<button id="truncate" type="submit" class="btn btn-warning btn-sm"><?php echo $x->empty_str;?></button>-->
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
		<?php include('keyword_js.php'); ?>
</body>
</html>
