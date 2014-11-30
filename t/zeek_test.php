<?php

require_once 'lib/output.php';
require_once 'lib/zeek.php';

class ExtendsZeek extends Zeek
{
    private $output;
    public $send_email_output = true;

    public function checkOutput($input)
    {
        if ($this->output == $input) {
            $this->output = NULL;
            return true;
        }

        echo "\n expect : " . $input
            . "\n received : " . $this->output . "\n";

        return false;
    }

    public function connect_to_database()
    {
        return $this->zlib->connect_to_database();
    }

    public function database()
    {
        return $this->db;
    }

    public function output($input)
    {
        /* echo "$input \n"; */
        $this->output = $input;
    }


    public function disconnect()
    {
    }

    public function environment_clean()
    {
        $this->zlib->environment_clean($this->zlib->db_name);
    }

    public function send_email($email, $title, $msg)
    {
        return $this->send_email_output;
    }
}

class TestZeek extends PHPUnit_Framework_TestCase
{
    private $zeek;
    protected $test_dbname = 'zeek_test';

    public function setUp()
    {
        $zeek = new ExtendsZeek();
        $zeek->start('t/test.ini');
        $zeek->connect_to_database();

        $this->zeek = $zeek;
    }

    public function test_project()
    {
        $zeek = $this->zeek;

        $this->assertFalse(
            $zeek->connect('no_test', 'no_test', 'no_test'));
            $zeek->checkOutput(
                '{"error":"unexpected project name, login & password!"}');

        $this->assertTrue(
            $zeek->connect('test', 'test', 'test'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

        $this->assertTrue($zeek->project_create('test'));

        $this->assertTrue(
            $zeek->checkOutput('{"redirect":"home.php"}'));

        $this->assertFalse($zeek->project_create('test'));


        $this->assertTrue($zeek->project_delete('test'));
        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Project \'test\' correctly deleted!"}'));

        $this->assertFalse($zeek->project_delete('test'));

        /* we establish the connection with the database */
        $this->assertTrue($zeek->connect_to_database());

        $this->assertTrue(
            $zeek->connect('test', 'test', 'test'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

        $zeek->environment_clean();
    }

    // public function test_user()
    // {
    //     $zeek = $this->zeek;

    //     $this->assertTrue(
    //         $zeek->connect('test', 'test', 'test'));

    //     $this->assertTrue(
    //         $zeek->checkOutput(
    //             '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

    //     $this->assertTrue($zeek->project_create('test'));

    //     $this->assertTrue(
    //         $zeek->checkOutput('{"redirect":"home.php"}'));

    //     $this->assertFalse($zeek->user_add(0,'test', NULL));

    //     $this->assertTrue(
    //         $zeek->checkOutput('{"error":"Expecting valid user email!"}'));

    //     $this->assertFalse($zeek->user_add(0,'test', 'test'));

    //     $this->assertTrue(
    //         $zeek->checkOutput('{"error":"The user \'test\' already exist!"}'));

    //     $this->assertFalse($zeek->user_add(1,'test', 'test_zeek.fr'));

    //     $this->assertTrue(
    //         $zeek->checkOutput(
    //             '{"error":"Expected a valid email adress, received \'test_zeek.fr\'!"}'));

    //     $zeek->send_email_output = false;
    //     $this->assertFalse($zeek->user_add(1,'test', 'test@zeek.fr'));

    //     $this->assertTrue(
    //         $zeek->checkOutput(
    //             '{"error":"Impossible to send email to \'test@zeek.fr\'!"}'));

    //     $zeek->send_email_output = true;
    //     $this->assertTrue($zeek->user_add(1,'test', 'test@zeek.fr'));

    //     $this->assertTrue(
    //         $zeek->checkOutput(
    //             '{"success":"User \'test@zeek.fr\' correctly added & informed!"}'));

    //     $this->assertFalse($zeek->user_add(1,'test', 'test@zeek.fr'));

    //     $this->assertTrue(
    //         $zeek->checkOutput('{"error":"The user \'test@zeek.fr\' already exist!"}'));

    //     $this->assertTrue($zeek->user_delete(1, 'test@zeek.fr'));

    //     $this->assertTrue(
    //         $zeek->checkOutput(
    //             '{"success":"User \'test@zeek.fr\' correctly deleted!"}'));

    //     $this->assertFalse($zeek->user_delete(1, 'test@zeek.fr'));

    //     $zeek->environment_clean();
    // }

    // public function test_data()
    // {
    //     $zeek = $this->zeek;

    //     $this->assertTrue(
    //         $zeek->connect('test', 'test', 'test'));

    //     $this->assertTrue(
    //         $zeek->checkOutput(
    //             '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

    //     $this->assertTrue($zeek->project_create('test'));

    //     $this->assertTrue(
    //         $zeek->checkOutput('{"redirect":"home.php"}'));

    //     $this->assertTrue(
    //         $zeek->data_set('album', 'name=tutu&duration=10&comments=hey'));

    //     $this->assertTrue(
    //         $zeek->checkOutput('{"success":"Value correctly inserted!"}'));

    //     $this->assertFalse(
    //         $zeek->data_set('albu', 'name=tutu&duration=10&comments=hey'));

    //     $result = $zeek->data_get('album', 0, 10);

    //     /* $this->assertFalse( */
    //     /*     $zeek->data_set('album', 'name=tutu&duration=toto&comments=hey')); */

    //     $zeek->environment_clean();
    // }

    // public function test_password()
    // {
    //     $password = $this->zeek->password_generate(9);
    //     $this->assertEquals(strlen($password), 9);

    //     for ($i = 0; $i < 20; $i++) {
    //         $new_password = $this->zeek->password_generate(9);
    //         $this->assertFalse($password == $new_password);
    //     }

    //     $password = $this->zeek->password_generate(8);
    //     $this->assertEquals(strlen($password), 8);
    // }

    // public function test_success()
    // {
    //     $this->zeek->success('toto', array('tutu' => 'titi'));
    //     $this->assertTrue(
    //         $this->zeek->checkOutput(
    //             json_encode(array('success' => 'toto', 'tutu' => 'titi'))));


    //     $this->zeek->success('toto', NULL);
    //     $this->assertTrue(
    //         $this->zeek->checkOutput(
    //             json_encode(array('success' => 'toto'))));

    //     $result = json_decode(json_encode(array('success' => 'toto')));
    //     $this->assertEquals($result->success, 'toto');
    // }

}
?>
