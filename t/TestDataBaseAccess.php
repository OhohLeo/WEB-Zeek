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

    private function connect()
    {
        $access = $this->access;
        $access->connect('test', 'test', 'test');
        return $access;
    }

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

    public function test_database()
    {
        $access = $this->connect();

        $this->assertFalse(
            $access->database_use('test'));

        $this->assertFalse(
            $access->database_check('test'));

        $this->assertTrue(
            $access->database_create('test'));

        $this->assertTrue(
            $access->database_create('test'));

        $this->assertTrue(
            $access->database_check('test'));

        $this->assertTrue(
            $access->database_use('test'));

        $attributes = array(
            "id"   => "INT",
            "data" => array(
                "VARCHAR",
                "limit"   => 100,
                "default" => 'NULL'),
            "current_time" => "TIMESTAMP");

        $this->assertTrue(
            $access->table_create('persons', $attributes));

        $this->assertTrue(
            $access->database_delete('test'));

        $this->assertTrue(
            $access->database_delete('test'));

        $this->assertFalse(
            $access->database_check('test'));

        $this->assertFalse(
            $access->database_use('test'));
    }
}
?>