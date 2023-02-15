<?php
require_once('lib/commonFunc.php');

switch ($_POST['mode']) {
	case "getmenu":
		$xml = GetLanguage("menu",$lang);
	
		error_log("access string: " . trim($_SESSION['access_string']));

		$access_arr = explode(",",trim($_SESSION['access_string']));
		//User Management
		$display_user = (in_array('2',$access_arr) ? '' : 'd-none');
		$display_acct = (in_array('26',$access_arr) || in_array('27',$access_arr) ? '' : 'd-none');
		$display_rol = (in_array('28',$access_arr) || in_array('29',$access_arr) ? '' : 'd-none');
		$display_dpt = (in_array('30',$access_arr) || in_array('31',$access_arr) ? '' : 'd-none');
		$display_log = (in_array('32',$access_arr) ? '' : 'd-none');
		$display_transfer = (in_array('69',$access_arr) ? '' : 'd-none');
		//Address Book
		$display_add_menu = (in_array('3',$access_arr) || in_array('4',$access_arr) ? '' : 'd-none');
		$display_gab = (in_array('3',$access_arr) ? '' : 'd-none');
		$display_gag = (in_array('3',$access_arr) ? '' : 'd-none');
		$display_pab = (in_array('4',$access_arr) ? '' : 'd-none');
		$display_pag = (in_array('4',$access_arr) ? '' : 'd-none');
		//Message Template
		$display_tpl_menu = (in_array('5',$access_arr) || in_array('6',$access_arr) ? '' : 'd-none');
		$display_gmt = (in_array('5',$access_arr) ? '' : 'd-none');
		$display_mmt = (in_array('63',$access_arr) ? '' : 'd-none');
		$display_gmmt = (in_array('66',$access_arr) ? '' : 'd-none');
		$display_pmt = (in_array('6',$access_arr) ? '' : 'd-none');
		//Send SMS
		$display_sms_menu = "";
		if (in_array('7',$access_arr)) {
			$display_sms_menu = <<< END
			<a href="#" class="nav-link nav-first-level"><i class="fa fa-commenting-o fa-fw"></i> {$xml->send_msg}<span class="fa arrow"></span></a>
			<div class="nav-submenu" style="display:none">
				<a href="send_sms.php" class="nav-link">{$xml->send_sms}</a>
				<a href="broadcast_sms.php" class="nav-link">{$xml->send_sms_upload}</a>
				<a href="broadcast_sms_status.php" class="nav-link">{$xml->send_sms_upload_status}</a>
			</div>

			<a href="#" class="nav-link nav-first-level"><i class="fa fa-clock-o fa-fw"></i> {$xml->schedule_msg}<span class="fa arrow"></span></a>
			<div class="nav-submenu" style="display:none">
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
							|| in_array('19',$access_arr) || in_array('20',$access_arr) ? 'block' : 'none');
		$display_pinb = (in_array('11',$access_arr) ? 'block' : 'none');
		$display_pslog = (in_array('12',$access_arr) ? 'block' : 'none');
		$display_pulog = (in_array('13',$access_arr) ? 'block' : 'none');
		$display_pqlog = (in_array('14',$access_arr) ? 'block' : 'none');
		$display_ginb = (in_array('17',$access_arr) ? 'block' : 'none');
		$display_gslog = (in_array('18',$access_arr) ? 'block' : 'none');
		$display_gulog = (in_array('19',$access_arr) ? 'block' : 'none');
		$display_gqlog = (in_array('20',$access_arr) ? 'block' : 'none');
		//Unsubscribe List
		$display_unsub_menu = "";
		if (in_array('47',$access_arr)) {
			$display_unsub_menu = <<< END
			<a href="#" class="nav-link nav-first-level"><i class="fa fa-ban fa-fw"></i> {$xml->unsub_list}<span class="fa arrow"></span></a>
			<div class="nav-submenu" style="display:none">
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
			<div class="nav-submenu" style="display:none">
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
			<div class="nav-submenu" style="display:none">
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
			<div class="nav-submenu" style="display:none">
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
			<a class="nav-link nav-first-level" href="analytic.php"><i class="fa fa-bar-chart fa-fw"></i> Analytic</a>
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
				<a style="display:{$display_pab}" class="nav-link"  href="address_book.php">{$xml->address_book}</a>
				<a style="display:{$display_pag}"  class="nav-link"  href="address_group.php"> {$xml->address_group}</a>
				<a style="display:{$display_gab}" class="nav-link"  href="global_address_book.php"> {$xml->global_address_book}</a>
				<a style="display:{$display_gag}" class="nav-link"  href="global_address_group.php"> {$xml->global_address_group}</a>
			</div>
		<a href="#" class="nav-link nav-first-level" style="display:{$display_tpl_menu}"><i class="fa fa-envelope fa-fw"></i> {$xml->msg_tmpl}<span class="fa arrow"></span></a>
			<div class="nav-submenu">
				<a style="display:{$display_pmt}" class="nav-link" href="message_template.php">{$xml->msg_tmpl}</a>
				<a style="display:{$display_gmt}" class="nav-link" href="global_message_template.php">{$xml->global_msg_tmpl}</a>
				<a style="display:{$display_mmt}" class="nav-link" href="mim_message_template.php">{$xml->mim_msg_tmpl}</a>
				<a style="display:{$display_gmmt}" class="nav-link" href="global_mim_message_template.php">{$xml->global_mim_msg_tmpl}</a>
			</div>
		
		$display_campaign_menu

		$display_sms_menu
		$display_cmn_inb

		<a href="#" class="nav-link nav-first-level" style="display:{$display_log_mgnt}"><i class="fa fa-cubes fa-fw"></i> {$xml->logs_mgnt}<span class="fa arrow"></span></a>
		<div class="nav-submenu">
		<a style="display:{$display_pinb}" href="inbox.php" class="nav-link">{$xml->inbox}</a>
		<a style="display:{$display_ginb}" href="global_inbox.php" class="nav-link">{$xml->global_inbox}</a>
		<a style="display:{$display_pslog}" href="sent_log.php" class="nav-link">{$xml->sent_log}</a>
		<a style="display:{$display_gslog}" href="global_sent_log.php" class="nav-link">{$xml->global_sent}</a>
		<a style="display:{$display_pulog}" href="unsent_log.php" class="nav-link">{$xml->unsent_log}</a>
		<a style="display:{$display_gulog}" href="global_unsent_log.php" class="nav-link">{$xml->global_unsent_log}</a>
		<a style="display:{$display_pqlog}" href="queue_log.php" class="nav-link">{$xml->queue_log}</a>
		<a style="display:{$display_gqlog}" href="global_queue_log.php" class="nav-link">{$xml->global_queue_log}</a>
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
<script src="js/header_lib_js.php"></script>
<!-- 
<script>
// check url 
var urlParams = new URLSearchParams(window.location.search);
var pn = window.location.pathname.substr(window.location.pathname.lastIndexOf("/")+1)+(urlParams.has("view")?"?view="+urlParams.get("view"):"");

$("#side-nav a").each(function() {
	if (pn == $(this).attr("href")) {
		$(this).addClass("active");
		if ($(this).parent().hasClass("nav-submenu")) {
			$(this).parent().show();
		}
	}
});
$("#side-nav a.nav-first-level").each(function() {
	$(this).on('click',function(evt) {
		if ($(this).next().is(":visible") && $(this).next().hasClass("nav-submenu")) {
			$(this).next().slideUp();
		} else {
			$(".nav-submenu:visible").slideUp();
			$(this).next().slideDown();
		}
	});
});

$("#when_conversation_btn_was_clicked").on("click",function(event){event.preventDefault();Cookies.remove('id');window.location="conversation.php"});</script> -->
