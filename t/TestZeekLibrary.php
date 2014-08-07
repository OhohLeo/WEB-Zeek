<?php

require_once "lib/output.php";
require_once 'lib/zeek_library.php';

class ExtendsZeekLibrary extends ZeekLibrary
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

class TestZeekLibrary extends PHPUnit_Framework_TestCase
{
    private $zlib;

    public function setUp()
    {
        $zlib = new ExtendsZeekLibrary();

        $config = parse_ini_file('t/test.ini');
        $zlib->config($config);
        $zlib->global_path = '';

        $this->zlib = $zlib;
    }

    public function test_environment()
    {
        $this->assertTrue(
            $this->zlib->connect_to_database());

        $this->zlib->environment_clean('zeek_test');

        $this->assertTrue(
            $this->zlib->environment_setup('zeek_test', 'test', 'test'));

        $access = $this->zlib->database();

        $this->assertTrue(
            $access->database_check('zeek_test'));

        $this->assertTrue(
            $access->table_check('user'));

        $result = $access->table_view(
            'user', '*', NULL, NULL, NULL, NULL)->fetch();

        $this->assertEquals($result->id, 1);
        $this->assertEquals($result->name, "test");
        $this->assertEquals($result->password, "test");

        $this->assertTrue(
            $access->table_check('project'));


        $this->zlib->environment_clean('zeek_test');

        $this->assertFalse(
            $access->database_check('zeek_test'));
    }


    public function test_user()
    {
        $zlib = $this->zlib;

        $this->assertTrue(
            $zlib->connect_to_database());

        $zlib->environment_clean('zeek_test');

        $this->assertTrue(
            $zlib->environment_setup('zeek_test', 'test', 'test'));

        $this->assertTrue($zlib->user_get('test') !== NULL);

        $this->assertTrue($zlib->user_check('test', 'test'));

        $this->assertTrue($zlib->user_get('toto') == NULL);

        $this->assertFalse($zlib->user_check('toto', 'toto'));

        $this->assertTrue($zlib->user_add('toto', 'toto'));

        $this->assertFalse($zlib->user_add('toto', 'toto'));

        $this->assertTrue($zlib->user_get('toto') !== NULL);

        $this->assertTrue($zlib->user_check('toto', 'toto'));

        $this->assertFalse($zlib->user_get('toto') == NULL);

        $this->assertTrue($zlib->user_change_password(
            'toto', 'toto', 'titi'));

        $this->assertFalse($zlib->user_change_password(
            'tutu', 'toto', 'titi'));

        $this->assertFalse($zlib->user_change_password(
            'toto', 'toto', 'titi'));

        $this->assertTrue($zlib->user_change_password(
            'toto', 'titi', 'toto'));

        $this->assertFalse($zlib->user_change_password(
            'toto', 'titi', 'toto'));

        $this->assertFalse($zlib->user_change_password(
            'toto', 'titi', 'toto'));

        $this->assertTrue($zlib->user_remove('toto'));
        $this->assertFalse($zlib->user_remove('toto'));
        $this->assertFalse($zlib->user_remove('tutu'));

        $zlib->environment_clean('zeek_test');
    }

    public function test_project()
    {
        $this->assertTrue(
            $this->zlib->connect_to_database());

        $this->zlib->environment_clean('zeek_test');

        $this->assertTrue(
            $this->zlib->environment_setup('zeek_test', 'test', 'test'));

        $this->assertFalse(
            $this->zlib->project_check('test'));

        $this->assertTrue(
            $this->zlib->project_add('test'));

        $this->assertFalse(
            $this->zlib->project_add('test'));

        $this->assertTrue(
            $this->zlib->project_check('test'));

        $this->assertTrue(
            $this->zlib->connect_to_database());

        $this->assertTrue(
            $this->zlib->project_delete());

        $this->zlib->environment_clean('zeek_test');
    }
}
