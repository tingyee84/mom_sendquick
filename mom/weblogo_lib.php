<?php
require "lib/commonFunc.php";

$msgstr = GetLanguage("sysconfig",$lang);
$mode = filter_input(INPUT_POST,'mode');
$config_file = '/home/msg/conf/image_header.xml';

switch ($mode) {
	case "view":
		if(file_exists($config_file)){	
			$xml = simplexml_load_file($config_file);
			if(empty($xml->image)){
				$val['image'] = '';
			}else{
				$val['image'] = '<img src="images/'.$xml->image.'" height="40" border="0" alt="Logo">';
			}
			$val['detail'] = $msgstr->latest_upload.trim($xml->date).' ('.$msgstr->by.trim($xml->user).')';
		} else {
			$val['image'] = '';
		}
	
		echo json_encode($val);
		break;
		
	case "upload":
		if(!file_exists($config_file)){	
			$xmlstr='<?xml version="1.0" encoding="UTF-8"?><config></config>';
			$newXML = new SimpleXMLElement($xmlstr);
			$newXML->asXml($config_file);
		}
		
		$imgfile = $_FILES['imgfile']['tmp_name'];
		$uploadname = preg_replace("/[^A-Za-z0-9-_\.]+/","",$_FILES['imgfile']['name']);
		$filepath = "images/".$uploadname;
		
		if(is_uploaded_file($imgfile)) {
			if (move_uploaded_file($imgfile, $filepath)) {
				$file_parts = pathinfo($filepath);
				
				if (!in_array($file_parts['extension'], Array('gif','jpeg','jpg','png','bmp'))){
					echo $msgstr->alert_2;
				} else {
					$xml = simplexml_load_file($config_file);
					$xml->image = basename($_FILES['imgfile']['name']);
					$xml->date = date('d-m-Y H:i:s');
					$xml->user = $_SESSION['userid'];
					$xml->asXml($config_file);
				}	
			} else {
				echo $msgstr->alert_6;
			}
		}  else {
			echo $msgstr->alert_6;
		}

		break;
	
	case "delete":

		$xml = simplexml_load_file($config_file);
		unlink("images/".$xml->image);
		
		$xml->image = '';
		$xml->date = '';
		$xml->user = '';
		$xml->asXml($config_file);
		
		break;

	default:
		die("Invalid Command");
}
?>
