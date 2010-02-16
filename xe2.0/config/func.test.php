<?php
require_once 'PHPUnit/Framework.php';

class FuncTest extends PHPUnit_Framework_TestCase
{
    public function testLoadBrick()
    {
        $res = loadBrick("notexists");
        $this->assertFalse($res);
    }
}

?>
