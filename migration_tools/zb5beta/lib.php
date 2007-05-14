<?php
    $path = $_POST['path'];
    $target_module = $_POST['target_module'];

    unset($connect);

    if($path) {

        if(substr($path,-1)=='/') $path = substr($path, 0, strlen($path)-1);
        $config_file = sprintf('%s/files/db_config.inc.php',$path);

        if(file_exists($config_file)) {

            $file_buff = file($config_file);
            $db_info = explode("\n",base64_decode(trim($file_buff[1])));

            for($i=0;$i<count($db_info);$i++) {
                $buff[$i+1] = base64_decode($db_info[$i]);
            }

            $connect = mysql_connect(trim($buff[1]), trim($buff[2]), trim($buff[3])) or die(mysql_error());
            mysql_select_db(trim($buff[4]), $connect) or die(mysql_error());
            mysql_query("SET NAMES 'utf8';", $connect);

            $db_prefix = $buff[5];
        }
    } 

    if(!$connect) {
        header("location:./");
        exit();
    }

    function addXmlQuote($val) {
        return str_replace(array('<','>','&'),array('&lt;','gt;','&amp;'),trim($val));
    }

    function getFileContentByBase64Encode($filename) {
        $fp = fopen($filename,"r");
        $buff = fgets($fp, filesize($filename));
        fclose($fp);
        return base64_encode($buff);
    }
?>
