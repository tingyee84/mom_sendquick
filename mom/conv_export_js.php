<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.html5.min.js"></script>
<script nonce="<?php echo session_id();?>">
$("#from_dt").val(moment().format('DD/MM/YYYY'));
$("#to_dt").val(moment().format('DD/MM/YYYY'));
$('#from_dt, #to_dt').datepicker({
  format: 'dd/mm/yyyy'
})
$('#from_dt, #to_dt').on('changeDate', function() {
  $('#from_dt, #to_dt').datepicker('hide');
});
//Export conversation
var table = $('#exporttbl').DataTable({
  deferRender: true,
  responsive: true,
  ajax:{type: 'POST',
    url: 'conv_export_lib.php',
    data: function () { return $('#exportForm').serialize(); }
  },
  columnDefs: [{ "orderable": false, "targets": 8 }]
});

$('#from_dt, #to_dt').on('changeDate', function() {
  $('#from_dt, #to_dt').datepicker('hide');
  table.ajax.reload();//table.draw();
});

var date = $.now();
new $.fn.dataTable.Buttons( table, {
  buttons: [
    {extend:'csv', text: '<?php echo $xml_common->export.' CSV'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php echo $_SESSION['userid']; ?>_Conversation_'+date},
    {extend:'excel', text: '<?php echo $xml_common->export.' Excel'; ?>', exportOptions: {columns: ':visible'}, filename:'<?php echo $_SESSION['userid']; ?>_Conversation_'+date}
  ]
} );
table.buttons().container().appendTo('#export2');
//Export end
</script>
