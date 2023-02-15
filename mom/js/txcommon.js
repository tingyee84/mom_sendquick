var msg;

function validateSize(pVal,pType) {
    // pType : UID, PWD, ID, NAME, DESC, SHORTMSG, LONGMSG, MIMMSG, KEY, AID, SID 
    var defineMin = {"UID": 8, "PWD": 12, "ID": 1, "NAME": 1, "DESC": 0, "SHORTMSG": 0, "LONGMSG": 0, "MIMMSG": 0, "KEY": 1, "AID": 1, "SID": 1};
    var defineMax = {"UID": 15, "PWD": 128, "ID": 15, "NAME": 30, "DESC": 100, "SHORTMSG": 160, "LONGMSG": 1530, "MIMMSG": 4096, "KEY": 15, "AID": 32, "SID": 50};
   
    var min = defineMin[pType];
    var max = defineMax[pType];

    var len = pVal.length;
    if(pType == "MSG"){
        var lines = pVal.split(/\r\n|\r|\n/);		
		var total_lines = lines.length;
        len = len + total_lines;
    }    		
    //alert("len "+len+", tline "+total_lines);
    if(len < min){
        msg = "Sorry, minimun data length required is "+min;
        return 0;
    }else if(len > max){
        msg = "Sorry, maximum data length allowed is "+max;
        return 0;
    }else if(len >= min && len <= max){
        return 1;
    }
}

function getAlertMsg(){
    return msg;
}