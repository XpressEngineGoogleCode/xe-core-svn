<?php

/**
 * @class  admin
 * @author NHN (developers@xpressengine.com)
 * @brief  base class of admin module
 * */
class admin extends ModuleObject {

    /**
     * @brief install admin module
     * @return new Object
     * */
    function moduleInstall() {
        return new Object();
    }

    /**
     * @brief if update is necessary it returns true
     * */
    function checkUpdate() {
        $oDB = DB::getInstance();
        if (!$oDB->isColumnExists("admin_favorite", "type"))
            return true;
        FileHandler::makeDir('./files/icons/shortcuts');
        return false;
    }

    /**
     * @brief update module
     * @return new Object
     * */
    function moduleUpdate() {
        $oDB = DB::getInstance();
        if (!$oDB->isColumnExists("admin_favorite", "type")) {
            $oAdminAdminModel = getAdminModel('admin');
            $output = $oAdminAdminModel->getFavoriteList();
            $favoriteList = $output->get('favoriteList');

            $oDB->dropColumn('admin_favorite', 'admin_favorite_srl');
            $oDB->addColumn('admin_favorite', "admin_favorite_srl", "number", 11, 0);
            $oDB->addColumn('admin_favorite', "type", "varchar", 30, 'module');
            if (is_array($favoriteList)) {
                $oAdminAdminController = getAdminController('admin');
                $oAdminAdminController->_deleteAllFavorite();
                foreach ($favoriteList AS $key => $value) {
                    $oAdminAdminController->_insertFavorite($value->site_srl, $value->module);
                }
            }
        }
        FileHandler::makeDir('./files/icons/shortcuts');
        return new Object();
    }

    /**
     * @brief regenerate cache file
     * @return none
     * */
    function recompileCache() {

    }

