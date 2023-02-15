<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/datetime-moment.js"></script>
<script src="js/jquery.redirect.js"></script>
<script nonce="<?php echo session_id();?>">
$('#bc_date').datepicker({format: 'dd-mm-yyyy', startDate : new Date(), autoclose: true});
$('#scheduled').click(function(){
    if($(this).is(':checked')) {
		$('#divScheduled').removeClass('hidden');
		$('#mode').val('sendScheduledBC');
    } else {
		$('#divScheduled').addClass('hidden');
		$('#mode').val('sendBC');
	}
});

$('#bc_rcpt').on('click','button',function(e) {
  e.preventDefault();
  $(this).parent().remove();
});

var table1 = $('#tblbc_rcpt').DataTable({
	autoWidth: false,
	deferRender: false,
	processing: true,
	retrieve: true,
	ajax: {type:'POST',url:'broadcast_mim_lib.php',data:{mode:'list'}},
	'columnDefs': [{'targets': 3,'searchable': false,'orderable': false}]
});

$('#getContact').on('show.bs.modal', function(e) {
  $('#c_bc').change(function() {
    var cells = table1.cells().nodes();
	  $(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
  });
});

$('#s_cont').click(function(e) {
	var selected = [];
  var channel = '';
	$.each($("input[name='selected']:checked",table1.cells().nodes()), function(){
    channel = $(this).data('channel');
    var img = channelMIM(channel);
	  selected.push('<div><span>'+$(this).val()+img+'</span><input name="inc_id" id="inc_id" type="hidden" value="'+$(this).data('id')+
    '"><button class="close">&times;</button></div>');
	});
	$("#bc_rcpt").append(selected);
  $("#getContact").modal('hide');
});

$('#s_temp').click(function(e) {
	var content = $('#bc_msg_temp option:selected');
	$('#bc_text').val(content.text());
	$('#getTemplate').modal('hide');
});

$('.modal').on('hidden.bs.modal', function(e) {
	$('input[type=checkbox]').prop('checked',false);
});

$('#sendBCForm').on('submit', function(e) {
  var bc_length = $('#bc_rcpt div').length;
  var bc_image = $('#bc_img img').length;
  var content_type = $('#cont_type').val();
  var inc_id = [];
  if(bc_length > 0){
    if(bc_image <= 0 && content_type == 2 || content_type == 3){
      alert("Please select the broadcast image.");
      return false;
    } else{
    $.each($("input[name='inc_id']"), function(){
      inc_id.push($(this).val());
    });
    var bc_rcpt = inc_id.join(",");
    var bc_text = $('#bc_text').val();
    var bc_date = $("#bc_date").val();
  	var bc_hour = $("#bc_hour").val();
  	var bc_min = $("#bc_min").val();
    var mode = $("#mode").val();
    var bc_img = $('#mime_image').val();
console.log(bc_rcpt);
    var sendobj = $.param({'bc_rcpt':bc_rcpt,'bc_text':encodeURIComponent(bc_text),'bc_date': bc_date,'bc_hour': bc_hour,'bc_min':bc_min,'mode':mode,'mime_image':encodeURIComponent(bc_img)});
    $('#status').addClass('hidden');
    $.post('broadcast_mim_lib.php',sendobj,function(res) {
      $.redirect('bc_success.php', {'error':res.error, 'output':res.output});
    },"json");
    }
  } else{
    alert("Please insert broadcast recipient(s).");
    return false;
  }
  e.preventDefault();
});
$('.close').click(function() {
	$('#status').addClass('hidden');
});

loadlist('#bc_msg_temp','broadcast_mim_lib.php','temp','template_id','template_text');
function loadlist(selobj,url,mode,val,name) {
	$('#selobj option:gt(0)').remove();
	$.post(url,{mode:mode},function(data) {
		$.each(data, function(index, value) {
			$(selobj).append("<option value="+value[val]+">" + escapeHtml(value[name]) + "</option>");
		});
	}, "json");
}
function escapeHtml(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

var id2 = "<?php echo ( isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '' ); ?>";
if(id2){
  var inc_arr = id2.split(','), val_arr = [], inc_id2 = '', channel2 = '', name2 = '', selected2 = [];
  $.each(inc_arr, function(index, value) {
    val_arr = value.split(':');
    inc_id2 = val_arr[0];
    channel2 = val_arr[1];
    name2 = val_arr[2];

    if(inc_id2 != 'undefined'){
    var img2 = channelMIM(channel2);
    selected2.push('<div><span>'+name2+img2+'</span><input name="inc_id" id="inc_id" type="hidden" value="'+inc_id2+
    '"><button class="close">&times;</button></div>');
    }
  });
  $("#bc_rcpt").append(selected2);
}

function channelMIM(channel){
  var selected = [];
  var img = '';
  if(channel == 'FACEBOOK'){
    img = '<img src="images/icons/icon_messenger@2x.png">';
  } else if(channel == 'TELEGRAM'){
    img = '<img src="images/icons/icon_telegram@2x.png">';
  }else if(channel == 'SQOOPE'){
    img = '<img src="images/icons/icon_sqoope@2x.png">';
  }else if(channel == 'LINE'){
    img = '<img src="images/icons/icon_line@2x.png">';
	}else if(channel == 'LINE NOTIFY'){
    img = '<img src="images/icons/icon_line@2x.png">';
  }else if(channel == 'LIVECHAT'){
    img = '<img src="images/icons/icon_livechat@2x.png">';
  }else if(channel == 'SLACK'){
    img = '<img src="images/icons/icon_slack@2x.png">';
  }else if(channel == 'MICROSOFT TEAMS'){
    img = '<img src="images/icons/icon_teams@2x.png">';
  }else if(channel == 'VIBER'){
    img = '<img src="images/icons/icon_viber@2x.png">';
  }else if(channel == 'WECHAT'){
    img = '<img src="images/icons/icon_wechat@2x.png">';
  }else if(channel == 'WEBEX'){
    img = '<img src="images/icons/icon_webex.png">';
	}else if(channel == 'WHATSAPPDC'){
 		img = '<img src="images/icons/icon_whatsapp.png" width="24px" height="24px">';
  } else{
    img = '<img src="images/icons/icon_text@2x.png">';
  }
  return img;
}

var d = new Date();
var dateChecker = $('#bc_date').val();

var currDate = moment().format('DD-MM-YYYY');
var currHour = d.getHours();
var currMin = d.getMinutes();

if(dateChecker == currDate){
  $('#bc_hour option').each(function(){
    if($(this).val() < currHour){
      $(this).prop('disabled',true);
      $('#bc_min option').each(function(){
        if($(this).val() < currMin){
          $(this).prop('disabled',true);
        } else{
          $(this).prop('disabled',false);
        }
      });
    } else{
      $(this).prop('disabled',false);
    }
  });
}

//on date select
$('#bc_date').on('keyup change', function(){
  if(this.value == currDate){
    $('#bc_hour option').each(function(){
      if($(this).val() < currHour){
        $(this).prop('disabled',true);
        $('#bc_min option').each(function(){
          if($(this).val() < currMin){
            $(this).prop('disabled',true);
          } else{
            $(this).prop('disabled',false);
          }
        });
      } else{
        $(this).prop('disabled',false);

      }
    });
  } else if(this.value == ''){
    $('#bc_date').val(currDate);
  } else{
    $('#bc_hour option').prop('disabled',false);
    $('#bc_min option').prop('disabled',false);
  }
});

//on hour select
$('#bc_hour').on('change', function(){
    if($(this).val() == currHour){
      $('#bc_min option').each(function(){
        if($(this).val() < currMin){
          $(this).prop('disabled',true);
        } else{
          $(this).prop('disabled',false);
        }
      });
    } else{
      $(this).prop('disabled',false);
      $('#bc_min option').each(function(){
        $(this).prop('disabled',false);
      });
    }
});

//content type
$('#cont_type').on('change', function(){
  if($(this).val() == '1'){
    $('#img').hide();
    $('#bc_img').prop('required',false);
    $('#bc_text').prop('required',true);
    $('#txt').show();
  } else if ($(this).val() == '2'){
    $('#txt').hide();
    $('#bc_text').prop('required',false);
    $('#bc_img').prop('required',true);
    $('#img').show();
  } else{
    $('#txt').show();
    $('#bc_text').prop('required',true);
    $('#bc_img').prop('required',true);
    $('#img').show();
  }
});

//image upload
$('#img_btn').on('click', function(e){
  e.preventDefault();
  $('#img_file').trigger('click');
});

$("#img_file").change(function(){
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
		url: 'broadcast_mim_lib.php',
		type: 'POST',
		dataType:'json',
		data: formData,
		cache: false,
		contentType: false,
		processData: false,
		success: function(res){
			$('#progress').hide();
			var imgpath = res.imgpath;
			$('#bc_img').html('<img src="'+imgpath+'" style="height:100%">');
			$('#mime_image').val("MIME:IMAGE:"+imgpath);
		}
	});
	return false;
});

//image upload end
</script>
