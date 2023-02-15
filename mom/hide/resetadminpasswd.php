<?php
require('../lib/db_webapp.php');

if (strcmp($_SERVER['REMOTE_ADDR'], '127.0.0.1')!== 0) {
	exit('Access deny');
}

$req = filter_input(INPUT_GET,'req');
if (strcmp(trim($req), 'secret')!== 0) {
	exit('Access deny');
}
	
try {
	$encrypted_passwd = getEncryptedPassword('admin123');

	$sqlcmd = "update user_list set ".
		"password='".pg_escape_string($encrypted_passwd)."',".
		"modified_by='sys',".
		"modified_dtm='now' where ".
		"userid='useradmin'";
	$result = pg_query($dbconn, $sqlcmd);

	if($result){
		if(pg_affected_rows($result)){
			$status = 'OK -- Reset successful';
			$status .= '<br>Password is: admin123';
		} else {
			error_log('No row affected! Invalid user?');
			$status = 'Failed -- Change failed';
		}
	} else {
		$status = 'Failed -- Change failed';
	}

	echo $status;

} catch (Exception $e) {
	$status = 'Failed -- Change failed';
	$status .= '<br>'.$e->getMessage();
	exit($status);
}

function getEncryptedPassword($password)
{
	$key = 'Shinjitsu wa Itsumo Hitotsu';
	
	$l = strlen($key);
	if ($l < 16) $key = str_repeat($key, ceil(16/$l));

	if ($m = strlen($password)%8) $password .= str_repeat("\x00",  8 - $m);

	$val = openssl_encrypt($password, 'BF-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);

	return base64_encode($val);
}
?>
