<?php

require_once 'lib/output.php';
require_once 'lib/database_mysql.php';
require_once 't/database_common.php';

class ExtendsOldMySQL extends DataBaseOldMySQL
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

class TestOldMySQL extends TestDataBaseCommon
{
    public function setUp()
    {
        $this->db = new ExtendsOldMySQL();
    }

    public function test_connect()
    {
        $db = $this->db;

        $this->assertTrue(
            $db->connect('localhost', NULL, 'test', 'test'));

        $this->assertTrue(
            $db->checkOutput(NULL));

        $this->assertFalse(
            $db->connect('localhost', NULL, 'test', 'tes'));

        $this->assertTrue(
            $db->checkOutput(
                '{"error":"Impossible to connect to \'localhost\' with MySQL mysql_connect(): Access denied for user \'test\'@\'localhost\' (using password: YES)"}'));

        $this->assertFalse(
            $db->connect('localhost', NULL, 'tes', 'test'));

        $this->assertTrue(
            $db->checkOutput(
                '{"error":"Impossible to connect to \'localhost\' with MySQL mysql_connect(): Access denied for user \'tes\'@\'localhost\' (using password: YES)"}'));
    }
}

?>