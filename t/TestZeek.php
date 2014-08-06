<?php

require_once 'lib/zeek.php';

class ExtendsZeek extends Zeek
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

    protected function session_start()
    {
    }
}

class TestZeek extends PHPUnit_Framework_TestCase
{
    private $zeek;

    public function setUp()
    {
        $this->zeek = new ExtendsZeek();
        $this->zeek->config('t/test.ini');
    }

    public function test_environment()
    {
        $this->assertTrue(
            $this->zeek->connect_to_database());

        $this->zeek->environment_clean('zeek_test');

        $this->assertTrue(
            $this->zeek->environment_setup('zeek_test', 'test', 'test'));

        $access = $this->zeek->database();

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


        $this->zeek->environment_clean('zeek_test');

        $this->assertFalse(
            $access->database_check('zeek_test'));
    }


    public function test_user()
    {
        $zeek = $this->zeek;

        $this->assertTrue(
            $zeek->connect_to_database());

        $zeek->environment_clean('zeek_test');

        $this->assertTrue(
            $zeek->environment_setup('zeek_test', 'test', 'test'));

        $this->assertTrue($zeek->user_get('test') !== NULL);

        $this->assertTrue($zeek->user_check('test', 'test'));

        $this->assertTrue($zeek->user_get('toto') == NULL);

        $this->assertFalse($zeek->user_check('toto', 'toto'));

        $this->assertTrue($zeek->user_add('toto', 'toto'));

        $this->assertFalse($zeek->user_add('toto', 'toto'));

        $this->assertTrue($zeek->user_get('toto') !== NULL);

        $this->assertTrue($zeek->user_check('toto', 'toto'));

        $this->assertFalse($zeek->user_get('toto') == NULL);

        $this->assertTrue($zeek->user_change_password(
            'toto', 'toto', 'titi'));

        $this->assertFalse($zeek->user_change_password(
            'tutu', 'toto', 'titi'));

        $this->assertFalse($zeek->user_change_password(
            'toto', 'toto', 'titi'));

        $this->assertTrue($zeek->user_change_password(
            'toto', 'titi', 'toto'));

        $this->assertFalse($zeek->user_change_password(
            'toto', 'titi', 'toto'));

        $this->assertFalse($zeek->user_change_password(
            'toto', 'titi', 'toto'));

        $this->assertTrue($zeek->user_remove('toto'));
        $this->assertFalse($zeek->user_remove('toto'));
        $this->assertFalse($zeek->user_remove('tutu'));

        $zeek->environment_clean('zeek_test');
    }

    public function test_project()
    {
        $this->assertTrue(
            $this->zeek->connect_to_database());

        $this->zeek->environment_clean('zeek_test');

        $this->assertTrue(
            $this->zeek->environment_setup('zeek_test', 'test', 'test'));

        $this->assertFalse(
            $this->zeek->project_check('test'));

        $this->assertTrue(
            $this->zeek->project_add('test'));

        $this->assertFalse(
            $this->zeek->project_add('test'));

        $this->assertTrue(
            $this->zeek->project_check('test'));

        $this->assertTrue(
            $this->zeek->connect_to_database());

        $this->assertTrue(
            $this->zeek->project_delete());

        $this->zeek->environment_clean('zeek_test');
    }


    public function test_display_dynamic()
    {
        $this->assertEquals(
            $this->zeek->display_dynamic(''),
            '<div class="dynamic">
  </div>
');
    }

    public function test_connect()
    {
        $zeek = $this->zeek;

        $this->assertTrue(
            $zeek->connect('project', 'test', 'test'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

        $zeek->create_new_project('project');

        $this->assertTrue(
            $zeek->connect('project', 'test', 'test'));
        $this->assertTrue(
            $zeek->checkOutput('{"redirect":"home.php"}'));

        $zeek->project_delete();
        $zeek->environment_clean('zeek_test');
    }

    public function test_input()
    {
        $this->assertFalse($this->zeek->input(NULL));

        $this->assertTrue($this->zeek->input(
            array('method' => 'clicked',
            'type' => 'artist',
            'project_id' => 1)));
    }



    public function test_success()
    {
        $this->zeek->success('toto', array('tutu' => 'titi'));
        $this->assertTrue(
            $this->zeek->checkOutput(
                json_encode(array('success' => 'toto', 'tutu' => 'titi'))));


        $this->zeek->success('toto', NULL);
        $this->assertTrue(
            $this->zeek->checkOutput(
                json_encode(array('success' => 'toto'))));
    }

}
?>
