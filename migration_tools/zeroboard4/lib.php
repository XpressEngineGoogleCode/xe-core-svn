<?php
    $path = $_POST['path'];
    $target_module = $_POST['target_module'];

    $source_charset = $charset = $_POST['charset'];
    $target_charset = "UTF-8";

    unset($connect);

    if($path) {

        if(substr($path,-1)=='/') $path = substr($path, 0, strlen($path)-1);
        $config_file = sprintf('%s/config.php',$path);

        if(file_exists($config_file)) {

            $buff = file($config_file);

            $connect = mysql_connect(trim($buff[1]), trim($buff[2]), trim($buff[3])) or die(mysql_error());
            mysql_select_db(trim($buff[4]), $connect) or die(mysql_error());
        }

    } 

    if(!$connect) {
        header("location:./");
        exit();
    }

    function addXmlQuote($val) {
        global $source_charset, $target_charset;
        $val = str_replace(array('&','<','>'),array('&amp;','&lt;','&gt;'),trim(stripslashes($val)));
        return iconv($source_charset, $target_charset, $val);
    }

    function getFileContentByBase64Encode($filename) {
        $fp = fopen($filename,"r");
	    if($fp) {
  	        while(!feof($fp)) {
	            $buff .= fgets($fp, 1024);
	        }
	        fclose($fp);
	        return base64_encode($buff);
	    }
	    return null;
    }

    function printDownloadHeader($filename) {
        if(strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
            $filename = urlencode($filename);
            $filename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
        }

        header("Content-Type: application/octet-stream");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        //header("Content-Length: " .strlen($content));
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header("Content-Transfer-Encoding: binary");
    }
?>
