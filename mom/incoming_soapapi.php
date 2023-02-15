<?php
require('lib/db_spool.php');
require('lib/nusoap.php');

$mno = filter_input(INPUT_POST,'mno');
$txt = filter_input(INPUT_POST,'txt');
$charset = filter_input(INPUT_POST,'charset');
$dtm = filter_input(INPUT_POST,'dtm');
$imei = filter_input(INPUT_POST,'imei');
$smsc = filter_input(INPUT_POST,'smsc');
$url = filter_input(INPUT_POST,'resp_url');
$method = filter_input(INPUT_POST,'method_name');

if (!empty($url) && !empty($method)) {
	try {
		$client = new nusoap_client(trim($url).'?wsdl', true);
		
		$proxy = getProxyConfig();
		if ($proxy['status']) {
			if (checkExcludeIP(trim($proxy['exclude_iplist']),trim($url))) {
				$client->setHTTPProxy(
							trim($proxy['server_ip']),
							trim($proxy['port']),
							trim($proxy['username']),
							trim($proxy['passwd']));
			}
		}
		
		$response = $client->call(trim($method), array(
			'mno' => trim($mno),
			'txt' => trim($txt),	  
			'charset' => trim($charset),    
			'dtm' => trim($dtm),
			'imei' => trim($imei),
			'smsc' => trim($smsc))
		);
		$error = $client->getError();
		if ($error) {
			error_log("incoming_soapapi nusoap error: $error");
			exit($error);
		} else {
			exit($response);
		}
	} catch (Exception $e) {
		error_log("incoming_soapapi nusoap error:".$e->getMessage());
		exit($e->getMessage());
	}
} else {
	exit('Invalid request: missing URL or Service Name');
}

function getProxyConfig() 
{
	global $spdbconn;
	$ret = '';
	$sql = "select status,server_ip,port,username,passwd,exclude_iplist from proxy where type='http'";
	$res = pg_query($spdbconn, $sql);
	if($res){
		$ret = pg_fetch_assoc($res);
	} else {
		$ret = -1;
		error_log(pg_last_error($spdbconn));
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
?>
