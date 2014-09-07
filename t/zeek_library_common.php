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

    public function test_type()
    {
        $zlib = $this->zlib;

        $this->assertFalse(
            $zlib->type_check('failed'));

        $this->assertTrue(
            $zlib->type_check('project'));

        $this->assertFalse(
            $zlib->type_get('failed'));

        $this->assertEquals(
            $zlib->type_get('project'), array(
            'name'        => array('VARCHAR', 25),
            'since'       => 'DATE',
            'subtitle'    => array('VARCHAR', 300),
            'biography'   => array('TEXT', 1000)));

        $this->assertTrue($zlib->connect_to_database());

        $zlib->environment_clean($this->db_name);
    }

    public function test_value()
    {
        $zlib = $this->zlib;

        $this->assertTrue($zlib->connect_to_database());

        $zlib->environment_clean($this->db_name);

        $zlib->project_id = 0;

        # we add a new value here
        $this->assertTrue(
	    $zlib->value_insert(
            'artist',
            array('name'      => 'test_name',
            'surname'   => 'test_surname',
            'age'       => 27,
            'subtitle'  => 'test_subtitle',
            'biography' => 'la vie de ce test sera très courte',
            'skill'     => 'skill_test')));

        $result = $zlib->value_get('artist', NULL, NULL, NULL);

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
            'artist',
            array('name'      => 'test_name3',
            'surname'   => 'test_surname3',
            'age'       => 273,
            'subtitle'  => 'test_subtitle3',
            'biography' => 'la vie de ce test sera très très très courte',
            'skill'     => 'skill_test3')));

	$result = $zlib->value_get('artist', NULL, 1, 1);

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
		'artist', 3,
		array('name'      => 'test_update',
		      'surname'   => 'test_surname-update')));

	$result = $zlib->value_get('artist', NULL, 1, 2);

	$row = $zlib->value_fetch($result);

	$this->assertEquals($row->id, 3);
	$this->assertEquals($row->name, 'test_update');
	$this->assertEquals($row->surname, 'test_surname-update');
	$this->assertEquals($row->age, 273);
	$this->assertEquals($row->subtitle, 'test_subtitle3');
	$this->assertEquals($row->biography, 'la vie de ce test sera très très très courte');
	$this->assertEquals($row->skill, 'skill_test3');

	# we delete the last value
	$this->assertTrue($zlib->value_delete('artist', 3));

	$this->assertFalse(
	    $zlib->value_update(
		'artist', 3,
		array('name'      => 'test_update',
		      'surname'   => 'test_surname-update')));

	$this->assertFalse(
	    $zlib->value_insert(
		'failed',
		array('name'      => 'test_name3',
		      'surname'   => 'test_surname3',
		      'age'       => 273,
		      'subtitle'  => 'test_subtitle3',
		      'biography' => 'la vie de ce test sera très très très courte',
		      'skill'     => 'skill_test3')));

	$this->assertFalse(
	    $zlib->value_update(
		'failed', 3,
		array('name'      => 'test_update',
		      'surname'   => 'test_surname-update')));

    $zlib->environment_clean($this->db_name);
    }
}

?>
