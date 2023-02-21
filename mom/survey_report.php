<?php
	$page_mode = '800'; // Ty's Comment: can't really understand what does it for  
    $chk_mode = 67;

    // another things is $dbl_mode in check_user_access.php, seems only works department... my guess is department Admin level?
	$page_title = 'Interactive Campaign Report';
	include('header.php');
	include('checkAccess.php');
    $x = GetLanguage("survey",$lang);

?>		
    <link href="css/tychang.css" rel="stylesheet">
			<div class="page-header">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li></li>
<?php if (isset($_GET["pageview"]) && $_GET["pageview"] == "campaign") { ?>
						<li class="breadcrumb-item" aria-current="page"><a href="survey_report.php"><?php echo $x->title; ?></a></li>
						<li class="breadcrumb-item active" aria-current="page" id="breadcrumb_curr"></li>
<?php } else { ?>
						<li class="breadcrumb-item active" aria-current="page"><?php echo $x->title; ?></li>
<?php } ?>
					</ol>
				</nav>
			</div>
		
            <div class="page-content">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <br>
                            <table>
                            <tr>
                            <td><b>Search from</b>&nbsp;</td>
                            <td><input id="from" type="text" class="form-control input-sm" size="10" data-provide="datepicker" readonly></td>
                            <td><b>To</b>&nbsp;</td>
                            <td><input id="to" type="text" class="form-control input-sm" size="10" data-provide="datepicker" readonly>
                            </td>
                            </tr>
                            </table>

                            <?php if (!isset($_GET["pageview"]) || $_GET["pageview"] == "listcampaigns") { ?>
                            <h3><?php echo $x->listsurvey; ?></h3>
                            <table class="table table-bordered table-striped table-sm" id="tbl_campaign_list">
                                <thead>
                                <tr><th><?php echo $x->campaignname; ?></th><th><?php echo $x->totalsentout; ?></th><th><?php echo $x->totalreceived; ?></th><th><?php echo $x->createdby; ?></th></tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" id="export"></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <?php } else if (isset($_GET["pageview"]) && $_GET["pageview"] == "campaign") { ?>
                            <h3><?php echo $x->summary; ?></h3>
                            <table id="tbl_summary" class="table table-bordered tabletight">
                                <tbody>
                                    <tr><th width="250px">Campaign Name</th><td id="campaignname"></td></tr>
                                    <tr><th>Total Message Sent Out</th><td id="campaignout"></td></tr>
                                    <tr><th>Total Response Message</th><td id="campaignin"></td></tr>
                                </tbody>
                            </table>
                            <h3><?php echo $x->listresponse; ?></h3>
                            <table class="table" id="tbl_response_list">
                                <thead>
                                <tr><th><?php echo $x->date_time; ?></th><th><?php echo $x->mobile_number; ?></th><th><?php echo $x->message; ?></th></tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" id="export"></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <?php } ?>
                        </div> <!-- end of panel-body -->
                    </div> <!-- end of pane -->
                </div>
            </div>
            <?php include('footnote.php'); ?>
        </div>
        <script src="js/bootstrap-datepicker.min.js"></script>
        <script src="js/pdfmake.min.js"></script>
        <script src="js/vfs_fonts.js"></script><?php // defer means to let all pages rendered finished then begin load ?>
        <script src="js/dataTables.buttons.min.js"></script>
        <script src="js/buttons.html5.min.js"></script>

        <script src="js/moment.min.js" type="text/javascript"></script>
        <script src="survey_report_js.php?<?php
        echo !empty($_GET["pageview"]) ? ("pageview=".$_GET["pageview"]) : "";
        echo !empty($_GET["id"]) ? ("&id=".$_GET["id"]) : ""; ?>" defer></script>
    </body>
</html>