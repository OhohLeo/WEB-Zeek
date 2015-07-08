<?php

class TestDataBaseCommon extends PHPUnit_Framework_TestCase
{
    protected $db;
    protected $test_dbname = 'zeek_test';

    private function connect()
    {
        $db = $this->db;
        $db->connect('localhost', 'zeek_test', 'test', 'test');
        return $db;
    }

    private function clean()
    {
        $this->db->database_delete($this->test_dbname);
    }


    public function test_db()
    {
        $this->assertNotNull($this->db);
    }

    public function test_database()
    {
        $db = $this->connect();

        $this->clean();

        $this->assertFalse(
            $db->database_use($this->test_dbname));

        $this->assertFalse(
            $db->database_check($this->test_dbname));

        $this->assertTrue(
            $db->database_create($this->test_dbname));

        $this->assertTrue(
            $db->database_create($this->test_dbname));

        $this->assertTrue(
            $db->database_check($this->test_dbname));

        $this->assertTrue(
            $db->database_use($this->test_dbname));

        $this->assertFalse(
            $db->table_check('just_id'));

        $this->assertTrue(
            $db->table_create('just_id', NULL));

        $this->assertTrue(
            $db->table_check('just_id'));

        $this->assertTrue(
            $db->table_delete('just_id'));

        $this->assertFalse(
            $db->table_check('just_id'));

        $this->assertFalse(
            $db->row_insert(
                'more_attributes',
                array('first_element' => 'test',
                      'second_element' => 0,
                      'third_element' => 'test')));

        $this->assertFalse(
            $db->row_update('more_attributes', 3,
                            array('second_element' => 6,
                                  'third_element' => 'juliette')));

        $this->assertEquals(
            $db->table_count('more_attributes', '*', NULL), 0);

        $this->assertTrue(
            $db->table_create('more_attributes', array(
                'first_element'  => array('db_type' => 'VARCHAR',
					  'db_size' => 30),
                'second_element' => array('db_type' => 'INT',
					  'db_size' =>  11,
					  'db_default' => 'NOT NULL'),
                'third_element'  => array('db_type' => 'TEXT'))));

        $this->assertTrue(
            $db->table_check('more_attributes'));

        $this->assertEquals(
            $db->table_count('more_attributes', '*', NULL), 0);

        $first_elements = array('toto', 'titi', 'tata', 'tutu');
        $second_elements = array(2, 4, 3, 5);
        $third_elements = array("la vie est belle", "armagueddon",
                                "roméo", "nana");

        for ($i = 0; $i < count($first_elements); $i++) {
            $this->assertTrue(
                $db->row_insert(
                    'more_attributes',
                    array('first_element' => $first_elements[$i],
                          'second_element' => $second_elements[$i],
                          'third_element' => $third_elements[$i])));
        }

        $this->assertEquals(
            $db->table_count('more_attributes', '*', NULL), 4);

        $this->assertEquals(
            $db->table_count(
                'more_attributes', '*', array('second_element' => 3)), 1);

        $id = 0;

        $result = $db->table_view('more_attributes', '*',
                                      array('id', 'ASC'));

        while ($row = $db->handle_result($result)) {
            $this->assertTrue($row->first_element == $first_elements[$id]);
            $this->assertTrue($row->second_element == $second_elements[$id]);
            $this->assertTrue($row->third_element == $third_elements[$id]);
            $this->assertTrue($row->id == ++$id);
        }

        $result = $db->table_view('more_attributes', '*',
                                      array('id', 'DESC'));

        while ($row = $db->handle_result($result)) {
            $this->assertTrue($row->id == $id--);
            $this->assertTrue($row->first_element == $first_elements[$id]);
            $this->assertTrue($row->second_element == $second_elements[$id]);
            $this->assertTrue($row->third_element == $third_elements[$id]);
        }

        $result = $db->table_view('more_attributes',
                                      array('id', 'first_element'),
                                      NULL, NULL, NULL, array('id' => 1));

        while($row = $db->handle_result($result)) {
            $this->assertTrue($row->first_element == $first_elements[0]);
            $this->assertTrue($row->id == 1);
        }

        $result = $db->table_view('more_attributes', '*',
                                      NULL, 2);
        $id = 0;

        while ($id < 2) {
            $row = $db->handle_result($result);
            $this->assertTrue($row->first_element == $first_elements[$id]);
            $this->assertTrue($row->second_element == $second_elements[$id]);
            $this->assertTrue($row->third_element == $third_elements[$id]);
            $this->assertTrue($row->id == ++$id);
        }

        $result = $db->table_view('more_attributes', '*',
                                      NULL, 1, 2);
        $id = 2;

        while ($id < 3) {
            $row = $db->handle_result($result);
            $this->assertTrue($row->first_element == $first_elements[$id]);
            $this->assertTrue($row->second_element == $second_elements[$id]);
            $this->assertTrue($row->third_element == $third_elements[$id]);
            $this->assertTrue($row->id == ++$id);
        }

        $this->assertTrue(
            $db->row_update('more_attributes', 3,
                                array('second_element' => 6,
                                      'third_element' => 'juliette')));

        $this->assertFalse(
            $db->row_update('more_attributes', 118,
                                array('second_element' => 33,
                                      'third_element' => 'failed')));

        $result = $db->table_describe('more_attributes');

        $fields = array('id', 'first_element', 'second_element', 'third_element');
        $types  = array('int(11)', 'varchar(30)', 'int(11)', 'text');
        $keys   = array('PRI');
        $extra  = array('auto_increment');

        while ($row = $db->handle_result($result)) {
            $this->assertEquals($row->Field, array_shift($fields));
            $this->assertEquals($row->Type, array_shift($types));
            $this->assertEquals($row->Null, 'NO');
            $this->assertEquals($row->Extra, array_shift($extra));
            $this->assertEquals($row->Key, array_shift($keys));
            $this->assertEquals($row->Default, NULL);
        }

        $this->assertEquals(
            $db->table_show('more_attributes'), array(
                'id'             => array('Field'   => 'id',
                                          'Type'    => 'int(11)',
                                          'Null'    => 'NO',
                                          'Key'     => 'PRI',
                                          'Default' => NULL,
                                          'Extra'   => 'auto_increment'),
                'first_element'  => array('Field'   => 'first_element',
                                          'Type'    => 'varchar(30)',
                                          'Null'    => 'NO',
                                          'Key'     => '',
                                          'Default' => NULL,
                                          'Extra'   => ''),
                'second_element' => array('Field'   => 'second_element',
                                          'Type'    => 'int(11)',
                                          'Null'    => 'NO',
                                          'Key'     => '',
                                          'Default' => NULL,
                                          'Extra'   => ''),
                'third_element'  => array('Field'   => 'third_element',
                                          'Type'    => 'text',
                                          'Null'    => 'NO',
                                          'Key'     => '',
                                          'Default' => NULL,
                                          'Extra'   => '')
            ));

        // we add more attributes (new columns) to the table
        $this->assertTrue(
            $db->attribute_add('more_attributes', 'test', 'TEXT'));

        $this->assertEquals(
            $db->table_show('more_attributes'), array(
                'id'             => array('Field'   => 'id',
                                          'Type'    => 'int(11)',
                                          'Null'    => 'NO',
                                          'Key'     => 'PRI',
                                          'Default' => NULL,
                                          'Extra'   => 'auto_increment'),
                'first_element'  => array('Field'   => 'first_element',
                                          'Type'    => 'varchar(30)',
                                          'Null'    => 'NO',
                                          'Key'     => '',
                                          'Default' => NULL,
                                          'Extra'   => ''),
                'second_element' => array('Field'   => 'second_element',
                                          'Type'    => 'int(11)',
                                          'Null'    => 'NO',
                                          'Key'     => '',
                                          'Default' => NULL,
                                          'Extra'   => ''),
                'third_element'  => array('Field'   => 'third_element',
                                          'Type'    => 'text',
                                          'Null'    => 'NO',
                                          'Key'     => '',
                                          'Default' => NULL,
                                          'Extra'   => ''),
                'test'           => array('Field'   => 'test',
                                          'Type'    => 'text',
                                          'Null'    => 'NO',
                                          'Key'     => '',
                                          'Default' => NULL,
                                          'Extra'   => ''),
            ));

        // we remove an attribute (column) from the table
        $this->assertTrue(
            $db->attribute_remove('more_attributes', 'test'));

        $this->assertEquals(
            $db->table_show('more_attributes'), array(
                'id'             => array('Field'   => 'id',
                                          'Type'    => 'int(11)',
                                          'Null'    => 'NO',
                                          'Key'     => 'PRI',
                                          'Default' => NULL,
                                          'Extra'   => 'auto_increment'),
                'first_element'  => array('Field'   => 'first_element',
                                          'Type'    => 'varchar(30)',
                                          'Null'    => 'NO',
                                          'Key'     => '',
                                          'Default' => NULL,
                                          'Extra'   => ''),
                'second_element' => array('Field'   => 'second_element',
                                          'Type'    => 'int(11)',
                                          'Null'    => 'NO',
                                          'Key'     => '',
                                          'Default' => NULL,
                                          'Extra'   => ''),
                'third_element'  => array('Field'   => 'third_element',
                                          'Type'    => 'text',
                                          'Null'    => 'NO',
                                          'Key'     => '',
                                          'Default' => NULL,
                                          'Extra'   => ''),
            ));


        $this->assertEquals($db->tables_show($this->test_dbname),
                            array("more_attributes"));

        $this->assertTrue(
            $db->table_create('just_id', NULL));

        $this->assertEquals($db->tables_show($this->test_dbname),
                            array("just_id", "more_attributes"));

        $this->assertTrue(
            $db->database_delete($this->test_dbname));

        $this->assertFalse($db->tables_show($this->test_dbname));

        $this->assertFalse(
            $db->table_show('more_attributes'));

        $this->assertTrue(
            $db->database_delete($this->test_dbname));

        $this->assertFalse(
            $db->database_check($this->test_dbname));

        $this->assertFalse(
            $db->database_use($this->test_dbname));
    }

