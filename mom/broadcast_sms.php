<?php

	//error_reporting(E_ALL);
	//ini_set('display_errors', TRUE);

	$page_mode = '7';
	$page_title = 'Send SMS By File Upload';
	include('header.php');
	include('checkAccess.php');
?>
		<div class="page-header">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->send_sms;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->send_sms_upload;?></li>
				</ol>
			</nav>
		</div>
		
		<?php 
			$x = GetLanguage("broadcast_sms",$lang);
			$x2 = GetLanguage("menu",$lang); 
			$x3 = GetLanguage("campaign_mgnt",$lang); 
			$x4 = GetLanguage("send_sms",$lang); 
			$x5 = GetLanguage("file_upload_status",$lang); 
		?>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div id="status" class="alert alert-dismissable alert-sm text-center hidden">
							
							<span id="output"></span>
							<button class="close">&times;</button>
						</div>
					
						<form id="broadcast_sms_form" name="broadcast_sms_form">
							
							<div class="row">
								<div class="col-lg-3"><label class="control-label"><?php echo $x3->campaign; ?></label></div>
								<div class="col-lg-6">
									<select name="campaign_id" id="campaign_id" required>
										<option value=""><?php echo $x3->no_campaign;?></option>
									</select>
								</div>
							</div>
							<hr/>
							
							<div class="row">
								<div class="col-lg-3"><label class="control-label"><?php echo $x4->send_mode; ?></label></div>
								<div class="col-lg-6">
									<input type="radio" id = "sendmode" name="sendmode" value="sms" checked>&nbsp;&nbsp;<?php echo $x4->send_mode_sms?> &nbsp;&nbsp;&nbsp;&nbsp;
									<!--<input type="radio" name="sendmode" value="both">&nbsp;&nbsp;<?php echo $x4->send_mode_sms?> &amp; <?php echo $x4->send_mode_email?>-->
									<input type="radio" id = "sendmode" name="sendmode" value="sms_mim">&nbsp;&nbsp;<?php echo $x4->send_mode_sms?> &amp; <?php echo $x4->send_mode_mim?>&nbsp;&nbsp;
									<input type="radio" id = "sendmode" name="sendmode" value="mim">&nbsp;&nbsp;<?php echo $x4->send_mode_mim?>
								</div>
							</div>
							<hr/>
							
							<div id = "callerid_div">
								<div class="row">
									<div class="col-lg-3"><label class="control-label"><?php echo $x3->callerid; ?></label></div>
									<div class="col-lg-6">
										<select name="callerid" id="callerid" required>
											<option value=""><?php echo $x3->no_callerid;?></option>
										</select>
									</div>
								</div>
								<hr/>
							</div>
							
							<div id = "mim_bot_div">
								<div class="row">
									<div class="col-lg-3"><label class="control-label"><?php echo $x5->mim_bot; ?></label></div>
									<div class="col-lg-6">
										<select name="bot_id" id="bot_id">
											<!--<option value=""><?php echo $x5->no_bot;?></option>-->
										</select>
									</div>
								</div>
								<hr/>
							</div>
							
							<div id = "mim_file_div">
								<div class="row">
									<div class="col-lg-3"><label class="control-label"><?php echo $x5->mim_file_type; ?></label></div>
									<div class="col-lg-6">
										<select name = "mim_file_type" id = "mim_file_type">
												<option value = "0"><?php echo $x5->no_file;?></option>
												<option value = "1"><?php echo $x5->mim_image; ?></option>
												<option value = "2"><?php echo $x5->mim_doc; ?></option>
										</select>
									</div>
								</div>
								<hr/>
							</div>
							
							<div id = "mim_image1_div">
								<div class="row">
									<div class="col-lg-3"><label class="control-label"><?php echo $x5->mim_image1; ?></label></div>
									<div class="col-lg-6">
										<input type="file" name="mim_image1" value="mim_image1" id = "mim_image1">
										<p class="help-block"><?php echo $x->mim_image1_txt;?></p>
									</div>
								</div>
								<hr/>
							</div>
						
							<!--
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><?php echo $x->file_format; ?></label>
								</div>
								<div class="col-lg-6">
									<select name="file_format" id="file_format">
										<option value="1"><?php echo $x->file_format_1; ?></option>
										<option value="2"><?php echo $x->file_format_2; ?></option>
									</select>
								</div>
							</div>
							<hr>
							-->
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><?php echo $x->char_set; ?></label>
								</div>
								<div class="col-lg-6">
									<select name="character_set" id="character_set">
										<option value="text"><?php echo $xml_common->ascii; ?></option>
										<option value="utf8"><?php echo $xml_common->utf8; ?></option>
									</select>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><?php echo $x->contents_upload; ?></label>
								</div>
								<div class="col-lg-6">
									<select name="content_type" id="content_type">
										<option value="1"><?php echo $x->contents_upload_1; ?></option>
										<option value="2"><?php echo $x->contents_upload_2; ?></option>
										<option value="3"><?php echo $x->contents_upload_3; ?></option>
									</select>
									<p class="help-block"><?php echo $x->numbers_only; ?></p>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><?php echo $x->specify_file;?></label>
								</div>
								<div class="col-lg-6">
									<input type="file" name="upload_file" id="upload_file" required>
									<p class="help-block"><?php echo $x->upload_file_txt;?></p>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><?php echo $x->schedulingsms; ?></label>
								</div>
								<div class="col-lg-6">
									<input type="checkbox" name="scheduled" id="scheduled" value="1">&nbsp;<?php echo $xml_common->yes; ?>
									<div id="divScheduled" class="hidden">
										<hr>
										<div class="row">
											<div class="col-lg-1">
												<?php echo $x->date;?>
											</div>
											<div class="col-lg-3">
												<p><input type="text" class="form-control input-sm" name="sms_date" id="sms_date" value="<?php echo strftime("%d-%m-%Y", time()); ?>"></p>
											</div>
										</div>
										<div class="row">
											<div class="col-lg-1">
												<?php echo $x->time; ?>
											</div>
											<div class="col-lg-3">
												<p><select name="sms_hour" id="sms_hour"><?php $sms_hour = strftime("%H", time());
													for($a=0; $a<24; $a++)
													{ ?>
													<option value="<?php echo $a;?>" <?php echo $a == $sms_hour ? "selected" : ""; ?>>
														<?php echo $a < 10 ? "0".$a : $a; ?>
													</option>
												<?php } ?>
												</select>
												&nbsp;
												<select name="sms_min" id="sms_min"><?php
													$sms_min = strftime("%M", time());
													for($b=0; $b<60; $b++)
													{?>
												<option value="<?php echo $b;?>" <?php echo $b == $sms_min ? "selected" : ""; ?>>
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
							<div class="row">
								<div class="col-lg-3">
									<label class="control-label"><?php echo $x->priority; ?></label>
								</div>
								<div class="col-lg-6">
									<select name="priority" id="priority">
										<option value="1">1 - Highest Priority</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5" selected>5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8">8</option>
										<option value="9">9 - Lowest Priorty</option>
									</select>
								</div>
							</div>
							<hr>
							<div class="row" id="smstext_div">
								<div class="col-lg-3" id="div1">
									<label class="control-label"><?php echo $x->enter_text;?></label>
									<p><input type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#getTemplate" data-templatetype="sms" value="<?php echo $x->select_msgtemplateforsms; ?>"></p>
									<p class="help-block"><i><?php echo $x->mail_merge;?></i></p>
								</div>
								<div class="col-lg-6" id="div2">
									<p><textarea class="form-control input-sm" name="smstext" id="smstext" rows="5" required></textarea></p>
									<?php echo $x->characters; ?>&nbsp;<strong><span id="count_chars">0</span></strong><br>
									<?php echo $x->msgcounts; ?>&nbsp;<strong><span id="sms_num">0 / <?php echo $_SESSION['max_sms']; ?></span></strong>
								</div>
								
								<hr>
							</div>
							
							<hr>
							<div class="row" id="mimtext_div">
								<div class="col-lg-3" id="div3">
									<label class="control-label"><?php echo $x->enter_text;?></label>
									<p><input type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#getTemplate" data-templatetype="mim" value="<?php echo $x->select_msgtemplateformim; ?>"></p>
									<p><input type="button" class="btn btn-secondary btn-sm" role="button" value="Clear Template" id="clear_tpl"></p>
									<p class="help-block"><i><?php echo $x->mail_merge;?></i></p>
									
								</div>
								<div class="col-lg-6" id="div4">
									<p><textarea class="form-control input-sm" name="mimtext" id="mimtext" rows="5"></textarea></p>
									<?php echo $x->characters; ?>&nbsp;<strong><span id="count_charsmim">0</span></strong>
								</div>
								
								<hr>
							</div>

							<div id = "tpl_params_div">
								<div class="row">
									<div class="col-lg-3">
										<label class="control-label"><?php echo $x5->tpl_params; ?></label>
									</div>
									<div class="col-lg-6" id = "tpl_params_here">
										
									</div>
								</div>
								<hr>
							</div>
						
							<div class="row text-center">
								<input type="hidden" name="file_format" id="file_format" value = "1">
								<input type="hidden" name="sendtype" id="sendtype">
								<input type="hidden" id="tpl_params_total" value="">
								<input type="hidden" name="mim_tpl" id="mim_tpl"<?php // yes/no ?>>
								<input type="hidden" name="mim_tpl_id" id="mim_tpl_id">
								
								<input type="hidden" name="max_length" id="max_length">
								<!--<input type="hidden" name="mode" value="insertBroadCast">-->
								<input type="hidden" name="mode" value="UploadFile">
								<button type="submit" class="btn btn-primary"><?php echo $xml_common->upload;?></button>
								<button type="reset" class="btn btn-default" id = "reset_btn"><?php echo $xml_common->reset;?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php $x1 = Getlanguage("upload_broadcast_sms",$lang); ?>
		<div class="modal fade" id="preview" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">							
						</button>
						<h5 class="modal-title"><?php echo $x1->title;?></h5>
					</div>
					<div class="modal-body">
						<table class="table table-striped table-bordered table-condensed" id="tblpreview">
							<thead>
								<tr>
									<th><?php echo $xml_common->no;?></th>
									<th><?php echo $x1->mobile_number;?></th>
									<th><?php echo $x1->sms_text;?></th>
									<th><?php echo $x1->file_format;?></th>
									<th><?php echo $x1->priority;?></th>
									<th><?php echo $x1->schedule_time;?></th>
								</tr>
							</thead>
						</table>
					</div>
					<div class="modal-footer">
						<input type="button" class="btn btn-primary btn-sm" value="<?php echo $x1->send;?>" id="send">
						<input type="button" class="btn btn-default btn-sm" value="<?php echo $xml_common->cancel;?>" id="cancel">
					</div>
				</div>
			</div>
		</div>
		<?php $x2A = Getlanguage("get_template",$lang); ?>
		<div class="modal fade" id="getTemplate" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-xl" role="document">
				<div class="modal-content">
					<div class="modal-header">
						
						<h5 class="modal-title"><?php echo $x2A->title; ?></h5>
						
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						
					</div>
					<div class="modal-body">
						<ul class="nav nav-tabs" id="broadcastSMSTab" role="tablist">
							<li class="nav-item" id = "msg_template_li" role="presentation">
								<button class="nav-link active tab_button2" id="msg_template_a" data-bs-toggle="tab" data-bs-target="#template" type="button" role="tab" aria-controls="template" aria-selected="true"><?php echo $x2A->msg_template; ?></button>														
							</li>
							<li class="nav-item" id = "global_template_li" role="presentation">
								<button class="nav-link tab_button2" id="global_msg_template_a" data-bs-toggle="tab" data-bs-target="#global_template" type="button" role="tab" aria-controls="global_template" aria-selected="true"><?php echo $x2A->global_msg_template; ?></button>								
							</li>
							<li class="nav-item" id = "mim_template_li" role="presentation">
								<button class="nav-link tab_button2" id="mim_template_a" data-bs-toggle="tab" data-bs-target="#mim_template" type="button" role="tab" aria-controls="mim_template" aria-selected="true"><?php echo $x2A->mim_msg_template; ?></button>								
							</li>
							<li class="nav-item" id = "global_mim_template_li" role="presentation">
								<button class="nav-link tab_button2" id="mim_template_b" data-bs-toggle="tab" data-bs-target="#global_mim_template" type="button" role="tab" aria-controls="global_mim_template" aria-selected="true"><?php echo $x2A->global_mim_msg_template; ?></button>								
							</li>
						</ul>
						<div id="myTabContent" class="tab-content" id="broadcastSMSTabContent" style="overflow:visible;">
							<br>
							<div class="tab-pane fade show active" id="template" role="tabpanel" aria-labelledby="msg_template_a">
								<div class="row">
									<div class="col-lg-10">
										<?php echo $x2A->select_msgtemplate;?><hr>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-10">
										<select class="form-select input-sm" id="message_text" name="templateText"></select>
									</div>
								</div>
							</div>
							<div class="tab-pane fade" id="global_template" role="tabpanel" aria-labelledby="global_msg_template_a">
								<div class="row">
									<div class="col-lg-10">
										<?php echo $x2A->select_msgtemplate;?><hr>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-10">
										<select class="form-select input-sm" id="global_message_text" name="templateText"></select>
									</div>
								</div>
							</div>
							<div class="tab-pane fade" id="mim_template" role="tabpanel" aria-labelledby="mim_template_a">
								<div class="row">
									<div class="col-lg-10">
										<?php echo $x2A->select_msgtemplate;?><hr>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-10">
										<select class="form-select input-sm" id="mim_message_text" name="templateText"></select>
									</div>
								</div>
							</div>
							<div class="tab-pane fade" id="global_mim_template" role="tabpanel" aria-labelledby="mim_template_b">
								<div class="row">
									<div class="col-lg-10">
										<?php echo $x2A->select_msgtemplate;?><hr>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-10">
										<select class="form-select input-sm" id="global_mim_message_text" name="templateText"></select>
									</div>
								</div>
							</div>
							
						</div>
						
						<div class="tab-content" id = "tpl_info_1_div" style = "padding-top:10px;">
							<textarea name = "tpl_info_1" id = "tpl_info_1" class = "form-control input-sm" style = "width:100%;height:50px;resize: none;border: none;" readonly></textarea>
							<input type="hidden" id="tpl_mode" value="sms">
						</div>
						
					</div>
					<div class="modal-footer">
						<input type="button" id="selectTemplate" class="btn btn-primary btn-sm" value="<?php echo $xml_common->select ?>">
						<input type="button" class="btn btn-default btn-sm" value="<?php echo $xml_common->cancel ?>" data-bs-dismiss="modal">
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php');?>
	</div>
	<?php include('broadcast_sms_js.php');?>
</body>
</html>
