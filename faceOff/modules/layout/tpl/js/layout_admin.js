
/* 레이아웃 신규 생성시 완료 후 요청하는 함수 */
function completeInsertLayout(ret_obj) {
      var layout_srl = ret_obj['layout_srl'];
      var url = current_url.setQuery('act','dispLayoutAdminModify').setQuery('layout_srl',layout_srl);
      location.href = url;
}

/* 레이아웃 삭제 */
function doDeleteLayout(layout_srl) {
    var fo_obj = jQuery('#fo_layout').get(0);
    fo_obj.layout_srl.value = layout_srl;
    procFilter(fo_obj, delete_layout);
}

/* 수정된 레이아웃을 원본으로 돌림 */
function doResetLayoutCode(layout_srl) {
    var fo_obj = jQuery('#fo_layout').get(0);
    procFilter(fo_obj, reset_layout_code);
    return false;
}

/* 수정중인 레이아웃 미리보기 */
function doPreviewLayoutCode() {
    jQuery('#fo_layout')
        .attr('target', "_LayoutPreview")
        .find('input[name=act]')
            .val("dispLayoutAdminPreview")
        .end()
        .submit()
        .removeAttr('target')
        .find('input[name=act]')
            .val('');
}


/* 메뉴 관리로 이동 */
function doMenuManagement(menu_id) {
    var menu_srl = jQuery('#fo_layout select[name='+menu_id+'] option:selected').val();
    if(menu_srl == 0) return;

    var url = current_url.setQuery('act','dispMenuAdminManagement').setQuery('menu_srl',menu_srl);
    winopen(url,'MenuManage','width=850,height=800,resizable=yes,scrollbars=yes,toolbars=no');
}
