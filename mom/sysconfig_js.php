$.post('sysconfig_lib.php',{mode:'viewSMSTime'},function(val) {
	$('input[name="webapp_sms"][value="'+val.webapp_sms+'"]').prop("checked", true);
	$('input[name="monday_cb"][value="'+val.monday_value+'"]').prop("checked", true);
	$('#mon_start_hour').val(val.monday_starthr);
	$('#mon_start_min').val(val.monday_startmin);
	$('#mon_end_hour').val(val.monday_endhr);
	$('#mon_end_min').val(val.monday_endmin);
	$('input[name="tuesday_cb"][value="'+val.tues_value+'"]').prop("checked", true);
	$('#tue_start_hour').val(val.tues_starthr);
	$('#tue_start_min').val(val.tues_startmin);
	$('#tue_end_hour').val(val.tues_endhr);
	$('#tue_end_min').val(val.tues_endmin);
	$('input[name="wed_cb"][value="'+val.wed_value+'"]').prop("checked", true);
	$('#wed_start_hour').val(val.wed_starthr);
	$('#wed_start_min').val(val.wed_startmin);
	$('#wed_end_hour').val(val.wed_endhr);
	$('#wed_end_min').val(val.wed_endmin);
	$('input[name="thurs_cb"][value="'+val.thurs_value+'"]').prop("checked", true);
	$('#thu_start_hour').val(val.thurs_starthr);
	$('#thu_start_min').val(val.thurs_startmin);
	$('#thu_end_hour').val(val.thurs_endhr);
	$('#thu_end_min').val(val.thurs_endmin);
	$('input[name="fri_cb"][value="'+val.fri_value+'"]').prop("checked", true);
	$('#fri_start_hour').val(val.fri_starthr);
	$('#fri_start_min').val(val.fri_startmin);
	$('#fri_end_hour').val(val.fri_endhr);
	$('#fri_end_min').val(val.fri_endmin);
	$('input[name="sat_cb"][value="'+val.sat_value+'"]').prop("checked", true);
	$('#sat_start_hour').val(val.sat_starthr);
	$('#sat_start_min').val(val.sat_startmin);
	$('#sat_end_hour').val(val.sat_endhr);
	$('#sat_end_min').val(val.sat_endmin);
	$('input[name="sun_cb"][value="'+val.sun_value+'"]').prop("checked", true);
	$('#sun_start_hour').val(val.sun_starthr);
	$('#sun_start_min').val(val.sun_startmin);
	$('#sun_end_hour').val(val.sun_endhr);
	$('#sun_end_min').val(val.sun_endmin);
},"json");
$('#timeCfg').on('submit',function(e) {
	$('#status').addClass('hidden');
	$.post('sysconfig_lib.php',$("#timeCfg").serialize(),function() {
		$('#status').removeClass('hidden');
	})
	.fail(function() {
		alert('Failed To Update SMS Time Configuration');
	});
	e.preventDefault();
});
$('.close').click(function() {
	location.reload(true);
});

