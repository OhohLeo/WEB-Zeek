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

        $result = $db->table_view('user', '*');

        $result = $db->handle_result($result);

        $this->assertFalse($result);

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

        $this->assertTrue($zlib->user_add(1, 'toto', 'toto', false));

        $this->assertFalse($zlib->user_add(1, 'toto', 'toto', false));

        $this->assertTrue($zlib->user_get(1, 'toto') !== NULL);

        $this->assertTrue($zlib->user_get(2, 'toto') == NULL);

        $this->assertTrue($zlib->user_check(1, 'toto', 'toto'));

        $this->assertFalse($zlib->user_check(0, 'toto', 'toto'));

        $this->assertFalse($zlib->user_get(1, 'toto') == NULL);

        $this->assertTrue(
            $zlib->user_set_attribute(
                1, 'toto', 'options', 'test'));

        $this->assertEquals('test', $zlib->user_get_attribute(
            1, 'toto', 'options'));

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

        $this->assertTrue($zlib->user_change_authorisation(
            1, 'toto', true));

        $this->assertTrue($zlib->user_change_authorisation(
            1, 'toto', false));

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
            $zlib->project_add('test', 'deploy_test'));

        $this->assertFalse(
            $zlib->project_add('test', 'deploy_test'));

        $this->assertTrue(
            $zlib->checkOutput(
                '{"error":"Another project have the same name \'test\'!"}'));

        $this->assertEquals(
            'deploy_test', $zlib->project_get_attribute(1, 'destination'));

        $this->assertEquals(
            NULL, $zlib->project_get_attribute(1, 'failed'));

        $this->assertTrue(
            $zlib->project_set_attribute(1, 'destination', 'deploy_modified'));

        $this->assertEquals(
            'deploy_modified', $zlib->project_get_attribute(1, 'destination'));

        $this->assertFalse(
            $zlib->project_set_attribute(1, 'failed', 'failure'));

        $this->assertEquals(
            $zlib->project_get_id('test'), 1);

        $this->assertTrue($zlib->connect_to_database());

        $this->assertTrue($zlib->project_delete('test'));

        $this->assertFalse($zlib->project_delete('test'));

	    $this->assertTrue(
            $zlib->checkOutput(
                '{"error":"no existing project delete \'test\' in database!"}'));

        $zlib->environment_clean($this->db_name);
    }

    public function test_projects()
    {
	    $zlib = $this->zlib;

	    $this->assertTrue($zlib->connect_to_database());

	    $structure = $zlib->projects_get('t/projects.ini');

	    $this->assertEquals($structure,
			                array(
	                            "test" => array(
		                            "artist" => array(
		                                "name"      => array("db_type" => "VARCHAR", "db_size" => 100),
		                                "surname"   => array("db_type" => "VARCHAR", "db_size" => 100),
		                                "age"       => array("db_type" => "INT_U"),
		                                "subtitle"  => array("db_type" => "VARCHAR", "db_size" => 300),
		                                "biography" => array("db_type" => "TEXT", "db_size" => 1000),
		                                "skill"     => array("db_type" => "VARCHAR", "db_size" => 100)),
		                            "show"   => array(
		                                "name"      => array("db_type" => "VARCHAR", "db_size" => 100),
		                                "date"      => array("db_type" => "DATE"),
		                                "hour"      => array("db_type" => "TIME"),
		                                "location"  => array("db_type" => "VARCHAR", "db_size" => 300)),
		                            "news"   => array(
		                                "name"      => array("db_type" => "VARCHAR", "db_size" => 100),
		                                "date"      => array("db_type" => "DATE"),
		                                "comments"  => array("db_type" => "VARCHAR", "db_size" => 100)),
		                            "album"  => array(
		                                "name"      => array("db_type" => "VARCHAR", "db_size" => 100),
		                                "duration"  => array("db_type" => "INT_U"),
		                                "comments"  => array("db_type" => "TEXT", "db_size" => 1000)),
		                            "music"  => array(
		                                "name"      => array("db_type" => "VARCHAR", "db_size" => 100),
		                                "date"      => array("db_type" => "DATE"),
		                                "duration"  => array("db_type" => "INT_U"),
		                                "comments"  => array("db_type" => "TEXT", "db_size" => 1000)),
		                            "video"  => array(
		                                "name"      => array("db_type" => "VARCHAR", "db_size" => 100),
		                                "date"      => array("db_type" => "DATE"),
		                                "duration"  => array("db_type" => "INT_U"),
		                                "comments"  => array("db_type" => "TEXT", "db_size" => 1000)),
		                            "media"  => array(
		                                "name"      => array("db_type" => "VARCHAR", "db_size" => 100),
		                                "date"      => array("db_type" => "DATE"),
		                                "comments"  => array("db_type" => "TEXT", "db_size" => 1000))),
	                            "test2" => array(
		                            "test1" => array(
		                                "name"      => array("db_type" => "VARCHAR", "db_size" => 25),
		                                "since"     => array("db_type" => "DATE"),
		                                "subtitle"  => array("db_type" => "VARCHAR", "db_size" => 300),
		                                "biography" => array("db_type" => "TEXT", "db_size" => 1000)))));

	    $wrong_tests = array(
	        't/projects/no_project_name.ini',
	        't/projects/wrong_project_name.ini',
	        't/projects/wrong_table_name.ini',
	        't/projects/no_table_name.ini',
	        't/projects/wrong_attribute_name.ini',
	        't/projects/ignore_attribute_name.ini',
	    );

	    foreach ($wrong_tests as $filename) {
	        $this->assertFalse($zlib->projects_get($filename));
	        $this->assertTrue($zlib->checkOutput('{"error":"structure not defined!"}'));
	    }

	    $this->assertFalse($zlib->projects_get('t/projects/unknown.ini'));
	    $this->assertTrue($zlib->checkOutput(
	        '{"error":"Can\'t find projects configuration file \'t/projects/unknown.ini\'!"}'));

	    $zlib->environment_clean($this->db_name);
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

	    $structure = $zlib->projects_get('t/projects.ini');

	    $zlib->environment_clean($this->db_name);

	    $this->assertTrue(
	        $zlib->project_add('test', 'dst'));

	    $project_id = $zlib->project_get_id('test');

	    $zlib->project_id = $project_id;
	    $this->assertEquals($zlib->project_id, 1);

	    # we add a new value here
	    $this->assertTrue(
	        $zlib->value_insert(
		        $project_id,
		        'artist',
		        array('name'      => 'test_name',
		              'surname'   => 'test_surname',
		              'age'       => 27,
		              'subtitle'  => 'test_subtitle',
		              'biography' => 'la vie de ce test sera',
		              'skill'     => 'skill_test')));

	    $result = $zlib->value_get($project_id, 'artist');

	    $row = $zlib->value_fetch($result);

	    $this->assertEquals(1, $row["id"]);
	    $this->assertEquals('test_name', $row["name"]);
	    $this->assertEquals('test_surname', $row["surname"]);
	    $this->assertEquals(27, $row["age"]);
	    $this->assertEquals('test_subtitle', $row["subtitle"]);
	    $this->assertEquals('la vie de ce test sera', $row["biography"]);
	    $this->assertEquals('skill_test', $row["skill"]);

	    # we add a new value again
	    $this->assertTrue(
	        $zlib->value_insert(
		        $project_id,
		        'artist',
		        array('name'      => 'test_name2',
		              'surname'   => 'test_surname2',
		              'age'       => 272,
		              'subtitle'  => 'test_subtitle2',
		              'biography' => 'la vie de ce test sera',
		              'skill'     => 'skill_test2')));

	    # we add a new value again
	    $this->assertTrue(
	        $zlib->value_insert(
		        $project_id,
		        'artist',
		        array('name'      => 'test_name3',
		              'surname'   => 'test_surname3',
		              'age'       => 273,
		              'subtitle'  => 'test_subtitle3',
		              'biography' => 'la vie de ce test sera',
		              'skill'     => 'skill_test3')));

	    $result = $zlib->value_get($project_id, 'artist', NULL, 1, 1);

	    $row = $zlib->value_fetch($result);

	    $this->assertEquals($row["id"], 2);
	    $this->assertEquals($row["name"], 'test_name2');
	    $this->assertEquals($row["surname"], 'test_surname2');
	    $this->assertEquals($row["age"], 272);
	    $this->assertEquals($row["subtitle"], 'test_subtitle2');
	    $this->assertEquals($row["biography"], 'la vie de ce test sera');
	    $this->assertEquals($row["skill"], 'skill_test2');

	    # we add a new value again
	    $this->assertTrue(
	        $zlib->value_update(
		        $project_id, 'artist', 3,
		        array('name'      => 'test_update',
		              'surname'   => 'test_surname-update')));

	    $result = $zlib->value_get($project_id, 'artist', NULL, 1, 2);

	    $row = $zlib->value_fetch($result);

	    $this->assertEquals($row["id"], 3);
	    $this->assertEquals($row["name"], 'test_update');
	    $this->assertEquals($row["surname"], 'test_surname-update');
	    $this->assertEquals($row["age"], 273);
	    $this->assertEquals($row["subtitle"], 'test_subtitle3');
	    $this->assertEquals($row["biography"], 'la vie de ce test sera');
	    $this->assertEquals($row["skill"], 'skill_test3');

	    $this->assertEquals($zlib->table_count($project_id, 'artist'), 3);

	    # we delete the last value
	    $this->assertTrue($zlib->value_delete(
	        $project_id, 'artist', 3));

	    $this->assertFalse(
	        $zlib->value_update(
		        $project_id, 'artist', 3,
		        array('name'      => 'test_update',
		              'surname'   => 'test_surname-update')));

	    $this->assertFalse(
	        $zlib->value_insert(
		        $project_id, 'failed',
		        array('name'      => 'test_name3',
		              'surname'   => 'test_surname3',
		              'age'       => 273,
		              'subtitle'  => 'test_subtitle3',
		              'biography' => 'la vie de ce test sera',
		              'skill'     => 'skill_test3')));

	    $this->assertFalse(
	        $zlib->value_update(
		        $project_id, 'failed', 3,
		        array('name'      => 'test_update',
		              'surname'   => 'test_surname-update')));

	    $zlib->environment_clean($this->db_name);
    }

    function launch_test($zlib, $id, $table, $attribute, $input)
    {
	    print "check '$table $attribute' ";

	    $var_type;
	    $opt_size;

	    if (is_array($input)) {
	        $var_type = $input[0];
	        $opt_size = $input[1];
	        print "($var_type, $opt_size)\n";
	    } else {
	        $var_type = $input;
	        print "($var_type)\n";
	    }

	    $values = $this->get_value_from_format($var_type);

	    if (is_array($values)) {
	        $size = count($values);
	        for ($i=0; $i < $size; $i++) {
		        $this->assertTrue(
		            $this->check_value(
			            $zlib, $id, $table, $attribute, $values[$i]));
	        }
	    }
    }


    function check_value($zlib, $project_id, $table, $attribute, $value)
    {
	    print "=> insert $project_id, $table, $attribute, $value\n";

	    if ($zlib->value_insert(
	        $project_id, $table, array($attribute => $value)) == false) {
	        print "error insert!\n";
	        return false;
	    }

	    $result = $zlib->value_get($project_id, $table, NULL, 1, 0);
	    if ($result == false) {
	        print "error get!\n";
	        return false;
	    }

	    $row = $zlib->value_fetch($result);
	    if ($row == NULL) {
	        print "error fetch!\n";
	        return false;
	    }

	    if ($row->$attribute != $value) {
	        print "error insert '$value', get '". $row->$attribute . "'!\n";
	    }

	    if ($zlib->value_delete($project_id, $table, $row->id) == false) {
	        print "error delete!\n";
	        return false;
	    }

	    return true;
    }

    private function get_value_from_format($format, $opt_size=NULL)
    {
	    switch ($format) {
	        case "TINYINT":
		        return array(-128, 127);
	        case "TINYINT_U":
		        return array(0, 255);
	        case "SMALLINT":
		        return array(-32768, 32767);
	        case "SMALLINT_U":
		        return array(0,  65535);
	        case "MEDIUMINT":
		        return array(-8388608, 8388607);
	        case "MEDIUMINT_U":
		        return array(0,  16777215);
	        case "INT":
	        case "INTEGER":
		        return array(-2147483648,  2147483647);
	        case "INT_U":
	        case "INTEGER_U":
		        return array(0, 4294967295);
	        case "BIGINT":
		        return array(-9223372036854775808,  9223372036854775807);
	        case "BIGINT_U":
		        return array(0, 18446744073709551615);
	        case "FLOAT":
		        return array(-3.40282E+38, -1.5, 0, 1.5,
			                 1.17549E-38, 3.40282E+38);
	        case "FLOAT_U":
		        return array(0, 1.5, 1.175494351E-38, 3.402823466E+38);
	        case "DOUBLE":
		        return array(
		            -1.7976931348623e308,  -1.5, -2.2250738585072E-308,
		            0, 2.2250738585072E-308, 1.5, 1.7976931348623e308);
	        case "DOUBLE_U":
		        return array(0, 2.2250738585072E-308, 1.5, 1.7976931348623e308);
	        case "DECIMAL":
		        return array(-9999999999, -555, 0, 1231, 9999999999);


	        case "DATE":
		        return array('1000-01-01', '2014-09-16', '9999-12-31');
	        case "DATETIME":
		        return array('1000-01-01 00:00:00', '2014-09-16 11:25:15',
			                 '9999-12-31 23:59:59');
	        case "TIMESTAMP":
		        return array('1971-01-01 00:00:01',  '2014-09-16 11:25:15');
	        case "TIME":
		        return array('-838:59:59', '12:21:22', '838:59:59');
	        case "YEAR":
		        return array(1901, 2014, 2155);

	        case "CHAR":
	        case "VARCHAR":
		        return array('toto', '', 'titi est malade');
	        case "TINYTEXT":
	        case "TEXT":
	        case "MEDIUMTEXT":
	        case "LONGTEXT":
	        case "TINYBLOB":
	        case "BLOB":
	        case "MEDIUMBLOB":
	        case "LONGBLOB":
		        return array("turlututu \n chapeau poitu!!");
	    }
    }

    public function test_structure()
    {
	    $zlib = $this->zlib;

        $this->assertTrue(
            $zlib->connect_to_database());

        $zlib->environment_clean($this->db_name);

        $this->assertTrue(
            $zlib->environment_setup($this->db_name, 'test', 'test'));

        $project_name = "test";
        $project_id = 1;

        $this->assertTrue(
            $zlib->project_add($project_name, 'dst'));

        $this->assertEquals(
            $zlib->structure_get($project_id, $project_name), array(
                'artist' => array(
                    'name' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100
                    ),
                    'surname' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100
                    ),
                    'age' => array(
                        'db_type' => "INT_U"
                    ),
                    'subtitle' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 300
                    ),
                    'biography' => array(
                        'db_type' => "TEXT",
                        'db_size' => 1000
                    ),
                    'skill' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100
                    ),
                ),
                'show' => array(
                    'name' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100
                    ),
                    'date' => array(
                        'db_type' => "DATE",
                    ),
                    'hour' => array(
                        'db_type' => "TIME",
                    ),
                    'location' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 300
                    ),
                ),
                'news' => array(
                    'name' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100
                    ),
                    'date' => array(
                        'db_type' => "DATE",
                    ),
                    'comments' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100
                    ),
                ),
                'album' => array(
                    'name' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100
                    ),
                    'duration' => array(
                        'db_type' => "INT_U",
                    ),
                    'comments' => array(
                        'db_type' => "TEXT",
                        'db_size' => 1000
                    ),
                ),
                'music' => array(
                    'name' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100
                    ),
                    'date' => array(
                        'db_type' => "DATE",
                    ),
                    'duration' => array(
                        'db_type' => "INT_U",
                    ),
                    'comments' => array(
                        'db_type' => "TEXT",
                        'db_size' => 1000
                    ),
                ),
                'video' => array(
                    'name' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100,
                    ),
                    'date' => array(
                        'db_type' => "DATE",
                    ),
                    'duration' => array(
                        'db_type' => "INT_U",
                    ),
                    'comments' => array(
                        'db_type' => "TEXT",
                        'db_size' => 1000,
                    ),
                ),
                'media' => array(
                    'name' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100,
                    ),
                    'date' => array(
                        'db_type' => "DATE"
                    ),
                    'comments' => array(
                        'db_type' => "TEXT",
                        'db_size' => 1000,
                    ),
                )));

        // we create simplify this project
        $this->assertTrue(
            $zlib->structure_set(
                $project_id, $project_name,
                array(
                    'artist' => array(
                        'name' => array(
                            'db_type' => "VARCHAR",
                            'db_size' => 100
                        ),
                        'surname' => array(
                            'db_type' => "VARCHAR",
                            'db_size' => 100
                        ),
                    ),
                    'show' => array(
                        'name' => array(
                            'db_type' => "VARCHAR",
                            'db_size' => 100
                        )))));

        $this->assertEquals(
            $zlib->structure_get($project_id, $project_name),
            array(
                'artist' => array(
                    'name' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100
                    ),
                    'surname' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100
                    ),
                ),
                'show' => array(
                    'name' => array(
                        'db_type' => "VARCHAR",
                        'db_size' => 100
                    ))));

        // we insert new elements in this project
	    $this->assertTrue(
	        $zlib->value_insert(
		        $project_id, 'artist',
                array('name'      => 'test_name',
		              'surname'   => 'test_surname')));

	    $result = $zlib->value_get($project_id, 'artist');
	    $row = $zlib->value_fetch($result);
	    $this->assertEquals(
            $row, array("id"     => 1,
                        "name"   => 'test_name',
                        "surname"=> 'test_surname'));

        // we alter the "artist" table
        $this->assertTrue(
            $zlib->structure_set(
                $project_id, $project_name,
                array(
                    'artist' => array(
                        'name' => array(
                            'db_type' => "VARCHAR",
                            'db_size' => 100
                        ),
                        'age' => array(
                            'db_type' => "INT_U"
                        ),
                    ),
                    'show' => array(
                        'name' => array(
                            'db_type' => "VARCHAR",
                            'db_size' => 100
                        )))));

        // we check how is modified the project
	    $result = $zlib->value_get($project_id, 'artist');
	    $row = $zlib->value_fetch($result);
	    $this->assertEquals(
            $row, array("id"   => 1,
                        "name" => 'test_name',
                        "age"  => '0'));

        $zlib->environment_clean($this->db_name);
    }


    public function test_files()
    {
	    $zlib = $this->zlib;

        $this->assertTrue(
            $zlib->connect_to_database());

        $zlib->environment_clean($this->db_name);

	    // we create type file
	    $this->assertTrue($zlib->file_create(
	        'test', 'user', 'type', 'test1', 'type', true));
	    $this->assertTrue(
	        file_exists($zlib->global_path . 'projects/test/test1.type'));

	    // we can't create similar file
	    $this->assertFalse($zlib->file_create(
	        'test', 'user', 'type', 'test1', 'type', true));

	    // we check the type of file
	    $this->assertEquals($zlib->file_get_type('test1.type'), 'type');

	    // we create css file
	    $this->assertTrue($zlib->file_create(
	        'test', 'user', 'css', 'test2', 'css'));
	    $this->assertTrue(
	        file_exists($zlib->global_path . 'projects/test/css/test2.css'));

	    // we can't create similar file
	    $this->assertFalse($zlib->file_create(
	        'test', 'user', 'css', 'test2', 'css'));

	    // we check the type of file
	    $this->assertEquals($zlib->file_get_type('/css/test2.css'), 'css');

	    // we check that we can set the value
	    $this->assertTrue($zlib->file_set('test', 'user', 'test1.type',
					                      'Ceci est un test!'));

	    // we check that we can get the value
	    $this->assertEquals($zlib->file_get('test', 'user', 'test1.type'),
			                'Ceci est un test!');

	    // we check the list of file
	    $this->assertEquals(array(array('mime' => 'text/plain',
					                    'name' => 'css/test2.css',
					                    'type' => 'css',
                                        'in_main_directory' => false),
				                  array('mime' => 'text/plain',
					                    'name' => 'test1.type',
					                    'type' => 'type',
                                        'in_main_directory' => true)),
                            $zlib->file_get_list('test'));

	    // we modify the 2nd file
	    $this->assertTrue($zlib->file_modify(
	        'test', 'user', 'projects/test/css/test2.css', 'type', 'test3', 'type'));
	    $this->assertFalse(
	        file_exists($zlib->global_path . 'projects/test/css/test2.css'));
	    $this->assertTrue(
	        file_exists($zlib->global_path . 'projects/test/type/test3.type'));


	    // we check that we can delete the file
	    $this->assertTrue($zlib->file_delete('test', 'user', '/css/test2.css'));
	    $this->assertFalse(
	        file_exists($zlib->global_path . 'projects/test/css/test2.css'));

	    $this->assertTrue($zlib->file_delete('test', 'user', '/type/test3.type'));
	    $this->assertFalse(
	        file_exists($zlib->global_path . 'projects/test/type/test3.type'));

	    // we delete everything
	    $this->assertTrue($zlib->files_delete('test'));

	    // we check that whole directory has disappeared
	    $this->assertFalse(
	        file_exists($zlib->global_path . 'projects/test'));

	    // we check the type of file
	    $this->assertEquals(
            'css', $zlib->file_get_type('css/test2.hey'));

        // we check 'file_get_detail'
        $this->assertEquals(
            array('type' => 'css',
                  'name' => 'css/test2.hey',
                  'in_main_directory' => false),
            $zlib->file_get_details('css/test2.hey'));

        $this->assertEquals(
            array('type' => 'hey',
                  'name' => 'test2.hey',
                  'in_main_directory' => true),
            $zlib->file_get_details('test2.hey'));

	    $zlib->environment_clean($this->db_name);
    }


    /* public function test_all_values()
       {
       $zlib = new ZeekLibrary();
       $zlib->config(parse_ini_file('tools/check_values.ini'));

       // we establish the connection with the database
       if ($zlib->connect_to_database() == false)
       exit;
       print "\nconnection to database OK\n";

       $project_name = 'fullcheck';

       // we get the structure 1st
       $to_check = $zlib->structure_get($project_name);
       if ($to_check == false)
       exit;

       // we create the project
       if ($zlib->project_add($project_name, 'dst') == false) {
       print "\nimpossible to create project\n";
       exit;
       }

       $id = $zlib->project_get_id($project_name);

       print "project created\n";

       foreach ($to_check as $table => $params) {

       if ($table == 'all') {

       foreach ($params as $attribute => $type) {
       $this->launch_test($zlib, $id, $table, $attribute, $type);
       }

       continue;
       }

       $this->launch_test($zlib, $id, $table, $table, $params[$table]);
       }

       $zlib->environment_clean($zlib->db_name);
       } */
}

?>
