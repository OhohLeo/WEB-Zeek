<?php

require_once 't/zeek_extends.php';

class TestZeek extends PHPUnit_Framework_TestCase
{
    private $zeek;
    protected $test_dbname = 'zeek_test';

    public function setUp()
    {
        $zeek = new ExtendsZeek();
        $zeek->start('t/test_no_projects_path.ini');
        $zeek->connect_to_database();

        $this->zeek = $zeek;
    }

    public function test_project()
    {
        $zeek = $this->zeek;

    	// we connect with good login & password
        $this->assertTrue(
            $zeek->connect('project', 'test', 'test'));
        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

    	// we create a project
        $this->assertTrue($zeek->project_create(
            'project', array('zeekify', 'minimise_css', 'minimise_js')));
        $this->assertTrue(
            $zeek->checkOutput('{"redirect":"home.php"}'));

    	// we create the same project again
        $this->assertFalse($zeek->project_create(
            'project', array('zeekify', 'minimise_css', 'minimise_js')));
    	$this->assertTrue(
            $zeek->checkOutput('{"error":"Project already existing!"}'));

    	// we create another project
        $this->assertTrue($zeek->project_create(
            'project2', array('zeekify')));
        $this->assertTrue(
            $zeek->checkOutput('{"redirect":"home.php"}'));

        $this->assertTrue(
            $zeek->connect('project', 'test', 'test'));

    	// we check the structure
    	$zeek->structure_get();
    	$this->assertTrue($zeek->checkOutput('{"structure":null}'));


        $zeek->environment_clean();
    }
}
