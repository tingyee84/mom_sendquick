<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/datetime-moment.js"></script>
<script nonce="<?php echo session_id();?>">
$("#from").val(moment().subtract(1,'week').format('DD/MM/YYYY'));
$("#to").val(moment().format('DD/MM/YYYY'));
$('#from, #to').datepicker({
  format: 'dd/mm/yyyy',
  autoclose: true
});
$.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');


var table = $('#bclog').DataTable({
  deferRender: true,
  responsive: true,
  pageLength: 100,
  ajax:{type: 'POST',
    url: 'broadcast_log_lib.php',
    data: function () { return $('#BCLogForm').serialize(); }
  },
  columnDefs: [{ "orderable": false, "targets": 3 }]
});

$('#from, #to').on('changeDate', function() {
  $('#from, #to').datepicker('hide');
  table.ajax.reload();//table.draw();
});
</script>
