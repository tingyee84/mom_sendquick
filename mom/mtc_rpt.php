	<?php
		$page_title = "Maintenance & Report";
		include('header.php');
		
		$x = GetLanguage("maintenance_report",$lang);
	?>
		<div style="height:40px">
			<div class="page-header">
				<ol class="breadcrumb">
					<li><?php echo $xml->logs_mgnt;?></li>
					<li class="active"><?php echo $xml->maintenance_report;?></li>
				</ol>
			</div>
		</div>
		<!--?php $x = GetLanguage("RPTAPP",$lang);?-->
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<ul class="nav nav-pills">
							<li class="active"><a href="#log" data-toggle="tab"><b><?php echo $x->log_maintenance;?></b></a></li>
							<li><a href="#usage" data-toggle="tab"><b><?php echo $x->usage_report;?></b></a></li>
						</ul>
						<div class="tab-content" style="overflow:visible;">
							<p></p><hr><p></p>
							<div id="updated" class="alert alert-info alert-dismissable alert-sm text-center hidden">
								<button class="close">&times;</button>
								<span><?php echo $x->conversation_report_updated; ?></span>
							</div>
							<div class="tab-pane fade in active" id="log">
								<form id="logform" name="logform">
								<div class="row">
									<div class="col-lg-3">
										<label><?php echo $x->conversation_log_maintenance;?></label>
									</div>
									<div class="col-lg-4">
										<table class="table-condensed">
										<tr>
											<td><?php echo $x->delete_chat_history_after;?></td>
											<td><input class="form-control input-sm" type="text" id="keep_chat" name="keep_chat" size="2" maxlength="3" pattern="\d+" required></td>
											<td><?php echo $x->days;?></td>
										</tr>
										</table>
									</div>
									<div class="col-lg-5 help-block">
										<?php echo $x->descr1;;?>
									</div>
								</div>
								<hr>
								<div class="row text-center">
									<input type="hidden" name="mode" value="updateLog"/>
									<button type="submit" class="btn btn-primary"><?php echo $xml_common->save?></button>
									<button type="reset" class="btn btn-default"><?php echo $xml_common->reset?></button>
								</div>
								</form>
							</div>
							<div class="tab-pane fade" id="usage">
								<form id="usageform" name="usageform">
								<div class="row">
									<div class="col-lg-2">
										<label><?php echo $x->report_schedule;?></label>
									</div>
									<div class="col-lg-5">
										<p><?php echo $x->type;?>:&nbsp;
										<select id="schedule_opt" name="schedule_opt">
											<option value="Disable"><?php echo $xml_common->disable;?></option>
											<option value="Daily"><?php echo $xml_common->daily;?></option>
											<option value="Weekly"><?php echo $xml_common->weekly;?></option>
											<option value="Monthly"><?php echo $xml_common->monthly;?></option>
										</select></p>
										<p><?php echo $x->time_name;?>:&nbsp;
										<select id="schedule_tm" name="schedule_tm">
											<?php for($i=0;$i<24;$i++){ if($i<10){ echo "<option>0".$i."00</option>"; } else{ echo "<option>".$i."00</option>"; } } ?>
										</select></p>
									</div>
									<div class="col-lg-5 help-block">
										<?php echo $x->descr2;?>
										<ul>
											<li><?php echo $x->descr3;?></li>
											<li><?php echo $x->descr4;?></li>
											<li><?php echo $x->descr5;?></li>
											<li><?php echo $x->descr6;?></li>
										</ul>
									</div>
								</div>
								<hr>
								<div class="row">
									<div class="col-lg-2">
										<label><?php echo $x->email_usage_report;?></label>
									</div>
									<div class="col-lg-5">
										<textarea class="form-control input-sm" id="email_report" name="email_report" rows="3"></textarea>
									</div>
									<div class="col-lg-5 help-block">
										<?php echo $x->descr7;?>
									</div>
								</div>
								<hr>
								<div class="row text-center">
									<input type="hidden" name="mode" value="updateUsg"/>
									<button type="submit" class="btn btn-primary"><?php echo $xml_common->save;?></button>
									<button type="reset" class="btn btn-default"><?php echo $xml_common->reset;?></button>
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
		<?php include('mtc_rpt_js.php') ?>
</body>
</html>
