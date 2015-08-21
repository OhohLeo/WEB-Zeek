<?php

require_once 'lib/mime.php';

class TestZeekMime extends PHPUnit_Framework_TestCase
{

    public function test()
    {
        $mime = new ZeekMime();

        $this->assertTrue($mime->validate_mime_type("video/gl"));
        $this->assertTrue($mime->validate_mime_type("*/*"));
        $this->assertFalse($mime->validate_mime_type("error"));
    }
}
