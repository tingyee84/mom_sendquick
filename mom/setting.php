<?php
$chk_mode = 59;
$page_mode = '800'; // Ty's Comment: can't really understand what does it for  
// another things is $dbl_mode in check_user_access.php, seems only works department... my guess is department Admin level?
    
if (isset($_POST["sessiontime"])) {
    require_once("lib/commonFunc.php");
    if (isUserAdmin($_SESSION["userid"])) {
        $x = GetLanguage("setting",$lang);

        $v1 = dbSafe($_POST["sessiontime"]);
        $v2 = dbSafe($_POST["pwdexpiry"]);
        $v3 = dbSafe($_POST["accthreshold"]);

        if (txvalidator($v1,TX_INTEGER) && txvalidator($v2,TX_INTEGER) && txvalidator($v3,TX_INTEGER)) {
            $sqlcmd = "UPDATE user_list SET timeout = '$v1', pwd_expire = '$v2', pwd_threshold = '$v3'";
            $res = doSQLcmd($dbconn,$sqlcmd);

            if (empty($res)) {
                $data["statuscode"] = 0;
                $data["statusmsg"] = "Error Occured. Check with Administrator for Detail.";
            } else {
                    doSQLcmd($dbconn,"UPDATE setting SET value = '$v1' WHERE variable = 'sessiontime'");
                    doSQLcmd($dbconn,"UPDATE setting SET value = '$v2' WHERE variable = 'pwdexpiry'");
                    doSQLcmd($dbconn,"UPDATE setting SET value = '$v3' WHERE variable = 'accthreshold'");
                    $data["statuscode"] = 1;
                    $data["statusmsg"] = (String) $x->alert_1;
            }
        } else {
            $data["statuscode"] = 0;
            $data["statusmsg"] = "Unable update the detail. Check the input.";
        }
        echo json_encode($data);
    }
} else {
	$page_title = 'Setting';
	include('header.php');
    include('checkAccess.php');
    $x = GetLanguage("setting",$lang);
    
?>
			<div class="page-header">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item active"><?php echo $x->title; ?></li>
					</ol>
				</nav>
			</div>
	
            <div class="page-content">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="row">
                                <h3 class="col-md-4 offset-md-4"><?php echo $x->accsetting; ?></h3>
                            </div>
                            <form id="form-setting">
                                <div class="row">
                                    <div class="form-group col-md-4 offset-md-4">
                                        <div class="row">
                                            <div class="col-md-4">
                                            <label for="formControlRange"><?php echo $x->sessiontime; ?></label>    
                                            </div>
                                            <div class="col-auto">
                                            <input type="number" id="sessiontimeval" class="form-control input-sm" size=5 min=5 max=60 value=20>
                                            </div>
                                            <div class="col-auto" ><?php echo $x->minutes; ?>  
                                            </div>
                                        </div>
                                        <input name="sessiontime" type="range" class="form-range" min=5 max=60 value=20>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4 offset-md-4">
                                        <div class="row">
                                            <div class="col-md-4"><label for="formControlRange"><?php echo $x->pwdexpiry; ?></label>
                                            </div>
                                            <div class="col-auto"><input type="number" id="pwdexpiryval" class="form-control input-sm" size=5 min=1 max=365 value=180>
                                            </div>
                                            <div class="col-auto"><?php echo $x->days; ?>
                                            </div>
                                            <input name="pwdexpiry" type="range" class="form-range" min=1 max=365 value=180>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4 offset-md-4">
                                        <div class="row">
                                            <div class="col-md-4"><label for="formControlRange"><?php echo $x->accthreshold; ?><?php // echo txvalidator("1234",TX_INTEGER); ?></label>
                                            </div>
                                            <div class="col-auto"><input type="number" id="accthresholdval" class="form-control input-sm" size=5 min=5 max=15 value=10>
                                            </div>
                                            <div class="col-auto"><?php echo $x->times; ?>
                                            </div>
                                            <input name="accthreshold" type="range" class="form-range" min=5 max=15 value=10>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group text-center col-md-4 offset-md-4">
                                        <button type="submit" id="btnsave" class="btn btn-primary"><?php echo $x->save; ?></button> <button type="button" id="btnreset" class="btn btn-secondary"><?php echo $x->reset; ?></button>
                                    </div>
                                </div>
                            </form>
                            <div class="row m-3">
                                <div id="msgstatusbar" class="alert alert-dismissible show fade col-md-4 offset-md-4" role="alert">
                                    <span id="msgstatustext">A</span>
                                    <button type="button" class="btn float-end p-0" id="msgstatusbar_close" aria-label="Close"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include('footnote.php'); ?>
            </div> <!-- End of Page-content" -->
    </body>
<script language="javascript" src="js/txvalidator.js"></script>
<script src="setting_js.php"></script>
</html>
<?php } ?>