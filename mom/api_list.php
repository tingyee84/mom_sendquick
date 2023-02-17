<?php
	$page_mode = '300';
	$chk_mode = '4';
	$page_title = 'Application Management';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("api_list",$lang);
?>
        <link href="css/assmi.css" rel="stylesheet">
		<div class="page-header padding-top-10">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item active" aria-current="page"><?php echo "Application Management";?></li>
				</ol>
			</nav>
		</div>
		
        <div class="page-content">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="alert fade show d-none" id="alert-top" role="alert">
                            <span id="alert-msg"></span>
                            <button type="button" class="close" aria-label="Close" id="alert-close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                            <table class="table table-striped table-bordered table-condensed" id="api_accts">
                                <thead>
                                    <tr>
                                        <th><?php echo $x->api_name;?></th>
                                        <th><?php echo $x->api_agencyid; ?></th>
                                        <th><?php echo $x->api_serviceid; ?></th>
                                        <th><?php echo $x->api_clientid; ?></th>
                                        <th><?php echo $x->api_appntype; ?></th>
                                        <th><?php echo $x->api_dept; ?></th>
                                        <!-- <th><?php echo $x->api_statusurl; ?></th> -->
                                        <th><?php echo "Application URL"; ?></th>
                                        <th><?php echo $x->api_quota; ?></th>
                                        <th><?php echo $x->api_sftp_status; ?></th>
                                        <th><input type="checkbox" id="all"></th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <td colspan="10">
                                            <span class="pull-left">
                                                <button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myApiAccts"><?php echo $xml_common->add_new_record;?></button>&emsp;
                                                <button class="btn btn-primary btn-sm" onclick="window.location.href='keyword_management.php'"><?php echo $x->add_keyword;?></button>
                                            </span>
                                            <!-- <div id="export"></div> -->
                                            <span class="pull-right">
                                                <button id="delete" type="submit" class="btn btn-danger btn-sm"><?php echo $xml_common->delete;?></button>
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        <div class="modal fade" id="myApiAccts" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header" id="myApiAccts_header">
                                        <h4 class="modal-title" id="header">&nbsp;</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                            <!-- <span aria-hidden="true">&times;</span> -->
                                        </button>
                                    </div>
                                    <form id="api_acct_form" name="api_acct_form">
                                    <div class="modal-body">
                                        <div class="row">
												<div class="col-md-10">
													<div id="msgstatusbar" class="alert alert-dismissible show fade col-md-20 offset-md-2 d-none" role="alert">
														<span id="msgstatustext">A</span>	
														<button type="button" class="btn-close" id="msgstatusbar_close" aria-label="Close"></button>											
													</div>
												</div>											
										</div>
                                        <div class="row">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="api_name" class="control-label"><?php echo $x->api_name;?> <span class="color-red">*</span></label>
                                            </div>
                                            <div class="col-md-6">
                                                <input class="form-control input-sm" type="text" name="api_name" id="api_name" required>
                                                <div id="invalid_api_name" class="invalid-feedback">
										            <?php echo $x->invalid_api_name; ?>
									            </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="api_agencyid" class="control-label"><?php echo $x->api_agencyid;?> <span class="color-red">*</span></label>
                                            </div>
                                            <div class="col-md-6">
                                                <input class="form-control input-sm" type="text" name="api_agencyid" id="api_agencyid" required>
                                                <div id="invalid_api_agencyid" class="invalid-feedback">
										            <?php echo $x->invalid_api_agencyid; ?>
									            </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="api_serviceid" class="control-label"><?php echo $x->api_serviceid;?> <span class="color-red">*</span></label>
                                            </div>
                                            <div class="col-md-6">
                                                <input class="form-control input-sm" type="text" name="api_serviceid" id="api_serviceid" required>
                                                <div id="invalid_api_serviceid" class="invalid-feedback">
										            <?php echo $x->invalid_api_serviceid; ?>
									            </div>
                                            </div>
                                        </div>
                                        <!-- change password button -->
                                        <div class="row hidden" id="change_pwd_div">
                                            <div class="col-md-3 offset-md-1">
                                            </div>
                                            <div class="col-md-3">
                                                <p><button id="change_pwd" type="button" class="btn btn-primary"><?php echo $x->chg_password;?></button></p>
                                            </div>
                                            <div class="col-md-3">
                                            </div>
                                        </div>
                                        <!-- for password need to do additional checking -->
                                        <div class="row hidden" id="password_div" >
                                            <div class="col-md-3 offset-md-1">
                                                <label for="api_password" class="control-label"><?php echo $x->api_password;?> <span class="color-red">*</span></label>
                                            </div>
                                            <div class="col-md-6">
                                                <input class="form-control input-sm" type="password" name="api_password" id="api_password">
                                                <div id="invalid_api_password" class="invalid-feedback">
										            <?php echo $x->invalid_api_password; ?>
									            </div>
                                            </div>
                                        </div>
                                        <div class="row hidden" id="reenter_password_div">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="api_reenter_password" class="control-label"><?php echo $x->api_reenter_password;?> <span class="color-red">*</span></label>
                                            </div>
                                            <div class="col-md-6">
                                                <p><input class="form-control input-sm" type="password" name="api_reenter_password" id="api_reenter_password"></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="api_clientid" class="control-label"><?php echo $x->api_clientid;?> <span class="color-red">*</span></label>
                                            </div>
                                            <div class="col-md-6">
                                                <input class="form-control input-sm" type="text" name="api_clientid" id="api_clientid" required>
                                                <div id="invalid_api_clientid" class="invalid-feedback">
										            <?php echo $x->invalid_api_clientid; ?>
									            </div>
                                            </div>
                                        </div>
                                        <div class="row" id="access_token_div">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="api_access_token" class="control-label"><?php echo $x->api_access_token;?> <span class="color-red">*</span></label>
                                            </div>
                                            <div class="col-md-6">
                                                <p><input class="form-control input-sm" type="text" name="api_access_token" id="api_access_token"></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 offset-md-1">
                                                <!-- <label for="api_statusurl" class="control-label"><?php echo $x->api_statusurl;?> <span style="color:red">*</span></label> -->
                                                <label for="api_statusurl" class="control-label"><?php echo "Application URL";?> <span class="color-red">*</span></label>
                                            </div>
                                            <div class="col-md-6">
                                                <input class="form-control input-sm" type="text" name="api_statusurl" id="api_statusurl" required>
                                                <div id="invalid_api_statusurl" class="invalid-feedback">
										            <?php echo $x->invalid_api_statusurl; ?>
									            </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="api_dept" class="control-label"><?php echo $x->api_dept; ?></label>
                                            </div>
                                            <div class="col-md-4">
                                                <p><select name="api_dept" id="api_dept">
													<!-- <option value="None">None</option> -->
												</select></p>
                                            </div>
                                            <div class="col-md-3">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="api_appntype" class="control-label"><?php echo $x->api_appntype; ?></label>
                                            </div>
                                            <div class="col-md-4">
                                                <p><select name="api_appntype" id="api_appntype">
                                                    <option value="1">One-Way</option>
                                                    <option value="3">Two-Way</option>
                                                </select></p>
                                            </div>
                                            <div class="col-md-3">
                                                <!-- <a href="#" data-toggle="tooltip" data-html="true" title="<?php //echo $x->modem_desc; ?>"><i class="fa fa-2x fa-question-circle"></i></a> -->
                                            </div>
                                        </div>
                                        <!-- <div class="row" id="keyword_div">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="api_keyword" class="control-label"><?php echo "Keyword" ?> <span style="color:red">*</span></label>
                                            </div>
                                            <div class="col-md-6">
                                                <p><input class="form-control input-sm" type="text" name="api_keyword" id="api_keyword"></p>
                                            </div>
                                        </div>
                                        <div class="row" id="keyword_url_div">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="api_keyword_url" class="control-label"><?php echo "Keyword URL" ?> <span style="color:red">*</span></label>
                                            </div>
                                            <div class="col-md-6">
                                                <p><input class="form-control input-sm" type="text" name="api_keyword_url" id="api_keyword_url"></p>
                                            </div>
                                        </div> -->
                                        <div class="row">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="api_quota" class="control-label"><?php echo $x->api_quota;?> <span class="color-red">*</span></label>
                                            </div>
                                            <div class="col-md-6">
                                                <input class="form-control input-sm" type="text" name="api_quota" id="api_quota" required>
                                                <div id="invalid_api_quota" class="invalid-feedback">
										            <?php echo $x->invalid_api_quota; ?>
									            </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="sftp_subscribe" class="control-label">Subscribe SFTP</label>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="sftp_subscribe" id="inlineCheckbox1" value="0" checked>
                                                    <label class="form-check-label" for="inlineCheckbox1">No</label>
                                                </div>

                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="sftp_subscribe" id="inlineCheckbox2" value="1">
                                                    <label class="form-check-label" for="inlineCheckbox2">Yes</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 offset-md-1">
                                                <label for="sftp_swt" class="control-label">Enable SFTP Service</label>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="sftp_swt" id="inlineCheckbox3" value="2" checked>
                                                    <label class="form-check-label" for="inlineCheckbox3">No</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="sftp_swt" id="inlineCheckbox4" value="1">
                                                    <label class="form-check-label" for="inlineCheckbox4">Yes</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <input type="hidden" name="id" id="id">
                                        <input type="hidden" name="mode" id="mode">
                                        <button id="save" type="submit" class="btn btn-primary"><?php echo $xml_common->save;?></button>
                                        <button id="cancel" type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $xml_common->cancel;?></button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('footnote.php'); ?>
	</div>
    <?php 
        include("api_list_js.php");
    ?>

</body>
</html>