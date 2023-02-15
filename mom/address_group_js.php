<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.html5.min.js"></script>
<script language="javascript" src="js/txvalidator.js"></script>
<script nonce="<?php echo session_id();?>">
var table = $('#tblgroup').DataTable({
	deferRender: false,
	responsive: true,
	ajax: 'address_group_lib.php?mode=listAddressGroup',
	columnDefs: [{ "orderable": false, "targets": 1 },
				 { "searchable": false, "targets": 1 }]
});

var ctable = $('#contactlist').DataTable({
	deferRender: false,
	autoWidth: false,
	responsive: true,
	ajax: 'address_group_lib.php?mode=loadContactJSON',
	columnDefs: [
		{"orderable": false, "targets":3},
		{"searchable": false, "targets":[0,3]}
	]

});
// assmi
// $("#all").change(function () {
// 	var cells = table.cells().nodes();
//     $(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
// });

$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#tblgroup").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});

$("#tblgroup").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#tblgroup').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});

// assmi

$("#modal_all").change(function () {
	var cells = ctable.cells().nodes();
    $(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
});

$('#myGroup').on('submit', function(e)
{
	if(!txvalidator($("#group").val(),"TX_STRING")){
	  	$("#group").addClass("is-invalid");
	}else{
		$("input[name='selectedcontactid']").val(
		$("input[name='contact[]']:checked",$('#contactlist').dataTable().fnGetNodes()).map(function() {
			return $(this).val();
			}).get().join(",")
		);
	
		$.ajax({
			cache: false,
			url: 'address_group_lib.php',
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
					$('#myGroup').modal('hide');
				}
			}
		});	
	}
	e.preventDefault();	
});

$('#group').on('change keyup', function(e){
	$('#group').removeClass("is-invalid");
});


var date = $.now();
new $.fn.dataTable.Buttons( table, {
	// buttons: [
	// 	{extend:'csv', text: '<?php echo $xml_common->export.' CSV'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php echo $_SESSION['userid']; ?>_Addressgroup_'+date, header: false},
	// 	{extend:'excel', text: '<?php echo $xml_common->export.' Excel'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php echo $_SESSION['userid']; ?>_Addressgroup_'+date}
	// ]
	buttons: [
		{
			extend:'csv', 
			text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export.' CSV'; ?>', 
			exportOptions: {columns: ':visible'}, 
			filename:'<?php echo $_SESSION['userid']; ?>_Addressgroup_'+date, 
			header: false,
			init: function(api,node,config){
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			},
		},
		{
			extend:'excel', 
			text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export.' Excel'; ?>', 
			exportOptions: {columns: ':visible'}, 
			filename:'<?php echo $_SESSION['userid']; ?>_Addressgroup_'+date,
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-success btn-sm");
			}
		}
	]
} );
table.buttons().container().appendTo('#export');

var checkedcontact = [];
$('#myGroup').on('hidden.bs.modal', function () {
	$(this).find('form').trigger('reset');
	$("input[name='contact[]']").attr("checked",false);
	checkedcontact = [];	
    $("#msgstatusbar").removeClass("alert-success alert-warning");
	$("#msgstatusbar").hide();
	$("#group").removeClass("is-invalid");
});

$('#myGroup').on('show.bs.modal', function() {	
	$("#myGroup_header").show();
	ctable.ajax.reload();
});

$('#delete').on('click', function(e)
{
	if(confirm('<?php echo $x->alert_3; ?>')) {
		$('input[type=checkbox]').each(function() {
			if (this.checked && this.value!='on') {
				$.post('address_group_lib.php?mode=deleteAddressGroup', { id: this.value }, function(data) {
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
		$.post('address_group_lib.php?mode=emptyAddressGroup');
		table.ajax.reload();
	}
});

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});
</script>
