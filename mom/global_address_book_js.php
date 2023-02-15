<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.html5.min.js"></script>
<script src="js/jquery.redirect.js"></script>
<script language="javascript" src="js/txvalidator.js"></script>
<script language="javascript" src="js/txcommon.js"></script>
<script nonce="<?php echo session_id();?>">
// $('[data-toggle="tooltip"]').tooltip();
var table = $('#address').DataTable({
	deferRender: false,
	stateSave: true,
	ajax: 'address_book_lib.php?mode=listGlobalBook',
	columnDefs: [
		{"targets": 5, "width": "10px", "orderable" : false, "searchable" : false }
	]
});
var date = $.now();
new $.fn.dataTable.Buttons( table, {
	// buttons: [
	// 	{extend:'csv', text: '<?php echo $xml_common->export.' CSV'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php echo $_SESSION['userid']; ?>_GlobalAddressBook_'+date, header: false},
	// 	{extend:'excel', text: '<?php echo $xml_common->export.' Excel'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php echo $_SESSION['userid']; ?>_GlobalAddressBook_'+date}
	// ]
	buttons: [
		{
			extend:'csv', 
			text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export.' CSV'; ?>', 
			exportOptions: {columns: ':visible'}, 
			filename:'<?php echo $_SESSION['userid']; ?>_GlobalAddressBook_'+date, 
			header: false,
			init: function(api,node,config){
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			}
		},
		{
			extend:'excel', 
			text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export.' Excel'; ?>', 
			exportOptions: {columns: ':visible'}, 
			filename:'<?php echo $_SESSION['userid']; ?>_GlobalAddressBook_'+date,
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-success btn-sm");
			}
		}
	]
} );
table.buttons().container().appendTo('#export');
$("#all").change(function () {
	var cells = table.cells().nodes();
	$(cells).find(':checkbox:enabled').prop('checked', $(this).is(':checked'));
});
$('#myUpload').submit(function(){
    var formData = new FormData($('#upload_form')[0]);
    $.ajax({
        url: 'address_book_lib.php',
        type: 'POST',
        data: formData,
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        success: function(res){
			if(res==0) {
				alert('<?php echo $x->alert_6; ?>');
			} 
			else if(res==2){
				alert('<?php echo $x->alert_7; ?>');
			}else if(res==3){
				alert('<?php echo $x->alert_10; ?>');
			}else if(res==4){
				alert('<?php echo $x->alert_11; ?>');
			}
			else {
				$('#myUpload').modal('hide');
				$('#prevUpload').modal('show');
			}
		}
    });
    return false;
});
$('#myUpload').on('show.bs.modal', function(e){
	$(this).find('form').trigger('reset');
});
//Preview Upload : Added by Wafie @ 01/02/2017
$('#prevUpload').on('show.bs.modal', function(e)
{
	$('#upload_table').DataTable({
		deferRender: false,
		responsive: true,
		ajax: 'address_book_lib.php?mode=listContacts&access_type=1',
		columnDefs: [{ "orderable": false, "targets": 4 }]
	});
});
$('#prevUpload').on('hidden.bs.modal', function(){
	$('#upload_table').dataTable().fnDestroy();
});
$('#preCancel').on('click', function(e){
	if(confirm('<?php echo $x->alert_9; ?>')) {
		$.post('address_book_lib.php?mode=deleteContacts&access_type=1', function(data){
			table.ajax.reload();
			$('#prevUpload').modal('hide');
		});
	}
});
$('#prevUpload').on('submit', function(e)
{
	$.ajax({
		cache: false,
		url: 'address_book_lib.php',
		data: $("#upload_view").serialize(),
		type: 'POST',
		success: function(data){
			alert('<?php echo $x->alert_8 ?>');
			table.ajax.reload();
			$('#prevUpload').modal('hide');
		}
	});
	e.preventDefault();
});
$('#myGlbk').on('show.bs.modal', function(e)
{
	$("#header").show();
	var modal = $(this), id = $(e.relatedTarget).data('id');
	if(typeof id === "undefined") {
		modal.find('#header').html('<?php echo $x->create_new; ?>');
		modal.find('#mode').val('addGlobalBook');
		modal.find('#contact').prop('disabled', false);
	} else {
		$.ajax({
			cache: false,
			url: 'address_book_lib.php',
			data:'mode=editGlobalBook&id='+id,
			type:'POST',
			dataType:'json',
			success: function(val)
			{
				modal.find('#header').html('<?php echo $x->edit_contact; ?>');//Added by Wafie @ 11/11/2016
				modal.find('#id').val(id);
				modal.find('#mode').val('saveGlobalBook');//Wafie End
				modal.find('#contact').val(val.contact_name);
				modal.find('#contact').prop('disabled', true);
				modal.find('#mobile').val(val.mobile_numb);
				modal.find('#email').val(val.email);
				modal.find('.modem').val(val.modem_label);
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
	}
});

$('#myGlbk').on('submit', function(e)
{
	if(!txvalidator($("#contact").val(),"TX_STRING")){
	  	$("#contact").addClass("is-invalid");
	}
	else if(!txvalidator($("#mobile").val(),"TX_SGMOBILEPHONE")){
		$("#mobile").addClass("is-invalid");
	}
	else if(!txvalidator($("#email").val(),"TX_EMAILADDR")){
		$("#email").addClass("is-invalid");
	}
	else{
		$.ajax({
			cache: false,
			url: 'address_book_lib.php',
			data: $("#group_form").serialize(),
			type: 'POST',
			dataType: 'json',
			success: function(res){
				//alert(res.status);
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
					$('#myGlbk').modal('hide');
				}
			}
		});
	}  
	e.preventDefault();
});

$('#contact').on('change keyup', function(e){
	$('#contact').removeClass("is-invalid");
});

$('#mobile').on('change keyup', function(e){
	$('#mobile').removeClass("is-invalid");
});

$('#email').on('change keyup', function(e){
	$('#email').removeClass("is-invalid");
});

//Clear Modal : Added by Wafie @ 08/12/2016
$('#myGlbk').on('hidden.bs.modal', function () {
	$(this).find('form').trigger('reset');	
    $("#msgstatusbar").removeClass("alert-success alert-warning");
	$("#msgstatusbar").hide();
	$("#contact").removeClass("is-invalid");
	$("#mobile").removeClass("is-invalid");
	$("#email").removeClass("is-invalid");
});
//Clear Modal End
$('#delete').on('click', function(e)
{
	if(confirm('<?php echo $x->alert_4; ?>')) {
		$('input[type=checkbox]').each(function() {
			if (this.checked) {
				$.post('address_book_lib.php?mode=deleteGlobalBook', { id: this.value }, function(data) {
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e)
{
	if(confirm('<?php echo $x->alert_5; ?>')) {
		$.post('address_book_lib.php?mode=emptyGlobalBook', function(data) {
			table.ajax.reload();
		});
	}
});

$.getJSON('address_book_lib.php?mode=getGlobalGroup&department=<?php echo $department ?>',function(data)
{
	var list = '';
	$.each(data, function(index, value) {
		list += '<input type="checkbox" name="group[]" value="'+value['group_id']+'"/> '+value['group_name']+'<br>';
	});
	$('#grouplist').html(list);
	$('#grouplist2').html(list);
});

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});

function loadlist(selobj,url,val,name)
{
	$('#selobj option:gt(0)').remove();
	$.getJSON(url,function(data)
	{
		$.each(data, function(index, value) {
			if(value[name].length!=0) {
				$(selobj).append('<option value="' + value[val] + '">' + value[name] + '</option>');
			}
		});
	});
}
loadlist('.modem','lib_modem.php?mode=listModem','modem_label','modem_label');

//send MIM
$('#send_mim').on('click', function(){
	var inc_id = [];
	$('input[type=checkbox]').each(function() {
		if (this.checked) {
			if(inc_id.length > 0){
				inc_id += ","+$(this).data('inc')+":"+$(this).data('channel')+":"+$(this).data('name');
			}else{
				inc_id += $(this).data('inc')+":"+$(this).data('channel')+":"+$(this).data('name');
			}
		}
	});
	$.redirect('broadcast_mim.php', {'id': inc_id});

});


</script>