    public function test_value()
    {
        $db = $this->connect();

	$this->assertFalse(
	    $db->check_integer(0.45, -125, 127));
	$this->assertFalse(
	    $db->check_integer(-126, -125, 127));
	$this->assertFalse(
	    $db->check_integer(128, -125, 127));
	$this->assertTrue(
	    $db->check_integer(123, -125, 127));
	$this->assertTrue(
	    $db->check_integer(-125, -125, 127));
	$this->assertTrue(
	    $db->check_integer(10, 0, 255));
	$this->assertTrue(
	    $db->check_integer(127, 0, 255));
	$this->assertTrue(
	    $db->check_integer(255, 0, 255));
	$this->assertTrue(
	    $db->check_integer(0, 0, 255));

	$this->assertTrue(
	    $db->check_text('ceci est un texte valide'));
	$this->assertTrue(
	    $db->check_text('ceci drop est un texte table valide'));
	$this->assertFalse(
	    $db->check_text("ceci n'est pas un texte valide drop database"));
	$this->assertFalse(
	    $db->check_text("toto est à la plage drop table derrière"));
	$this->assertFalse(
	    $db->check_text("toto est à la plage INSERT INTO derrière"));

	$this->assertTrue(
	    $db->check_value('DATE', '1986-05-13'));
	$this->assertFalse(
	    $db->check_value('DATE', 'toto est à la plage'));
	$this->assertFalse(
	    $db->check_value('DATE', '1986-13-13'));
	$this->assertFalse(
	    $db->check_value('DATE', '1986-05-32'));

	$this->assertTrue(
	    $db->check_value('TIME', '10:08:30'));
	$this->assertFalse(
	    $db->check_value('TIME', 'toto est à la plage'));
	$this->assertFalse(
	    $db->check_value('TIME', '25:08:30'));
	$this->assertFalse(
	    $db->check_value('TIME', '10:61:30'));
	$this->assertFalse(
	    $db->check_value('TIME', '10:08:61'));

	$this->assertTrue(
	    $db->check_value('DATETIME', '1986-05-13 10:08:30'));
	$this->assertFalse(
	    $db->check_value('DATETIME', 'toto est à la plage'));
	$this->assertFalse(
	    $db->check_value('DATETIME', '1986-05-13 25:08:30'));
	$this->assertFalse(
	    $db->check_value('DATETIME', '1986-05-13 10:61:30'));
	$this->assertFalse(
	    $db->check_value('DATETIME', '1986-05-13 10:08:61'));
	$this->assertFalse(
	    $db->check_value('DATETIME', '1986-13-13 10:08:21'));
	$this->assertFalse(
	    $db->check_value('DATETIME', '10000-05-13 10:08:21'));
	$this->assertFalse(
	    $db->check_value('DATETIME', '1986-05-32 10:08:21'));
    }

