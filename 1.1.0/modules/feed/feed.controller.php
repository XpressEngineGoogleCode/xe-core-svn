<?php
/**
 * @class  feedController
 * @author zero (zero@nzeo.com)
 * @brief  feed module의 controller class
 *
 * RSS 2.0형식으로 문서 출력
 *
 **/

class feedController extends feed {

    /**
     * @brief 초기화
     **/
    function init() {
    }

    /**
     * @brief RSS 사용 유무를 체크하여 feed url 추가
     **/
    function triggerFeedUrlInsert() {
        $current_module_srl = Context::get('module_srl');

        if(!$current_module_srl) {
            // 선택된 모듈의 정보를 가져옴
            $current_module_info = Context::get('current_module_info');
            $current_module_srl = $current_module_info->module_srl;
        }

        if(!$current_module_srl) return new Object();

        // 선택된 모듈의 feed설정을 가져옴
        $oFeedModel = &getModel('feed');
        $feed_config = $oFeedModel->getFeedModuleConfig($current_module_srl);

        if($feed_config->open_feed != 'N') {
            Context::set('rss_url', getUrl('','mid',Context::get('mid'),'act','rss'));
            Context::set('atom_url', getUrl('','mid',Context::get('mid'),'act','atom'));
        }

        return new Object();
    }
}
?>