<?php
	$page_title = "Conversation";
	include("header.php");
	$x = GetLanguage("conversation",$lang);

	//print_r( $x );
	//die;
?>
<div class="page-header">
  <ol class="breadcrumb">
    <li class="active"><?php echo $x->conversation; ?></li>
  </ol>
</div>
<div class="page-content">
  <div class="col-lg-12">
    <div id="conversation" class="panel panel-body">
    <div class="col-lg-3" style="padding: 0px; border-right: 1px solid #ddd;">
        <!--search-->
        <div class="input-group custom-search-form">
          <form id="search_conv_form" name="search_conv_form" method="post">
            <input id="search_conv" name="search_conv" type="text" class="form-control" placeholder="<?php echo $x->search_msg;?>">
          </form>
        </div>
        <div id="inbox"></div>
        <div id="refresh_div">
          <button id="refresh" type="button" class="btn btn-success btn-sm">
            <span class="glyphicon glyphicon-refresh"></span> <?php echo $x->refresh; ?>
          </button>
        </div>
    </div>
    <div class="col-lg-9" style="padding: 0px;">
        <div id="conv_header" class="panel-heading" style="display: none;">
          <div id="sub-title" style="display: inline;"><?php echo $x->conversation; ?></div>
          <div id="save_addr" style="float: right; margin-right: 35px; display: inline; position: relative; top: -5px;">
            <button id="save_inc" type="button" class="btn btn-sm" data-toggle="modal" data-target="#saveAddr" title="Save to Address Book">
              <span class="glyphicon glyphicon-earphone"></span> <span id="btn_label"></span>
            </button>
          </div>
          <div id="export" style="float: right;margin-right: 5px;display: inline;position: relative;top: -5px;">
            <button id="exp_inc" type="button" class="btn btn-sm" data-toggle="modal" title="Export Conversation">
              <img src="images/csv-solid.png" style="height: 15px;">
            </button>
          </div>
          <div id="assign" style="float: right;">
            <a data-toggle="dropdown" aria-expanded="false">
              <img src="images/icons/menu@3x.png" style="width: 30px;">
            </a>
            <ul class="dropdown-menu slidedown" id="ul_assign">
              <li>
                <h5 style="margin: auto auto 5px auto;"><strong><?php echo $x->assign_to; ?></strong></h5>
                <form role="form" id="assign_note">
                <div class="form-group">
                  <label><?php echo $x->user; ?></label>
                  <div>
                    <select id="asg_user" name="asg_user">
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label><?php echo $x->add_note; ?>:</label>
                  <textarea class="form-control" rows="3" name="assign_msg" id="assign_msg"></textarea>
                </div>
                <span style="float:right;">
                  <input type="hidden" name="activity_id" id="activity_id">
                  <button id="s_assign" type="submit" class="btn btn-default"><?php echo $x->assign_to; ?></button>
                  <button id="c_assign" type="reset" class="btn btn-default"><?php echo $x->cancel; ?></button>
                </span>
                </form>
              </li>
            </ul>
          </div>
        </div>
        <form id="chatform">
        <div id="conv_body">

          <div id="summary">
  					<div class="panel-group" id="accordion">
              <span style="position: absolute;padding: 230px;">
                <img src="images/sendQuickMessaging.png">
              </span>
              <span style="position: absolute;padding: 270px;">
                <?php $mim_icon = array('facebook','wechat','telegram','line','viber','slack','microsoftteams','webexteams','wechatwork');
                for($i=0;$i<count($mim_icon);$i++){
                echo "<img src=\"/appliance/images/mim/".$mim_icon[$i].".png\" style=\"height: 40px; width: 40px;\">";
                } ?>
                <img src="/appliance/images/sqoope.png" style="height: 40px; width: 40px;">
                <img src="images/icons/icon_whatsapp@3x.png" style="height: 40px; width: 40px;">
                <img src="images/icons/icon_text@3x.png" style="height: 40px; width: 40px;">
                <img src="images/icons/icon_livechat@3x.png" style="height: 40px; width: 40px;">
              </span>
            </div>
  				</div>
        </div>

          <input type="hidden" id="mode" name="mode" value="add">
          <input type="hidden" name="chat_activity_id" id="chat_activity_id">
          <input type="hidden" id="inc_id" name="inc_id">
          <input type="hidden" id="s_mode" name="s_mode" value="text">
          <div class="input-group" id="conv_input" style="display:none;">
            <input type="hidden" name="mime_image" id="mime_image" style="display:none;"/>
            <input id="message" name="message" type="text" class="form-control" placeholder="Type your message here..." style="height: 50px;background-color: #DADADA; border-radius: unset;border-width: 0px 0px 0px 0px;" />
            <span class="input-group-btn">
              <button id="conv_send" class="btn" style="background-color: #006837; border-radius: unset; height: 50px;width: 60px;" id><img src="images/icons/send.png"></button>
            </span>
            <span class="input-group-btn" id="img_btn_span" style="display:none;">
              <button id="img_btn" class="btn" style="background-color: #5bc0de; border-radius: unset; height: 50px;width: 60px;"><img src="images/img_btn.png"></button>
            </span>
          </div>
          <div id="no_write_div" style="display:none;">
            <span id="no_write"></span>
          </div>
        </form>
        <div id="upl_div" style="position: absolute; bottom: 50px; border-top: 1px solid #ddd; width: 100%; height: 90px; background-color: #E8E5E4; padding: 5px; display: none;">
          <form id="uploadForm" name="uploadForm" method="POST">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true" style="position: relative; bottom: 20px; background: grey; border-radius: 15px; padding: 2px;">×</button>
            <img id="img_upl" src="#" style="margin: 5px; height: 60px;">
            <input type="file" id="img_file" name="img_file" accept="image" style="display: none;"/>
            <div id="progress" class="row text-center" style="display:none">
            <div class="col-md-4 offset-md-4">
              <div class="progress">
                <div id="bar" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="min-width:0.5em;">
                  <?php echo $x->processing; ?> ... <span id="percent"></span>
                </div>
              </div>
            </div>
            </div>
            <input name="mode" type="hidden" value="uploadImage">
          </form>
        </div>
    </div>
    <!--Modal: Save to Address Book-->
    <?php $x = GetLanguage("address_book",$lang); ?>
    <div class="modal fade" id="saveAddr" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title" id="header"><?php echo $x->save_to_addr_book; ?></h4>
          </div>
          <form id="contact_form" name="contact_form" method="post">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-3 offset-md-1">
                <label for="contact" class="control-label"><?php echo $x->contact_name; ?></label>
              </div>
              <div class="col-md-6">
                <p><input class="form-control input-sm" type="text" name="contact" id="contact" required></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3 offset-md-1">
                <label for="mobile" class="control-label"><?php echo $x->mobile_number; ?></label>
              </div>
              <div class="col-md-4">
                <p><input class="form-control input-sm" type="text" name="mobile" id="mobile" pattern="\+?\d+" required></p>
              </div>
              <div class="col-md-3">
                <a href="#" data-toggle="tooltip" data-html="true" title="<?php echo $x->numbers_only; ?>"><i class="fa fa-2x fa-question-circle"></i></a>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3 offset-md-1">
                <label for="modem" class="control-label"><?php echo $x->modem_label; ?></label>
              </div>
              <div class="col-md-4">
                <p><select name="modem" id="modem">
                  <option value="None"><?php echo $x->none; ?></option>
                </select></p>
              </div>
              <div class="col-md-3">
                <a href="#" data-toggle="tooltip" data-html="true" title="<?php echo $x->modem_desc; ?>"><i class="fa fa-2x fa-question-circle"></i></a>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3 offset-md-1">
                <label class="control-label"><?php echo $x->list_add_group; ?></label>
              </div>
              <div class="col-md-6" id="grouplist"></div>
            </div>
          </div>
          <div class="modal-footer">
            <input type="hidden" name="id" id="id">
            <input type="hidden" name="mode" id="mode" value="saveContact">
            <button id="save" type="submit" class="btn btn-primary"><?php echo $xml_common->save; ?></button>
            <button id="cancel" type="button" class="btn btn-default" data-dismiss="modal"><?php echo $xml_common->cancel; ?></button>
          </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Image Preview -->
    <div class="modal fade" id="imagePrev" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content" style="background-color: transparent;">
          <div class="modal-body" style="text-align: center;">
            <img src="#" class="imagepreview" style="max-width: 100%;">
          </div>
        </div>
      </div>
    </div>
    <!--Modal End-->
  </div>
  </div>
</div>
<?php include('footnote.php'); ?>
</div>
<?php include('conversation_js.php'); ?>
</body>
</html>
