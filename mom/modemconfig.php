<?php
	$page_mode = '45';
	$page_title = 'Modem Configuration';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("sysconfig",$lang);
?>
		<div class="page-header">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->system_config;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->modem_conf;?></li>
				</ol>
			</nav>
		</div>

		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table table-striped table-bordered table-sm" id="modemconf">
								<thead>
								<tr>
									<th><?php echo $xml_common->no;?></th>
									<th><?php echo $x->department;?></th>
									<th><?php echo $x->modem_label;?></th>
									<th><?php echo $x->created_by;?></th>
									<th><?php echo $x->created_dtm;?></th>
									<th><?php echo $x->updated_by;?></th>
									<th><?php echo $x->updated_dtm;?></th>
									<th><input type="checkbox" id="all"></th>
								</tr>
								</thead>
								<tfoot>
								<tr>
									<td colspan="8">
										<span class="pull-left">
											<button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myModem"><?php echo $xml_common->add_new_record;?></button>
										</span>
										<span class="pull-right">
											<input type="submit" class="btn btn-warning btn-sm" name="delete" id="delete" value="<?php echo $xml_common->delete;?>">
										</span>
									</td>
								</tr>
								</tfoot>
							</table>
						</div>
					</div>
					<div class="modal fade" id="myModem" tabindex="-1" role="dialog">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
										<!-- <span aria-hidden="true">&times;</span> -->
									</button>
									<h4 class="modal-title" id="header">&nbsp;</h4>
								</div>
								<form id="frmModem" name="frmModem">
									<div class="modal-body">
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="dept" class="control-label"><?php echo $x->department;?></label>
											</div>
											<div class="col-md-6">
												<p><select name="dept" id="dept">
													<option value="0">All Departments</option>
												</select></p>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 offset-md-1">
												<label for="label" class="control-label"><?php echo $x->modem_label;?> <span style="color:red">*</span></label>
											</div>
											<div class="col-md-6">
												<p><select name="label" id="label" required>
													<option value=""><?php echo $x->nomodem_desc;?></option>
												</select></p>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<input type="hidden" name="idx" id="idx">
										<input type="hidden" name="mode" id="mode">
										<button id="save" type="submit" class="btn btn-primary"><?php echo $xml_common->save;?></button>
										<button id="cancel" type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<script nonce="<?php echo session_id();?>">
	loadlist('#dept','user_department_lib.php','getDepartmentList','department_id','department');
	loadlist('#label','modemconfig_lib.php','getLabel','label','label');
	var table = $('#modemconf').DataTable({
		autoWidth: false,
		processing: true,
		stateSave: true,
		ajax: {type:'POST',url:'modemconfig_lib.php',data:{mode:'listModemDept'}},
		columnDefs: [{'orderable':false,'targets':7}]
	});
	$('#all').change(function () {
		var cells = table.cells().nodes();
		$(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
	});
	$('#myModem').on('show.bs.modal', function(e) {
		var modal = $(this), idx = $(e.relatedTarget).data('idx');
		if(typeof idx === "undefined") {
			modal.find('#header').html('<?php echo $x->title_add;?>');
			modal.find('#mode').val('addModemLabel');
			modal.find('#dept').prop("disabled", false);
		} else {
			modal.find('#header').html('<?php echo $x->title_edit;?>');
			modal.find('#mode').val('updateModemLabel');
			modal.find('#idx').val(idx);
			$.post('modemconfig_lib.php',{mode:'editModemDept',idx:idx},function(val) {
				modal.find('#dept').val(val[0].dept);
				modal.find('#label').val(val[0].modem_label);
				modal.find('#dept').prop("disabled", true);
			},"json")
			.fail(function() {
				alert('Failed To Retrieve Modem Configuration');
			});
		}
	});
	$('#myModem').on('submit', function(e) {
		$.post('modemconfig_lib.php',$("#frmModem").serialize(),function(data) {
			if(data!='') {
				alert(data);
			} else {
				table.ajax.reload();
				$('#myModem').modal('hide');
			}
		});
		e.preventDefault();
	});		
	$('#myModem').on('hidden.bs.modal', function(e){
		$(this).find('form').trigger('reset');
	});
	$('#delete').on('click', function(e) {
		if(confirm('<?php echo $x->alert_10; ?>')) {
			$('input[type=checkbox]').each(function() {     
				if (this.checked && this.value!='on') {
					$.post('modemconfig_lib.php',{mode:'deleteModemDept',idx:this.value},function(data) {
						table.ajax.reload();
					});
				}
			});
			$('#all').prop('checked',false);
		}
	});
	function loadlist(selobj,url,mode,val,name){
		$.post(url,{mode:mode},function(data) {
			$.each(data, function(index, value) {
				$(selobj).append("<option value="+value[val]+">"+value[name]+"</option>");
			});
		},"json");
	};
	</script>
</body>
</html>
