<?php

require_once 'lib/output.php';
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
        $this->zeek->start('t/test.ini');
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
