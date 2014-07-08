<?php

require_once 'php/functions.php';

class ExtendsDataBaseAccess extends DataBaseAccess
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

class TestDataBaseAccess extends PHPUnit_Framework_TestCase
{
    private $access;

    public function setUp()
    {
        $access = new ExtendsDataBaseAccess();

        $this->access = $access;
    }

    public function test_access()
    {
        $this->assertNotNull($this->access);
    }

    public function test_connect()
    {
        $access = $this->access;

        $this->assertTrue(
            $access->connect('test', 'test', 'test'));

        $this->assertTrue(
            $access->checkOutput(NULL));

        $this->assertFalse(
            $access->connect('test', 'test', 'tes'));

        $this->assertTrue(
            $access->checkOutput(
                "Impossible to connect with MySQL SQLSTATE[28000] [1045] "
                . "Access denied for user 'test'@'localhost'"
                . " (using password: YES)"));

        $this->assertFalse(
            $access->connect('test', 'tes', 'test'));

        $this->assertTrue(
            $access->checkOutput(
                "Impossible to connect with MySQL SQLSTATE[28000] [1045] "
                . "Access denied for user 'tes'@'localhost'"
                . " (using password: YES)"));
    }

    public function test_check_database()
    {
        $access = $this->access;

        $access->connect('test', 'test', 'test');

        $this->assertTrue(
            $this->access->check_database('test'));
    }
}
?>