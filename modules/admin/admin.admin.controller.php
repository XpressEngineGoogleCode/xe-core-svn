<?php

/**
* @class  adminAdminController
* @author NHN (developers@xpressengine.com)
* @brief  admin controller class of admin module
**/

class adminAdminController extends admin {
	var $icon_config = array("width"=>32,"height"=>32);

	/**
	* @brief initialization
	* @return none
	**/
	function init() 
	{
		// forbit access if the user is not an administrator
		$oMemberModel = getModel('member');
		$logged_info = $oMemberModel->getLoggedInfo();
		if($logged_info->is_admin!='Y') return $this->stop("msg_is_not_administrator");
        }

	function procAdminMenuReset()
	{
		$menuSrl = Context::get('menu_srl');
		if (!$menuSrl) return $this->stop('msg_invalid_request');

		$oMenuAdminController = getAdminController('menu');
		$output = $oMenuAdminController->deleteMenu($menuSrl);
		if (!$output->toBool()) return $output;

		FileHandler::removeDir('./files/cache/menu/admin_lang/');

		$this->setRedirectUrl(Context::get('error_return_url'));
	}

        /**
         * @brief Regenerate all cache files
         * @return none
         **/
        function procAdminRecompileCacheFile() 
	{
		// rename cache dir
		$temp_cache_dir = './files/cache_'. time();
		FileHandler::rename('./files/cache', $temp_cache_dir);
		FileHandler::makeDir('./files/cache');

		// remove debug files
		FileHandler::removeFile(_XE_PATH_.'files/_debug_message.php');
		FileHandler::removeFile(_XE_PATH_.'files/_debug_db_query.php');
		FileHandler::removeFile(_XE_PATH_.'files/_db_slow_query.php');

		$oModuleModel = getModel('module');
		$module_list = $oModuleModel->getModuleList();

		// call recompileCache for each module
		foreach($module_list as $module) {
			$oModule = null;
			$oModule = &getClass($module->module);
			if(method_exists($oModule, 'recompileCache')) $oModule->recompileCache();
		}

		// remove cache
		$truncated = array();
		$oObjectCacheHandler = &CacheHandler::getInstance('object');
		$oTemplateCacheHandler = &CacheHandler::getInstance('template');

		if($oObjectCacheHandler->isSupport()){
			$truncated[] = $oObjectCacheHandler->truncate();
		}

		if($oTemplateCacheHandler->isSupport()){
			$truncated[] = $oTemplateCacheHandler->truncate();
		}

		if(count($truncated) && in_array(false,$truncated)){
			return new Object(-1,'msg_self_restart_cache_engine');
		}


		// remove cache dir
		$tmp_cache_list = FileHandler::readDir('./files','/(^cache_[0-9]+)/');
		if($tmp_cache_list){
			foreach($tmp_cache_list as $tmp_dir){
				if($tmp_dir) FileHandler::removeDir('./files/'.$tmp_dir);
			}
		}

		$this->setMessage('success_updated');
        }

        /**
	* @brief Logout
	* @return none
	**/
	function procAdminLogout() 
	{
		$oMemberController = getController('member');
		$oMemberController->procMemberLogout();

		header('Location: '.getNotEncodedUrl('', 'module','admin'));
	}

