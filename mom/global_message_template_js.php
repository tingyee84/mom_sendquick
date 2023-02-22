<?php
header("Content-type:text/javascript");
require_once('lib/commonFunc.php');
$x = GetLanguage("global_message_template",$lang);
$xml_common = GetLanguage("common",$lang);
?>
var table = $('#tbl_tmpl').DataTable({
	autoWidth: false,
	processing: true,
	stateSave: true,
	ajax: {type:'POST',url:'message_template_lib.php',data:{mode:'listGlobalTemplate'}},
	columnDefs: [
		{'orderable':false,'targets':3},
		{'width':"30%",'targets':0},
		{'width':"55%",'targets':1},
		{'width':"10%",'targets':2},
		{'width':"5%",'targets':3},
	]
});
var date = $.now();
new $.fn.dataTable.Buttons( table, {
	// buttons: [
	// 	{extend:'csv',text:'<?php echo $xml_common->export.' CSV';?>',filename:'<?php echo $_SESSION['userid'];?>_GlobalTemplateMessage_'+date},
	// 	{extend:'excel',text:'<?php echo $xml_common->export.' Excel';?>',filename:'<?php echo $_SESSION['userid'];?>_GlobalTemplateMessage_'+date}
	// ]
	buttons: [
		{
			extend:'csv',
			text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export.' CSV'; ?>',
			filename:'<?php echo $_SESSION['userid'];?>_GlobalTemplateMessage_'+date,
			init: function(api,node,config){
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			}
		},
		{
			extend:'excel',
			text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export.' Excel'; ?>',
			filename:'<?php echo $_SESSION['userid'];?>_GlobalTemplateMessage_'+date,
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-success btn-sm");
			}
		}
	]
} );
table.buttons().container().appendTo('#export');
$('#all').change(function () {
	var cells = table.cells().nodes();
    $(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
});
$('#myCreate').on('show.bs.modal', function(e)
{
	$("#myCreate_header").show();
	var modal = $(this), id = $(e.relatedTarget).data('id');
	if(typeof id === "undefined") {
		modal.find('#header').html('<?php echo $x->create_new;?>');
		modal.find('#mode').val('addGlobalTemplate');
	} else {
		modal.find('#header').html('<?php echo $x->edit_template;?>');
		modal.find('#mode').val('saveGlobalTemplate');
		modal.find('#id').val(id);
		$.post('message_template_lib.php',{mode:'editGlobalTemplate',id:id},function(val) {
			modal.find('#template').val(val.text);
			modal.find('#template_name').val(val.template_name);
			modal.find('#charset').html(val.charset);
			modal.find('#count_chars2').html(val.count_chars2);
			modal.find('#sms_num').html(val.sms_num);
		},"json");
	}
});
$('#myCreate').on('submit', function(e)
{
	if(!txvalidator($("#template_name").val(),"TX_STRING","SPACE")){
	   	$("#template_name").addClass("is-invalid");
	}else{

		$.post('message_template_lib.php',$('#template_form').serialize(),function(res) {
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
			}else{
				table.ajax.reload();
				$('#myCreate').modal('hide');
			}
		},'json');
	}
	e.preventDefault();
});
$('#myCreate').on('hidden.bs.modal', function () {
	$(this).find('form').trigger('reset');
	$("#msgstatusbar").removeClass("alert-success alert-warning");
	$("#msgstatusbar").hide();
	$('#template_name').removeClass("is-invalid");
});
$('#delete').on('click', function(e)
{
	if(confirm('<?php echo $x->alert_3; ?>')) {
		$('input[type=checkbox]').each(function() {     
			if (this.checked && this.value!='on') {
				$.post('message_template_lib.php',{mode:'deleteGlobalTemplate',id:this.value},function() {
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e)
{
	if(confirm('<?php echo $x->alert_4; ?>')) {
		$.post('message_template_lib.php',{mode:'emptyGlobalTemplate'},function() {
			table.ajax.reload();
		});
	}
});
$('#myUpload').submit(function(){
	var formData = new FormData($('#upload_form')[0]);
    $.ajax({
        url: 'message_template_lib.php',
        type: 'POST',
        data: formData,
        async: false,
        contentType: false,
        processData: false,
		dataType: 'json',
        success: function(res){
			if(res.flag != 1) {
				alert(res.status);
			} 
			else {
				$('#myUpload').modal('hide');
				$('#prevUpload').modal('show');
			}
		}
  	});
    return false;
});
$('#myUpload').on('hidden.bs.modal', function(e){
	$(this).find('form').trigger('reset');
});
$('#prevUpload').on('show.bs.modal', function(e)
{
	$('#upload_table').DataTable({
		deferRender: true,
		processing: true,
		autoWidth: false,
		ajax: {type:'POST',url:'message_template_lib.php',data:{mode:'listTemplate',access_type:'1'}}
	});
});
$('#prevUpload').on('hidden.bs.modal', function(){
	$('#upload_table').dataTable().fnDestroy();
});
$('#preCancel').on('click', function(e){
	if(confirm('<?php echo $x->alert_6; ?>')) {
		$.post('message_template_lib.php',{mode:'deleteTemplate',access_type:'1'},function() {
			$('#prevUpload').modal('hide');
		});
	}
});
$('#prevUpload').on('submit', function(e)
{
	$.post('message_template_lib.php',$('#upload_view').serialize(),function(data) {
		var dataFlag = data.flag; 
		console.log("dataFlag: " + dataFlag);
		if(data.flag != 1) {
			console.log("1");
			alert(data.status);
		} else {
			console.log("2");
			table.ajax.reload();
			$('#prevUpload').modal('hide');
		}
		console.log("3");
	},"json");
	e.preventDefault();
});

$('#template').on('keyup change',function() {
		updateCounter();
});

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});

$('#template_name').on('change keyup', function(e){
	$('#template_name').removeClass("is-invalid");
});

function updateCounter() {
		var message_text = $('#template').val();
		checkUnicodeChar(message_text);
		var code_type = $('#charset').val();
		var long_sms = '<?php echo $_SESSION['long_sms']; ?>';
		var max_sms = '<?php echo $_SESSION['max_sms']; ?>';		
		var text_length = message_text.replace(/\r(?!\n)|\n(?!\r)/g, "\r\n").length;	
		var lines = message_text.split(/(?:\r\n|\r|\n)/g);		
		var total_lines = lines.length;		
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
			var new_msg = "";
			var tmp_total_len = total_length;
			// Create message with new line
			for (let i = 0; i < lines.length; i++) {
				var line = lines[i];
				var line_len = lines[i].length;
				if(i == 0){
					new_msg += line;
				}				
				else{
					if(line.length > 0){
						new_msg += "\r\n"+line;
						line_len = line_len+2;
					}					
				}					
			}
			
			new_msg = new_msg.substr(0, tmp_total_len);
			tmp_total_len = tmp_total_len-line_len;		
			$("#template").val(new_msg);
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
