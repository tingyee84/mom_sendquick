<?php
    header("Content-Type:text/javascript");
?>
// check url 
$("#when_conversation_btn_was_clicked").on("click",function(event){event.preventDefault();Cookies.remove('id');window.location="conversation.php"});
