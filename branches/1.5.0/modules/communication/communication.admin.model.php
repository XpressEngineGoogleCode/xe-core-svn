<?php
    /**
     * @class  communicationAdminModel
     * @author NHN (developers@xpressengine.com)
     * @brief communication module of the admin model class
     **/

    class communicationAdminModel extends communication {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief return the html to select colorset of the skin
         **/
        function getCommunicationAdminColorset() {
            $skin = Context::get('skin');
            if(!$skin) $tpl = "";
            else {
                $oModuleModel = &getModel('module');
                $skin_info = $oModuleModel->loadSkinInfo($this->module_path, $skin);
                Context::set('skin_info', $skin_info);

                $oModuleModel = &getModel('module');
                $communication_config = $oModuleModel->getModuleConfig('communication');
                if(!$communication_config->colorset) $communication_config->colorset = "white";
                Context::set('communication_config', $communication_config);

                $oTemplate = &TemplateHandler::getInstance();
                $tpl = $oTemplate->compile($this->module_path.'tpl', 'colorset_list');
            }

            $this->add('tpl', $tpl);
        }

    }
?>
