<?php
    // TODO UI part, merge 'two' scripts together so no need hop around
	$page_mode = '800'; // Ty's Comment: can't really understand what does it for  
    $chk_mode = 68;  // when mom
	$page_title = 'Global Report (API)';
    if (isset($_GET["view"])) {
        if ($_GET["view"] == "dept") { // when BU view
            $page_title = 'Report (API)';
            $chk_mode = 71; 
        }
    }
	include('header.php');
	include('checkAccess.php');
    $x = GetLanguage("report",$lang);

?>
			<div class="page-header">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item">API</li>
						<?php
						if ($chk_mode == 68) {
							echo '<li class="breadcrumb-item active" aria-current="page">View Global API Report</li>';
						} else if ($chk_mode == 71) {
							echo '<li class="breadcrumb-item active" aria-current="page">View API Report</li>';
						}
	
						?>
						
					</ol>
				</nav>
			</div>
		
            <div class="page-content">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item"><a id="smstab" class="nav-link active" href="#smspanel" data-bs-toggle="tab">SMS</a></li>
                                <li class="nav-item"><a id="mimtab" class="nav-link" href="#mimpanel" data-bs-toggle="tab">MIM</a></li>
                            </ul>
                            <div class="tab-content clearfix">
								<div class="tab-pane active" id="smspanel" role="tabpanel" aria-labelledby="smstab">
                                    <form id="deptlistForm" name="deptlistForm">
                                        <table style="border:none">
                                        <tr>
                                            <td><b><?php echo $xml_common->date_from;?></b>&nbsp;</td>
                                            <td><input class="form-control input-sm" type="text" id="from" name="from" size="10" required/></td>
                                            <td>&nbsp;</td>
                                            <td><b><?php echo $xml_common->date_to;?></b>&nbsp;</td>
                                            <td><input class="form-control input-sm" type="text" id="to" name="to" size="10" required/></td>
                                        </tr>
                                        </table>
                                        <input name="mode" type="hidden" value="listapi"/>
                                    </form><br>
<?php if ($chk_mode == 68) { ?>
                                    <table class="table table-bordered table-striped table-sm" id="tbl_dept_list" width="100%">
                                        <thead>
                                        <tr>
                                            <th><?php echo $x->dept; ?></th>
                                            <th>Total Service</th>
                                            <th>Total Message Count</th>
                                            <th>Total API SMS Sent</th>
                                            <th>Total Message Failed</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            
                                        </tbody>
                                    </table>
<?php } else if ($chk_mode == 71) { // if mom is viewing, auto hide this part first, else don't hide ?>
                                        <h3>Summary</h3>
                                        <table id="tbl_summary" class="table table-bordered table-sm" style="width:450px">
                                            <tbody>
                                            </tbody>
                                        </table>
                                        <h3>List of Message</h3>
                                        <table class="table table-bordered table-striped table-sm" id="tbl_msg_list" width="100%">
                                            <thead>
                                            <tr>
                                                <th><?php echo $x->date_time; ?></th>
                                                <th>Service Name</th>
                                                <th>Mobile Number</th>
                                                <th>Message</th>
                                                <th>Status</th>
                                                <th>Total SMS</th>
                                                <th>Template</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="7" id="export"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
<?php } ?>
                                </div> <!-- mim tab -->
                                <div class="tab-pane" id="mimpanel" role="tabpanel" aria-labelledby="mimtab">
                                    <form id="" name="">
                                        <table style="border:none">
                                        <tr>
                                            <td><b><?php echo $xml_common->date_from;?></b>&nbsp;</td>
                                            <td><input class="form-control input-sm" type="text" id="from_mim" name="from" size="10" required/></td>
                                            <td>&nbsp;</td>
                                            <td><b><?php echo $xml_common->date_to;?></b>&nbsp;</td>
                                            <td><input class="form-control input-sm" type="text" id="to_mim" name="to" size="10" required/></td>
                                        </tr>
                                        </table>
                                        <input name="mode" type="hidden" value="listapi_mim"/>
                                    </form><br>
<?php if ($chk_mode == 68) { ?>
                                    <table class="table table-bordered table-striped table-sm" id="tbl_dept_list">
                                        <thead>
                                        <tr>
                                            <th><?php echo $x->dept; ?></th>
                                            <th>Total Service</th>
                                            <th>Total API MIM Sent</th>
                                            <th>Total MIM Message Count</th>
                                            <th>Total MIM Message Failed</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            
                                        </tbody>
                                    </table>

                                </div>
<?php } else if ($chk_mode == 71) { // if mom is viewing, auto hide this part first, else don't hide ?>
                                        <h3>Summary</h3>
                                        <table id="tbl_summary_mim" class="table table-bordered table-sm" style="width:450px">
                                            <tbody>
                                            </tbody>
                                        </table>
                                        <h3>List of Message</h3>
                                        <table class="table table-bordered table-striped table-sm" id="tbl_msg_list_mim" width="100%">
                                            <thead>
                                            <tr>
                                                <th><?php echo $x->date_time; ?></th>
                                                <th>Service Name</th>
                                                <th>Mobile Number</th>
                                                <th>Message</th>
                                                <th>Status</th>
                                                <th>Total SMS</th>
                                                <th>Template</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="7" id="export_mim"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
<?php } ?>                
                        </div>
                    </div>
                </div>
            </div>
            <?php include('footnote.php'); ?>
        </div>
        <?php include ('report_api_js.php'); ?>
    </body>
</html>