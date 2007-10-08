<?php
    /**
     * @class  editor
     * @author zero (zero@nzeo.com)
     * @brief  editor 모듈의 high class
     **/

    class editor extends ModuleObject {

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            // action forward에 등록 (관리자 모드에서 사용하기 위함)
            $oModuleController = &getController('module');
            $oModuleController->insertActionForward('editor', 'view', 'dispEditorAdminIndex');
            $oModuleController->insertActionForward('editor', 'view', 'dispEditorAdminSetupComponent');

            // 기본 에디터 컴포넌트를 추가
            $oEditorController = &getAdminController('editor');
            $oEditorController->insertComponent('colorpicker_text',true);
            $oEditorController->insertComponent('colorpicker_bg',true);
            $oEditorController->insertComponent('emoticon',true);
            $oEditorController->insertComponent('url_link',true);
            $oEditorController->insertComponent('image_link',true);
            $oEditorController->insertComponent('multimedia_link',true);
            $oEditorController->insertComponent('quotation',true);
            $oEditorController->insertComponent('table_maker',true);
            $oEditorController->insertComponent('poll_maker',true);
            $oEditorController->insertComponent('image_gallery',true);

            // 에디터 모듈에서 사용할 디렉토리 생성
            FileHandler::makeDir('./files/cache/editor');

            return new Object();
        }

        /**
         * @brief 설치가 이상이 없는지 체크하는 method
         **/
        function checkUpdate() {
            return false;
        }

        /**
         * @brief 업데이트 실행
         **/
        function moduleUpdate() {
            return new Object();
        }

        /**
         * @brief 캐시 파일 재생성
         **/
        function recompileCache() {
            // 에디터 컴포넌트 캐시 파일 삭제
            FileHandler::removeFilesInDir("./files/cache/editor");
        }
    }
?>
