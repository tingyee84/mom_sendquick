<script nonce="<?php echo session_id();?>">
$.post('mtc_rpt_lib.php',{mode:'view'},function(val) {
	$('#keep_chat').val(val.keep_chat);
	$('#schedule_opt').val(val.type);
	$('#schedule_tm').val(val.time);
	$('#email_report').val(val.email);
},"json")
.fail(function() {
	alert('Failed To Retrieve Maintenance & Report');
});
$('#logform').on('submit', function(e)
{
	$('#updated').addClass('hidden');
	$.post('mtc_rpt_lib.php',$("#logform").serialize(),function(data) {
		if(data!='1') {
			alert(data);
			$('#keep_chat').focus();
		} else {
			$('#updated').removeClass('hidden');
			window.scrollTo(0,0);
		}
	});
	e.preventDefault();
});
$('form#usageform').submit(function(){
	$('#updated').addClass('hidden');
    var formData = new FormData($(this)[0]);
    $.ajax({
        url: 'mtc_rpt_lib.php',
        type: 'POST',
        data: formData,
        async: false,
        contentType: false,
        processData: false,
        dataType:'json',
        success: function(data){
			$('#updated').removeClass('hidden');
			window.scrollTo(0,0);
		},
		error: function(xhr, ajaxOptions, thrownError){
			alert(xhr.responseText);
		}
    });
    return false;
});
$('.close').click(function() {
	location.reload(true);
});
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	$('#updated').addClass('hidden');
	localStorage.setItem('lastTab', $(this).attr('href'));
});
var lastTab = localStorage.getItem('lastTab');
if (lastTab) {
	$('[href="'+lastTab+'"]').tab('show');
}
</script>
