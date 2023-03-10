<?php header("Content-type:text/javascript");
include_once("../lib/commonFunc.php");
$header_lib = "header_lib.php";
?>
$.sessionTimeout({
    warnAfter:<?php echo ($_SESSION["timeout"] - 1) * 0.06; ?>e+6,
    redirAfter:<?php echo $_SESSION["timeout"] * 0.06; ?>e+6,
    keepAliveUrl:"keepalive.php",
    logoutUrl: 'logout.php?yes',
    countdownMessage: false,
    countdownBar: true,
    title: "Auto Logout Soon",
    message: "Your session is about to expire. Please click 'Stay Connected' to continue or 'Logout'",
    onRedir:function(){
        $.redirect("logout.php",{yes:"",sessionend:""},"GET")
    }
});
// $('#session-timeout-dialog').modal({
// 	backdrop: 'static',
// 	keyboard: false
// });
// $('#session-timeout-dialog').modal('hide');
$("#lang").val("<?php echo $lang;?>");
$("#lang").on("change",function(){
    confirm("<?php echo $xml_common->change_lang;?> "+$("option:selected",$(this)).text()+"?")&&$.post("langsetting.php",{mode:'update',lang:this.value},function(e){location.reload(!0)})
});
$.post("<?php echo $header_lib?>",{mode:"getmenu"},function(e){$("#web_menu").html(e)}).done(function(ev){
    $("#when_conversation_btn_was_clicked").on("click",function(event){event.preventDefault();Cookies.remove('id');window.location="conversation.php"});
    var urlParams = new URLSearchParams(window.location.search);
    var pn = window.location.pathname.substr(window.location.pathname.lastIndexOf("/")+1)+(urlParams.has("view")?"?view="+urlParams.get("view"):"");
    if (pn == "keyword_api_add.php" || pn == "keyword_add.php") {
        pn = "keyword_management.php";
    }
    <?php // ,-1!==jQuery.inArray(document.location.pathname.match(/[^\/]+$/)[0],["keyword_add.php","keyword_edit.php"])&&$(".key_sub_menu").addClass("active")} ?>
    $(".nav-submenu").hide();
	$("#side-nav a").each(function() {
        if ($(this).attr("href") != "#") {
            if (pn == $(this).attr("href")) {
                $(this).addClass("active");
                $("#"+$(this).data("submenu")).show();
                return true;
            }
        } 
    });
    $("#side-nav a.nav-first-level").each(function() {
        
        $(this).on('click',function(evt) {
            if($(this).next().hasClass("nav-submenu")) {
                evt.preventDefault();
                if ($(this).next().is(":visible")) {
                    $(this).next().slideUp();
                } else {
                    $(this).next().slideDown();
                }
            }
        });
    });
});