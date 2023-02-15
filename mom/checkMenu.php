<?php //Create by Wafie @ 26/01/2017
  $userid = $_SESSION['userid'];

  $_SESSION['access_string'] = getAccessString($userid);
  $access_string = $_SESSION['access_string'];
  $access_arr = explode(",", trim($access_string));

  //User Management
  if(!(in_array('2',$access_arr))){
    echo "<script>$('#user').hide();</script>";
  }else{
    if(!(in_array('26',$access_arr)) || !(in_array('27',$access_arr))){
      echo "<script>$('#acct').hide();</script>";
    }
    if(!(in_array('28',$access_arr)) || !(in_array('29',$access_arr))){
      echo "<script>$('#rol').hide();</script>";
    }
    if(!(in_array('30',$access_arr)) || !(in_array('31',$access_arr))){
      echo "<script>$('#dpt').hide();</script>";
    }
    if(!(in_array('32',$access_arr))){
      echo "<script>$('#log').hide();</script>";
    }
  }

  //Address Book
  if(!(in_array('3',$access_arr))){
    echo "<script>
            $('#add_menu').hide();
            $('#pab').hide();
            $('#pag').hide();
            $('#gab').hide();
            $('#gag').hide();
          </script>";
    if(in_array('4',$access_arr)){
      echo "<script>
              $('#add_menu').show();
              $('#pab').show();
              $('#pag').show();
            </script>";
    }
    if(in_array('23',$access_arr)){
      echo "<script>
              $('#add_menu').show();
              $('#gab').show();
              $('#gag').show();
            </script>";
    }
  }else{
    if(!(in_array('4',$access_arr))){
      echo "<script>
              $('#pab').hide();
              $('#pag').hide();
            </script>";
    }
  }

  //Message Template
  if(!(in_array('5',$access_arr))){
    echo "<script>
            $('#tpl_menu').hide();
            $('#pmt').hide();
            $('#gmt').hide();
          </script>";
    if(in_array('6',$access_arr)){
      echo "<script>
              $('#tpl_menu').show();
              $('#pmt').show();
            </script>";
    }
  } else{
    if(!(in_array('6',$access_arr))){
      echo "<script>
              $('#pmt').hide();
            </script>";
    }
  }

  //Send SMS
  if(!(in_array('7',$access_arr))){
    echo "<script>
            $('#sms_menu').hide();
            $('#sch_sms').hide();
          </script>";
  }

  //Common inbox
  if(!(in_array('10',$access_arr))){
    echo "<script>
            $('#cmn_inb').hide();
          </script>";
  }

  //Inbox/Logs Management
  if(!(in_array('11',$access_arr)) && !(in_array('12',$access_arr))
  && !(in_array('13',$access_arr)) && !(in_array('14',$access_arr))
  && !(in_array('17',$access_arr)) && !(in_array('18',$access_arr))
  && !(in_array('19',$access_arr)) && !(in_array('20',$access_arr))){
    echo "<script>
            $('#log_mgnt').hide();
          </script>";
  } else{
    if(!(in_array('11',$access_arr))){
      echo "<script>
              $('#pinb').hide();
            </script>";
    }
    if(!(in_array('12',$access_arr))){
      echo "<script>
              $('#pslog').hide();
            </script>";
    }
    if(!(in_array('13',$access_arr))){
      echo "<script>
              $('#pulog').hide();
            </script>";
    }
    if(!(in_array('14',$access_arr))){
      echo "<script>
              $('#pqlog').hide();
            </script>";
    }
    if(!(in_array('17',$access_arr))){
      echo "<script>
              $('#ginb').hide();
            </script>";
    }
    if(!(in_array('18',$access_arr))){
      echo "<script>
              $('#gslog').hide();
            </script>";
    }
    if(!(in_array('19',$access_arr))){
      echo "<script>
              $('#gulog').hide();
            </script>";
    }
    if(!(in_array('20',$access_arr))){
      echo "<script>
              $('#gqlog').hide();
            </script>";
    }
  }

  //Unsubscribe List
  if(!(in_array('47',$access_arr))){
    echo "<script>
            $('#unsub_menu').hide();
          </script>";
  }

  //Keyword Management
  if(!(in_array('56',$access_arr))){
    echo "<script>
            $('#key_menu').hide();
          </script>";
  }

  //Quota Management
  if(!(in_array('48',$access_arr))){
    echo "<script>
            $('#quo_mnt').hide();
          </script>";
  }

  //System Configuration
  if(!(in_array('45',$access_arr))){
    echo "<script>
            $('#sys_menu').hide();
          </script>";
  }
?>
