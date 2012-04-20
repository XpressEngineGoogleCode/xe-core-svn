/* ------------------------------------------------------------------------
	prettyCheckboxes

	Developped By: Stephane Caron (http://www.no-margin-for-errors.com)
	Inspired By: All the non user friendly custom checkboxes solutions ;)
	Version: 1.1

	Copyright: Feel free to redistribute the script/modify it, as
			   long as you leave my infos at the top.
------------------------------------------------------------------------- */
(function($){
    jQuery.fn.prettyCheckboxes = function(settings) {
        settings = jQuery.extend({
            checkboxWidth: 17,
            checkboxHeight: 17,
            className : 'prettyCheckbox',
            display: 'list'
        }, settings);

        $(this).each(function(){
            // Find the label
            $label = $('label[for="'+$(this).attr('id')+'"]');

            //If we have no label we create one
            if ($label.length < 1) {
                //create a new label
                $label = $(document.createElement('label'));
                // check if input has id attribute
                $label.attr('for', $(this).attr('id'));
                //wrap input with label
                $(this).wrap($label);
                $label = $(this).parent();
            }

            // Add the checkbox holder to the label
            $label.prepend("<span class='holderWrap'><span class='holder'></span></span>");

            // If the checkbox is checked, display it as checked
            if($(this).is(':checked')) {
                $label.addClass('checked');
            };

            // If the checkbox is disabled, display it as disabled
            if($(this).is(':disabled')) {
                $label.addClass('disabled');
            };

            // Assign the class on the label
            $label.addClass(settings.className).addClass($(this).attr('type')).addClass(settings.display);

            // Assign the dimensions to the checkbox display
            $label.find('span.holderWrap').width(settings.checkboxWidth).height(settings.checkboxHeight);
            $label.find('span.holder').width(settings.checkboxWidth);

            // Hide the checkbox
            $(this).addClass('hiddenCheckbox');

            $label.click(function(e) {
                e.stopPropagation();
            });

            $label.dblclick(function(e) {
                e.stopPropagation();
            });

            $label.bind('click',function(e) {

                $this = $(this);

                // we check to call the event only once because it is fired twice if user clicks label
                if (e.target.tagName.toLowerCase() != 'input') {
                    $input = $this.children('input');
                    if($input.length < 1) {
                        $input = $('input#' + $this.attr('for'));   
                    }

                    if (!$this.hasClass('disabled')) {

                        $input.triggerHandler('click');

                        if ($input.is(':checkbox')) {

                            $this.toggleClass('checked');
                            $input.checked = true;

                            $this.find('span.holder').css('top',0);
                        } else {
                            $toCheck = $input;

                            // Uncheck all radio
                            $('input[name="'+$toCheck.attr('name')+'"]').each(function() {
                                $('label[for="' + $(this).attr('id')+'"]').removeClass('checked');
                            });

                            $this.addClass('checked');
                            $toCheck.checked = true;
                        };

                    }
                }

            });

            $label.bind('dblclick',function(e) {

                e.preventDefault();
                $(this).triggerHandler('click');

            });


            $('input#' + $label.attr('for')).not('.disabled').bind('keypress',function(e) {
                if(e.keyCode == 32){
                    if($.browser.msie){
                        $('label[for="'+$(this).attr('id')+'"]').toggleClass("checked");
                    } else {
                        $(this).trigger('click');
                    }
                    return false;
                };
            });
        });
    };

    checkAllPrettyCheckboxes = function(caller, container){
        if($(caller).is(':checked')){
            // Find the label corresponding to each checkbox and click it
            $(container).find('input[type=checkbox]:not(:checked)').each(function() {
                $(this).parent().trigger('click');
                if($.browser.msie){
                    $(this).attr('checked','checked');
                }else{
                    $(this).trigger('click');
                };
            });
        } else {
            $(container).find('input[type=checkbox]:checked').each(function() {
                $(this).parent().trigger('click');
                if($.browser.msie){
                    $(this).attr('checked','');
                }else{
                    $(this).trigger('click');
                };
            });
        };
    };
})(jQuery);