    /**
     * @brief regenerate xe admin default menu
     * @return none
     * */
    function createXeAdminMenu() {
        //insert menu
        $args->title = '__XE_ADMIN__';
        $args->menu_srl = getNextSequence();
        $args->listorder = $args->menu_srl * -1;
        $output = executeQuery('menu.insertMenu', $args);
        $menuSrl = $args->menu_srl;
        unset($args);

        // gnb item create
        $gnbList = array('dashboard', 'content', 'design', 'users', 'extensions', 'settings');
        foreach ($gnbList AS $key => $value) {
            //insert menu item
            $args->menu_srl = $menuSrl;
            $args->menu_item_srl = getNextSequence();
            $args->name = '{$lang->menu_gnb[\'' . $value . '\']}';
            switch($value) {
                case 'dashboard':
                    $args->url = getUrl('', 'module', 'admin');
                    break;
                case 'design':
                    $args->url = getNotEncodedUrl('','module','admin','act','dispAdminTheme');
                    break;
                default:
                    $args->url='';
            }
            $args->listorder = -1 * $args->menu_item_srl;
            $output = executeQuery('menu.insertMenuItem', $args);
        }

        $oMenuAdminModel = getAdminModel('menu');
        $columnList = array('menu_item_srl', 'name');
        $output = $oMenuAdminModel->getMenuItems($menuSrl, 0, $columnList);
        if (is_array($output->data)) {
            foreach ($output->data AS $key => $value) {
                preg_match('/\{\$lang->menu_gnb\[(.*?)\]\}/i', $value->name, $m);
                $gnbDBList[$m[1]] = $value->menu_item_srl;
            }
        }
        unset($args);

        $gnbModuleList = array(
            0 => array(
                'module' => 'menu',
                'subMenu' => array('siteMap'),
            ),
            1 => array(
                'module' => 'file',
                'subMenu' => array('file'),
            ),
            2 => array(
                'module' => 'comment',
                'subMenu' => array('comment'),
            ),
            3 => array(
                'module' => 'trackback',
                'subMenu' => array('trackback'),
            ),
            4 => array(
                'module' => 'poll',
                'subMenu' => array('poll'),
            ),
            5 => array(
                'module' => 'rss',
                'subMenu' => array('rss'),
            ),
            6 => array(
                'module' => 'module',
                'subMenu' => array('multilingual'),
            ),
            7 => array(
                'module' => 'document',
                'subMenu' => array('document'),
            ),
            8 => array(
                'module' => 'trash',
                'subMenu' => array('trash'),
            ),
            9 => array(
                'module' => 'admin',
                'subMenu' => array('theme'),
            ),
            10 => array(
                'module' => 'layout',
                'subMenu' => array('installedLayout'),
            ),
            11 => array(
                'module' => 'autoinstall',
                'subMenu' => array('easyInstall'),
            ),
            12 => array(
                'module' => 'module',
                'subMenu' => array('installedModule'),
            ),
            13 => array(
                'module' => 'widget',
                'subMenu' => array('installedWidget'),
            ),
            14 => array(
                'module' => 'addon',
                'subMenu' => array('installedAddon'),
            ),
            15 => array(
                'module' => 'editor',
                'subMenu' => array('editor'),
            ),
            16 => array(
                'module' => 'member',
                'subMenu' => array('userList', 'userSetting', 'userGroup'),
            ),
            17 => array(
                'module' => 'importer',
                'subMenu' => array('importer'),
            ),
            18 => array(
                'module' => 'admin',
                'subMenu' => array('adminConfigurationGeneral', 'adminConfigurationFtp', 'adminMenuSetup'),
            ),
            19 => array(
                'module' => 'file',
                'subMenu' => array('fileUpload'),
            ),
            20 => array(
                'module' => 'module',
                'subMenu' => array('filebox'),
            ),
            21 => array(
                'module' => 'point',
                'subMenu' => array('point')
            ),
            22 => array(
                'module' => 'spamfilter',
                'subMenu' => array('spamFilter'),
            ),
        );

        $oMemberModel = getModel('member');
        $output = $oMemberModel->getAdminGroup(array('group_srl'));
        $adminGroupSrl = $output->group_srl;

        // gnb sub item create
        // common argument setting
        $args->menu_srl = $menuSrl;
        $args->open_window = 'N';
        $args->expand = 'N';
        $args->normal_btn = '';
        $args->hover_btn = '';
        $args->active_btn = '';
        $args->group_srls = $adminGroupSrl;
        $oModuleModel = getModel('module');

        foreach ($gnbModuleList AS $key => $value) {
            if (is_array($value['subMenu'])) {
                $moduleActionInfo = $oModuleModel->getModuleActionXml($value['module']);
                foreach ($value['subMenu'] AS $key2 => $value2) {
                    $gnbKey = "'" . $this->_getGnbKey($value2) . "'";

                    //insert menu item
                    $args->menu_item_srl = getNextSequence();
                    $args->parent_srl = $gnbDBList[$gnbKey];
                    $args->name = '{$lang->menu_gnb_sub[\'' . $value2 . '\']}';
                    $args->url = 'index.php?module=admin&act=' . $moduleActionInfo->menu->{$value2}->index;
                    $args->listorder = -1 * $args->menu_item_srl;
                    $output = executeQuery('menu.insertMenuItem', $args);
                }
            }
        }

        $oMenuAdminConroller = getAdminController('menu');
        $oMenuAdminConroller->makeXmlFile($menuSrl);
    }

    function _getGnbKey($menuName) {
        switch ($menuName) {
            case 'siteMap':
            case 'file':
            case 'document':
            case 'comment':
            case 'trackback':
            case 'poll':
            case 'rss':
            case 'multilingual':          
            case 'trash':
                return 'content';
                break;
            case 'userList':
            case 'userSetting':
            case 'userGroup':
            case 'point':
                return 'users';
                break;
            case 'theme':
            case 'installedLayout':
                return 'design';
                break;
            case 'easyInstall':
            case 'installedModule':
            case 'installedWidget':
            case 'installedAddon':
            case 'editor':
            case 'importer':
                return 'extensions';
                break;
            case 'adminConfigurationGeneral':
            case 'adminConfigurationFtp':
            case 'adminMenuSetup':
            case 'fileUpload':
            case 'filebox':
            case 'spamFilter':
                return 'settings';
                break;
            default:
                return 'extensions';
        }
    }

}

?>
