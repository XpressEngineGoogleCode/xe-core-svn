<?php
    /**
     * @archivo   es.lang.php
     * @autor zero (zero@nzeo.com)
     * @sumario  Paquete del idioma español (sólo los básicos)
     **/

    $lang->admin_info = 'Administrador de Información';
    $lang->admin_index = 'Índice de la página admin';
    $lang->control_panel = 'Control panel';

    $lang->module_category_title = array(
        'service' => 'Service Setting',
        'member' => 'Member Setting',
        'content' => 'Content Setting',
        'statistics' => 'Statistics',
        'construction' => 'Construction',
        'utility' => 'Utility Setting',
        'interlock' => 'Interlock Setting',
        'accessory' => 'Accessories',
        'migration' => 'Data Migration',
        'system' => 'System Setting',
    );


    $lang->newest_news = "Noticias recientes";
    
    $lang->env_setup = "Configuración";
    $lang->sso_url = "SSO URL";
    $lang->about_sso_url = "여러개의 virtual site운영시 한 곳에서 로그인하여도 모든 virtual site에서 로그인 정보를 유지할 수 있게 하기 위해서는 기본 사이트의 XE 설치 url을 입력해주시면 됩니다. (ex: http://도메인/xe)";

    $lang->env_information = "Información Ambiental";
    $lang->current_version = "Versión actual";
    $lang->current_path = "Instalado Sendero";
    $lang->released_version = "Versión más reciente";
    $lang->about_download_link = "La versión más reciente Zerboard XE está disponible.\nPara descargar la versión más reciente, haga clic en enlace de descarga.";
	
    $lang->item_module = "Lista de Módulos";
    $lang->item_addon  = "Lista de Addons";
    $lang->item_widget = "Lista de Widgets";
    $lang->item_layout = "Liasta de Diseños";

    $lang->module_name = "Nombre del Módulo";
    $lang->addon_name = "Nombre de Addon";
    $lang->version = "Versión";
    $lang->author = "Autor";
    $lang->table_count = "Número de los tableros";
    $lang->installed_path = "Ruta de instalación";

    $lang->cmd_shortcut_management = "Editar el Menú";

    $lang->msg_is_not_administrator = 'Sólo se permite el ingreso del administrador.';
    $lang->msg_manage_module_cannot_delete = 'No se puede eliminar acceso directo del Módulo, Addon, Diseño y Widget.';
    $lang->msg_default_act_is_null = 'No se puede registrar acceso directo por no estar determinada la acción del administrador predefinido.';
	
    $lang->welcome_to_xe = 'Esta es la página del Administrador de XE';
    $lang->about_admin_page = "La página del Administrador aún está en desarrollo.";
    $lang->about_lang_env = "Para aplicar idioma seleccionado conjunto de los usuarios, como por defecto, haga clic en el botón [Guardar] el cambio.";


    $lang->xe_license = 'XE está bajo la Licencia de GPL';
    $lang->about_shortcut = 'Puede Eliminar los accesos directos de módulos, los cuales fueron registrados en la lista de módulos usados frecuentemente';

    $lang->yesterday = "Yesterday";
    $lang->today = "Today";

    $lang->cmd_lang_select = "언어선택";
    $lang->about_cmd_lang_select = "선택된 언어들만 서비스 됩니다";
    $lang->about_recompile_cache = "쓸모없어졌거나 잘못된 캐시파일들을 정리할 수 있습니다";
    $lang->use_ssl = "SSL 사용";
    $lang->ssl_options = array(
        'none' => "사용안함",
        'optional' => "선택적으로",
        'always' => "항상사용"
    );
    $lang->about_use_ssl = "선택적으로에서는 회원가입/정보수정등의 지정된 action에서 SSL을 사용하고 항상 사용은 모든 서비스가 SSL을 이용하게 됩니다.";
    $lang->server_ports = "서버포트지정";
    $lang->about_server_ports = "HTTP는 80, HTTPS는 443이외의 다른 포트를 사용하는 경우에 포트를 지정해주어야합니다.";
?>
