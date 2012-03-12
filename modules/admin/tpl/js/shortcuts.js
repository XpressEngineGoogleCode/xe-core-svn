function addNewLine()
{
	jQuery("#dashboard_shortcuts").find('tr:last').clone(false,false).appendTo("#dashboard_shortcuts");
	var last_tr = jQuery("#dashboard_shortcuts").find('tr:last');
	var inputs = last_tr.find("input:text");
	inputs.each(function() {
		jQuery(this).val('');
	});
	last_tr.find("img[id*=currentIcon]").remove();
	last_tr.find("td:last").html('');
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
	jQuery('table.sortable').bind('mouseup.st', function() {
		reorderTableElements();
	});
})

