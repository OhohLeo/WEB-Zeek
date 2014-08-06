<?php

require_once 'lib/database_mysql.php';

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

class TestOldMySQL extends PHPUnit_Framework_TestCase
{
    private $db;
    private $test_dbname = 'zeek_test';

    private function connect()
    {
        $db = $this->db;
        $db->connect('localhost', NULL, 'test', 'test');
        return $db;
    }

    private function clean()
    {
        $this->db->database_delete($this->test_dbname);
    }

    public function setUp()
    {
        $this->db = new ExtendsOldMySQL();
    }


    public function test_db()
    {
        $this->assertNotNull($this->db);
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
                "Impossible to connect to 'localhost' with MySQL mysql_connect(): Access denied for user 'test'@'localhost' (using password: YES)"));

        $this->assertFalse(
            $db->connect('localhost', NULL, 'tes', 'test'));

        $this->assertTrue(
            $db->checkOutput(
                "Impossible to connect to 'localhost' with MySQL mysql_connect(): Access denied for user 'tes'@'localhost' (using password: YES)"));
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
                'first_element'  => array('VARCHAR', 30),
                'second_element' => array('INT', 11, 'NOT NULL'),
                'third_element'  => 'TEXT')));

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
                    array('first_element'  => $first_elements[$i],
                          'second_element' => $second_elements[$i],
                          'third_element'  => $third_elements[$i])));
        }

        $this->assertEquals(
            $db->table_count('more_attributes', '*', NULL), 4);

        $this->assertEquals(
            $db->table_count(
                'more_attributes', '*', array('second_element' => 3)), 1);

        $id = 0;

        $result = $db->table_view('more_attributes', '*',
                                      array('id', 'ASC'), NULL, NULL, NULL);

        while ($row = mysql_fetch_assoc($result)) {
            $this->assertTrue($row['first_element'] == $first_elements[$id]);
            $this->assertTrue($row['second_element'] == $second_elements[$id]);
            $this->assertTrue($row['third_element'] == $third_elements[$id]);
            $this->assertTrue($row['id'] == ++$id);
        }

        $result = $db->table_view('more_attributes', '*',
                                      array('id', 'DESC'), NULL, NULL, NULL);

        while ($row = mysql_fetch_assoc($result)) {
            $this->assertTrue($row['id'] == $id--);
            $this->assertTrue($row['first_element'] == $first_elements[$id]);
            $this->assertTrue($row['second_element'] == $second_elements[$id]);
            $this->assertTrue($row['third_element'] == $third_elements[$id]);
        }

        $result = $db->table_view('more_attributes',
                                      array('id', 'first_element'),
                                      NULL, NULL, NULL, array('id' => 1));

        while ($row = mysql_fetch_assoc($result)) {
            $this->assertTrue($row['first_element'] == $first_elements[0]);
            $this->assertTrue($row['id'] == 1);
        }

        $result = $db->table_view('more_attributes', '*',
                                      NULL, 2, NULL, NULL);
        $id = 0;

        while ($id < 2) {
            $row = mysql_fetch_assoc($result);
            $this->assertTrue($row['first_element'] == $first_elements[$id]);
            $this->assertTrue($row['second_element'] == $second_elements[$id]);
            $this->assertTrue($row['third_element'] == $third_elements[$id]);
            $this->assertTrue($row['id'] == ++$id);
        }

        $result = $db->table_view('more_attributes', '*',
                                      NULL, 1, 2, NULL);
        $id = 2;

        while ($id < 3) {
            $row = mysql_fetch_assoc($result);
            $this->assertTrue($row['first_element'] == $first_elements[$id]);
            $this->assertTrue($row['second_element'] == $second_elements[$id]);
            $this->assertTrue($row['third_element'] == $third_elements[$id]);
            $this->assertTrue($row['id'] == ++$id);
        }

        $this->assertTrue(
            $db->row_update('more_attributes', 3,
                                array('second_element' => 6,
                                      'third_element' => 'juliette')));

        /* $this->assertFalse( */
        /*     $db->row_update('more_attributes', 118, */
        /*                         array('second_element' => 33, */
        /*                               'third_element' => 'failed'))); */

        $result = $db->table_describe('more_attributes');

        $fields = array('id', 'first_element', 'second_element', 'third_element');
        $types  = array('int(11)', 'varchar(30)', 'int(11)', 'text');
        $keys   = array('PRI');
        $extra  = array('auto_increment');

        while ($row = mysql_fetch_assoc($result)) {
            $this->assertEquals($row['Field'], array_shift($fields));
            $this->assertEquals($row['Type'], array_shift($types));
            $this->assertEquals($row['Null'], 'NO');
            $this->assertEquals($row['Extra'], array_shift($extra));
            $this->assertEquals($row['Key'], array_shift($keys));
            $this->assertEquals($row['Default'], NULL);
        }

        $this->assertTrue(
            $db->database_delete($this->test_dbname));

        $this->assertTrue(
            $db->database_delete($this->test_dbname));

        $this->assertFalse(
            $db->database_check($this->test_dbname));

        $this->assertFalse(
            $db->database_use($this->test_dbname));
    }

}