/**
 * @file   modules/page/js/page_admin.js
 * @author zero (zero@nzeo.com)
 * @brief  page모듈의 관리자용 javascript
 **/

/* 모듈 생성 후 */
function completeInsertPage(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    var page = ret_obj['page'];
    var module_srl = ret_obj['module_srl'];

    alert(message);

    var url = '';
    if(location.href.getQuery('module')=='admin') {
        url = location.href.setQuery('module_srl',module_srl).setQuery('act','dispPageAdminInfo');
        if(page) url = url.setQuery('page',page);
    } else {
        url = location.href.setQuery('act','').setQuery('module_srl','');
    }

    location.href = url;
}


/* 모듈 삭제 후 */
function completeDeletePage(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    alert(message);

    var url = location.href.setQuery('act','dispPageAdminContent');
    if(page) url = url.setQuery('page',page);

    location.href = url;
}

/* 카테고리 이동 */
function doChangeCategory(sel_obj, url) {
    var module_category_srl = sel_obj.options[sel_obj.selectedIndex].value;
    if(!module_category_srl) location.href=url;
    else location.href = location.href.setQuery('module_category_srl',module_category_srl);
}