    public function test_attribute()
    {
        $db = $this->connect();

        $this->clean();

        $this->assertTrue(
            $db->database_create($this->test_dbname));

        $this->assertTrue(
            $db->database_use($this->test_dbname));

        $table_name = 'attributes';

        $this->assertTrue(
            $db->table_create($table_name, NULL));

        $check = array("TINYINT", "TINYINT_U", "SMALLINT",
                       "SMALLINT_U", "MEDIUMINT", "MEDIUMINT_U",
                       "INT", "INT_U", "BIGINT", "BIGINT_U",
                       "DECIMAL", "INTEGER", "FLOAT",
                       "DOUBLE", "REAL", "DATE", "TIME",
                       "TIMESTAMP", "DATETIME", "YEAR",
                       "CHAR", array(
                           "db_type" => "VARCHAR",
                           "db_size" => 10),
                       "TINYTEXT", "TEXT", "MEDIUMTEXT",
                       "LONGTEXT", "TINYBLOB", "BLOB", "MEDIUMBLOB",
                       "LONGBLOB");

        foreach ($check as $type)
        {
            $this->assertTrue($db->attribute_add(
                $table_name, "test", $type));

            $internal = $db->table_show($table_name);

            $this->assertTrue($db->attribute_check(
                $internal["test"], $type));

            $this->assertTrue($db->attribute_remove(
                $table_name, "test"));
        }

        $this->assertTrue(
            $db->database_delete($this->test_dbname));

        $this->clean();
    }
}
?>
