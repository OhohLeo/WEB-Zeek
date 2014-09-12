<?php

require_once 'lib/output.php';
require_once 'lib/zeek_library.php';
require_once 't/zeek_library_common.php';

class ExtendsZeekLibrary extends ZeekLibrary
{
    private $output;

    public function checkOutput($input)
    {
        if ($this->output == $input) {
            $this->output = NULL;
            return true;
        }

        echo "\n expect : " . $input
            . "\n received : " . $this->output . "\n";

        return false;
    }

    public function database()
    {
        return $this->db;
    }

    public function output($input)
    {
        echo "$input \n";
        $this->output = $input;
    }
}


class TestZeekLibrary extends TestZeekLibraryCommon
{
    public function setUp()
    {
        $zlib = new ExtendsZeekLibrary();

        $config = parse_ini_file('t/test.ini');
        $zlib->config($config);
        $zlib->global_path = '';

        $this->zlib = $zlib;
    }
}

?>
