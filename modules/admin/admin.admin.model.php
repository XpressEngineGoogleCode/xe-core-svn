<?php
require_once _XE_PATH_ . "libs/google_api/apiClient.php";
require_once _XE_PATH_ . "libs/google_api/contrib/apiAnalyticsService.php";	

class adminAdminModel extends admin
{
	var $pwd;
	var $gnbLangBuffer;
	var $client;
	var $service;

	function getSFTPList()
	{
		$ftp_info =  Context::getRequestVars();
		if(!$ftp_info->ftp_host)
		{
			$ftp_info->ftp_host = "127.0.0.1";
		}
		$connection = ssh2_connect($ftp_info->ftp_host, $ftp_info->ftp_port);
		if(!ssh2_auth_password($connection, $ftp_info->ftp_user, $ftp_info->ftp_password))
		{
			return new Object(-1,'msg_ftp_invalid_auth_info');
		}

		$sftp = ssh2_sftp($connection);
		$curpwd = "ssh2.sftp://$sftp".$this->pwd;
		$dh = @opendir($curpwd);
		if(!$dh) return new Object(-1, 'msg_ftp_invalid_path');
		$list = array();
		while(($file = readdir($dh)) !== false)
		{
			if(is_dir($curpwd.$file))
			{
				$file .= "/";
			}
			else
			{
				continue;
			}
			$list[] = $file;
		}
		closedir($dh);
		$this->add('list', $list);
	}

	function getAdminFTPList()
	{
		set_time_limit(5);
		require_once(_XE_PATH_.'libs/ftp.class.php');
		$ftp_info =  Context::getRequestVars();
		if(!$ftp_info->ftp_user || !$ftp_info->ftp_password)
		{
			return new Object(-1, 'msg_ftp_invalid_auth_info');
		}

		$this->pwd = $ftp_info->ftp_root_path;

		if(!$ftp_info->ftp_host)
		{
			$ftp_info->ftp_host = "127.0.0.1";
		}

		if (!$ftp_info->ftp_port || !is_numeric ($ftp_info->ftp_port)) {
			$ftp_info->ftp_port = "21";
		}

		if($ftp_info->sftp == 'Y')
		{
			if(!function_exists(ssh2_sftp))
			{
				return new Object(-1,'disable_sftp_support');
			}
			return $this->getSFTPList();
		}

		$oFtp = new ftp();
		if($oFtp->ftp_connect($ftp_info->ftp_host, $ftp_info->ftp_port)){
			if($oFtp->ftp_login($ftp_info->ftp_user, $ftp_info->ftp_password)) {
				$_list = $oFtp->ftp_rawlist($this->pwd);
				$oFtp->ftp_quit();
			}
			else
			{
				return new Object(-1,'msg_ftp_invalid_auth_info');
			}
		}
		$list = array();

		if($_list){
			foreach($_list as $k => $v){
				$src = null;
				$src->data = $v;
				$res = Context::convertEncoding($src);
				$v = $res->data;
				if(strpos($v,'d') === 0 || strpos($v, '<DIR>')) $list[] = substr(strrchr($v,' '),1) . '/';
			}
		}
		$this->add('list', $list);
	}

