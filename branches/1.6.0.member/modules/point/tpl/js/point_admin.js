/**
 * @file   modules/point/js/point_admin.js
 * @author NHN (developers@xpressengine.com)
 * @brief  point 모듈의 관리자용 javascript
 **/

jQuery(function($){

    $('a.calc_point').click(function(e){
        var $this, form, elems, reset, el, fn, i=0;

        $this = $(this);
        $expr = $('input.level_expression');
        elems = $(".point_input:input");
        reset = $this.hasClass('_reset');

        e.preventDefault();
        $this.blur();
        
        if (reset || !$expr.val()) {
            $expr.val('Math.pow(i,2) * 90');
        }

        try {
            fn = new Function('i', 'return ('+$expr.val()+')');
        } catch(e) {
            fn = null;
        }

        if (!fn) return;

        for ( i=0; i<=(elems.length - 1); i++ ) {
            elems[i].value = fn(i);
        }
    });

});

/**
 * @brief 포인트를 전부 체크하여 재계산하는 action 호출
 **/
function doPointRecal() {
    var resp, $recal;

    function on_complete(ret) {
        if(!$recal) $recal = jQuery('#pointReCal');

        $recal.html(ret.message);

        if(ret.position == ret.total) {
            alert(message);
            location.reload();
        } else {
            exec_xml(
                'point',
                'procPointAdminApplyPoint',
                {
                    position : ret.position,
                    total : ret.total
                },
                on_complete,
                resp
                );
        }
    }

    exec_xml(
        'point', // module
        'procPointAdminReCal', // procedure
        {}, // parameters
        on_complete, // callback
        resp=['error','message','total','position'] // response tags
        );
}

function updatePoint(member_srl)
{
    var $point = jQuery('#point_'+member_srl);
    if ($point.attr('value') != undefined) {
        jQuery('#update_member_srl').attr('value',member_srl);
        jQuery('#update_point').attr('value',$point.val());
        jQuery("#updateForm").submit();
    }
}


function doPointReset(module_srls) {
    exec_xml(
        'point',
        'procPointAdminReset',
        {
            module_srls : module_srls
        },
        function(ret_obj){
            alert(ret_obj['message']);
            location.reload(true);
        },
        ['error','message']
        );
}
