<script src="js/PageTitleNotification.min.js"></script>
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/moment.min.js"></script>
<script nonce="<?php echo session_id();?>">

$("#from_dt").val(moment().format('YYYY-MM-DD'));
$("#to_dt").val(moment().format('YYYY-MM-DD'));
$('#from_dt, #to_dt').datepicker({
  format: 'yyyy-mm-dd'
})
$('#from_dt, #to_dt').on('changeDate', function() {
  $('#from_dt, #to_dt').datepicker('hide');
});

setTimeout(function(){
	location.reload()
},35*60*1000);
var userid = '<?php echo $_SESSION['userid']; ?>';

loadlist('#asg_user','user_account_lib.php?mode=listAssign','id','userid');
chatUser();
$(document).one('ready', function () {
  setTimer();
});

setTimeout(function(){
	getNotification();
},1000);

function loadlist(selobj,url,val,name)
{
	$('#selobj option:gt(0)').remove();
	$.getJSON(url,function(data)
	{
		$.each(data, function(index, value) {
			if(value[name].length!=0) {
				$(selobj).append('<option value="' + value[val] + '">' + value[name] + '</option>');
			}else{
				$(selobj).append('<option value="">No user</option>');
			}

		});
	});
}

function chatUser(search_conv){
	$.ajax({
		cache: false,
		url: 'conversation_lib.php?mode=user',
		data: search_conv,//'mode=user',
		type:'POST',
		dataType:'json',
		success: function(data)
		{

			var header = '<ul class="chat">';
			var list2 = '',icon = "",img = "",reply = "",assign = "",edit = "",lock = "",status = "", badge="", title="";
			var end = "</ul>";
			$.each(data, function(index, value) {
				if(value['sender']==userid){
					reply = '<img title="Replied by you" src="images/icons/status_reply_you.png">';
				} else{
					reply = '<img title="Replied by '+value['sender']+'" src="images/icons/status_reply_non.png">';
				}

				if(value['assign_to']==userid){
					assign = '<img title="This conversation is assigned to you" src="images/icons/status_paper_on.png">';
				} else{
					assign = '<img title="This conversation not assigned to you" src="images/icons/status_paper_off.png">';
				}

				if(value['assign_flag']=='t'){
					lock = '<img title="This conversation is locked for reply" src="images/icons/status_lock_on.png">';
				} else{
					lock = '<img title="This conversation is unlocked for reply" src="images/icons/status_lock_off.png">';
				}

				if(value['channel']=='SMS' || value['channel']=='LIVECHAT' || value['channel']=='WHATSAPP' || value['channel']=='WHATSAPPDC' ){
					if(value['channel']=='SMS'){
						img = 'icon_text@2x.png';
					} else if(value['channel']=='LIVECHAT'){
						img = 'icon_livechat@2x.png';
					} else if(value['channel']=='WHATSAPP' || value['channel']=='WHATSAPPDC'){
						img = 'icon_whatsapp.png';
					}
					icon = '<img src="images/icons/'+img+'" style="height: 20px; width: 20px;">';
				} else{
					if(value['channel']=='LINE'){
					 img = 'mim/line.png';
					} else if(value['channel']=='LINE NOTIFY'){
					 img = 'mim/line.png';
				 	} else if(value['channel']=='FB' || value['channel']=='FACEBOOK'){
					 img = 'mim/facebook.png';
				 	} else if(value['channel']=='WECHAT'){
					 img = 'mim/wechat.png';
				 	} else if(value['channel']=='SLACK'){
					 img = 'mim/slack.png';
				 	} else if(value['channel']=='TELEGRAM'){
					 img = 'mim/telegram.png';
				 	} else if(value['channel']=='VIBER'){
					 img = 'mim/viber.png';
				 	} else if(value['channel']=='MICROSOFT TEAMS'){
					 img = 'mim/microsoftteams.png';
				 	} else if(value['channel']=='WEBEX'){
					 img = 'mim/webexteams.png';
				 	} else if(value['channel']=='WECHAT WORK'){
					 img = 'mim/wechatwork.png';
				 	} else if(value['channel']=='SQOOPE'){
					 img = 'sqoope.png';
				 	} else{
					 img = 'icon_text@3x.png';
				 	}
					icon = '<img src="/appliance/images/'+img+'" style="height: 20px; width: 20px;">';
				}

					if(value['using_by'] == null || value['using_by'] == 'null'){
						edit = '<img title="No one is replying" src="images/icons/status_pencil_off.png">';
					} else{
						edit = '<img title="'+value['using_by']+' is replying" src="images/icons/status_pencil_on.png">';
					}

					status = '<span class="status">'+assign+edit+lock+reply+'</span>';
					if(value['unread_flag']=='t'){
						list2 += '<li class="content active">';
						badge = '<span class=\'badge progress-bar-warning\' style="float: right;">'+value['c_unread_flag']+'</span>';
					}else{
						list2 += '<li class="content">';
						badge = '';
					}
					list2 += '<a data-id="'+data[index]['id']+'"><span id="conv_id">'+data[index]['title']+' '+icon+'</span><br/><p>';
					list2 += '<span class="ellipsis" title="'+data[index]['msg']+'">';
					if(value['mime_flag']==1){
						list2 += '<span class="glyphicon glyphicon-picture"></span><i> Image attached.</i>';
					} else{
						if(data[index]['msg'] == ''){
							list2 += '<i>blank message</i>';
						} else{
							list2 += data[index]['msg']+badge;
						}
					}
					list2 += '</span><br><span class="conv_date">Last replied by '+data[index]['sender']+'...</span>';

					if(value['type']=='assign'){
						list2 += '<br><span class="conv_date">Assigned to '+data[index]['assign_to']+'</span>';
					}
					list2 += status+'</p></a><i data-id="'+data[index]['chat_id']+'" data-name="'+data[index]['assignee']+'" data-flag="'+data[index]['assign_flag'];
					list2 += '" data-user="'+data[index]['using_by']+'"></i></li>';
			});
			$('#inbox').html(header+list2+end);
			$('#archive').html(header+list2+end);
		}
	});
}

