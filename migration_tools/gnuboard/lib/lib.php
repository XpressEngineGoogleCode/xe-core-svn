<?php

    /**
     * @brief gnuboard4의 경로를 이용하여 DB정보를 얻어옴
     * @author zero@zeroboard.com
     **/
    function getDBInfo($path) {
        if(substr($path,-1)=='/') $path = substr($path, 0, strlen($path)-1);
        $config_file = sprintf('%s/dbconfig.php',$path);

        if(!file_exists($config_file)) return;

	@include($config_file);

        $output->hostname = $mysql_host;
        $output->userid = $mysql_user;
        $output->password = $mysql_password;
        $output->database = $mysql_db;

	$common_file = sprintf('%s/config.php', $path);
	@include($common_file);

	$output->g4 = $g4;
	$output->db_prefix = $g4['table_prefix'];
        return $output;
    } 

    /**
     * @brief javascript로 에러 메세지 출력
     **/
    function doError($message) {
        header("Content-Type: text/html; charset=UTF-8");
        include "./tpl/header.php"; 
        printf('<script type="text/javascript">alert("%s"); location.href="./";</script>', $message);
        include "./tpl/footer.php"; 
        exit();
    }

    /**
     * @brief change charset
     */
    function convStr($str, $charset) {
	if(!function_exists('iconv') || $charset == 'UTF-8') return $str;
	return iconv($charset, 'UTF-8', $str);
    }

?>
