<?php
	$page_mode = '45';
	$page_title = 'LDAP Server Management';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("ldap_mgnt",$lang);
	//$x->dlgrp = "Download Group";
?>
		<div class="page-header">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->system_config;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->ldap_mgnt;?></li>
				</ol>
			</nav>
		</div>
		
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table table-striped table-bordered table-condensed" id="ldaptable">
								<thead>
									<tr>
										<th><?php echo $x->no;?></th>
										<th><?php echo $x->name; ?></th>
										<th><?php echo $x->description; ?></th>
										<th><?php echo $x->server; ?></th>
										<th><?php echo $x->login_mode; ?></th>
										<th><?php echo $x->attr_name; ?></th>
										<th><?php echo $x->basedn; ?></th>
										<th><?php echo $x->scope; ?></th>
										<th><?php echo $x->dlgrp; ?></th>
										<th><?php echo $x->sync_info; ?></th>
										<th><input type="checkbox" id="all"></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="11">
											<span class="pull-left">
												<button id="addldap" type="submit" class="btn btn-primary btn-sm"><?php echo $xml_common->add_new_record; ?></button>
											</span>
											<span class="pull-right">
												<button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete; ?></button>
											</span>
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
	<script nonce="<?php echo session_id();?>">
	var strdelete = '<?php echo $x->alert_2; ?>';
	</script>
	<script src="ldap_mgnt_js.php"></script>
</body>
</html>
