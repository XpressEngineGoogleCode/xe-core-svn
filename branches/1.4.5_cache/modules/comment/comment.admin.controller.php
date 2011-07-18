<?php
    /**
     * @class  commentAdminController
     * @author NHN (developers@xpressengine.com)
     * @brief  comment 모듈의 admin controller class
     **/

    class commentAdminController extends comment {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 관리자 페이지에서 선택된 댓글들을 삭제
         **/
        function procCommentAdminDeleteChecked() {
			$isTrash = Context::get('is_trash');

            // Error display if none is selected
            $cart = Context::get('cart');
            if(!$cart) return $this->stop('msg_cart_is_null');
            $comment_srl_list= explode('|@|', $cart);
            $comment_count = count($comment_srl_list);
            if(!$comment_count) return $this->stop('msg_cart_is_null');

			$oCommentController = &getController('comment');
			// begin transaction
			$oDB = &DB::getInstance();
			$oDB->begin();

			// comment into trash
			if($isTrash == 'true') $this->_moveCommentToTrash($comment_srl_list, &$oCommentController, &$oDB);

			$deleted_count = 0;
			// Delete the comment posting
			for($i=0;$i<$comment_count;$i++) {
				$comment_srl = trim($comment_srl_list[$i]);
				if(!$comment_srl) continue;

				$output = $oCommentController->deleteComment($comment_srl, true, $isTrash);
				if(!$output->toBool())
				{
					$oDB->rollback();
					return $output;
				}

				$deleted_count ++;
			}
			$oDB->commit();
        	//remove from cache
            $oCacheHandler = &CacheHandler::getInstance('object');
            if($oCacheHandler->isSupport()) 
            {
            	$cache_object = $oCacheHandler->get('comment_list_document_pages');
            	foreach ($cache_object as $object){
            		$cache_key = $object;
                	$oCacheHandler->delete($cache_key);
            	}
                $oCacheHandler->delete('comment_list_document_pages');
            }
            $this->setMessage( sprintf(Context::getLang('msg_checked_comment_is_deleted'), $deleted_count) );
        }

        /**
         * @brief 신고대상을 취소 시킴
         **/
        function procCommentAdminCancelDeclare() {
            $comment_srl = trim(Context::get('comment_srl'));

            if($comment_srl) {
                $args->comment_srl = $comment_srl;
                $output = executeQuery('comment.deleteDeclaredComments', $args);
                if(!$output->toBool()) return $output;
            }
        }

        /**
         * @brief 특정 모듈의 모든 댓글 삭제
         **/
        function deleteModuleComments($module_srl) {
            $args->module_srl = $module_srl;
            $output = executeQuery('comment.deleteModuleComments', $args);
            if(!$output->toBool()) return $output;

            $output = executeQuery('comment.deleteModuleCommentsList', $args);
        	//remove from cache
            $oCacheHandler = &CacheHandler::getInstance('object');
            if($oCacheHandler->isSupport()) 
            {
            	$cache_object = $oCacheHandler->get('comment_list_document_pages');
            	foreach ($cache_object as $object){
            		$cache_key = $object;
                	$oCacheHandler->delete($cache_key);
            	}
                $oCacheHandler->delete('comment_list_document_pages');
            }
            return $output;
        }

    }
?>
