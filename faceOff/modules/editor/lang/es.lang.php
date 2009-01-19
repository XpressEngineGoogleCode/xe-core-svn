<?php
    /**
     * @archivo  modules/editor/lang/es.lang.php
     * @autor zero <zero@nzeo.com>
     * @sumario  Paquete del idioma español para el editor de WYSIWYG
     **/

    $lang->editor = 'Editor WYSIWYG';
    $lang->component_name = 'Componente';
    $lang->component_version = 'Versión';
    $lang->component_author = 'Autor';
    $lang->component_link = 'Enlace';
    $lang->component_date = 'Fecha';
    $lang->component_license = 'License';
    $lang->component_history = 'History';
    $lang->component_description = 'Descripción';
    $lang->component_extra_vars = 'Varibles Extras';
    $lang->component_grant = 'Ajuste de las atribuciones';

    $lang->about_component = 'Presentación del componente';
    $lang->about_component_grant = 'Usted puede configurar el permiso de utilizar la ampliación de los componentes de editor.<br /> (Todo el mundo tendría permiso si no comprobado)';
    $lang->about_component_mid = '에디터 컴포넌트가 사용될 대상을 지정할 수 있습니다.<br />(모두 해제시 모든 대상에서 사용 가능합니다)';

    $lang->msg_component_is_not_founded = 'No se puede encontrar el componente del editor %s';
    $lang->msg_component_is_inserted = 'El componente seleccionado ya esta insertado';
    $lang->msg_component_is_first_order = 'El componente seleccionado se localiza en la primera posición';
    $lang->msg_component_is_last_order = 'El componente seleccionado se localiza en la última posición';
    $lang->msg_load_saved_doc = "Existe un documento guardado automáticamente ¿desea recuperarlo ?\nDespués de guardar el documento escrito, el documento autoguardado sera eliminado.";
    $lang->msg_auto_saved = 'Documento guardado automáticamente';

    $lang->cmd_disable = 'Desactivado';
    $lang->cmd_enable = 'activado';

    $lang->editor_skin = 'Editor de Cuidado de la Piel';
    $lang->upload_file_grant = 'La autorización para cargar';
    $lang->enable_default_component_grant = 'La autorización del uso de los componentes por defecto';
    $lang->enable_component_grant = 'La autorización de la utilización de componentes';
    $lang->enable_html_grant = 'La autorización de uso de HTML';
    $lang->enable_autosave = 'Utilice función de guardado automático,';
    $lang->height_resizable = 'Altura cambiar de tamaño';
    $lang->editor_height = 'Altura de Editor';

    $lang->about_editor_skin = 'Usted puede seleccionar la piel del editor.';
    $lang->about_upload_file_grant = 'Usted puede configurar el permiso de archivo adjunto. (Todo el mundo tendría permiso si no comprobado)';
    $lang->about_default_component_grant = 'Usted puede configurar el permiso de uso de los componentes de editor por defecto. (Todo el mundo tendría permiso si no comprobado)';
    $lang->about_editor_height = 'Usted puede configurar la altura del editor.';
    $lang->about_editor_height_resizable = 'Permiso para cambiar el tamaño de la altura del editor.';
    $lang->about_enable_html_grant = 'Usted puede dar el permiso de uso de HTML';
    $lang->about_enable_autosave = 'Usted puede permitir que la función de guardado automático, en tanto que función de la redacción de artículos';

    $lang->edit->fontname = 'Fuente';
    $lang->edit->fontsize = 'Tamaño';
    $lang->edit->use_paragraph = 'Párrafo';
    $lang->edit->fontlist = array(
    'Arial'=>'Arial',
    'Arial Black'=>'Arial Black',
    'Tahoma'=>'Tahoma',
    'Verdana'=>'Verdana',
    'Sans-serif'=>'Sans-serif',
    'Serif'=>'Serif',
    'Monospace'=>'Monospace',
    'Cursive'=>'Cursive',
    'Fantasy'=>'Fantasy',
    );

    $lang->edit->header = 'Estilo';
    $lang->edit->header_list = array(
    'h1' => 'Título 1',
    'h2' => 'Título 2',
    'h3' => 'Título 3',
    'h4' => 'Título 4',
    'h5' => 'Título 5',
    'h6' => 'Título 6',
    );

    $lang->edit->submit = 'Confirmar';

    $lang->edit->fontcolor = 'Text Color';
    $lang->edit->fontbgcolor = 'Background Color';
    $lang->edit->bold = 'Bold';
    $lang->edit->italic = 'Italic';
    $lang->edit->underline = 'Underline';
    $lang->edit->strike = 'Strike';
    $lang->edit->sup = 'Sup';
    $lang->edit->sub = 'Sub';
    $lang->edit->redo = 'Re Do';
    $lang->edit->undo = 'Un Do';
    $lang->edit->align_left = 'Align Left';
    $lang->edit->align_center = 'Align Center';
    $lang->edit->align_right = 'Align Right';
    $lang->edit->align_justify = 'Align Justify';
    $lang->edit->add_indent = 'Indent';
    $lang->edit->remove_indent = 'Outdent';
    $lang->edit->list_number = 'Orderd List';
    $lang->edit->list_bullet = 'Unordered List';
    $lang->edit->remove_format = 'Style Remover';

    $lang->edit->help_fontcolor = 'Selecciona el color de las letras';
    $lang->edit->help_fontbgcolor = 'Selecciona el color del fondo de la letras';
    $lang->edit->help_bold = 'Letra gruesa';
    $lang->edit->help_italic = 'Letra cursiva';
    $lang->edit->help_underline = 'Letra subrayada';
    $lang->edit->help_strike = 'Letra con linea';
    $lang->edit->help_sup = 'Sup';
    $lang->edit->help_sub = 'Sub';
    $lang->edit->help_redo = 'Rehacer';
    $lang->edit->help_undo = 'Deshacer';
    $lang->edit->help_align_left = 'Margen izquierdo';
    $lang->edit->help_align_center = 'Margen central';
    $lang->edit->help_align_right = 'Margen derecho';
    $lang->edit->help_add_indent = 'Anadir tabulación';
    $lang->edit->help_remove_indent = 'Quitar tabulación';
    $lang->edit->help_list_number = 'Aplicar la lista con números';
    $lang->edit->help_list_bullet = 'Aplicar la lista con símbolos';
    $lang->edit->help_use_paragraph = 'Presiona Ctrl+Enter para usar el párrafo (Presiona Alt+S para guardar)';

    $lang->edit->url = 'URL';
    $lang->edit->blockquote = 'Blockquote';
    $lang->edit->table = 'Table';
    $lang->edit->image = 'Image';
    $lang->edit->multimedia = 'Movie';
    $lang->edit->emoticon = 'Emoticon';

    $lang->edit->upload = 'Adjuntar';
    $lang->edit->upload_file = 'Archivo adjunto';
    $lang->edit->link_file = 'Insertar en el contenido del documento';
    $lang->edit->delete_selected = 'Eliminar lo seleccionado';

    $lang->edit->icon_align_article = 'Ocupar un párrafo';
    $lang->edit->icon_align_left = 'Margen izquierdo';
    $lang->edit->icon_align_middle = 'Margen central';
    $lang->edit->icon_align_right = 'Margen derecho';

    $lang->about_dblclick_in_editor = 'Para la configuracion más detallada debera hacer dobleclick sobre el texto, imagen, fondo, etc.';


    $lang->edit->rich_editor = '스타일 편집기';
    $lang->edit->html_editor = 'HTML 편집기';
    $lang->edit->extension ='확장 컴포넌트';
    $lang->edit->help = '도움말';
    $lang->edit->help_command = '단축키 안내';
?>
