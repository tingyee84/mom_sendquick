<?php
	$page_mode = '7';
	$page_title = 'Broadcast MIM';
	include('header.php');
	include('checkAccess.php');
?>
		<div class="page-header">
			<ol class="breadcrumb">
				<li class="active"><?php echo $xml->broadcast_mim; ?></li>
			</ol>
		</div>
		<?php $x = GetLanguage("send_sms",$lang);?>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div id="status" class="alert alert-dismissable alert-sm text-center hidden">
							<button class="close">&times;</button>
							<span id="output"></span>
						</div>
						<form id="sendBCForm" name="sendBCForm">
						<div class="row">
							<div class="col-lg-4">
								<label class="control-label"><?php echo $x->descr1; ?> <span style="color:red">*</span></label>
								<p><input type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#getContact" value="<?php echo $x->select_addressbook; ?>"></p>
							</div>
							<div class="col-lg-4">
								<div class="form-control input-sm" name="bc_rcpt" id="bc_rcpt" rows="5" required="" style="background-color: lightgray; height: 100px; overflow: auto; resize: vertical;"></div>
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="col-lg-4">
								<label class="control-label"><?php echo $x->content_type; ?>:</label>
							</div>
							<div class="col-lg-4">
								<p>
									<select name="cont_type" id="cont_type">
										<option value="1"><?php echo $x->text; ?></option>
										<option value="2"><?php echo $x->image; ?></option>
										<!--option value="3"><?php echo "Text + Image"; ?></option-->
									</select>
								</p>
							</div>
						</div>
						<hr>
						<div class="row" id="txt">
							<div class="col-lg-4">
								<label class="control-label"><?php echo $x->message; ?> <span style="color:red">*</span></label>
								<p><input type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#getTemplate" value="<?php echo $x->select_msgtemplate; ?>"></p>
							</div>
							<div class="col-lg-4">
								<p><textarea class="form-control input-sm" name="bc_text" id="bc_text" rows="5" required></textarea></p>
							</div>
						</div>
						<div class="row" id="img" style="display:none;">
							<div class="col-lg-4">
								<label class="control-label"><?php echo $x->descr2; ?>: <span style="color:red">*</span></label>
								<p><button class="btn btn-info btn-circle" id="img_btn"><i class="fa fa-image"></i></button></p>
							</div>
							<div class="col-lg-4">
								<div class="form-control input-sm" name="bc_img" id="bc_img" rows="5" required="" style="background-color: lightgray; height: 100px; overflow: auto; resize: vertical;"></div>
								<input type="hidden" name="mime_image" id="mime_image" />
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="col-lg-4">
								<label class="control-label"><?php echo $x->scheduling_message; ?></label>
							</div>
							<div class="col-lg-4">
								<input type="checkbox" name="scheduled" id="scheduled" value="1">&nbsp;<?php echo $xml_common->yes; ?>
								<div id="divScheduled" class="hidden">
									<hr>
									<div class="row">
										<div class="col-lg-2">
											<?php echo $x->date;?>
										</div>
										<div class="col-lg-5">
											<p><input type="text" class="form-control input-sm" name="bc_date" id="bc_date" value="<?php echo strftime("%d-%m-%Y", time()); ?>"></p>
										</div>
									</div>
									<div class="row">
										<div class="col-lg-2">
											<?php echo $x->time; ?>
										</div>
										<div class="col-lg-5">
											<p><select name="bc_hour" id="bc_hour">
											<?php
											$bc_hour = strftime("%H", time());
												for($a=0; $a<24; $a++)
												{
											?>
												<option value="<?php echo $a;?>" <?php echo $a == $bc_hour ? "selected" : ""; ?>>
													<?php echo $a < 10 ? "0".$a : $a; ?>
												</option>
											<?php } ?>
											</select>
											&nbsp;
											<select name="bc_min" id="bc_min">
											<?php
												$bc_min = strftime("%M", time());
												for($b=0; $b<60; $b++)
												{
											?>
											<option value="<?php echo $b;?>" <?php echo $b == $bc_min ? "selected" : ""; ?>>
												<?php echo $b < 10 ? "0".$b : $b; ?>
											</option>
											<?php }?>
											</select></p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<hr>
						<div class="row text-center">
							<input type="hidden" name="mode" id="mode" value="sendBC">
							<input type="hidden" name="max_length" id="max_length">
							<input type="hidden" name="count_chars" id="count_chars">
							<button id="submit" type="submit" class="btn btn-primary"><?php echo $x->send;?></button>
							<button id="clear" type="reset" class="btn btn-light"><?php echo $xml_common->reset;?></button>
						</div>
						</form>
						<form id="uploadForm" name="uploadForm" method="POST">
							<input type="file" id="img_file" name="img_file" accept="image/*" style="display:none"/>
							<div id="progress" class="row text-center" style="display:none">
	            <div class="col-md-4 offset-md-4">
	              <div class="progress">
	                <div id="bar" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="min-width:0.5em;">
	                  Processing ... <span id="percent"></span>
	                </div>
	              </div>
	            </div>
	            </div>
	            <input name="mode" type="hidden" value="uploadImage">
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php $x1 = Getlanguage("get_contacts",$lang);?>
		<div class="modal fade" id="getContact" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title"><?php echo $x1->title_addressbook; ?></h4>
					</div>
					<div class="modal-body">
							<div id="global_contacts">
								<table class="table table-striped table-bordered table-condensed" id="tblbc_rcpt">
									<thead>
										<tr>
											<th><?php echo $xml_common->no;?></th>
											<th><?php echo "Profile Name";?></th>
											<th><?php echo "MIM Channel"; ?></th>
											<th><input type="checkbox" id="c_bc"></th>
										</tr>
									</thead>
								</table>
							</div>
					</div>
					<div class="modal-footer">
						<input type="button" id="s_cont" class="btn btn-primary btn-sm" value="<?php echo $xml_common->select;?>">
						<input type="button" class="btn btn-default btn-sm" value="<?php echo $xml_common->cancel;?>" data-dismiss="modal">
					</div>
				</div>
			</div>
		</div>
		<?php $x2 = Getlanguage("get_template",$lang);?>
		<div class="modal fade" id="getTemplate" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title"><?php echo $x2->title;?></h4>
					</div>
					<div class="modal-body">
							<div id="global_template">
								<div class="row">
									<div class="col-lg-10">
										<?php echo $x2->select_msgtemplate2;?><hr>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-10">
										<select class="form-control input-sm" id="bc_msg_temp" name="template_text"></select>
									</div>
								</div>
							</div>
					</div>
					<div class="modal-footer">
						<input type="button" id="s_temp" class="btn btn-primary btn-sm" value="<?php echo $xml_common->select ?>">
						<input type="button" class="btn btn-default btn-sm" value="<?php echo $xml_common->cancel ?>" data-dismiss="modal">
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<?php include('broadcast_mim_js.php'); ?>
</body>
</html>
