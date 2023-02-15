<?php
ini_set('session.save_handler','files');
require_once('lib/class.cluster.php');
require_once('lib/class.session.php');

$isSecure = false;
if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off') {
	$isSecure = true;
} else if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] !='off') {
	$isSecure = true;
} else if (isset($_SERVER['HTTP_X_SSLREQ_HEADER']) && $_SERVER['HTTP_X_SSLREQ_HEADER'] == 'Stunnel-HAProxy') {
	$isSecure = true;
}

session_set_save_handler(new EncryptedSessionHandler('Shinjitsu wa Itsumo Hitotsu'),true);
session_set_cookie_params(0,'/mom/',null,$isSecure,true);
session_start();

$id = (isset($_SESSION['userid'])?$_SESSION['userid']:'ExN');
echo $id;
/*
Found out session gc maxlifetime is kinda unrealiable so going to extend this script. check-in by javascript setinterval
prepare a session variable called time_action to record unix_timestamp, this is responsible for
1) Check the session time by javascript but this script does not update session itself
2) Refresh session, to avoid overload, client (other pages that is) has an interval to allow fire up

use parameter to indicate two items, does value to let this script knows user is interacting (clicking that is)

each interacted, will get boolean as true

return value as updated (by auto check in but don't update session time), updatedd (by auto check in detect about time out),
if this return expired session, this will make other script detect and should lockout

after pass value and get result, get boolean as false

there is session js in header that keep track the time, but I gonna make it read according this 
*/
?>