<?php
class BaseBot
{
	var $conn;
	var $webhookPath;
	var $webhookURL;
	var $webhookTemplatePath;
	var $sqDBConn;
	var $filterDBConn;
	var $nmDBConn;
	var $webappDBConn;
	var $clusterObj;
	
	# Default constructor
	public function __construct($conn)
	{
		$this->conn = $conn;
		$this->webhookPath = "/home/msg/www/htdocs/appliance/webhook";
		if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
			$this->webhookURL .= "https://" ;
		else
			$this->webhookURL .= "http://" ;

		$this->webhookURL .= $_SERVER['HTTP_HOST'] . "/appliance/webhook";

		$this->webhookTemplatePath = $this->webhookPath . "/templates";
		$this->dockerAPIHost = 'v1.37';
		$this->dockerConfigPath =  '/home/msg/conf/docker/';
		$this->whatsAppAPIVersion = $this->readWhatsAppAPIVersion();
		$this->clusterObj;
	}

	public function setSQDBConn($conn) {
		$this->sqDBConn = $conn;
	}

	public function setWebAppDBConn($conn) {
		$this->webappDBConn = $conn;
	}

	public function setCluster($obj) {
		$this->clusterObj = $obj;
	}

	public function getSyncMode() {
		$sync = 1;
		if ($this->clusterObj->checkClusterFlag() == 1) {
			if ($this->clusterObj->getClusterMode() == 4 || $this->clusterObj->getClusterMode() == 5) {
				$sync = 0;
			}
		}

		return $sync;
	}

	public function setFilterDBConn($conn) {
		$this->filterDBConn = $conn;
	}

	public function setNMDBConn($conn) {
		$this->nmDBConn = $conn;
	}

	public function getWebhookPath() {
		return $this->webhookPath;
	}

	public function getAPI($tag) {
		$tag = pg_escape_string($tag);
		$sql = "SELECT send_url FROM bot_send_api_urls WHERE tag = '$tag'";
		$res = pg_query($this->conn, $sql);
		if(!$res){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = pg_fetch_assoc($res);
		}

		return $ret;

	}


	public function idxGenerator($table){
		$cmd = "SELECT nextval('{$table}_id_seq')";
		$res = pg_query($this->conn, $cmd);
		$c = pg_fetch_array($res, 0);
		$seq = time().sprintf("%05d",$c[0]);
		return $seq;
	}

	function getSystemConfig($configKey) {
		$ret = 0;
		$sqlcmd = "SELECT config_value FROM system_config WHERE config_key='$configKey'";
		$res = pg_query($this->sqDBConn, $sqlcmd);
		if(!$res){
			error_log(pg_last_error($this->sqDBConn));
		} else {
			$assoc = pg_fetch_assoc($res);
			$ret = $assoc['config_value'];
		}

		return $ret;
	}

	public function logEvent($type, $event, $status = null)
	{
		if(!$this->getSystemConfig('global_bot_messaging_log')) {
			return;
		}

		$event = pg_escape_string($event);
		$sql = "INSERT INTO bot_messaging_log (type, raw_msg, status)
				VALUES ('$type', '$event', '$status')
				";
		doSQLcmd($this->conn, $sql);
	}

	function isProfileExist($botID, $userID)
	{
		$found = 0;
		$sql = "SELECT user_id FROM bot_social_contacts
			    WHERE user_id = '$userID' AND bot_id = '$botID'";
		$result = pg_query($this->conn, $sql);
		if($result) {
			if(pg_num_rows($result) > 0){
				$found = 1;
			}
		}

		return $found;
	}

	public function getSendURL($type) {
		$ret = '';
		$sql = "SELECT id, send_url, bot_type_id, created
				FROM bot_send_api_urls
				WHERE bot_type_id = '$type'";
		$res = pg_query($this->conn, $sql);
		if(!$res){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = pg_fetch_assoc($res);
		}

		return $ret;
	}

	static function getBotInfoByUserID($conn, $userID){
		$ret = array();
		$userID = pg_escape_string($userID);
		$sql = "SELECT bot_id, profile_name, alias, c.created, c.modified, b.bot_type_id
			FROM bot_social_contacts c, bot_route b
			WHERE c.user_id = '$userID'
			AND c.bot_id = b.id
			";

		$result = pg_query($conn, $sql);
		if(!$result) {
			error_log(pg_last_error($conn));
		} else {
			while($row = pg_fetch_assoc($result)) {
				$ret['bot_id'] = $row['bot_id'];
				$ret['profile_name'] = $row['profile_name'];
				$ret['alias'] = $row['alias'];
				$ret['created'] = $row['created'];
				$ret['modified'] = $row['modified'];
				$ret['type'] = $row['bot_type_id'];
			}
		}

		return $ret;
	}

