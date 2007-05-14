<?php
    // act값을 구함
    $act = $_REQUEST["act"];

    // act값에 따른 처리
    switch($act) {
        case 'procGetModuleList' : 
            break;
    }

    // 결과 출력
    header("Content-Type: text/xml; charset=UTF-8");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
?>
<response>
    <error>0</error>
    <message>success</message>
    <module_list>aaa</module_list>
</response>
