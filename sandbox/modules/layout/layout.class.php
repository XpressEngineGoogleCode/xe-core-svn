<?php
    /**
     * @class  layout
     * @author zero (zero@nzeo.com)
     * @brief  layout 모듈의 high class
     **/

    class layout extends ModuleObject {

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            // 레이아웃에서 사용할 디렉토리 생성
            FileHandler::makeDir('./files/cache/layout');

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
            // 레이아웃 캐시 삭제 (수정본은 지우지 않음)
            $path = './files/cache/layout';
            if(!is_dir($path)) {
                FileHandler::makeDir($path);
                return;
            }
            $directory = dir($path);
            while($entry = $directory->read()) {
                if ($entry == "." || $entry == ".." || preg_match('/\.html$/i',$entry) ) continue;
                FileHandler::removeFile($path."/".$entry);
            }
            $directory->close();
        }
    }
?>
