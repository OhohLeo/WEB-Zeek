<?php

require_once 'php/database_access.php';

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
    private $test_dbname = 'zeek_test';

    private function connect()
    {
        $access = $this->access;
        $access->connect('zeek_test', 'test', 'test');
        return $access;
    }

    private function clean()
    {
        $this->access->database_delete($this->test_dbname);
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
            $access->connect($this->test_dbname, 'test', 'test'));

        $this->assertTrue(
            $access->checkOutput(NULL));

        $this->assertFalse(
            $access->connect($this->test_dbname, 'test', 'tes'));

        $this->assertTrue(
            $access->checkOutput(
                "Impossible to connect with MySQL SQLSTATE[28000] [1045] "
                . "Access denied for user 'test'@'localhost'"
                . " (using password: YES)"));

        $this->assertFalse(
            $access->connect($this->test_dbname, 'tes', 'test'));

        $this->assertTrue(
            $access->checkOutput(
                "Impossible to connect with MySQL SQLSTATE[28000] [1045] "
                . "Access denied for user 'tes'@'localhost'"
                . " (using password: YES)"));
    }

    public function test_database()
    {
        $access = $this->connect();

        $this->clean();

        $this->assertFalse(
            $access->database_use($this->test_dbname));

        $this->assertFalse(
            $access->database_check($this->test_dbname));

        $this->assertTrue(
            $access->database_create($this->test_dbname));

        $this->assertTrue(
            $access->database_create($this->test_dbname));

        $this->assertTrue(
            $access->database_check($this->test_dbname));

        $this->assertTrue(
            $access->database_use($this->test_dbname));

        $this->assertFalse(
            $access->table_check('just_id'));

        $this->assertTrue(
            $access->table_create('just_id', NULL));

        $this->assertTrue(
            $access->table_check('just_id'));

        $this->assertTrue(
            $access->table_delete('just_id'));

        $this->assertFalse(
            $access->table_check('just_id'));

        $this->assertTrue(
            $access->table_create('one_more_attributes',
            array('menu_name', 'VARCHAR(30)')));

        $this->assertTrue(
            $access->database_delete($this->test_dbname));

        $this->assertTrue(
            $access->database_delete($this->test_dbname));

        $this->assertFalse(
            $access->database_check($this->test_dbname));

        $this->assertFalse(
            $access->database_use($this->test_dbname));
    }
}
?>