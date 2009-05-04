
jQuery(function($) {
    var url_regx = /((http|https|ftp|news|telnet|irc):\/\/(([0-9a-z\-._~!$&'\(\)*+,;=:]|(%[0-9a-f]{2}))*\@)?((\[(((([0-9a-f]{1,4}:){6}([0-9a-f]{1,4}:[0-9a-f]{1,4})|(([0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])){3}[0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])))|(::([0-9a-f]{1,4}:){5}([0-9a-f]{1,4}:[0-9a-f]{1,4})|(([0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])){3}[0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])))|(([0-9a-f]{1,4})?::([0-9a-f]{1,4}:){4}([0-9a-f]{1,4}:[0-9a-f]{1,4})|(([0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])){3}[0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])))|((([0-9a-f]{1,4}:)?[0-9a-f]{1,4})?::([0-9a-f]{1,4}:){3}([0-9a-f]{1,4}:[0-9a-f]{1,4})|(([0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])){3}[0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])))|((([0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::([0-9a-f]{1,4}:){2}([0-9a-f]{1,4}:[0-9a-f]{1,4})|(([0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])){3}[0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])))|((([0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:([0-9a-f]{1,4}:[0-9a-f]{1,4})|(([0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])){3}[0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])))|((([0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::([0-9a-f]{1,4}:[0-9a-f]{1,4})|(([0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])){3}[0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])))|((([0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4})|((([0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::))|(v[0-9a-f]+.[0-9a-z\-._~!$&'\(\)*+,;=:]+))\])|(([0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5])){3}[0-9]|([1-9][0-9])|(1[0-9][0-9])|(2[0-4][0-9])|(25[0-5]))|(([0-9a-z\-._~!$&'\(\)*+,;=]|(%[0-9a-f]{2}))+))(:[0-9]*)?(\/([0-9a-z\-._~!$&'\(\)*+,;=:@]|(%[0-9a-f]{2}))*)*(\?([0-9a-z\-._~!$&'\(\)*+,;=:@\/\?]|(%[0-9a-f]{2}))*)?(#([0-9a-z\-._~!$&'\(\)*+,;=:@\/\?]|(%[0-9a-f]{2}))*)?)/i;

    function replaceHrefLink(obj) {
        var obj_list = obj.childNodes;

        for(var i = 0; i < obj_list.length; ++i) {
            var obj = obj_list[i];
            var pObj = obj.parentNode;
            if(!pObj) continue;

            var pN = pObj.nodeName.toLowerCase();
            if($.inArray(pN, ['a', 'pre', 'xml', 'textarea', 'input', 'select', 'option', 'code', 'script', 'style']) != -1) continue;

            if(obj.nodeType == 3 && obj.length >= 10) {
                var content = obj.nodeValue;
                if(!/(http|https|ftp|news|telnet|irc):\/\//i.test(content)) continue;

                content = content.replace(/</g, '&lt;');
                content = content.replace(/>/g, '&gt;');
                content = content.replace(url_regx, '<a href="$1" onclick="window.open(this.href); return false;">$1</a>');

                $(obj).replaceWith(content);
                delete(content);

            } else if(obj.nodeType == 1 && obj.childNodes.length) {
                replaceHrefLink(obj);
            }
        }
    }

    $('.xe_content').each(function() {
        replaceHrefLink(this);
    });
});
