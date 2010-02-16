<?php
    /**
     * @file   config/config.inc.php
     * @author zero (zero@nzeo.com)
     * @brief  기본적으로 사용하는 class파일의 include 및 환경 설정을 함
     **/

    @error_reporting(E_ALL ^ E_NOTICE);

    if(!defined('__ZBXE__')) exit();

    /**
     * @brief XE version 
     **/
    define('__ZBXE_VERSION__', '2.0.0');

    /**
     * @brief base path where XpressEngine is installed 
     **/
    define('_XE_PATH_', str_replace('config/config.inc.php', '', str_replace('\\', '/', __FILE__)));

    if(!defined('__DEBUG__')) define('__DEBUG__', 1);
    if(!defined('__DEBUG_OUTPUT__')) define('__DEBUG_OUTPUT__', 0);
    if(!defined('__debug_protect__')) define('__debug_protect__', 0);
    if(!defined('__debug_protect_ip__')) define('__debug_protect_ip__', '127.0.0.1');

    /**
     * @brief require basic functions
     **/
    require(_XE_PATH_.'config/func.inc.php');

	if(__DEBUG__ & 1) loadBrick('debug');
//	loadBrick("context");
//	loadBrick("requesthandler");
//	loadBrick("runtarget");
//	loadBrick("object");
?>
