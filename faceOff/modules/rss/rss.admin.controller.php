<?php
    /**
     * @class  rssAdminController
     * @author zero (zero@nzeo.com)
     * @brief  rss module의 admin controller class
     *
     * RSS 2.0형식으로 문서 출력
     *
     **/

    class rssAdminController extends rss {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief RSS 모듈별 설정
         **/
        function procRssAdminInsertModuleConfig() {
            // 대상을 구함
            $module_srl = Context::get('target_module_srl');

            // 여러개의 모듈 일괄 설정일 경우
            if(preg_match('/^([0-9,]+)$/',$module_srl)) $module_srl = explode(',',$module_srl);
            else $module_srl = array($module_srl);
            if(!is_array($module_srl)) $module_srl[0] = $module_srl;

            $config_vars = Context::getRequestVars();
            $open_rss = $config_vars->open_rss;
            $open_total_feed = $config_vars->open_total_feed;
            $feed_description = trim($config_vars->feed_description);
            $feed_copyright = trim($config_vars->feed_copyright);
            if(!$module_srl || !$open_rss) return new Object(-1, 'msg_invalid_request');

            if(!in_array($open_rss, array('Y','H','N'))) $open_rss = 'N';

            // 설정 저장
            for($i=0;$i<count($module_srl);$i++) {
                $srl = trim($module_srl[$i]);
                if(!$srl) continue;
                $output = $this->setRssModuleConfig($srl, $open_rss, $open_total_feed, $feed_description, $feed_copyright);
            }

            $this->setError(-1);
            $this->setMessage('success_updated');
        }

        /**
         * @brief RSS 모듈별 설정 함수
         **/
        function setRssModuleConfig($module_srl, $open_rss, $open_total_feed = 'N', $feed_description = 'N', $feed_copyright = 'N') {
            $oModuleController = &getController('module');
            $config->open_rss = $open_rss;
            $config->open_total_feed = $open_total_feed;
            if($feed_description != 'N') { $config->feed_description = $feed_description; }
            if($feed_copyright != 'N') { $config->feed_copyright = $feed_copyright; }
            $oModuleController->insertModulePartConfig('rss',$module_srl,$config);
            return new Object();
        }
    }
?>