$('#conversation #inbox').on('click','ul li', function(){
	$('#conv_header').show();
	$('#save_inc').show();
	$('#assign').show();
	$('#upl_div').hide();
	$('#mime_image').val('');
	$('#img_upl').prop('src','#');
	$("#message").prop("readonly", false);

		var inc_id = $(this).closest('li').find('a').data('id');
		var chat_id = $(this).closest('li').find('i').data('id');
		var assignee = $(this).closest('li').find('i').data('name');
		var assign_flag = $(this).closest('li').find('i').data('flag');
		var reply_by = $(this).closest('li').find('i').data('user');
		var assign_arr = [];

		var unread_flag = updateUnreadFlag(inc_id,chat_id);
		if(unread_flag==1){
			$(this).removeClass('active');
			getNotification();
		}
		// Set Cookie for inc_id
		Cookies.set('id', inc_id);
		Cookies.set('chat_id', chat_id);
		Cookies.set('flag',assign_flag);
		Cookies.set('reply_by', reply_by);
		Cookies.set('scrolled', false);

		if(assignee != null){
			assignee = assignee.replace(/[\])}[{(]/g, '');
			assign_arr = assignee.split(',');
		}
		Cookies.set('assignee', assignee);
		$('#inc_id').val(inc_id);
		$('#activity_id').val(chat_id);
		$('#chat_activity_id').val(chat_id);
		//active link
		var link = $(this).closest('li').find('a').data('id');
		if(link){
			$(this).addClass('active');
		}else{
			$('ul.chat li').removeClass('active');
		}
		Cookies.set('link', link);

		var res = if_replying(reply_by,chat_id);
		if($.inArray(userid,assign_arr) > 0 || $.inArray(userid,assign_arr) == 0){
			if(res == 1){
				updateReply(chat_id);
			} else if(res == 0){
				updateNotReply(chat_id);
				console.log("not reply");
			}
		} else{
			if(userid == 'useradmin' || assign_flag == 'f'){
				if(res == 1){
					updateReply(chat_id);
					$('#conv_input').show();
					$('#no_write_div').hide();
				} else if(res == 0){
					updateNotReply(chat_id);
				}
			} else{
				var not_assign = "You're unable to reply to this message because you're not assigned to the conversation" ;
				$('#conv_input').hide();
				$('#no_write').html(not_assign);
				$('#no_write_div').show();
			}
		}
		chatroom(inc_id,false);
	});


