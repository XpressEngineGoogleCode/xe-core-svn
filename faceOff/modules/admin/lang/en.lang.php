<?php
    /**
     * @file   en.lang.php
     * @author zero (zero@nzeo.com)
     * @brief  English Language Pack (Only basic words are included here)
     **/

    $lang->admin_info = 'Administrator Info';
    $lang->admin_index = 'Index Admin Page';

    $lang->module_category_title = array(
        'service' => 'Service Modules',
        'manager' => 'Managing Modules',
        'utility' => 'Utility Modules',
        'accessory' => 'Additional Modules',
        'base' => 'Default Modules',
    );

    $lang->newest_news = "Latest News";
    
    $lang->env_setup = "Setting";
    $lang->sso_url = "SSO URL";
    $lang->about_sso_url = "여러개의 virtual site운영시 한 곳에서 로그인하여도 모든 virtual site에서 로그인 정보를 유지할 수 있게 하기 위해서는 기본 사이트의 XE 설치 url을 입력해주시면 됩니다. (ex: http://도메인/xe)";

    $lang->env_information = "Environment Information";
    $lang->current_version = "Current Version";
    $lang->current_path = "Installed Path";
    $lang->released_version = "Latest Version";
    $lang->about_download_link = "New version of Zerboard XE is now available.\nClick the download link to get the latest version.";
    
    $lang->item_module = "Module List";
    $lang->item_addon  = "Addon List";
    $lang->item_widget = "Widget List";
    $lang->item_layout = "Layout List";

    $lang->module_name = "Module Name";
    $lang->addon_name = "Addon Name";
    $lang->version = "Version";
    $lang->author = "Developer";
    $lang->table_count = "Number of Table";
    $lang->installed_path = "Installed Path";

    $lang->cmd_shortcut_management = "Edit Menu";

    $lang->msg_is_not_administrator = 'Administrator only';
    $lang->msg_manage_module_cannot_delete = 'Shortcuts of module, addon, layout, widget cannot be removed';
    $lang->msg_default_act_is_null = 'Shortcut could not be registered because default admin Action is not set';

    $lang->welcome_to_xe = 'Welcome to the admin page of XE';
    $lang->about_admin_page = "Admin page is still being developing,\nWe will add essential contents by accepting many good suggestions during Closebeta.";
    $lang->about_lang_env = "To apply selected language as default language, click the [Save] button.";

    $lang->xe_license = 'XE complies with the GPL';
    $lang->about_shortcut = 'You may remove shortcuts of modules which are registered on frequently using module list';

    $lang->yesterday = "Yesterday";
    $lang->today = "Today";

    $lang->cmd_lang_select = "Language";
    $lang->about_cmd_lang_select = "Selected languages only will be serviced";
    $lang->about_recompile_cache = "You can arrange useless or invalid cache files";
    $lang->use_ssl = "Use SSL";
    $lang->ssl_options = array(
        'none' => "Not use",
        'optional' => "optional",
        'always' => "always"
    );
    $lang->about_use_ssl = "If you choose 'optional', SSL will be used for actions such as sign up / changing information. And for 'always', your site will be served only via https.";
    $lang->server_ports = "Server port";
    $lang->about_server_ports = "If your web-server uses other than 80 for HTTP, 443 for HTTPS, you should specify server ports";
?>
