<?php

class TestZeekLibraryCommon extends PHPUnit_Framework_TestCase
{
    protected $zlib;
    private $db_name = 'zeek_test';

    public function test_environment()
    {
        $zlib = $this->zlib;

        $this->assertTrue($zlib->connect_to_database());

        $zlib->environment_clean($this->db_name);

        $this->assertTrue(
            $zlib->environment_setup($this->db_name, 'test', 'test'));

        $db = $zlib->database();

        $this->assertTrue(
            $db->database_check($this->db_name));

        $this->assertTrue(
            $db->table_check('user'));

        $result = $db->table_view('user', '*', NULL, NULL, NULL, NULL);

        $result = $db->handle_result($result);

        $this->assertEquals($result->id, 1);
        $this->assertEquals($result->name, "test");
        $this->assertEquals($result->password, "test");

        $this->assertTrue(
            $db->table_check('project'));


        $zlib->environment_clean($this->db_name);

        $this->assertFalse(
            $db->database_check($this->db_name));
    }


    public function test_user()
    {
        $zlib = $this->zlib;

        $this->assertTrue(
            $zlib->connect_to_database());

        $zlib->environment_clean($this->db_name);

        $this->assertTrue(
            $zlib->environment_setup($this->db_name, 'test', 'test'));

        $this->assertTrue($zlib->user_check(0, 'test', 'test'));

        $this->assertTrue($zlib->user_get(1, 'test') !== NULL);

        $this->assertTrue($zlib->user_check(0, 'test', 'test'));

        $this->assertTrue($zlib->user_get(1, 'toto') == NULL);

        $this->assertFalse($zlib->user_check(1, 'toto', 'toto'));

        $this->assertTrue($zlib->user_add(1, 'toto', 'toto'));

        $this->assertFalse($zlib->user_add(1, 'toto', 'toto'));

        $this->assertTrue($zlib->user_get(1, 'toto') !== NULL);

        $this->assertTrue($zlib->user_get(2, 'toto') == NULL);

        $this->assertTrue($zlib->user_check(1, 'toto', 'toto'));

        $this->assertFalse($zlib->user_check(0, 'toto', 'toto'));

        $this->assertFalse($zlib->user_get(1, 'toto') == NULL);

        $this->assertTrue($zlib->user_change_password(
            1, 'toto', 'toto', 'titi'));

        $this->assertFalse($zlib->user_change_password(
            1, 'tutu', 'toto', 'titi'));

        $this->assertFalse($zlib->user_change_password(
            1, 'toto', 'toto', 'titi'));

        $this->assertTrue($zlib->user_change_password(
            1, 'toto', 'titi', 'toto'));

        $this->assertFalse($zlib->user_change_password(
            1, 'toto', 'titi', 'toto'));

        $this->assertFalse($zlib->user_change_password(
            1, 'toto', 'titi', 'toto'));

        $this->assertTrue($zlib->user_remove(1, 'toto'));
        $this->assertFalse($zlib->user_remove(1, 'toto'));
        $this->assertFalse($zlib->user_remove(1, 'tutu'));

        $zlib->environment_clean($this->db_name);
    }

    public function test_project()
    {
        $zlib = $this->zlib;

        $this->assertTrue($zlib->connect_to_database());

        $zlib->environment_clean($this->db_name);

        $this->assertTrue(
            $zlib->environment_setup($this->db_name, 'test', 'test'));

        $this->assertFalse(
            $zlib->project_get_id('test'));

        $this->assertTrue(
            $zlib->project_add('test'));

        $this->assertFalse(
            $zlib->project_add('test'));

        $this->assertEquals(
            $zlib->project_get_id('test'), 1);

        $this->assertTrue($zlib->connect_to_database());

        $this->assertTrue($zlib->project_delete('test'));

        $zlib->environment_clean($this->db_name);
    }


    public function test_projects()
    {
        $zlib = $this->zlib;

        $this->assertTrue($zlib->connect_to_database());

        $structure = $zlib->projects_get('t/projects.ini');

        $this->assertEquals($structure,
        array(
            'test' => array(
                'artist'      => array(
                    'name'       => array('VARCHAR', 100),
                    'surname'    => array('VARCHAR', 100),
                    'age'        => array('INT', 11),
                    'subtitle'   => array('VARCHAR', 300),
                    'biography'  => array('TEXT', 1000),
                    'skill'      => array('VARCHAR', 100)),
                'show'        => array(
                    'name'       => array('VARCHAR', 100),
                    'date'       => 'DATE',
                    'hour'       => 'TIME',
                    'location'   => array('VARCHAR', 300)),
                'news'        => array(
                    'name'       => array('VARCHAR', 100),
                    'date'       => 'DATE',
                    'comments'   => array('TEXT', 1000)),
                'album'       => array(
                    'name'       => array('VARCHAR', 100),
                    'duration'   => array('INT', 11),
                    'comments'   => array('TEXT', 1000)),
                'music'       => array(
                    'name'       => array('VARCHAR', 100),
                    'date'       => 'DATE',
                    'duration'   => array('INT', 11),
                    'comments'   => array('TEXT', 1000)),
                'video'       => array(
                    'name'       => array('VARCHAR', 100),
                    'date'       => 'DATE',
                    'duration'   => array('INT', 11),
                    'comments'   => array('TEXT', 1000)),
                'media'       => array(
                    'name'       => array('VARCHAR', 100),
                    'date'       => 'DATE',
                    'comments'   => array('TEXT', 1000))),
            'test2'  =>  array(
                'test1'       => array(
                    'name'        => array('VARCHAR', 25),
                    'since'       => 'DATE',
                    'subtitle'    => array('VARCHAR', 300),
                    'biography'   => array('TEXT', 1000)),
            )));

        $wrong_tests = array(
            't/projects/no_project_name.ini' =>
            "Can't find project name for this table!",
            't/projects/wrong_project_name.ini' =>
            "Can't find project name for this table!",
            't/projects/wrong_table_name.ini' =>
            "Can't find table name for this attribute!",
            't/projects/no_table_name.ini' =>
            "Can't find table name for this attribute!",
            't/projects/wrong_attribute_name.ini' =>
            "Unknown type 'TOTO' for attribute 'name'!",
            't/projects/ignore_attribute_name.ini' =>
            "Ignore line 3 in file 't\/projects\/ignore_attribute_name.ini'!",
            't/projects/unknown.ini' =>
            "Can't find projects configuration file 't\/projects\/unknown.ini'!"
        );

        foreach ($wrong_tests as $filename => $error) {
            $this->assertFalse($zlib->projects_get($filename));
            $this->assertTrue($zlib->checkOutput('{"error":"' . $error .'"}'));
        }
    }

