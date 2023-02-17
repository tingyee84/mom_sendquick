<?php
header("Content-type:text/javascript");
?>
$('#logout').click(function(){
    window.location = 'logout.php?yes';
});
$('#cancel').click(function() {
    history.back(1);
});