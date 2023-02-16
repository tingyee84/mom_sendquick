<?php
header("Content-type:text/javascript");
require_once('lib/commonFunc.php');
$x = GetLanguage("broadcast_sms",$lang);
$x2 = GetLanguage("menu",$lang); 
$x3 = GetLanguage("campaign_mgnt",$lang); 
$x4 = GetLanguage("send_sms",$lang); 
$x5 = GetLanguage("file_upload_status",$lang); 
?>
$( document ).ready(function() {
	$("#mimtext_div").hide();
	$( "#msg_template_li" ).click(function() {
		$( "#message_text, #tpl_info_1" ).val('');
		
		autosize($('#tpl_info_1'));
		autosize.update($('#tpl_info_1'));
				
	});
	
	$( "#global_template_li" ).click(function() {
		$( "#global_message_text, #tpl_info_1" ).val('');
		
		autosize($('#tpl_info_1'));
		autosize.update($('#tpl_info_1'));
	});
	
	$( "#mim_template_li" ).click(function() {
		$( "#mim_message_text, #tpl_info_1" ).val('');
		
		autosize($('#tpl_info_1'));
		autosize.update($('#tpl_info_1'));
	});
	
	$( "#global_mim_template_li" ).click(function() {
		$( "#global_mim_message_text, #tpl_info_1" ).val('');
		
		autosize($('#tpl_info_1'));
		autosize.update($('#tpl_info_1'));
	});
	
	$( "#message_text, #global_message_text, #mim_message_text, #global_mim_message_text" ).change(function() {
		
		var url = 'send_sms_lib.php';
		var data = { mode: 'get_template_datas', tpl_id: $(this).val(), field: 'template_text' };
		
		$.ajax({
				
			//dataType: 'txt',
			type:"POST",
			url: url,
			data:data,
			success: function (response){
				
				//alert(response);
				$( "#tpl_info_1" ).val( response );
				
				//alert( $( "#tpl_info_1" ).scrollHeight );
				autosize($('#tpl_info_1'));
				autosize.update($('#tpl_info_1'));
			
			}
				
		});
		
	});
	
});

$("#mim_file_type").change(function () {
	if ($(this).val() != 0) {
		$("#mim_image1_div").show();
	} else {
		$("#mim_image1_div").hide();
	}
	$('#mim_image1').val('');
	
});
	
$("#mim_image1").change(function () {
	
	var fileType = $("#mim_file_type").val();
	var fileExtension = '';
	
	if( fileType == "1" ){
		fileExtension = ['jpg','jpeg', 'png'];
	}else if( fileType == "2" ){
		fileExtension = ['pdf'];
	}
	
	if( fileExtension ){
		
		if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
			alert("Only formats are allowed : "+fileExtension.join(', '));
			$('#mim_image1').val('');
		}
		
	}else{
		
		if( $(this).val().split('.').pop().toLowerCase() ){
			alert('Please select MIM File Type');
			$('#mim_image1').val('');
			
		}
		
	}
		
	/*
	var mim_tpl = $('#mim_tpl').val();
	
	if( mim_tpl == "yes" ){
		alert( "MIM image not allowed in template message" );
		$('#mim_image1').val('');
	}
	*/
});

