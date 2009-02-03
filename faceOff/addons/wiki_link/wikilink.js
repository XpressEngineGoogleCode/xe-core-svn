function openWikiLinkDialog()
{
    jQuery("#link").css('display', 'block');
    var target = xGetElementById('linktarget');
    target.value = "";
    jQuery("#link").dialog({height:100});
}

function setText() {
    var target = xGetElementById('linktarget');
    if(!target.value || target.value.trim() == '') return;
    var text = target.value;
    text.replace(/&/ig,'&amp;').replace(/</ig,'&lt;').replace(/>/ig,'&gt;');
    var url = request_uri.setQuery('mid',current_mid).setQuery('entry',text); 
    var link = "<a href=\""+url+"\" ";
    link += ">"+text+"</a>";

    var iframe_obj = editorGetIFrame(1)
    editorReplaceHTML(iframe_obj, link);
    jQuery("#link").dialog("close");
}

function addShortCutForWiki() 
{
    var iframe_obj = editorGetIFrame(1);
    jQuery(iframe_obj.contentWindow.document).bind('keydown', "Alt+Space", function(evt) { openWikiLinkDialog(); return false;}); 
}

xAddEventListener(window, 'load', addShortCutForWiki);

