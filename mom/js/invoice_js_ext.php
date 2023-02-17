<?php 
header("Content-type:text/javascript");
include_once("../lib/commonFunc.php");
?>

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