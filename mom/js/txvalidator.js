function txvalidator (value,checktype,special="") {    
    switch(checktype) {
        case "TX_EMAILADDR":
        case "TX-001":
            return /(^_?[a-z0-9](_?[a-z0-9])+){1,64}@[a-z0-9](\.?[a-z0-9]){1,}\.([a-z]){2,}$/i.test(value);
        break;
        case "TX_URL":
        case "TX-002":
            return /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:[/?#]\S*)?$/i.test(value);
        break;
        case "TX_POSTCODE":
        case "TX-003":
            return /^\d{6}$/.test(value);
        break;
        case "TX_SGLANDPHONE":
        case "TX-004":
            return /^(\+?65)?(3|6)\d{7}$/.test(value.replace("-",""));
        break;
        case "TX_SGMOBILEPHONE":
            return /^(\+?65)?(8|9)\d{7}$/.test(value.replace("-",""));
        break;
        case "TX_SGPHONE":
        case "TX-006":
            return /^(\+?65)?(3|6|8|9)\d{7}$/.test(value.replace("-",""));
        break;
        case "TX_INTEGER":
        case "TX-009":
            return /^\d+$/.test(value);
        break;
        case "TX_STRING":
        case "TX-008":
            if (special.trim() == "") {
                return /^[a-z0-9]{1,}$/i.test(value);
            }
            else if(special == "ALL") {
                // Not allow space
                let patternstr = new RegExp("^[a-z0-9]{1,}((\W)*([a-z0-9])*)*(\W)*","i"); 
                return patternstr.test(value);
            }
            else if(special == "SPACE") {               
                // allow space
                const cleanStr = value.replace(/\s/g, '')
                let patternstr = new RegExp("^[a-z0-9]{1,}$","i"); 
                return patternstr.test(cleanStr);
            }
            else {
                let temparray = special.split("").filter((x, i, a) => a.indexOf(x) == i).join("|");
                // remove duplicate
                // ES6
                if(temparray.length == 1){
                    let patternstr = new RegExp("^[a-z0-9]{1,}(("+temparray+")*([a-z0-9])*)*["+temparray+"]?$","i"); 
                    return patternstr.test(value);
                }else{
                    let patternstr = new RegExp("^[a-z0-9]{1,}(("+temparray+")*([a-z0-9])*)*[("+temparray+")]?$","i"); 
                    return patternstr.test(value);
                }              
                
            }
            break;
        default:
            return -1;
    }
}