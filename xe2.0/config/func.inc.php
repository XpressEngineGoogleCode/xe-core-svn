<?php
    function loadBrick($brick_name, $once = false)
    {
        $brick_path = sprintf("%sbricks/%s/%s.brick.php", _XE_PATH_, $brick_name, $brick_name); 
        if(!file_exists($brick_path)) return false;
        if($once)
        {
            require_once($brick_path);
        }
        else
        {
            require($brick_path);
        }
        return true;
    }

	function debugPrint($debug_output = null, $display_option = true)
	{
        if(!(__DEBUG__ & 1)) return;
		if(__DEBUG_PROTECT__ === 1 && __DEBUG_PROTECT_IP__ != $_SERVER['REMOTE_ADDR']) return;

        $bt = debug_backtrace();
		DebugBrick::debugPrint($debug_output, $display_option, $bt);
	}
?>
