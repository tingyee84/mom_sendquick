<?php
    header("Content-Type:text/javascript");
?>
// check url 
var urlParams = new URLSearchParams(window.location.search);
var pn = window.location.pathname.substr(window.location.pathname.lastIndexOf("/")+1)+(urlParams.has("view")?"?view="+urlParams.get("view"):"");

$("#side-nav a").each(function() {
	if (pn == $(this).attr("href")) {
		$(this).addClass("active");
		if ($(this).parent().hasClass("nav-submenu")) {
			$(this).parent().show();
		}
	}
});
$("#side-nav a.nav-first-level").each(function() {
	$(this).on('click',function(evt) {
		if ($(this).next().is(":visible") && $(this).next().hasClass("nav-submenu")) {
			$(this).next().slideUp();
		} else {
			$(".nav-submenu:visible").slideUp();
			$(this).next().slideDown();
		}
	});
});

$("#when_conversation_btn_was_clicked").on("click",function(event){event.preventDefault();Cookies.remove('id');window.location="conversation.php"});