$('#conv_send').on('click',function(e){
	var inc_id = Cookies.get('id');
	Cookies.set('scrolled', false);
	if(!$('#message').val() && !$('#mime_image').val()){
		$('#conv_input').hide();
		$('#no_write').html('Do not send empty messages');
		$('#no_write_div').show();
		setTimeout(function(){
			$('#conv_input').show();
			$('#no_write').html('');
			$('#no_write_div').hide();
    }, 1000);
	} else{
		$.ajax({
			cache: false,
			url: 'conversation_lib.php',
	      data: $('#chatform').serialize(),
	      type: 'POST',
	      dataType:'json',
	      success: function(res){
					console.log(res);

					$('#accordion').ajax.reload();
				}
		});
		loadpage();
		$('#message').val('');
		$('#mime_image').val('');
		$('#upl_div').hide();
		$('#img_upl').prop('src','#');
	}
	e.preventDefault();
});

// chatroom
function chatroom(inc_id,scrolled) {
	$.ajax({
		cache: false,
		url: 'conversation_lib.php',
		data: 'mode=list&id='+inc_id,
		type:'POST',
		dataType:'json',
		success: function(data)
		{
				var start = "",list = "",temp = "",icon = "",img = "",title = "",ic_title = "",ic_img = "";
				var end = '</ul>';
				start = '<ul class="chat">';

				if(data[0]['add_flag'] == 1){
					$('#save_inc').removeClass('btn-success').addClass('btn-info');
					$('#btn_label').html('Edit');
				} else{
					$('#save_inc').removeClass('btn-info').addClass('btn-success');
					$('#btn_label').html('Save');
				}

				if(data[0]['channel']=='SMS' || data[0]['channel']=='LIVECHAT' || data[0]['channel']=='WHATSAPP' || data[0]['channel']=='WHATSAPPDC' ){
					if(data[0]['channel']=='SMS'){
						ic_img = 'icon_text@2x.png';
					} else if(data[0]['channel']=='LIVECHAT'){
						ic_img = 'icon_livechat@2x.png';
					} else if(data[0]['channel']=='WHATSAPP'|| data[0]['channel']=='WHATSAPPDC'){
						ic_img = 'icon_whatsapp.png';
					}
					ic_title = '<img src="images/icons/'+ic_img+'" style="position:absolute; height: 30px; width: 30px; top: 10px;">';
				} else{
					if(data[0]['channel']=='LINE'){
					 ic_img = 'mim/line.png';
           $('#img_btn_span').show();
					} else if(data[0]['channel']=='LINE NOTIFY'){
					 ic_img = 'mim/line.png';
           $('#img_btn_span').hide();
				 	} else if(data[0]['channel']=='FB' || data[0]['channel']=='FACEBOOK'){
					 ic_img = 'mim/facebook.png';
           $('#img_btn_span').show();
				 	} else if(data[0]['channel']=='WECHAT'){
					 ic_img = 'mim/wechat.png';
           $('#img_btn_span').hide();
				 	} else if(data[0]['channel']=='SLACK'){
					 ic_img = 'mim/slack.png';
           $('#img_btn_span').hide();
				 	} else if(data[0]['channel']=='TELEGRAM'){
					 ic_img = 'mim/telegram.png';
           $('#img_btn_span').show();
				 	} else if(data[0]['channel']=='VIBER'){
					 ic_img = 'mim/viber.png';
           $('#img_btn_span').hide();
				 	} else if(data[0]['channel']=='MICROSOFT TEAMS'){
					 ic_img = 'mim/microsoftteams.png';
           $('#img_btn_span').show();
				 	} else if(data[0]['channel']=='WEBEX'){
					 ic_img = 'mim/webexteams.png';
           $('#img_btn_span').show();
				 	} else if(data[0]['channel']=='WECHAT WORK'){
					 ic_img = 'mim/wechatwork.png';
           $('#img_btn_span').hide();
				 	} else if(data[0]['channel']=='SQOOPE'){
						ic_img = 'sqoope.png';
            $('#img_btn_span').hide();
					} else{
						ic_img = 'icon_text@3x.png';
            $('#img_btn_span').hide();
					}
					ic_title = '<img src="/appliance/images/'+ic_img+'" style="position:absolute; height: 30px; width: 30px; top: 10px;">';
				}
				title = '<span>'+data[0]['display_name']+'&nbsp;'+ic_title+'</span>';

					$.each(data, function(index, value) {
						list += start;

						if(value['type']=='outgoing' || value['type']=='assign' || value['type']=='livechat'){
							img = 'img_default.png';
							icon = '<img src="images/icons/'+img+'">';
						} else{
							if(value['channel']=='SMS' || value['channel']=='LIVECHAT' || value['channel']=='WHATSAPP' || value['channel']=='WHATSAPPDC'){
								if(value['channel']=='SMS'){
									img = 'icon_text@2x.png';
								} else if(value['channel']=='LIVECHAT'){
									img = 'icon_livechat@2x.png';
								} else if(value['channel']=='WHATSAPP' || value['channel']=='WHATSAPPDC'){
									img = 'icon_whatsapp.png';
								}
								icon = '<img src="images/icons/'+img+'" style="height: 40px; width: 40px;">';
							} else{
								if(value['channel']=='LINE'){
								 img = 'mim/line.png';
								} else if(value['channel']=='LINE NOTIFY'){
								 img = 'mim/line.png';
 								} else if(value['channel']=='FB' || value['channel']=='FACEBOOK'){
								 img = 'mim/facebook.png';
							 	} else if(value['channel']=='WECHAT'){
								 img = 'mim/wechat.png';
							 	} else if(value['channel']=='SLACK'){
								 img = 'mim/slack.png';
							 	} else if(value['channel']=='TELEGRAM'){
								 img = 'mim/telegram.png';
							 	} else if(value['channel']=='VIBER'){
								 img = 'mim/viber.png';
							 	} else if(value['channel']=='MICROSOFT TEAMS'){
								 img = 'mim/microsoftteams.png';
							 	} else if(value['channel']=='WEBEX'){
								 img = 'mim/webexteams.png';
							 	} else if(value['channel']=='WECHAT WORK'){
								 img = 'mim/wechatwork.png';
							 	} else if(value['channel']=='SQOOPE'){
								 img = 'sqoope.png';
							 	} else{
								 img = 'icon_text@3x.png';
							 }
								icon = '<img src="/appliance/images/'+img+'" style="height: 40px; width: 40px;">';
							}
						}

							if(value['type']=='assign'){
								list += '<li class="left clearfix assign"><span class="chat-img pull-left">'+icon+'</span><div class="chat-body clearfix"><div class="header">';
								list += '<strong class="primary-font">'+value['from']+'</strong><small class="pull-right text-muted assign"><i class="fa fa-clock-o fa-fw"></i>'+value['dtm'];
								list += '</small></div><p>'+value['msg']+'</p></li>';
							} else{
								list += '<li class="left clearfix"><span class="chat-img pull-left">'+icon+'</span><div class="chat-body clearfix"><div class="header">';
								list += '<strong class="primary-font">'+value['from']+'</strong><small class="pull-right text-muted"><i class="fa fa-clock-o fa-fw"></i>'+value['dtm'];
								if(value['bc_id']!==null){
									list += '</small><small style="background-color: forestgreen;color: white;padding: 3px;border-radius: 5px;">Broadcast';
								}
								list += '</small></div><p>';
								if(value['mime_flag']==1){
									list += '<a id="img_prev" data-toggle="modal" data-id="'+value['msg']+'" data-target="#imagePrev"><img src="'+value['msg']+'" style="height:50px;" /></a>';
								} else{
									if(value['msg'] == ''){
										list += '<i>blank message</i>';
									} else{
										if(value['channel'] === 'WHATSAPPDC' && value['message_status'] === 'F') {
											list +=  value['msg'] + '<img data-msgid="'+value['msgid']+'" id="retryButton" title="Message failed. Click to retry" alt="Message failed. Click to retry" style="margin-left: 20px;" src="images/icons/retry.png" width="24px" height="24px"/>';
										} else {
											list += value['msg'];
										}
									}
								}
								list += '</p></li>';
							}
						temp = value['from'].substr(value['from'].length - 8);
					});
					$('#accordion').html(list+end);
					$('#sub-title').html(title);

					if($('#conv_body').scrollTop == $("#accordion")[0].scrollHeight){
						var inc_id = Cookies.get('id');
						setInterval(function(){
							chatroom(inc_id,false) // this will run after every 100 miliseconds
						}, 100);
					}
			    if(!scrolled){
			      $("#conv_body").prop({ scrollTop: $("#accordion")[0].scrollHeight});
			    }
		}
	});
}

