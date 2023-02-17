<?php
// TODO zin: yes. we need to have mim tab under report and need to show summary mim template message - success and failed and mim normal message - success and failed
    $chk_mode = 59;
    if (isset($_GET["view"])) {
        if ($_GET["view"] == "alldepts" ) { // MOMAdmin view all depts
            $chk_mode = 61;
        } else if ($_GET["view"] == "users") { // BU Admin view all users
            $chk_mode = 62;
        }
    }
	$page_mode = '800'; // Ty's Comment: can't really understand what does it for  
    // another things is $dbl_mode in check_user_access.php, seems only works department... my guess is department Admin level?
	$page_title = 'Report';
	include('header.php');
	include('checkAccess.php');
    
    $x = GetLanguage("report",$lang);
    if (!isset($_SESSION["report_datefrom"]) || strtolower($_SESSION["report_datefrom"]) == "invalid date") {
        $_SESSION["report_datefrom"] = date("d/m/Y");
        $_SESSION["report_dateto"] = date("d/m/Y");
    }

?>
			<div class="page-header">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li></li>
						<li class="breadcrumb-item"><?php echo $x->reportt; ?></li>
						<?php
						 if ($chk_mode == 61) {
							echo '<li class="breadcrumb-item active" aria-current="page">'.$x->viewalldept.'</li>';
						} else if ($chk_mode == 62) {
							echo '<li class="breadcrumb-item active" aria-current="page">'.$x->viewusers.'</li>';
						} else {
							echo '<li class="breadcrumb-item active" aria-current="page">'.$x->viewreport.'</li>';
						}
						?>
						
					</ol>
				</nav>
			</div>
	
            <div class="page-content">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-body">

                            <table>
                            <tr>
                            <td><b><?php echo $xml_common->date_from; ?></b>&nbsp;</td>
                            <td><input id="datefrom" type="text" class="form-control input-sm" size="10" data-provide="datepicker"></td>
                            <td><b><?php echo $xml_common->date_to; ?></b>&nbsp;</td>
                            <td><input id="dateto" type="text" class="form-control input-sm" size="10" data-provide="datepicker">
                            </td>
                            </tr>
                            </table>
                            <br>

  

