<?php
    /**
     * @class  point
     * @author zero <zero@zeroboard.com>
     * @brief  point모듈의 high class
     **/

    class point extends ModuleObject {

        /**
         * @berif point 설정
         */
        public $config;
        public $cache_path = array(
            'point' => './files/member_extra_info/point/',
            'exp' => './files/member_extra_info/exp/'
        );

        function __construct() {
            $oModuleModel = &getModel('module');
            $this->config = $oModuleModel->getModuleConfig('point');
        }

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            // action forward에 등록 (관리자 모드에서 사용하기 위함)
            $oModuleController = &getController('module');
            $oModuleController->insertActionForward('point', 'view', 'dispPointAdminConfig');
            $oModuleController->insertActionForward('point', 'view', 'dispPointAdminModuleConfig');
            $oModuleController->insertActionForward('point', 'view', 'dispPointAdminActConfig');
            $oModuleController->insertActionForward('point', 'view', 'dispPointAdminPointList');
            $oModuleController->insertActionForward('point', 'view', 'dispPointAdminPointLogList');

            // 포인트 정보를 기록할 디렉토리 생성
            FileHandler::makeDir($this->cache_path['point']);

            $oModuleController = &getController('module');
            $oModuleModel = &getModel('module');
            $config = $oModuleModel->getModuleConfig('point');

            // 최고레벨
            $config->max_level = (isset($config->max_level)) ? $config->max_level : 30;

            // 레벨별 점수
            for($i=1; $i <= 30; $i++) {
                $config->level_step[$i] = pow($i, 2) * 90;
            }

            // 포인트 호칭
            $config->point_name = (isset($config->point_name)) ? $config->point_name : 'point';
            $config->activity_point_name = (isset($config->activity_point_name)) ? $config->activity_point_name : 'point';

            // 레벨 아이콘 디렉토리
            $config->level_icon = (isset($config->level_icon)) ? $config->level_icon : 'default';

            // 점수가 없을때 다운로드 금지 기능
            $config->disable_download = (isset($config->disable_download)) ? $config->disable_download : false;

            /**
             * 모듈별 기본 점수 및 각 action 정의 (게시판, 블로그외에 어떤 모듈이 생길지 모르니 act값을 명시한다
             **/
            if(!isset($config->point) && isset($config->signup_point)) {
                // 이전 설정이 있으면 가져오기. 추후 제거 대상
                // 회원가입
                $config->point->signup = $config->signup_point;
                $config->exp->signup = $config->signup_point;

                // 로그인
                $config->point->login = $config->login_point;
                $config->exp->login = $config->login_point;

                // 글 작성
                $config->point->insert_document = $config->insert_document;
                $config->exp->insert_document = $config->insert_document;

                // 댓글 작성
                $config->point->insert_comment = $config->insert_comment;
                $config->exp->insert_comment = $config->insert_comment;

                // 업로드
                $config->point->upload_file = $config->upload_file;
                $config->exp->upload_file = $config->upload_file;

                // 다운로드
                $config->point->download_file = $config->download_file;
                $config->exp->download_file = $config->download_file;

                // 조회
                $config->point->read_document = $config->read_document;
                $config->exp->read_document = $config->read_document;

                // 추천 / 비추천
                $config->point->voted = $config->voted;
                $config->exp->voted = $config->voted;
                $config->point->blamed = $config->blamed;
                $config->exp->blamed = $config->blamed;
            } else {
                // 회원가입
                $config->point->signup = 10;
                $config->exp->signup = 10;

                // 로그인
                $config->point->login = 5;
                $config->exp->login = 5;

                // 글 작성
                $config->point->insert_document = 10;
                $config->exp->insert_document = 10;

                // 댓글 작성
                $config->point->insert_comment = 5;
                $config->exp->insert_comment = 5;

                // 업로드
                $config->point->upload_file = 5;
                $config->exp->upload_file = 5;

                // 다운로드
                $config->point->download_file = -5;
                $config->exp->download_file = 0;

                // 조회
                $config->point->read_document = 0;
                $config->exp->read_document = 0;

                // 추천 / 비추천
                $config->point->voted = 0;
                $config->exp->voted = 0;
                $config->point->blamed = 0;
                $config->exp->blamed = 0;
            }

            // 설정 저장
            $oModuleController->insertModuleConfig('point', $config);

            // 빠른 실행을 위해서 act list를 캐싱
            $oPointController = &getAdminController('point');
            $oPointController->cacheActList();

            // 가입/글작성/댓글작성/파일업로드/다운로드에 대한 트리거 추가
            $oModuleController->insertTrigger('member.insertMember', 'point', 'controller', 'triggerInsertMember', 'after');
            $oModuleController->insertTrigger('document.insertDocument', 'point', 'controller', 'triggerInsertDocument', 'after');
            $oModuleController->insertTrigger('document.deleteDocument', 'point', 'controller', 'triggerDeleteDocument', 'after');
            $oModuleController->insertTrigger('comment.insertComment', 'point', 'controller', 'triggerInsertComment', 'after');
            $oModuleController->insertTrigger('comment.deleteComment', 'point', 'controller', 'triggerDeleteComment', 'after');
            $oModuleController->insertTrigger('file.insertFile', 'point', 'controller', 'triggerInsertFile', 'after');
            $oModuleController->insertTrigger('file.deleteFile', 'point', 'controller', 'triggerDeleteFile', 'after');
            $oModuleController->insertTrigger('file.downloadFile', 'point', 'controller', 'triggerBeforeDownloadFile', 'before');
            $oModuleController->insertTrigger('file.downloadFile', 'point', 'controller', 'triggerDownloadFile', 'after');
            $oModuleController->insertTrigger('member.doLogin', 'point', 'controller', 'triggerAfterLogin', 'after');
            $oModuleController->insertTrigger('module.dispAdditionSetup', 'point', 'view', 'triggerDispPointAdditionSetup', 'after');
            $oModuleController->insertTrigger('document.updateReadedCount', 'point', 'controller', 'triggerUpdateReadedCount', 'after');

            // 추천 / 비추천에 대한 트리거 추가 2008.05.13 haneul
            $oModuleController->insertTrigger('document.updateVotedCount', 'point', 'controller', 'triggerUpdateVotedCount', 'after');

            // 특정 문서의 모든 댓글 삭제 트리거
            $oModuleController->insertTrigger('comment.deleteComments', 'point', 'controller', 'triggerDeleteComments', 'after');

            return new Object();
        }

        /**
         * @brief 설치가 이상이 없는지 체크하는 method
         **/
        function checkUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');

            // point 모듈 정보 가져옴

            // 가입/글작성/댓글작성/파일업로드/다운로드에 대한 트리거 추가
            if(!$oModuleModel->getTrigger('member.insertMember', 'point', 'controller', 'triggerInsertMember', 'after')) return true;
            if(!$oModuleModel->getTrigger('document.insertDocument', 'point', 'controller', 'triggerInsertDocument', 'after')) return true;
            if(!$oModuleModel->getTrigger('document.deleteDocument', 'point', 'controller', 'triggerDeleteDocument', 'after')) return true;
            if(!$oModuleModel->getTrigger('comment.insertComment', 'point', 'controller', 'triggerInsertComment', 'after')) return true;
            if(!$oModuleModel->getTrigger('comment.deleteComment', 'point', 'controller', 'triggerDeleteComment', 'after')) return true;
            if(!$oModuleModel->getTrigger('file.insertFile', 'point', 'controller', 'triggerInsertFile', 'after')) return true;
            if(!$oModuleModel->getTrigger('file.deleteFile', 'point', 'controller', 'triggerDeleteFile', 'after')) return true;
            if(!$oModuleModel->getTrigger('file.downloadFile', 'point', 'controller', 'triggerBeforeDownloadFile', 'before')) return true;
            if(!$oModuleModel->getTrigger('file.downloadFile', 'point', 'controller', 'triggerDownloadFile', 'after')) return true;
            if(!$oModuleModel->getTrigger('member.doLogin', 'point', 'controller', 'triggerAfterLogin', 'after')) return true;
            if(!$oModuleModel->getTrigger('module.dispAdditionSetup', 'point', 'view', 'triggerDispPointAdditionSetup', 'after')) return true;
            if(!$oModuleModel->getTrigger('document.updateReadedCount', 'point', 'controller', 'triggerUpdateReadedCount', 'after')) return true;

            // 추천 / 비추천에 대한 트리거 추가 2008.05.13 haneul
            if(!$oModuleModel->getTrigger('document.updateVotedCount', 'point', 'controller', 'triggerUpdateVotedCount', 'after')) return true;

            // exp 필드
            if(!$oDB->isColumnExists('point', 'exp')) return true;

            // 대체된 트리거 삭제
            if($oModuleModel->getTrigger('document.deleteDocument', 'point', 'controller', 'triggerBeforeDeleteDocument', 'before')) return true;
            if(!$oModuleModel->getTrigger('comment.deletecomments', 'point', 'controller', 'triggerDeleteComments', 'after')) return true;

            if(!$oModuleModel->getActionForward('dispPointAdminPointLogList')) return true;

            return false;
        }

        /**
         * @brief 업데이트 실행
         **/
        function moduleUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');

            // point 모듈 정보 가져옴
            $oModuleController = &getController('module');

            // 가입/글작성/댓글작성/파일업로드/다운로드에 대한 트리거 추가
            if(!$oModuleModel->getTrigger('member.insertMember', 'point', 'controller', 'triggerInsertMember', 'after'))
                $oModuleController->insertTrigger('member.insertMember', 'point', 'controller', 'triggerInsertMember', 'after');
            if(!$oModuleModel->getTrigger('document.insertDocument', 'point', 'controller', 'triggerInsertDocument', 'after'))
                $oModuleController->insertTrigger('document.insertDocument', 'point', 'controller', 'triggerInsertDocument', 'after');
            if(!$oModuleModel->getTrigger('document.deleteDocument', 'point', 'controller', 'triggerBeforeDeleteDocument', 'before'))
                $oModuleController->insertTrigger('document.deleteDocument', 'point', 'controller', 'triggerBeforeDeleteDocument', 'before');
            if(!$oModuleModel->getTrigger('document.deleteDocument', 'point', 'controller', 'triggerDeleteDocument', 'after'))
                $oModuleController->insertTrigger('document.deleteDocument', 'point', 'controller', 'triggerDeleteDocument', 'after');
            if(!$oModuleModel->getTrigger('comment.insertComment', 'point', 'controller', 'triggerInsertComment', 'after'))
                $oModuleController->insertTrigger('comment.insertComment', 'point', 'controller', 'triggerInsertComment', 'after');
            if(!$oModuleModel->getTrigger('comment.deleteComment', 'point', 'controller', 'triggerDeleteComment', 'after'))
                $oModuleController->insertTrigger('comment.deleteComment', 'point', 'controller', 'triggerDeleteComment', 'after');
            if(!$oModuleModel->getTrigger('file.insertFile', 'point', 'controller', 'triggerInsertFile', 'after'))
                $oModuleController->insertTrigger('file.insertFile', 'point', 'controller', 'triggerInsertFile', 'after');
            if(!$oModuleModel->getTrigger('file.deleteFile', 'point', 'controller', 'triggerDeleteFile', 'after'))
                $oModuleController->insertTrigger('file.deleteFile', 'point', 'controller', 'triggerDeleteFile', 'after');
            if(!$oModuleModel->getTrigger('file.downloadFile', 'point', 'controller', 'triggerBeforeDownloadFile', 'before'))
                $oModuleController->insertTrigger('file.downloadFile', 'point', 'controller', 'triggerBeforeDownloadFile', 'before');
            if(!$oModuleModel->getTrigger('file.downloadFile', 'point', 'controller', 'triggerDownloadFile', 'after'))
                $oModuleController->insertTrigger('file.downloadFile', 'point', 'controller', 'triggerDownloadFile', 'after');
            if(!$oModuleModel->getTrigger('member.doLogin', 'point', 'controller', 'triggerAfterLogin', 'after'))
                $oModuleController->insertTrigger('member.doLogin', 'point', 'controller', 'triggerAfterLogin', 'after');
            if(!$oModuleModel->getTrigger('module.dispAdditionSetup', 'point', 'view', 'triggerDispPointAdditionSetup', 'after'))
                $oModuleController->insertTrigger('module.dispAdditionSetup', 'point', 'view', 'triggerDispPointAdditionSetup', 'after');
            if(!$oModuleModel->getTrigger('document.updateReadedCount', 'point', 'controller', 'triggerUpdateReadedCount', 'after'))
                $oModuleController->insertTrigger('document.updateReadedCount', 'point', 'controller', 'triggerUpdateReadedCount', 'after');

            // 추천 / 비추천에 대한 트리거 추가 2008.05.13 haneul
            if(!$oModuleModel->getTrigger('document.updateVotedCount', 'point', 'controller', 'triggerUpdateVotedCount', 'after'))
                $oModuleController->insertTrigger('document.updateVotedCount', 'point', 'controller', 'triggerUpdateVotedCount', 'after');

            // 경험치 필드 추가
            if(!$oDB->isColumnExists('point', 'exp')) {
                $oDB->addColumn('point', 'exp', 'number', 11, 0, true);
            }

            // 특정 문서의 모든 댓글 삭제 트리거 추가
            if(!$oModuleModel->getTrigger('comment.deleteComments', 'point', 'controller', 'triggerDeleteComments', 'after')) {
                $oModuleController->insertTrigger('comment.deleteComments', 'point', 'controller', 'triggerDeleteComments', 'after');
            }

            // 대체된 트리거 삭제
            if($oModuleModel->getTrigger('document.deleteDocument', 'point', 'controller', 'triggerBeforeDeleteDocument', 'before')) {
                $oModuleController->deleteTrigger('document.deleteDocument', 'point', 'controller', 'triggerBeforeDeleteDocument', 'before');
            }

            if(!$oModuleModel->getActionForward('dispPointAdminPointLogList')) {
                $oModuleController->insertActionForward('point', 'view', 'dispPointAdminPointLogList');
            }

            return new Object(0, 'success_updated');
        }

        /**
         * @brief 캐시 파일 재생성
         **/
        function recompileCache() {
            // point action 파일 재정의
            $oPointAdminController = &getAdminController('point');
            $oPointAdminController->cacheActList();

        }

        /**
         * @brief 권한 체크를 실행하는 method
         * 모듈 객체가 생성된 경우는 직접 권한을 체크하지만 기능성 모듈등 스스로 객체를 생성하지 않는 모듈들의 경우에는
         * ModuleObject에서 직접 method를 호출하여 권한을 확인함
         *
         * isAdminGrant는 관리권한 이양시에만 사용되도록 하고 기본은 false로 return 되도록 하여 잘못된 권한 취약점이 생기지 않도록 주의하여야 함
         **/
        function isAdmin() {
            // 로그인이 되어 있지 않으면 무조건 return false
            $is_logged = Context::get('is_logged');
            if(!$is_logged) return false;

            // 사용자 아이디를 구함
            $logged_info = Context::get('logged_info');

            // 모듈 요청에 사용된 변수들을 가져옴
            $args = Context::getRequestVars();

            // act의 값에 따라서 관리 권한 체크
            switch($args->act) {
                case 'procPointAdminInsertPointModuleConfig' :
                        if(!$args->target_module_srl) return false;

                        $oModuleModel = &getModel('module');
                        $module_info = $oModuleModel->getModuleInfoByModuleSrl($args->target_module_srl);
                        if(!$module_info) return false;

                        if($oModuleModel->isModuleAdmin($module_info, $logged_info)) return true;
                    break;
            }

            return false;
        }
    }
?>