$(document).on('click', '#retryButton', function(obj){
	var msgid = $("#retryButton").data('msgid');
	console.log('hello retry', msgid);
	$.post('conversation_lib.php?mode=retry',{msgid:msgid},function(data){
	});
});


//assign menu
$('.dropdown-menu').on('click', function(e) {
  e.stopPropagation();
});

$('#c_assign').on('click', function(e){
	$('#assign').find('a').dropdown('toggle');
});

$('#assign_note').on('submit',function(e){
	var inc_id = Cookies.get('id');
	Cookies.set('scrolled', false);
	if(!$('#assign_msg').val()){
		$('#assign_msg').val('Do not send empty assignment notes');
		$('#assign_msg').attr('disabled',true);
		setTimeout(function(){
			$('#assign_msg').val('');
      $('#assign_msg').attr('disabled',false);
    }, 1000);
	} else{
		$.ajax({
			cache: false,
			url: 'conversation_lib.php',
	    data: 'mode=addAssign&'+$('#assign_note').serialize(),
	    type: 'POST',
	    dataType:'json',
	    success: function(res){
				console.log(res);
			}
		});
	}
	e.preventDefault(e);
	loadpage();
	// clear the textbox
	$('#assign_msg').val("");
	// close the assign form
	$('#assign').find('a').dropdown('toggle');
	chatroom(inc_id,false);
});
//assign end

