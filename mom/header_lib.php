<?php
require_once('lib/commonFunc.php');

switch ($_POST['mode']) {
	case "getmenu":
		$xml = GetLanguage("menu",$lang);
		error_log("access string: " . trim($_SESSION['access_string']));
		$access_arr = explode(",",trim($_SESSION['access_string']));
		$temp = "<nav class=\"nav flex-column px-0\" id=\"side-nav\">";
		// user management
		if (count(array_intersect(array(2,26,27,28,29,30,31,32,69),$access_arr)) > 0) {
			// allow submenu
			$temp .= "<a href=\"#\" class=\"nav-link nav-first-level\" ><i class=\"fa fa-user fa-fw\"></i> {$xml->user_mgnt}<span class=\"fa arrow\"></span></a>";
			$temp .= "<div class=\"nav-submenu\" id=\"submenu1\">";
			if (count(array_intersect(array(26,27),$access_arr)))
				$temp .= "<a class=\"nav-link\" href=\"user_account.php\" data-submenu=\"submenu1\"> {$xml->user_acc_mgnt}</a>";
			if (count(array_intersect(array(28,29),$access_arr))) 
				$temp .= "<a class=\"nav-link\" href=\"user_role.php\" data-submenu=\"submenu1\"> {$xml->user_role_mgnt}</a>";
			if (count(array_intersect(array(30,31),$access_arr)))
				$temp .= "<a class=\"nav-link\" href=\"user_department.php\" data-submenu=\"submenu1\"> {$xml->department_mgnt}</a>";
			if (in_array(32,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"access_log.php\" data-submenu=\"submenu1\"> {$xml->access_log}</a>";
			if (in_array(69,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"user_transfer.php\" data-submenu=\"submenu1\"> {$xml->user_transfer}</a>";
			$temp .= "</div>";
		}
		// address book
		if (count(array_intersect(array(3,4),$access_arr)) > 0) {
			// allow submenu
			$temp .= "<a href=\"#\" class=\"nav-link nav-first-level\" ><i class=\"fa fa-phone fa-fw\"></i> {$xml->address_book}<span class=\"fa arrow\"></span></a>";
			$temp .= "<div class=\"nav-submenu\" id=\"submenu2\">";
			if (in_array(4,$access_arr)) {
				$temp .= "<a class=\"nav-link\" href=\"address_book.php\" data-submenu=\"submenu2\"> {$xml->address_book}</a>";
				$temp .= "<a class=\"nav-link\" href=\"address_group.php\" data-submenu=\"submenu2\"> {$xml->address_group}</a>";
			}
			if (in_array(3,$access_arr)) {
				$temp .= "<a class=\"nav-link\" href=\"global_address_book.php\" data-submenu=\"submenu2\"> {$xml->global_address_book}</a>";
				$temp .= "<a class=\"nav-link\" href=\"global_address_group.php\" data-submenu=\"submenu2\"> {$xml->global_address_group}</a>";
			}
				

			$temp .= "</div>";
		}
		// message template
		if (count(array_intersect(array(5,63,66,6),$access_arr)) > 0) {
			// allow submenu
			$temp .= "<a href=\"#\" class=\"nav-link nav-first-level\" ><i class=\"fa fa-envelope fa-fw\"></i> {$xml->msg_tmpl}<span class=\"fa arrow\"></span></a>";
			$temp .= "<div class=\"nav-submenu\" id=\"submenu3\">";
			if (in_array(6,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"message_template.php\" data-submenu=\"submenu3\"> {$xml->msg_tmpl}</a>";
			if (in_array(5,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"global_message_template.php\" data-submenu=\"submenu3\"> {$xml->global_msg_tmpl}</a>";
			if (in_array(63,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"mim_message_template.php\" data-submenu=\"submenu3\"> {$xml->mim_msg_tmpl}</a>";
			if (in_array(66,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"global_mim_message_template.php\" data-submenu=\"submenu3\"> {$xml->global_mim_msg_tmpl}</a>";

			$temp .= "</div>";
		}
		// campaign menu
		if (in_array(58,$access_arr)) {
			$temp .= <<< TEMP
			<a href="campaign.php" class="nav-link nav-first-level"><i class="fa fa-bullhorn fa-fw"></i> {$xml->campaign}</a>
TEMP;
		}
		
		// send sms
		if (in_array(7,$access_arr)) {
			$temp .= <<< TEMP
			<a href="#" class="nav-link nav-first-level"><i class="fa fa-commenting-o fa-fw"></i> {$xml->send_msg}<span class="fa arrow"></span></a>
			<div class="nav-submenu" data-submenu="submenu4">
				<a href="send_sms.php" class="nav-link" data-submenu=\"submenu4\">{$xml->send_sms}</a>
				<a href="broadcast_sms.php" class="nav-link" data-submenu=\"submenu4\">{$xml->send_sms_upload}</a>
				<a href="broadcast_sms_status.php" class="nav-link" data-submenu=\"submenu4\">{$xml->send_sms_upload_status}</a>
			</div>

			<a href="#" class="nav-link nav-first-level"><i class="fa fa-clock-o fa-fw"></i> {$xml->schedule_msg}<span class="fa arrow"></span></a>
			<div class="nav-submenu" id=\"submenu5\">
				<a href="scheduled_sms.php" class="nav-link" data-submenu=\"submenu5\">{$xml->schedule_msg}</a>
			</div>
TEMP;
		}
		// common inbox
		if (in_array(10,$access_arr)) {
			$temp .= <<< TEMP
			<a href="common_inbox.php" class="nav-link nav-first-level"><i class="fa fa-inbox fa-fw"></i> {$xml->common_inbox}</a>
TEMP;
		}
		// inbox management
		if (count(array_intersect(array(11,12,13,14,17,18,19,20),$access_arr)) > 0) {
			// allow submenu
			$temp .= "<a href=\"#\" class=\"nav-link nav-first-level\" ><i class=\"fa fa-cubes fa-fw\"></i> {$xml->logs_mgnt}<span class=\"fa arrow\"></span></a>";
			$temp .= "<div class=\"nav-submenu\" id=\"submenu6\">";
			if (in_array(11,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"inbox.php\" data-submenu=\"submenu6\"> {$xml->inbox}</a>";
			if (in_array(17,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"global_inbox.php\" data-submenu=\"submenu6\"> {$xml->global_inbox}</a>";
			if (in_array(12,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"sent_log.php\" data-submenu=\"submenu6\"> {$xml->sent_log}</a>";
			if (in_array(18,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"global_sent_log.php\" data-submenu=\"submenu6\"> {$xml->global_sent}</a>";
			if (in_array(13,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"unsent_log.php\" data-submenu=\"submenu6\"> {$xml->unsent_log}</a>";
			if (in_array(19,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"global_unsent_log.php\" data-submenu=\"submenu6\"> {$xml->global_unsent_log}</a>";
			if (in_array(14,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"queue_log.php\" data-submenu=\"submenu6\"> {$xml->queue_log}</a>";
			if (in_array(20 ,$access_arr))
				$temp .= "<a class=\"nav-link\" href=\"global_queue_log.php\" data-submenu=\"submenu6\"> {$xml->global_queue_log}</a>";

			$temp .= "</div>";
		}
		// unsubscribe
		if (in_array(47,$access_arr)) {
			$temp .= <<< TEMP
			<a href="#" class="nav-link nav-first-level"><i class="fa fa-ban fa-fw"></i> {$xml->unsub_list}<span class="fa arrow"></span></a>
			<div class="nav-submenu" id=\"submenu7\">
				<a href="unsubscribe_list.php" class="nav-link" data-submenu=\"submenu7\">{$xml->unsub_mobile}</a>
				<a href="unsubscribe_keyword.php" class="nav-link"  data-submenu=\"submenu7\">{$xml->unsub_kw}</a>
			</div>
TEMP;
		}
		// Quota Management
		if (in_array(48,$access_arr)) {
			$temp .= <<< TEMP
			<a href="quota_mnt.php" class="nav-link nav-first-level"><i class="fa fa-pie-chart fa-fw"></i> {$xml->quota_mgnt}</a>
TEMP;
		}
		// Keyword Management
		if (in_array(56,$access_arr)) {
			$temp .= <<< TEMP
			<a href="keyword_management.php" class="nav-link nav-first-level"><i class="fa fa-font fa-fw"></i> {$xml->keyword_mgnt}</a>
TEMP;
		}
		// System Configuration
		if (in_array(45,$access_arr)) {
			$temp .= <<< TEMP
			<a href="#" class="nav-link nav-first-level"><i class="fa fa-wrench fa-fw"></i> {$xml->system_config}<span class="fa arrow"></span></a>
			<div class="nav-submenu"  id=\"submenu8\">
			<a href="modemconfig.php" class="nav-link" data-submenu=\"submenu8\">{$xml->modem_conf}</a>
			<a href="sysconfig.php" class="nav-link" data-submenu=\"submenu8\">{$xml->time_config}</a>
			<a href="ldap_mgnt.php" class="nav-link" data-submenu=\"submenu8\">{$xml->ldap_mgnt}</a>
			<a href="weblogo.php" class="nav-link" data-submenu=\"submenu8\">{$xml->web_logo}</a>
			</div>
TEMP;
		}
		// api application
		if (in_array(9,$access_arr)) {
			$temp .= <<< TEMP
			<a href="api_list.php" class="nav-link nav-first-level"><i class="fa fa-pie-chart fa-fw"></i> {$xml->application_mgnt}</a>
TEMP;
		}
		// Audit Trail
		if (in_array(65,$access_arr)) {
			$temp .= <<< TEMP
			<a href="audit_trail.php" class="nav-link nav-first-level"><i class="fa fa-font fa-fw"></i> {$xml->audit_trail}</a>
TEMP;
		}
		// report menu
		if (count(array_intersect(array(59,60,61,62,67,68,71),$access_arr)) > 0) {
			$temp .= "<a href=\"#\" class=\"nav-link nav-first-level\" ><i class=\"fa fa-clipboard fa-fw\"></i> {$xml->report}<span class=\"fa arrow\"></span></a>";
			$temp .= "<div class=\"nav-submenu\" id=\"submenu9\">";
			if (in_array(67,$access_arr))
				$temp .= '<a class="nav-link" href="survey_report.php" data-submenu="submenu9">'.$xml->survey_report.'</a>';
			if (in_array(60,$access_arr))
				$temp .= '<a class="nav-link" href="report_incoming.php" data-submenu="submenu9">'.$xml->incoming_report.'</a>';
			if (in_array(68,$access_arr))
				$temp .= '<a class="nav-link" href="report_api.php" data-submenu="submenu9">'.$xml->api_global_report.'</a>';
			else if (in_array(71,$access_arr))
				$temp .= '<a class="nav-link" href="report_api.php?view=dept" data-submenu="submenu9">'.$xml->api_report.'</a>';
			if (in_array(59,$access_arr))
				$temp .= '<a class="nav-link" href="report.php?view=user" data-submenu="submenu9">'.$xml->preport.'</a>';
			if (in_array(61,$access_arr))
				$temp .= '<a class="nav-link" href="report.php?view=alldepts" data-submenu="submenu9">'.$xml->alldeptsreport.'</a>';
			if (in_array(62,$access_arr))
				$temp .= '<a class="nav-link" href="report.php?view=users" data-submenu="submenu9">'.$xml->deptreport.'</a>';
			$temp .= "</div>";
		}
		// analytic
		if (in_array(72,$access_arr)) {
			$temp .= <<< TEMP
			<a class="nav-link nav-first-level" href="analytic.php" id="link72"><i class="fa fa-bar-chart fa-fw"></i> Analytic</a>
TEMP;
		}
		// invoice
		if (in_array(73,$access_arr)) {
			$temp .= <<< TEMP
			<a class="nav-link nav-first-level" href="invoice.php"><i class="fa fa-dollar fa-fw"></i> Invoice</a>
TEMP;
		}
		// shortener url
		if (in_array(70,$access_arr)) {
			$temp .= <<< TEMP
			<a href="shortended_url.php" class="nav-link nav-first-level"><i class="fa fa-font fa-fw"></i> {$xml->shortended_url}</a>
TEMP;
		}
		// setting
		if (isUserAdmin($_SESSION["userid"])){
			$temp .= <<< TEMP
				<a class="nav-link nav-first-level" href="setting.php"><i class="fa fa-cog fa-fw"></i> {$xml->setting}</a>
TEMP;
			}
		// incident report
		if (in_array(64, $access_arr)) {
			$temp .= <<< TEMP
			<a class="nav-link nav-first-level" href="https://ice.nera.net/support" target = "_blank"><i class="fa fa-clipboard fa-fw"></i> {$xml->incident_report}</a>
TEMP;
		}
		$temp .= "</nav>";
		echo $temp;
	break;
	case "getmenu_backup":
		// tychang: old menu. prefer not to use this 2023-02-20
		$xml = GetLanguage("menu",$lang);
	
		error_log("access string: " . trim($_SESSION['access_string']));

		$access_arr = explode(",",trim($_SESSION['access_string']));
		//User Management
		$display_user = (in_array('2',$access_arr) ? '' : 'dnone');
		$display_acct = (in_array('26',$access_arr) || in_array('27',$access_arr) ? '' : 'dnone');
		$display_rol = (in_array('28',$access_arr) || in_array('29',$access_arr) ? '' : 'dnone');
		$display_dpt = (in_array('30',$access_arr) || in_array('31',$access_arr) ? '' : 'dnone');
		$display_log = (in_array('32',$access_arr) ? '' : 'dnone');
		$display_transfer = (in_array('69',$access_arr) ? '' : 'dnone');
		//Address Book
		$display_add_menu = (in_array('3',$access_arr) || in_array('4',$access_arr) ? '' : 'dnone');
		$display_gab = (in_array('3',$access_arr) ? '' : 'dnone');
		$display_gag = (in_array('3',$access_arr) ? '' : 'dnone');
		$display_pab = (in_array('4',$access_arr) ? '' : 'dnone');
		$display_pag = (in_array('4',$access_arr) ? '' : 'dnone');
		//Message Template
		$display_tpl_menu = (in_array('5',$access_arr) || in_array('6',$access_arr) ? '' : 'dnone');
		$display_gmt = (in_array('5',$access_arr) ? '' : 'dnone');
		$display_mmt = (in_array('63',$access_arr) ? '' : 'dnone');
		$display_gmmt = (in_array('66',$access_arr) ? '' : 'dnone');
		$display_pmt = (in_array('6',$access_arr) ? '' : 'dnone');
		//Send SMS
		$display_sms_menu = "";
		if (in_array('7',$access_arr)) {
	
			$display_sms_menu = <<< END
			<a href="#" class="nav-link nav-first-level"><i class="fa fa-commenting-o fa-fw"></i> {$xml->send_msg}<span class="fa arrow"></span></a>
			<div class="nav-submenu">
				<a href="send_sms.php" class="nav-link">{$xml->send_sms}</a>
				<a href="broadcast_sms.php" class="nav-link">{$xml->send_sms_upload}</a>
				<a href="broadcast_sms_status.php" class="nav-link">{$xml->send_sms_upload_status}</a>
			</div>

			<a href="#" class="nav-link nav-first-level"><i class="fa fa-clock-o fa-fw"></i> {$xml->schedule_msg}<span class="fa arrow"></span></a>
			<div class="nav-submenu">
				<a href="scheduled_sms.php" class="nav-link">{$xml->schedule_msg}</a>
			</div>
END;
		}
		//Common inbox 
		$display_cmn_inb = "";
		if (in_array('10',$access_arr)) {
			$display_cmn_inb = <<< END
			<a href="common_inbox.php" class="nav-link nav-first-level"><i class="fa fa-inbox fa-fw"></i> {$xml->common_inbox}</a>
END;
		}
		//Inbox/Logs Management
		$display_log_mgnt = (in_array('11',$access_arr) || in_array('12',$access_arr)
							|| in_array('13',$access_arr) || in_array('14',$access_arr)
							|| in_array('17',$access_arr) || in_array('18',$access_arr)
							|| in_array('19',$access_arr) || in_array('20',$access_arr) ? 'block' : 'dnone');
		$display_pinb = (in_array('11',$access_arr) ? '' : 'dnone');
		$display_pslog = (in_array('12',$access_arr) ? '' : 'dnone');
		$display_pulog = (in_array('13',$access_arr) ? '' : 'dnone');
		$display_pqlog = (in_array('14',$access_arr) ? '' : 'dnone');
		$display_ginb = (in_array('17',$access_arr) ? '' : 'dnone');
		$display_gslog = (in_array('18',$access_arr) ? '' : 'dnone');
		$display_gulog = (in_array('19',$access_arr) ? '' : 'dnone');
		$display_gqlog = (in_array('20',$access_arr) ? '' : 'dnone');
		//Unsubscribe List
		$display_unsub_menu = "";
		if (in_array('47',$access_arr)) {
			$display_unsub_menu = <<< END
			<a href="#" class="nav-link nav-first-level"><i class="fa fa-ban fa-fw"></i> {$xml->unsub_list}<span class="fa arrow"></span></a>
			<div class="nav-submenu">
				<a href="unsubscribe_list.php" class="nav-link">{$xml->unsub_mobile}</a>
				<a href="unsubscribe_keyword.php" class="nav-link">{$xml->unsub_kw}</a>
			</div>
END;
		}
		//Quota Management
		$display_quo_mnt = "";
		if (in_array('48',$access_arr)) {
			$display_quo_mnt = <<< END
			<a href="quota_mnt.php" class="nav-link nav-first-level"><i class="fa fa-pie-chart fa-fw"></i> {$xml->quota_mgnt}</a>
END;
		} 
		//Keyword Management
		$display_key_menu = "";
		if (in_array('56',$access_arr)) {
			$display_key_menu = <<< END
			<a href="keyword_management.php" class="nav-link nav-first-level"><i class="fa fa-font fa-fw"></i> {$xml->keyword_mgnt}</a>
END;
		}
		//System Configuration
		$display_sys_menu = "";
		if (in_array('45',$access_arr)) {
			$display_sys_menu = <<< END
			<a href="#" class="nav-link nav-first-level"><i class="fa fa-wrench fa-fw"></i> {$xml->system_config}<span class="fa arrow"></span></a>
			<div class="nav-submenu">
			<a href="modemconfig.php" class="nav-link">{$xml->modem_conf}</a>
			<a href="sysconfig.php" class="nav-link">{$xml->time_config}</a>
			<a href="ldap_mgnt.php" class="nav-link">{$xml->ldap_mgnt}</a>
			<a href="weblogo.php" class="nav-link">{$xml->web_logo}</a>
			</div>
END;
		}
		//campagin
		$display_campaign_menu = "";
		if (in_array('58',$access_arr)) {
			$display_campaign_menu = <<< END
			<a href="#" class="nav-link nav-first-level"><i class="fa fa-bullhorn fa-fw"></i> {$xml->campaign_mgnt}<span class="fa arrow"></span></a>
			<div class="nav-submenu">
				<a href="campaign.php" class="nav-link" >{$xml->campaign}</a>
			</div>
END;
		}

		// assmi
		$display_application_mgnt = "";
		if (in_array('9',$access_arr)) {
			$display_application_mgnt = <<< END
			<a href="api_list.php" class="nav-link nav-first-level"><i class="fa fa-pie-chart fa-fw"></i> {$xml->application_mgnt}</a>
END;
		}
		$display_audit_trail = "";
		if (in_array('65',$access_arr)) {
			$display_audit_trail = <<< END
			<a href="audit_trail.php" class="nav-link nav-first-level"><i class="fa fa-font fa-fw"></i> {$xml->audit_trail}</a>
END;
		}
		//assmi
		$display_shortended_url = "";
		if (in_array('70',$access_arr)) {
			$display_shortended_url = <<< END
			<a href="shortended_url.php" class="nav-link nav-first-level"><i class="fa fa-font fa-fw"></i> {$xml->shortended_url}</a>
END;
		}

		// Ty's Comment: trying new method instead of using css's hidden block
		$display_report_menu = "";
		if(in_array('59',$access_arr) || in_array('61',$access_arr) || in_array('62',$access_arr) || in_array('60',$access_arr) || in_array('67',$access_arr)) {
			$display_report_menu = <<< END
			<a class="nav-link nav-first-level" href="#"><i class="fa fa-clipboard fa-fw"></i> {$xml->report}<span class="fa arrow"></span></a>
			<div class="nav-submenu">
END;
			if (in_array('67',$access_arr))
				$display_report_menu .= '<a class="nav-link" href="survey_report.php">'.$xml->survey_report.'</a></li>';
			if (in_array('60',$access_arr))
				$display_report_menu .= '<a class="nav-link" href="report_incoming.php">'.$xml->incoming_report.'</a>';
			if (in_array('68',$access_arr))
				$display_report_menu .= '<a class="nav-link" href="report_api.php">'.$xml->api_global_report.'</a>';
			else if (in_array('71',$access_arr))
				$display_report_menu .= '<a class="nav-link" href="report_api.php?view=dept">'.$xml->api_report.'</a>';
			if (in_array('59',$access_arr))
				$display_report_menu .= '<a class="nav-link" href="report.php?view=user">'.$xml->preport.'</a>';
			if (in_array('61',$access_arr))
				$display_report_menu .= '<a class="nav-link" href="report.php?view=alldepts">'.$xml->alldeptsreport.'</a>';
			if (in_array('62',$access_arr))
				$display_report_menu .= '<a class="nav-link" href="report.php?view=users">'.$xml->deptreport.'</a>';
			$display_report_menu .= "</div>";

		}

		$display_analytic_menu = "";
		if (in_array('72',$access_arr)) {
			$display_analytic_menu = <<< END
			<a class="nav-link nav-first-level" href="analytic.php" id="link72"><i class="fa fa-bar-chart fa-fw"></i> Analytic</a>
END;
		}
		$display_invoice_menu = "";
		if (in_array('73',$access_arr)) {
			$display_invoice_menu = <<< END
			<a class="nav-link nav-first-level" href="invoice.php"><i class="fa fa-dollar fa-fw"></i> Invoice</a>
END;
		}
		
		$display_incident_report = "";
		if (in_array('64', $access_arr)) {
			$display_incident_report = <<< END
			<a class="nav-link nav-first-level" href="https://ice.nera.net/support" target = "_blank"><i class="fa fa-clipboard fa-fw"></i> {$xml->incident_report}</a>
END;
		}
		if (isUserAdmin($_SESSION["userid"])){
		$display_setting = <<< END
			<a class="nav-link nav-first-level" href="setting.php"><i class="fa fa-cog fa-fw"></i> {$xml->setting}</a>
END;
		}
		$html = <<<HTML
	<nav class="nav flex-column px-0" id="side-nav">
		<a href="#" class="nav-link nav-first-level" ><i class="fa fa-user fa-fw"></i> {$xml->user_mgnt}<span class="fa arrow"></span></a>
			<div class="nav-submenu">
				<a class="nav-link {$display_acct}" href="user_account.php"> {$xml->user_acc_mgnt}</a>
				<a class="nav-link {$display_rol}"  href="user_role.php">{$xml->user_role_mgnt}</a>
				<a class="nav-link {$display_dpt}" href="user_department.php">{$xml->department_mgnt}</a>
				<a class="nav-link {$display_log}"  href="access_log.php">{$xml->access_log}</a>
				<a class="nav-link {$display_transfer}"  href="user_transfer.php">{$xml->user_transfer}</a>
			</div>

		<a href="#" class="nav-link nav-first-level" style="display:{$display_add_menu}"><i class="fa fa-phone fa-fw"></i> {$xml->address_book}<span class="fa arrow"></span></a>
			<div class="nav-submenu">
				<a class="nav-link {$display_pab}"  href="address_book.php">{$xml->address_book}</a>
				<a class="nav-link {$display_pag}"  href="address_group.php"> {$xml->address_group}</a>
				<a class="nav-link {$display_gab}"  href="global_address_book.php"> {$xml->global_address_book}</a>
				<a class="nav-link {$display_gag}"  href="global_address_group.php"> {$xml->global_address_group}</a>
			</div>
		<a href="#" class="nav-link nav-first-level" style="display:{$display_tpl_menu}"><i class="fa fa-envelope fa-fw"></i> {$xml->msg_tmpl}<span class="fa arrow"></span></a>
			<div class="nav-submenu">
				<a class="nav-link {$display_pmt}" href="message_template.php">{$xml->msg_tmpl}</a>
				<a class="nav-link {$display_gmt}" href="global_message_template.php">{$xml->global_msg_tmpl}</a>
				<a class="nav-link {$display_mmt}" href="mim_message_template.php">{$xml->mim_msg_tmpl}</a>
				<a class="nav-link {$display_gmmt}" href="global_mim_message_template.php">{$xml->global_mim_msg_tmpl}</a>
			</div>
		
		$display_campaign_menu

		$display_sms_menu
		$display_cmn_inb

		<a href="#" class="nav-link nav-first-level" style="display:{$display_log_mgnt}"><i class="fa fa-cubes fa-fw"></i> {$xml->logs_mgnt}<span class="fa arrow"></span></a>
		<div class="nav-submenu">
		<a href="inbox.php" class="nav-link  {$display_pinb}">{$xml->inbox}</a>
		<a href="global_inbox.php" class="nav-link {$display_ginb}">{$xml->global_inbox}</a>
		<a href="sent_log.php" class="nav-link {$display_pslog}">{$xml->sent_log}</a>
		<a href="global_sent_log.php" class="nav-link {$display_gslog}">{$xml->global_sent}</a>
		<a href="unsent_log.php" class="nav-link {$display_pulog}">{$xml->unsent_log}</a>
		<a href="global_unsent_log.php" class="nav-link {$display_gulog}">{$xml->global_unsent_log}</a>
		<a href="queue_log.php" class="nav-link {$display_pqlog}">{$xml->queue_log}</a>
		<a href="global_queue_log.php" class="nav-link {$display_gqlog}">{$xml->global_queue_log}</a>
		</div>

		$display_unsub_menu

		$display_quo_mnt
		
		$display_key_menu
		
		$display_sys_menu
	
		$display_application_mgnt
		
		$display_audit_trail

		$display_report_menu
		$display_analytic_menu
		$display_invoice_menu

		$display_shortended_url
		$display_setting
		$display_incident_report

	</nav>
HTML;
		echo $html;
        break;
    default:
        die("Invalid Command");
}
?>