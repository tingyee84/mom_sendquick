<?php
	$page_mode = '800'; // Ty's Comment: can't really understand what does it for  
    $chk_mode = 60;

    // another things is $dbl_mode in check_user_access.php, seems only works department... my guess is department Admin level?
	$page_title = 'Report (Incoming Message)';
	include('header.php');
	include('checkAccess.php');
    $x = GetLanguage("report",$lang);

?>
			<div class="page-header">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li></li>
						
						<li class="breadcrumb-item active" aria-current="page"><?php echo $x->incoming_report; ?></li>
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
                            <td><input id="datefrom" type="text" class="form-control input-sm" size="10" data-provide="datepicker" readonly style="cursor:pointer"></td>
                            <td><b>To</b>&nbsp;</td>
                            <td><input id="dateto" type="text" class="form-control input-sm" size="10" data-provide="datepicker" readonly style="cursor:pointer">
                            </td>
                            </tr>
                            </table>
                            <h3><?php echo $x->summary; ?></h3>
                            <table id="tbl_summary" class="table table-bordered" style="width:400px">
                                <tbody>
                                </tbody>
                            </table>
                            <h3><?php echo $x->listincoming; ?></h3>
                            <table class="table" id="tbl_msg_list">
                                <thead>
                                <tr><th><?php echo $x->date_time; ?></th><th><?php echo $x->mobile_number; ?></th><th><?php echo $x->message; ?></th><?php echo ($_SESSION['department'] == 0) ?"<th>".$x->department."</th>" : ""; ?><th><?php echo $x->keyword; ?></th></tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td id="export" colspan="<?php echo ($_SESSION['department'] == 0 ? 5 : 4); ?>"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div> <!-- end of panel-body -->
                    </div> <!-- end of pane -->
                </div>
            </div>
            <?php include('footnote.php'); ?>
        </div>
        <?php include ('report_incoming_js.php'); ?>
    </body>
</html>