	function getEnv($type='WORKING') {

			$skip = array(
					'ext' => array('pcre','json','hash','dom','session','spl','standard','date','ctype','tokenizer','apache2handler','filter','posix','reflection','pdo')
					,'module' => array('addon','admin','autoinstall', 'comment', 'communication', 'counter', 'document', 'editor', 'file', 'importer', 'install', 'integration_search', 'layout', 'member', 'menu', 'message', 'module', 'opage', 'page', 'point', 'poll', 'rss', 'session', 'spamfilter', 'tag',  'trackback', 'trash', 'widget')
					,'addon' => array('autolink', 'blogapi', 'captcha', 'counter', 'member_communication', 'member_extra_info', 'mobile', 'openid_delegation_id', 'point_level_icon', 'resize_image' )
				);

		$info = array();
		$info['type'] = ($type !='INSTALL' ? 'WORKING' : 'INSTALL');
		$info['location'] = _XE_LOCATION_;
		$info['package'] = _XE_PACKAGE_;
		$info['host'] = $db_type->default_url ? $db_type->default_url : getFullUrl();
		$info['app'] = $_SERVER['SERVER_SOFTWARE'];
		$info['xe_version'] = __ZBXE_VERSION__;
		$info['php'] = phpversion();

		$db_info = Context::getDBInfo();
		$info['db_type'] = Context::getDBType();
		$info['use_rewrite'] = $db_info->use_rewrite;
		$info['use_db_session'] = $db_info->use_db_session == 'Y' ?'Y':'N';
		$info['use_ssl'] = $db_info->use_ssl;

		$info['phpext'] = '';
		foreach (get_loaded_extensions() as $ext) {
			$ext = strtolower($ext);
			if(in_array($ext, $skip['ext'])) continue;
			$info['phpext'] .= '|'. $ext;
		}
		$info['phpext'] = substr($info['phpext'],1);

		$info['module'] = '';
		$oModuleModel = getModel('module');
		$module_list = $oModuleModel->getModuleList();
		foreach($module_list as $module){
			if(in_array($module->module, $skip['module'])) continue;
			$info['module']  .= '|'.$module->module;
		}
		$info['module'] = substr($info['module'],1);

		$info['addon'] = '';
		$oAddonAdminModel = getAdminModel('addon');
		$addon_list = $oAddonAdminModel->getAddonList();
		foreach($addon_list as $addon){
			if(in_array($addon->addon, $skip['addon'])) continue;
			$info['addon'] .= '|'.$addon->addon;
		}
		$info['addon'] = substr($info['addon'],1);

		$param = '';
		foreach($info as $k => $v){
			if($v) $param .= sprintf('&%s=%s',$k,urlencode($v));
		}
		$param = substr($param, 1);

		return $param;
	}

	function getThemeList(){
		$path = _XE_PATH_.'themes';
		$list = FileHandler::readDir($path);

		$theme_info = array();
		if(count($list) > 0){
			foreach($list as $val){
				$theme_info[$val] = $this->getThemeInfo($val);
			}
		}

		return $theme_info;
	}

