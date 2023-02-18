<?php
header("Content-type:text/javascript");
require_once('lib/commonFunc.php');
$x = GetLanguage("scheduled_sms",$lang);

?>
$.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');
var table =  $('#schedule').DataTable({
    autoWidth: false,
    deferRender: true,
    processing: true,
    stateSave: true,
    ajax: {type:'POST',url:'scheduled_sms_lib.php',data:{mode:'listScheduled'}},
    columnDefs: [{ "orderable":false,"targets":5},
                    { "width":"50%","targets":1}]
});
$('#all').change(function () {
    var cells = table.cells().nodes();
    $(cells).find(':checkbox').prop('checked',$(this).is(':checked'));
});
$('#delete').on('click', function(e) {
    if(confirm('<?php echo $x->alert_2; ?>')) {
        $('input[type=checkbox]').each(function() {     
            if (this.checked && this.value!='on') {
                $.post('scheduled_sms_lib.php',{mode:'deleteScheduled',idx:this.value},function(data) {
                    
                    //alert(data);
                    //return false;
                    table.ajax.reload();
                });
            }
        });
        $('#all').prop('checked',false);
    }
});
$('#truncate').on('click', function(e) {
    if(confirm('<?php echo $x->alert_3; ?>')) {
        $.post('scheduled_sms_lib.php',{mode:'emptyScheduled'},function(data) {
            table.ajax.reload();
        });
    }
});