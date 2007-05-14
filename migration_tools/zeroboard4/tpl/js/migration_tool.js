function doMigrationStep1(fo_obj) {
    var path = fo_obj.path.value;
    if(!path) {
        alert("경로를 입력해주세요");
        return false;
    }

    var params = new Array();
    params["path"] = path;

    var response_tags = new Array("error","message","module_list");

    exec_xml("procGetModuleList", params, completeMigrationStep1, response_tags);

    return false;
}

function completeMigrationStep1(ret_obj, response_tags) {
    var module_list = ret_obj["module_list"];

    xGetElementById("path").style.display = "none";
    xGetElementById("module_list").style.display = "block";
}