	function getThemeInfo($theme_name, $layout_list = null){
		if ($GLOBALS['__ThemeInfo__'][$theme_name]) return $GLOBALS['__ThemeInfo__'][$theme_name];

		$info_file = _XE_PATH_.'themes/'.$theme_name.'/conf/info.xml';
		if(!file_exists($info_file)) return;

		$oXmlParser = new XmlParser();
		$_xml_obj = $oXmlParser->loadXmlFile($info_file);

		if(!$_xml_obj->theme) return;
		$xml_obj = $_xml_obj->theme;
		if(!$_xml_obj->theme) return;

		// 스킨이름
		$theme_info->name = $theme_name;
		$theme_info->title = $xml_obj->title->body;
		$thumbnail = './themes/'.$theme_name.'/thumbnail.png';
		$theme_info->thumbnail = (file_exists($thumbnail))?$thumbnail:null;
		$theme_info->version = $xml_obj->version->body;
		sscanf($xml_obj->date->body, '%d-%d-%d', $date_obj->y, $date_obj->m, $date_obj->d);
		$theme_info->date = sprintf('%04d%02d%02d', $date_obj->y, $date_obj->m, $date_obj->d);
		$theme_info->description = $xml_obj->description->body;
		$theme_info->path = './themes/'.$theme_name.'/';

		if(!is_array($xml_obj->publisher)) $publisher_list[] = $xml_obj->publisher;
		else $publisher_list = $xml_obj->publisher;

		foreach($publisher_list as $publisher) {
			unset($publisher_obj);
			$publisher_obj->name = $publisher->name->body;
			$publisher_obj->email_address = $publisher->attrs->email_address;
			$publisher_obj->homepage = $publisher->attrs->link;
			$theme_info->publisher[] = $publisher_obj;
		}

		$layout = $xml_obj->layout;
		$layout_path = $layout->directory->attrs->path;
		$layout_parse = explode('/',$layout_path);
		switch($layout_parse[1]){
			case 'themes' : {
								$layout_info->name = $theme_name.'.'.$layout_parse[count($layout_parse)-1];
								break;
							}
			case 'layouts' : {
								$layout_info->name = $layout_parse[count($layout_parse)-1];
									break;
							}
		}
		$layout_info->path = $layout_path;

		$site_info = Context::get('site_module_info');
		// check layout instance
		$is_new_layout = true;
		$oLayoutModel = getModel('layout');
		$layout_info_list = array();
		$layout_list = $oLayoutModel->getLayoutList($site_info->site_srl);
		if ($layout_list){
			foreach($layout_list as $val){
				if ($val->layout == $layout_info->name){
					$is_new_layout = false;
					$layout_info->layout_srl = $val->layout_srl;
					break;
				}
			}
		}

		if ($is_new_layout){
			$site_module_info = Context::get('site_module_info');
			$args->site_srl = (int)$site_module_info->site_srl;
			$args->layout_srl = getNextSequence();
			$args->layout = $layout_info->name;
			$args->title = $layout_info->name;
			$args->layout_type = "P";
			// Insert into the DB
			$oLayoutAdminController = getAdminController('layout');
			$output = $oLayoutAdminController->insertLayout($args);
			$layout_info->layout_srl = $args->layout_srl;
		}

		$theme_info->layout_info = $layout_info;

		$skin_infos = $xml_obj->skininfos;
		if(is_array($skin_infos->skininfo))$skin_list = $skin_infos->skininfo;
		else $skin_list = array($skin_infos->skininfo);

		$oModuleModel = getModel('module');
		$skins = array();
		foreach($skin_list as $val){
			unset($skin_info);
			unset($skin_parse);
			$skin_parse = explode('/',$val->directory->attrs->path);
			switch($skin_parse[1])
			{
				case 'themes' : {
							$is_theme = true;
							$module_name = $skin_parse[count($skin_parse)-1];
							$skin_info->name = $theme_name.'.'.$module_name;
							break;
							}
				case 'modules' : {
							$is_theme = false;
							$module_name = $skin_parse[2];
							$skin_info->name = $skin_parse[count($skin_parse)-1];
							break;
							}
			}
			$skin_info->path = $val->directory->attrs->path;
			$skins[$module_name] = $skin_info;

			if ($is_theme)
			{
				if (!$GLOBALS['__ThemeModuleSkin__'][$module_name]){
					$GLOBALS['__ThemeModuleSkin__'][$module_name] = array();
					$GLOBALS['__ThemeModuleSkin__'][$module_name]['skins'] = array();
					$moduleInfo = $oModuleModel->getModuleInfoXml($module_name);
					$GLOBALS['__ThemeModuleSkin__'][$module_name]['title'] = $moduleInfo->title;
				}
				$GLOBALS['__ThemeModuleSkin__'][$module_name]['skins'][$skin_info->name] = $oModuleModel->loadSkinInfo($skin_info->path, '', '');
			}
		}
		$theme_info->skin_infos = $skins;

		$GLOBALS['__ThemeInfo__'][$theme_name] = $theme_info;
		return $theme_info;
	}

