<?php
require('lib/db_spool.php');

$mno = filter_input(INPUT_POST,'mno');
$txt = filter_input(INPUT_POST,'txt');
$charset = filter_input(INPUT_POST,'charset');
$dtm = filter_input(INPUT_POST,'dtm');
$imei = filter_input(INPUT_POST,'imei');
$smsc = filter_input(INPUT_POST,'smsc');
$url = filter_input(INPUT_POST,'resp_url');

if (!empty($url)) {
	$response = HttpRequest(trim($url),
		json_encode(array(
			'mno' => trim($mno),
			'txt' => trim($txt),
			'charset' => trim($charset),
			'imei' => trim($imei),
			'smsc' => trim($smsc),
			'dtm' => trim($dtm)
		))
	);
	exit($response);
} else {
	exit('Invalid request: missing URL');
}

function HttpRequest($url, $data) 
{ 
	$ch = curl_init();
	$header[] = 'Content-Type: application/json';

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	
	$proxy = getProxyConfig();
	if ($proxy['status']) {
		if (checkExcludeIP(trim($proxy['exclude_iplist']),$url)) {
			$host = trim($proxy['server_ip']);
			$port = trim($proxy['port']);
			$user = trim($proxy['username']);
			$pass = trim($proxy['passwd']);
			curl_setopt($ch, CURLOPT_PROXY, "$host:$port");
			if(strlen($user) > 0){
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$user:$pass"); 
			}
		}
	}
	
	$result = curl_exec($ch);
	
	if($errno = curl_errno($ch)) {
		$errdesc = curl_strerror($errno);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$result = "$errdesc $httpcode";
		error_log("incoming_jsonapi cURL error($errno): $result");
	}
	
	curl_close($ch);
	
	return $result;
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