    public function test_type()
    {
        $zlib = $this->zlib;

        $this->assertFalse(
            $zlib->type_check('test', 'failed'));

        $this->assertFalse(
            $zlib->type_get('test', 'failed'));

        $this->assertTrue($zlib->connect_to_database());

        $zlib->environment_clean($this->db_name);
    }

    public function test_value()
    {
        $zlib = $this->zlib;

        $this->assertTrue($zlib->connect_to_database());

        $zlib->environment_clean($this->db_name);

        $this->assertTrue(
            $zlib->project_add('test'));

        $zlib->project_id = $zlib->project_get_id('test');
        $this->assertEquals($zlib->project_id, 1);

        # we add a new value here
        $this->assertTrue(
	    $zlib->value_insert(
            'test',
            'artist',
            array('name'      => 'test_name',
                  'surname'   => 'test_surname',
                  'age'       => 27,
                  'subtitle'  => 'test_subtitle',
                  'biography' => 'la vie de ce test sera très courte',
                   'skill'     => 'skill_test')));

        $result = $zlib->value_get('test', 'artist');

        $row = $zlib->value_fetch($result);

        $this->assertEquals($row->id, 1);
        $this->assertEquals($row->name, 'test_name');
        $this->assertEquals($row->surname, 'test_surname');
        $this->assertEquals($row->age, 27);
        $this->assertEquals($row->subtitle, 'test_subtitle');
        $this->assertEquals($row->biography, 'la vie de ce test sera très courte');
        $this->assertEquals($row->skill, 'skill_test');

        # we add a new value again
        $this->assertTrue(
            $zlib->value_insert(
                'test',
                'artist',
                array('name'      => 'test_name2',
                'surname'   => 'test_surname2',
                'age'       => 272,
                'subtitle'  => 'test_subtitle2',
                'biography' => 'la vie de ce test sera très très courte',
                'skill'     => 'skill_test2')));

	# we add a new value again
	$this->assertTrue(
	    $zlib->value_insert(
            'test',
            'artist',
            array('name'      => 'test_name3',
            'surname'   => 'test_surname3',
            'age'       => 273,
            'subtitle'  => 'test_subtitle3',
            'biography' => 'la vie de ce test sera très très très courte',
            'skill'     => 'skill_test3')));

	$result = $zlib->value_get('test', 'artist', NULL, 1, 1);

	$row = $zlib->value_fetch($result);

	$this->assertEquals($row->id, 2);
	$this->assertEquals($row->name, 'test_name2');
	$this->assertEquals($row->surname, 'test_surname2');
	$this->assertEquals($row->age, 272);
	$this->assertEquals($row->subtitle, 'test_subtitle2');
	$this->assertEquals($row->biography, 'la vie de ce test sera très très courte');
	$this->assertEquals($row->skill, 'skill_test2');

	# we add a new value again
	$this->assertTrue(
	    $zlib->value_update(
            $zlib->project_id, 'artist', 3,
            array('name'      => 'test_update',
                  'surname'   => 'test_surname-update')));

	$result = $zlib->value_get('test', 'artist', NULL, 1, 2);

	$row = $zlib->value_fetch($result);

	$this->assertEquals($row->id, 3);
	$this->assertEquals($row->name, 'test_update');
	$this->assertEquals($row->surname, 'test_surname-update');
	$this->assertEquals($row->age, 273);
	$this->assertEquals($row->subtitle, 'test_subtitle3');
	$this->assertEquals($row->biography, 'la vie de ce test sera très très très courte');
	$this->assertEquals($row->skill, 'skill_test3');

	$this->assertEquals($zlib->table_count('test', 'artist'), 3);

	# we delete the last value
	$this->assertTrue($zlib->value_delete(
        $zlib->project_id, 'artist', 3));

	$this->assertFalse(
	    $zlib->value_update(
            $zlib->project_id, 'artist', 3,
            array('name'      => 'test_update',
     		      'surname'   => 'test_surname-update')));

	$this->assertFalse(
	    $zlib->value_insert(
            'test', 'failed',
            array('name'      => 'test_name3',
		      'surname'   => 'test_surname3',
		      'age'       => 273,
		      'subtitle'  => 'test_subtitle3',
		      'biography' => 'la vie de ce test sera très très très courte',
		      'skill'     => 'skill_test3')));

	$this->assertFalse(
	    $zlib->value_update(
            $zlib->project_id, 'failed', 3,
            array('name'      => 'test_update',
                  'surname'   => 'test_surname-update')));

    $zlib->environment_clean($this->db_name);
    }
}

?>