	function getModulesSkinList(){
		if ($GLOBALS['__ThemeModuleSkin__']['__IS_PARSE__']) return $GLOBALS['__ThemeModuleSkin__'];
		$searched_list = FileHandler::readDir('./modules');
		sort($searched_list);

		$searched_count = count($searched_list);
		if(!$searched_count) return;

		$exceptionModule = array('editor', 'poll', 'homepage', 'textyle');

		$oModuleModel = getModel('module');
		foreach($searched_list as $val) {
			$skin_list = $oModuleModel->getSkins('./modules/'.$val);

			if (is_array($skin_list) && count($skin_list) > 0 && !in_array($val, $exceptionModule)){
				if(!$GLOBALS['__ThemeModuleSkin__'][$val]){
					$GLOBALS['__ThemeModuleSkin__'][$val] = array();
					$moduleInfo = $oModuleModel->getModuleInfoXml($val);
					$GLOBALS['__ThemeModuleSkin__'][$val]['title'] = $moduleInfo->title;
					$GLOBALS['__ThemeModuleSkin__'][$val]['skins'] = array();
				}
				$GLOBALS['__ThemeModuleSkin__'][$val]['skins'] = array_merge($GLOBALS['__ThemeModuleSkin__'][$val]['skins'], $skin_list);
			}
		}
		$GLOBALS['__ThemeModuleSkin__']['__IS_PARSE__'] = true;

		return $GLOBALS['__ThemeModuleSkin__'];
	}

	function getAdminMenuLang()
	{
		$currentLang = Context::getLangType();
		$cacheFile = sprintf('./files/cache/menu/admin_lang/adminMenu.%s.lang.php', $currentLang);

		// Update if no cache file exists or it is older than xml file
		if(!is_readable($cacheFile))
		{
			$oModuleModel = getModel('module');
			$installed_module_list = $oModuleModel->getModulesXmlInfo();

			$this->gnbLangBuffer = '<?php ';
			foreach($installed_module_list AS $key=>$value)
			{
				$moduleActionInfo = $oModuleModel->getModuleActionXml($value->module);
				if(is_object($moduleActionInfo->menu))
				{
					foreach($moduleActionInfo->menu AS $key2=>$value2)
					{
						$lang->menu_gnb_sub[$key2] = $value2->title;
						$this->gnbLangBuffer .=sprintf('$lang->menu_gnb_sub[\'%s\'] = \'%s\';', $key2, $value2->title);
					}
				}
			}
			$this->gnbLangBuffer .= ' ?>';
			FileHandler::writeFile($cacheFile, $this->gnbLangBuffer);
		}
		else include $cacheFile;

		return $lang->menu_gnb_sub;
	}

	/**
	* @brief Get admin favorite list
	**/
	function getFavoriteList($siteSrl = 0, $isGetModuleInfo = false)
	{
		$args->site_srl = $siteSrl;
		$output = executeQueryArray('admin.getFavoriteList', $args);
		if (!$output->toBool()) return $output;
		if (!$output->data) return new Object();

		if($isGetModuleInfo && is_array($output->data))
		{
			$oModuleModel = getModel('module');
			foreach($output->data AS $key=>$value)
			{
				$moduleInfo = $oModuleModel->getModuleInfoXml($value->module);
				$output->data[$key]->admin_index_act = $moduleInfo->admin_index_act;
				$output->data[$key]->title = $moduleInfo->title;
			}
		}

		$returnObject = new Object();
		$returnObject->add('favoriteList', $output->data);
		return $returnObject;
	}

	/**
	* @brief Check available insert favorite
	**/
	function isExistsFavorite($siteSrl, $module)
	{
		$args->site_srl = $siteSrl;
		$args->module = $module;
		$output = executeQuery('admin.getFavorite', $args);
		if (!$output->toBool()) return $output;

		$returnObject = new Object();
		if ($output->data)
		{
			$returnObject->add('result', true);
			$returnObject->add('favoriteSrl', $output->data->admin_favorite_srl);
		}
		else
		{
			$returnObject->add('result', false);
		}

		return $returnObject;
	}

	/**
	* @brief Return site list
	**/
	function getSiteAllList()
	{
		if(Context::get('domain')) $args->domain = Context::get('domain');
		$columnList = array('domain', 'site_srl');

		$siteList = array();
		$output = executeQueryArray('admin.getSiteAllList', $args, $columnList);
		if($output->toBool()) $siteList = $output->data;

		$this->add('site_list', $siteList);
	}

