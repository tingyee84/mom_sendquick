<?php
	$page_mode = '800'; // Ty's Comment: can't really understand what does it for  
    $chk_mode = 72;
	$page_title = 'Analytic';
	include('header.php');
	include('checkAccess.php');
    $x = GetLanguage("report",$lang);
?>
			<div class="page-header">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item active" aria-current="page">Analytic</li>
					</ol>
				</nav>
			</div>
	
            <div class="page-content">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-body">

<?php if (isUserAdmin($_SESSION["userid"])) { ?>
<nav>
  <div class="nav nav-tabs" id="nav-tab" role="tablist">
    <a class="nav-item nav-link active" id="nav-users-tab" data-bs-toggle="tab" href="#nav-users" role="tab" aria-controls="nav-users" aria-selected="true">TOP BU</a>
    <a class="nav-item nav-link" id="nav-bu-tab" data-bs-toggle="tab" href="#nav-bu" role="tab" aria-controls="nav-bu" aria-selected="false">TOP MOM</a>
  </div>
</nav>
<?php } ?>
<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-users" role="tabpanel" aria-labelledby="nav-users-tab">
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-secondary active">
               <input type="radio" name="options0" id="option1" value="chart" checked> Chart
            </label>
            <label class="btn btn-secondary">
                <input type="radio" name="options0" id="option2" value="table"> Table
            </label>
        </div>
        <br>
        <div class="form-inline">
            <div class="row"><div class="col-auto">
            <select name="range" id="range" class="form-select">
            <option value="currmonth">Current Month</option>
            <option value="lastmonth">Last Month</option>
            </select>
</div></div>
        </div>
        <br>
        <div id="1achart">
            <canvas id="userChart" width="768px"></canvas>
            <button id="export_u_chart_pdf" class="btn btn-secondary btn-sm">Export to PDF</button>
        </div>
        <div id="1atable" class="d-none">
            <h3>Top 10 User</h3>
            <table class="table" id="tbl_user">
            <thead>
            <tr>
            <th>#</th>
            <th>User</th>
            <th>Total Sent</th>
            <th>Total Message Sent Success (SMS &amp; MIM)</th>
            <th>Total Message Sent Fail (SMS &amp; MIM)</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
            <tr>
            <td colspan="5">
            <div class="pull-left">
            <button class="btn btn-secondary btn-sm" id="export_u_table_xls">Export to Excel</button>
            <button class="btn btn-secondary btn-sm" id="export_u_table_pdf">Export to PDF</button>
            </div>
            </tr>
            </tfoot>
            </table>
        </div>
    </div>

<?php if (isUserAdmin($_SESSION["userid"])) { ?>
    <div class="tab-pane fade" id="nav-bu" role="tabpanel" aria-labelledby="nav-bu-tab">
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-secondary active">
               <input type="radio" name="options1" id="option3" value="chart" checked > Chart
            </label>
            <label class="btn btn-secondary">
                <input type="radio" name="options1" id="option4" value="table"> Table
            </label>
        </div>

        <div class="form-inline">
            <div class="row"><div class="col-auto">
            <select name="range_mom" id="range_mom" class="form-select">
            </select>
</div></div>
        </div>

        <br>
        <div class="tab-pane show active" id="2achart">
            <canvas id="deptChart" width="768px"></canvas>
            <button id="export_d_chart_pdf" class="btn btn-secondary btn-sm">Export to PDF</button>
        </div>
        <div class="tab-pane" id="2atable">
            <h3>TOP 10 BU</h3>

            <table class="table" id="tbl_month">
                <thead>
                <tr><th colspan="5" id="month">Month</th></tr>
                <tr>
                <th>#</th>
                <th>BU</th>
                <th id="m0_a">Total Sent
                <th id="m0_b">Total Message Sent Success (SMS &amp; MIM)
                <th id="m0_c">Total Message Sent Fail (SMS &amp; MIM)
                </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <td colspan="5">
                        <div class="pull-left">
                            <button id="export_d_table_xls" class="btn btn-secondary btn-sm">Export to Excel</button>
                            <button id="export_d_table_pdf" class="btn btn-secondary btn-sm">Export to PDF</button>
                        </div>
                    </td>
                </tfoot>
            </table>
    
        </div>
    </div>
<?php } ?>
</div>
                            </div> <!-- end of tab-content -->
                        </div> <!-- end of panel-body -->
                    </div>
                </div>
            </div> <!-- end of page-content -->
            <?php include('footnote.php'); ?>
        </div> <!-- end of page-wrapper -->
        <?php include ('analytic_js.php'); ?>
    </body>
</html>