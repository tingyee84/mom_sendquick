<?php
require_once('/home/msg/www/htdocs/appliance/lib/bot/whatsapp_dc_bot.php');
require_once('/home/msg/www/htdocs/appliance/lib/db_filter.php');
require_once('/home/msg/www/htdocs/appliance/lib/db_spool.php');
require_once('/home/msg/www/htdocs/appliance/lib/db_sq.php');
require_once('/home/msg/www/htdocs/appliance/lib/db_webapp.php');

$botID = trim(file_get_contents('bot.id'));

list($campaignId, $campaignSecret, $campaignAccessToken) = WhatsAppDCBot::GetAPICredentials($spdbconn, $botID);

$bot = new WhatsAppDCBot($spdbconn, $campaignId, $campaignSecret, $campaignAccessToken);
$bot->setSQDBConn($sqdbconn);
$bot->setFilterDBConn($filterdbconn);
$bot->setWebAppDBConn($webappdbconn);

$applianceInfo = WhatsAppDCBot::getPhonebookInfo();
if($applianceInfo['APPLIANCE'] == 'Avera') {
	require_once('/home/msg/www/htdocs/appliance/lib/db_nm.php');
	$bot->setNMDBConn($nmdbconn);
}

foreach($bot->parseEventRequest() as $ev)
{
	$event = $ev['event'];
	
	switch($event['type']) {

	case 'verify-webhook':
		echo $event['challenge'];
		break;

	case 'message-status':
		$s['broadcastId'] = $event['message']['broadcastId'];
		$s['status'] = $event['message']['status'];
		$s['remark'] = $event['message']['remark'];

		if($s['status'] != 'R') {
			$bot->updateMessageStatus($s);
		}
		break;

	case 'opt-in':
	case 'message':
	case 'command':
		$message = $event['messages'][0];

		switch($message['type']){
			case 'text':
				$content = $message['message'];
				break;
			default:
				break;
		}

		$subscriberID = trim($event['from']['subscriberId']);
		$userProfile = $bot->getExistingProfile($botID, $subscriberID);
			
		if (empty($userProfile)){
			$userProfile = $bot->getUserProfile($subscriberID);
			$status = $bot->createUserProfile($botID, $subscriberID, $userProfile);
		} else {
			$status = 1;
		}

		$userID = $msg['to'] = trim(@$userProfile['uid']);
		$userProfile['type'] = 1;

		if ($bot->isSlashCmd($content)) {
			$response = $bot->processSlashCmd($userID, $botID, $content);
			$bot->sendMessage($userID, $response['msg']);
		} else {
			
			$replacement['userid'] = @$userProfile['uid'];
			$replacement['profilename'] = @$userProfile['uid'];

			switch($status){
				case '0': # new record added
					$msg = $bot->getMessage('waSuccessOptIn', $replacement);
					$subscription['event'] = 'optin';
					break;
				case '1': # existing record found
					$msg = $bot->getMessage('receiveMessage', $replacement);
					$subscription['event'] = 'message';
					break;
				case '-1': # error
					$msg = $bot->getMessage('failOptIn', $replacement);
					break;
			}
			
			if (!empty($msg)) {
				$bot->sendMessage($userID, $msg);
			}
		}

		$s['user_id'] = $userID;
		$s['msg'] = $content;
		$s['smsc'] = 'MIM';
		$s['imei'] = 'WHATSAPPDC';
		$i_status = $bot->addIncomingSMS($s);

		$subscription['bot_id'] = $botID;
		$subscription['profile_name'] = @$userProfile['uid'];
		$subscription['msg'] = $content;
		$subscription['user_id'] = $userID;
		$subscription['raw_msg'] = json_encode($event);
		$bot->createBotSubscription($subscription);

		break;

	case 'message-status':
		//to be completed
		//update WA message status and trigger message template retry immediately
		break;
	default:
		break;
	}
}
?>
