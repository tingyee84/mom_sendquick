<?php
header("Content-type: text/javascript");
header("Cache-Control: public,  max-age=86400");
$my_file = fopen("vfs_fonts.js", "r"); 
echo fgets($my_file);
?>