// backend reload chat room
function loadpage(){
	var inc_id = Cookies.get('id');
	var chat_id = Cookies.get('chat_id');
	var assignee = Cookies.get('assignee');
	var assign_flag = Cookies.get('flag');
	var reply_by = Cookies.get('reply_by');
	var link = Cookies.get('link');
	var scrolled = Cookies.get('scrolled');
	var searching = Cookies.get('searching');
	var assign_arr = [];
	if (inc_id != undefined || inc_id != null) {
		chatroom(inc_id,scrolled);
		chatUser();

		var reply_by2 = getReplyingBy();
		console.log("reply_by2:::"+reply_by2);

		console.log("reply_by:::"+reply_by);

		if(assignee != null){
			assignee = assignee.replace(/[\])}[{(]/g, '');
			assign_arr = assignee.split(',');
		}
		var resload = if_replying(reply_by,chat_id);
		if($.inArray(userid,assign_arr) > 0 || $.inArray(userid,assign_arr) == 0){
			//content left blank
		} else{
			if(userid == 'useradmin' || assign_flag == 'f'){
				//content left blank
			} else{
				$('#conv_input').hide();
				$('#no_write_div').show();
			}
		}
		getNotification();
	} else{
		chatUser();
	}
	getNotification();
}

loadpage(); // This will run on page load
var setTime;
function setTimer(){
setTime = setInterval(function(){
    loadpage(); // this will run after every 5 seconds
}, 5000);
}

setInterval(function(){
	load_new_notification();
}, 5000);
// backend reload chat room end

//search messages
$('#search_conv').on('change keyup press', function(){
		chatUser($('#search_conv_form').serialize());
		Cookies.set('searching',true);
		clearInterval(setTime);
});

$('#search_conv').on('blur', function(){
	$(this).val('');
	chatUser();
	setTimer();
});
//search messages end

//if someone is replying
function if_replying(reply_by,chat_id){
	var res = 0;
	if(userid == 'useradmin'){
		$('#conv_input').show();
		$('#no_write_div').hide();
		res = 1;
	} else{
	if(reply_by == userid){
		$('#conv_input').show();
		$('#no_write_div').hide();
		res = 1;
	} else{
		if(reply_by == null || reply_by == 'null' || reply_by == 'undefined'){
			$('#conv_input').show();
			$('#no_write_div').hide();
			res = 1;
		}else{
			var is_used = "You're unable to reply because <strong>"+reply_by+"</strong> is reply right now";
			$('#conv_input').hide();
			$('#no_write').html(is_used);
			$('#no_write_div').show();
			res = 0;
		}
	}}
	return res;
}
//replying end
//update reply_by
function updateReply(chat_id){
	$.post('conversation_lib.php?mode=using',{using_by:userid,activity_id:chat_id},function(data){
		//console.log('replying');
		loadpage();
	});
}
//update reply_by end
//update reply_by
function updateNotReply(chat_id){
	$.post('conversation_lib.php?mode=not_using',{not_using_by:userid,activity_id:chat_id},function(data){
		//console.log('not replying');
		loadpage();
	});
}
//update reply_by end

//scroll chatroom
$("#conv_body").on('scroll', function(){
	var inc_id = Cookies.get('id');
	chatroom(inc_id,true);
	Cookies.set('scrolled',true);
});
//scroll chatroom end

