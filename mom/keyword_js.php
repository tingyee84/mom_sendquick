<script src="js/keyword_js_ext.php" defer></script>
<!-- <script nonce="<?php //echo session_id();?>">
var table = $('#keyword').DataTable({
	deferRender: true,
	ajax:{type:'POST',url:'keyword_lib.php',data:{mode:'listKeyword2'}},
	columnDefs: [
		{ "orderable": false, "targets": 4 },
		{ "orderable": false, "targets": 6 }
	],
});
// assmi
// $('#all').change(function () {
// 	var cells = table.cells().nodes();
//     $(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
// });

$("#all").on("change",function(){
	$('input:checkbox.user_checkbox').prop('checked', $(this).prop("checked"));
});

$("#keyword").on('change',"input[type='checkbox']",function(e){
	if($(this).prop("checked") == false){
		$('#all').prop('checked', false);
	}
});

$("#keyword").on('change',"input[type='checkbox']",function(e){
	if(table.$('input[type="checkbox"]').filter(':checked').length == table.$('input[type="checkbox"]').length  ){
		$('#all').prop('checked', true);
	}

	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0 ){
		$('#all').prop('checked', true);
	}
});

$('#keyword').on( 'draw.dt', function () {
	if($('.user_checkbox:checked').length == $('.user_checkbox').length && $('.user_checkbox').length != 0){
		$('#all').prop('checked', true);
	}else{
		$('#all').prop('checked', false);
	}
});

// assmi

$('#create').on("click",function(){
	window.location = 'keyword_add.php';
});
$('#create_api').on("click",function(){
	window.location = 'keyword_api_add.php';
});
$('#delete').on('click', function(e)
{
	if(confirm('<?php //echo $x->alert_2; ?>')) {
		$('input[type=checkbox]').each(function() {     
			if (this.checked && this.value!='on') {
				$.post('keyword_lib.php',{mode:'deleteKeyword',keyword:this.value}, function(data) {
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#truncate').on('click', function(e)
{
	if(confirm('<?php //echo $x->alert_3; ?>')) {
		$.post('keyword_lib.php',{mode:'emptyKeyword'},function(data) {
			table.ajax.reload();
		});
	}
});
</script> -->
