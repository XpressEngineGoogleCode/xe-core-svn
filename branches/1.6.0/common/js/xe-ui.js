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
    if( $(window).height() > $('body').height() ) {
        var target = $('.xe-ui-footer');
        var val = $(window).height() - (target.position().top + target.height() - parseInt(target.prev().css('margin-bottom'))) - 1; // -1 for borderd top
        target.css('margin-top',val);
    }

    /*
     * set <div> height in table to match the height of their parent <td> or <th>
     */
    $('.xe-ui-table td>div, .xe-ui-table th>div').each(function(){
        var divPadding = parseInt($(this).css('padding-top')) + parseInt($(this).css('padding-bottom'));
        var divBorder = parseInt($(this).css('border-top-width'),10) + parseInt($(this).css('border-bottom-width'),10);
        var tdHeight = $(this).parent('td,th').height() - divPadding - divBorder;
        $(this).height(tdHeight);
    });

    /*
     * vertical align content in <div>'s with "v-mid"
     */
    $('.xe-ui-table td.v-mid>div').each(function(){
        var child = $(this).children('div');
        var val = ($(this).height() - child.height())/2;
        if (child && val>0) {
            $(this).children('div').css('margin-top',val + 'px');
        }
    })

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
    $("input:checkbox, textarea, input:text").each( function() {
        if ($(this).attr('title') != undefined ) {
            $(this).tipTip({
                defaultPosition: "right",
                activation: "focus",
                maxWidth:"300px",
                delay:"0"
            });
        }
    });

});