$("#upload_file").change(function () {
	var fileExtension = ['csv', 'xls', 'xlsx'];
	if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
		alert("Only formats are allowed : "+fileExtension.join(', '));
		$('#upload_file').val('');
	}
});
	
	
$('#mim_bot_div, #tpl_params_div, #mim_image1_div, #mim_file_div').hide();
function checkToHide(var1) {
	// Check the sendmode and content_type
	let var2 = $('#content_type').val();
	if (var2 == 3) {
		$('#smstext_div, #mimtext_div').hide();
		$('#mimtext, #smstext').removeAttr("required");
		if (var1 == "sms") {
			$('#callerid_div').show();
			$('#bot_id').attr('required', false);
			$('#mim_bot_div, #tpl_params_div, #mim_image1_div, #mim_file_div').hide();
			$('#smstext').attr('readonly', false);
			
			$('#mim_template_li, #global_mim_template_li').removeClass('active');
			$('#msg_template_li').addClass('active');
			
			$('#mim_template, #global_mim_template').removeClass('active in');
			$('#template').addClass('active in');
			$('#callerid').prop('required',true);
		} else if (var1 == "mim") {
			$('#mim_bot_div, #mim_file_div').show();
			$('#bot_id').attr('required', true);
			$('#callerid').attr('required', false);
			$('#smstext').removeAttr("required");
		} else if (var1 == "sms_mim") {
			$('#mim_bot_div, #mim_file_div').show();
			$('#bot_id').attr('required', true);
			$('#callerid').attr('required', true);
		}
	} else if (var2 == 1 || var2 == 2) {
		if (var1 == "sms") {
			$('#smstext_div').show();
			$('#smstext').attr("required",true);
			$('#mimtext_div').hide();
			$('#mimtext').removeAttr("required");

			$('#callerid_div').show();
			$('#bot_id').attr('required', false);
			$('#mim_bot_div, #tpl_params_div, #mim_image1_div, #mim_file_div').hide();
			$('#smstext').attr('readonly', false);
			
			$('#mim_template_li, #global_mim_template_li').removeClass('active');
			$('#msg_template_li').addClass('active');
			
			$('#mim_template, #global_mim_template').removeClass('active in');
			$('#template').addClass('active in');
			$('#callerid').prop('required',true);
		} else if (var1 == "mim") {
			$('#smstext_div').hide();
			$('#callerid_div').hide();
			$('#mim_bot_div, #mim_file_div').show();
			$('#bot_id').attr('required', true);
			$('#callerid').attr('required', false);
			$('#smstext').removeAttr("required");
			$('#mimtext_div').show();
			$('#mimtext').attr("required",true);
		} else if (var1 == "sms_mim") {
			$('#mimtext_div, #smstext_div').show();
			$('#callerid_div').show();
			$('#mim_bot_div, #mim_file_div').show();
			$('#bot_id').attr('required', true);
			$('#callerid').attr('required', true);
			$('#mimtext', '#smstext').attr("required",true);
		}
 	}
	if ($("#mim_file_type").val() != 0 && var1 != "sms") {
		$("#mim_image1_div").show();
	}
}
$( "input[name='sendmode']" ).change(function() {
	
	sendmode = this.value;
	$('#sendtype').val( sendmode );
	// mode: sms, mim, sms_mim

	checkToHide(sendmode);

	/*
	old statement
	if( sendmode == "sms_mim" || sendmode == "mim" ){
		$('#mim_bot_div, #mim_image1_div, #mim_file_div').show();
		$('#bot_id').attr('required', true);
		
		if( sendmode == "mim" ){
			$('#callerid').attr('required', false);
			$('#callerid_div').hide();
		}else{
			$('#callerid_div').show();
			$('#callerid').attr('required', true);
		}
		
	}else{
		$('#bot_id').attr('required', false);
		$('#mim_bot_div, #tpl_params_div, #mim_image1_div, #mim_file_div').hide();
		$('#smstext').attr('readonly', false);
		
		$('#mim_template_li, #global_mim_template_li').removeClass('active');
		$('#msg_template_li').addClass('active');
		
		$('#mim_template, #global_mim_template').removeClass('active in');
		$('#template').addClass('active in');
		
		$('#callerid_div').show();
		$('#callerid').prop('required',true);
		
	}
	*/
});

//$("input[name='sendmode']:checked").val();
$(".tab_button2").on('click', function(e){
		
	tpl_tab = $(e.target).attr("id");
	
	if( tpl_tab == 'mim_template_a' || tpl_tab == 'mim_template_b'  ){
		mim_tpl = 'yes';
	}else{
		mim_tpl = 'no';
	}
	
	$('#mim_tpl').val(mim_tpl);
	
});
$("#clear_tpl").on("click",function(e) {
	$('#mimtext').attr("readonly",false);
	$('#mimtext').val("");
	$('#mim_tpl').val('no');
	$('#mim_tpl_id').val('');
	$('#tpl_params_here').html('');
	$('#tpl_params_total').val('0');
	$('#tpl_params_div').hide();
	updateCounterMIM();
});
$(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
	
	//alert( $(e.target).attr("id") );
	tpl_tab = $(e.target).attr("id");
	
	if( tpl_tab == 'mim_template_a' || tpl_tab == 'mim_template_b'  ){
		mim_tpl = 'yes';
	}else{
		mim_tpl = 'no';
	}
	
	$('#mim_tpl').val(mim_tpl);
	
});
	
