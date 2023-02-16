<?php
	$page_mode = '7';
	$page_title = 'Send SMS';
	include('header.php');
	include('checkAccess.php');
?>
	<link href="css/style1.css" rel="stylesheet">

		<!-- <div class="page-header">
			<ol class="breadcrumb">
				<li><?php echo $xml->send_sms;?></li>
				<li class="active"><?php echo $xml->send_sms;?></li>
			</ol>
		</div> -->
		<div class="page-header">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo "Send Message"?></li>
    				<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->send_sms?></li>
				</ol>
			</nav>
		</div>
		<?php $x = GetLanguage("send_sms",$lang); 
				$x2 = GetLanguage("menu",$lang); 
				$x3 = GetLanguage("campaign_mgnt",$lang); 
				$x4 = GetLanguage("file_upload_status",$lang);
			  //$x->send_mode="Send Mode"; 
			  //$x->email_1 = "Email Address"; 
			  //$x->email_2 = "Email Subject";
			  //$x->email_3 = "Email From Address";
			
		?>
		
		<div class="page-content">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-body">
						
						<div id="status" class="alert alert-dismissable alert-sm text-center hidden">
							
							<span id="output"></span>
							<button class="btn-close" aria-label="close" id="btn_status_close"></button>
						</div>
						
						<form id="sendForm" name="sendForm" enctype="multipart/form-data">
							
							<div class="row">
								<div class="col-md-4"><label class="control-label"><?php echo $x3->campaign; ?></label></div>
								<div class="col-md-4">
									<select name="campaign_id" id="campaign_id" required>
										<option value=""><?php echo $x3->no_campaign;?></option>
									</select>
								</div>
							</div>
							<hr/>
							<div class="row">
								<div class="col-md-4"><label class="control-label"><?php echo $x->send_mode; ?></label></div>
								<div class="col-md-4">
									<input type="radio" name="sendmode" value="sms" checked>&nbsp;&nbsp;<?php echo $x->send_mode_sms?> &nbsp;&nbsp;&nbsp;&nbsp;
									<!--<input type="radio" name="sendmode" value="both">&nbsp;&nbsp;<?php echo $x->send_mode_sms?> &amp; <?php echo $x->send_mode_email?>-->
									<input type="radio" name="sendmode" value="sms_mim">&nbsp;&nbsp;<?php echo $x->send_mode_sms?> &amp; <?php echo $x->send_mode_mim?>&nbsp;&nbsp;
									<input type="radio" name="sendmode" value="mim">&nbsp;&nbsp;<?php echo $x->send_mode_mim?>
								</div>
							</div>
							<hr/>
							
							<div id = "callerid_div">
							
								<div class="row">
									<div class="col-md-4"><label class="control-label"><?php echo $x3->callerid; ?></label></div>
									<div class="col-md-4">
										<select name="callerid" id="callerid" required>
											<option value=""><?php echo $x3->no_callerid;?></option>
										</select>
									</div>
								</div>
								<hr/>
							</div>
							
							<div id = "mim_bot_div">
								<div class="row">
									<div class="col-md-4"><label class="control-label"><?php echo $x4->mim_bot; ?></label></div>
									<div class="col-md-4">
										<select name="bot_id" id="bot_id">
											<!--<option value=""><?php echo $x4->no_bot;?></option>-->
										</select>
									</div>
								</div>
								<hr/>
							</div>
							
							<div id = "mim_file_div">
								<div class="row">
									<div class="col-md-4"><label class="control-label"><?php echo $x4->mim_file_type; ?></label></div>
									<div class="col-md-4">
										<select name = "mim_file_type" id = "mim_file_type">
												<option value = "0"><?php echo $x4->no_file;?></option>
												<option value = "1"><?php echo $x4->mim_image; ?></option>
												<option value = "2"><?php echo $x4->mim_doc; ?></option>
										</select>
									</div>
								</div>
								<hr/>
							</div>
							
							<div id = "mim_image1_div">
								<div class="row">
									<div class="col-md-4"><label class="control-label"><?php echo $x4->mim_image1; ?></label></div>
									<div class="col-md-4">
										<input type="file" name="mim_image1" value="mim_image1" id = "mim_image1">
									</div>
								</div>
								<hr/>
							</div>
							
							<div class="row">
								<div class="col-md-4">
									<label class="control-label"><?php echo $x->mno_1; ?> <span class = "contact_cls">*</span></label>
									<p><input type="button" class="btn btn-primary btn-sm openadrbook" data-bs-toggle="modal" data-bs-target="#getContact" data-type="mobile" value="<?php echo $x->select_addressbook; ?>"></p>
								</div>
								<div class="col-md-4">
									<textarea class="form-control input-sm" name="mobile" id="mobile" rows="5" required></textarea>
								</div>
								<div class="col-md-4 help-block">
									<i><?php echo $x->mno_2; ?></i>
									<br>
									<i><?php echo $x->mno_3; ?>
									<br><?php echo $x->mno_4; ?></i>
								</div>
							</div>
						
							<div id="email-block" class = "msgstatusbar">
							<hr>
						
							<div class="row" >
								<div class="col-md-4">
									<label class="control-label"><?php echo $x->email_2; ?> </label>
									<br/>&nbsp;
								</div>
								<div class="col-md-4">
									<input class="form-control input-sm" name="eml_subj" id="eml_subj" placeholder="<?php echo $x->subject?>">
								</div>
							</div>
							<div class="row" >
								<div class="col-md-4">
									<label class="control-label"><?php echo $x->email_3; ?> </label>
									<br/>&nbsp;
								</div>
								<div class="col-md-4">
									<input class="form-control input-sm" name="eml_fr" id="eml_fr" placeholder="from@email.com">
								</div>
							</div>
							<div class="row" >
								<div class="col-md-4">
									<label class="control-label"><?php echo $x->email_1; ?> </label>
									<p>
										<input type="button" class="btn btn-primary btn-sm openadrbook" data-bs-toggle="modal" data-type="email" value="<?php echo $x->select_addressbook; ?>">
										<!--<input type="button" class="btn btn-primary btn-sm openadrbook" data-toggle="modal" data-type="mobile" value="<?php echo $x->select_addressbook; ?>">-->
									</p>
								</div>
								<div class="col-md-4">
									<textarea class="form-control input-sm" name="email" id="email" rows="5" placeholder="to@email.com"></textarea>
								</div>
								<div class="col-md-4 help-block">
									<i><?php echo $x->mno_2; ?></i>
									<br>
									
								</div>
							</div>
						
						</div>
						<hr>
						<div class="row">
							<div class="col-md-4">
								<label class="control-label"><?php echo $x->char_set; ?></label>
							</div>
							<div class="col-md-4">
								<select name="charset" id="charset">
									<option value="text"><?php echo $xml_common->ascii; ?></option>
									<option value="utf8"><?php echo $xml_common->utf8; ?></option>
								</select>
							</div>
						</div>
						<hr>
						<div id="msg_sms">
							<div class="row">
								<div class="col-md-4">
									<label class="control-label"><?php echo $x->message; ?> <span class = "contact_cls">*</span></label>
									<p><input type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#getTemplate" data-templatetype="sms" value="<?php echo $x->select_msgtemplateforsms; ?>"></p>
								</div>
								<div class="col-md-4">
									<p><textarea class="form-control input-sm" name="smstext" id="smstext" rows="5"></textarea></p>
									
									<span id="mim_tpl_id_show_here"></span><br>
									<?php echo $x->characters; ?>&nbsp;<strong><span id="count_chars2">0</span></strong><br>
									<?php echo $x->msgcounts; ?>&nbsp;<strong><span id="sms_num">0 / <?php echo $_SESSION['max_sms']; ?></span></strong>
								</div>
							</div>
							<hr>
						</div>
						<div id="msg_mim">
							<div class="row">
								<div class="col-md-4">
									<label class="control-label"><?php echo $x->message; ?> <span class = "contact_cls">*</span></label>
									<p>
										<input type="button" class="btn btn-primary btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#getTemplate" data-templatetype="mim" value="<?php echo $x->select_msgtemplateformim; ?>"><br>
										<input type="button" class="btn btn-secondary btn-sm mb-1" value="<?php echo $x->remove_msgtemplate; ?>" id="btn_clear_tpl"></p>
								</div>
								<div class="col-md-4">
									<p><textarea class="form-control input-sm" name="mimtext" id="mimtext" rows="5"></textarea></p>
									
									<span id="mim_tpl_id_show_here"></span><br>
									<?php echo $x->characters; ?>&nbsp;<strong><span id="count_charsmim2">0</span></strong><br>
								</div>
							</div>
							<hr>
						</div>
						<div id = "tpl_params_div">
							<div class="row">
								<div class="col-md-4">
									<label class="control-label"><?php echo $x4->tpl_params; ?></label>
								</div>
								<div class="col-md-4" id = "tpl_params_here">
									xxx
								</div>
							</div>
							<hr>
						</div>
						
						<div class="row">
							<div class="col-md-4">
								<label class="control-label"><?php echo $x->prioritysms; ?></label>
							</div>
							<div class="col-md-4">
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
						<div class="row">
							<div class="col-md-4">
								<label class="control-label"><?php echo $x->schedulingsms; ?></label>
							</div>
							<div class="col-md-4">
								<input type="checkbox" name="scheduled" id="scheduled" value="1">&nbsp;<?php echo $xml_common->yes; ?>
								<div id="divScheduled" class="hidden">
									<hr>
									<div class="row">
										<div class="col-md-2">
											<?php echo $x->date;?>
										</div>
										<div class="col-md-5">
											<p><input type="text" class="form-control input-sm" name="sms_date" id="sms_date" value="<?php echo strftime("%d-%m-%Y", time()); ?>"></p>
										</div>
									</div>
									<div class="row">
										<div class="col-md-2">
											<?php echo $x->time; ?>
										</div>
										<div class="col-md-5">
											<p><select name="sms_hour" id="sms_hour">
											<?php
											$sms_hour = strftime("%H", time());
												for($a=0; $a<24; $a++)
												{
											?>
												<option value="<?php echo $a;?>" <?php echo $a == $sms_hour ? "selected" : ""; ?>>
													<?php echo $a < 10 ? "0".$a : $a; ?>
												</option>
											<?php } ?>
											</select>
											&nbsp;
											<select name="sms_min" id="sms_min">
											<?php
												$sms_min = strftime("%M", time());
												for($b=0; $b<60; $b++)
												{
											?>
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
							<div class="col text-center">
							<input type="hidden" id="tpl_params_total" value="">
							<input type="hidden" id="tpl_type" value="">
							<input type="hidden" id="tpl_id" value="">
							<!--<input type="hidden" name="mode" id="mode" value="sendSMS">-->
							<input type="hidden" name="mode" id="mode" value="sendSMS_v2">
							<input type="hidden" name="max_length" id="max_length">
							<input type="hidden" name="count_chars" id="count_chars">
							<input type="hidden" name="count_charsmim" id="count_charsmim">
							<button id="submit" type="submit" class="btn btn-primary"><?php echo $x->send;?></button>
							<button id="clear" type="reset" class="btn btn-light"><?php echo $xml_common->reset;?></button>
							</div>
						</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php $x1 = Getlanguage("get_contacts",$lang); //$x1->email='Email' ?>
		<div class="modal fade" id="getContact" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						
						<h5 class="modal-title"><?php echo $x1->title_addressbook; ?></h5>
						
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">							
						</button>
						
					</div>
					<div class="modal-body">
						<ul class="nav nav-pills" id="pills-tab" role="tablist">
							<li class="nav-item" id="li1" role="presentation">
								<button class="nav-link active tab_button" id="tab1" data-bs-toggle="pill" data-bs-target="#contacts" type="button" role="tab" aria-controls="contacts" aria-selected="true"><?php echo $x1->contacts; ?></button>								
							</li>
							<li class="nav-item" id="li2">
								<button class="nav-link tab_button" id="tab2" data-bs-toggle="pill" data-bs-target="#groups" type="button" role="tab" aria-controls="groups" aria-selected="false"><?php echo $x1->group; ?></button>
							</li>
							<li class="nav-item" id="li3">
								<button class="nav-link tab_button" id="tab3" data-bs-toggle="pill" data-bs-target="#global_contacts" type="button" role="tab" aria-controls="global_contacts" aria-selected="false"><?php echo $x1->global_contacts; ?></button>								
							</li>
							<li class="nav-item" id="li4">
								<button class="nav-link tab_button" id="tab4" data-bs-toggle="pill" data-bs-target="#global_groups" type="button" role="tab" aria-controls="global_groups" aria-selected="false"><?php echo $x1->global_group; ?></button>																
							</li>
						</ul>
						<div class="tab-content tab-content_cls" id="pills-tabContent">
							<br>
							<div class="tab-pane fade show active" id="contacts" role="tabpanel" aria-labelledby="pills-contacts-tab">
								<table class="table table-striped table-bordered table-sm" id="tblcontact">
									<thead>
										<tr>
											<th><?php echo $xml_common->no;?></th>
											<th><?php echo $x1->contact_name;?></th>
											<th><?php echo $x1->mobile_number; ?></th>
											<th><?php echo $x1->email; ?></th>
											<th><input type="checkbox" id="c1"></th>
										</tr>
									</thead>
								</table>
							</div>
							<div class="tab-pane fade" id="groups" role="tabpanel" aria-labelledby="pills-groups-tab">
								<table class="table table-striped table-bordered table-sm" id="tblgroup">
									<thead>
										<tr>
											<th><?php echo $xml_common->no;?></th>
											<th><?php echo $x1->group_name;?></th>
											<th><?php echo $x1->contacts_name;?></th>
											<th><input type="checkbox" id="c2"></th>
										</tr>
									</thead>
								</table>
							</div>
							<div class="tab-pane fade" id="global_contacts" role="tabpanel" aria-labelledby="pills-gcontacts-tab">
								<table class="table table-striped table-bordered table-sm" id="tblglobal_contact">
									<thead>
										
										<tr id = "tblglobal_contact_choosetype">
											<th colspan="6">
												<input type="radio" name="choosetype2" id = "choosetype_1" value="both" checked>
													<span id = "choosetype_C"></span>
													<?php //echo $x1->choosetype_1; ?> 
												<input type="radio" name="choosetype2" id = "choosetype_2" value="single">
													<span id = "choosetype_D"></span>
													<?php //echo $x1->choosetype_2; ?>
											</th>
										</tr>
										
										<tr>
											<th><?php echo $xml_common->no;?></th>
											<th><?php echo $x1->contact_name;?></th>
											<th><?php echo $x1->mobile_number; ?></th>
											<th><?php echo $x1->email; ?></th>
											<th><?php echo $x1->department; ?></th>
											<th><input type="checkbox" id="c3"></th>
										</tr>
									</thead>
								</table>
							</div>
							<div class="tab-pane fade" id="global_groups" role="tabpanel" aria-labelledby="pills-ggroups-tab">
								<table class="table table-striped table-bordered table-condensed" id="tblglobal_group">
									<thead>
										<tr id = "tblglobal_group_choosetype">
											<th colspan="5">
												<input type="radio" name="choosetype" id = "choosetype_3" value="both" checked>
													<span id = "choosetype_A"></span>
													<?php //echo $x1->choosetype_1; ?> 
												<input type="radio" name="choosetype" id = "choosetype_4" value="single">
													<span id = "choosetype_B"></span>
													<?php //echo $x1->choosetype_2; ?>
											</th>
										</tr>
										<tr>
											<th><?php echo $xml_common->no;?></th>
											<th><?php echo $x1->group_name;?></th>
											<th><?php echo $x1->contacts_name;?></th>
											<th><?php echo $x1->department; ?></th>
											<th><input type="checkbox" id="c4"></th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" id="modalclick" name="modalclick">
						<input type="hidden" id="tab_id" name="tab_id">
						<input type="button" id="s_cont" class="btn btn-primary btn-sm" value="<?php echo $xml_common->select;?>">
						<input type="button" class="btn btn-secondary btn-sm" value="<?php echo $xml_common->cancel;?>" data-bs-dismiss="modal">
					</div>
				</div>
			</div>
		</div>

		<?php $x2A = Getlanguage("get_template",$lang);?>
		<div class="modal fade" id="getTemplate" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-xl" role="document">
				<div class="modal-content">
					<div class="modal-header">
						
						<h5 class="modal-title"><?php echo $x2A->title;?></h5>
						
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">							
						</button>
						
					</div>
					<div class="modal-body">
						
						<ul class="nav nav-tabs" id="sendSMSTab" role="tablist">
							<li class="nav-item" id = "msg_template_li" role="presentation">
								<button class="nav-link active tab_button2" id="msg_template" data-bs-toggle="tab" data-bs-target="#template" type="button" role="tab" aria-controls="template" aria-selected="true"><?php echo $x2A->msg_template; ?></button>								
							</li>
							<li class="nav-item" id = "global_msg_template_li" role="presentation">
								<button class="nav-link tab_button2" id="global_msg_template" data-bs-toggle="tab" data-bs-target="#global_template" type="button" role="tab" aria-controls="global_template" aria-selected="true"><?php echo $x2A->global_msg_template; ?></button>								
							</li>
							<li class="nav-item" id = "mim_template_li" role="presentation">
								<button class="nav-link tab_button2" id="mim_msg_template" data-bs-toggle="tab" data-bs-target="#mim_template" type="button" role="tab" aria-controls="mim_template" aria-selected="true"><?php echo $x2A->mim_msg_template; ?></button>								
							</li>
							<li class="nav-item" id = "global_mim_template_li" role="presentation">
								<button class="nav-link tab_button2" id="global_mim_msg_template" data-bs-toggle="tab" data-bs-target="#global_mim_template" type="button" role="tab" aria-controls="global_mim_template" aria-selected="true"><?php echo $x2A->global_mim_msg_template; ?></button>								
							</li>
						</ul>
											
						<div id="myTabContent" class="tab-content tab-content_cls">
							<br>
							<div class="tab-pane fade show active" id="template" role="tabpanel" aria-labelledby="msg_template">
								<div class="row">
									<div class="col-md-10">
										<?php echo $x2A->select_msgtemplate;?><hr>
									</div>
								</div>
								<div class="row">
									<div class="col-md-10">
										<select class="form-select form-select-sm" id="message_text" name="template_text"></select>
									</div>
								</div>
							</div>
							<div class="tab-pane fade" id="global_template" role="tabpanel" aria-labelledby="global_msg_template">
								<div class="row">
									<div class="col-md-10">
										<?php echo $x2A->select_msgtemplate;?><hr>
									</div>
								</div>
								<div class="row">
									<div class="col-md-10">
										<select class="form-select form-select-sm" id="global_message_text" name="template_text"></select>
									</div>
								</div>
							</div>
							<div class="tab-pane fade" id="mim_template" role="tabpanel" aria-labelledby="mim_msg_template">
								<div class="row">
									<div class="col-md-10">
										<?php echo $x2A->select_msgtemplate2;?><hr>
									</div>
								</div>
								<div class="row">
									<div class="col-md-10">
										<select class="form-select form-select-sm" id="mim_message_text" name="template_text"></select>
									</div>
								</div>
							</div>
							
							<div class="tab-pane fade" id="global_mim_template" role="tabpanel" aria-labelledby="global_mim_msg_template">
								<div class="row">
									<div class="col-md-10">
										<?php echo $x2A->select_msgtemplate2;?><hr>
									</div>
								</div>
								<div class="row">
									<div class="col-md-10">
										<select class="form-select form-select-sm" id="global_mim_message_text" name="template_text"></select>
									</div>
								</div>
							</div>
							
						</div>
						
						<div class="tab-content tab-content2" id = "tpl_info_1_div">
							<textarea name = "tpl_info_1" id = "tpl_info_1" class = "form-control input-sm tpl_info_1_cls" readonly></textarea>
						</div>
						
					</div>
					<div class="modal-footer">
						<input type="button" id="s_temp" class="btn btn-primary btn-sm" value="<?php echo $xml_common->select ?>">
						<input type="button" class="btn btn-secondary btn-sm" value="<?php echo $xml_common->cancel ?>" data-bs-dismiss="modal">
					</div>
				</div>
			</div>
		</div>

		<?php include('footnote.php'); ?>
	</div>
	<?php //include('send_sms_js.php'); ?>

	<script src="js/autosize.min.js"></script>
	<script src="js/bootstrap-datepicker.min.js"></script>
	<script src="send_sms_js.php"></script>
</body>
</html>
