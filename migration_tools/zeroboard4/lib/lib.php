<?php

    /**
     * @brief 제로보드4의 경로를 이용하여 DB정보를 얻어옴
     * @author zero@zeroboard.com
     **/
    function getDBInfo($path) {
        if(substr($path,-1)=='/') $path = substr($path, 0, strlen($path)-1);
        $config_file = sprintf('%s/config.php',$path);

        if(!file_exists($config_file)) return;

        $buff = file($config_file);

        $output->hostname = trim($buff[1]);
        $output->userid = trim($buff[2]);
        $output->password = trim($buff[3]);
        $output->database = trim($buff[4]);
        return $output;
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

?>
