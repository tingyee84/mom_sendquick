$("#from").val(moment().format('DD/MM/YYYY'));
$("#to").val(moment().format('DD/MM/YYYY'));
$('#from, #to').datepicker({format: 'dd/mm/yyyy'});
var table = $('#access').DataTable({
    deferRender: true,
    responsive: true,
    stateSave: true,
    ajax:{type: 'POST',
        url: 'access_log_lib.php',
        data: function () { return $('#accessForm').serialize(); }
    },
    columns: [
        { "data": "login_dtm_new" },
        { "data": "userid" },
        { "data": "remote_ip" },
        { "data": "user_agent" }
    ]
});
$('#from, #to').on('changeDate', function() {
    $('#from, #to').datepicker('hide');
    table.ajax.reload();
});