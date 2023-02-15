<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.html5.min.js"></script>
<script language="javascript" src="js/txvalidator.js"></script>
<script language="javascript" src="js/txcommon.js"></script>
<script nonce="<?php echo session_id();?>">
var ctable = $('#contactlist').DataTable({
	deferRender: false,
	autoWidth: false,
	responsive: true,
	ajax: 'address_group_lib.php?mode=loadGlobalContactJSON',
	columnDefs: [
		{"orderable": false, "targets":3},
		{"searchable": false, "targets":[0,3]}
	]

});

var table;
listTable();

function listTable()
{
	table =  $('#tblgroup').DataTable({
		deferRender: false,
		responsive: true,
		ajax: 'address_group_lib.php?mode=listGlobalGroup',
		"columns":[
			{ "data": null },//Arrow
            { "data": function(row){//Name
					return row[0];
				}
			},
            { "data": function(row){//dept
					return row[1];
				}
			},
			{ "data": function(row){//location
					return row[2];
				}
			},
			{ "data": function(row){//checkbox
					return row[3];
				}, "width":"10px"
			}
        ],
		columnDefs: [
			{
				"targets":[0],//Arrow
				"render": function(a, b, data, d){
                     return '<span><a href="#" class="open_grpmember" id="open_'+data[4]+'"><span class="fa fa-chevron-right"></span></a></span>';
				}
			},
			{ "orderable": false, "targets": [0,3,4],"searchable":false }
		]
	
	});
	
}

$(document).on('click','.open_grpmember',function(e)
{
	//console.log("open grpmember...");
	var tr = $(this).closest('tr');
	var row = table.row( tr );
	
	var $row = $(this).closest('tr');			 
	var data = table.row($row).data(); 
	
	var gidx = data[4];
	//console.log("gidx:"+gidx);
	
	var html_table='<div class="table-responsive"><table class="table" cellspacing="0" style="table-layout:fixed;background-color:#eee;" width="99.5%" id="contact_table_'+gidx+'">'+
		'<thead>'+
			'<tr style="color: darkgray">'+
				'<th width="10%">No <i class="fa fa-sort" aria-hidden="true"></i></th>'+
				'<th width="20%">Contact Name <i class="fa fa-sort" aria-hidden="true"></i></th>'+
				'<th width="15%">Mobile <i class="fa fa-sort" aria-hidden="true"></i></th>'+
				'<th width="20%">Email<i class="fa fa-sort" aria-hidden="true"></i></th>'+
				'<th width="15%">Added Date/Time<i class="fa fa-sort" aria-hidden="true"></i></th>'+
				'<th width="10%">Added By<i class="fa fa-sort" aria-hidden="true"></i></th>'+
			'</tr>'+	
		'</thead>'+
		
	'</table></div>';


	//console.log(row);
	if ( row.child.isShown() ) {
		// This row is already open - close it
		row.child.hide();
		tr.removeClass('shown');
		$('#open_'+gidx).html('<span class="fa fa-chevron-right"></span>');
	}
	else {
		// Open this row
		row.child( html_table ).show();
		loadGroupMemberList(row.data());
		tr.addClass('shown');
		$('#open_'+gidx).html('<span class="fa fa-chevron-down"></span>');
	}
		
	e.stopPropagation();
	e.preventDefault();
	
});
$(document).on('click','.grpsync',function(e){
	//console.log("clicked grpsync...");
	var grpid = $(this).attr('id');
	//console.log("grpid:"+grpid);
	$('#'+grpid).html('<i class="fa fa-spinner fa-spin" style="font-size: 1.5rem; cursor: pointer;"></i>');
	var sendobj = $.param({ 'mode':'syncGroupMember',
							'grpid':grpid
							});
	$.ajax({
		cache: false,
		url: 'ldap_lib.php',
		data: sendobj,
		type: 'POST',
		dataType:'json',
		success: function(data){
			//console.log(data);
			if(data[0] == 1){
				
				$('#d-msg').html(data.err);
				$('#d-msg').addClass('alert alert-danger');
			} else {
				
				$('#d-msg').html(data.msg);
				$('#d-msg').addClass('alert alert-info');
			}
			
			$('#'+grpid).html('<i class="fa fa-refresh text-success" style="font-size: 1.5rem; cursor: pointer;"></i>');
			
		}
	});
	
	
	e.stopPropagation();
	e.preventDefault();
});
var date = $.now();
new $.fn.dataTable.Buttons( table, {
	// buttons: [
	// 	{extend:'csv', text: '<?php echo $xml_common->export.' CSV'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php echo $_SESSION['userid']; ?>_GlobalAddressgroup_'+date, header: false},
	// 	{extend:'excel', text: '<?php echo $xml_common->export.' Excel'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php echo $_SESSION['userid']; ?>_GlobalAddressgroup_'+date}
	// ]
	buttons: [
		{
			extend:'csv', 
			text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export.' CSV'; ?>', 
			exportOptions: {columns: ':visible'}, 
			filename:'<?php echo $_SESSION['userid']; ?>_GlobalAddressgroup_'+date, 
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
			filename:'<?php echo $_SESSION['userid']; ?>_GlobalAddressgroup_'+date,
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
$('#myGroup').on('submit', function(e){
	
	if(!txvalidator($("#group").val(),"TX_STRING")){
	  	$("#group").addClass("is-invalid");
	}
	else if(!validateSize($("#group").val(),"NAME")){
		$("#group").addClass("is-invalid");
	}
	else{

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


var checkedcontact = [];
$('#myGroup').on('hidden.bs.modal', function () {
	$(this).find('form').trigger('reset');
	checkedcontact = [];
	$("#msgstatusbar").hide();
    $("#msgstatusbar").removeClass("alert-success alert-warning");
	$("#group").removeClass("is-invalid");
});
$('#myGroup').on('show.bs.modal', function () {
	$("#myGroup_header").show();
	ctable.ajax.reload();
});
$('#delete').on('click', function(e)
{
	if(confirm('<?php echo $x->alert_3; ?>')) {
		$('input[type=checkbox]').each(function() {
			if (this.checked && this.value!='on') {
				$.post('address_group_lib.php?mode=deleteGlobalGroup', { id: this.value }, function(data) {
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
		$.post('address_group_lib.php?mode=emptyGlobalGroup');
		table.ajax.reload();
	}
});

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});

function loadGroupMemberList(d){
	
	var gidx = d[4];
	var gname = d[0];
	
	var contactTable = $('#contact_table_'+gidx).DataTable({
		deferRender: false,
		stateSave: true,
		ajax: 'address_group_lib.php?mode=loadGroupMember&gidx='+gidx,
		columnDefs: [{ "orderable": false, "targets": 4 },
					{ "searchable": false, "targets": 4 }]
	});
	
}
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
function grpsync(grpid)
{
	console.log("grpid:"+grpid);
}



</script>