<?php if (in_array("61",$access_arr) && $chk_mode == 61) { ?>
                                        <div class="row"><div class=" col-md-12">
                                            <table id="tbl_dept_summary" class="table table-bordered table-striped table-sm" width="100%">
                                                <thead>
                                                <tr>
                                                    <th><?php echo $x->department; ?></th>
                                                    <th><?php echo $x->totaluser; ?></th>
                                                    <th><?php echo $x->totalsent; ?></th>
                                                    <th><?php echo $x->quota; ?></th>
                                                    <th><?php echo $x->delivered; ?></th>
                                                    <th><?php echo $x->undelivered; ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    <tr><td colspan="6">Loading Data... Please Wait</td></tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td><b><?php echo $x->total; ?></b></td><td></td><td></td><td></td><td></td><td></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6"><div id="export"></div></td>
                                                    </tr></tfoot>
                                            </table>
                                        </div></div>
<?php } else if ((in_array("61",$access_arr) || in_array("62",$access_arr)) && $chk_mode == 62) { ?>
    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-sms-tab" data-bs-toggle="tab" href="#nav-smspanel" role="tab" aria-controls="nav-smspanel" aria-selected="true">SMS</a>
        <a class="nav-item nav-link" id="nav-mim-tab" data-bs-toggle="tab" href="#nav-mimpanel" role="tab" aria-controls="nav-mimpanel" aria-selected="false">MIM</a>
        </div>
    </nav>

<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane show active" id="nav-smspanel" role="tabpanel" aria-labelledby="nav-sms-tab">
        <div class="row"><div class="col-md-6"><h3><span id="txtdeptname"></span><?php echo $x->summary; ?></h3></div></div>
        <table id="tbl_summary" class="table table-bordered table-sm" style="width:450px"><tbody></tbody></table>
        <h3>List of Users</h3>
        <table id="tbl_users_list" class="table table-bordered table-striped table-sm" width="100%">
            <thead>
                <tr>
                    <th><?php echo $x->users; ?></th>
                    <th><?php echo $x->totalsent; ?></th>
                    <th><?php echo $x->quota; ?></th>
                    <th><?php echo $x->delivered; ?></th>
                    <th><?php echo $x->undelivered; ?></th>
                    <th><?php echo $x->autorefresh; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="6">Loading Data... Please Wait</td></tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6"><div id="export" class="pull-left">

                    </div></td>
                </tr></tfoot>
        </table>
    </div>
    <div class="tab-pane" id="nav-mimpanel" role="tabpanel" aria-labelledby="nav-mim-tab">
        <div class="row"><div class="col-md-6"><h3><span id="txtdeptname_mim"></span><?php echo $x->summary; ?></h3></div></div>
        <table id="tbl_summary_mim" class="table table-bordered table-sm" style="width:450px"><tbody></tbody></table>
        <h3>List of Users</h3>
        <table id="tbl_users_list_mim" class="table table-bordered table-striped table-sm" width="100%">
            <thead>
                <tr>
                    <th><?php echo $x->users; ?></th>
                    <th>Total MIM Template Message Sent</th>
                    <th>Total MIM Normal Message Sent</th>
                    <th><?php echo $x->quota; ?></th>
                    <th>Total MIM Template Message Delivered Success</th>
                    <th>Total MIM Template Message Delivered Failed</th>
                    <th>Total MIM Normal Message Delivered Success</th>
                    <th>Total MIM Normal Message Delivered Failed</th>
                    <th><?php echo $x->autorefresh; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="9">Loading Data... Please Wait</td></tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="9"><div id="export_mim" class="pull-left">

                    </div></td>
                </tr></tfoot>
        </table>
    </div>
</div><!-- end of tab content -->
<?php } else if ((in_array("59",$access_arr) || in_array("62",$access_arr) || in_array("61",$access_arr)) && $chk_mode == 59) { ?>
<nav>
    <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-sms-tab" data-bs-toggle="tab" href="#nav-smspanel" role="tab" aria-controls="nav-smspanel" aria-selected="true">SMS</a>
        <a class="nav-item nav-link" id="nav-mim-tab" data-bs-toggle="tab" href="#nav-mimpanel" role="tab" aria-controls="nav-mimpanel" aria-selected="false">MIM</a>
    </div>
</nav>

<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane show active" id="nav-smspanel" role="tabpanel" aria-labelledby="nav-sms-tab">
        <div class="row"><div class="col-md-6"><h3><?php echo $x->summary; ?></h3></div></div>
        <table id="tbl_summary" class="table table-bordered table-sm" style="width:450px"><tbody></tbody></table>
        <h3><?php echo $x->listmsg; ?></h3>
        <table id="tbl_msg_list" class="table table-bordered table-striped table-sm" width="100%">
            <thead>
                <tr>
                    <th><?php echo $x->date_time; ?></th>
                    <th><?php echo $x->campaignname; ?></th>
                    <th><?php echo $x->mobile_number; ?></th>
                    <th><?php echo $x->message; ?></th>
                    <th><?php echo $x->status; ?></th>
                    <th><?php echo $x->totalsms; ?></th>
                    <th><?php echo $x->action; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="7">Loading Data... Please Wait</td></tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7"><div id="export"></div></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="tab-pane" id="nav-mimpanel" role="tabpanel" aria-labelledby="nav-mim-tab">
        <div class="row"><div class="col-md-6"><h3><?php echo $x->summary; ?></h3></div></div>
        <table id="tbl_summary_mim" class="table table-bordered table-sm" style="width:450px"><tbody></tbody></table>
        <h3><?php echo $x->listmsg; ?></h3>
        <table id="tbl_msg_list_mim" class="table table-bordered table-striped table-sm" width="100%">
            <thead>
                <tr>
                    <th><?php echo $x->date_time; ?></th>
                    <th><?php echo $x->campaignname; ?></th>
                    <th><?php echo $x->mobile_number; ?></th>
                    <th><?php echo $x->message; ?></th>
                    <th><?php echo $x->status; ?></th>
                    <th><?php echo $x->action; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="7">Loading Data... Please Wait</td></tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6"><div id="export_mim"></div></td>
                </tr>
            </tfoot>
        </table>
    </div>


    <div class="modal fade" tabindex="-1" id="msgdetailmodal" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3 offset-md-1"><b>Track ID</b></div>
                        <div class="col-md-7" id="trackid"></div>
                    </div>
                    <hr style="border-top: 1px solid #AAA; width: 83%" align="center">
                    <div class="row">
                        <div class="col-md-3 offset-md-1"><b>Mobile Number</b></div>
                        <div class="col-md-7" id="recipient"></div>
                    </div>
                    <hr style="border-top: 1px solid #AAA; width: 83%" align="center">
                    <div class="row">
                        <div class="col-md-3 offset-md-1"><b>Status</b></div>
                        <div class="col-md-7" id="status"></div>
                    </div>
                    <hr style="border-top: 1px solid #AAA; width: 83%" align="center">
                    <div class="row">
                        <div class="col-md-3 offset-md-1"><b>Caller ID</b></div>
                        <div class="col-md-7" id="callerid"></div>
                    </div>
                    <hr style="border-top: 1px solid #AAA; width: 83%" align="center">
                    <div class="row">
                        <div class="col-md-3 offset-md-1"><b>Message</b></div>
                        <div class="col-md-7" id="message"></div>
                    </div>
                    <hr style="border-top: 1px solid #AAA; width: 83%" align="center">
                    <div class="row">
                        <div class="col-md-3 offset-md-1"><b>Date Sent</b></div>
                        <div class="col-md-7" id="sent_dtm"></div>
                    </div>
                    <hr style="border-top: 1px solid #AAA; width: 83%" align="center">
                    <div class="row">
                        <div class="col-md-3 offset-md-1"><b>Date Completed</b></div>
                        <div class="col-md-7" id="completed_dtm"></div>
                    </div>
                    <span id="smsmimswitch">
                    <hr style="border-top: 1px solid #AAA; width: 83%" align="center">
                    <div class="row">
                        <div class="col-md-3 offset-md-1"><b>Total SMS</b></div>
                        <div class="col-md-7" id="totalsms"></div>
                    </div>
                    </span>
                    <hr style="border-top: 1px solid #AAA; width: 83%" align="center">
                    <div class="row">
                        <div class="col-md-3 offset-md-1"><b><?php echo $x->campaignname; ?></b></div>
                        <div class="col-md-7" id="campaign"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div><!-- /.modal -->


</div> <!-- end of tab-content -->
<?php } ?>

                    </div>
                </div>
            </div>
            <?php include('footnote.php'); ?>
        </div>
        <script src="js/bootstrap-datepicker.min.js"></script>
        <script src="js/pdfmake_0.2.7.min.js"></script>
        <script src="js/vfs_fonts.js"></script><?php // defer means to let all pages rendered finished then begin load ?>
        <script src="js/dataTables.buttons.min.js?"></script>

        <script src="js/buttons.html5.min.js"></script>
        <script src="js/moment.min.js" type="text/javascript"></script>
        <script src="report_js.php?view=<?php echo $_GET["view"];
        echo !empty($_GET["user"]) ? "&user=".$_GET["user"]: "";
        echo !empty($_GET["dept"]) ? "&dept=".$_GET["dept"]: "";
        ?>" defer></script>
    </body>
</html>