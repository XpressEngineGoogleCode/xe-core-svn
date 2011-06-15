<?php
    /**
     * @class  pageAdminController
     * @author NHN (developers@xpressengine.com)
     * @brief page of the module admin controller class
     **/

    class pageAdminController extends page {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief Add a Page
         **/
        function procPageAdminInsert() {
            // Create model/controller object of the module module
            $oModuleController = &getController('module');
            $oModuleModel = &getModel('module');
            // Set board module
            $args = Context::getRequestVars();
            $args->module = 'page';
            $args->mid = $args->page_name;
            unset($args->page_name);

			if($args->use_mobile != 'Y') $args->use_mobile = '';
            // Check if an original module exists by using module_srl
            if($args->module_srl) {
				$columnList = array('module_srl');
                $module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl, $columnList);
                if($module_info->module_srl != $args->module_srl) {
					unset($args->module_srl);
				}
				else
				{
					foreach($args as $key=>$val)
					{
						$module_info->{$key} = $val;
					}
					$args = $module_info;
				}
            }

			switch ($args->page_type){
				case 'WIDGET' : {
									unset($args->skin);
									unset($args->mskin);
									unset($args->opage_path);
									unset($args->opage_mpath);
									break;
								}
				case 'ARTICLE' : {
									unset($args->page_caching_interval);
									unset($args->opage_path);
									unset($args->opage_mpath);
									break;
								}
				case 'OUTSIDE' : {
									unset($args->skin);
									unset($args->mskin);
									break;
								}
			}
            // Insert/update depending on module_srl
            if(!$args->module_srl) {
                $output = $oModuleController->insertModule($args);
                $msg_code = 'success_registed';
            } else {
                $output = $oModuleController->updateModule($args);
                $msg_code = 'success_updated';
            }

            if(!$output->toBool()) return $output;

            $this->add("page", Context::get('page'));
            $this->add('module_srl',$output->get('module_srl'));
            $this->setMessage($msg_code);
        }

		function putDocumentsInPageToArray($target, &$array)
		{
			if(!$target) return;
			preg_match_all('!<img src="./common/tpl/images/widget_bg.jpg" ([^>]+)!is', $target, $matches);
			$pattern = '!document_srl="(\d+)"!';
			foreach($matches[1] as $match)
			{
				$match2 = null;
				preg_match($pattern, $match, $match2);
				if(count($match2))
				{
					$array[(int)$match2[1]] = 1;
				}
			}
		}

        /**
         * @brief Save page edits
         **/
        function procPageAdminInsertContent() {
            $module_srl = Context::get('module_srl');
            $content = Context::get('content');
            if(!$module_srl) return new Object(-1,'msg_invalid_request');
			$mcontent = Context::get('mcontent');
			$type = Context::get('type');
            // Guhaeom won information page
            $oModuleModel = &getModel('module');
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
			if($type == "mobile") {
                if(!$mcontent) $mcontent = '';
				$module_info->mcontent = $mcontent;
			}
			else {
				if(!isset($content)) $content ='';
				$module_info->content = $content;
			}

			$document_srls = array();
			$this->putDocumentsInPageToArray($module_info->content, $document_srls);
			$this->putDocumentsInPageToArray($module_info->mcontent, $document_srls);

            $oDocumentModel = &getModel('document');
            $oDocumentController = &getController('document');
            $obj->module_srl = $module_srl;
            $obj->list_count = 99999999;
            $output = $oDocumentModel->getDocumentList($obj);
			if(count($output->data)) {
				foreach($output->data as $document)
				{
					if($document_srls[$document->document_srl]) continue;
					$oDocumentController->deleteDocument($document->document_srl, true);
				}
			}
            // Creates an object of the controller module module
            $oModuleController = &getController('module');
            // Save
            $output = $oModuleController->updateModule($module_info);
            if(!$output->toBool()) return $output;
            // On the page, change the validity status of the attached file
            $oFileController = &getController('file');
            $oFileController->setFilesValid($module_info->module_srl);
            // Create cache file
            //$this->procPageAdminRemoveWidgetCache();

            $this->add("module_srl", $module_info->module_srl);
            $this->add("page", Context::get('page'));
            $this->add("mid", $module_info->mid);
            $this->setMessage($msg_code);
        }

        /**
         * @brief Delete page
         **/
        function procPageAdminDelete() {
            $module_srl = Context::get('module_srl');
            // Get an original
            $oModuleController = &getController('module');
            $output = $oModuleController->deleteModule($module_srl);
            if(!$output->toBool()) return $output;

            $this->add('module','page');
            $this->add('page',Context::get('page'));
            $this->setMessage('success_deleted');
        }

        /**
         * @brief Additional pages of basic information
         **/
        function procPageAdminInsertConfig() {
            // Get the basic information
            $args = Context::getRequestVars();
            // Insert by creating the module Controller object
            $oModuleController = &getController('module');
            $output = $oModuleController->insertModuleConfig('page',$args);
            return $output;
        }

        /**
         * @brief Upload attachments
         **/
        function procUploadFile() {
            // Basic variables setting
            $upload_target_srl = Context::get('upload_target_srl');
            $module_srl = Context::get('module_srl');
            // Create the controller object file class
            $oFileController = &getController('file');
            $output = $oFileController->insertFile($module_srl, $upload_target_srl);
            // Attachment to the output of the list, java script
            $oFileController->printUploadedFileList($upload_target_srl);
        }

        /**
         * @brief Delete the attachment
         * Delete individual files in the editor using
         **/
        function procDeleteFile() {
            // Basic variable setting(upload_target_srl and module_srl set)
            $upload_target_srl = Context::get('upload_target_srl');
            $module_srl = Context::get('module_srl');
            $file_srl = Context::get('file_srl');
            // Create the controller object file class
            $oFileController = &getController('file');
            if($file_srl) $output = $oFileController->deleteFile($file_srl, $this->grant->manager);
            // Attachment to the output of the list, java script
            $oFileController->printUploadedFileList($upload_target_srl);
        }

        /**
         * @brief Clear widget cache files of the specified page
         **/
        function procPageAdminRemoveWidgetCache() {
            $module_srl = Context::get('module_srl');

            $oModuleModel = &getModel('module');
			$columnList = array('module_srl', 'content');
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl, $columnList);

            $content = $module_info->content;

            $cache_file = sprintf("%sfiles/cache/page/%d.%s.cache.php", _XE_PATH_, $module_info->module_srl, Context::getLangType());
            if(file_exists($cache_file)) FileHandler::removeFile($cache_file);
            // widget controller re-run of the cache files
            $oWidgetController = &getController('widget');
            $oWidgetController->recompileWidget($content);
        }

		function procPageAdminArticleDocumentInsert(){
            $logged_info = Context::get('logged_info');
			if ($logged_info->is_admin != 'Y')return new Object(-1, 'msg_not_permitted');

            $obj = Context::getRequestVars();
            $obj->module_srl = $this->module_info->module_srl;
            $obj->is_notice = 'N';

            settype($obj->title, "string");
            if($obj->title == '') $obj->title = cut_str(strip_tags($obj->content),20,'...');
            //그래도 없으면 Untitled
            if($obj->title == '') $obj->title = 'Untitled';

            // document module의 model 객체 생성
            $oDocumentModel = &getModel('document');

            // document module의 controller 객체 생성
            $oDocumentController = &getController('document');

            // 이미 존재하는 글인지 체크
            $oDocument = $oDocumentModel->getDocument($obj->document_srl, true);

			$bAnonymous = false;

            // 이미 존재하는 경우 수정
            if($oDocument->isExists() && $oDocument->document_srl == $obj->document_srl) {
                $output = $oDocumentController->updateDocument($oDocument, $obj);
                $msg_code = 'success_updated';
            // 그렇지 않으면 신규 등록
            } else {
                $output = $oDocumentController->insertDocument($obj, $bAnonymous);
                $msg_code = 'success_registed';
                $obj->document_srl = $output->get('document_srl');

				$oModuleController = &getController('module');
				$this->module_info->document_srl = $obj->document_srl;
				$oModuleController->updateModule($this->module_info);

            }

            // 오류 발생시 멈춤
            if(!$output->toBool()) return $output;

            // 결과를 리턴
            $this->add('mid', Context::get('mid'));
            $this->add('document_srl', $output->get('document_srl'));

            // 성공 메세지 등록
            $this->setMessage($msg_code);
		}


    }
?>
