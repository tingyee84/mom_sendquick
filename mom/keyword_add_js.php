<script language="javascript" src="js/txvalidator.js"></script>
<script language="javascript" src="js/txcommon.js"></script>
<script nonce="<?php echo session_id();?>">

$('form#add_keyword').submit(function(){	
	if(!txvalidator($("#keyword").val(),"TX_STRING","-")){
		$('#keyword').addClass("is-invalid");		
	}	
	else if(!validateSize($("#description").val(),"DESC")){
		$('#description').addClass("is-invalid");
	}
	else{
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
			url: 'keyword_lib.php',
			type: 'POST',
			dataType:'json',
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			success: function(res){
				$('#progress').hide();
				if(res.flag == 0){	
					$("#msgstatusbar").removeClass("alert-success");
					$("#msgstatusbar").addClass("alert-warning");
       				$("#msgstatustext").html(res.status);
					$("#msgstatusbar").show();
					$('#'+res.field).focus();
				}else if(res.flag == 2){
					$("#msgstatusbar").removeClass("alert-success");
					$("#msgstatusbar").addClass("alert-warning");
       				$("#msgstatustext").html(res.status);
					$("#msgstatusbar").show();												
				}
				else{
					//alert('<?php echo $x->success_msg; ?>');
					$("#msgstatusbar").removeClass("alert-warning");
					$("#msgstatusbar").addClass("alert-success");
       				$("#msgstatustext").html('<?php echo $x->success_msg; ?>');
					$("#msgstatusbar").show();					
					$(location).attr('href','keyword_management.php');
				}
			}
		});
	}
	
	return false;
});

$('#keyword').on('change keyup', function(e){
	if(!txvalidator($("#keyword").val(),"TX_STRING","-")){
		$('#keyword').removeClass("is-valid");
		$('#keyword').addClass("is-invalid");		
	}else{
		$('#keyword').removeClass("is-invalid");
		$('#keyword').addClass("is-valid");
	}
});

$('#description').on('change keyup', function(e){
	$('#description').removeClass("is-invalid");
	$('#description').removeClass("is-valid");
});

$('#url').on('change keyup', function(e){
	$('#url').removeClass("is-invalid");
	$('#url').removeClass("is-valid");
});


$('form#add_api_keyword').submit(function(){
	var formData = new FormData($(this)[0]);
	if(!txvalidator($("#keyword").val(),"TX_STRING","-")){
		$('#keyword').addClass("is-invalid");
	}
	else if(!validateSize($("#description").val(),"DESC")){
		$('#description').addClass("is-invalid");
	}
	else if(!txvalidator($("#url").val(),"TX_URL")){
		$('#url').addClass("is-invalid");
	}
	else{
		$.ajax({
			// xhr: function() {
			// 	var xhr = $.ajaxSettings.xhr();
			// 	if(xhr.upload){
			// 		xhr.upload.onprogress = function(e) {
			// 			if (e.lengthComputable) {
			// 				var percent = Math.floor(e.loaded / e.total *100);
			// 				$('#progress').show();
			// 				$('#percent').html(percent+'%');
			// 				$('#bar').attr('aria-valuenow',percent+'%').css('width',percent+'%');
			// 			}
			// 		};
			// 	}
			// 	return xhr;
			// },
			url: 'keyword_lib.php',
			type: 'POST',
			dataType:'json',
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			success: function(res){
				// $('#progress').hide();
				
				if(res.flag == 0){
					//alert(res.status);
					$("#msgstatusbar").removeClass("alert-success");
					$("#msgstatusbar").addClass("alert-warning");
       				$("#msgstatustext").html(res.status);
					$("#msgstatusbar").show();
					$('#'+res.field).focus();
				}else if(res.flag == 2){
					$("#msgstatusbar").removeClass("alert-success");
					$("#msgstatusbar").addClass("alert-warning");
       				$("#msgstatustext").html(res.status);
					$("#msgstatusbar").show();												
				} else{
					//alert('<?php echo $x->success_msg; ?>');
					$("#msgstatusbar").removeClass("alert-warning");
					$("#msgstatusbar").addClass("alert-success");
       				$("#msgstatustext").html('<?php echo $x->success_msg; ?>');
					$("#msgstatusbar").show();
					$(location).attr('href','keyword_management.php');
				}
			}
		});
	}	
	return false;
});

$('#cancel').on('click', function(e){
	$(location).attr('href','keyword_management.php');
	e.preventDefault();
});

$('#message').on('change keyup', function(e){
	var length = $('#message').val().length;
	$('#textcount').val(160-length);
	if (length > 160) {
		//alert('Sorry, you are over the limit of 160 characters');
		$('#message').addClass("is-invalid");
		var substr = $('#message').val().substring(0,160);
		$('#message').val(substr);
		var length2 = substr.length;
		$('#textcount').val(160-length2);
		this.focus();
	}else{
		$('#message').removeClass("is-invalid");
	}
});

$('#brochure').click(function(){
    if($(this).is(":checked")) {
       $('#email_file').prop('required',true);
    } else {
		$('#email_file').prop('required',false);
	}
});

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});

function loadlist(selobj,url,val,name)
{
	$('#selobj option:gt(0)').remove();
	$.getJSON(url, function(data)
	{
		$.each(data, function(index, value) {
			if(value[name].length!=0) {
				// $(selobj).append('<option value="' + value[val] + '">' + value[name] + '</option>');
				$(selobj).append('<option value="' + value[val] + '">' + value[val] + '</option>');
			}
		});
	});
}

// load application names
loadlist('#api_name','api_list_lib.php?mode=listApplications','serviceid','name');
</script>
