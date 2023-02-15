<?php
	$page_mode = '45';
	$page_title = 'SMS Time Configuration';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("sysconfig",$lang);
	
	function getSelOption_HH($elem_id,$elem_name){
		$html = "<select id=\"$elem_id\" name=\"$elem_name\">";
		for($i=0; $i<=23; $i++){
			$html .= "<option value=\"$i\">".sprintf("%02d",$i)."</option>";
		}
		$html .= "</select>";
		return $html;
	}
	function getSelOption_MM($elem_id,$elem_name){
		$html = "<select id=\"$elem_id\" name=\"$elem_name\">";
		for($i=0; $i<=59; $i++){
			$html .= "<option value=\"$i\">".sprintf("%02d",$i)."</option>";
		}
		$html .= "</select>";
		return $html;
	}
?>
		<div class="page-header">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->system_config;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->time_config;?></li>
				</ol>
			</nav>
		</div>

		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div id="status" class="alert alert-info alert-dismissable alert-sm text-center hidden">
							<button class="close">&times;</button>
							<span><?php echo $x->alert_11;?></span>
						</div>
						<form id="timeCfg" name="timeCfg">
						<table class="table table-striped table-bordered table-condensed">
							<thead>
							<tr>
								<th class="text-left" colspan="6">&nbsp;<?php echo $x->enable;?>&emsp;<input type="checkbox" name="webapp_sms" value="1"></th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td><?php echo $x->mon;?></td>
								<td><input type="checkbox" name="monday_cb" value="Y"></td>
								<td><?php echo $x->start;?></td>
								<td>
									<?php echo getSelOption_HH('mon_start_hour','mon_start_hour');?>
									<?php echo getSelOption_MM('mon_start_min','mon_start_min');?>
								</td>
								<td><?php echo $x->end;?></td>
								<td>
									<?php echo getSelOption_HH('mon_end_hour','mon_end_hour');?>
									<?php echo getSelOption_MM('mon_end_min','mon_end_min');?>
								</td>
							</tr>
							<tr>
								<td><?php echo $x->tue;?></td>
								<td><input type="checkbox" name="tuesday_cb" value="Y"></td>
								<td><?php echo $x->start;?></td>
								<td>
									<?php echo getSelOption_HH('tue_start_hour','tue_start_hour');?>
									<?php echo getSelOption_MM('tue_start_min','tue_start_min');?>
								</td>
								<td><?php echo $x->end;?></td>
								<td>
									<?php echo getSelOption_HH('tue_end_hour','tue_end_hour');?>
									<?php echo getSelOption_MM('tue_end_min','tue_end_min');?>
								</td>
							</tr>
							<tr>
								<td><?php echo $x->wed;?></td>
								<td><input type="checkbox" name="wed_cb" value="Y"></td>
								<td><?php echo $x->start;?></td>
								<td>
									<?php echo getSelOption_HH('wed_start_hour','wed_start_hour');?>
									<?php echo getSelOption_MM('wed_start_min','wed_start_min');?>
								</td>
								<td><?php echo $x->end;?></td>
								<td>
									<?php echo getSelOption_HH('wed_end_hour','wed_end_hour');?>
									<?php echo getSelOption_MM('wed_end_min','wed_end_min');?>
								</td>
							</tr>
							<tr>
								<td><?php echo $x->thu;?></td>
								<td><input type="checkbox" name="thurs_cb" value="Y"></td>
								<td><?php echo $x->start;?></td>
								<td>
									<?php echo getSelOption_HH('thu_start_hour','thu_start_hour');?>
									<?php echo getSelOption_MM('thu_start_min','thu_start_min');?>
								</td>
								<td><?php echo $x->end;?></td>
								<td>
									<?php echo getSelOption_HH('thu_end_hour','thu_end_hour');?>
									<?php echo getSelOption_MM('thu_end_min','thu_end_min');?>
								</td>
							</tr>
							<tr>
								<td><?php echo $x->fri;?></td>
								<td><input type="checkbox" name="fri_cb" value="Y"></td>
								<td><?php echo $x->start;?></td>
								<td>
									<?php echo getSelOption_HH('fri_start_hour','fri_start_hour');?>
									<?php echo getSelOption_MM('fri_start_min','fri_start_min');?>
								</td>
								<td><?php echo $x->end;?></td>
								<td>
									<?php echo getSelOption_HH('fri_end_hour','fri_end_hour');?>
									<?php echo getSelOption_MM('fri_end_min','fri_end_min');?>
								</td>
							</tr>
							<tr>
								<td><?php echo $x->sat;?></td>
								<td><input type="checkbox" name="sat_cb" value="Y"></td>
								<td><?php echo $x->start;?></td>
								<td>
									<?php echo getSelOption_HH('sat_start_hour','sat_start_hour');?>
									<?php echo getSelOption_MM('sat_start_min','sat_start_min');?>
								</td>
								<td><?php echo $x->end;?></td>
								<td>
									<?php echo getSelOption_HH('sat_end_hour','sat_end_hour');?>
									<?php echo getSelOption_MM('sat_end_min','sat_end_min');?>
								</td>
							</tr>
							<tr>
								<td><?php echo $x->sun;?></td>
								<td><input type="checkbox" name="sun_cb" value="Y"></td>
								<td><?php echo $x->start;?></td>
								<td>
									<?php echo getSelOption_HH('sun_start_hour','sun_start_hour');?>
									<?php echo getSelOption_MM('sun_start_min','sun_start_min');?>
								</td>
								<td><?php echo $x->end;?></td>
								<td>
									<?php echo getSelOption_HH('sun_end_hour','sun_end_hour');?>
									<?php echo getSelOption_MM('sun_end_min','sun_end_min');?>
								</td>
							</tr>
							</tbody>
						</table>
						<div class="well well-sm">
							<?php echo $x->sysconfig_desc;?>  
						</div>
						<div class="text-center">
							<input type="hidden" name="mode" value="saveSMSTime">
							<input class="btn btn-primary" type="submit" value="<?php echo $xml_common->save;?>">
							<input class="btn btn-default" type="reset" value="<?php echo $xml_common->reset;?>">
						</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php');?>
	</div>
	<script src="sysconfig_js.php"></script>
</body>
</html>
