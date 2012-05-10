<?php
if(!defined('__XE__')) exit();

/**
 * @file counter.addon.php
 * @author NHN (developers@xpressengine.com)
 * @brief Counter add-on
 **/
// Execute if called_position is before_display_content
if(Context::isInstalled() && $called_position == 'before_module_init' && Context::get('module')!='admin' && Context::getResponseMethod() == 'HTML') {
	$oCounterController = getController('counter');
	$oCounterController->counterExecute();
	
	//getting data about Analytics -  START 

	// get type of analytics that you choosed to be used on Admin Dashboard
	// if analytics type is GA or Naver you have to include the js script in all your pages
	$oModuleModel = getModel('module');
	$module_info = $oModuleModel->getModuleConfig("admin");
	Context::set('analytics_type',$module_info->analytics_type);
	switch ($module_info->analytics_type)
	{
		case "ga" :
		case "naver" :
				$ga_script = $module_info->script;
				Context::addHtmlHeader($ga_script);
				break;
	}
	//getting data about Analytics - STOP
}
?>