	function procAdminInsertThemeInfo()
	{
		$vars = Context::getRequestVars();
		$theme_file = _XE_PATH_.'files/theme/theme_info.php';

		$theme_output = sprintf('$theme_info->theme=\'%s\';', $vars->themeItem);
		$theme_output = $theme_output.sprintf('$theme_info->layout=%s;', $vars->layout);

		$site_info = Context::get('site_module_info');

		$args->site_srl = $site_info->site_srl;
		$args->layout_srl = $vars->layout;
		// layout submit
		$output = executeQuery('layout.updateAllLayoutInSiteWithTheme', $args);
		if (!$output->toBool()) return $output;

		$skin_args->site_srl = $site_info->site_srl;

		foreach($vars as $key=>$val){
			$pos = strpos($key, '-skin');
			if ($pos === false) continue;
			if ($val != '__skin_none__')
			{
				$module = substr($key, 0, $pos);
				$theme_output = $theme_output.sprintf('$theme_info->skin_info[%s]=\'%s\';', $module, $val);
				$skin_args->skin = $val;
				$skin_args->module = $module;
				if ($module == 'page')
				{
					$article_output = executeQueryArray('page.getArticlePageSrls');
					if (count($article_output->data)>0){
						$article_module_srls = array();
						foreach($article_output->data as $val){
							$article_module_srls[] = $val->module_srl;
						}
						$skin_args->module_srls = implode(',', $article_module_srls);
					}
				}
				$skin_output = executeQuery('module.updateAllModuleSkinInSiteWithTheme', $skin_args);
				if (!$skin_output->toBool()) return $skin_output;

				$oModuleModel = getModel('module');
				$module_config = $oModuleModel->getModuleConfig($module, $site_info->site_srl);
				$module_config->skin = $val;
				$oModuleController = getController('module');
				$oModuleController->insertModuleConfig($module, $module_config, $site_info->site_srl);
			}
		}

		$theme_buff = sprintf(
			'<?php '.
			'if(!defined("__ZBXE__")) exit(); '.
			'%s'.
			'?>',
			$theme_output
		);
		// Save File
		FileHandler::writeFile($theme_file, $theme_buff);

		if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON')))
		{
			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAdminTheme');
			header('location:'.$returnUrl);
			return;
		}
		else return $output;
	}

	/**
	* @brief Toggle favorite
	**/
	function procAdminToggleFavorite()
	{
		$siteSrl = Context::get('site_srl');
		$moduleName = Context::get('module_name');

		// check favorite exists
		$oModel = getAdminModel('admin');
		$output = $oModel->isExistsFavorite($siteSrl, $moduleName);
		if (!$output->toBool()) return $output;

		// if exists, delete favorite
		if ($output->get('result'))
		{
			$favoriteSrl = $output->get('favoriteSrl');
			$output = $this->_deleteFavorite($favoriteSrl);
			$result = 'off';
		}
		// if not exists, insert favorite
		else
		{
			$output = $this->_insertFavorite($siteSrl, $moduleName);
			$result = 'on';
		}

		if (!$output->toBool()) return $output;

		$this->add('result', $result);
		$this->setRedirectUrl(Context::get('error_return_url'));
	}

	/**
	* @brief enviroment gathering agreement
	**/
	function procAdminEnviromentGatheringAgreement()
	{
		$isAgree = Context::get('is_agree');
		if($isAgree == 'true') $_SESSION['enviroment_gather'] = 'Y';
		else $_SESSION['enviroment_gather'] = 'N';

		$redirectUrl = getUrl('', 'module', 'admin');
		$this->setRedirectUrl($redirectUrl);
	}

