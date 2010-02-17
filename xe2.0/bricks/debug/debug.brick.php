<?php
interface iDebugPrinter {
	public function debugPrint($debug_output, $display_option, $debug_backtrace);
}

class DebugBrick {
	private static $debugPrinter = null;

	public static function debugPrint($debug_output, $display_option, $debug_backtrace)
	{
		if(!self::$debugPrinter) self::_createPrinter(); 
		self::$debugPrinter->debugPrint($debug_output, $display_option, $debug_backtrace);
	}

	private static function _createPrinter()
	{
		if(__DEBUG_OUTPUT__ == 2 && version_compare(PHP_VERSION, '6.0.0') === -1) {
			require(_XE_PATH_.'bricks/debug/firephpDebugPrinter.class.php');
			self::$debugPrinter = new FirephpDebugPrinter();
		}
		else
		{
			require(_XE_PATH_.'bricks/debug/fileDebugPrinter.class.php');
			self::$debugPrinter = new FileDebugPrinter();
		}
	}

	public static function setPrinter($printer)
	{
		self::$debugPrinter = $printer;
	}
}
?>
