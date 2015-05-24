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
        /* echo "expect : " . $input . "\n"; */

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

	// we connect with bad login & password
        $this->assertFalse(
            $zeek->connect('no_test', 'no_test', 'no_test'));
            $zeek->checkOutput(
                '{"error":"unexpected project name, login & password!"}');

	// we connect with good login & password
        $this->assertTrue(
            $zeek->connect('test', 'test', 'test'));
        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

	// we create a project
        $this->assertTrue($zeek->project_create('test'));
        $this->assertTrue(
            $zeek->checkOutput('{"redirect":"home.php"}'));

	// we create the project again
        $this->assertFalse($zeek->project_create('test'));
	$this->assertTrue(
           $zeek->checkOutput('{"error":"Project already existing!"}'));

        $this->assertTrue(
            $zeek->connect('test', 'test', 'test'));

	// we check the structure
	$zeek->structure_get();
	$this->assertTrue(
            $zeek->checkOutput('{"structure":{"artist":{"name":{"type":"text","size":100},"surname":{"type":"text","size":100},"age":{"type":"number","min":0,"max":4294967295,"step":1},"subtitle":{"type":"text","size":300},"biography":{"type":"text","size":1000},"skill":{"type":"text","size":100}},"show":{"name":{"type":"text","size":100},"date":{"type":"date"},"hour":{"type":"time"},"location":{"type":"text","size":300}},"news":{"name":{"type":"text","size":100},"date":{"type":"date"},"comments":{"type":"text","size":100}},"album":{"name":{"type":"text","size":100},"duration":{"type":"number","min":0,"max":4294967295,"step":1},"comments":{"type":"text","size":1000}},"music":{"name":{"type":"text","size":100},"date":{"type":"date"},"duration":{"type":"number","min":0,"max":4294967295,"step":1},"comments":{"type":"text","size":1000}},"video":{"name":{"type":"text","size":100},"date":{"type":"date"},"duration":{"type":"number","min":0,"max":4294967295,"step":1},"comments":{"type":"text","size":1000}},"media":{"name":{"type":"text","size":100},"date":{"type":"date"},"comments":{"type":"text","size":1000}}}}'));

	// we delete the project
        $this->assertTrue($zeek->project_delete('test'));
        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Project \'test\' correctly deleted!"}'));

	// we delete again the project
        $this->assertFalse($zeek->project_delete('test'));

        // we establish the connection with the database
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

    public function test_data()
    {
        $zeek = $this->zeek;

        $this->assertTrue(
            $zeek->connect('test', 'test', 'test'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

	// create new project
        $this->assertTrue($zeek->project_create('test'));

        $this->assertTrue(
            $zeek->checkOutput('{"redirect":"home.php"}'));

	// create new album element
        $this->assertTrue(
            $zeek->data_set('album', 'name=tutu&duration=10&comments=hey'));

	// check the value has been correctly created
        $this->assertTrue(
            $zeek->checkOutput('{"success":"Value correctly inserted!"}'));

        $zeek->data_get('album', 0, 10);

        $this->assertTrue(
            $zeek->checkOutput('[{"id":"1","name":"tutu","duration":"10","comments":"hey"}]'));

	// try to create a new value on an unkown element
        $this->assertFalse(
            $zeek->data_set('albu', 'name=tutu&duration=10&comments=hey'));

	// try to create album element with wrong parameters : ERROR duration=toto accepted!!
	// $this->assertFalse(
        //   $zeek->data_set('album', 'name=tutu&duration=toto&comments=hey'));

	// try to update the value of this element
        $this->assertTrue(
            $zeek->data_update('album', 1, 'name=toto&duration=20&comments=hoy'));

        $zeek->data_get('album', 0, 10);

        $this->assertTrue(
            $zeek->checkOutput('[{"id":"1","name":"toto","duration":"20","comments":"hoy"}]'));


        $zeek->environment_clean();
    }


    public function test_file()
    {
        $zeek = $this->zeek;

        $this->assertTrue(
            $zeek->connect('test', 'test', 'test'));

	$zeek->file_get_type_list(true);

	$this->assertTrue(
	    $zeek->checkOutput(
		json_encode(array('type_list' => array("abap","actionscript","ada","apache_conf","applescript","asciidoc","assembly_x86","autohotkey","batchfile","c9search","c_cpp","cirru","clojure","cobol","coffee","coldfusion","csharp","css","curly","d","dart","diff","django","dockerfile","dot","eiffel","ejs","elixir","elm","erlang","forth","ftl","gcode","gherkin","gitignore","glsl","golang","groovy","haml","handlebars","haskell","haxe","html","html_ruby","ini","io","jack","jade","java","javascript","json","jsoniq","jsp","jsx","julia","latex","less","liquid","lisp","livescript","logiql","lsl","lua","luapage","lucene","makefile","markdown","matlab","mel","mushcode","mysql","nix","objectivec","ocaml","pascal","perl","pgsql","php","plain_text","powershell","praat","prolog","properties","protobuf","python","r","rdoc","rhtml","ruby","rust","sass","scad","scala","scheme","scss","sh","sjs","smarty","snippets","soy_template","space","sql","stylus","svg","tcl","tex","text","textile","toml","twig","typescript","vala","vbscript","velocity","verilog","vhdl","xml","xquery","yaml")))));

	$this->assertFalse($zeek->file_create(
	    'type', 'to_to', 'extension', false));
	$this->assertTrue(
	    $zeek->checkOutput('{"error":"The filename \'to_to\' should only'
			     . ' contains letters & numbers!"}'));

	$this->assertFalse($zeek->file_create('type', 'toto', 'extension', false));
	$this->assertTrue(
	    $zeek->checkOutput('{"error":"The file type \'type\' is invalid!"}'));

	$this->assertTrue($zeek->file_create('css', 'toto', 'css', false));
	$this->assertTrue(
	    $zeek->checkOutput(
		'{"success":"file \'toto.css\' with type \'css\' created!"}'));

	$this->assertTrue($zeek->file_create('css', 'tutu', 'css', false,
					     'projects/test/css/toto.css'));
	$this->assertTrue(
	    $zeek->checkOutput(
		'{"success":"file \'projects\/test\/css\/toto.css\' stored as \'tutu.css\' with type \'css\' created!"}'));

	$this->assertTrue($zeek->file_delete('css/tutu.css'));
	$this->assertTrue(
	    $zeek->checkOutput(
		'{"success":"The file \'css\/tutu.css\' successfully deleted!"}'));



        $zeek->environment_clean();
    }


    public function test_zeekify()
    {
        $zeek = $this->zeek;

        $this->assertTrue(
            $zeek->connect('test', 'test', 'test'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

	// create new project
        $this->assertTrue($zeek->project_create('test'));

        $this->assertTrue(
            $zeek->checkOutput('{"redirect":"home.php"}'));

	// create new album element
        $this->assertTrue(
            $zeek->data_set('album', 'name=test&duration=100&comments=Tropcool!'));

	// check the value has been correctly created
        $this->assertTrue(
            $zeek->checkOutput('{"success":"Value correctly inserted!"}'));

	// create new album element
        $this->assertTrue(
            $zeek->data_set('album', 'name=tuto&duration=111&comments=ItWorks!'));

	// check the value has been correctly created
        $this->assertTrue(
            $zeek->checkOutput('{"success":"Value correctly inserted!"}'));

	// create new album element
        $this->assertTrue(
            $zeek->data_set('album', 'name=titi&duration=321&comments=notWorking!'));

	// check the value has been correctly created
        $this->assertTrue(
            $zeek->checkOutput('{"success":"Value correctly inserted!"}'));

        $this->assertEquals($zeek->zeekify(
            'begin <zeek  tutu=""    >hey!</zeek> middle '
          . '<zeek table="toto"  toto=""    >hoy!</zeek>'
          . ' Here is the list of albums: '
          . '<zeek table="album" offset="1" size="2"><p>album {{name}} ({{duration}}): {{comment}}</p></zeek> end'), "begin Table name should be defined! middle Table 'toto' not found! Here is the list of albums: <p>album tuto (111): Attribute 'comment' not found!</p><p>album titi (321): Attribute 'comment' not found!</p> end");

        $zeek->environment_clean();
    }

    public function test_deploy()
    {
        $zeek = $this->zeek;

        $this->assertTrue(
            $zeek->connect('test', 'test', 'test'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

	// create new project
        $this->assertTrue($zeek->project_create('test'));

        $this->assertTrue(
            $zeek->checkOutput('{"redirect":"home.php"}'));

        // create toto.css
	$this->assertTrue($zeek->file_create('html', 'index', 'html', true));
	$this->assertTrue(
	    $zeek->checkOutput(
		'{"success":"file \'index.html\' with type \'html\' created!"}'));

        // create css/tutu.css
	$this->assertTrue($zeek->file_create('css', 'test', 'css', false));
	$this->assertTrue(
	    $zeek->checkOutput(
		'{"success":"file \'test.css\' with type \'css\' created!"}'));

        // check test functionality
        $zeek->test();
        $this->assertTrue(
            $zeek->checkOutput('{"href":"projects\/1\/test_test\/index.html"}'));

        // check the files have been correctly set & deploy
        $this->assertTrue($zeek->file_get('test_test', 'index.html'));
        $this->assertTrue(
            $zeek->checkOutput('{"get":"<!DOCTYPE html>\n<html>\n  <head>\n    <meta charset=\"utf-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n    <title>Generic<\/title>\n    <link rel=\"stylesheet\" href=\"generic.css\">\n  <\/head>\n  <body>\n      <!-- now you play!  -->\n  <\/body>\n<\/html>\n","type":"html"}'));

        $this->assertTrue($zeek->file_get('test_test', 'css/test.css'));
        $this->assertTrue(
            $zeek->checkOutput('{"get":"body {\n    \/* now you play! *\/\n}\n","type":"css"}'));

        // delete project
	$this->assertTrue($zeek->project_delete('test'));
        $this->assertTrue(
	    $zeek->checkOutput('{"success":"Project \'test\' correctly deleted!"}'));

        $zeek->environment_clean();
    }

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
