<?php
    /**
     * @class  document 
     * @author zero (zero@nzeo.com)
     * @brief  document 모듈의 high 클래스
     **/

    require_once('./modules/document/document.item.php');

    class document extends ModuleObject {

        // 공지사항용 값
        var $notice_list_order = -2100000000;

        // 관리자페이지에서 사용할 검색 옵션
        var $search_option = array('title','content','title_content','user_name',); ///< 검색 옵션

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            // action forward에 등록 (관리자 모드에서 사용하기 위함)
            $oModuleController = &getController('module');
            $oModuleController->insertActionForward('document', 'view', 'dispDocumentAdminList');
            $oModuleController->insertActionForward('document', 'view', 'dispDocumentPrint');

            return new Object();
        }

        /**
         * @brief 설치가 이상이 없는지 체크하는 method
         **/
        function checkUpdate() {
            $oDB = &DB::getInstance();

            /**
             * 2007. 7. 23 : 확장변수(extra_vars1~20까지 추가)
             **/
            if(!$oDB->isColumnExists("documents","extra_vars20")) return true;

            return false;
        }

        /**
         * @brief 업데이트 실행
         **/
        function moduleUpdate() {
            /**
             * 2007. 7. 23 : 확장변수(extra_vars1~20까지 추가)
             **/
            $oDB = &DB::getInstance();
            if(!$oDB->isColumnExists("documents","extra_vars20")) {
                for($i=1;$i<=20;$i++) {
                    $column_name = "extra_vars".$i;
                    $oDB->addColumn('documents',$column_name,'text');
                }
            }

            return new Object(0,'success_updated');
        }

    }
?>