	/**
	* @brief Return site count
	**/
	function getSiteCountByDate($date = '')
	{
		if($date) $args->regDate = date('Ymd', strtotime($date));

		$output = executeQuery('admin.getSiteCountByDate', $args);
		if(!$output->toBool()) return 0;

		return $output->data->count;
	}

	function getFaviconUrl()
	{
		return $this->iconUrlCheck('favicon.ico','faviconSample.png');
	}

	function getMobileIconUrl()
	{
		return $this->iconUrlCheck('mobicon.png','mobiconSample.png');
	}

	function iconUrlCheck($iconname,$default_icon_name)
	{
		$file_exsit = FileHandler::readFile(_XE_PATH_.'files/attach/xeicon/'.$iconname);
		if(!$file_exsit){
			$icon_url = './modules/admin/tpl/img/'.$default_icon_name	;
		} else {
			$icon_url = $db_info->default_url.'files/attach/xeicon/'.$iconname;
		}
		return $icon_url;
	}

	/**
	* @brief Get Dashboard shortcuts
	* @return array 
	*/
	function getAdminDashboardShortcuts()
	{
		// get admin member_srl
		$args->member_srl = Context::get("logged_info")->member_srl;

		$default_icon_name = "./modules/admin/tpl/img/no_icon.png";

		// get all shortcuts corresponding to logged member_srl
		$output = executeQueryArray('admin.getShortcuts', $args);
		if($output->toBool() && count($output->data) > 0)
		{
			$shortcutsList = $output->data;
		}
		else
		{
			return array();
		}
		$allowed_ext = array(".png",".jpg",".gif");
		$path = 'files/icons/shortcuts/';
		foreach($shortcutsList as $shortcut)
		{
			$file_exist = false;
			foreach($allowed_ext as $ext)
			{
				$file_exist = FileHandler::readFile($path.$shortcut->shortcut_srl.$ext);
				if($file_exist)
				{
					$used_ext = $ext;
					break;
				}
			}
			if(!$file_exist)
			{
				$shortcut->icon_path = $default_icon_name;
			}
			else
			{
				$shortcut->icon_path = $path.$shortcut->shortcut_srl.$used_ext;
			}
		}
		return $shortcutsList;
	}

	/**
	 * @brief Set Google Analytics Account 
	 */
	function setGAAccount()
	{
		$oModuleModel = getModel('module');
		$module_info = $oModuleModel->getModuleConfig($this->module);
		//set google api client
		$this->client = new apiClient();
		$this->client->setApplicationName("Google Analytics PHP Application");
		$this->client->setClientId($module_info->client_id);
		$this->client->setClientSecret($module_info->client_secret);
		$this->client->setRedirectUri($module_info->redirect_uri);
		$this->client->setDeveloperKey($module_info->developer_key);
		$this->client->setScopes(array("https://www.googleapis.com/auth/analytics.readonly"));
		if(isset($module_info->auth_token))
		{
			$this->client->setAccessToken($module_info->auth_token);
		}
	}

	/**
	 * @brief Get Information from Google Analytics API Account
	 * @return Object 
	 */
	function getGAData()
	{
		$this->setGAAccount();
		$client = $this->client;
		$data = array();
		if(isset($client::$auth->accessToken))
		{
			$params = array(
				'client_id' => $client->clientId,
				'client_secret' => $client->clientSecret,
				'refresh_token' => $client::$auth->accessToken["refresh_token"],
				'grant_type' => 'refresh_token'
			);

			$client::$auth->sign(new apiHttpRequest($client->OAUTH2_TOKEN_URI, 'POST', array(), $params));

			try
			{
				$dimensions = 'ga:date';
				$metrics = 'ga:visits,ga:visitors';
				$segment = 'gaid::-1';
				$ids = 'ga:44995332';
				$timestamp = strtotime('-1 month');
				$start_date = date('Y-m-d', $timestamp);
				$end_date = date("Y-m-d",time());
				$this->service = new apiAnalyticsService($client);
				$data = $this->service->data_ga->get($ids,$start_date,$end_date,$metrics,array('segment'=>$segment,'dimensions'=>$dimensions));
			}
			catch(Exception $e)
			{
				return new Object(-1, $e->getMessage());
			}
		}
		return $data;
	}

