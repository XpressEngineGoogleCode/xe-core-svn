<?php
/**
 * @class  feedAdminController
 * @author zero (zero@nzeo.com)
 * @brief  feed module의 admin controller class
 *
 * RSS 2.0형식으로 문서 출력
 *
 **/

class feedAdminController extends feed {

    /**
     * @brief 초기화
     **/
    function init() {
    }

    /**
     * @brief RSS 모듈별 설정
     **/
    function procFeedAdminInsertModuleConfig() {
        // 대상을 구함
        $module_srl = Context::get('target_module_srl');

        // 여러개의 모듈 일괄 설정일 경우
        if(preg_match('/^([0-9,]+)$/',$module_srl)) $module_srl = explode(',', $module_srl);
        else $module_srl = array($module_srl);

        $open_feed = Context::get('open_feed');
        if(!$module_srl || !$open_feed) return new Object(-1, 'msg_invalid_request');

        if(!in_array($open_feed, array('Y','H','N'))) $open_feed = 'N';

        // 설정 저장
        for($i = 0; $i < count($module_srl); $i++) {
            $srl = trim($module_srl[$i]);
            if(!$srl) continue;
            $output = $this->setFeedModuleConfig($srl, $open_feed);
        }

        $this->setError(-1);
        $this->setMessage('success_updated');
    }

    /**
     * @brief RSS 모듈별 설정 함수
     **/
    function setFeedModuleConfig($module_srl, $open_feed) {
        $oModuleModel = &getModel('module');
        $oModuleController = &getController('module');

        $feed_config = $oModuleModel->getModuleConfig('feed');
        $feed_config->module_config[$module_srl]->module_srl = $module_srl;
        $feed_config->module_config[$module_srl]->open_feed = $open_feed;

        $oModuleController->insertModuleConfig('feed', $feed_config);

        return new Object();
    }
}
?>