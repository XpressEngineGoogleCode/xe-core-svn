jQuery(function(){
    jQuery('.language_selector','div.widgetLanguage').click(function(){
        var c = jQuery(this).parent();
        var langList = c.next('ul.langList');
        langList.toggle();
        if(langList.css('top')){
            if(jQuery('body').height() <= c.offset().top + langList.height()) {
                langList.css('top',-(langList.height()+17));
            }else{
                langList.css('top','26px');
            }
        }
        return false;
    });
});