$('#sms_date').datepicker({format: 'dd-mm-yyyy'});
$('#smstext').on('keyup change',function() {
	updateCounter();
});

$('#mimtext').on('keyup change',function() {
	updateCounterMIM();
});
$('#scheduled').on("click",function(){
    if($(this).is(':checked')) {
		$('#divScheduled').removeClass('hidden');
    } else {
		$('#divScheduled').addClass('hidden');
	}
});
$('#selectTemplate').on("click",function(e) {
	// sms and mim share the same modal
	// button from modal, in order to fill the selected template into front textarea
	let tplmode = $("#tpl_mode").val();

	let content_type = $('#content_type').val();

	if (tplmode == "mim") {
		$('#mimtext').val($('#tpl_info_1').val());
		$("#mim_tpl_id").val( $('#myTabContent').find('.tab-pane.active').find('select[name="templateText"] option:selected').val() );

		$('#mimtext').attr('readonly', true);
		// load parameters
		var sendobj2 = $.param({ 
			'mode':'findTplParamElement',
			'tpl_id':$('#mim_tpl_id').val()
		});

		$.post('send_sms_lib.php',sendobj2,function(res) {
			//alert(res.element);
			if (typeof res.element === "undefined") {
				$('#tpl_params_here').html('');
				$('#tpl_params_total').val('0');
				$('#tpl_params_div').hide();
			}else{
				$('#tpl_params_here').html(res.element);
				$('#tpl_params_total').val(res.total);
			}
			
		},"json");
		
		if( content_type == "1" ){
			$('#tpl_params_div').show();
		} else {
			$('#tpl_params_div').hide();
		}
		
		updateCounterMIM();
	} else {
		$('#smstext').val($('#tpl_info_1').val());

		updateCounter();
	}
<?php 
	/*
	var mim_tpl = $('#mim_tpl').val();
	
	//var content = $('#myTabContent').find('.tab-pane.active').find('select[name="templateText"] option:selected');
	//$('#smstext').val(content.text());
	
	var actual_content = $('#tpl_info_1').val();
	$('#smstext').val( actual_content );
	
	if( mim_tpl == 'yes' ){
		//$('#mim_image1').val('');
		//$('#mim_image1_div').hide();
		$("#mim_tpl_id").val( $('#myTabContent').find('.tab-pane.active').find('select[name="templateText"] option:selected').val() );
	}else{
		$("#mim_tpl_id").val( '' );
	}
	*/

	// ShowTplParamDiv(); // should remove this part. copy the original func into here
?>
	$('#getTemplate').modal('hide');
});
$('form#broadcast_sms_form').submit(function(){
	
	var mim_params = "";
	var dataName = "";
	

	if( ( $("#sendtype").val() == "sms_mim" || $("#sendtype").val() == "mim" ) && $("#mim_tpl").val() == 'yes' ){ 
		
		for (i = 1; i <= $("#tpl_params_total").val(); i++) {
			
			dataName = "data" + i;
			
			if( mim_params == "" ){
				mim_params = dataName + "==" + $("#"+dataName).val();
			}else{
				mim_params = mim_params + "@@" + dataName + "==" + $("#"+dataName).val();
			}
			
		}
		
	}else{
		
		var mim_params = "";
	}
		
	$('#status').addClass('hidden');
    var formData = new FormData($(this)[0]);
	
	formData.append('mim_params', mim_params);
	formData.append('mim_file_type', $('#mim_file_type').val() );
	
    $.ajax({
        url: 'broadcast_sms_lib.php',
        type: 'POST',
        data: formData,
        async: false,
        contentType: false,
        processData: false,
        success: function(res){
			
			//alert(res);
			//return false;
			if( res == "1" ){
				
				$('#output').html('<?php echo $x->alert_12?>');
				$("#status").addClass("alert-info").removeClass("alert-danger");
				$('#status').removeClass('hidden');
			
			}else{
				
				$('#output').html('<?php echo $x->alert_13?>');
				$("#status").addClass("alert-danger").removeClass("alert-info");
				$('#status').removeClass('hidden');
				
			}
			/*
			if(res > 0) {
				$('#preview').modal('show');
			} else {
				alert('<?php echo $x->alert_3; ?>');
			}
			*/
		},
		error: function (xhr, ajaxOptions, thrownError) {
			alert(xhr.status);
			alert(thrownError);
		  }
	  
    });
    return false;
});
$('#preview').on('show.bs.modal', function(e) {
	$('#tblpreview').DataTable({
		autoWidth: false,
		deferRender: true,
		processing: true,
		retrieve: true,
		ajax: {type:'POST',url:'broadcast_sms_lib.php',data:{mode:'listBroadCast'}}
	});
});
$('#send').on('click', function(e){
	$('#status').addClass('hidden');
	$.post('broadcast_sms_lib.php',{mode:'sendBroadCast'},function(res) {
		$('#output').html(res.output);
		if (res.error > 0) {
			$("#status").addClass("alert-danger").removeClass("alert-info");
		} else {
			$("#status").addClass("alert-info").removeClass("alert-danger");
		}
		$('#status').removeClass('hidden');
		$('#preview').modal('hide');
		$('html, body').animate({ scrollTop: $('#status').offset().top }, 'slow');
	},"json");
});
$('#cancel').on('click', function(e) {
    if(confirm('<?php echo $x1->alert_3;?>?')) {
		$.post('broadcast_sms_lib.php',{mode:'deleteBroadCast'});
		$('#preview').modal('hide');
	}
});
$('.close').click(function() {
	$('#status').addClass('hidden');
});
function updateCounterMIM() {
	let message_text = $('#mimtext').val().replace(/\r(?!\n)|\n(?!\r)/g, "\r\n");
	
	// var text_length = message_text.length;
	let text_length = message_text.length;
	// console.log(text_length + " " + $("#mim_tpl").val());
	let max_length = 4096;
	if($("#mim_tpl").val() == "" && text_length > max_length) {
		alert('Normal Message cannot be exceed more than '+max_length+' characters. Your message will be trimmed.');
		$("#mimtext").val(message_text.substr(0, max_length));
	} 

	$("#count_charsmim").html(text_length);
}
function updateCounter() {
	var message_text = $('#smstext').val();
	checkUnicodeChar(message_text);
	var code_type = $('#character_set').val();
	var long_sms = '<?php echo $_SESSION['long_sms']; ?>';
	var max_sms = '<?php echo $_SESSION['max_sms']; ?>';
	// var text_length = message_text.length;
	var text_length = message_text.replace(/\r(?!\n)|\n(?!\r)/g, "\r\n").length;
	var max_length = 153;
	if(code_type == 'text') {
				if(text_length > 160){
					max_length = 153;
				}else{
					max_length = 160;
				}				
			} else {
				if(text_length > 70){
					max_length = 67;
				}else{
					max_length = 70;
				}				
		}
	$("#max_length").val(max_length);
	var total_length = max_length * max_sms;
	// var text_length = message_text.length;
	var text_length = message_text.replace(/\r(?!\n)|\n(?!\r)/g, "\r\n").length;
	$("#count_chars").html(text_length);
	if(text_length > total_length) {
		$("#smstext").val(message_text.substr(0, total_length));
		$("#count_chars").html(total_length);
		$("#sms_num").html(max_sms+ " / " +max_sms);
		alert('<?php echo $x->alert_9; ?> '+total_length+' <?php echo $x->alert_10; ?>');
		return;
	} else {
		var curr_sms = Math.ceil(text_length / max_length);
		$("#sms_num").html(curr_sms+ " / " +max_sms);
	}
}
loadlist('#message_text','send_sms_lib.php','listTemplate','template_id','template_name');
loadlist('#global_message_text','send_sms_lib.php','listGlobalTemplate','template_id','template_name');
loadlist3('#mim_message_text','send_sms_lib.php','listMIMTemplate','template_id','template_name', 'mim_tpl_id');
loadlist3('#global_mim_message_text','send_sms_lib.php','listGlobalMIMTemplate','template_id','template_name', 'mim_tpl_id');

