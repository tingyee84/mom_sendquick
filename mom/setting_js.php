<?php header("Content-type:text/javascript");
include("./lib/commonFunc.php");
$chk_mode = 59;
$page_mode = '800'; // Ty's Comment: can't really understand what does it for
include('checkAccess.php');
?>
$("#msgstatusbar").hide();
if (typeof txvalidator === 'function') {
    console.log("Okay");
} else {
    console.log("Not okay");
}
$("button.btn-close").on("click",function(event) {
    $(this).parent().hide();
});
$("input[name='sessiontime']").on("input",function() {
    $("#sessiontimeval").val($(this).val());
});

$("#sessiontimeval").on("change",function() {
    $("input[name='sessiontime']").val($(this).val());
});

$("input[name='pwdexpiry']").on("input",function() {
    $("#pwdexpiryval").val($(this).val());
});

$("#pwdexpiryval").on("change",function() {
    $("input[name='pwdexpiry']").val($(this).val());
});

$("input[name='accthreshold']").on("input",function() {
    $("#accthresholdval").val($(this).val());
});
$("#accthresholdval").on("change",function() {
    $("input[name='accthreshold']").val($(this).val());
});
function reset() {
<?php
$res = getSQLresult($dbconn,"SELECT * FROM setting WHERE variable IN ('sessiontime','pwdexpiry','accthreshold')");
if (is_string($res)) {
    // error
} else {
    foreach($res as $row) {
        echo "\n\t$(\"input[name='{$row["variable"]}']\").val({$row["value"]});";
        echo "\n\t$(\"#{$row["variable"]}val\").val({$row["value"]});";
    }
}
?>
}
reset();
$("#btnreset").on("click",function(event){
    reset();
});
$("#form-setting").submit(function(event) {
    event.preventDefault();
    $("#msgstatusbar").hide();
    $("#msgstatusbar").removeClass("alert-success alert-warning");
    if (!txvalidator($("#sessiontimeval").val(),"TX_INTEGER")) {
        $("#msgstatusbar").addClass("alert-warning");
        $("#msgstatustext").html("Invalid Input. Please insert integer only.");
        $("#msgstatusbar").show();
        $("#sessiontimeval").focus();
    } else if (!txvalidator($("#pwdexpiryval").val(),"TX_INTEGER")) {
        $("#msgstatusbar").addClass("alert-warning");
        $("#msgstatustext").html("Invalid Input. Please insert integer only.");
        $("#msgstatusbar").show();
        $("#pwdexpiryval").focus();
    } else if (!txvalidator($("#accthresholdval").val(),"TX_INTEGER")) {
        $("#msgstatusbar").addClass("alert-warning");
        $("#msgstatustext").html("Invalid Input. Please insert integer only.");
        $("#msgstatusbar").show();
        $("#accthresholdval").focus();
    } else {
        $.ajax({
            type: "POST",
            url: "setting.php",
            data: $("#form-setting").serialize(),
            dataType: "json",
            success: function(data) {
                if(data["statuscode"] === 1) {
                    $("#msgstatusbar").addClass("alert-success");
                } else {
                    $("#msgstatusbar").addClass("alert-warning");
                }
                $("#msgstatustext").html(data["statusmsg"]);
                $("#msgstatusbar").show();
            }
        });
    }
});