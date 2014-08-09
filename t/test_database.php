<?php

require_once "lib/output.php";
require_once 'lib/database.php';
require_once 't/database_common.php';

class ExtendsDataBase extends DataBase
{
    private $output;

    public function checkOutput($input)
    {
        if ($this->output == $input) {
            $this->output = NULL;
            return true;
        }

        echo "\n expect : " . $this->output
            . "\n received : " . $input . "\n";

        return false;
    }

    public function output($input)
    {
        $this->output = $input;
    }
}

class TestDataBase extends TestDataBaseCommon
{

    public function setUp()
    {
        $this->db = new ExtendsDataBase();
    }

    public function test_connect()
    {
        $db = $this->db;

        $this->assertTrue(
            $db->connect('localhost', $this->test_dbname, 'test', 'test'));

        $this->assertTrue(
            $db->checkOutput(NULL));

        $this->assertFalse(
            $db->connect('localhost', $this->test_dbname, 'test', 'tes'));

        $this->assertTrue(
            $db->checkOutput(
                "Impossible to connect to 'localhost' with MySQL SQLSTATE[28000] [1045] "
                . "Access denied for user 'test'@'localhost'"
                . " (using password: YES)"));

        $this->assertFalse(
            $db->connect('localhost', $this->test_dbname, 'tes', 'test'));

        $this->assertTrue(
            $db->checkOutput(
                "Impossible to connect to 'localhost' with MySQL SQLSTATE[28000] [1045] "
                . "Access denied for user 'tes'@'localhost'"
                . " (using password: YES)"));
    }
}
?>