	/**
		* @brief admin config update
		**/
	function procAdminUpdateConfig()
	{
		$adminTitle = Context::get('adminTitle');
		$file = $_FILES['adminLogo'];

		$oModuleModel = getModel('module');
		$oAdminConfig = $oModuleModel->getModuleConfig('admin');

		if($file['tmp_name'])
		{
			$target_path = 'files/attach/images/admin/';
			FileHandler::makeDir($target_path);

			// Get file information
			list($width, $height, $type, $attrs) = @getimagesize($file['tmp_name']);
			if($type == 3) $ext = 'png';
			elseif($type == 2) $ext = 'jpg';
			else $ext = 'gif';

			$target_filename = sprintf('%s%s.%s.%s', $target_path, 'adminLogo', date('YmdHis'), $ext);
			@move_uploaded_file($file['tmp_name'], $target_filename);

			$oAdminConfig->adminLogo = $target_filename;
		}
		if($adminTitle) $oAdminConfig->adminTitle = strip_tags($adminTitle);
		else unset($oAdminConfig->adminTitle);

		if($oAdminConfig)
		{
			$oModuleController = getController('module');
			$oModuleController->insertModuleConfig('admin', $oAdminConfig);
		}

		$this->setMessage('success_updated', 'info');
		if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAdminSetup');
			$this->setRedirectUrl($returnUrl);
			return;
		}
	}

	/**
	* @brief admin logo delete
	**/
	function procAdminDeleteLogo()
	{
		$oModuleModel = getModel('module');
		$oAdminConfig = $oModuleModel->getModuleConfig('admin');

		FileHandler::removeFile(_XE_PATH_.$oAdminConfig->adminLogo);
		unset($oAdminConfig->adminLogo);

		$oModuleController = getController('module');
		$oModuleController->insertModuleConfig('admin', $oAdminConfig);

		$this->setMessage('success_deleted', 'info');
		if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAdminSetup');
			$this->setRedirectUrl($returnUrl);
			return;
		}
	}

	/**
	* @brief Insert favorite
	**/
	function _insertFavorite($siteSrl, $module, $type = 'module')
	{
		$args->adminFavoriteSrl = getNextSequence();
		$args->site_srl = $siteSrl;
		$args->module = $module;
		$args->type = $type;
		$output = executeQuery('admin.insertFavorite', $args);
		return $output;
	}

	/**
	* @brief Delete favorite
	**/
	function _deleteFavorite($favoriteSrl)
	{
		$args->admin_favorite_srl = $favoriteSrl;
		$output = executeQuery('admin.deleteFavorite', $args);
		return $output;
	}

	/**
	* @brief Delete favorite
	**/
	function _deleteAllFavorite()
	{
		$args = null;
		$output = executeQuery('admin.deleteAllFavorite', $args);
		return $output;
	}
		
	/**
	* @brief Save shortcuts for Admin Dashboard
	**/
	function procAdminSaveShortcuts()
	{
		$shortcut_srls = Context::get("shortcutId");
		$icons = $_FILES["shortcutIcon"];
		$links = Context::get("link");
		$names = Context::get("name");
		$order = Context::get("order");
		$path = 'files/icons/shortcuts/';
		$args->member_srl = Context::get("logged_info")->member_srl;
		foreach($shortcut_srls as $shortcut_srl=>$value)
		{
			if (trim($names[$shortcut_srl]) != "" && trim($links[$shortcut_srl]) != "")
			{
				$args->link = $links[$shortcut_srl];
				$args->display_name = $names[$shortcut_srl];
				$args->order_number = $order[$shortcut_srl];
				if ($value == "-1")
				{
					$args->shortcut_srl = getNextSequence();

					$output = executeQuery('admin.insertShortcut', $args);
					if ($icons["error"][$shortcut_srl]==0)
					{
						$file = $icons['tmp_name'][$shortcut_srl];
						if(!is_uploaded_file($file)) return $this->stop('msg_not_uploaded_shortcut_icon_image');
						// Get a target path to save
						if (!file_exists($path))
						{
							FileHandler::makeDir($path);
						}
						// Get file information
						list($width, $height, $type, $attrs) = @getimagesize($file);
						if($type == 3) $ext = 'png';
						elseif($type == 2) $ext = 'jpg';
						else $ext = 'gif';

						$target_filename = sprintf('%s%d.%s', $path, $args->shortcut_srl, $ext);
						// Convert if the image size is larger than a given size or if the format is not a gif
						if($type!=1)
						{
							//FileHandler::createImageFile($file, $target_filename, $max_width, $max_height, $ext);
							@move_uploaded_file($file, $target_filename);
						}
						else
						{
							@copy($file, $target_filename);
						}
					}
				}
				else
				{
					$args->shortcut_srl = $value;
					$output = executeQuery('admin.updateShortcut', $args);
					if ($icons["error"][$shortcut_srl]==0)
					{
						$file = $icons['tmp_name'][$shortcut_srl];
						if(!is_uploaded_file($file)) return $this->stop('msg_not_uploaded_shortcut_icon_image');
						// Get a target path to save
						if (!file_exists($path))
						{
							FileHandler::makeDir($path);
						}
						// Get file information
						list($width, $height, $type, $attrs) = @getimagesize($file);
						if($type == 3) $ext = 'png';
						elseif($type == 2) $ext = 'jpg';
						else $ext = 'gif';

						$target_filename = sprintf('%s%d.%s', $path, $shortcut_srl, $ext);
						$allowed_ext = array(".png",".jpg",".gif");
						foreach($allowed_ext as $extension)
						{
							if(file_exists(_XE_PATH_.$path. $shortcut_srl. $extension))
							{
								@FileHandler::removeFile(_XE_PATH_.$path. $shortcut_srl. $extension);
							}
						}
						// Convert if the image size is larger than a given size or if the format is not a gif
						if($type!=1)
						{
							@move_uploaded_file($file, $target_filename);
						}
						else
						{
							@copy($file, $target_filename);
						}
					}
				}
			}
		}
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAdminDashboardShortcuts');
		$this->setRedirectUrl($returnUrl);
	}

	/**
	* @brief Delete shortcut from Admin Dashboard 
	*/
	function procAdminDeleteShortcut(){
		$shortcut_srl = Context::get('idWillBeDelete');
		$path =  'files/icons/shortcuts/';
		$allowed_ext = array(".png",".jpg",".gif");
		foreach($allowed_ext as $extension)
		{
			if(file_exists(_XE_PATH_.$path, $shortcut_srl, $extension))
			{
				@FileHandler::removeFile(_XE_PATH_.$path, $shortcut_srl, $extension);
			}
		}
		$args->shortcut_srl = $shortcut_srl;
		@executeQuery('admin.deleteShortcut', $args);
		$this->setMessage('success_deleted');
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAdminDashboardShortcuts');
		$this->setRedirectUrl($returnUrl);
	}

	function procAdminRemoveIcons(){
		$iconname = Context::get('iconname');
		$file_exist = FileHandler::readFile(_XE_PATH_.'files/attach/xeicon/'.$iconname);
		if($file_exist) {
			@FileHandler::removeFile(_XE_PATH_.'files/attach/xeicon/'.$iconname);
		} else {
			return new Object(-1,'fail_to_delete');
		}
		$this->setMessage('success_deleted');
	}
	
	/**
	 * @brief Save Google Analytics Informations 
	 */
	function procAdminSaveGAInfo()
	{
		$oModuleModel = getModel('module');
		$module_info = $oModuleModel->getModuleConfig($this->module);
		$analytics_type = Context::get("analytics_type");
		if($analytics_type == 'ga')
		{
			$module_info->client_id = Context::get("ga_client_id");
			$module_info->client_secret = Context::get("ga_client_secret");
			$module_info->developer_key = Context::get("ga_developer_key");
			$module_info->redirect_uri = Context::get("ga_redirect_uri");
		}
		if($analytics_type == 'xe')
		{
			if(isset($module_info->analytics_type) && $module_info->analytics_type == 'ga')
			{
				unset($module_info->client_id);
				unset($module_info->client_secret);
				unset($module_info->developer_key);
				unset($module_info->redirect_uri);
				unset($module_info->auth_token);
				unset($module_info->access_token);
			}
		}
		$module_info->analytics_type = $analytics_type;
		$oModuleController = &getController("module");
		$output = $oModuleController->insertModuleConfig($this->module,$module_info);
		if($output->toBool())
		{
			$this->setMessage("success_updated");
		}
		else
		{
			$this->setMessage($output->message);
		}
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAdminDashboardGA');
		$this->setRedirectUrl($returnUrl);
	}
		
	function procAdminSaveGAAuthToken()
	{
		$oModuleModel = getModel('module');
		$module_info = $oModuleModel->getModuleConfig($this->module);
		
		$oAdminModel = &getAdminModel("admin");
		$oAdminModel->setGAAccount();
		if (isset($_GET["code"])) 
		{
			$oAdminModel->client->authenticate();
			$my_token =$oAdminModel->client->getAccessToken();
			$module_info->auth_token = $my_token;
		}
		else
		{
			$authUrl = $oAdminModel->client->createAuthUrl();
			header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
			exit;
		}
		$oModuleController = &getController("module");
		$output = $oModuleController->insertModuleConfig($this->module,$module_info);
		if($output->toBool())
		{
			$this->setMessage("success_updated");
		}
		else
		{
			$this->setMessage($output->message);
		}
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAdminDashboardGA');
		$this->setRedirectUrl($returnUrl);
	}
		
    }
?>