	function getWebHookByID($id = null) {
		$ret = '';
		$sql = "SELECT id, name, description, url FROM bot_webhooks";
		$id = pg_escape_string($id);
		if(isset($id) && !empty($id)) {
			$sql .= " WHERE id = $id";
		}

		$result = pg_query($this->conn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($conn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;

	}

	function formatCondArrayStr($k, $v){
		$prefix = explode('.', $k);
		if(strpos($k, 'from') != FALSE) {
			return $prefix[0] . ".created >= '" . pg_escape_string($v) . " 00:00:00'";
		} else if(strpos($k, 'to') != FALSE) {
			return $prefix[0] . ".created <= '" . pg_escape_string($v) . " 23:59:59'";
		} else {
			return "$k = '" . pg_escape_string($v) . "'";
		}
  	}

	function readBot($conditions = array()){
		$ret = '';
		$sql = "SELECT b.id, b.description,  b.status, b.webhook, b.bot_type_id,
				b.created, b.modified, b.total_retry, b.send_api_url_id,
				send_api_url.send_url, t.name as type_name, b.retry, b.timeout, b.outgoing_hook,
				array_to_string(array_agg(params.parameter ORDER BY params.seq ASC), ',') as parameter_key,
				array_to_string(array_agg(conf.parameter_value ORDER BY conf.seq ASC), ',') as parameter_value,
				array_to_string(array_agg(conf.id ORDER BY conf.seq ASC), ',') as config_id
				FROM bot_route b
				LEFT JOIN bot_types t ON (b.bot_type_id = t.id)
				LEFT JOIN bot_api_configs conf ON (b.id = conf.bot_id)
				LEFT JOIN bot_api_parameters params ON (conf.bot_api_parameter_id = params.id)
				LEFT JOIN bot_send_api_urls send_api_url ON (b.send_api_url_id = send_api_url.id)
				";

		if(!empty($conditions)){
			$sql .= " WHERE ";
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$sql .= "GROUP BY b.id, send_api_url.send_url, t.name";
		$sql .= " ORDER BY modified DESC";

		$result = pg_query($this->conn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;

	}

	function updateBot($conditions = array(), $update = array()){
		$ret = -1;

		$sql = "UPDATE bot_route";

		if(!empty($update)){
			  $update_str = implode(',', array_map(array($this, "formatCondArrayStr"), array_keys($update), array_values($update)));
			  $sql .= " SET $update_str";
		}

		if(!empty($conditions)) {
		  $sql .= ' WHERE ';
		  $sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->conn, $sql, $this->getSyncMode());

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function createBot($bot = array()){
		$ret = -1;
		$id = $this->idxGenerator('bot_route');
		$desc = pg_escape_string($bot['description']);
		$status = pg_escape_string($bot['status']);
		$type = pg_escape_string($bot['type']);
		$webhook = pg_escape_string($bot['webhook']);
		$totalRetry= pg_escape_string($bot['total_retry']);
		$timeout = pg_escape_string($bot['timeout']);
		$retry = pg_escape_string($bot['retry']);
		$sendURLID = pg_escape_string($bot['send_api_url_id']);
		$outhook = pg_escape_string($bot['outhook']);

		$sql = "INSERT INTO bot_route (id, description, bot_type_id, webhook, total_retry, status, send_api_url_id, timeout, retry, outgoing_hook)
				VALUES ('$id', '$desc', '$type', '$webhook', '$totalRetry', '$status', '$sendURLID', $timeout, $retry, '$outhook')";
		$ret = doSQLcmd($this->conn, $sql, $this->getSyncMode());

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = $id;
		}

		return $ret;
	}

	function deleteBot($conditions = array()){
		$ret = -1;
		$sql = "DELETE FROM bot_route";

		if(!empty($conditions)){
			$sql .= ' WHERE ';
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->conn, $sql, $this->getSyncMode());

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function createWebhook($webhook = array()){
		$ret = -1;
		#$id = $this->idxGenerator('bot_webhooks');
		#$name = pg_escape_string($webhook['name']);
		#$desc = pg_escape_string($webhook['description']);
		$type = pg_escape_string($webhook['type']);
		$rand = pg_escape_string($webhook['rand']);
		$url = $this->webhookURL . "/$type/$rand/";

		$status = $this->createWebhookPath($type, $rand);
		if($status) {
			$opt = array();
			$opt['botId'] = $webhook['botId'];
			$opt['type'] = $type;
			$opt['whId'] = $rand;
			$this->ExecuteSecondary('addwh', $opt);
			/*
			$sql = "INSERT INTO bot_webhooks (id, name, description, type, url, rand)
					VALUES ('$id', '$name', '$desc', '$type', '$url', '$rand')";

			$ret = doSQLcmd($this->conn, $sql);

			if($ret != '') {
				$ret = -1;
				error_log(pg_last_error($this->conn));
			} else {
				$ret = $id;
			}
			 */
			$ret = 0;
		}

		return $ret;
	}

	function readWebhook($conditions = array()){
		$ret = '';
		$sql = "SELECT w.id, w.name as webhook_name, w.description, w.url, w.type, t.name as type_name, w.created, w.modified, b.name as bot_name
				FROM bot_webhooks w
				LEFT JOIN bots b ON (b.webhook_id = w.id)
				LEFT JOIN bot_types t ON (t.id = w.type)
				";

		if(!empty($conditions)){
			$sql .= " WHERE ";
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$sql .= " ORDER BY modified DESC";

		$result = pg_query($this->conn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;

	}

	function updateWebhook($conditions = array(), $update = array()){
		$ret = -1;

		$deleteStatus = $this->deleteWebhookPath($conditions['oldtype'], $conditions['oldrand']);
		$createStatus = $this->createWebhookPath($update['newtype'], $update['newrand']);

		if($deleteStatus && $createStatus) {
			$opt = array();
			$opt['type'] = $conditions['oldtype'];
			$opt['whId'] = $conditions['oldrand'];
			$this->ExecuteSecondary('delwh', $opt);

			$opt['botId'] = $update['botid'];
			$opt['type'] = $update['newtype'];
			$opt['whId'] = $update['newrand'];
			$this->ExecuteSecondary('addwh', $opt);
			$ret = 0;
		}

		return $ret;
	}

	# input: type, rand
	function deleteWebhook($conditions = array()){
		$ret = -1;

		$status = $this->deleteWebhookPath($conditions['type'], $conditions['rand']);
		if($status) {
			$opt = array();
			$opt['type'] = $conditions['type'];
			$opt['whId'] = $conditions['rand'];
			$this->ExecuteSecondary('delwh', $opt);
			$ret = 0;
		}

		return $ret;
	}

	function createWebhookPath($type, $id){
		$firstLevel = $this->webhookPath . "/$type";
		$secondLevel = $this->webhookPath . "/$type/$id";

		if(!is_dir($firstLevel)) {
			mkdir($firstLevel);
		}

		if(!is_dir($secondLevel)) {
			mkdir($secondLevel);
		}

		if(!is_dir($secondLevel)) {
			$status = false;
			error_log("Directory ($secondLevel) not found");
		} else {
			$source = $this->webhookTemplatePath ."/$type/index.php";
			$target = $secondLevel . '/index.php';
			$status = copy($source, $target);

			if(!$status)
				error_log("Unable to copy webhook template file from ($source) to ($target)");
			else {
				$idFile = $secondLevel . '/bot.id';
				$status = file_put_contents($idFile, "empty");
			}

			$source2 = $this->webhookTemplatePath ."/$type/authorize.php";
			if(file_exists($source2)) {
				$target2 = $secondLevel . '/authorize.php';
				$status2 = copy($source2, $target2);
			}
		}

		return $status;
	}

	function deleteWebhookPath($type, $id){
		$secondLevel = $this->webhookPath . "/$type/$id";
		$phpTemplate = $secondLevel . "/index.php";
		$idFile = $secondLevel . "/bot.id";

		if(!file_exists($phpTemplate) || !file_exists($idFile)) {
			$status = true;
		} else {
			$status = unlink($phpTemplate);
			if($status){
				$status = unlink($idFile);
				if($type == 3) {
					unlink($secondLevel . '/authorize.php');
				}
				if($status){
					$status = @rmdir($secondLevel);
					if(!$status){
						error_log("Unable to remove directory($secondLevel)");
					}
				} else {
					error_log("Unable to unlink($idFile)");
				}
			} else {
				error_log("Unable to unlink($phpTemplate)");
			}
		}

		return $status;
	}


	function readSubscriber($conditions = array()){
		$ret = '';
		$sql = "SELECT s.id, s.user_id, s.bot_id, s.profile_name, s.alias,
				s.created, s.modified, b.description as bot_name, t.name as bot_type,
				s.mobile, s.email, access_token, channel_id, pb_id
				FROM bot_social_contacts s
				LEFT JOIN bot_route b ON (s.bot_id = b.id)
				LEFT JOIN bot_types t on (b.bot_type_id = t.id)
				";

		if(!empty($conditions)){
			$sql .= " WHERE ";
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$sql .= " ORDER BY s.modified DESC";

		$result = pg_query($this->conn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;

	}

	function updateSubscriber($conditions = array(), $update = array()){
		$ret = -1;

		$sql = "UPDATE bot_social_contacts";

		if(!empty($update)){
			  $update_str = implode(',', array_map(array($this, "formatCondArrayStr"), array_keys($update), array_values($update)));
			  $sql .= " SET $update_str";
		}

		if(!empty($conditions)) {
		  $sql .= ' WHERE ';
		  $sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->conn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function deleteSubscriber($conditions = array()){
		$ret = -1;

		$sql = "DELETE FROM bot_social_contacts";

		if(!empty($conditions)){
			$sql .= ' WHERE ';
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->conn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function removeAlertReceiver($idx){
		$alerts = [];
		$pings = [];
		$alert_ret = 0;
		$ping_ret = 0;
		$ret = -1;

		#Remove user from alert_list
		$sql = "SELECT idx, social_contact FROM alert_list WHERE social_contact LIKE '%$idx%'";
		$result = pg_query($this->conn, $sql);
		if($result){
			while($row = pg_fetch_assoc($result)){
				if($row['social_contact']) {
					$alert_idx = $row['idx'];
					$contacts = explode(",", $row['social_contact']);
					foreach($contacts as $c){
						if(strpos($c, $idx) === FALSE) {
							$alerts[] = $c;
						}
					}

					$new_contact = implode(",", $alerts);

					$sql = "UPDATE alert_list SET social_contact = '$new_contact' WHERE idx = '$alert_idx'";
					$alert_ret = doSQLcmd($this->conn, $sql);
					if($alert_ret != '') {
						$alert_ret = -1;
						error_log(pg_last_error($this->conn));
					}
				}
			}
		}

		#Remove user from ping_rules
		$sql = "SELECT idx, social_contact FROM ping_rules WHERE social_contact LIKE '%$idx%'";
		$result = pg_query($this->conn, $sql);
		if($result){
			while($row = pg_fetch_assoc($result)){
				if($row['social_contact']) {
					$ping_idx = $row['idx'];
					$contacts = explode(",", $row['social_contact']);
					foreach($contacts as $c){
						if(strpos($c, $idx) === FALSE) {
							$pings[] = $c;
						}
					}

					$new_contact = implode(",", $pings);

					$sql = "UPDATE ping_rules SET social_contact = '$new_contact' WHERE idx = '$ping_idx'";
					$ping_ret = doSQLcmd($this->conn, $sql);
					if($ping_ret != '') {
						$ping_ret = -1;
						error_log(pg_last_error($this->conn));
					}
				}
			}
		}

		if($alert_ret == 0 && $ping_ret == 0){
			$ret = 0;
		}

		return $ret;

	}

	function readMsgByUDHID($udhid) {
		$messages = array();
		$sql = "SELECT raw_message FROM message_status WHERE udhid = '$udhid' ORDER BY msgid";
		$result = pg_query($this->conn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			while($row = pg_fetch_assoc($result)){
				$messages[] = $row['raw_message'];
			}
		}

		return $messages;

	}

	function readBotLog($conditions = array()){
		$ret = '';
		$sql = "SELECT bms.id as bms_id, bms.target_user_id as user_id, ms.raw_message as msg, bms.attempt_count,
				bms.status, to_char(bms.created, 'YYYY-MM-DD HH24:MI:SS') as created, to_char(bms.modified, 'YYYY-MM-DD HH24:MI:SS') as modified,
				b.id, c.alias, t.name as type_name, b.description as bot_name, bms.api_response, c.mobile, ms.udhid
				FROM bot_message_status bms
				LEFT JOIN bot_route b ON (bms.bot_id = b.id)
				LEFT JOIN bot_social_contacts c ON (bms.target_user_id = c.user_id)
				LEFT JOIN bot_types t ON (b.bot_type_id = t.id)
				LEFT JOIN message_status ms ON (bms.msgid = ms.msgid)
				";

		if(!empty($conditions)){
			$sql .= " WHERE ";
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$sql .= " ORDER BY bms.modified DESC";

		$result = pg_query($this->conn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				$msg = '';
				foreach($row as $k => $v) {
					if($k == 'udhid' && $v != '0') {
						#is a long message, so need to append complete msg
						$messages = $this->readMsgByUDHID($v);
						if(count($messages) > 0) {
							foreach($messages as $m) {
								$msg .= $m;
							}
						}
					}
					$ret[$i][$k] = $v;
				}

				#overwrite msg field if it is a long message
				if($msg != '') {
					$ret[$i]['msg'] = $msg;
				}

				$i++;
			}
		}

		return $ret;

	}

	function deleteBotLog($conditions = array()){
		$ret = -1;

		$sql = "DELETE FROM bot_message_status";

		if(!empty($conditions)){
			$sql .= ' WHERE ';
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->conn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function createAPIConfigs($params = array()){
		$ret = -1;
		$id = $this->idxGenerator('bot_api_configs');
		$bot_id = pg_escape_string($params['bot_id']);
		$param_id = pg_escape_string($params['parameter_id']);
		$param_value = pg_escape_string($params['parameter_value']);
		$seq = pg_escape_string($params['seq']);


		$sql = "INSERT INTO bot_api_configs (id, bot_api_parameter_id, parameter_value, bot_id, seq)
				VALUES ('$id', '$param_id', '$param_value', '$bot_id', '$seq')";

		$ret = doSQLcmd($this->conn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = $id;
		}

		return $ret;
	}

	function readAPIParameters($conditions = array()) {
		$ret = '';
		$sql = "SELECT id, parameter, seq, display, bot_type_id
				FROM bot_api_parameters";

		if(!empty($conditions)){
			$sql .= " WHERE ";
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$sql .= " ORDER BY seq ASC";

		$result = pg_query($this->conn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;


	}

	function updateAPIConfigs($conditions = array(), $update = array()){
		$ret = -1;

		$sql = "UPDATE bot_api_configs";

		if(!empty($update)){
			  $update_str = implode(',', array_map(array($this, "formatCondArrayStr"), array_keys($update), array_values($update)));
			  $sql .= " SET $update_str";
		}

		if(!empty($conditions)) {
		  $sql .= ' WHERE ';
		  $sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->conn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function deleteAPIConfigs($conditions = array()){
		$ret = -1;

		$sql = "DELETE FROM bot_api_configs";

		if(!empty($conditions)){
			$sql .= ' WHERE ';
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->conn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function createBotSubscription($subs = array()) {
		$ret = -1;
		$id = $this->idxGenerator('bot_subscription_log');
		$bot_id = pg_escape_string($subs['bot_id']);
		$profile_name = pg_escape_string($subs['profile_name']);
		$msg = pg_escape_string($subs['msg']);
		$raw_msg = pg_escape_string($subs['raw_msg']);
		$user_id = pg_escape_string($subs['user_id']);
		
		//$sql0 = "insert into edwin_temp ( datas ) values ( 'come in createBotSubscription' )";
		//$ret0 = doSQLcmd($this->conn, $sql0);

		#Get bot detail#
		$bot["b.id"] = $bot_id;
		$botInfo = $this->readBot($bot);

		$bot_type = $botInfo[0]['type_name'];
		$bot_name = $botInfo[0]['description'];
		$outgoing_webhook = $botInfo[0]['outgoing_hook'];

		$sql = "INSERT INTO bot_subscription_log (id, bot_id, bot_name, bot_type, user_id, profile_name, msg, raw_msg)
				VALUES ('$id', '$bot_id', '$bot_name', '$bot_type', '$user_id','$profile_name', '$msg', '$raw_msg');";
		$ret = doSQLcmd($this->conn, $sql);
		
		//once received incoming mim message, check keyword/survey
		//user_id=mobile number for whatsapp
		$statusSurvey = $this->survey( $user_id, $msg, $bot_id );
		
		# Post event to outgoing webhook
		if (isset($subs['event']) && filter_var($outgoing_webhook, FILTER_VALIDATE_URL) !== false) {
			$p['bot_id'] = $bot_id;
			$p['recipient_user_id'] = $user_id;
			$mimpb = $this->readMIMPhonebook($p);

			$content['event'] = $subs['event'];
			$content['id'] = $mimpb[0]['id'];
			$content['userid'] = $user_id;
			$content['name'] = $mimpb[0]['recipient_name'];
			$content['type'] = ($mimpb[0]['type'] == '1' ? 'Individual' : 'Group');
			$content['origin'] = $bot_type;
			$content['route'] = $bot_name;
			$content['message'] = $msg;
			$httpArray['http']['content'] = http_build_query($content);

			$httpArray['http']['method'] = 'POST';
			$httpArray['http']['header'] = 'Content-type: application/x-www-form-urlencoded';

			list($status, $response) = $this->request(trim($outgoing_webhook), $httpArray);
			if (substr($status, 9, 3) != '200') {
				error_log('Outgoing webhook res: '.$status.' - '.$response);
			}
		}

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = $id;
		}

		return $ret;
	}
	
	function survey( $user_id, $msg, $bot_id ){
		
		$webappdbname = 'momdb';
		$webappdbuser = 'msg';
		$webappdbpass = 'msg!@#$%';
		$webappconstr = "host=localhost port=5432 dbname=$webappdbname user=$webappdbuser password=$webappdbpass";
		$temp_conn = pg_connect($webappconstr);
		
		if(!($temp_conn)){
			die("Database Connection Failed: ".pg_last_error($temp_conn));
		}
		
		$dbname = 'spooldb';
		$dbuser = 'msg';
		$dbpass = 'msg!@#$%';
		$constr = "host=localhost port=5432 dbname=$dbname user=$dbuser password=$dbpass";
		$temp_conn2 = pg_connect($constr);
		
		if(!($temp_conn2)){
			die("Database Connection Failed: ".pg_last_error($temp_conn2));
		}
		
		//$sql0 = "insert into edwin_temp ( datas ) values ( 'running survey function' )";
		//$ret0 = pg_query($temp_conn, $sql0);
		
		$msg_array = explode(" ", $msg );
		$keyword_received = strtolower($msg_array[0]);
		
		$plus_string = substr( $user_id, 0, 1); 
		
		if( $plus_string == "+" ){
			
			$user_id_with_plus = $user_id;
			
			$string_len = strlen( $user_id );
			$user_id_without_plus = substr( $user_id, 1, $string_len); 
			
		}else{
			
			$user_id_without_plus = $user_id;
			
			$user_id = "+".$user_id;
			
			$user_id_with_plus = $user_id;
		}
		
		//go find this keyword
		$keywords = "";
		//$sql1 = "select a.*, b.keyword from campagin_survey_outbox a, campaign_mgnt b where a.campagin_id = b.campaign_id and mobile_no = '$user_id' and b.campaign_start_date <= 'now()' and campaign_end_date >= 'now()' and campaign_type = '2' and campaign_status = 'active'";
		
		$sql1 = "select a.*, b.keyword from campagin_survey_outbox a, campaign_mgnt b where a.campagin_id = b.campaign_id and mobile_no in ( '$user_id_with_plus', '$user_id_without_plus' ) and b.campaign_start_date <= 'now()' and campaign_end_date >= 'now()' and campaign_type = '2' and campaign_status = 'active'";
		
		//$sql0 = "insert into edwin_temp ( datas ) values ( '". pg_escape_string( "keyword_received: $keyword_received" ) ."' )";
		//$result0 = pg_query($temp_conn, $sql0);
					
		//$sql0 = "insert into edwin_temp ( datas ) values ( '". pg_escape_string( print_r( $sql1, true ) ) ."' )";
		//$ret0 = pg_query($temp_conn, $sql0);
						
		$result1 = pg_query($temp_conn, $sql1);
		while($row1 = pg_fetch_assoc($result1)){
	
			$keywords = $row1['keyword'];
			$label = $row1['label'];
			$cby = $row1['cby'];
			
			if( $keywords ){
			
				//get keyword list to compare
				$keyword_list = array();
				$sql2 = "select keyword from mom_sms_response where id in ( $keywords )";
				$result2 = pg_query($temp_conn, $sql2);
				while($row2 = pg_fetch_assoc($result2)){
					$keyword_list[] = strtolower($row2['keyword']);
				}
				
				//$sql0 = "insert into edwin_temp ( datas ) values ( '". pg_escape_string( "keyword_list: " . print_r( $keyword_list, true ) ) ."' )";
				//$result0 = pg_query($temp_conn, $sql0);
			
				//keyword received matched
				if( in_array( $keyword_received, $keyword_list ) ){
					
					//$sql0 = "insert into edwin_temp ( datas ) values ( '". pg_escape_string( "keyword_received matched: $keyword_received" ) ."' )";
					//$result0 = pg_query($temp_conn, $sql0);
					
					$survey_outbox_id = $row1['id'];
					$campagin_id = $row1['campagin_id'];
					$department = $row1['department'];
					$keywords = $row1['keywords'];
					$type = $row1['type'];
					$send_mode = $row1['send_mode'];
					$bot_id = $row1['bot_id'];
					$full_msg_received = $msg;
					
					//check only can insert one time for each mobile and survey
					$total_exited = 0;
					$sql5 = "select count(*) as total from campagin_survey_inbox where mobile_no = '$user_id' and campagin_id = '$campagin_id'";
					
					$result5 = pg_query($temp_conn, $sql5);
					if($row5 = pg_fetch_assoc($result5)){
						$total_exited = $row5['total'];
					}
				
					if( $total_exited == 0 ){//each mobile only can reply one time
						
						$sql3 = "insert into campagin_survey_inbox ( campagin_id, department, keywords, type, send_mode, bot_id, mobile_no, keyword_received, full_msg_received, received_via ) values ( '$campagin_id', '". pg_escape_string($department) ."', '".pg_escape_string($keywords)."', '$type', '$send_mode', '$bot_id', '$user_id', '".pg_escape_string($keyword_received)."', '".pg_escape_string($full_msg_received)."', 'mim' )";
						
						//$sql0 = "insert into edwin_temp ( datas ) values ( '". pg_escape_string( print_r( $sql3, true ) ) ."' )";
						//$ret0 = pg_query($temp_conn, $sql0);
					
						$result3 = pg_query($temp_conn, $sql3);
					
					}
					
					//get this keyword
					$autoreply = $autoreply_msg = "";
					
					$sql4 = "select * from mom_sms_response where lower(keyword) = '".strtolower($keyword_received)."'";
					
					$result4 = pg_query($temp_conn, $sql4);
					if($row4 = pg_fetch_assoc($result4)){
						$autoreply = $row4['autoreply'];
						$autoreply_msg = $row4['autoreply_msg'];
					}
					
					if( $autoreply == "1" && $autoreply_msg && $total_exited == 0 ){//each mobile only can reply one time
						
						//$sql0 = "insert into edwin_temp ( datas ) values ( 'auto reply: yes | send_mode: ".$send_mode."' )";
						//$ret0 = pg_query($temp_conn, $sql0);
						
						if( $send_mode == "sms_mim" || $send_mode == "mim" ){
							
							//get bot details
							$bot = $this->getBotDetails( $temp_conn, $temp_conn2, $bot_id );
							$bot_datas = $this->getBotByBotID( $temp_conn, $temp_conn2, $bot_id );
							
							if( $bot_datas['bot_type_id'] == 13 ){//whatsapp DC
								
								$datas['id'] = $bot['campaignId'];//IO campaign_id
								$datas['secret'] = $bot['campaignSecret'];//IO Campaign Secret
							
								//$datas['subscribers'] = explode("\n", stripslashes(trim($mobile_list)));
								$datas['subscribers'] = array( $user_id );
	
								$datas['type'] = "text";
								$datas['message'] = $autoreply_msg;//if not use tpl, send all text
								$datas['channelId'] = 'c5144c3f-76eb-4972-850d-ca2993c9c53d';//hardcoded channelId
								
								$datas = json_encode($datas);
								
								$sendAsTemplate = "0";
								$priority = '5';

								error_log("Datas: $datas, Token: ".$bot['campaignAccessToken']);
							
								$WDC_result = $this->SendMsgViaWhatsappDC( $temp_conn2, $temp_conn, $datas, $bot['campaignAccessToken'], $bot_id, $autoreply_msg, $priority, $department, $label, $campagin_id, $sendAsTemplate, $cby, $bot_datas, $survey_outbox_id );
								
							}
						
						}else{
							
							//only sms
							
						}
						
					}
				
				}
			
			}
			
		}
		
	}
	
	function SendMsgViaWhatsappDC( $spdbconn, $dbconn, $datas, $campaignAccessToken, $bot_id, $sms_txt, $priority, $department, $label, $campaign_id, $sendAsTemplate, $cby, $bot, $survey_outbox_id ){
		
		$url = "https://api.sendquick.io/channel-subscribers/send";
		$server_prefix = "C";
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $datas);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			//'Authorization: 7wMVLp35hSQfhnscfwrID554OXwAbMiURhqJ8CTUMcUvfZFY34VppZwB1Ob9MEvo',//IO Campaign Access Token
			'Authorization: ' . $campaignAccessToken,//IO Campaign Access Token
			'Content-Type: application/json',
		));

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

		$api_result = curl_exec($curl);
		
		//$api_result2 = json_encode($api_result);
		$api_result3 = json_decode($api_result);
		
		if(  isset($api_result3->data->broadcastId) ){
			
			$msg_status = "Y";
			$returns["status"] = "sent";
			$returns["broadcastId"] = ( $api_result3->data->broadcastId ? $api_result3->data->broadcastId : "" );
			$api_response = $returns["broadcastId"];
			$send_status = "Y";
			$outgoing_log_message_status = "Y";
			
		}else{
			
			$msg_status = "F";
			$returns["status"] = "error";
			$returns["code"] = ( $api_result3->error->statusCode ? $api_result3->error->statusCode : "" );
			$returns["message"] = ( $api_result3->error->message ? $api_result3->error->message : "" );
			$api_response = $returns["message"];
			$send_status = "F";
			$outgoing_log_message_status = "F";
			//echo "fail";
		}
	
		$ori_datas = json_decode( $datas );
	
		$api_sent_received = array( "sent"=> $ori_datas, "received"=>$api_result3 );
		
		//insert into bot_message_status and message_status
		foreach( $ori_datas->subscribers as $key => $mno ){
		
			$msgid = date("YmdHIs") . mt_rand(100000, 999999);
			$msg_from = $cby . " (" . $server_prefix . ")";
			$msg_content = $sms_txt;
			$msg_type = "W";
			$charset = "1";
			//$bot = $this->getBotByBotID( $bot_id );

			//$bot_msg_status_id = getSequence($spdbconn,'bot_message_status_id_seq');
			$sql00 = "select nextval('bot_message_status_id_seq')";
			$result00 = pg_query($spdbconn,$sql00);
			$c = pg_fetch_array($result00,0);
			$bot_msg_status_id = $c[0];
			
			$sql1 = "insert into message_status ( msgid, mobile_numb, msg_from, msg_content, msg_status, msg_type, completed_dtm, charset, raw_message, priority ) values ( '$msgid', '$mno', '$msg_from', '$msg_content', '$msg_status', '$msg_type', now(), '$charset', '$msg_content', '$priority' )";
			
			$sql2 = "insert into bot_message_status ( id, msgid, bot_id, type, target_user_id, api_response, status, send_process_flag, remote_msgid, send_template, priority, api_sent_received, survey_outbox_id ) values ( '$bot_msg_status_id', '$msgid', '$bot_id', '$bot[bot_type_id]', '$mno', '$api_response', '$send_status', '1', '".$returns["broadcastId"]."', '$sendAsTemplate', '$priority', '".pg_escape_string(print_r( $api_sent_received, true ))."', '$survey_outbox_id'  )";
			
			//if mim need insert one copy, so that in mom log page can view
			//$t = getSQLresult($dbconn, "select nextval('message_trackid') as trackid");
			$sql00 = "select nextval('message_trackid') as trackid";
			$result00 = pg_query($dbconn,$sql00);
			$t = pg_fetch_array($result00,0);
			
			$trackid = "C".date('His').$t['trackid'];
			
			//$outgoing_id = getSequenceID($dbconn,'outgoing_logs_outgoing_id_seq');
			$seq = $t['trackid'];
			$code_prefix = "E";//outgoing_logs_outgoing_id_seq prefix
			$outgoing_id = $server_prefix . $code_prefix . strftime('%y%j%H%M', time()) . sprintf("%06d", $seq);
			
			if( isset( $_SESSION['userid'] ) ){
				$sent_by = $_SESSION['userid'];
			}else{
				$sent_by = 'bot';
			}
			
			$sql3 = "insert into outgoing_logs (outgoing_id,msgid, priority,trackid,sent_by,department,mobile_numb,message,message_status,completed_dtm, modem_label, campaign_id, bot_message_status_id ) values ('".$outgoing_id."', '".$msgid."', '".$priority."','".$trackid."','".pg_escape_string( $sent_by )."','".pg_escape_string($department)."','".$mno."','$msg_content','$outgoing_log_message_status', now(), '".$label."', '".$campaign_id."', '$bot_msg_status_id')";
			
			/*
			$all_sql = array( $sql1, $sql2, $sql3 );
			$sql0 = "insert into edwin_temp ( datas ) values ( '". pg_escape_string( print_r( $all_sql, true ) ) ."' )";
			$result0 = pg_query($dbconn, $sql0);
			*/
			
			$row1 = pg_query($spdbconn,$sql1);
			if( $row1 ){
				
				$row2 = pg_query($spdbconn,$sql2);
				if( $row2 ){
					
					$row3 = pg_query($dbconn,$sql3);
					//if( !$row3 ){
						//echo $sql3;
					//}
					
				}
				
			}
			
			
		}
	
		return $returns;
		
	}
	
	function getBotDetails( $temp_conn, $spdbconn, $bot_id ){
	
		$sqlcmd = "select a.bot_api_parameter_id, a.parameter_value, b.parameter from bot_api_configs a, bot_api_parameters b where a.bot_api_parameter_id = b.id and a.bot_id = '$bot_id'";
		
		$result = pg_query($spdbconn, $sqlcmd);
		while($row = pg_fetch_assoc($result)){
			
			$returns[ $row["parameter"] ] = $row["parameter_value"];
			
		}
		
		return $returns;
		
	}
	
	function getBotByBotID( $temp_conn, $spdbconn, $bot_id ){
	
		$sqlcmd = "select * from bot_route where id = '$bot_id'";
					
		$result = pg_query($spdbconn, $sqlcmd);
		if($row = pg_fetch_assoc($result)){
			
		}
	
		return $row;
		
	}
	
	function readBotSubscription($conditions = array()){
		$ret = '';
		$sql = "SELECT l.id, bot_name, bot_type, l.profile_name, l.user_id, msg, to_char(l.created, 'YYYY-MM-DD HH24:MI:SS') as created, c.mobile
				FROM bot_subscription_log l
				LEFT JOIN bot_social_contacts c ON (l.user_id = c.user_id)
				";

		if(!empty($conditions)){
			$sql .= " WHERE ";
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$sql .= " ORDER BY l.created DESC";

		$result = pg_query($this->conn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;

	}

	function deleteBotSubscription($conditions = array()){
		$ret = -1;

		$sql = "DELETE FROM bot_subscription_log";

		if(!empty($conditions)){
			$sql .= ' WHERE ';
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->conn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function addIncomingSMS($in) {
		$ret = -1;
		
		$s['user_id'] = $in['user_id'];
		$subscriber = $this->readSubscriber($s);
		# Get parent phonebook idx
		$pbId = $subscriber[0]['pb_id'];
		$p['idx'] = $pbId;
		$phonebook = $this->readPhonebook($p);

		$content['mno'] = trim($phonebook[0]['mobile_numb']);
	
		$content['smsc'] = trim($in['smsc']);
		$content['imei'] = trim($in['imei']);
		$content['txt'] = trim($in['msg']);
		$httpArray['http']['content'] = http_build_query($content);
		$httpArray['http']['method'] = 'POST';
		$httpArray['http']['header'] = 'Content-type: application/x-www-form-urlencoded';

		$context  = stream_context_create($httpArray);
		$response = file_get_contents('http://127.0.0.1/cmd/system/api/receivesms.cgi',false,$context);

		$tmp_stat = explode(':',$response);
		if (trim($tmp_stat[0]) == 'OK') {
			$tmp_res = explode(';',trim($tmp_stat[1]));
			$msgid = trim($tmp_res[0]);
			$sql = "UPDATE incoming_sms SET bot_contact_userid='".pg_escape_string($s['user_id'])."' WHERE left(msgid,-1)='$msgid'";
			doSQLcmd($this->conn,$sql);
			$ret = $msgid;
		}

		return $ret;
	}

	##### Immediate Bot Response ###################

	function requestUserData($uid, $botid) {
		$msg = "/requestuserdata";
		return $this->processSlashCmd($uid, $botid, $msg);
	}

	function isSlashCmd($msg){
		$m = explode(" ", $msg);
		$cmdList = $this->getCmdList();

		if(in_array($m[0], $cmdList)) {
			return true;
		} else {
			return false;
		}
	}

	function getCmdList(){
		$sql = "SELECT cmd FROM bot_commands";
		$result = pg_query($this->conn, $sql);
		return pg_fetch_all_columns($result, 0);
	}

	function processSlashCmd($uid, $botid, $msg) {
		$ret = array();
		$status = -1;
		$m = explode(" ", $msg);
		$cmd = $m[0];
		array_shift($m);

		$me = $this->slashMe($uid, $botid);
		$replacement['userid'] = $uid;
		$replacement['profilename'] = $me[0]['alias'];
		$replacement['mobile'] = $me[0]['mobile'];
		$replacement['email'] = $me[0]['email'];

		switch(strtolower($cmd)) {

		case '/requestuserdata':
			$c['b.id'] = $botid;
			$bot = $this->readBot($c);
			$msg = '';
			if(!$me[0]['mobile']) {
				$msg .= "\n\nTo complete the registration process, please follow steps below:";
				$msg .= "\n\n1) To update mobile number, type \n/setmobile number";
			}
			if(!$me[0]['email']) {
				$msg .= "\n\n2) To update email, type \n/setemail a@b.com";
			}

			$status = 0;

			break;

		case '/me':
			$c['b.id'] = $botid;
			$bot = $this->readBot($c);
			if(!empty($me)) {
				$status = 0;
				$msg = $this->getMessage('meCmd', $replacement);
			} else {
				$msg = $this->getMessage('userNotFound', $replacement);
			}

			break;

		case '/setemail':
			if(!empty($me)) {
				preg_match_all("/\|(.*?)>/", $m[0], $matches);
				if(!empty($matches[1])){
					$email = $matches[1][0];
				} else {
					$email = $m[0];
				}

				$email = filter_var($email, FILTER_SANITIZE_EMAIL);
				if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){
					$msg = $this->getMessage('failInvalidSetEmailCmd', $replacement);
				} else {
					$m[0] = $email;
					$status = $this->slashUpdateSubscriber($uid, $botid, 'email', $m);
					if($status == 0) {
						$msg = $this->getMessage('successSetEmailCmd', $replacement);
					} else {
						$msg = $this->getMessage('failSetEmailCmd', $replacement);
					}
				}
			} else {
				$msg = $this->getMessage('userNotFound', $replacement);
			}
		break;

		case '/unsetemail':
			if(!empty($me)) {
				$m[0] = "";
				$status = $this->slashUpdateSubscriber($uid, $botid, 'email', $m);
				if($status == 0) {
					$msg = $this->getMessage('successUnsetEmailCmd', $replacement);
				} else {
					$msg = $this->getMessage('failUnsetEmailCmd', $replacement);
				}
			} else {
				$msg = $this->getMessage('userNotFound', $replacement);
			}
		break;


		case '/setmobile':

			if(!empty($me)) {
				if(preg_match('/^\+*[0-9]{8,15}+$/', $m[0])) {
					$status = $this->slashUpdateSubscriber($uid, $botid, 'mobile', $m);
					if($status == 0) {
						$msg = $this->getMessage('successSetMobileCmd', $replacement);
					} else {
						$msg = $this->getMessage('failSetMobileCmd', $replacement);
					}
				} else {
					$msg = $this->getMessage('failInvalidSetMobileCmd', $replacement);
				}
			} else {
				$msg = $this->getMessage('userNotFound', $replacement);
			}
		break;

		case '/unsetmobile':
			if(!empty($me)) {
				$m[0] = "";
				$status = $this->slashUpdateSubscriber($uid, $botid, 'mobile', $m);
				if($status == 0) {
					$msg = $this->getMessage('successUnsetMobileCmd', $replacement);
				} else {
					$msg = $this->getMessage('failUnsetMobileCmd', $replacement);
				}
			} else {
				$msg = $this->getMessage('userNotFound', $replacement);
			}
		break;

		case '/?':
		case '/help':
			$result = $this->slashHelp();
			$msg = "Available commands:\n\n";
			$count = 0;
			foreach($result as $r){
				$msg .= ++$count . ". " . $r['cmd'] . "\n";
				$msg .= $r['description']. "\n\n";
			}
			$status = 0;

		break;


		}

		$ret['status'] = $status;
		$ret['msg'] = $msg;
		return $ret;

	}

	function slashHelp() {
		$sql = "SELECT cmd, description FROM bot_commands WHERE cmd NOT IN ('/unsetemail', '/unsetmobile') ORDER BY cmd";
		$result = pg_query($this->conn, $sql);
		return pg_fetch_all($result);

	}

	function slashUpdateSubscriber($uid, $botid, $field, $args) {
		$c['user_id'] = $uid;
		$c['bot_id'] = $botid;
		$u[$field] = trim($args[0]);

		$status = -1;
		$delStatus = -1;
		$updateStatus = -1;
		#update subscriber first
		$status = $this->updateSubscriber($c, $u);
		#then check whether mno matches any mno, if yes, delete current phonebook contact and set bot subscriber id to main pb id
		if($field == 'mobile') {
			$idx = $this->checkPhonebook(trim($args[0])); //get parent pb index
			if($idx != -1) {
				#delete new phonebook entry
				$u['user_id'] = $uid;
				$u['bot_id'] = $botid;
				$sub = $this->readSubscriber($u);
				if($sub[0]['pb_id']) {
					$p['idx'] = $sub[0]['pb_id']; //child pb index
					if($p['idx'] != $idx) {
						$delStatus = $this->deletePhonebook($p);
					} else {
						$delStatus = 1;
						$cond['idx'] = $sub[0]['pb_id'];
						$update['mobile_numb'] = trim($args[0]);
						$updateStatus = $this->updatePhonebook($cond, $update);
					}

					if (isset($update['mobile_numb'])) {
						unset($update['mobile_numb']);
					}
					$update['pb_id'] = $idx;
					$update['mobile'] = trim($args[0]);
					$updateStatus = $this->updateSubscriber($u, $update);
				}
			} else {
				$delStatus = 1;
				$u['user_id'] = $uid;
				$u['bot_id'] = $botid;
				$sub = $this->readSubscriber($u);
				if($sub[0]['pb_id']) {
					$cond['idx'] = $sub[0]['pb_id'];
					$update['mobile_numb'] = trim($args[0]);
					$updateStatus = $this->updatePhonebook($cond, $update);
				}
			}
		} else if($field == 'email') {
			$c['user_id'] = $uid;
			$c['bot_id'] = $botid;
			$sub = $this->readSubscriber($c);
			if($sub[0]['pb_id']) {
				#has phonebook record, then update phonebook email
				$info = $this->getPhonebookInfo();
				$cond['idx'] = $sub[0]['pb_id'];
				$update[$info['EMAIL_FIELD']] = trim($args[0]);
				$updateStatus = $this->updatePhonebook($cond, $update);
			}
		}

		$s = ($status) ? 1 : 0;
		return $s;
	}

	function createPhonebook($p) {
		$info = $this->getPhonebookInfo();
		switch($info['APPLIANCE']) {
			case 'Entera':
				$pbID = $this->createEnteraPhonebook($p);
			break;
			case 'Avera':
				$pbID = $this->createAveraPhonebook($p);
			break;
			default:
				$pbID = $this->createEnteraPhonebook($p);
			break;
		}

		return $pbID;
	}

	function updatePhonebook($c = array(), $u = array()) {
		$info = $this->getPhonebookInfo();
		switch($info['APPLIANCE']) {
			case 'Entera':
				$status = $this->updateEnteraPhonebook($c, $u);
			break;
			case 'Avera':
				$status = $this->updateAveraPhonebook($c, $u);
			break;
			default:
				$status = $this->updateEnteraPhonebook($c, $u);
			break;
		}

		return $status;
	}

	function deletePhonebook($p = array()) {
		$info = $this->getPhonebookInfo();
		switch($info['APPLIANCE']) {
			case 'Entera':
				$status = $this->deleteEnteraPhonebook($p);
			break;
			case 'Avera':
				$status = $this->deleteAveraPhonebook($p);
			break;
			default:
				$status = $this->deleteEnteraPhonebook($p);
			break;
		}

		return $status;
	}

	function readPhonebook($p = array()) {
		$info = $this->getPhonebookInfo();
		switch($info['APPLIANCE']) {
			case 'Entera':
				$phonebooks = $this->readEnteraPhonebook($p);
			break;
			case 'Avera':
				$phonebooks = $this->readAveraPhonebook($p);
			break;
			default:
				$phonebooks = $this->readEnteraPhonebook($p);
			break;
		}

		return $phonebooks;
	}

	function checkPhonebook($mno) {
		$ret = -1;
		$phonebooks = $this->readPhonebook();
		foreach($phonebooks as $pb) {
			if($this->isSameMobileNum(trim($mno), trim($pb['mobile_numb']))) {
				$ret = $pb['idx'];
				break;
			}
		}

		return $ret;
	}

	function slashMe($uid, $botid) {
		$c['user_id'] = $uid;
		$c['bot_id'] = $botid;
		return $this->readSubscriber($c);
	}


	##### End of immediate Bot Response ###################

	function readBotTypeByInClause($field, $values){
		$ret = '';
		$sql = "SELECT id, name FROM bot_types";
		$sql .= " WHERE $field IN ($values)";
		$sql .= " ORDER BY id";

		$result = pg_query($this->conn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;
	}

	function readBotType($conditions = null) {
		$ret = '';
		$sql = "SELECT id, name FROM bot_types";

		if(!empty($conditions)){
			$sql .= " WHERE ";
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$sql .= " ORDER BY id";

		$result = pg_query($this->conn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;

	}

	function getSubscribedBots($conditions = array()) {
		$ret = '';
		$sql = "SELECT bt.name, bsc.mobile
				FROM bot_social_contacts bsc, bot_route br, bot_types bt
				WHERE bsc.bot_id = br.id
				AND br.bot_type_id = bt.id";

		if(!empty($conditions['pbid'])){
			$sql .= " AND bsc.pb_id = '" . $conditions['pbid'] . "'";
		}


		if(!empty($conditions['mobile'])){
			$sql .= " AND bsc.mobile = '" . $conditions['mobile'] . "'";
		}

		if(!empty($conditions['email'])){
			$sql .= " AND bsc.email = '" . $conditions['email'] . "'";
		}

		$sql .= " ORDER BY bt.name";

		$result = pg_query($this->conn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;

	}

	function encryptAveraPassword($passwd) {
		$cipher = 'blowfish';
		$key = 'tlxep10jr';
		$iv = 'talariax';
		$encrypted_passwd = mcrypt_encrypt($cipher, $key, $passwd, MCRYPT_MODE_CBC,$iv);
		$encrypted_passwd = base64_encode($encrypted_passwd);
		return $encrypted_passwd;
	}

	static function getPhonebookInfo() {
		$appliance = `cat /home/msg/conf/model`;
		$appliance = trim($appliance);
		$info = array();

		$info['APPLIANCE'] = $appliance;
		switch($appliance) {
			case 'Entera':
				$info['SQL'] = "SELECT user_name, mobile_numb, email_address, modem_label, idx FROM addressbook";
				$info['TABLE'] = 'addressbook';
				$info['FIELDS'] = 'user_name, mobile_numb';
				$info['MNO_FIELD'] = 'mobile_numb';
				$info['EMAIL_FIELD'] = 'email_address';
				break;
			case 'Avera':
				$info['SQL'] = "SELECT user_name, mobile_numb, email, idx FROM users";
				$info['TABLE'] = 'users';
				$info['FIELDS'] = 'user_name, mobile_numb';
				$info['MNO_FIELD'] = 'mobile_numb';
				$info['EMAIL_FIELD'] = 'email';
				break;
			default:
				$info['SQL'] = "SELECT user_name, mobile_numb, email_address, modem_label, idx FROM addressbook";
				$info['TABLE'] = 'addressbook';
				$info['FIELDS'] = 'user_name, mobile_numb';
				$info['MNO_FIELD'] = 'mobile_numb';
				$info['EMAIL_FIELD'] = 'email_address';

				break;
		}

		return $info;
	}

	function readAveraPhonebook($conditions = null) {
		$ret = '';
		$sql = "SELECT * FROM users";

		if(!empty($conditions)){
			$sql .= " WHERE ";
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$sql .= " ORDER BY created_dtm DESC";

		$result = pg_query($this->nmDBConn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->nmDBConn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;

	}

	function readEnteraPhonebook($conditions = null) {
		$ret = '';
		$sql = "SELECT user_name, mobile_numb, email_address, modem_label, idx FROM addressbook";

		if(!empty($conditions)){
			$sql .= " WHERE ";
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$sql .= " ORDER BY idx";

		$result = pg_query($this->filterDBConn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->filterDBConn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;
	}

	function readMIMPhonebook($conditions = null) {
		$ret = '';
		$sql = "SELECT id,recipient_name,recipient_user_id,type,bot_id,bot_type_id FROM mim_addressbook";

		if(!empty($conditions)){
			$sql .= " WHERE ";
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$result = pg_query($this->filterDBConn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->filterDBConn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;
	}

	function check_duplicate($conn, $name,$mno) {
		$chksql = "select idx from addressbook where user_name='".$name."' and mobile_numb='".$mno."'";
		$res = pg_query($conn, $chksql);
		$rows = pg_num_rows($res);
		return $rows;
	}

	function cleanName($name) {
		$name = str_replace(' ', '_', $name);
		$name = preg_replace('/[^A-Za-z0-9\_]/', '', $name);

		return preg_replace('/_+/', '_', $name);
	}

	function updateEnteraPhonebook($conditions = array(), $update = array()) {
		$ret = -1;

		$sql = "UPDATE addressbook";

		if(!empty($update)){
			  $update_str = implode(',', array_map(array($this, "formatCondArrayStr"), array_keys($update), array_values($update)));
			  $sql .= " SET $update_str";
		}

		if(!empty($conditions)) {
		  $sql .= ' WHERE ';
		  $sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->filterDBConn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->filterDBConn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function updateAveraPhonebook($conditions = array(), $update = array()) {
		$ret = -1;

		$sql = "UPDATE users";

		if(!empty($update)){
			  $update_str = implode(',', array_map(array($this, "formatCondArrayStr"), array_keys($update), array_values($update)));
			  $sql .= " SET $update_str";
		}

		if(!empty($conditions)) {
		  $sql .= ' WHERE ';
		  $sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->nmDBConn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->nmDBConn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function deleteAveraPhonebook($conditions = array()){
		$ret = -1;
		$sql = "DELETE FROM users";

		if(!empty($conditions)){
			$sql .= ' WHERE ';
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->nmDBConn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->nmDBConn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function deleteEnteraPhonebook($conditions = array()){
		$ret = -1;
		$sql = "DELETE FROM addressbook";

		if(!empty($conditions)){
			$sql .= ' WHERE ';
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->filterDBConn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->filterDBConn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function isSameMobileNum($mno1, $mno2) {
		$length = 0;
		if($mno1 == $mno2) {
			return true;
		} else if(!$mno1 || !$mno2) {
			return false;
		} else {
			$l1 = strlen($mno1);
			$l2 = strlen($mno2);
			if($l1 > $l2) {
				//$mno2 = (int)$mno2;
				$length = strlen($mno2);
			} else {
				//$mno1 = (int)$mno1;
				$length = strlen($mno1);
			}

			$m1 = substr($mno1, $length * -1);
			$m2 = substr($mno2, $length * -1);
			if($m1 == $m2) {
				return true;
			} else {
				return false;
			}
		}
	}

	function checkAveraUsersExist($conn,$login_id,$username)
	{
		$sql = "SELECT count(*) FROM users WHERE login_id='" . $login_id . "' OR user_name='$username'";
		$result = pg_query($conn, $sql);
		if( !$result ){
			error_log($sql . ' -- ' . pg_last_error($conn));
			return 0;
		}
		$arr = pg_fetch_array($result, 0);
		$count = $arr[0];
		return $count;
	}

	function checkAveraUserMobileExist($conn,$login_id,$usermobile)
	{
		$sql = "SELECT count(*) FROM users WHERE login_id !='" . $login_id . "' AND mobile_numb='$usermobile'";
		$result = pg_query($conn, $sql);
		if( !$result ){
			error_log($sql . ' -- ' . pg_last_error($conn));
			return 0;
		}
		$arr = pg_fetch_array($result, 0);
		$count = $arr[0];
		return $count;
	}

	function createAveraPhonebook($pb) {
		$pbIdx = -1;
		$login_id = $user_name = strtolower($this->cleanName($pb['name']));
		$passwd = $this->encryptAveraPassword('password');
		$mobile_numb = isset($pb['mno']) ? $pb['mno'] : '';
		$created_by = 'bot';
		$sqlcmd = '';

		$email = '';
		$type = 'U';
		$designation = '';
		$group_name = '';
		$new_group_name = '';
		$alert_suspend = 'N';
		$s_id = 'N';
		$s_day1 = 'N';
		$s_day2 = 'N';
		$s_day3 = 'N';
		$s_day4 = 'N';
		$s_day5 = 'N';
		$s_day6 = 'N';
		$s_day0 = 'N';
		$s_day1_time = '';
		$s_day2_time = '';
		$s_day3_time = '';
		$s_day4_time = '';
		$s_day5_time = '';
		$s_day6_time = '';
		$s_day0_time = '';
		$s_date_flag = 'N';
		$s_date_list = '';
		$s_leave_date_list = '';
		$s_customize_flag = 'N';

		if($this->checkAveraUsersExist($this->nmDBConn, $login_id, $user_name)) {
			error_log("Avera user with same Login ID and Username found");
			$login_id .= '_x';
			$user_name .= '_x';
		}

		$newIdx = 'AVERA'.time().getSequence($this->nmDBConn,"users_id_seq");
		$sqlcmd = "INSERT INTO users " .
			"(idx, login_id, user_name, created_by, passwd, type," .
			"mobile_numb, email, " .
			"group_name, designation, " .
			"alert_suspend,shift_id,s_day1, s_day2, s_day3, s_day4, s_day5, s_day6, s_day0, " .
			"s_day1_time, s_day2_time, s_day3_time, s_day4_time, s_day5_time, s_day6_time, s_day0_time, " .
			"s_date_flag, s_date_list,s_leave_date_list,s_customize_flag ) values ('" .
			pg_escape_string($newIdx) . "', '" .
			pg_escape_string($login_id) . "', '" .
			pg_escape_string($user_name) . "', '" .
			pg_escape_string($created_by) . "', '" .
			pg_escape_string($passwd) . "', '" .
			pg_escape_string($type) . "', ";

		if(strlen($mobile_numb) == 0){
			$sqlcmd .= "NULL, ";
		} else {
			$sqlcmd .= "'" . pg_escape_string($mobile_numb) . "', ";
		}

		if(strlen($email) == 0){
			$sqlcmd .= "NULL, ";
		} else {
			$sqlcmd .= "'" . pg_escape_string($email) . "', ";
		}

		$sqlcmd .= "'" . pg_escape_string($group_name) . "', '" .
		pg_escape_string($designation) . "', '" .
		pg_escape_string($alert_suspend) . "', '" .
		pg_escape_string($s_id) . "', '" .
		pg_escape_string($s_day1) . "', '" .
		pg_escape_string($s_day2) . "', '" .
		pg_escape_string($s_day3) . "', '" .
		pg_escape_string($s_day4) . "', '" .
		pg_escape_string($s_day5) . "', '" .
		pg_escape_string($s_day6) . "', '" .
		pg_escape_string($s_day0) . "', '" .
		pg_escape_string($s_day1_time) . "', '" .
		pg_escape_string($s_day2_time) . "', '" .
		pg_escape_string($s_day3_time) . "', '" .
		pg_escape_string($s_day4_time) . "', '" .
		pg_escape_string($s_day5_time) . "', '" .
		pg_escape_string($s_day6_time) . "', '" .
		pg_escape_string($s_day0_time) . "', '" .
		pg_escape_string($s_date_flag) . "', '" .
		pg_escape_string($s_date_list) . "', '" .
		pg_escape_string($s_leave_date_list) . "', '" .
		pg_escape_string($s_customize_flag) . "')";

		$res = doSQLcmd($this->nmDBConn, $sqlcmd);
		if (!empty($res)) {
			error_log(pg_last_error($this->nmDBConn));
		} else {
			$pbIdx = $newIdx;
		}

		return $pbIdx;
	}

	function createEnteraPhonebook($pb) {
		$ret = -1;

		$name = isset($pb['name']) ? trim($pb['name']) : '';
		$mno = isset($pb['mno']) ? trim($pb['mno']) : '';
		$email = isset($pb['email']) ? trim($pb['email']) : '';
		$group = isset($pb['group']) ? $pb['group'] : '';
		$new_grp = isset($pb['new_grp']) ? $pb['new_grp'] : '';
		$shift = isset($pb['shift']) ? $pb['shift'] : '';
		$modem = isset($pb['modem']) ? $pb['modem'] : '';
		$profile_idx = isset($pb['profile_idx']) ? $pb['profile_idx'] : '';
		$match = isset($pb['match']) ? $pb['match'] : '';
		$type = isset($pb['local']) ? $pb['local'] : '';

		if (empty($match)) {
			$match = 'f';
		}

		$newIdx = Get_Prefix().getSequence($this->filterDBConn,"addressbook_idx_sequence");
		$name = $this->cleanName($name);
		$cmd = "INSERT INTO addressbook
				(idx,user_name,mobile_numb,email_address,shift_idx,created_dtm,modem_label,match_flag,type)
				values ('".$newIdx."','".pg_escape_string($name)."','".pg_escape_string($mno)."',
				'".pg_escape_string($email)."','".pg_escape_string($shift)."','now()',
				'".pg_escape_string($modem)."','".$match."','".pg_escape_string($type)."')";
		$res = doSQLcmd($this->filterDBConn,$cmd);

		if (!empty($res)) {
			error_log(pg_last_error($this->filterDBConn));
		} else {
			$ret = $newIdx;
		}

		return $ret;
	}

	function createMIMPhonebook($pb) {
		$profileName = isset($pb['name']) ? trim($pb['name']) : '';
		$userID = isset($pb['userID']) ? trim($pb['userID']) : '';
		$type = isset($pb['type']) ? $pb['type'] : '1';
		$botID = isset($pb['botID']) ? $pb['botID'] : '';
		$botTypeID = isset($pb['botTypeID']) ? $pb['botTypeID'] : '';

		$sql = "INSERT INTO mim_addressbook (id,recipient_name, recipient_user_id, type, bot_id, bot_type_id)
				VALUES (md5(random()::text || clock_timestamp()::text)::uuid,'$profileName','$userID',$type,'$botID','$botTypeID');";
		$res = doSQLcmd($this->filterDBConn, $sql);

		if (!empty($res)) {
			error_log(pg_last_error($this->filterDBConn));
		}
	}

	function readAutoResponse($conditions = array()){
		$ret = '';
		$sql = "SELECT event, response FROM bot_auto_responses";

		if(!empty($conditions)){
			$sql .= " WHERE ";
			$sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$result = pg_query($this->conn, $sql);
		if(!$result){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = array();
			$i = 0;
			while($row = pg_fetch_assoc($result)){
				foreach($row as $k => $v) {
					$ret[$i][$k] = $v;
				}

				$i++;
			}
		}

		return $ret;

	}

	function updateAutoResponse($conditions = array(), $update = array()) {
		$ret = -1;

		$sql = "UPDATE bot_auto_responses";

		if(!empty($update)){
			  $update_str = implode(',', array_map(array($this, "formatCondArrayStr"), array_keys($update), array_values($update)));
			  $sql .= " SET $update_str";
		}

		if(!empty($conditions)) {
		  $sql .= ' WHERE ';
		  $sql .= implode(' AND ', array_map(array($this, "formatCondArrayStr"), array_keys($conditions), array_values($conditions)));
		}

		$ret = doSQLcmd($this->conn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = 0;
		}

		return $ret;
	}

	function getMessage($event, $replacement) {
		$ret = '';
		$cond['event'] = $event;
		$msg = $this->readAutoResponse($cond);
		$newMsg = $this->replaceTmpl($msg[0]['response'], $replacement);
		return $newMsg;
	}

	function convertKeyword($arr) {
		return '-' . strtoupper($arr) . '-';
	}

	function replaceTmpl($content, $replacement){
		$find = array_keys($replacement);
		$find = array_map(array($this, "convertKeyword"), $find);
		$replace = array_values($replacement);
		$newContent = str_replace($find, $replace, $content);
		return html_entity_decode($newContent);
	}

	function getMIMSubscription($pbIdx) {
		$ret = '';

		$sql = "SELECT array_to_string(array_agg(bt.name ORDER BY bt.name), ',') as name
			FROM bot_social_contacts bsc
			LEFT JOIN bot_route br ON (br.id = bsc.bot_id)
			LEFT JOIN bot_types bt on (br.bot_type_id = bt.id)
			WHERE pb_id = '$pbIdx'
			GROUP BY pb_id";

		$result = pg_query($this->conn, $sql);

		if($ret != '') {
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = pg_fetch_all($result);
		}

		return $ret;
	}

	function dockerAPI($host, $method, $url) {
		//error_log("-------------------------------------------");
		//error_log("$method $host $url");
		$ret = "";
		$header = "";
		$code = "";
		$fs = fsockopen('unix:///var/run/docker.sock');
		if(!$fs) {
			error_log('Docker API: Unable to connect to Docker socket');
			exit();
		}

		fwrite($fs, "$method $url HTTP/1.1\r\nHOST: $host\r\nCONNECTION: Close\r\n\r\n");
		$response = '';

		//Real value
		while (!feof($fs)) {
			$response .= fread($fs, 256);
		}
		fclose($fs);

		//error_log('HEADER ONLY: ');
		//error_log(print_r(json_encode($header), 1));

		//error_log('CODE ONLY: ');
		//error_log($code);
		//error_log('RESPONSE ONLY: ');
		var_dump($response[strlen($response) - 5]);
		if($response && $response[strlen($response)-5] == "0") {
			$response = substr($response, 0, -8);
		}
		$response = substr($response, strpos($response,"\r\n\r\n")+4);

		$pos = strpos($response, '[');

		$response = substr($response,$pos);

		$response = json_decode($response,true);
		//error_log("DECODED: " . print_r($response,1));
		var_dump($response);

		//error_log("-------------------------------------------");
		return $response;
	}


	function dockerGetContainerInfo($name) {
		$ret = array();

		$resp = $this->dockerAPI($this->dockerAPIHost, 'GET', '/containers/json?filters=%7B%22name%22%3A%5B%22'.$name.'%22%5D%7D');

		foreach($resp as $r) {
			$ret['id'] = $r['Id'];
			$ret['name'] = $r['Names'][0];
			$ret['publicport'] = @$r['Ports'][0]['PublicPort'];
			$ret['privateport'] = $r['Ports'][0]['PrivatePort'];
			$ret['state'] = $r['State'];
			$ret['status'] = $r['Status'];
		}

		return $ret;
	}

	function getContainerStackInfo($botId) {
		$status['waweb'] = $this->dockerGetContainerInfo($botId.'_waweb_1');
		$status['wacore'] = $this->dockerGetContainerInfo($botId.'_wacore_1');
		$status['db'] = $this->dockerGetContainerInfo($botId.'_db_1');

		return $status;
	}

	function getContainerInfo($botId) {
		return $this->dockerGetContainerInfo($botId);
	}

	function dockerCheckPortStatus($port) {
		$ret = array();

		$resp = $this->dockerAPI($this->dockerAPIHost, 'GET', '/containers/json?filters=%7B%22expose%22%3A%5B%22'.$port.'%22%5D%7D');

		foreach($resp as $r) {
			$ret['id'] = $r['Id'];
			$ret['name'] = $r['Names'][0];
			$ret['publicport'] = @$r['Ports'][0]['PublicPort'];
			$ret['privateport'] = $r['Ports'][0]['PrivatePort'];
			$ret['state'] = $r['State'];
			$ret['status'] = $r['Status'];
		}

		return $ret;
	}

	function checkPortStatus($port) {
		return $this->dockerCheckPortStatus($port);
	}

	function downContainer($botId) {
		$dockerConfigPath = $this->dockerConfigPath;
		$cmd = "COMPOSE_HTTP_TIMEOUT=2400 docker-compose -p $botId -f $dockerConfigPath" ."docker-compose-whatsapp-$botId.yml down -v > /dev/null 2>&1 &";

		$output = `$cmd`;
		$opt = array();
		$opt['botId'] = $botId;
		$this->ExecuteSecondary('delwa', $opt);
	}

	function upContainer($botId) {
		$dockerConfigPath = $this->dockerConfigPath;
		$source = $dockerConfigPath . 'docker-compose-whatsapp-default.yml';
		$target = $dockerConfigPath . "docker-compose-whatsapp-$botId.yml";
		if (copy($source, $target)) {
			$ports = $this->readLastUsedPort();

			$waWebPortStatus = $this->checkPortStatus($ports['waweb'] + 1);
			$dbPortStatus = $this->checkPortStatus($ports['db'] + 1);
			$containerInfo = $this->getContainerInfo($botId);

			//error_log("CONTAINER STATUS: ");
			//error_log(print_r($containerInfo, 1));

			//error_log("WA WEB PORT STATUS: ");
			//error_log(print_r($waWebPortStatus, 1));

			//error_log("DB STATUS: ");
			//error_log(print_r($dbPortStatus, 1));

			if (count($containerInfo) === 0 &&
				count($waWebPortStatus) === 0 &&
				count($dbPortStatus) === 0) {
				$search = array();
				$replace = array();
				$search[] = '-WAWEBPORT-';
				$search[] = '-DBPORT-';
				$replace[] = $ports['waweb'] + 1;
				$replace[] = $ports['db'] + 1;
				$this->replaceConfigPort($search, $replace, $target);
				$cmd = "COMPOSE_HTTP_TIMEOUT=2400 docker-compose -p $botId -f $dockerConfigPath" ."docker-compose-whatsapp-$botId.yml up > /dev/null 2>&1 &";

				$ports['waweb'] = $ports['waweb'] + 1;
				$ports['db'] = $ports['db'] + 1;
				$this->createLastUsedPort($botId, $ports);
				$output = `$cmd`;

				$opt = array();
				$opt['botId'] = $botId;
				$opt['webPort'] = $ports['waweb'];
				$opt['dbPort'] = $ports['db'];
				$this->ExecuteSecondary('addwa', $opt);
			} else {
				if (count($containerInfo) !== 0) {
					error_log('Container already created.');
				}
				if (count($waWebPortStatus) !== 0) {
					error_log('WA Web Port already in used.');
				}
				if (count($dbPortStatus) !== 0) {
					error_log('DB Port already in used.');
				}
			}

			return $ports['waweb'];

		} else {
			error_log("Unable to copy docker-compose file from $source to $target");
			return -1;
		}
	}

	function replaceConfigPort($search, $replace, $yml) {
		$content = file_get_contents($yml);
		$newContent = str_replace($search, $replace, $content);
		file_put_contents($yml, $newContent);
	}

	function readLastUsedPort() {
		$defaultWaWebPort = '9191';
		$defaultDbPort = '33061';

		$sql = "SELECT port FROM whatsapp_last_ports WHERE type = 'waweb' ORDER BY created DESC LIMIT 1";
		$result1 = pg_query($this->conn, $sql);
		if($result1 === FALSE){
			error_log($sql . ' -- ' . pg_last_error($this->conn));
			error_log('Unable to obtain waweb port for docker stack');
			exit;
		} else {
			$value = pg_fetch_array($result1, 0);
			$ports['waweb'] = $value ? $value[0]: $defaultWaWebPort;
		}

		$sql = "SELECT port FROM whatsapp_last_ports WHERE type = 'db' ORDER BY created DESC LIMIT 1";
		$result2 = pg_query($this->conn, $sql);
		if($result2 === FALSE ){
			error_log($sql . ' -- ' . pg_last_error($this->conn));
			error_log('Unable to obtain db port for docker stack');
			exit;
		} else {
			$value = pg_fetch_array($result2, 0);
			$ports['db'] = $value ? $value[0]: $defaultDbPort;
		}

		error_log('Last used ports: ');
		error_log(print_r($ports, 1));

		return $ports;
	}

	function createLastUsedPort($botId, $ports) {
		foreach($ports as $type=>$port) {
			$sql = "INSERT INTO whatsapp_last_ports (bot_id, type, port, created, modified)
				VALUES ('$botId', '$type', '$port', now(), now())";

			doSQLcmd($this->conn, $sql);
		}
	}

	function login($url, $username, $password, $firstTime = false) {
		$header[] = 'Content-Type: application/json';

		if($firstTime) {
			$header[] = "Authorization: Basic ". base64_encode("$username:secret");
			$c['new_password'] = $password;
			$httpArray['http']['content'] = json_encode($c);
		} else {
			$header[] = "Authorization: Basic ". base64_encode("$username:$password");
			$httpArray['http']['content'] = json_encode('{}');
		}

		$httpArray['http']['method'] = 'POST';
		$httpArray['http']['header'] = implode("\r\n", $header);

		$httpArray['ssl'] = array('verify_peer' => false, 'verify_peer_name' => false);

		$context = stream_context_create($httpArray);

		$response = file_get_contents($url . '/' . $this->readWhatsAppAPIVersion() . '/users/login', false, $context);

		if(strpos($http_response_header[0], '200') === false) {
			//http_response_code(500);
			error_log("Request failed: $http_response_header[0]");
		}

		$response = json_decode($response, true);

		$token['token'] = $response['users'][0]['token'];
		$token['expiry'] = $response['users'][0]['expires_after'];

		$ret = array();
		$ret['http_status'] = $http_response_header[0];
		$ret['http_response'] = $token;

		return $ret;
	}

	function request($url, $httpArray) {
		$httpArray['ssl'] = array('verify_peer' => false, 'verify_peer_name' => false);

		#Check Proxy Setting
		$proxy = $this->getProxyConfig();
		if ($proxy['status']) {
			if (!$this->checkExcludeIP(trim($proxy['exclude_iplist']),$url)) {
				$host = trim($proxy['server_ip']);
				$port = trim($proxy['port']);
				$user = trim($proxy['username']);
				$pass = trim($proxy['passwd']);
				$httpArray['http']['proxy'] = "tcp://$host:$port";
				$httpArray['http']['request_fulluri'] = true;
				if(strlen($user) > 0){
					$auth = base64_encode("$user:$pass");
					if (isset($httpArray['http']['header'])) {
						$httpArray['http']['header'] = $httpArray['http']['header']."\r\nProxy-Authorization: Basic $auth";
					} else {
						$httpArray['http']['header'] = "Proxy-Authorization: Basic $auth";
					}
				}
			}
		}

		$context = stream_context_set_default($httpArray);
		$response = file_get_contents($url, false, $context);

		$response = json_decode($response, true);
		$status = $http_response_header[0];

		return array($status, $response);
	}

	function requestCode($url, $token, $countryCode, $phoneNum, $method, $cert) {
		$header[] = 'Content-Type: application/json';
		$header[] = "Authorization: Bearer $token";

		$content['cc'] = $countryCode;
		$content['phone_number'] = $phoneNum;
		$content['method'] = $method;
		$content['cert'] = $cert;
		$httpArray['http']['content'] = json_encode($content);

		$httpArray['http']['method'] = 'POST';
		$httpArray['http']['header'] = implode("\r\n", $header);

		list($status, $response) = $this->request($url . '/' . $this->readWhatsAppAPIVersion() . '/account', $httpArray);

		$ret = array();
		$ret['http_status'] = $status;
		$ret['http_response'] = $response;

		return $ret;
	}

	function verifyCode($url, $token, $code) {
		$header[] = 'Content-Type: application/json';
		$header[] = "Authorization: Bearer $token";

		$content['code'] = $code;
		$httpArray['http']['content'] = json_encode($content);

		$httpArray['http']['method'] = 'POST';
		$httpArray['http']['header'] = implode("\r\n", $header);

		list($status, $response) = $this->request($url . '/' . $this->readWhatsAppAPIVersion() . '/account/verify', $httpArray);

		$ret = array();
		$ret['http_status'] = $status;
		$ret['http_response'] = '';

		return $ret;
	}

	function updateAppSettings($url, $token, $settings) {
		$header[] = 'Content-Type: application/json';
		$header[] = "Authorization: Bearer $token";

		if (isset($settings['webhooks'])) {
			$content['webhooks'] = $settings['webhooks'];
		}

		$httpArray['http']['content'] = json_encode($content);

		$httpArray['http']['method'] = 'PATCH';
		$httpArray['http']['header'] = implode("\r\n", $header);

		list($status, $response) = $this->request($url . '/' . $this->readWhatsAppAPIVersion() . '/settings/application', $httpArray);

		$ret = array();
		$ret['http_status'] = $status;
		$ret['http_response'] = '';

		return $ret;
	}

	function readAppSettings($url, $token) {
		$header[] = 'Content-Type: application/json';
		$header[] = "Authorization: Bearer $token";

		$httpArray['http']['method'] = 'GET';
		$httpArray['http']['header'] = implode("\r\n", $header);

		list($status, $response) = $this->request($url . '/' . $this->readWhatsAppAPIVersion() . '/settings/application', $httpArray);

		$ret = array();
		$ret['http_status'] = $status;
		$ret['http_response'] = $response;

		return $ret;
	}

	function readAPIHealth($url, $token){
		$header[] = 'Content-Type: application/json';
		$header[] = "Authorization: Bearer $token";

		$httpArray['http']['method'] = 'GET';
		$httpArray['http']['header'] = implode("\r\n", $header);

		list($status, $response) = $this->request($url . '/' . $this->readWhatsAppAPIVersion() .'/health', $httpArray);

		$ret = array();
		$ret['http_status'] = $status;
		$ret['http_response'] = $response;

		return $ret;
	}

	function readWhatsAppAPIVersion() {
		$version = `cat /home/msg/conf/whatsapp_api_version`;
		$version = trim($version);
		return $version;
	}

	function backupWhatsAppSettings($url, $token, $password, $port, $botId) {
		$ret = 0;
		$header[] = 'Content-Type: application/json';
		$header[] = "Authorization: Bearer $token";

		$content['password'] = $password;
		$httpArray['http']['content'] = json_encode($content);

		$httpArray['http']['method'] = 'POST';
		$httpArray['http']['header'] = implode("\r\n", $header);

		list($status, $response) = $this->request($url . '/' . $this->readWhatsAppAPIVersion() . '/settings/backup', $httpArray);
		error_log(print_r($response,1));
		if (strpos($status, '200') !== false) {
					error_log('Backup successfully with content: ' . $response['settings']['data']);
					$opt = array();
					$opt['url'] = '/v1/settings/restore';
					$opt['port'] = $port;
					$opt['token'] = $token;
					$opt['botId'] = $botId;
					$opt['settings'] = $response['settings']['data'];
					$this->ExecuteSecondary('restorewa', $opt);
					$ret = 1;
				}

		return $ret;
	}

	function ExecuteSecondary($action, $opt = array()) {
		$cluster = $this->clusterObj;
		$remote_ip = $cluster->clusterconfig['remote_ip'];
		$port = 9180;
		$path = $cluster->pathlistconfig->$action;
		$ret = 0;

		if ($cluster->checkClusterFlag() == 1) {
			if ($cluster->getClusterMode() == 2) {
				if (!isset($path) || strlen(trim($path))==0){
					error_log("PATH not found!");
					return;
				}

				$url = "http://" . $remote_ip . ':' . $port . $path;
				$data = array('m' => '1');
				$whData = http_build_query($opt);
				$data_string = "m=1";
				$data_string .= '&' . $whData;

				error_log("NOTE Accessing URL: $url");
				try{
					$ch = curl_init();

					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);
					curl_setopt($ch, CURLOPT_TIMEOUT, 5);

					$res = curl_exec($ch);

					if (curl_errno($ch)) {
						error_log("Error: " . curl_error($ch));
						return;
					} else {
						error_log("RES:".$res);
						curl_close($ch);
					}
				} catch (Exception $e){
					error_log("ERROR:".$e->getMessage());
					return;
				}
			}
		}
	}

	function getProxyConfig() {
		$ret = '';
		$sql = "select status,server_ip,port,username,passwd,exclude_iplist from proxy where type='http'";
		$res = pg_query($this->conn, $sql);
		if(!$res){
			$ret = -1;
			error_log(pg_last_error($this->conn));
		} else {
			$ret = pg_fetch_assoc($res);
		}

		return $ret;
	}

	function checkExcludeIP($exclude_ip,$url)
	{
		$url1 = parse_url($url);
		$list = explode(',', $exclude_ip);

		foreach($list as $excluded){
			if ($url1['host'] == $excluded){
				return 1;
			}
		}

		return 0;
	}

	function updateMessageStatus($s) {
		
		$remoteMessageId = $s['broadcastId'];
		$IO_status = $status = $s['status'];
		$remark = $s['remark'];
		
		//get status code
		$sql00 = "select status_code, message_status from status_code_definition where io_remark = '$remark'";
		$res00 = pg_query( $this->webappDBConn, $sql00);
		$c00 = pg_fetch_array($res00, 0);
		$status_code = $c00[0];
		$message_status = $c00[1];
		
		//not found match IO remark
		if( !$message_status ){
			$message_status = "F";
			$status_code = "9999";
		}
		
		//if($status == 'F2') {
		if( $IO_status == "F" || $IO_status == "F2" ){
			$status = 'F';
		} else if($status == 'S') {
			$status = 'Y';
		}
		
		$outgoing_logs = $message_status;
		
		$sql0 = "insert into bot_message_all_status ( remote_msgid, return_data, status, api_response, io_status ) values ( '$remoteMessageId', '".print_r( $s, true )."', '$status', '$remark', '$IO_status' )";
		doSQLcmd($this->conn, $sql0);
		
		$sql = "UPDATE bot_message_status SET status = '$status', api_response = '$remark', modified = now() WHERE remote_msgid= '$remoteMessageId'";
		doSQLcmd($this->conn, $sql);

		$sql = "SELECT * FROM bot_message_status where remote_msgid = '$remoteMessageId'";
		$res = pg_query($this->conn, $sql);
		if(!$res) {
			error_log(pg_last_error($this->conn));
		} else {
			$ret = pg_fetch_assoc($res);
			$msgid = $ret['msgid'];
			
			if($msgid) {

				// assmi
				// check for portal or api
				$sqlCheckPortalorApi = "select * from outgoing_logs where msgid = '$msgid'";
				$resCheckPortalorApi = pg_query($this->webappDBConn, $sqlCheckPortalorApi);
				if(!$resCheckPortalorApi) {
					error_log(pg_last_error($this->webappDBConn));
				}else{
					$ret2 = pg_fetch_assoc($resCheckPortalorApi);
					$msgid2 = $ret2['msgid'];
					if($msgid2) {//portal outgoing_logs
						if( $IO_status == "F" || $IO_status == "F2" ){
					
							$sql01 = "select sent_by from outgoing_logs where msgid = '$msgid'";
							$res01 = pg_query( $this->webappDBConn, $sql01);
							$c01 = pg_fetch_array($res01, 0);
							$sender = $c01[0];
							
							//return mim quota
							$sql02 = "update quota_mnt set quota_left = quota_left + 1 where unlimited_quota = '0' and userid = '$sender'";
							doSQLcmd($this->webappDBConn, $sql02);
							
							$sql03 = "update bot_message_status set refunded = 'yes' where remote_msgid = '$remoteMessageId'";
							doSQLcmd($this->conn, $sql03);
							
							//$sqlZ = "insert into edwin_temp (datas) values ( '".pg_escape_string($sql02)."' )";
							//doSQLcmd($this->webappDBConn, $sqlZ);
						}
						//end refund
				
						//$sql = "UPDATE outgoing_logs SET message_status = '$status' WHERE msgid = '$msgid'";
						if( $outgoing_logs == "F" ){
							$sql = "UPDATE outgoing_logs SET message_status = '$outgoing_logs', delivered_dtm = null, status_code = '$status_code' WHERE msgid = '$msgid'";//refer zin sqoope msg 30/06/2020 1157AM
						}else{
							$sql = "UPDATE outgoing_logs SET message_status = '$outgoing_logs', delivered_dtm = 'now()', status_code = '$status_code' WHERE msgid = '$msgid'";//refer zin sqoope msg 30/06/2020 1157AM
						}
						
						doSQLcmd($this->webappDBConn, $sql);
					}else{//api appn_outgoing_logs
						if( $IO_status == "F" || $IO_status == "F2" ){
							$sql201 = "select clientid from appn_outgoing_logs where msgid = '" .$msgid. "'";
							$res201 = pg_query( $this->webappDBConn, $sql201);
							$c201 = pg_fetch_array($res201, 0);
							$clientid = $c201[0];

							$sql202 = "update appn_list set quota = quota + 1 where clientid = '$clientid'";
							doSQLcmd($this->webappDBConn, $sql202);
						}
						// return quota
						
						if( $outgoing_logs == "F" ){
							$sql = "UPDATE appn_outgoing_logs SET message_status = '$outgoing_logs', delivered_dtm = null, status_code = '$status_code' WHERE msgid = '$msgid'";
						}else{
							$sql = "UPDATE appn_outgoing_logs SET message_status = '$outgoing_logs', delivered_dtm = 'now()', status_code = '$status_code' WHERE msgid = '$msgid'";
						}

						doSQLcmd($this->webappDBConn, $sql);
					}
				}
			}
		}
	}
}
?>
