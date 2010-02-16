<?php

class FileDebugPrinter implements iDebugPrinter {
	private $debug_file; 

	public function __construct()
	{
		$this->debug_file = _XE_PATH_.'files/_debug_message.php';
	}

	public function debugPrint($debug_output, $display_option, $debug_backtrace)
	{
		$first = array_shift($debug_backtrace);
        $file_name = array_pop(explode(DIRECTORY_SEPARATOR, $first['file']));
        $line_num = $first['line'];

		if(function_exists("memory_get_usage"))
		{
			$debug_output = sprintf("[%s %s:%d] - mem(%s)\n%s\n", date('Y-m-d H:i:s'), $file_name, $line_num, memory_get_usage(), print_r($debug_output, true)); // should filesize function added
		}
		else
		{
			$debug_output = sprintf("[%s %s:%d]\n%s\n", date('Y-m-d H:i:s'), $file_name, $line_num, print_r($debug_output, true));
		}

		if($display_option === true) $debug_output = str_repeat('=', 40)."\n".$debug_output.str_repeat('-', 40);
		$debug_output = "\n<?php\n/*".$debug_output."*/\n?>\n";

		if(!$fp = fopen($this->debug_file, 'a')) return;
		fwrite($fp, $debug_output);
		fclose($fp);
	}
}
?>
