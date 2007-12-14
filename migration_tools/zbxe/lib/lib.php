<?php

    define('__ZBXE__', true);

    /**
     * @brief 제로보드4의 경로를 이용하여 DB정보를 얻어옴
     * @author zero@zeroboard.com
     **/
    function getDBInfo($path) {
        if(substr($path,-1)=='/') $path = substr($path, 0, strlen($path)-1);
        $config_file = sprintf('%s/files/config/db.config.php',$path);

        if(!file_exists($config_file)) return;
        include($config_file);

        return $db_info;
    } 

    /**
     * @brief javascript로 에러 메세지 출력
     **/
    function doError($message) {
        include "./tpl/header.php"; 
        printf('<script type="text/javascript">alert("%s"); location.href="./";</script>', $message);
        include "./tpl/footer.php"; 
        exit();
    }


    function getNumberingPath($no, $size=3) {
        $mod = pow(10,$size);
        $output = sprintf('%0'.$size.'d/', $no%$mod);
        if($no >= $mod) $output .= getNumberingPath((int)$no/$mod, $size);
        return $output;
    }
?>
