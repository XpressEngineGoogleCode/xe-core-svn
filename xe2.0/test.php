<?php
    require_once 'PHPUnit/Framework.php';

    function loadDir($path, &$test_suite)
    {
        $handle = opendir($path);
        while($file = readdir($handle))
        {
            if($file == "." || $file == "..") continue;
            $file_path = $path."/".$file;
            if(is_dir($file_path)) loadDir($file_path, $test_suite);
            if(stristr($file, ".test.php"))
            {
                $test_suite->addTestFile($file_path);
            }
        }
        closedir($handle);
    }

    class Package_AllTests
    {
        public static function suite()
        {
            $test_suite = new PHPUnit_Framework_TestSuite();
            $test_suite->setName('XETest');
            
            $target_dirs = array("modules", "bricks");

            foreach($target_dirs as $target_dir)
            {
                loadDir($target_dir, $test_suite);
            }
            return $test_suite;
        }
    }
?>
