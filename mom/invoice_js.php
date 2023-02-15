<script src="js/moment.min.js" type="text/javascript"></script>
<script nonce="<?php echo session_id();?>">

<?php
if(isUserAdmin($_SESSION["userid"])) { ?>
function getMonth(dept) {
    $.ajax({
        cache: false,
        url: 'invoice_lib.php',
        data:'mode=getMonth&dept='+dept,
        type:'GET',
        success:function(data) {
            $("#tbl_invoice tbody").html(data);
        }
    });
}
$.ajax({
    cache: false,
	url: 'invoice_lib.php',
	data:'mode=getDept',
	type:'GET',
	success:function(data) {
        $("#deptname").append(data);
        $("#deptname").change(function(e){
            getMonth($(this).val());
        }).change();

	}
});
<?php } else { ?>
function getInvoice() {
    $.ajax({
        cache: false,
        url: 'invoice_lib.php',
        data:'mode=getMonth&dept=<?php echo $_SESSION["department"]; ?>',
        type:'GET',
        success:function(data) {
            $("#tbl_invoice tbody").html(data);
        }
    });
}
getInvoice();
<?php } ?>
</script>