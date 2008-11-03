<?php
    /**
     * @class  pointView
     * @author zero <zero@zeroboard.com>
     * @brief  point module의 view class
     **/

    class pointView extends point {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 서비스형 모듈의 추가 설정을 위한 부분
         * point의 사용 형태에 대한 설정만 받음
         **/
        function triggerDispPointAdditionSetup(&$obj) {
            $current_module_srl = Context::get('module_srl');
            $current_module_srls = Context::get('module_srls');

            if(!$current_module_srl && !$current_module_srls) {
                $current_module_info = Context::get('current_module_info');
                $current_module_srl = $current_module_info->module_srl;
                if(!$current_module_srl) return new Object();
            }

            // 설정 정보 가져오기
            $oModuleModel = &getModel('module');

            if($current_module_srl) {
                $module_config = $oModuleModel->getModulePartConfig('point', $current_module_srl);
                
                if(!$module_config) {
                    $module_config['insert_document'] = $this->config->insert_document;
                    $module_config['insert_comment'] = $this->config->insert_comment;
                    $module_config['upload_file'] = $this->config->upload_file;
                    $module_config['download_file'] = $this->config->download_file;
                    $module_config['read_document'] = $this->config->read_document;
                    $module_config['voted'] = $this->config->voted;
                    $module_config['blamed'] = $this->config->blamed;
                }
            }

            $module_config['module_srl'] = $current_module_srl;
            $module_config['point_name'] = $this->config->point_name;
            $module_config['activity_point_name'] = $this->config->activity_point_name;
            Context::set('module_config', $module_config);

            // 템플릿 파일 지정
            $oTemplate = &TemplateHandler::getInstance();
            $tpl = $oTemplate->compile($this->module_path.'tpl', 'point_module_config');
            $obj .= $tpl;

            return new Object();
        }
    }
?>