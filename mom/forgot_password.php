<?php
require_once('lib/commonFunc.php');
$weburl = "http://localhost";

function random_password( $length = 12 ) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    while(preg_match('/[a-z]/',$password) == 0 || preg_match('/[A-Z]/',$password) == 0 || preg_match('/[0-9]/',$password) == 0) {
        srand();
        $password = substr( str_shuffle( $chars ), mt_rand(0,strlen($chars)-$length), $length );
    }
    return $password;
}
if(@isset($_POST["mode"])) {
    $data = array();
    $username = trim(filter_input(INPUT_POST,'username'));
    $mode = trim(filter_input(INPUT_POST,'mode'));

    if ($mode == "checkusername" || $mode == "2faresend") {
        if (empty($username)) {
            $data['errcode'] = 2;
            $data['errmsg'] = "Username cannot be empty";
        } else {
            global $dbconn;
            $result = pg_query($dbconn,"SELECT mobile_numb, access_string FROM user_list WHERE userid='".pg_escape_string($username)."'");
            if ($row = pg_fetch_array($result)) {
                $acc_str = explode(",",$row["access_string"]);
                if (in_array("8",$acc_str)) {
                    $ch = curl_init();
                    $url = "$weburl/webotp/otp_http.php?id=momappotp&passwd=M0M@pp0tp&mobile=".urlencode($row["mobile_numb"])."&username=$username";
                    if ($_POST["mode"] == "2faresend") {
                        $url .= "&resend=1&sessionid".trim(filter_input(INPUT_POST,'sessionid'));
                    }
                    curl_setopt($ch,CURLOPT_URL,$url);

                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $server_output = curl_exec($ch);
                    curl_close ($ch);

                    if (substr($server_output,0,3) == "205") {
                        $temp = explode(",",$server_output);
                        $data['errcode'] = $temp[0];
                        $data['sessionid'] = $temp[1];
                        $data['errmsg'] = "****".substr($row["mobile_numb"],-4);
                    } else {
                        $data['errcode'] = $server_output;
                        $data['errmsg'] = "Error. Unable to retrieve OTP, please try again later. Error CODE: ".$server_output;
                        // leave log here
                    }
                } else {
                    $data['errcode'] = 7;
                    $data['errmsg'] = "User is not permitted to reset password. Contact your BU";
                }
            } else {
                $data['errcode'] = 3;
                $data['errmsg'] = "Username is not found";
            }
        }
    } else if ($mode == "2faverify") {
        global $dbconn;
    
        $sessionid = trim(filter_input(INPUT_POST,'sessionid'));
        $otp = trim(filter_input(INPUT_POST,'otp'));
    
        $result = pg_query($dbconn,"SELECT mobile_numb, email FROM user_list WHERE userid='".pg_escape_string($username)."'");
        if ($row = pg_fetch_array($result)) {
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,"$weburl/webotp/session_http.php?username=$username&session_id=$sessionid&token=$otp&mobile=".urlencode($row["mobile_numb"]));
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close ($ch);
            if ($server_output == "201") {
                $data['errcode'] = 201;

                // Generated Password
                $temppwd = random_password(12);

                $_SESSION['cryptkey'] = 'Shinjitsu wa Itsumo Hitotsu';

                $epassword = getEncryptedPassword($temppwd);

                $row2 = doSQLcmd($dbconn, "UPDATE user_list SET password = '".pg_escape_string($epassword)."', chg_onlogon = TRUE WHERE userid = '".pg_escape_string($username)."'");
                if ($row2 === 1) {
                    if (file_exists("lib/class.phpmailer.php") && file_exists("lib/class.smtp.php")){
                        require('lib/class.smtp.php');
                        require("lib/class.phpmailer.php");
                        $mail = new PHPMailer();
                        $mail->IsSMTP();
                        $mail->CharSet = 'UTF-8';
                        $mail->Timeout = 10;

                        $mail->Host = "mail.sendquickasp.com";
                        $mail->Port = 587;
                        $mail->SMTPSecure = "tls";
                        $mail->SMTPAuth = true;
                        $mail->From     = "noreply@sendquickasp.com";
                        $mail->Subject  = "MOMChat - Password Reset";
                        $mail->Username = "admin@sendquickasp.com";
                        $mail->Password = "Wj#4X8]1Xh7W";
                        $mail->IsHTML(true);
                        $mail->Body     = <<< END
Dear $username,<br>
<br>
We have successfully reset your password and the new password is as follows:<br>
<br>
<center><b>$temppwd</b></center><br><br>
For security purposes, please login and change the password before you can use the webportal.<br>
<br>
Regards,<br>
MOMChat
END;

                        $mail->AddAddress($row["email"]);
                        if ($mail->Send()) {
                            $data['errmsg'] = "<p style='text-align:left'>We have successfully reset your password and the new password is sent to your email address. You may also check the junk or spam folder too.<br><br> For security purposes, you are required to change the password before you can use web portal.</p>";
                        } else {
                            $data['errcode'] = 9;
                            $data['errmsg'] = "<p>We have sent out but had problem sending out to your email inbox. Please use '<b>$temppwd</b>'</p>";
                            error_log("PHPMailer from forgot_password.php, failed to send out to ".$row["email"].". - ".@$mail->ErrorInfo);
                        } 

                    }
                    else
                    {
                        $data['errcode'] = 6;
                        $data['errmsg'] = 'phpmailer is not available';
                    }
                
                    error_log($userid." made a password change");
                } else {
                    $data['errcode'] = 5;
                    $data['errmsg'] = "Unable to reset password";
                    error_log($userid." made a password change but db cannot catch up");
                }
            } else {
                $data['errcode'] = $server_output;
                $data['errmsg'] = "Invalid OTP Authentication. Err Code:".$server_output;
            }
        }
    } else {
        $data['errcode'] = 8;
        $data['errmsg'] = "Invalid Command";
        error_log("Sending non existing to forgot_password.php. mode parameter received". $_POST["mode"]);
    }
    echo json_encode($data);
} else {
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo "Forgot Password"; ?></title>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/sb-admin-2.css" rel="stylesheet">
        <link href="css/font-awesome.min.css" rel="stylesheet">
        <link href="css/tychang.css" rel="stylesheet">
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/metisMenu.min.js"></script>
        <script src="js/sb-admin-2.js"></script>
        <script src="forgot_password.js"></script>
    </head>
    <body background="images/background.jpg">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 offset-md-4">
                        <img src="images/TalariaX-Logo.png" alt="TalariaX"><br>
                        <img src="images/sendQuickMessaging.png" alt="SendQuick Messaging Portal">
                        <h2>Reset Password</h2>
                </div>
                <!-- test -->
            </div>
                <div class="row text-center">
                    <div id="thirdstep"class="col-md-4 offset-md-4">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title">Successfully Reset</h3>
                            </div>
                            <div class="panel-body"></div>
                            <div class="panel-footer">Click <a href="./index.php">here</a> to go back to login page</div>
                        </div>
                    </div>
                    <div id="secondstep" class="col-md-4 offset-md-4">
                        <div class="form-group">
                        <p>We have sent you a SMS with OTP code to your mobile number for verification.</p>
                                <p><b><span id='censorednumber'></span></b></span>
                                <p>One Time Password:</p>
                                <p><span id="sessionidtext" class="fs-4"></span><span class="fs-4">-</span>
    <?php for ($i = 0 ; $i < 6 ; $i++) { ?>
                                <input name="otp[]" type="text" maxlength="1" size="1" class="otpnumberfield">
    <?php } ?>
                        </div>

                        <form method="POST" name="otp_verify" id="otp_verify">
                            <input type="hidden" name="sessionid" id="sessionid" value=""/>
                            <input type="hidden" name="otp" id="otp" value=""/>
                            <input type="hidden" name="username" id="otpusername" value=""/>
                            <input type="hidden" name="mode" id="otpmode" value="2faverify"/>
                            <button class="btn btn-primary" type="submit" id="otp_submit">Submit</button><br>
                            <p>Didn't receive the OTP?</p>
                            <button class="btn btn-danger" type="submit" id="otp_resend">Wait 60s</button>
                        </form>
                    </div>
                    <div id="firststep" class="col-md-2 offset-md-5">
                        <form method="POST" name="forgotpwd_form" id="forgotpwd_form">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Enter your username</label>
                                <input type="text" class="form-control mt-1" id="username" name="username" placeholder="Username" autocomplete="off">
                            </div>
                            <input type="hidden" name="mode" value="checkusername"/>
                            <button id="btn_submit" type="submit" class="btn btn-primary btn-block mt-1">Send OTP to Verify</button>
                        </form>
                    </div>
                </div>
                <div class="row text-center"><br>
                        <div id="alertbar" class="alert alert-dismissible col-md-4 offset-md-4 mt-2" role="alert">
                            <span id="alertmsg"></span>
                            <button type="button" class="btn float-end pt-0" aria-label="Close"><i class="fa fa-time" aria-hidden="true"></i></span></button>
                        </div>
                </div>
            <!-- </div> -->
        </div>
        <div class="row">
            <div class="login-footer">
                <p>Copyright &#169; 2002-<?php echo strftime("%Y", time()); ?>, TalariaX Pte Ltd, Singapore. All Rights Reserved. <?php $datestr = strftime("%a, %d %b %Y %H:%M", time());echo "$datestr";?></p>
            </div>
        </div>

    </body>
</html>
<?php } ?>
