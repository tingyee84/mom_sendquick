<?php
	$userid = $_SESSION['userid'];

	$access_xml = GetLanguage("check_user_access",$lang);
	$access_xml_msg_1 = (string)$access_xml->msg_1;
	$access_xml_msg_2 = (string)$access_xml->msg_2;
	$access_xml_msg_3 = (string)$access_xml->msg_3;

	if(!checkSession())
	{
		#$_SESSION['error_msg'] = "Sorry, Either You Have Not Login Or The Session Has Expired";
		$_SESSION['error_msg'] = $access_xml_msg_1;
		include("error.php");
		exit;
	}

	$_SESSION['access_string'] = getAccessString($userid);
	$access_string = $_SESSION['access_string'];
	$access_arr = explode(",", trim($access_string));

	//Address Book
	if($page_mode == 300)
	{
		if(!(in_array($chk_mode, $access_arr)))
		{
			$page_mode = '23';
		}
		else
		{
			$page_mode = $chk_mode;
		}
	}

	//Message Template
	if($page_mode == 400)
	{
		if(!(in_array($chk_mode, $access_arr)))
		{
			$page_mode = '24';
		}
		else
		{
			$page_mode = $chk_mode;
		}
	}

	//User Management
	if($page_mode == 200)
	{
		if(!(in_array($chk_mode, $access_arr)))
		{
			$page_mode = $chk_mode - 1;
			$dbl_mode = $chk_mode;
		}
		else
		{
			$page_mode = $chk_mode;
			$dbl_mode = $chk_mode - 1;
		}
	}

	//Logs Management
	if($page_mode == 500)
	{
		for($a=0; $a<count($logs_mode_arr); $a++)
		{
			$chk_mode = $logs_mode_arr[$a];
			if(in_array($chk_mode, $access_arr))
			{
				$page_mode = $chk_mode;
				break;
			}
		}
	}

	//Get Contacts From Address Book
	if($page_mode == 600)
	{
		if(!(in_array($chk_mode, $access_arr)))
		{
			$page_mode = $chk_mode - 1;
			$dbl_mode = $chk_mode;
		}
		else
		{
			$page_mode = $chk_mode;
			$dbl_mode = $chk_mode - 1;
		}
	}

	//Get Message Templates
	if($page_mode == 700)
	{
		if(!(in_array($chk_mode, $access_arr)))
		{
			$page_mode = $chk_mode - 1;
			$dbl_mode = $chk_mode;
		}
		else
		{
			$page_mode = $chk_mode;
			$dbl_mode = $chk_mode - 1;
		}
	}
	
	//Set User Access Mode For This Page
	$_SESSION['page_mode'] = $page_mode;

	if(!(in_array($page_mode, $access_arr)) && !(in_array($dbl_mode, $access_arr)) && ($page_mode != '25'))
	{
		// $_SESSION['error_msg'] = "Sorry, You Do Not Have The Access Right To View This Page ; $_SESSION['access_string']";
		//$_SESSION['error_msg'] = $access_xml_msg_2 . ' ' . $_SESSION['access_string'] . ' ; ' . $page_mode;
		$_SESSION['error_msg'] = $access_xml_msg_2;
		include("error.php");
		exit;
	}

	if(isUserAdmin($userid))
	{
		$department = 0;
	}
	else
	{
		$department = getUserDepartment($userid);
		if(strcmp($department, 0)==0 ) {
			#$_SESSION['error_msg'] = "Invalid Department For User!";
			$_SESSION['error_msg'] = $access_xml_msg_3;
			include("error.php");
			exit;
		}
	}
	$id_of_user = getUserID($userid);
?>
