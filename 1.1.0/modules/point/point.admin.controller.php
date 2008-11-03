<?php
    /**
     * @class  pointAdminController
     * @author zero <zero@zeroboard.com>
     * @brief  point모듈의 admin controller class
     **/

    class pointAdminController extends point {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 기본 설정 저장
         **/
        function procPointAdminInsertConfig() {
            // 설정 정보 가져오기
            $oModuleModel = &getModel('module');
            $config = $oModuleModel->getModuleConfig('point');

            // 변수 정리
            $args = Context::getRequestVars();

            // 포인트 이름 체크
            $config->point_name = $args->point_name;
            if(!$config->point_name) $config->point_name = 'point';
            $config->activity_point_name = $args->activity_point_name;
            if(!$config->activity_point_name) $config->activity_point_name = 'point';

            // 기본 포인트 지정
            $config->point->signup = (int)$args->point_signup;
            $config->point->login = (int)$args->point_login;
            $config->point->insert_document = (int)$args->point_insert_document;
            $config->point->read_document = (int)$args->point_read_document;
            $config->point->insert_comment = (int)$args->point_insert_comment;
            $config->point->upload_file = (int)$args->point_upload_file;
            $config->point->download_file = (int)$args->point_download_file;
            $config->point->voted = (int)$args->point_voted;
            $config->point->blamed = (int)$args->point_blamed;

            // 기본 활동 포인트 지정
            $config->exp->signup = (int)$args->exp_signup;
            $config->exp->login = (int)$args->exp_login;
            $config->exp->insert_document = (int)$args->exp_insert_document;
            $config->exp->read_document = (int)$args->exp_read_document;
            $config->exp->insert_comment = (int)$args->exp_insert_comment;
            $config->exp->upload_file = (int)$args->exp_upload_file;
            $config->exp->download_file = (int)$args->exp_download_file;
            $config->exp->voted = (int)$args->exp_voted;
            $config->exp->blamed = (int)$args->exp_blamed;

            // 최고 레벨
            $config->max_level = $args->max_level;
            if($config->max_level > 1000) $config->max_level = 1000;
            if($config->max_level < 1) $config->max_level = 1;

            // 레벨 아이콘 설정
            $config->level_icon = $args->level_icon;

            // 포인트 미달시 다운로드 금지 여부 체크
            if($args->disable_download == 'Y') $config->disable_download = 'Y';
            else $config->disable_download = 'N';

            // 레벨별 그룹 설정
            foreach($args as $key => $val) {
                if(substr($key, 0, strlen('point_group_')) != 'point_group_') continue;
                $group_srl = substr($key, strlen('point_group_'));
                $level = $val;
                if(!$level) unset($config->point_group[$group_srl]);
                else $config->point_group[$group_srl] = $level;
            }

            // 레벨별 포인트 설정
            unset($config->level_step);
            for($i=1; $i <= $config->max_level; $i++) {
                $key = 'level_step_'.$i;
                $config->level_step[$i] = (int)$args->{$key};
            }

            // 레벨별 포인트 계산 함수
            $config->expression = $args->expression;

            // 저장
            $oModuleController = &getController('module');
            $oModuleController->insertModuleConfig('point', $config);

            $this->cacheActList();

            $this->setMessage('success_updated');
        }

        /**
         * @brief 모듈별 설정 저장
         **/
        function procPointAdminInsertModuleConfig() {
            $args = Context::getRequestVars();

            foreach($args as $key => $val) {
                preg_match("/^(point|exp)_([a-z_]+)_([0-9]+)$/", $key, $matches);
                if(!$matches[1]) continue;
                $name = $matches[2];
                $module_srl = $matches[3];
                if(strlen($val) > 0) $module_config[$module_srl][$matches[1]][$name] = (int)$val;
            }

            $oModuleController = &getController('module');
            if(count($module_config)) {
                foreach($module_config as $module_srl => $config) {
                    $oModuleController->insertModulePartConfig('point', $module_srl, $config);
                }
            }

            $this->cacheActList();

            $this->setMessage('success_updated');
        }

        /**
         * @brief 모듈별 개별 포인트 저장
         **/
        function procPointAdminInsertPointModuleConfig() {
            $module_srl = Context::get('target_module_srl');
            if(!$module_srl) return new Object(-1, 'msg_invalid_request');

            // 여러개의 모듈 일괄 설정일 경우
            if(preg_match('/^([0-9,]+)$/', $module_srl)) $module_srl = explode(',', $module_srl);
            else $module_srl = array($module_srl);

            // 설정 저장
            $oModuleController = &getController('module');
            for($i=0; $i < count($module_srl); $i++) {
                $srl = trim($module_srl[$i]);
                if(!$srl) continue;
                unset($config);
                $config['point']['insert_document'] = Context::get('point_insert_document');
                $config['point']['insert_comment'] = Context::get('point_insert_comment');
                $config['point']['upload_file'] = Context::get('point_upload_file');
                $config['point']['download_file'] = Context::get('point_download_file');
                $config['point']['read_document'] = Context::get('point_read_document');
                $config['point']['voted'] = Context::get('point_voted');
                $config['point']['blamed'] = Context::get('point_blamed');

                $config['exp']['insert_document'] = Context::get('exp_insert_document');
                $config['exp']['insert_comment'] = Context::get('exp_insert_comment');
                $config['exp']['upload_file'] = Context::get('exp_upload_file');
                $config['exp']['download_file'] = Context::get('exp_download_file');
                $config['exp']['read_document'] = Context::get('exp_read_document');
                $config['exp']['voted'] = Context::get('exp_voted');
                $config['exp']['blamed'] = Context::get('exp_blamed');

                $oModuleController->insertModulePartConfig('point', $srl, $config);
            }

            $this->setError(-1);
            $this->setMessage('success_updated');
        }

        /**
         * @brief 회원 포인트 변경
         **/
        function procPointAdminUpdatePoint() {
            $member_srl = Context::get('member_srl');
            $point = Context::get('point');

            $oPointController = &getController('point');
            return $oPointController->setPoint($member_srl, (int)$point, 0, 'admin_set');
        }

        /**
         * @brief 회원 포인트 변경
         **/
        function procPointAdminUpdateExp() {
            $member_srl = Context::get('member_srl');
            $exp = Context::get('exp');

            $oPointController = &getController('point');
            return $oPointController->setPoint($member_srl, 0, (int)$exp, 'admin_set');
        }

        /**
         * @brief 전체글/ 댓글/ 첨부파일과 가입정보를 바탕으로 포인트를 재계산함. 단 로그인 점수는 1번만 부여됨
         * @todo 포인트 재계산을 포인트 로그 테이블을 이용하여 계산하도록 변경 필요
         **/
        function procPointAdminReCal() {
            set_time_limit(0);

            // 모듈별 포인트 정보를 가져옴
            $oModuleModel = &getModel('module');
            $module_config = $oModuleModel->getModulePartConfigs('point');

            // 회원의 포인트 저장을 위한 변수
            $member = array();

            // 게시글 정보를 가져옴
            $output = executeQueryArray('point.getDocumentPoint');
            if(!$output->toBool()) return $output;

            if($output->data) {
                foreach($output->data as $key => $val) {
                    $insert_point = $module_config['point']['insert_document'];
                    if(!$insert_point) $insert_point = $this->config->point->insert_document;
                    $insert_exp = $module_config['exp']['insert_document'];
                    if(!$insert_exp) $insert_exp = $this->config->exp->insert_document;

                    if(!$val->member_srl) continue;
                    $point = $insert_point * $val->count;
                    $exp = $insert_exp * $val->count;
                    $member[$val->member_srl]['point'] += $point;
                    $member[$val->member_srl]['exp'] += $exp;
                }
            }
            $output = null;

            // 댓글 정보를 가져옴
            $output = executeQueryArray('point.getCommentPoint');
            if(!$output->toBool()) return $output;

            if($output->data) {
                foreach($output->data as $key => $val) {
                    $insert_point = $module_config['point']['insert_comment'];
                    if(!$insert_point) $insert_point = $config->point->insert_comment;
                    $insert_exp = $module_config['exp']['insert_comment'];
                    if(!$insert_exp) $insert_exp = $config->exp->insert_comment;

                    if(!$val->member_srl) continue;
                    $point = $insert_point * $val->count;
                    $exp = $insert_exp * $val->count;
                    $member[$val->member_srl]['point'] += $point;
                    $member[$val->member_srl]['exp'] += $exp;
                }
            }
            $output = null;

            // 첨부파일 정보를 가져옴
            $output = executeQueryArray('point.getFilePoint');
            if(!$output->toBool()) return $output;

            if($output->data) {
                foreach($output->data as $key => $val) {
                    $insert_point = $module_config['point']['upload_file'];
                    if(!$insert_point) $insert_point = $config->point->upload_file;
                    $insert_exp = $module_config['exp']['upload_file'];
                    if(!$insert_exp) $insert_exp = $config->exp->upload_file;

                    if(!$val->member_srl) continue;
                    $point = $insert_point * $val->count;
                    $exp = $insert_exp * $val->count;
                    $member[$val->member_srl]['point'] += $point;
                    $member[$val->member_srl]['exp'] += $exp;
                }
            }
            $output = null;

            // 모든 회원의 포인트를 0으로 세팅
            $output = executeQuery('point.initMemberPoint');
            if(!$output->toBool()) return $output;

            // 모든 포인트 로그를 삭제
            $output = executeQuery('point.initPointLog');
            if(!$output->toBool()) return $output;

            // 임시로 파일 저장
            $f = fopen('./files/cache/pointRecal.txt', 'w');
            foreach($member as $key => $val) {
                $val['point'] += (int)$this->config->point->signup;
                $val['exp'] += (int)$this->config->exp->signup;
                fwrite($f, $key.','.$val['point'].','.$val['exp']."\r\n");
            }
            fclose($f);

            $this->add('total', count($member));
            $this->add('position', 0);
            $this->setMessage( sprintf(Context::getLang('point_recal_message'), 0, $this->get('total')) );
        }

        /**
         * @brief 파일로 저장한 회원 포인트를 5000명 단위로 적용
         * @todo 제거 대상. 포인트 재계산을 포인트 로그 테이블을 이용하여 계산하도록 변경 필요
         **/
        function procPointAdminApplyPoint() {
        	$oPointController = &getController('point');
            $position = (int)Context::get('position');
            $total = (int)Context::get('total');

            if(!file_exists('./files/cache/pointRecal.txt')) return new Object(-1, 'msg_invalid_request');

            $idx = 0;
            $f = fopen('./files/cache/pointRecal.txt', 'r');
            while(!feof($f)) {
                $str = trim(fgets($f, 1024));
                $idx ++;
                if($idx > $position) {
                    list($member_srl, $point, $exp) = explode(',', $str);

                    $output = $oPointController->SetPoint($member_srl, $point, $exp, 'init');
                    if($idx % 100 == 0) break;
                }
            }

            if(feof($f)) {
                FileHandler::removeFile('./files/cache/pointRecal.txt');
                $idx = $total;

                FileHandler::rename('./files/member_extra_info/point', './files/member_extra_info/point.old');

                FileHandler::removeDir('./files/member_extra_info/point.old');
            }
            fclose($f);


            $this->add('total', $total);
            $this->add('position', $idx);
            $this->setMessage(sprintf(Context::getLang('point_recal_message'), $idx, $total));

        }

        /**
         * @brief 캐시파일 저장
         **/
        function cacheActList() {
            return;
        }

    }
?>