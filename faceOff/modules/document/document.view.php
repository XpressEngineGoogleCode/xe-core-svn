<?php
    /**
     * @class  documentView
     * @author zero (zero@nzeo.com)
     * @brief  document 모듈의 View class
     **/

    class documentView extends document {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 문서 인쇄 기능
         * 해당 글만 찾아서 그냥 출력해버린다;;
         **/
        function dispDocumentPrint() {
            // 목록 구현에 필요한 변수들을 가져온다
            $document_srl = Context::get('document_srl');

            // document 객체를 생성. 기본 데이터 구조의 경우 document모듈만 쓰면 만사 해결.. -_-;
            $oDocumentModel = &getModel('document');

            // 선택된 문서 표시를 위한 객체 생성 
            $oDocument = $oDocumentModel->getDocument($document_srl, $this->grant->manager);
            if(!$oDocument->isExists()) return new Object(-1,'msg_invalid_request');

            // 권한 체크
            if(!$oDocument->isAccessible()) return new Object(-1,'msg_not_permitted');


            // 설정된 확장 변수를 찾는다.
            $oModuleModel = &getModel('module');
            $module_info = $oModuleModel->getModuleInfoByDocumentSrl($document_srl);
            $extra_vars = array();
            foreach($module_info->extra_vars as $key => $extra_var){
                $extra_vars[$key]->name = $extra_var->name;
                $extra_vars[$key]->value = $oDocument->getExtraValue($key);
                if(is_array($extra_vars[$key]->value)) $extra_vars[$key]->value = join("",$extra_vars[$key]->value);
            }
            Context::set('extra_vars', $extra_vars);

            // 브라우저 타이틀 설정
            Context::setBrowserTitle($oDocument->getTitleText());
            Context::set('oDocument', $oDocument);

            Context::set('layout','none');
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('print_page');
        }

        /**
         * @brief 미리 보기
         **/
        function dispDocumentPreview() {
            Context::set('layout','none');

            $content = Context::get('content');
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('preview_page');
        }
        
        /**
         * @brief 관리자가 선택한 문서에 대한 관리
         **/
        function dispDocumentManageDocument() {
            if(!Context::get('is_logged')) return new Object(-1,'msg_not_permitted');

            // 선택한 목록을 세션에서 가져옴
            $flag_list = $_SESSION['document_management'];
            if(count($flag_list)) {
                foreach($flag_list as $key => $val) {
                    if(!is_bool($val)) continue;
                    $document_srl_list[] = $key;
                }
            }

            if(count($document_srl_list)) {
                $oDocumentModel = &getModel('document');
                $document_list = $oDocumentModel->getDocuments($document_srl_list, $this->grant->is_admin);
                Context::set('document_list', $document_list);
            }

            $oModuleModel = &getModel('module');

            // 모듈 카테고리 목록과 모듈 목록의 조합
            if(count($module_list)>1) Context::set('module_list', $module_categories);

            // 팝업 레이아웃 선택
            $this->setLayoutPath('./common/tpl');
            $this->setLayoutFile('popup_layout');

            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('checked_list');
        }

    }
?>
