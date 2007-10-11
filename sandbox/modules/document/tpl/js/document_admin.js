function doCheckAll(bToggle) {
    var fo_obj = xGetElementById('fo_list');
	if(typeof(bToggle) == "undefined") bToggle = false;
    for(var i=0;i<fo_obj.length;i++) {
        if(fo_obj[i].name == 'cart'){
			if( !fo_obj[i].checked || !bToggle) fo_obj[i].checked = true; else fo_obj[i].checked = false;
		}
    }
}

/**
 * @brief 모든 생성된 썸네일 삭제하는 액션 호출
 **/
function doDeleteAllThumbnail() {
    exec_xml('document','procDocumentAdminDeleteAllThumbnail',new Array(), completeDeleteAllThumbnail);
}

function completeDeleteAllThumbnail(ret_obj) {
    alert(ret_obj['message']);
    location.reload();
}

/* 선택된 글의 삭제 또는 이동 */
function doManageDocument(type, mid) {
    var fo_obj = xGetElementById("fo_management");
    fo_obj.type.value = type;

    procFilter(fo_obj, manage_checked_document);
}

/* 선택된 글의 삭제 또는 이동 후 */
function completeManageDocument(ret_obj) {
    if(opener) opener.location.href = opener.location.href;
    alert(ret_obj['message']);
    window.close();
}

/* 선택된 모듈의 카테고리 목록을 가져오는 함수 */
function doGetCategoryFromModule(obj) {
    var module_srl = obj.options[obj.selectedIndex].value;

    var params = new Array();
    params['module_srl'] = module_srl;

    var response_tags = new Array('error','message','categories');

    exec_xml('document','getDocumentCategories',params, completeGetCategoryFromModules, response_tags);

}

function completeGetCategoryFromModules(ret_obj, response_tags) {
    var obj = xGetElementById('target_category');
    var length = obj.options.length;
    for(var i=0;i<length;i++) obj.remove(0);

    var categories = ret_obj['categories'];
    if(!categories) return;

    var category_list = categories.split("\n");
    for(var i=0;i<category_list.length;i++) {
        var item = category_list[i];
        var pos = item.indexOf(',');
        var category_srl = item.substr(0,pos);
        var category_title = item.substr(pos+1,item.length);
        if(!category_srl || !category_title) continue;

        var opt = new Option(category_title, category_srl, false, false);
        obj.options[obj.options.length] = opt;
    }
}
