<?php
/**
 * @brief  feed module의 admin view class
 * @class  feedAdminView
 * @author BNU <bnufactory@gmail.com>
 **/
class feedAdminView extends feed {

    // @brief 초기화
    function init() {
        $this->setTemplatePath($this->module_path.'tpl');
    }

    /**
     * @brief 에러 출력
     **/
    function dispFeedAdminContent() {
        $oModuleModel = &getModel('module');
        $module_config = $oModuleModel->getModuleConfig('feed')->module_config;

        $module_list = $oModuleModel->getMidList();

        $lang_open_feed_types = Context::getLang('open_feed_types');

        $feed_count = array('Y' => 0, 'H' => 0, 'N' => 0);

        foreach($module_list as $mid => $module) {
            if($module_config[$module->module_srl]->open_feed) {
                $open_feed_type = $module_config[$module->module_srl]->open_feed;
            } else {
                $open_feed_type = 'N';
            }

            switch($open_feed_type) {
                case 'N' :
                    $feed_count['N']++;
                    break;
                case 'H' :
                    $feed_count['H']++;
                    break;
                case 'Y' :
                    $feed_count['Y']++;
                    break;
            }

            $module_list[$mid]->open_feed = $lang_open_feed_types[$open_feed_type];
        }

        Context::set('lang_open_feed_types', $lang_open_feed_types);
        Context::set('feed_count', $feed_count);
        Context::set('module_list', $module_list);

        // 템플릿 파일 지정
        $this->setTemplateFile('index');
    }

}
?>