//refresh manual
$('#refresh').on('click', function(){
	var inc_id = Cookies.get('id');
	if(inc_id == undefined){
		chatUser();
	} else{
		loadpage();
	}
});
//refresh manual end

$('#saveAddr').on('submit',function(e){
	$.ajax({
		cache: false,
		url: 'conversation_lib.php?inc_id='+$('#inc_id').val(),
		data: $("#contact_form").serialize(),
		type: 'POST',
		success: function(data){
			if(data!='') {
				alert(data);
			} else {
				loadpage();
				$('#saveAddr').modal('hide');
			}
		}, error: function(){
			alert("Failed to Retrieve Data!");
		}
	});
	e.preventDefault();
});

$('#saveAddr').on('show.bs.modal', function(e){
	var modal = $(this), id = $('#inc_id').val();

	$.ajax({
		cache: false,
		url: 'conversation_lib.php',
		data:'mode=listAddr&inc_id='+id,
		type:'POST',
		dataType:'json',
		success: function(val)
		{
			$('#contact').val(val.display_name);
			$('#mobile').val(val.mobile);
			if(val.group_string.length > 0) {
				if (val.group_string.indexOf(",") >= 0) {
					var group_arr = val.group_string.split(',');
					for (var i=0; i < group_arr.length; i++) {
						modal.find('input:checkbox[value='+group_arr[i]+']').prop("checked", true);
					}
				} else {
					modal.find('input:checkbox[value='+val.group_string+']').prop("checked", true);
				}
			} else {
				$("input:checkbox").prop('checked', false);
			}
		}
	})
});
$('#saveAddr').on('hidden.bs.modal', function () {
	$(this).find('form').trigger('reset');
});

//image preview
$('#imagePrev').on('show.bs.modal', function(e){
	src = $(e.relatedTarget).data('id');
	$('.imagepreview').attr('src', src);
});

$('#imagePrev').on('hidden.bs.modal', function () {
	$(this).find('img').attr('src','#');
});
//end preview


//NOTIFICATION PROCESS BELOW HERE
// request permission on page load
function load_new_notification() {
	$.ajax({
		cache: false,
		url: 'conversation_lib.php',
		data:'mode=notification',
		type: 'POST',
		cache: false,
		dataType:'json',
		success: function(data)
		{
			//console.log(data[0].name);
			// notifyMe(data.name,data.msg,data.roomID,data.chat_id);
			if (data) {
				notifyMe(data);
			}
		}
	});
}

document.addEventListener('DOMContentLoaded', function () {
  if (!Notification) {
    alert('Desktop notifications not available in your browser. Try Chromium.');
    return;
  }

  if (Notification.permission !== "granted")
	// get permission to run notifications
    Notification.requestPermission().then(function(result) {
		  console.log(result);
		});
});

//notifyMe('yoke sun', 'testing here', '123','456');

function notifyMe(data) {
	//console.log("notifyMe");

	if (Notification.permission !== "granted")
		Notification.requestPermission();
	else {
		//console.log("START notifyMe PROCESS");
		var name = data[0].name;
		var msg = '';//data[0].msg;
		var inc_id = data[0].roomID;
		var chat_id = data[0].chat_id;
		var noti_flag = data[0].noti_flag;
		if(noti_flag==1){
			msg = 'Image attached..';
		} else{
			msg = data[0].msg;
		}


		var notification = new Notification('Webapp Conversation', {
			icon: 'images/logo_sendquick.jpg',
			sound: 'audio/notification.mp3',
			body: name+' says: '+msg,
		});


		notification.onclick = function () {
			Cookies.set('id', inc_id);
			Cookies.set('chat_id', chat_id);
			Cookies.set('scrolled', true);
			parent.focus();
			window.focus(); //just in case, older browsers
			this.close();
			getNotification();
			$('#conv_header').show();
			loadpage();
			$('#search_conv').blur();
		};
	}
}

var nCounter = 0;

// Set up event handler to produce text for the window focus event
window.addEventListener("focus", function(event)
{
	var inc_id = Cookies.get('id');
	var chat_id = Cookies.get('chat_id');
  chatroom(inc_id);
	updateUnreadFlag(inc_id,chat_id);
	getNotification();
	$('#inc_id').val(inc_id);
	$('#activity_id').val(chat_id);
	$('#chat_activity_id').val(chat_id);
  nCounter = nCounter + 1;
}, false);

