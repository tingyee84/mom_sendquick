<script language="javascript" src="js/txvalidator.js"></script>
<script language="javascript" src="js/txcommon.js"></script>
<script nonce="<?php echo session_id();?>" src="js/bootstrap-datepicker.min.js"></script>
<script nonce="<?php echo session_id();?>">

$("#campaign_start_date, #campaign_end_date").datepicker( {format: 'dd-mm-yyyy',todayHighlight:'TRUE'} ).on('show.bs.modal', function(event) {
    // prevent datepicker from firing bootstrap modal "show.bs.modal"
    event.stopPropagation();
});


//$('[data-toggle="tooltip"]').tooltip();

var table = $('#campaign').DataTable({
	deferRender: false,
	stateSave: true,
	ajax: 'campaign_lib.php?mode=list',
	columnDefs: [
		{ "orderable": false, "targets": 6 },
		{ "width": "15%", "targets": 0 },
		{ "width": "10%", "targets": 1 },
		{ "width": "15%", "targets": 2 },
		{ "width": "15%", "targets": 3 },
		{ "width": "10%", "targets": 4 },
		{ "width": "10%", "targets": 5 },
		{ "width": "10%", "targets": 6 }
	],
});

// assmi
$("#all").change(function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#campaign").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});

$("#campaign").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#campaign').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});

// assmi

$('#myModal').on('show.bs.modal', function(e){
	$("#myModal_header").show();	
	var modal = $(this), id = $(e.relatedTarget).data('id');
	KeywordList(id);
	
	if(typeof id === "undefined") {
		
		modal.find('#header').html('<?php echo $x->new_campaign; ?>');
		modal.find('#mode').val('add');
		
		modal.find('#id').val("");
		modal.find('#campaign_name').val("");
		modal.find("input[name=campaign_type][value='1']").prop("checked",true);
		modal.find('#campaign_status').val("active");
		modal.find('#campaign_start_date').val("");
		modal.find('#campaign_end_date').val("");
		modal.find('#campaign_name').attr('disabled', false);
		modal.find('#save_btn_id').attr( 'disabled', false );
		
		show_start_end_date( '1' );
	
	} else {

		modal.find('#header').html('<?php echo $x->edit_campaign; ?>');
		modal.find('#mode').val('save');
		
		$.ajax({
			
			cache: false,
			url: 'campaign_lib.php',
			data:'mode=edit&id='+id,
			type:'POST',
			dataType:'json',
			success: function(val){
				
				modal.find('#save_btn_id').attr( 'disabled', false );
				modal.find('#id').val(id);
				modal.find('#campaign_name').val(val.campaign_name);
				modal.find("input[name=campaign_type][value='"+val.campaign_type+"']").prop("checked",true);
				modal.find('#campaign_status').val( val.campaign_status );
				modal.find('#campaign_name').attr('disabled', true);
				
				show_start_end_date( val.campaign_type );
				
				modal.find('#campaign_start_date').val( val.campaign_start_date );
				modal.find('#campaign_end_date').val( val.campaign_end_date );
				
				var keywords = val.keywords;
				var keywords_list = keywords.split(",");
				
				for (i=0;i < keywords_list.length;i++){
					
					modal.find("input[type=checkbox][value='"+keywords_list[i]+"']").prop("checked",true);
					
				}
				
				//alert(val.survey_sent);
				if( val.survey_sent == 'yes' ){
					modal.find('#save_btn_id').attr( 'disabled', true );
				}else{
					modal.find('#save_btn_id').attr( 'disabled', false );
				}
				
			}
			
		})
		
	}
});

$('#myModal').on('submit', function(e){

	if(!txvalidator($("#campaign_name").val(),"TX_STRING","-_")){
	   	$("#campaign_name").addClass("is-invalid");	  	
	}	
	else if(!validateSize($("#campaign_name").val(),"NAME")){
		$("#campaign_name").addClass("is-invalid");
	}
	else{
		$.ajax({
			cache: false,
			url: 'campaign_lib.php',
			data: $("#campaign_form").serialize(),
			type: 'POST',
			dataType:'json',
			success: function(res){
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
				}else{
					table.ajax.reload();
					$('#myModal').modal('hide');
				}
			}
		});
	}		
	e.preventDefault();
});

$("#all").change(function () {
	var cells = table.cells().nodes();
    $(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
});


$('#myModal').on('hidden.bs.modal', function (e) {
	$('#campaign_name').removeClass("is-invalid");	
	$("#msgstatusbar").removeClass("alert-success");
	$("#msgstatusbar").removeClass("alert-warning");
	$("#msgstatusbar").hide();
	$(this).find('form').trigger('reset');
});

$('#delete').on('click', function(e)
{
    if(confirm('<?php echo $x->confirm_delete; ?>')) {
		$('input[type=checkbox]').each(function() {
			if (this.checked && this.value!='on') {
	
				$.post('campaign_lib.php?mode=delete', { id: this.value }, function(data) {
					table.ajax.reload();
				});
				
			}
		});
		$('#all').prop('checked',false);
	}
});

$("#campaign_type_1").click(function () {
	
	$("#start_date_div, #end_date_div, #keyword_div").hide();
	
});

$("#campaign_type_2").click(function () {

	$("#start_date_div, #end_date_div, #keyword_div").show();
	
});

$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});

$('#campaign_name').on('change keyup', function(e){
	$('#campaign_name').removeClass("is-invalid");
});


function show_start_end_date( campaign_type ){
	
	if( campaign_type == "1" ){
		$("#start_date_div, #end_date_div, #keyword_div").hide();
	}else if( campaign_type == "2" ){
		$("#start_date_div, #end_date_div, #keyword_div").show();
	}else{
		$("#start_date_div, #end_date_div, #keyword_div").hide();
	}
	
}

function KeywordList( campaign_id ){
	
	$.ajax({
			
		cache: false,
		url: 'campaign_lib.php',
		data:'mode=KeywordList&id='+campaign_id,
		type:'POST',
		dataType:'text',
		success: function(val){
			
			//alert(val);
			$("#keyword_div_p").html( val );
			
		}
		
	})
		
}
</script>