function loadlist3(selobj,url,mode,val,name,mim_tpl_id) {
	$('#selobj option:gt(0)').remove();
	$.post(url,{mode:mode},function(data) {
		
		$(selobj).append("<option value=''>Please Select</option>");
		
		$.each(data, function(index, value) {
			
			var option_value = escapeHtml(value[name]) + "(Template ID:" + escapeHtml(value[mim_tpl_id]) + ")";
			//alert(option_value);
			$(selobj).append("<option value="+value[val]+">" + option_value + "</option>");
		});
	}, "json");
}

function loadlist(selobj,url,mode,val,name) {
	$('#selobj option:gt(0)').remove();
	$.post(url,{mode:mode},function(data) {
		
		$(selobj).append("<option value=''>Please Select</option>");
		
		$.each(data, function(index, value) {
			$(selobj).append("<option value="+value[val]+">"+escapeHtml(value[name])+"</option>");
		});
	}, "json");
}

function escapeHtml(text) {
		
	if( text ){
		
		var map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		
		text = text.replace(/[&<>"']/g, function(m) { return map[m]; });
		text = text.replace( "\n", "<br />" );
		
		return text;
		
		
	}else{
		
		return 'No Title';
	}
	
}
	
$('#content_type').on('change',function(e){
	checkToHide($('input[name=sendmode]:checked').val());
	if($('#content_type option:selected').val()==3){
		$('#tpl_params_div').hide();
	}else if( $('#content_type option:selected').val()==2 ){
		$('#tpl_params_div').hide();
	} else{
		$('#tpl_params_div').show();
	}
});

$('#getTemplate').on('show.bs.modal', function(e) {
	
	let sendmode = $(e.relatedTarget).data("templatetype");
	$("#tpl_mode").val(sendmode);
	if (sendmode == "mim") {
		$('#msg_template_li, #global_template_li').hide();
		$('#mim_template_li, #global_mim_template_li').show();
		$('#mim_template_li').find("button").click();
	} else {
		$('#msg_template_li, #global_template_li').show();
		$('#mim_template_li, #global_mim_template_li').hide();
		$('#msg_template_li').find("button").click();
	}
	
});


function ShowTplParamDiv(){
		
	var mim_tpl = $('#mim_tpl').val();
	var content_type = $('#content_type').val();
	
	if( mim_tpl == "yes" ){
		
		$('#smstext').attr('readonly', true);
		$('#tpl_params_div').show();
		
		//alert( $('#mim_tpl_id').val() );
		//load param element
		var sendobj2 = $.param({ 
									'mode':'findTplParamElement',
									'tpl_id':$('#mim_tpl_id').val()
									});
									
		$.post('send_sms_lib.php',sendobj2,function(res) {
			
			//alert(res.element);
			if (typeof res.element === "undefined") {
				
				$('#tpl_params_here').html('');
				$('#tpl_params_total').val('0');
				$('#tpl_params_div').hide();
				
			}else{
				
				$('#tpl_params_here').html(res.element);
				$('#tpl_params_total').val(res.total);
			
			}
			
		},"json");
		
		if( content_type == "1" ){
			
			$('#tpl_params_div').show();
			
		}else{
			
			$('#tpl_params_div').hide();
		}
		
	}else{
		
		$('#smstext').attr('readonly', false );
		$('#tpl_params_div').hide();
		$('#tpl_params_here').html('');
		
	}
	
}

function loadlist2(selobj,url,val,name){

	$('#selobj option:gt(0)').remove();
	$.getJSON(url,function(data)
	{
	
		//console.log(data);
		$.each(data, function(index, value) {
			$(selobj).append("<option value=" + value[val] + ">" + escapeHtml(value[name]) + "</option>");
		});
		
	});
	
}

function checkUnicodeChar(msg) {
	var isUnicode = 'N';
	for (var i = 0; i < msg.length; i++){
		if(msg.charCodeAt(i)> 127){
			isUnicode = 'Y';
			break;
		}
	}

	if(isUnicode == 'Y'){
        character_set = "utf8";
	}else{
        character_set = "text";
    }
    $('#character_set').val(character_set);
}

loadlist2('#campaign_id','campaign_lib.php?mode=select','campaign_id','campaign_name');
loadlist2('#callerid','broadcast_sms_lib.php?mode=select_callerid','callerid','label');
loadlist2('#bot_id','broadcast_sms_status_lib.php?mode=select_bot','bot_id','bot_name');

