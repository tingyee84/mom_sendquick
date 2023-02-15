<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.html5.min.js"></script>
<script language="javascript" src="js/txvalidator.js"></script>
<script language="javascript" src="js/txcommon.js"></script>
<script nonce="<?php echo session_id();?>">
$("#example_upload_file").on("click",function () {
	
	window.open('file/addressbook_upload_same.csv');   
	
});

// $('[data-toggle="tooltip"]').tooltip();
var table = $('#address').DataTable({
	//scrollX: false,
	//deferRender: true,
	//stateSave: true,
	responsive: true,
	ajax: 'address_book_lib.php?mode=listAddressBook',
	columnDefs: [
		{ "orderable": false, "targets": [3,4] },
		{ "orderable": false, "targets": 4 },
		{ "width": "20%", "targets": [0,2] },
		{ "width": "140px", "targets": [1] },
		{ "width": "25px", "targets": [4] }
	]
});

// assmi
$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#address").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});

$("#address").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#address').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});


// assmi

var date = $.now();
new $.fn.dataTable.Buttons( table, {
	// buttons: [
	// 	{extend:'csv', text: '<?php echo $xml_common->export.' CSV'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php echo $_SESSION['userid']; ?>_AddressBook_'+date},
	// 	{extend:'excel', text: '<?php echo $xml_common->export.' Excel'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php echo $_SESSION['userid']; ?>_AddressBook_'+date}
	// ]
	buttons: [
		{
			extend:'csv', 
			text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export.' CSV'; ?>',
			exportOptions: {columns: ':visible'}, 
			filename:'<?php echo $_SESSION['userid']; ?>_AddressBook_'+date,
			init: function(api,node,config){
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			},
		},
		{
			extend:'excel', 
			text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export.' Excel'; ?>', 
			exportOptions: {columns: ':visible'}, 
			filename:'<?php echo $_SESSION['userid']; ?>_AddressBook_'+date,
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
	$(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
});
$('#myAdbk').on('show.bs.modal', function(e)
{
	$("#myAdbk_header").show();
	var modal = $(this), id = $(e.relatedTarget).data('id');
	if(typeof id === "undefined") {
		modal.find('#header').html('<?php echo $x->create_new; ?>');
		modal.find('#mode').val('addAddressBook');
		modal.find('#contact').prop('disabled', false);
	} else {
		$.ajax({
			cache: false,
			url: 'address_book_lib.php',
			data:'mode=editAddressBook&id='+id,
			type:'POST',
			dataType:'json',
			success: function(val)
			{
				modal.find('#header').html('<?php echo $x->edit_contact; ?>');
				modal.find('#id').val(id);
				modal.find('#mode').val('saveAddressBook');
				modal.find('#contact').val(val.contact_name);
				modal.find('#contact').prop('disabled', true);
				modal.find('#mobile').val(val.mobile_numb);
				modal.find('#email').val(val.email);
				modal.find('#modem').val(val.modem_label);
				//Edited by Wafie @ 11/11/2016
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
				//Wafie End
			}
		})
	}
});

$('#myAdbk').on('submit', function(e) {
	if(!txvalidator($("#contact").val(),"TX_STRING")){
	  	$("#contact").addClass("is-invalid");
	}
	else if(!validateSize($("#contact").val(),"NAME")){
		$("#contact").addClass("is-invalid");
	}
	else if(!txvalidator($("#mobile").val(),"TX_SGMOBILEPHONE")){
		$("#mobile").addClass("is-invalid");
	}
	else if(!txvalidator($("#email").val(),"TX_EMAILADDR")){
		$("#email").addClass("is-invalid");
	}
	else{
		$("#msgstatusbar").hide();
    	$("#msgstatusbar").removeClass("alert-success alert-warning");
		$.ajax({
			cache: false,
			url: 'address_book_lib.php',
			data: $("#contact_form").serialize(),
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
					$('#myAdbk').modal('hide');
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


$('#myAdbk').on('hidden.bs.modal', function () {
	$(this).find('form').trigger('reset');	
    $("#msgstatusbar").removeClass("alert-success alert-warning");
	$("#msgstatusbar").hide();
	$("#contact").removeClass("is-invalid");
	$("#mobile").removeClass("is-invalid");
	$("#email").removeClass("is-invalid");
});

$('#movegrp').on('hidden.bs.modal', function () {
	$(this).find('form').trigger('reset');
});
$('#delete').on('click', function(e)
{
	if(confirm('<?php echo $x->alert_5; ?>')) {
		$('input[type=checkbox]').each(function() {
			if (this.checked) {
				$.post('address_book_lib.php?mode=deleteAddressBook', { id: this.value }, function(data) {
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e)
{
	if(confirm('<?php echo $x->alert_8; ?>')) {
		$.post('address_book_lib.php?mode=emptyAddressBook', function(data) {
			table.ajax.reload();
		});
	}
});
$('form#upload_form').submit(function(){
    var formData = new FormData($(this)[0]);
    $.ajax({
        url: 'address_book_lib.php',
        type: 'POST',
        data: formData,
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        success: function(res){
			
			//alert(res);
			//return false;
			if(res==0) {
				alert('<?php echo $x->alert_6; ?>');
			} 
			else if(res==2){
				alert('<?php echo $x->alert_7; ?>');
			}else if(res==3){
				alert('<?php echo $x->alert_11; ?>');
			}else if(res==4){
				alert('<?php echo $x->alert_12; ?>');
			}
			else {
				$('#myUpload').modal('hide');
				$('#prevUpload').modal('show');
			}
		}
  	});
    return false;
});
$('#move_group_form').submit(function(){
	alert("hello");
	return false;
});

$('#myUpload').on('show.bs.modal', function(e){
	$(this).find('form').trigger('reset');
});
//Preview Upload : Added by Wafie @ 01/02/2017
$('#prevUpload').on('show.bs.modal', function(e)
{
	$('#upload_table').DataTable({
		deferRender: true,
		responsive: true,
		ajax: 'address_book_lib.php?mode=listContacts&access_type=0',
		columnDefs: [{ "orderable": false, "targets": 4 }]
	});
});
$('#prevUpload').on('hidden.bs.modal', function(){
	$('#upload_table').dataTable().fnDestroy();
});
$('#preCancel').on('click', function(e){
	if(confirm('<?php echo $x->alert_10; ?>')) {
		$.post('address_book_lib.php?mode=deleteContacts', function(data){
			table.ajax.reload();
		});
	}
});
$('#upload_view').on('submit', function(e)
{
	$.ajax({
		cache: false,
		url: 'address_book_lib.php',
		data: $("#upload_view").serialize(),
		type: 'POST',
		success: function(data){
			alert('<?php echo $x->alert_9 ?>');
			table.ajax.reload();
			$('#prevUpload').modal('hide');
		}
	});
	e.preventDefault();
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
function loadcheck(chkobj,url,val,name)
{
	$('#chkobj input[type=checkbox]').remove();<?php // take note this part, not sure has problem or not ?>
	$.getJSON(url,function(data)
	{
		$.each(data, function(index, value) {
			$(chkobj).append('<input type="checkbox" name="group[]" value="'+value['group_id']+'"/> '+value['group_name']+'<br>');
		});
	});
}
loadlist('#modem','lib_modem.php?mode=listModem','modem_label','modem_label');
loadcheck('#grouplist','address_book_lib.php?mode=getAddressGroup','group_id','group_name');
loadcheck('#grouplist2','address_book_lib.php?mode=getAddressGroup');
</script>
