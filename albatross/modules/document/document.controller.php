<?php
    /**
     * @class  documentController
     * @author zero (zero@nzeo.com)
     * @brief  document 모듈의 controller 클래스
     **/

    class documentController extends document {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @breif 게시글의 추천을 처리하는 action (Up)
         **/
        function procDocumentVoteUp() {
            $document_srl = Context::get('target_srl');
            if(!$document_srl) return new Object(-1, 'msg_invalid_request');

            $point = 1;
            return $this->updateVotedCount($document_srl, $point);
        }

        /**
         * @breif 게시글의 추천을 처리하는 action (Down)
         **/
        function procDocumentVoteDown() {
            $document_srl = Context::get('target_srl');
            if(!$document_srl) return new Object(-1, 'msg_invalid_request');

            $point = -1;
            return $this->updateVotedCount($document_srl, $point);
        }

        /**
         * @brief 게시글이 신고될 경우 호출되는 action
         **/
        function procDocumentDeclare() {
            $document_srl = Context::get('target_srl');
            if(!$document_srl) return new Object(-1, 'msg_invalid_request');

            return $this->declaredDocument($document_srl);
        }

        /**
         * @brief 모듈이 삭제될때 등록된 모든 글을 삭제하는 trigger
         **/
        function triggerDeleteModuleDocuments(&$obj) {
            $module_srl = $obj->module_srl;
            if(!$module_srl) return new Object();

            // document 삭제
            $oDocumentController = &getAdminController('document');
            $output = $oDocumentController->deleteModuleDocument($module_srl);
            if(!$output->toBool()) return $output;

            // category 삭제
            $output = $oDocumentController->deleteModuleCategory($module_srl);
            if(!$output->toBool()) return $output;

            return new Object();
        }

        /**
         * @brief 문서의 권한 부여 
         * 세션값으로 현 접속상태에서만 사용 가능
         **/
        function addGrant($document_srl) {
            $_SESSION['own_document'][$document_srl] = true;
        }

        /**
         * @brief 문서 입력
         **/
        function insertDocument($obj, $manual_inserted = false) {
            // trigger 호출 (before)
            $output = ModuleHandler::triggerCall('document.insertDocument', 'before', $obj);
            if(!$output->toBool()) return $output;

            // begin transaction
            $oDB = &DB::getInstance();
            $oDB->begin();

            // 기본 변수들 정리
            if($obj->is_secret!='Y') $obj->is_secret = 'N';
            if($obj->allow_comment!='Y') $obj->allow_comment = 'N';
            if($obj->lock_comment!='Y') $obj->lock_comment = 'N';
            if($obj->allow_trackback!='Y') $obj->allow_trackback = 'N';
            if($obj->homepage &&  !eregi('^http:\/\/',$obj->homepage)) $obj->homepage = 'http://'.$obj->homepage;
            if($obj->notify_message != "Y") $obj->notify_message = "N";

            // 자동저장용 필드 제거
            unset($obj->_saved_doc_srl);
            unset($obj->_saved_doc_title);
            unset($obj->_saved_doc_content);
            unset($obj->_saved_doc_message);

            // 주어진 문서 번호가 없으면 문서 번호 등록
            if(!$obj->document_srl) $obj->document_srl = getNextSequence();

            // 카테고리가 있나 검사하여 없는 카테고리면 0으로 세팅
            if($obj->category_srl) {
                $oDocumentModel = &getModel('document');
                $category_list = $oDocumentModel->getCategoryList($obj->module_srl);
                if(!$category_list[$obj->category_srl]) $obj->category_srl = 0;
            }

            // 조회수, 등록순서 설정
            if(!$obj->readed_count) $obj->readed_count = 0;
            $obj->update_order = $obj->list_order = $obj->document_srl * -1;

            // 수동입력을 대비해서 비밀번호의 hash상태를 점검, 수동입력이 아니면 무조건 md5 hash
            if($obj->password && !$obj->password_is_hashed) $obj->password = md5($obj->password);


            // 수동 등록이 아니고 로그인 된 회원일 경우 회원의 정보를 입력
            if(Context::get('is_logged')&&!$manual_inserted) {
                $logged_info = Context::get('logged_info');
                $obj->member_srl = $logged_info->member_srl;
                $obj->user_id = $logged_info->user_id;
                $obj->user_name = $logged_info->user_name;
                $obj->nick_name = $logged_info->nick_name;
                $obj->email_address = $logged_info->email_address;
                $obj->homepage = $logged_info->homepage;
            }

            // 내용에서 제로보드XE만의 태그를 삭제
            $obj->content = preg_replace('!<\!--(Before|After)Document\(([0-9]+),([0-9]+)\)-->!is', '', $obj->content);

            // DB에 입력
            $output = executeQuery('document.insertDocument', $obj);
            if(!$output->toBool()) {
                $oDB->rollback();
                return $output;
            }

            // 성공하였을 경우 category_srl이 있으면 카테고리 update
            if($obj->category_srl) $this->updateCategoryCount($obj->category_srl);

            // trigger 호출 (after)
            if($output->toBool()) {
	            $trigger_output = ModuleHandler::triggerCall('document.insertDocument', 'after', $obj);
                if(!$trigger_output->toBool()) {
                    $oDB->rollback();
                    return $trigger_output;
                }
            }

            // commit
            $oDB->commit();

            // return
            $this->addGrant($obj->document_srl);
            $output->add('document_srl',$obj->document_srl);
            $output->add('category_srl',$obj->category_srl);
            return $output;
        }

        /**
         * @brief 문서 수정
         **/
        function updateDocument($source_obj, $obj) {
            // trigger 호출 (before)
            $output = ModuleHandler::triggerCall('document.updateDocument', 'before', $obj);
            if(!$output->toBool()) return $output;

            // begin transaction
            $oDB = &DB::getInstance();
            $oDB->begin();

            // 기본 변수들 정리
            if($obj->is_secret!='Y') $obj->is_secret = 'N';
            if($obj->allow_comment!='Y') $obj->allow_comment = 'N';
            if($obj->lock_comment!='Y') $obj->lock_comment = 'N';
            if($obj->allow_trackback!='Y') $obj->allow_trackback = 'N';
            if($obj->homepage &&  !eregi('^http:\/\/',$obj->homepage)) $obj->homepage = 'http://'.$obj->homepage;
            if($obj->notify_message != "Y") $obj->notify_message = "N";

            // 자동저장용 필드 제거
            unset($obj->_saved_doc_srl);
            unset($obj->_saved_doc_title);
            unset($obj->_saved_doc_content);
            unset($obj->_saved_doc_message);

            // 카테고리가 변경되었으면 검사후 없는 카테고리면 0으로 세팅
            if($source_obj->get('category_srl')!=$obj->category_srl) {
                $oDocumentModel = &getModel('document');
                $category_list = $oDocumentModel->getCategoryList($obj->module_srl);
                if(!$category_list[$obj->category_srl]) $obj->category_srl = 0;
            }

            // 수정 순서를 조절
            $obj->update_order = getNextSequence() * -1;

            // 비밀번호가 있으면 md5 hash
            if($obj->password) $obj->password = md5($obj->password);

            // 원본 작성인과 수정하려는 수정인이 동일할 시에 로그인 회원의 정보를 입력
            if(Context::get('is_logged')) {
                $logged_info = Context::get('logged_info');
                if($source_obj->get('member_srl')==$logged_info->member_srl) {
                    $obj->member_srl = $logged_info->member_srl;
                    $obj->user_name = $logged_info->user_name;
                    $obj->nick_name = $logged_info->nick_name;
                    $obj->email_address = $logged_info->email_address;
                    $obj->homepage = $logged_info->homepage;
                }
            }

            // 로그인한 유저가 작성한 글인데 nick_name이 없을 경우
            if($source_obj->get('member_srl')&& !$obj->nick_name) {
                $obj->member_srl = $source_obj->get('member_srl');
                $obj->user_name = $source_obj->get('user_name');
                $obj->nick_name = $source_obj->get('nick_name');
                $obj->email_address = $source_obj->get('email_address');
                $obj->homepage = $source_obj->get('homepage');
            }

            // 내용에서 제로보드XE만의 태그를 삭제
            $obj->content = preg_replace('!<\!--(Before|After)Document\(([0-9]+),([0-9]+)\)-->!is', '', $obj->content);

            // DB에 입력
            $output = executeQuery('document.updateDocument', $obj);
            if(!$output->toBool()) {
                $oDB->rollback();
                return $output;
            }

            // 성공하였을 경우 category_srl이 있으면 카테고리 update
            if($source_obj->get('category_srl')!=$obj->category_srl) {
                if($source_obj->get('category_srl')) $this->updateCategoryCount($source_obj->get('category_srl'));
                if($obj->category_srl) $this->updateCategoryCount($obj->category_srl);
            }

            // trigger 호출 (after)
            if($output->toBool()) {
                $trigger_output = ModuleHandler::triggerCall('document.updateDocument', 'after', $obj);
                if(!$trigger_output->toBool()) {
                    $oDB->rollback();
                    return $trigger_output;
                }
            }

            // commit
            $oDB->commit();

            $output->add('document_srl',$obj->document_srl);
            return $output;
        }

        /**
         * @brief 문서 삭제
         **/
        function deleteDocument($document_srl, $is_admin = false) {
            // trigger 호출 (before)
            $trigger_obj->document_srl = $document_srl;
            $output = ModuleHandler::triggerCall('document.deleteDocument', 'before', $trigger_obj);
            if(!$output->toBool()) return $output;

            // begin transaction
            $oDB = &DB::getInstance();
            $oDB->begin();

            // document의 model 객체 생성
            $oDocumentModel = &getModel('document');

            // 기존 문서가 있는지 확인
            $oDocument = $oDocumentModel->getDocument($document_srl, $is_admin);
            if(!$oDocument->isExists() || $oDocument->document_srl != $document_srl) return new Object(-1, 'msg_invalid_document');

            // 권한이 있는지 확인
            if(!$oDocument->isGranted()) return new Object(-1, 'msg_not_permitted');

            // 글 삭제
            $args->document_srl = $document_srl;
            $output = executeQuery('document.deleteDocument', $args);
            if(!$output->toBool()) {
                $oDB->rollback();
                return $output;
            }

            // 카테고리가 있으면 카테고리 정보 변경
            if($oDocument->get('category_srl')) $this->updateCategoryCount($oDocument->get('category_srl'));

            // 신고 삭제
            executeQuery('document.deleteDeclared', $args);

            // trigger 호출 (after)
            if($output->toBool()) {
                $trigger_obj = $oDocument->getObjectVars();
                $trigger_output = ModuleHandler::triggerCall('document.deleteDocument', 'after', $trigger_obj);
                if(!$trigger_output->toBool()) {
                    $oDB->rollback();
                    return $trigger_output;
                }
            }

            // commit
            $oDB->commit();

            return $output;
        }

        /**
         * @brief 해당 document의 조회수 증가
         **/
        function updateReadedCount($oDocument) {
            $document_srl = $oDocument->document_srl;
            $member_srl = $oDocument->get('member_srl');
            $logged_info = Context::get('logged_info');

            // session에 정보로 조회수를 증가하였다고 생각하면 패스
            if($_SESSION['readed_document'][$document_srl]) return false;

            // 글의 작성 ip와 현재 접속자의 ip가 동일하면 패스
            if($document->ipaddress == $_SERVER['REMOTE_ADDR']) {
                $_SESSION['readed_document'][$document_srl] = true;
                return false;
            }

            // document의 작성자가 회원일때 조사
            if($member_srl) {

                // 글쓴이와 현재 로그인 사용자의 정보가 일치하면 읽었다고 생각하고 세션 등록후 패스
                if($member_srl && $logged_info->member_srl == $member_srl) {
                    $_SESSION['readed_document'][$document_srl] = true;
                    return false;
                }
            }

            // 로그인 사용자이면 member_srl, 비회원이면 ipaddress로 판단
            if($logged_info->member_srl) {
                $args->member_srl = $logged_info->member_srl;
            } else {
                $args->ipaddress = $_SERVER['REMOTE_ADDR'];
            }
            $args->document_srl = $document_srl;
            $output = executeQuery('document.getDocumentReadedLogInfo', $args);

            // 로그 정보에 조회 로그가 있으면 세션 등록후 패스
            if($output->data->count) return $_SESSION['readed_document'][$document_srl] = true;

            // 조회수 업데이트
            $output = executeQuery('document.updateReadedCount', $args);

            // 로그 남기기
            $output = executeQuery('document.insertDocumentReadedLog', $args);

            // 세션 정보에 남김
            return $_SESSION['readed_document'][$document_srl] = true;
        }

        /**
         * @brief 해당 document의 추천수 증가
         **/
        function updateVotedCount($document_srl, $point = 1) {
            // 세션 정보에 추천 정보가 있으면 중단
            if($_SESSION['voted_document'][$document_srl]) return new Object(-1, 'failed_voted');

            // 문서 원본을 가져옴
            $oDocumentModel = &getModel('document');
            $oDocument = $oDocumentModel->getDocument($document_srl, false, false);

            // 글의 작성 ip와 현재 접속자의 ip가 동일하면 패스
            if($oDocument->get('ipaddress') == $_SERVER['REMOTE_ADDR']) {
                $_SESSION['voted_document'][$document_srl] = true;
                return new Object(-1, 'failed_voted');
            }

            // document의 작성자가 회원일때 조사
            if($oDocument->get('member_srl')) {
                // member model 객체 생성
                $oMemberModel = &getModel('member');
                $member_srl = $oMemberModel->getLoggedMemberSrl();

                // 글쓴이와 현재 로그인 사용자의 정보가 일치하면 읽었다고 생각하고 세션 등록후 패스
                if($member_srl && $member_srl == $oDocument->get('member_srl')) {
                    $_SESSION['voted_document'][$document_srl] = true;
                    return new Object(-1, 'failed_voted');
                }
            }

            // 로그인 사용자이면 member_srl, 비회원이면 ipaddress로 판단
            if($member_srl) {
                $args->member_srl = $member_srl;
            } else {
                $args->ipaddress = $_SERVER['REMOTE_ADDR'];
            }
            $args->document_srl = $document_srl;
            $output = executeQuery('document.getDocumentVotedLogInfo', $args);

            // 로그 정보에 추천 로그가 있으면 세션 등록후 패스
            if($output->data->count) {
                $_SESSION['voted_document'][$document_srl] = true;
                return new Object(-1, 'failed_voted');
            }

            // 추천수 업데이트
            $args->voted_count = $oDocument->get('voted_count') + $point;
            $output = executeQuery('document.updateVotedCount', $args);

            // 로그 남기기
            $output = executeQuery('document.insertDocumentVotedLog', $args);

            // 세션 정보에 남김
            $_SESSION['voted_document'][$document_srl] = true;

            // 결과 리턴
            return new Object(0, 'success_voted');
        }

        /**
         * @brief 게시글 신고
         **/
        function declaredDocument($document_srl) {
            // 세션 정보에 신고 정보가 있으면 중단
            if($_SESSION['declared_document'][$document_srl]) return new Object(-1, 'failed_declared');

            // 이미 신고되었는지 검사
            $args->document_srl = $document_srl;
            $output = executeQuery('document.getDeclaredDocument', $args);
            if(!$output->toBool()) return $output;

            // 문서 원본을 가져옴
            $oDocumentModel = &getModel('document');
            $oDocument = $oDocumentModel->getDocument($document_srl, false, false);

            // 글의 작성 ip와 현재 접속자의 ip가 동일하면 패스
            if($oDocument->get('ipaddress') == $_SERVER['REMOTE_ADDR']) {
                $_SESSION['declared_document'][$document_srl] = true;
                return new Object(-1, 'failed_declared');
            }

            // document의 작성자가 회원일때 조사
            if($oDocument->get('member_srl')) {
                // member model 객체 생성
                $oMemberModel = &getModel('member');
                $member_srl = $oMemberModel->getLoggedMemberSrl();

                // 글쓴이와 현재 로그인 사용자의 정보가 일치하면 읽었다고 생각하고 세션 등록후 패스
                if($member_srl && $member_srl == $oDocument->get('member_srl')) {
                    $_SESSION['declared_document'][$document_srl] = true;
                    return new Object(-1, 'failed_declared');
                }
            }

            // 로그인 사용자이면 member_srl, 비회원이면 ipaddress로 판단
            if($member_srl) {
                $args->member_srl = $member_srl;
            } else {
                $args->ipaddress = $_SERVER['REMOTE_ADDR'];
            }
            $args->document_srl = $document_srl;
            $output = executeQuery('document.getDocumentDeclaredLogInfo', $args);

            // 로그 정보에 신고 로그가 있으면 세션 등록후 패스
            if($output->data->count) {
                $_SESSION['declared_document'][$document_srl] = true;
                return new Object(-1, 'failed_declared');
            }

            // 신고글 추가
            if($output->data->declared_count > 0) $output = executeQuery('document.updateDeclaredDocument', $args);
            else $output = executeQuery('document.insertDeclaredDocument', $args);
            if(!$output->toBool()) return $output;

            // 로그 남기기
            $output = executeQuery('document.insertDocumentDeclaredLog', $args);

            // 세션 정보에 남김
            $_SESSION['declared_document'][$document_srl] = true;

            $this->setMessage('success_declared');
        }

        /**
         * @brief 해당 document의 댓글 수 증가
         **/
        function updateCommentCount($document_srl, $comment_count, $comment_inserted = false) {
            $args->document_srl = $document_srl;
            $args->comment_count = $comment_count;

            if($comment_inserted) $args->update_order = -1*getNextSequence();

            return executeQuery('document.updateCommentCount', $args);
        }

        /**
         * @brief 해당 document의 엮인글 수증가
         **/
        function updateTrackbackCount($document_srl, $trackback_count) {
            $args->document_srl = $document_srl;
            $args->trackback_count = $trackback_count;

            return executeQuery('document.updateTrackbackCount', $args);
        }

        /** 
         * @brief 카테고리에 문서의 숫자를 변경
         **/
        function updateCategoryCount($category_srl, $document_count = 0) {
            // document model 객체 생성
            $oDocumentModel = &getModel('document');
            if(!$document_count) $document_count = $oDocumentModel->getCategoryDocumentCount($category_srl);

            $args->category_srl = $category_srl;
            $args->document_count = $document_count;
            return executeQuery('document.updateCategoryCount', $args);
        }

        /**
         * @brief document의 20개 확장변수를 xml js filter 적용을 위해 직접 적용
         * 모듈정보를 받아서 20개의 확장변수를 체크하여 type, required등의 값을 체크하여 header에 javascript 코드 추가
         **/
        function addXmlJsFilter($module_info) {
            $extra_vars = $module_info->extra_vars;
            if(!$extra_vars) return;

            $js_code = "";

            foreach($extra_vars as $key => $val) {
                $js_code .= sprintf('alertMsg["extra_vars%d"] = "%s";', $key, $val->name);
                $js_code .= sprintf('extra_vars[extra_vars.length] = "extra_vars%d";', $key);
                $js_code .= sprintf('target_type_list["extra_vars%d"] = "%s";', $key, $val->type);
                if($val->is_required == 'Y') $js_code .= sprintf('notnull_list[notnull_list.length] = "extra_vars%s";',$key);
            }

            $js_code = "<script type=\"text/javascript\">//<![CDATA[\n".$js_code."\n//]]></script>";
            Context::addHtmlHeader($js_code);
        }
    }
?>
