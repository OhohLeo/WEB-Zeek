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
    	$this->assertTrue($zeek->checkOutput('{"structure":[]}'));

        // we set the new structure
        $this->assertTrue($zeek->structure_set('{"artist":{"name":{"type":"VARCHAR","size":100},"surname":{"type":"VARCHAR","size":100},"age":{"type":"INT_U"},"subtitle":{"type":"VARCHAR","size":300},"biography":{"type":"TEXT","size":1000},"skill":{"type":"VARCHAR","size":100}},"show":{"name":{"type":"VARCHAR","size":100},"date":{"type":"DATE"},"hour":{"type":"TIME"},"location":{"type":"VARCHAR","size":300}},"news":{"name":{"type":"VARCHAR","size":100},"date":{"type":"DATE"},"comments":{"type":"VARCHAR","size":100}},"album":{"name":{"type":"VARCHAR","size":100},"duration":{"type":"INT_U"},"comments":{"type":"TEXT","size":1000}},"music":{"name":{"type":"VARCHAR","size":100},"date":{"type":"DATE"},"duration":{"type":"INT_U"},"comments":{"type":"TEXT","size":1000}},"video":{"name":{"type":"VARCHAR","size":100},"date":{"type":"DATE"},"duration":{"type":"INT_U"},"comments":{"type":"TEXT","size":1000}},"media":{"name":{"type":"VARCHAR","size":100},"date":{"type":"DATE"},"comments":{"type":"TEXT","size":1000}}}'));

        $this->assertTrue($zeek->structure_get());
    	$this->assertTrue($zeek->checkOutput('{"structure":{"artist":{"name":{"type":"text","size":100,"db_type":"VARCHAR"},"surname":{"type":"text","size":100,"db_type":"VARCHAR"},"age":{"type":"number","min":0,"max":4294967295,"step":1,"db_type":"INT_U"},"subtitle":{"type":"text","size":300,"db_type":"VARCHAR"},"biography":{"type":"text","size":1000,"db_type":"TEXT"},"skill":{"type":"text","size":100,"db_type":"VARCHAR"}},"show":{"name":{"type":"text","size":100,"db_type":"VARCHAR"},"date":{"type":"date","db_type":"DATE"},"hour":{"type":"time","db_type":"TIME"},"location":{"type":"text","size":300,"db_type":"VARCHAR"}},"news":{"name":{"type":"text","size":100,"db_type":"VARCHAR"},"date":{"type":"date","db_type":"DATE"},"comments":{"type":"text","size":100,"db_type":"VARCHAR"}},"album":{"name":{"type":"text","size":100,"db_type":"VARCHAR"},"duration":{"type":"number","min":0,"max":4294967295,"step":1,"db_type":"INT_U"},"comments":{"type":"text","size":1000,"db_type":"TEXT"}},"music":{"name":{"type":"text","size":100,"db_type":"VARCHAR"},"date":{"type":"date","db_type":"DATE"},"duration":{"type":"number","min":0,"max":4294967295,"step":1,"db_type":"INT_U"},"comments":{"type":"text","size":1000,"db_type":"TEXT"}},"video":{"name":{"type":"text","size":100,"db_type":"VARCHAR"},"date":{"type":"date","db_type":"DATE"},"duration":{"type":"number","min":0,"max":4294967295,"step":1,"db_type":"INT_U"},"comments":{"type":"text","size":1000,"db_type":"TEXT"}},"media":{"name":{"type":"text","size":100,"db_type":"VARCHAR"},"date":{"type":"date","db_type":"DATE"},"comments":{"type":"text","size":1000,"db_type":"TEXT"}}}}'));


        $zeek->environment_clean();
    }
}
