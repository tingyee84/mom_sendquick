<?php
/** 
 * TODO:
 * 1. When choose mim template, character is no limit, then
 **/
header("Content-type:text/javascript");
?>
function pushMsg(xdata) {
	var result;
	$.ajax({
		type: "POST",
		enctype: 'multipart/form-data',
		url: "send_sms_lib.php",
		data: xdata,
		processData: false,
		contentType: false,
		//cache: false,
		//timeout: 600000,
		success: function (return_data) { // Takes time, caller should not do anything! 
			result = JSON.parse(return_data);
			$('#output').html(result.output);
			if (result.error > 0) {
				$("#status").addClass("alert-danger").removeClass("alert-info");
			} else {
				$("#status").addClass("alert-info").removeClass("alert-danger");
				$('#submit').attr('disabled', true);
			}

			$('#status').removeClass('hidden');
			$('html, body').animate({ scrollTop: $('#status').offset().top }, 'slow');

			$('#clear').attr('disabled', false);
			$('#mim_image1').val('');
			
		},
		error: function (e) {
			alert( 'error' );
		}
	});
}
$( document ).ready(function() {
	$("#btn_status_close").click(function(ev) {
		$("#status").slideUp();
	});
	$("#msg_mim").hide();
	$("#btn_clear_tpl").click(function(ev) {
		$("#tpl_id, #tpl_type, #tpl_params_total").val('');
		$('#tpl_params_here').html('');
		$('#tpl_params_total').val('0');
		$('#tpl_params_div').hide();
		$("#mimtext").val('');
		$("#mimtext").attr('readonly',false);
		updateCounterMIM();
	});
	$( "#msg_template_li" ).click(function() {
		$( "#message_text, #tpl_info_1" ).val('');
		
		autosize($('#tpl_info_1'));
		autosize.update($('#tpl_info_1'));
				
	});
	
	$( "#global_msg_template_li" ).click(function() {
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
	
	var sendtype='sms';	// only have sms, mim and sms_mim only, changes then user click the radio
	$('#sms_date').datepicker({format: 'dd-mm-yyyy'});
	$('#smstext').on('keyup change',function() {
		updateCounter();
	});
	$('#mimtext').on('keyup change',function() {
		updateCounterMIM();
	});
	var table1,table2,table3,table4,contact_type;
	
	$('.openadrbook').on('click', function(e){
		
		$('#choosetype_1').prop('checked', true);
		$('#choosetype_2').prop('checked', false);
		contact_type = $(this).data('type');
	
		table1list(contact_type);
		table2list(contact_type);
		table3list(contact_type);
		table4list(contact_type);
		//$('#getContact').modal('show');
		$('#modalclick').val(contact_type);
		
		var sendmode = $("input[name='sendmode']:checked").val();
	
		if( sendmode == "sms" ){
			
			$("#tblglobal_contact_choosetype").hide();
			$("#tblglobal_group_choosetype").hide();
			$("#msg_mim").hide();
			$('#msg_sms').show();
			
		}else if( sendmode == "both"){
			
			$("#tblglobal_contact_choosetype").show();
			$("#tblglobal_group_choosetype").show();
			
		}else if(  sendmode == "sms_mim"){
			
			$("#tblglobal_contact_choosetype").hide();
			$("#tblglobal_group_choosetype").hide();
			$("#msg_mim").show();
			$('#msg_sms').show();
		}else if(sendmode == "mim" ){
			
			$("#tblglobal_contact_choosetype").hide();
			$("#tblglobal_group_choosetype").hide();
			$("#msg_mim").show();
			$('#msg_sms').hide();
		}
		
		if( contact_type == "mobile" ){
		
			$("#choosetype_A").html('<?php echo $x1->choosetype_1?>');
			$("#choosetype_B").html('<?php echo $x1->choosetype_2?>');
			
			$("#choosetype_C").html('<?php echo $x1->choosetype_1?>');
			$("#choosetype_D").html('<?php echo $x1->choosetype_2?>');
			
		}else if( contact_type == "email" ){
			
			$("#choosetype_A").html('<?php echo $x1->choosetype_1?>');
			$("#choosetype_B").html('<?php echo $x1->choosetype_3?>');
			
			$("#choosetype_C").html('<?php echo $x1->choosetype_1?>');
			$("#choosetype_D").html('<?php echo $x1->choosetype_3?>');
			
		}else{
			
			$("#choosetype_A").html('<?php echo $x1->choosetype_1?>');
			$("#choosetype_B").html('<?php echo $x1->choosetype_2?>');
			
			$("#choosetype_C").html('<?php echo $x1->choosetype_1?>');
			$("#choosetype_D").html('<?php echo $x1->choosetype_2?>');
		}
		
	});
	
	$('#choosetype_1, #choosetype_2').click(function(e) {
		
		var EmailMobile = $('#choosetype_1').prop('checked');
		var EmailOnly = $('#choosetype_2').prop('checked');
		
		if( EmailMobile ){
			UpdateTable3(contact_type,'EmailMobile');
		}else if( EmailOnly ){
			UpdateTable3(contact_type,'EmailOnly');
		}else{
			UpdateTable3(contact_type,'EmailMobile');
		}
	
	});
	
	$(".tab_button").on('click', function(e){
		
		var tab_no = $(e.target).attr("id"); 
		//alert( 'tab_button' + tab_no );
		
		$('#tab_id').val(tab_no);
	
	});
	
	$(".tab_button2").on('click', function(e){
		
		var tab_no = $(e.target).attr("id"); 
		//alert( 'tab_button' + tab_no );
		
		$('#tpl_type').val(tab_no);
	});
	
	/*
	$(document).on('shown.bs.tab', 'a[data-bs-toggle="tab"]', function (e) {
		//alert('show tab');
		
		var tab_no = $(e.target).attr("id"); 
		$('#tab_id').val(tab_no);
		$('#tpl_type').val(tab_no);

		if( tab_no == "tab3" ){
			
		}else if( tab_no == "tab3" ){
			
		}
	
	});
	*/
	
	$('#getContact').on('show.bs.modal', function(e) {
		
		$('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
			$($.fn.dataTable.tables(true)).DataTable().columns.adjust();
		});
		$('#c1').change(function() {
			var cells = table1.cells().nodes();
			$(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
		});
		$('#c2').change(function() {
			var cells = table2.cells().nodes();
			$(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
		});
		$('#c3').change(function() {
			var cells = table3.cells().nodes();
			$(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
		});
		$('#c4').change(function() {
			var cells = table4.cells().nodes();
			$(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
		});
	});
	$('#s_cont').click(function(e) {
		
		var selected = [];
		var selemail = [];
		var selected2 = [];
		var selemail2 = [];
		var modalclick = $('#modalclick').val();
		
		$.each($("input[name='selected']:checked",table1.cells().nodes()), function(){
			selected.push($(this).val());
		});
		$.each($("input[name='selected']:checked",table2.cells().nodes()), function(){
			selected.push($(this).val());
		});
		$.each($("input[name='selected']:checked",table3.cells().nodes()), function(){
			
			//selected.push($(this).val());
			
			var list = $(this).val();
			//alert(list);
			//if(sendtype == 'both'){
				var tmp = list.split('|');
				
				//console.log(tmp[0]);
				
				if (typeof tmp[0]  !== "undefined" && tmp[0] != 'on' ){
					selected2.push(tmp[0]);
				}
				
				if (typeof tmp[1]  !== "undefined"){
					selemail2.push(tmp[1]);
				}
				
			//} else {
				//selected2.push($(this).val());
			//}
			
		});
		
		//console.log(selected2);
		//console.log(selemail2);
		//return false;
		$.each($("input[name='selected']:checked",table4.cells().nodes()), function(){
		
			var list = $(this).val();
			if(sendtype == 'both'){
				var tmp = list.split('|');
				
				if( tmp[0].length > 0 ){
					selected.push(tmp[0]);
				}
				
				if( tmp[1].length > 0 ){
					selemail.push(tmp[1]);
				}
		
			} else {
				
				if( $(this).val().length > 0 ){
					selected.push($(this).val());
				}
				
			}
		});
		
		//alert(selected);

		var tab_id = $('#tab_id').val();
		
		//alert( 'tab_id==' + tab_id );
		//console.log(tab_id);
		if( tab_id == "tab3" ){
			//alert( 'tab_id ==' + tab_id);
			var radioValue = $("input[name='choosetype2']:checked").val();
			var new_value = selected2.join("\n");
			var new_email = selemail2.join("\n");
		}else if( tab_id == "tab4" ){
			//alert( 'tab_id ==' + tab_id);
			var radioValue = $("input[name='choosetype']:checked").val();
			var new_value = selected.join("\n");
			var new_email = selemail.join("\n");
		}else{
			var radioValue = $("input[name='choosetype']:checked").val();
			var new_value = selected.join("\n");
			var new_email = selemail.join("\n");
		}
		
		//alert(new_value);
		//alert(new_email);
		//alert( 'radioValue ==' + radioValue);
		//alert( 'sendtype ==' + sendtype);
		
		if(radioValue == 'both' && sendtype == 'both'){
			
			//alert( 'mobile length =='+ $("#mobile").val().length );
			//alert( 'email length =='+ $("#email").val().length );
			//alert( $("#email").val() );
			//alert( new_email );
			
			if($("#mobile").val().length > 0){
				new_value = $("#mobile").val() + '\n' + new_value;
			}
			$("#mobile").val(new_value.replace(/,/g,'\n'));
			
			if($("#email").val().length > 0){
				new_email = $("#email").val() + '\n' + new_email;
			}
			$("#email").val(new_email.replace(/,/g,'\n'));
			
			//alert( $("#email").val() );
			
		} else {
			
			if(modalclick == 'mobile'){
				if($("#mobile").val().length > 0){
					new_value = $("#mobile").val() + '\n' + new_value;
				}
				$("#mobile").val(new_value.replace(/,/g,'\n'));
			} else {
				if($("#email").val().length > 0){
					new_email = $("#email").val() + '\n' + new_email;
				}
				$("#email").val(new_email.replace(/,/g,'\n'));
			}
		}
		$("#getContact").modal('hide');
	});
	
	$('#s_temp').click(function(e) {
		
		/*
		//$('#smstext').prop('readonly', true);
		var content = $('#myTabContent').find('.tab-pane.active').find('select[name="template_text"] option:selected');
		
		var str = content.text();
		var n = str.indexOf("(Template ID:");
		
		if( n > 0 ){
			var actual_content = str.substr(0, n );
			var mim_tpl_id = str.substr(n);
			
		}else{
			var actual_content = str;
			var mim_tpl_id = '';
		}
		*/
		
		var actual_content = $('#tpl_info_1').val();
		//alert(mim_tpl_id);
		//alert(actual_content);
		//$('#smstext').val(content.text());
		if ($("#mim_template_li").is(":visible")) {
			$('#mimtext').val( actual_content );
			updateCounterMIM();

		} else {
			$('#smstext').val( actual_content );
			updateCounter();
		}
		
		$('#getTemplate').modal('hide');
		
		$("#tpl_id").val( $('#myTabContent').find('.tab-pane.active').find('select[name="template_text"] option:selected').val() );
		ShowTplParamDiv();
	
	});

	$('.modal').on('hidden.bs.modal', function(e) {
		$('input[type=checkbox]').prop('checked',false);
	});

	$('#scheduled').click(function(){
		if($(this).is(':checked')) {
			$('#divScheduled').removeClass('hidden');
			$('#mode').val('sendScheduledSMS');
		} else {
			$('#divScheduled').addClass('hidden');
			$('#mode').val('sendSMS');
		}
	});
	
	$('#sendForm').on('submit', function(e) {
	
		console.log("Anchor Test");
		$('#output').html("sending.....");
		$("#status").addClass("alert-info");
		$('#status').removeClass('hidden');
		
		document.getElementById("submit").disabled = true;
		
		$('#submit, #clear').attr('disabled', true);
		//return false;
		var mobile = $("#mobile").val();
		var email = '',eml_fr='',eml_subj='';
		var charset = $("#charset").val();
		var smstext = $("#smstext").val();
		var mimtext = $("#mimtext").val();
		var priority = $("#priority").val();
		var sms_date = $("#sms_date").val();
		var sms_hour = $("#sms_hour").val();
		var sms_min = $("#sms_min").val();
		var mode = $("#mode").val(); // sendsms_v1, sendsms
		var max_length = $("#max_length").val();
		var count_chars = $("#count_chars").val();
		var count_charsmim = $("#count_charsmim").val();
		var campaign_id = $("#campaign_id").val();
		var bot_id = $("#bot_id").val();
	
		if(sendtype == 'both'){ // check remark ontop ignore following statement
			email = $("#email").val();
			eml_fr = $("#eml_fr").val();
			eml_subj = $("#eml_subj").val();
		}
		
		var mim_params = "";
		var dataName = "";
		
		if( ( sendtype == "sms_mim" || sendtype == "mim" ) && ( $("#tpl_type").val() == 'mim_msg_template' || $("#tpl_type").val() == 'global_mim_msg_template' ) ){
			
			for (i = 1; i <= $("#tpl_params_total").val(); i++) {
				
				dataName = "data" + i;
				
				if( mim_params == "" ){
					mim_params = dataName + "==" + $("#"+dataName).val();
				}else{
					mim_params = mim_params + "@@" + dataName + "==" + $("#"+dataName).val();
				}
				
			}
			
		}
<?php		
		/*
		//console.log("sendtype:"+sendtype);
		var sendobj = $.param({ 'mobile':mobile,
								'email':email,
								'eml_fr':eml_fr,
								'eml_subj':eml_subj,
								'charset':charset,
								'smstext':encodeURIComponent(smstext),
								'priority': priority,
								'sms_date': sms_date,
								'sms_hour': sms_hour,
								'sms_min':sms_min,
								'mode':mode,
								'sendtype':sendtype,
								'max_length':max_length,
								'count_chars': count_chars,
								'campaign_id': campaign_id,
								'bot_id': bot_id,
								'tpl_type': $("#tpl_type").val(),
								'tpl_id': $("#tpl_id").val(),
								'mim_params': mim_params
								});
							
		//alert( sendobj );
		//console.log( sendobj );
		//return false;
		
		//$('#status').addClass('hidden');

		$.post('send_sms_lib.php',sendobj,function(res) {
			
			//alert(res);
			//$('#submit, #clear').attr('disabled', false);
			//return false;
			
			$('#output').html(res.output);
			if (res.error > 0) {
				$("#status").addClass("alert-danger").removeClass("alert-info");
			} else {
				$("#status").addClass("alert-info").removeClass("alert-danger");
			}
			$('#status').removeClass('hidden');
			$('html, body').animate({ scrollTop: $('#status').offset().top }, 'slow');
			
			$('#submit, #clear').attr('disabled', false);
			
		},"json");
		*/
		
		/*
		$.post('send_sms_lib.php', $( "#sendForm" ).serialize(), function(res) {
			
			alert(res);
			$('#submit, #clear').attr('disabled', false);
			return false;
			
			$('#output').html(res.output);
			if (res.error > 0) {
				$("#status").addClass("alert-danger").removeClass("alert-info");
			} else {
				$("#status").addClass("alert-info").removeClass("alert-danger");
			}
			$('#status').removeClass('hidden');
			$('html, body').animate({ scrollTop: $('#status').offset().top }, 'slow');
			
			$('#submit, #clear').attr('disabled', false);
			
		},"text");
		*/
?>	
		
        // Create formdata
        var form = $('#sendForm')[0];	// not used?
        var data = new FormData();
		data.append('mobile', mobile);
		data.append('email', email);	// Not necessary
		data.append('eml_fr', eml_fr);	// not necessary
		data.append('eml_subj', eml_subj);	// not necessary
		// data.append('smstext', encodeURIComponent(smstext) );
		data.append('priority', priority);
		data.append('sms_date', sms_date);
		data.append('sms_hour', sms_hour);
		data.append('sms_min', sms_min);
		data.append('mobile', mobile);
		data.append('mode', mode);
		// data.append('sendtype', sendtype);
		data.append('max_length', max_length);
		data.append('campaign_id', campaign_id );
		data.append('bot_id', bot_id);
		data.append('tpl_type', $("#tpl_type").val() );
		data.append('tpl_id', $("#tpl_id").val() );
		data.append('mim_params', mim_params);
		data.append('mim_image1',  $('#mim_image1')[0].files[0] );
		data.append('callerid', $("#callerid").val() );
		data.append('mim_file_type', $("#mim_file_type").val() );
		
		$('#submit, #clear').attr('disabled', false);
		
		<?php // Ty suggests if sendtype is sms then to split into two records ?>
		if (sendtype == "sms") {
			if (smstext != ""){
				data.append('sendtype', "sms");
				data.append('count_chars', count_chars);
				data.append('smstext', encodeURIComponent(smstext));
				pushMsg(data);
			} else {
				console.log("Require SMS TEXT");
				$("smstext").focus();
			}
		} else if (sendtype == "mim") {
			if (mimtext != "") {
				data.append('sendtype', "mim");
				data.append('count_chars', count_charsmim);
				data.append('smstext', encodeURIComponent(mimtext));
				pushMsg(data);
			} else {
				console.log("Require MIM TEXT");
				$("#mimtext").focus();
			}
		} else if (sendtype == "sms_mim") {
			if (smstext == "") {
				console.log("sms required!");
				$("#smstext").focus();
			} else if (mimtext == "") {
				console.log("mim required!");
				$("#mimtext").focus();
			} else {
				// TODO: optimisation is required for ajaxing two type of msg. also go unify one with above so easily maintain the code. use function and return boolean
				// update: currently stuck at this section.
				// individual can
				data.append('sendtype', "mim");
				data.append('count_chars', count_charsmim);
				data.append('smstext', encodeURIComponent(mimtext));

				var finalresult = 0;

				// runtime A
				$.ajax({
					type: "POST",
					enctype: 'multipart/form-data',
					url: "send_sms_lib.php",
					data: data,
					processData: false,
					contentType: false,
					//cache: false,
					//timeout: 600000,
					success: function (return_data) {
						//alert( return_data );
						//return false;
						var res = JSON.parse( return_data ); 
						//alert(res.output);
						if (res.error == "0") {
							finalresult += 1;
						}
					},
					error: function (e) {
						alert( 'error' );
					}
				});

				// runtime B
				data.set('sendtype', "sms");
				data.set('count_chars', count_chars);
				data.set('smstext', encodeURIComponent(smstext));
				$.ajax({
					type: "POST",
					enctype: 'multipart/form-data',
					url: "send_sms_lib.php",
					data: data,
					processData: false,
					contentType: false,
					//cache: false,
					//timeout: 600000,
					success: function (return_data) {
						//alert( return_data );
						//return false;
						var res = JSON.parse( return_data ); 
						if (res.error == "0") {
							finalresult += 2;
						}
					},
					error: function (e) {
						alert( 'error' );
					}
				});
				/*
				runtime a & b have been executed, but the return will not be at the time so using interval to get finalresult
				*/
				var counterLog = 0;
				var timer = setInterval(function() {
					console.log(counterLog++);

					if (counterLog > 60 || finalresult == 3) {
						clearInterval(timer);
						if (finalresult == 3) {
							// either 1 or 2, then continue
							$("#status").addClass("alert-info").removeClass("alert-danger");
							$('#submit').attr('disabled', true);
							$('#output').html("Message has been successfully inserted into queue");
						} else {
							$("#status").addClass("alert-danger").removeClass("alert-info");
							$('#output').html("One of message is failed to be sent out.");
						}

						$('#status').removeClass('hidden');
						$('html, body').animate({ scrollTop: $('#status').offset().top }, 'slow');
						$('#clear').attr('disabled', false);
						$('#mim_image1').val('');
					}
				},1000);
			}
		}
		
		e.preventDefault();
	});
	$('.close').click(function() {
		$('#status').addClass('hidden');
	});
	
	$('input[type=radio][name=sendmode]').change(function() {
		
		$('#scheduled').attr('checked', false);
		$('#divScheduled').addClass('hidden');
		
		if (this.value == 'sms') {
			$('#email-block').hide();
			sendtype='sms';
			$('#scheduled').attr('disabled', false);
			
			$('#msg_template_li').addClass('active');
			$('#global_template, #mim_template_li, #global_mim_template_li').removeClass('active');
			
			$('#template').addClass('active in');
			$('#global_template, #mim_template, #global_mim_template').removeClass('active in');
			
			$('#smstext').attr('readonly', false);
			
			$('#tpl_params_total, #tpl_type, #tpl_id').val('');
			$('#msg_mim').hide();
			$('#msg_sms').show();
		} else if (this.value == 'both') {
			$('#email-block').show();
			sendtype='both';
			$('#scheduled').attr('disabled', true);
			
			$('#divScheduled').addClass('hidden');
			$('#mode').val('sendSMS');
		
		} else if (this.value == 'sms_mim') {
			
			$('#email-block').hide();
			sendtype='sms_mim';
			$('#scheduled').attr('disabled', false);
			
			$('#msg_mim, #msg_sms').show();
		} else if (this.value == 'mim') {
			
			$('#email-block').hide();
			sendtype='mim';
			$('#scheduled').attr('disabled', false);

			$('#msg_sms').hide();
			$('#msg_mim').show();
			
		}	
		
		ShowTplParamDiv();
		ShowMIMBotDiv( this.value );		
		//$('#scheduled').attr('disabled', true);
	});
	
	var access_arr = <?php echo json_encode($access_arr); ?>;
	if($.inArray('3',access_arr) == -1){
		$('#tab3').hide();
		$('#tab4').hide();
	}
	
	if($.inArray('4',access_arr) == -1){
		$('#tab1').hide();
		$('#tab2').hide();
		$('#li1 > a').removeClass('active');
		$('#contacts').removeClass('tab-pane fade in active').addClass('tab-pane fade');
		$('#tab3').tab('show');
	}
	
	loadlist('#message_text','send_sms_lib.php','listTemplate','template_id','template_name');
	loadlist('#global_message_text','send_sms_lib.php','listGlobalTemplate','template_id','template_name');
	loadlist3('#mim_message_text','send_sms_lib.php','listMIMTemplate','template_id','template_name', 'mim_tpl_id');
	loadlist2('#global_mim_message_text','send_sms_lib.php','listGlobalMIMTemplate','template_id','template_name', 'mim_tpl_id');
	
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
	
	function loadlist2(selobj,url,mode,val,name,mim_tpl_id) {
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
				$(selobj).append("<option value="+value[val]+">" + escapeHtml(value[name]) + "</option>");
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
	
	function table1list(contact_type){

		$('#tblcontact').DataTable().clear();
		$('#tblcontact').DataTable().destroy();
		table1 = $('#tblcontact').DataTable({
			autoWidth: false,
			deferRender: false,
			processing: true,
			retrieve: true,
			ajax: {type:'POST',url:'send_sms_lib.php',data:{mode:'listContacts',contacttype:contact_type,sendtype:sendtype}},
			'columnDefs': [{'targets': 3,'searchable': false,'orderable': false}]
		});
	}
	
	function table2list(contact_type){
		$('#tblgroup').DataTable().clear();
		$('#tblgroup').DataTable().destroy();
		table2 = $('#tblgroup').DataTable({
			autoWidth: false,
			deferRender: false,
			processing: true,
			retrieve: true,
			ajax: {type:'POST',url:'send_sms_lib.php',data:{mode:'listGroup',contacttype:contact_type,sendtype:sendtype}},
			'columnDefs': [{'targets': 3,'searchable': false,'orderable': false}]
		});
	}
	
	function UpdateTable3( contact_type, RadioType ){
		
		//alert(RadioType);
		$('#c3').prop('checked', false);
		
		$('#tblglobal_contact').DataTable().clear();
		$('#tblglobal_contact').DataTable().destroy();
		table3 = $('#tblglobal_contact').DataTable({
			autoWidth: false,
			deferRender: false,
			processing: true,
			retrieve: true,
			ajax: {type:'POST',url:'send_sms_lib.php',data:{mode:'listGlobalContacts',contacttype:contact_type,sendtype:sendtype,RadioType:RadioType}},
			'columnDefs': [{'targets': [4,5],'searchable': false,'orderable': false}]
		});
		
	}
	
	function table3list(contact_type){
		
		var RadioType = 'EmailMobile';
		$('#tblglobal_contact').DataTable().clear();
		$('#tblglobal_contact').DataTable().destroy();
		table3 = $('#tblglobal_contact').DataTable({
			autoWidth: false,
			deferRender: false,
			processing: true,
			retrieve: true,
			ajax: {type:'POST',url:'send_sms_lib.php',data:{mode:'listGlobalContacts',contacttype:contact_type,sendtype:sendtype,RadioType:RadioType}},
			'columnDefs': [{'targets': [4,5],'searchable': false,'orderable': false}]
		});
	}
	
	function table4list(contact_type){
		
		$('#tblglobal_group').DataTable().clear();
		$('#tblglobal_group').DataTable().destroy();
		table4 = $('#tblglobal_group').DataTable({
			autoWidth: false,
			deferRender: false,
			processing: true,
			retrieve: true,
			ajax: {type:'POST',url:'send_sms_lib.php',data:{mode:'listGlobalGroup',contacttype:contact_type,sendtype:sendtype}},
			'columnDefs': [{'targets': 4,'searchable': false,'orderable': false}]
		});
		
	}
	
	function updateCounter() {
		var message_text = $('#smstext').val();
		checkUnicodeChar(message_text);
		var code_type = $('#charset').val();
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
		$("#count_chars").val(text_length);
		$("#count_chars2").html(text_length);
		if(text_length > total_length) {
			$("#smstext").val(message_text.substr(0, total_length));
			$("#count_chars").val(total_length);
			$("#count_chars2").html(total_length);
			$("#sms_num").html(max_sms+ " / " +max_sms);
			alert("<?php echo $x->alert_9; ?> "+total_length+ " <?php echo $x->alert_10; ?>");
			return;
		} else {
			var curr_sms = Math.ceil(text_length / max_length);
			$("#sms_num").html(curr_sms+ " / " +max_sms);
		}
	}
	function updateCounterMIM() {
		var message_text = $('#mimtext').val();
		checkUnicodeChar(message_text);
		// var text_length = message_text.length;
		var text_length = message_text.replace(/\r(?!\n)|\n(?!\r)/g, "\r\n").length;

		$("#count_charsmim").val(text_length);
		$("#count_charsmim2").html(text_length);

		// TODO: require backend to check
		if ($("#tpl_id").val() == "" && text_length > 4096) {
			$("#mimtext").val(message_text.substr(0, 4096));
			alert("Number character of Normal Message / Non-template Message cannot exceed more than 4096!");			
		}
	}
	
	$("#mim_file_type").change(function () {
		
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
		
    });
	

	<?php if (isset($_GET["mobile_numb"])) { 
	echo "$('#mobile').val('".$_GET["mobile_numb"]."')";
} else if (isset($_POST["msgid"])) { 
	echo <<< END
	$.ajax({
		type: "POST",
		url: "send_sms_lib.php",
		cache: false,
		data: "mode=getMessage&msgid={$_POST["msgid"]}", 
		dataType: 'json',
		success: function (val) {
			$("#smstext").val(val["message"]);
			$("#mobile").val(val["mobile_numb"]);
			updateCounter();
		}
	});
END;
} ?>
});

$('#getTemplate').on('show.bs.modal', function(e) {
	let sendmode = $(e.relatedTarget).data("templatetype");
	
	if( sendmode == "sms") {
		ShowHideMIMTemplate('hide');
		
	} else if (sendmode == "mim") {
		ShowHideMIMTemplate('show');
	}
	
});

function ShowTplParamDiv(){
		
	var tab_no = $('#tpl_type').val();

	if( tab_no == "mim_msg_template" || tab_no == "global_mim_msg_template" ){
		
		$('#mimtext').attr('readonly', true);
		$('#tpl_params_div').show();
		
		//alert( $('#tpl_id').val() );
		//load param element
		var sendobj2 = $.param({ 
									'mode':'findTplParamElement',
									'tpl_id':$('#tpl_id').val()
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
		
	}else{
		
		$('#mimtext').attr('readonly', false );
		$('#tpl_params_div').hide();
		$('#tpl_params_here').html('');
	
	}
	
}

function ShowHideMIMTemplate( cmd ){
	
	if( cmd == "show" ){
		$('#mim_template_li, #global_mim_template_li').show();
		$('#msg_template_li, #global_msg_template_li').hide();

		$( "#mim_template_li > button" ).click();
	}else{
		$('#mim_template_li, #global_mim_template_li').hide();
		$('#msg_template_li, #global_msg_template_li').show();
		$( "#msg_template_li > button" ).click();
	}
	
}

function loadlist(selobj,url,val,name){
	
	$('#selobj option:gt(0)').remove();
	$.getJSON(url,function(data)
	{
		//console.log(data);
		$.each(data, function(index, value) {
			$(selobj).append("<option value=" + value[val] + ">" + value[name] + "</option>");
		});
	});
	
}

function ShowMIMBotDiv( sendmode_is ){

	if( sendmode_is == "sms_mim" || sendmode_is == "mim" ){
		$('#bot_id').prop('required',true);
		$('#mim_bot_div, #mim_image1_div, #mim_file_div').show();
		
		if( sendmode_is == "mim" ){
			$('#callerid').prop('required',false);
			$('#callerid_div').hide();
		}else{
			$('#callerid_div').show();
			$('#callerid').prop('required',true);
		}
		
	}else{

		$('#bot_id').val('');
		$('#mim_image1').val('');

		$('#bot_id').prop('required',false);
		$('#mim_bot_div, #mim_image1_div, #mim_file_div').hide();
		
		$('#callerid_div').show();
		$('#callerid').prop('required',true);
	}
	
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
    $('#charset').val(character_set);
}

loadlist('#callerid','broadcast_sms_lib.php?mode=select_callerid','callerid','label');
loadlist('#campaign_id','campaign_lib.php?mode=select','campaign_id','campaign_name');
loadlist('#bot_id','broadcast_sms_status_lib.php?mode=select_bot','bot_id','bot_name');
ShowTplParamDiv();
ShowMIMBotDiv( '' );

