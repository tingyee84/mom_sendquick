<?php
require('lib/commonFunc.php');

switch ($_POST['mode']) {
	case "update":
		$lang = filter_input(INPUT_POST,'lang');
		if (empty($lang)) { $lang="EN"; }
		
		$sqlang = "UPDATE user_list SET language='".pg_escape_string($lang)."' WHERE userid='".pg_escape_string($_SESSION['userid'])."'";
		$result = pg_query($dbconn,$sqlang);
		
		$_SESSION['language'] = $lang;
		session_write_close();
        break;
    default:
        die("Invalid Command");
}
?>
