<?php
    $path = $_REQUEST['path'];
    if(!$path) return;

    if(substr($path,-1)=='/') $path = substr($path, 0, strlen($path)-1);
    $config_file = sprintf('%s/config.php',$path);
    if(!file_exists($config_file)) return;

    $buff = file($config_file);
?>
