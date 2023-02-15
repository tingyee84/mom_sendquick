<?php
	$access_xml = GetLanguage("check_user_access",$lang);
	$access_xml_msg_1 = (string)$access_xml->msg_1;
	$access_xml_msg_2 = (string)$access_xml->msg_2;
	$access_xml_msg_3 = (string)$access_xml->msg_3;

	$access_arr = explode(",", trim($_SESSION['access_string']));
	$id_of_user = getUserID($_SESSION['userid']);
	if (!in_array('8',$access_arr) && $_SESSION["needchgpwd"] == "yes") {
		array_push($access_arr,'8');
	}
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


	// Report
	if ($page_mode == 800) {
		if(!(in_array($chk_mode, $access_arr)))	{
			$page_mode = '59';

		}else{
			$page_mode = $chk_mode;
		}
	}

	//Set User Access Mode For This Page
	$_SESSION['page_mode'] = $page_mode;
	
	/*
	if(!(in_array($page_mode, $access_arr)) && !(in_array($dbl_mode, $access_arr)) && ($page_mode != '25'))
	{
		$_SESSION['error_msg'] = $access_xml_msg_2;
		header("Location: /mom2/error.php");
		exit;
	}
	*/
	
	if(isUserAdmin($_SESSION['userid']))
	{
		$department = 0;
	}
	else
	{
		$department = getUserDepartment($_SESSION['userid']);
		if(strcmp($department, 0)==0 ) {
			$_SESSION['error_msg'] = $access_xml_msg_3;
			header("Location: /mom2/error.php");
			exit;
		}
	}
?>
