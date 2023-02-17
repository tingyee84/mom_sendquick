$().ready(function(){
    $("#thirdstep").hide();
    $("#secondstep").hide();
    $("#username").focus();
    $("#alertbar").hide();
    $("input[name='otp[]']").each(function(){
        $(this).on('focusin',function(e) {
            $(this).select();
        });
        $(this).on('input',function(e) {
            if ($(this).next("input")[0]) {
                $(this).next("input").focus();
            } else {
                $("#otp_submit").focus();
            }
        });
    });
    $("#otp_resend").on("click",function(e) {
        e.preventDefault();
        $(this).attr("disabled",1);
        $("#otpmode").val("2faresend");
        $("#alertbar").hide();
        $("input[name='otp[]']").val("");
        $.ajax({
            cache:!1,
            url: "forgot_password.php",
            method: "POST",
            data: $("#otp_verify").serialize(),
            dataType: "json",
            success: function(r) {
                $("input[name='otp[]']")[0].focus();
                waitThenActive();
            }
        });
    });
    $("#alertbar").find("button.close").on("click",function(){
        $("#alertbar").hide();
    });
    $("#otp_verify").on('submit',function(e) {
        e.preventDefault();
        $("#alertbar").hide();
        $("#otp_submit").attr("disabled",1);
        $("#otpmode").val("2faverify");
        let temp = "";
        $("input[name='otp[]']").each(function() {
            temp += $(this).val();
        });
        $("input[name='otp']").val(temp);
        $.ajax({
            cache:!1,
            url: "forgot_password.php",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(r) {
                if (r.errcode == 201) {
                    $("#thirdstep").find(".panel-body").html(r.errmsg);
                    $("#thirdstep").slideDown();
                    $("#secondstep").slideUp();
                } else {
                    alertbar(r.errmsg,"danger");
                    $("input[name='otp[]']")[0].focus();    
                    $("#otp_submit").removeAttr("disabled");
                }
            }
        });
    });
    $("#forgotpwd_form").on('submit',function(e) {
        $("#alertbar").hide();
        $("#btn_submit").attr("disabled",1);
        $("input[name='otp[]']").val("");
        $.ajax({
            cache:!1,
            url: "forgot_password.php",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(r) {
                if (r.errcode == 205) {
                    $("#censorednumber").html(r.errmsg);
                    $("#firststep").slideUp();
                    $("#secondstep").slideDown();

                    $("#otpusername").val($("#username").val());
                    $("#sessionid").val(r.sessionid);
                    $("#sessionidtext").text(r.sessionid);
                    $("input[name='otp[]']")[0].focus();
                    waitThenActive();
                } else {
                    alertbar(r.errmsg,"danger");
                    $("#otp_submit").removeAttr("disabled");
                }
            }
        });
        e.preventDefault();
    });
});
function alertbar (msg,type) {
    $("#alertbar").removeClass("alert-success alert-danger alert-warning alert-info");
    $("#alertmsg").html(msg);
    $("#alertbar").addClass("alert-"+type);
    $("#alertbar").show();
}

var activetime = 0;
function waitThenActive () {
    activetime = 60;
    $("#otp_resend").prop("disabled",true);
    var timer = setInterval(() => {
        if (activetime != 0) {
            $("#otp_resend").html("Wait " + (activetime--) + "s");
        } else {
            $("#otp_resend").html("Resend Code");
            $("#otp_resend").prop("disabled",false);
            clearInterval(timer);
        }
    }, 1000);
}