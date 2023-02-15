<?php
define("TX_EMAILADDR","TX-001");
define("TX_URL","TX-002");
define("TX_POSTCODE","TX-003");
define("TX_SGLANDPHONE","TX-004");
define("TX_SGMOBILEPHONE","TX-005");
define("TX_SGPHONE","TX-006");
define("TX_MINMAX","TX-007");
define("TX_STRING","TX-008");
define("TX_INTEGER","TX-009");

$arrayfilter[TX_EMAILADDR] = '/(^_?[a-z0-9](_?[a-z0-9])+){1,64}@[a-z0-9](\.?[a-z0-9]){1,}\.([a-z]){2,}$/i';

// using localhost or 127.0.0.1 will be given ERROR! 
$arrayfilter[TX_URL] = "/^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:[/?#]\S*)?$/i";
$arrayfilter[TX_POSTCODE] = "/^\d{6}$/";
$arrayfilter[TX_SGLANDPHONE] = "/^(\+?65)?(3|6)\d{7}$/";
$arrayfilter[TX_SGMOBILEPHONE] = "/^(\+?65)?(8|9)\d{7}$/";
$arrayfilter[TX_SGPHONE] = "/^(\+?65)?(3|6|8|9)\d{7}$/";
$arrayfilter[TX_INTEGER] = "/^\d+$/";
function txvalidator($string,$check_type,$special="") {
    global $arrayfilter;
    switch($check_type) {
        case TX_EMAILADDR:
            return preg_match($arrayfilter[TX_EMAILADDR],$string);
            // Javascript doesn't have filter_var.
            // _ at the first character is ACCEPTABLE
            // if prefer simple, (^_?[a-z0-9](_?[a-z0-9])+){1,64}@[a-z0-9](\.?[a-z0-9]){1,}\.([a-z]){2,}$ 
            /*if (filter_var($string,FILTER_VALIDATE_EMAIL) != false) {
                return true;
            } else {
                return false;
            }*/
        break;
        case TX_URL:
            // return preg_match($arrayfilter[TX_URL],$string);
            return filter_var($string,FILTER_VALIDATE_URL);
        break;
        case TX_POSTCODE:
           return preg_match($arrayfilter[TX_POSTCODE],$string);
        break;
        case TX_SGLANDPHONE:
            return preg_match($arrayfilter[TX_SGLANDPHONE],str_replace("-","",$string));
        break;
        case TX_SGMOBILEPHONE:
            return preg_match($arrayfilter[TX_SGMOBILEPHONE],str_replace("-","",$string));
        break;
        case TX_SGPHONE:
            return preg_match($arrayfilter[TX_SGPHONE],str_replace("-","",$string));
        break;
        case TX_INTEGER:
            return preg_match($arrayfilter[TX_INTEGER],$string);
        break;
        case TX_MINMAX:
            // 
            if (is_numeric($string) == true) {
                $temp = intval($string);
                if ($special!="") {
                    $range = explode(",",$special);
                    if (count($range) != 2) {
                        return -1;
                    } else if ($temp >= $range[0] && $temp <= $range[1]) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            } else {
                return 0;
            }
        break;
        case TX_STRING:
            if (!empty(trim($special))) {
                if(strcmp($special,"SPACE") == 0){                   
                    return preg_match('/^[a-z0-9]{1,}((\s)*([a-z0-9])*)*[\s]?$/i',$string);
                }else if(strcmp($special,"ALL") != 0){
                    $temp = array();
                    for($i = 0 ; $i < strlen($special) ; $i++) {
                        $temp[$i] = preg_quote(substr($special,$i,1));
                    }
                    $str = implode("|",array_unique($temp)); 
                    if(strlen($str) == 1){
                        return preg_match('/^[a-z0-9]{1,}(('.$str.')*([a-z0-9])*)*['.$str.']?$/i',$string);
                    }else{
                        return preg_match('/^[a-z0-9]{1,}(('.$str.')*([a-z0-9])*)*[('.$str.')]?$/i',$string);
                    }
                }
                else{
                    return preg_match('/^[a-z0-9]{1,}((\W)*([a-z0-9])*)*[\W]?$/i',$string);
                }
                // V1: '/^[a-z0-9]{1,}(('.$str.')*[a-z0-9])+[^('.$str.')]?$/i'                                               
            } else {
                return preg_match('/^[a-z0-9]{1,}$/i',$string);
            }
            // string if can accept special character but not at beginning and at end
        break;
        default:
            return -1;
    }
}
?>