	/**
	 * @brief Get Information from XE Analytics (XE Counter module)
	 * @return Object 
	 */
	function getXEAnalyticsData()
	{
		$selected_date = date("Ymd");
		// create the counter model object
		$oCounterModel = getModel('counter');
		// get a total count and daily count
		$site_module_info = Context::get('site_module_info');
		$type = 'day';
		$detail_status = $oCounterModel->getHourlyStatus($type, $selected_date, $site_module_info->site_srl);
		return $detail_status;
	}
	
	/**
	 * @brief Get Information about modules activities from last admin login
	 * @return array 
	 */
	function getInfoFromLastAdminLogin()
	{
		$oMemberModel = &getModel('member');
		$logged_info = Context::get("logged_info");
		$last_login = $oMemberModel->getMemberInfoByMemberSrl($logged_info->member_srl)->last_login;
		$args->date = $last_login;
		$documents = executeQuery("admin.getDocumentsFromLastLogin", $args)->data;
		$comments = executeQuery("admin.getCommentsFromLastLogin", $args)->data;
		$trackbacks = executeQuery("admin.getTrackbacksFromLastLogin", $args)->data;
		$members = executeQuery("admin.getMembersFromLastLogin", $args)->data;
		$activity_data = array();
		$modules = array();
		//arrange information about new documents
		if(is_array($documents))
		{
			foreach ($documents as $value)
			{
				$modules[] = $value->module_srl;
				$activity_data[$value->module_srl]["documents"] = $value->count;
			}
		}
		else
		{
			if (isset($documents->module_srl))
			{
				$modules[] = $documents->module_srl;
				$activity_data[$documents->module_srl]["documents"] = $documents->count;
			}
		}
		//arrange information about new comments
		if(is_array($comments))
		{
			foreach ($comments as $value)
			{
				$modules[] = $value->module_srl;
				$activity_data[$value->module_srl]["comments"] = $value->count;
			}
		}
		else
		{
			if (isset($comments->module_srl))
			{
				$modules[] = $comments->module_srl;
				$activity_data[$comments->module_srl]["comments"] = $comments->count;
			}
		}
		// arrange information about new trackbacks 
		if(is_array($trackbacks))
		{
			foreach ($trackbacks as $value)
			{
				$modules[] = $value->module_srl;
				$activity_data[$value->module_srl]["trackbacks"] = $value->count;
			}
		}
		else
		{
			if (isset($trackbacks->module_srl))
			{
				$modules[] = $trackbacks->module_srl;
				$activity_data[$trackbacks->module_srl]["trackbacks"] = $trackbacks->count;
			}
		}
		
		//getting information about modules
		$unique_modules = array_unique($modules);
		$oModuleModel = &getModel("module");
		foreach ($unique_modules as $module)
		{
			if (!is_null($module))
			{
				$module_info = $oModuleModel->getModuleInfoByModuleSrl($module,array('module','mid'));
				$module_admin_page = $oModuleModel->getModuleActionXml($module_info->module);
				$activity_data[$module]["admin_page"] = $module_admin_page->admin_index_act;
				$activity_data[$module]["module"] = $module_info->module;
				$activity_data[$module]["mid"] = $module_info->mid;
			}
		}
		
		//arrange information about new members
		if(!is_null($members))
		{
			$activity_data["members"] = $members->count;
		}	
		return $activity_data;
	}
}
