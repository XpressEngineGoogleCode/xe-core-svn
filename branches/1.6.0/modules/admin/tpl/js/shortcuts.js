function addNewLine()
{
    jQuery("#dashboard_shortcuts").find('tr:last').clone().appendTo("#dashboard_shortcuts");
    var last_tr = jQuery("#dashboard_shortcuts").find('tr:last');
    var inputs = last_tr.find("input");
    inputs.each(function() {
        jQuery(this).val("");
    });
    var rows = jQuery("table#dashboard_shortcuts > tbody").find('tr').length;
    last_tr.find("input:text").eq(0).attr("name","link[-"+rows+"]");
    last_tr.find("input:text").eq(1).attr("name","name[-"+rows+"]");
    last_tr.find("input:file").attr("name","shortcutIcon[-"+rows+"]");
    last_tr.find("input:hidden").eq(0).attr("name","order[-"+rows+"]");
    last_tr.find("input:hidden").eq(0).val(rows);
    last_tr.find("input:hidden").eq(1).attr("name","shortcutId[-"+rows+"]");
    last_tr.find("input:hidden").eq(1).val("-1");
    last_tr.find("img[id*=currentIcon]").remove();
    last_tr.find("td:last a").attr("onclick","jQuery(this).closest('tr').remove();");
}

function removeLine(id) {
    jQuery(id).closest('tr').remove();
}

function delCurrentLine(id)
{
    jQuery("#act").val('procAdminDeleteShortcut');
    jQuery("#idWillBeDelete").val(id);
    document.getElementById('form_shortcuts').submit();
}

function reorderTableElements()
{
    var myTableLines = jQuery('#dashboard_shortcuts > tbody > tr');
    var i = 1;
    myTableLines.each(function() {
        orderElem = jQuery(this).find("input[name*=order]");
        orderElem.val(i);
        i++;
    });
}

jQuery(document).ready(function()
{
    jQuery('table.sortable').live('after-drag.st',function() {
        reorderTableElements();
    });
})

