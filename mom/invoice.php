<?php
$page_mode = '800'; // Ty's Comment: can't really understand what does it for  
$chk_mode = 73;

// another things is $dbl_mode in check_user_access.php, seems only works department... my guess is department Admin level?
$page_title = 'Invoice';
include('header.php');
include('checkAccess.php');
$x = GetLanguage("report",$lang);
?>
            <link href="css/assmi.css" rel="stylesheet">
			<div class="page-header">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li></li>
						
						<li class="breadcrumb-item active" aria-current="page">Invoice</li>
					</ol>
				</nav>
			</div>
            
            <div class="page-content">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                           <?php if (isUserAdmin($_SESSION["userid"])) { ?>
                                Select BU <select class="form_control" id="deptname" name="deptname"><option value="0" disabled>Please select department</option></select>
                           <?php } else { echo getDepartmentName($_SESSION["department"]); ?>

                           <?php } ?> 
                            <table class="table table-bordered mx-auto width-600px" id="tbl_invoice">
                                <thead>
                                <tr>
                                    <th width="350px">Month
                                    <th width="200px">Action
                                </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="2">Now Loading...</td></tr>
                                </tbody>
                            </table>
                        </div> <!-- end of panel-body -->
                    </div>
                </div>
            </div> <!-- end of page-content -->
            <?php include('footnote.php'); ?>
        </div> <!-- end of page-wrapper -->
        <?php include('invoice_js.php'); ?>
    </body>
</html>c