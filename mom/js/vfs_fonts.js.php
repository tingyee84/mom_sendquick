<?php
header("Cache-Control: private,  max-age=1800");
header("Content-type: text/javascript");
$my_file = fopen("vfs_fonts.js", "r"); 
echo fgets($my_file);
?>