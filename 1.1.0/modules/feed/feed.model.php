<?php
/**
 * @class  feedModel
 * @author zero (zero@nzeo.com)
 * @brief  feed module의 model class
 *
 * RSS 2.0형식으로 문서 출력
 *
 **/

class feedModel extends feed {

    /**
     * @brief 특정 모듈의 feed 설정을 return
     **/
    function getFeedModuleConfig($module_srl) {
        // feed 모듈의 config를 가져옴
        $oModuleModel = &getModel('module');
        $feed_config = $oModuleModel->getModuleConfig('feed');

        $module_feed_config = $feed_config->module_config[$module_srl];

        if(!$module_feed_config->module_srl) {
            $module_feed_config->module_srl = $module_srl;
            $module_feed_config->open_feed = 'N';
        }
        return $module_feed_config;
    }


    /**
     * @brief RSS 출력
     **/
    function getFeedDodumentList() {
        $mid = Context::get('mid'); ///< 대상 모듈 id, 없으면 전체로

        // feed module config를 가져옴
        $oModuleModel = &getModel('module');
        $feed_config = $oModuleModel->getModuleConfig('feed');

        /**
         * 요청된 모듈 혹은 전체 모듈의 정보를 구하고 open_feed의 값을 체크
         **/
        $mid_list = array();

        // mid값이 없으면 전체 mid중 open_feed == 'Y|H'인 걸 고름
        if(!$mid) {

            $module_srl_list = null;

            // feed config에 등록된 모듈중 feed 공개하는 것들의 module_srl을 고름
            if($feed_config->module_config && count($feed_config->module_config)) {
                foreach($feed_config->module_config as $key => $val) {
                    if($val->open_feed == 'N' || !$val->open_feed) continue;
                    $module_srl_list[] = $val->module_srl;
                }
            }
            // 선택된 모듈이 없으면 패스
            if(!$module_srl_list || !count($module_srl_list)) return $this->dispError();

            // 선택된 모듈들을 정리
            $args->module_srls = implode(',',$module_srl_list);
            $module_list = $oModuleModel->getMidList($args);
            if(!$module_list) return $this->dispError();

            // 대상 모듈을 정리함
            $module_srl_list = array();
            foreach($module_list as $mid => $val) {
                $val->open_feed = $feed_config->module_config[$val->module_srl]->open_feed;
                $module_srl_list[] = $val->module_srl;
                $mid_list[$val->module_srl] = $val;
            }
            if(!count($module_srl_list)) return $this->dispError();
            unset($output);
            unset($args);

            $module_srl = implode(',',$module_srl_list);

        // 있으면 해당 모듈의 정보를 구함
        } else {
            // 모듈의 설정 정보를 받아옴 (module model 객체를 이용)
            $module_info = $oModuleModel->getModuleInfoByMid($mid);
            if($module_info->mid != $mid) return $this->dispError();

            // 해당 모듈이 feed를 사용하는지 확인
            $feed_module_config = $feed_config->module_config[$module_info->module_srl];
            if(!$feed_module_config->open_feed) $feed_module_config->open_feed = 'N';

            // RSS 비활성화 되었는지 체크하여 비활성화시 에러 출력
            if($feed_module_config->open_feed == 'N') return $this->dispError();

            $module_srl = $module_info->module_srl;
            $module_info->open_feed = $feed_module_config->open_feed;
            $mid_list[$module_info->module_srl] = $module_info;

            unset($args);
        }

        /**
         * 출력할 컨텐츠 추출을 위한 인자 정리
         **/
        $args->module_srl = $module_srl;
        $args->page = 1;
        $args->list_count = 15;
        if($start_date) $args->start_date = $start_date;
        if($end_date) $args->end_date = $end_date;

        $args->sort_index = 'list_order';
        $args->order_type = 'asc';

        // 대상 문서들을 가져옴
        $oDocumentModel = &getModel('document');
        $output = $oDocumentModel->getDocumentList($args);
        $document_list = $output->data;

        return $document_list;
    }


}
?>