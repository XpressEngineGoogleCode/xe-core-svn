<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */

if(!defined('__XE__'))
	exit();

/**
 * @file adminlogging.addon.php
 * @author NAVER (developers@xpressengine.com)
 * @brief Automatic link add-on
 */
$logged_info = Context::get('logged_info');
$act = Context::get('act');
$kind = stripos($act, 'admin') !== false ? 'admin' : '';

if($called_position == 'before_module_proc' && $kind == 'admin' && $logged_info->is_admin == 'Y')
{
	$oAdminloggingController = getController('adminlogging');
	$oAdminloggingController->insertLog($this->module, $this->act);
}
/* End of file adminlogging.php */
/* Location: ./addons/adminlogging */
