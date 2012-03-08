jQuery(document).ready(function($) {
    
    /*
     * disable all links with class 'disabled'
     * NOTE: removes existing onclick and href from elements
     */
    $('a.disabled').each(function(){
        $(this).attr('onclick','');
        $(this).attr('href','');
        $(this).click(function(e) {
            e.preventDefault();
        })
    });

    /*
     * simulate 'min-height':100%; for <body>
     */
    var target = $('.xe-ui-footer');
    if (($(window).height()>$('body').height())&&(target.size()!= 0)) {
        var val = $(window).height() - (target.position().top + target.height() - parseInt(target.prev().css('margin-bottom'))) - 1; // -1 for borderd top
        target.css('margin-top',val);
    }

    /*
     * Simulate 'min-width':100px for submenus of the main admin menu
     */
    $('.xe-ui-menu-submenu').each( function() {
        if ($(this).width() < 100) $(this).width('100px');
    });

    /*
     * Disable default image drag in Mozilla Firefox
     */
    $(document).bind("dragstart", function() {
        return false;
    });

    /*
     * Enable tooltip on all form elements
     */
    $("input:checkbox, textarea, input:text, input:password, .xe-ui-tolltip").each( function() {
        $(this).tipTip({
            defaultPosition: "right",
            activation: "hover",
            maxWidth:"300px",
            delay:"0"
        });
    });

    /*
     * Set custom checkbox and radio buttons
     */
    $('input[type=checkbox],input[type=radio]').prettyCheckboxes();

});