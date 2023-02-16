<?php
	$page_mode = '301';
	$dbl_mode = '4';
	$page_title = 'Address Group';
	include('header.php');
	include('checkAccess.php');
?>
	<link href="css/style1.css" rel="stylesheet">
	
		<div class="page-header page-header2">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->address_book;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->address_group;?></li>
				</ol>
			</nav>
		</div>
		
		<?php $x = GetLanguage("address_group",$lang); ?>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table table-striped table-bordered table-condensed" id="tblgroup">
								<thead>
									<tr>
										<th><?php echo $x->group_name;?></th>
										<th><input type="checkbox" name="all" id="all"></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="2">
											<span class="pull-left" id="export">
												
											</span>
											<span class="pull-right">
												<button id="create" type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myGroup"><?php echo $xml_common->add_new_record;?></button>
												<button id="truncate" type="submit" class="btn btn-warning btn-sm"><?php echo $x->empty_str;?></button>
												<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete; ?></button>
											</span>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
						<!-- Modal -->
						<div class="modal fade" id="myGroup" tabindex="-1" role="dialog">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header" id="myGroup_header">
										<h4 class="modal-title"><?php echo $x->create_new; ?></h4>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
											<!-- <span aria-hidden="true">&times;</span> -->
										</button>
									</div>
									<form id="group_form" name="group_form" method="post">
									<div class="modal-body">
										<div class="row">
											<div class="col-md-10">
												<div id="msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2 msgstatusbar" role="alert">
													<span id="msgstatustext">A</span>	
													<button type="button" class="btn-close" id="msgstatusbar_close" aria-label="Close"></button>											
												</div>
											</div>											
										</div>	
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="group" class="control-label"><?php echo $x->new_group_name; ?></label>
											</div>
											<div class="col-md-6">
												<input class="form-control input-sm" type="text" name="group" id="group" maxlength="30" required>
												<div id="invalid_group" class="invalid-feedback">
													<?php echo $x->invalid_group; ?>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-11 offset-md-1">
												<label for="group" class="control-label"><?php echo $x->put_contact; ?></label>
											</div>
										</div>
										<div class="row">
											<div class="col-md-10 offset-md-1 table-responsive">
												<table id="contactlist" class="table table-striped table-bordered table-condensed">
												<thead>
												<tr>
													<th>#</th>
													<th><?php echo $x->contact_name;?></th>
													<th><?php echo $x->mobile_number; ?></th>
													<th><input type="checkbox" name="modal_all" id="modal_all"></th>
												</tr>
												</thead>
												<tbody>
												</tbody>
												</table>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<input type="hidden" name="mode" value="addAddressGroup"/>
										<input type="hidden" name="id_of_user" value="<?php echo $id_of_user; ?>"/>
										<input type="hidden" name="department" value="<?php echo $department; ?>"/>
										<input type="hidden" name="selectedcontactid" value=""/>
										<button id="save" type="submit" class="btn btn-primary"><?php echo $xml_common->save; ?></button>
										<button id="cancel" type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo $xml_common->cancel; ?></button>
									</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<script src="js/dataTables.buttons.min.js"></script>
	<script src="js/buttons.html5.min.js"></script>
	<script language="javascript" src="js/txvalidator.js"></script>
	<script type="application/javascript" src="address_group_js.php"></script>
</body>
</html>
