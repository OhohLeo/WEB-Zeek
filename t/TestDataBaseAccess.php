<?php

require_once 'lib/database_access.php';

class ExtendsDataBaseAccess extends DataBaseAccess
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

class TestDataBaseAccess extends PHPUnit_Framework_TestCase
{
    private $access;
    private $test_dbname = 'zeek_test';

    private function connect()
    {
        $access = $this->access;
        $access->connect('localhost', 'zeek_test', 'test', 'test');
        return $access;
    }

    private function clean()
    {
        $this->access->database_delete($this->test_dbname);
    }

    public function setUp()
    {
        $this->access = new ExtendsDataBaseAccess();
    }

    public function test_access()
    {
        $this->assertNotNull($this->access);
    }

    public function test_connect()
    {
        $access = $this->access;

        $this->assertTrue(
            $access->connect('localhost', $this->test_dbname, 'test', 'test'));

        $this->assertTrue(
            $access->checkOutput(NULL));

        $this->assertFalse(
            $access->connect('localhost', $this->test_dbname, 'test', 'tes'));

        $this->assertTrue(
            $access->checkOutput(
                "Impossible to connect to 'localhost' with MySQL SQLSTATE[28000] [1045] "
                . "Access denied for user 'test'@'localhost'"
                . " (using password: YES)"));

        $this->assertFalse(
            $access->connect('localhost', $this->test_dbname, 'tes', 'test'));

        $this->assertTrue(
            $access->checkOutput(
                "Impossible to connect to 'localhost' with MySQL SQLSTATE[28000] [1045] "
                . "Access denied for user 'tes'@'localhost'"
                . " (using password: YES)"));
    }

    public function test_database()
    {
        $access = $this->connect();

        $this->clean();

        $this->assertFalse(
            $access->database_use($this->test_dbname));

        $this->assertFalse(
            $access->database_check($this->test_dbname));

        $this->assertTrue(
            $access->database_create($this->test_dbname));

        $this->assertTrue(
            $access->database_create($this->test_dbname));

        $this->assertTrue(
            $access->database_check($this->test_dbname));

        $this->assertTrue(
            $access->database_use($this->test_dbname));

        $this->assertFalse(
            $access->table_check('just_id'));

        $this->assertTrue(
            $access->table_create('just_id', NULL));

        $this->assertTrue(
            $access->table_check('just_id'));

        $this->assertTrue(
            $access->table_delete('just_id'));

        $this->assertFalse(
            $access->table_check('just_id'));

        $this->assertFalse(
            $access->row_insert(
                'more_attributes',
                array('first_element' => 'test',
                      'second_element' => 0,
                      'third_element' => 'test')));

        $this->assertFalse(
            $access->row_update('more_attributes', 3,
                                array('second_element' => 6,
                                'third_element' => 'juliette')));

        $this->assertEquals(
            $access->table_count('more_attributes', '*', NULL), 0);

        $this->assertTrue(
            $access->table_create('more_attributes', array(
                'first_element'  => array('VARCHAR', 30),
                'second_element' => array('INT', 11, 'NOT NULL'),
                'third_element'  => 'TEXT')));

        $this->assertTrue(
            $access->table_check('more_attributes'));

        $this->assertEquals(
            $access->table_count('more_attributes', '*', NULL), 0);

        $first_elements = array('toto', 'titi', 'tata', 'tutu');
        $second_elements = array(2, 4, 3, 5);
        $third_elements = array("la vie est belle", "armagueddon",
                                "rom�o", "nana");

        for ($i = 0; $i < count($first_elements); $i++) {
            $this->assertTrue(
                $access->row_insert(
                    'more_attributes',
                    array('first_element' => $first_elements[$i],
                          'second_element' => $second_elements[$i],
                          'third_element' => $third_elements[$i])));
        }

        $this->assertEquals(
            $access->table_count('more_attributes', '*', NULL), 4);

        $this->assertEquals(
            $access->table_count(
                'more_attributes', '*', array('second_element' => 3)), 1);

        $id = 0;

        $result = $access->table_view('more_attributes', '*',
                                      array('id', 'ASC'), NULL, NULL, NULL);

        while ($row = $result->fetch()) {
            $this->assertTrue($row->first_element == $first_elements[$id]);
            $this->assertTrue($row->second_element == $second_elements[$id]);
            $this->assertTrue($row->third_element == $third_elements[$id]);
            $this->assertTrue($row->id == ++$id);
        }

        $result = $access->table_view('more_attributes', '*',
                                      array('id', 'DESC'), NULL, NULL, NULL);

        while ($row = $result->fetch()) {
            $this->assertTrue($row->id == $id--);
            $this->assertTrue($row->first_element == $first_elements[$id]);
            $this->assertTrue($row->second_element == $second_elements[$id]);
            $this->assertTrue($row->third_element == $third_elements[$id]);
        }

        $result = $access->table_view('more_attributes',
                                      array('id', 'first_element'),
                                      NULL, NULL, NULL, array('id' => 1));

        while($row = $result->fetch()) {
            $this->assertTrue($row->first_element == $first_elements[0]);
            $this->assertTrue($row->id == 1);
        }

        $result = $access->table_view('more_attributes', '*',
                                      NULL, 2, NULL, NULL);
        $id = 0;

        while ($id < 2) {
            $row = $result->fetch();
            $this->assertTrue($row->first_element == $first_elements[$id]);
            $this->assertTrue($row->second_element == $second_elements[$id]);
            $this->assertTrue($row->third_element == $third_elements[$id]);
            $this->assertTrue($row->id == ++$id);
        }

        $result = $access->table_view('more_attributes', '*',
                                      NULL, 1, 2, NULL);
        $id = 2;

        while ($id < 3) {
            $row = $result->fetch();
            $this->assertTrue($row->first_element == $first_elements[$id]);
            $this->assertTrue($row->second_element == $second_elements[$id]);
            $this->assertTrue($row->third_element == $third_elements[$id]);
            $this->assertTrue($row->id == ++$id);
        }


        $this->assertTrue(
            $access->row_update('more_attributes', 3,
                                array('second_element' => 6,
                                      'third_element' => 'juliette')));

        $this->assertFalse(
            $access->row_update('more_attributes', 118,
                                array('second_element' => 33,
                                      'third_element' => 'failed')));

        $result = $access->table_describe('more_attributes');

        $fields = array('id', 'first_element', 'second_element', 'third_element');
        $types  = array('int(11)', 'varchar(30)', 'int(11)', 'text');
        $keys   = array('PRI');
        $extra  = array('auto_increment');

        while ($row = $result->fetch()) {
            $this->assertEquals($row->Field, array_shift($fields));
            $this->assertEquals($row->Type, array_shift($types));
            $this->assertEquals($row->Null, 'NO');
            $this->assertEquals($row->Extra, array_shift($extra));
            $this->assertEquals($row->Key, array_shift($keys));
            $this->assertEquals($row->Default, NULL);
        }

        $this->assertTrue(
            $access->database_delete($this->test_dbname));

        $this->assertTrue(
            $access->database_delete($this->test_dbname));

        $this->assertFalse(
            $access->database_check($this->test_dbname));

        $this->assertFalse(
            $access->database_use($this->test_dbname));
    }
}
?>