//To call tab notifcations
//pageTitleNotification.on("New Message!", 1000);

//notify unread counter
function getNotification(){
	$.ajax({
		cache: false,
		url: 'conversation_lib.php',
		data: 'mode=getNotiCount',
		type:'POST',
		dataType:'json',
		success: function(data)
		{
			var title = '<?php echo $page_title ?>';
			var c_unread_flag = data[0]['c_unread_flag'];
			if(c_unread_flag>0){
				$(document).prop('title', "("+c_unread_flag+") "+title);
			} else{
				$(document).prop('title', title);
			}
		}
	});
}

//update unread_flag
function updateUnreadFlag(inc_id,chat_activity_id){
	$.ajax({
			cache: false,
			url: 'conversation_lib.php',
			data: 'mode=updateUnreadFlag&inc_id='+inc_id+'&chat_activity_id='+chat_activity_id,
			type:'POST',
			dataType:'json',
			success: function(data)
			{
				return data.status;
			},
			error: function(){
				alert('Failed To Retrieve Rule');
			}
		});
}

function loadcheck(chkobj,url,val,name)
{
	$('#chkobj input[type=checkbox]').remove();
	$.getJSON(url,function(data)
	{
		$.each(data, function(index, value) {
			$(chkobj).append('<input type="checkbox" name="group[]" value="'+value['group_id']+'"/> '+value['group_name']+'<br>');
		});
	});
}
loadcheck('#grouplist','address_book_lib.php?mode=getGlobalGroup','group_id','group_name');

$(window).on('load', function(){
	var test_id = Cookies.get('id');
	if(test_id !== undefined){
		$('#conv_header').show();
		$('#conv_input').show();
	} else{
		$('#chat_activity_id').val('0');
		var no_chat_id = $('#chat_activity_id').val();
		updateNotReply(no_chat_id);
	}
});

$(window).on('beforeunload',function(){
	$('#chat_activity_id').val('0');
	var no_chat_id = $('#chat_activity_id').val();
	updateNotReply(no_chat_id);
	return;
});

//getreplyingby
function getReplyingBy(){
	var chat_id = $('#chat_activity_id').val();
	$.ajax({
		cache: false,
		url: 'conversation_lib.php',
		data: 'mode=replying_by&activity_id='+chat_id,
		type:'POST',
		dataType:'json',
		success: function(data)
		{

		}
	});
}

function getChatID(inc_id){
	$.ajax({
		cache: false,
		url: 'conversation_lib.php',
		data: 'mode=getchatid&inc_id='+inc_id,
		type:'POST',
		dataType:'json',
		success: function(data)
		{
			return data.chat_id;
		}
	});
}

//image upload
$('#img_btn').on('click', function(e){
  e.preventDefault();
  $('#img_file').trigger('click');
});

$("#img_file").change(function(){
	$('#upl_div').show();
	$('#uploadForm').submit();
});

$('form#uploadForm').submit(function(){
	var formData = new FormData($(this)[0]);
	$.ajax({
		xhr: function() {
			var xhr = $.ajaxSettings.xhr();
			if(xhr.upload){
				xhr.upload.onprogress = function(e) {
					if (e.lengthComputable) {
						var percent = Math.floor(e.loaded / e.total *100);
						$('#progress').show();
						$('#percent').html(percent+'%');
						$('#bar').attr('aria-valuenow',percent+'%').css('width',percent+'%');
					}
				};
			}
			return xhr;
		},
		url: 'conversation_lib.php',
		type: 'POST',
		dataType:'json',
		data: formData,
		cache: false,
		contentType: false,
		processData: false,
		success: function(res){
			$('#progress').hide();
			var imgpath = res.imgpath;
			$('#img_upl').prop('src',imgpath);
			$('#mime_image').val("MIME:IMAGE:"+imgpath);
			$("#message").prop("readonly", true);
		}
	});
	return false;
});

$('.close').click(function() {
	$('#upl_div').hide();
	$('#mime_image').val('');
	$('#img_upl').prop('src','#');
	$("#message").prop("readonly", false);
});
//image upload end
//Export conversation
$('#exp_inc').on('click', function(e){
  var inc_id = $('#inc_id').val();
  e.preventDefault();
  $.redirect("conv_export.php", {'inc_id':inc_id}, 'POST', '_blank');
});
//Export end

</script>
