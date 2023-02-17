<script language="javascript" src="js/txvalidator.js"></script>
<script language="javascript" src="js/txcommon.js"></script>
<script src="js/keyword_edit_js_ext.php?keyword=<?php echo $_GET['keyword']; ?>"></script>
<!-- <script nonce="<?php //echo session_id();?>">

loadlist('#api_name','api_list_lib.php?mode=listApplications','serviceid','name');
var serviceId = "";

$.ajax({
	cache: false,
	url: 'keyword_lib.php',
	data:{mode:'readKeyword2',keyword:'<?php //echo $_GET['keyword'];?>'},
	type:'POST',
	dataType:'json',
	success: function(val){
		var userid = '<?php //echo $_SESSION['userid']?>';

		console.log("userid: " + userid);

		if(userid == "useradmin" || userid == "momadmin"){

		}else{
			var department = '<?php //echo $_SESSION['department']?>';

			if( val[0].department != department ){
				window.location = 'keyword_management.php'
			}
		}

		$('#keyword').val(val[0].keyword);
		$('#description').val(val[0].keyword_desc);

		if(val[0].type == "1"){
			serviceId = val[0].serviceid;
			console.log("serviceid: " + val[0].serviceid);
			$('#api_name').val(val[0].serviceid);
			$('#url').val(val[0].url);
		}else{
			if(val[0].autoreply == '1'){
				$("#autoreply").prop('checked', true);
			} else {
				$("#autoreply").prop('checked', false);
			}
			$('#message').val(val[0].autoreply_msg);
		}
	},
	error: function(){
		alert('Failed To Retrieve SMS Keyword');
	}
});
$('form#edit_keyword').submit(function(){	
	if(!validateSize($("#description").val(),"DESC")){
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
				} 
				else if(res.flag == 2){
					$("#msgstatusbar").removeClass("alert-success");
					$("#msgstatusbar").addClass("alert-warning");
					$("#msgstatustext").html(res.status);
					$("#msgstatusbar").show();	
				}	
				else{
					//alert('<?php //echo $x->success_msg; ?>');
					$("#msgstatusbar").removeClass("alert-warning");
					$("#msgstatusbar").addClass("alert-success");
					$("#msgstatustext").html('<?php //echo $x->success_msg; ?>');
					$("#msgstatusbar").show();
					$(location).attr('href','keyword_management.php');
				}
			}
		});
	}
	
	return false;
});

$('#description').on('change keyup', function(e){
	$('#description').removeClass("is-invalid");
	$('#description').removeClass("is-valid");
});

$('#url').on('change keyup', function(e){
	$('#url').removeClass("is-invalid");
	$('#url').removeClass("is-valid");
});

$('form#edit_api_keyword').submit(function(){
	if(!validateSize($("#description").val(),"DESC")){
		$('#description').addClass("is-invalid");		
	}
	else if(!txvalidator($("#url").val(),"TX_URL")){
		$('#url').addClass("is-invalid");
	}
	else{
		var formData = new FormData($(this)[0]);
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
				} 
				else if(res.flag == 2){
					$("#msgstatusbar").removeClass("alert-success");
					$("#msgstatusbar").addClass("alert-warning");
					$("#msgstatustext").html(res.status);
					$("#msgstatusbar").show();	
				}
				else{
					//alert('<?php //echo $x->success_msg; ?>');
					$("#msgstatusbar").removeClass("alert-warning");
					$("#msgstatusbar").addClass("alert-success");
					$("#msgstatustext").html('<?php //echo $x->success_msg; ?>');
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
$('#sender').change(function() {
	if (!this.checked) {
		$(this).after('<input type="hidden" name="' + $(this).attr("name") + '" value=off>')
	} else {
		$('input[value=off]').remove();
	}
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
$('#remove_current_email_file').click(function(){
    if($(this).is(":checked")) {
		$('#email_file').prop('required',true);
    } else {
		$('#email_file').prop('required',false);
	}
});
$('#brochure').click(function(){
    if($(this).is(":checked")){
		if($('#current_email_file').val().length > 0) {
			$('#email_file').prop('required',false);
		} else {
			$('#email_file').prop('required',true);
		}
	} else {
		$('#email_file').prop('required',false);
	}
});

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});

function loadlist(selobj, url, val, name)
{
	$('#selobj option:gt(0)').remove();
	$.getJSON(url, function(data)
	{
		$.each(data, function(index, value) {
			console.log("value: " + value[val]);
			if(value[name].length!=0) {
				if(value[val] == serviceId){
					$(selobj).append('<option value="' + value[val] + '" selected>' + value[val] + '</option>');
				}else{
					$(selobj).append('<option value="' + value[val] + '">' + value[val] + '</option>');
				}
				// $(selobj).append('<option value="' + value[val] + '">' + value[name] + '</option>');
			}
		});
	});
}

// load application names

</script> -->
