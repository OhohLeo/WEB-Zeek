<?php

require_once 't/zeek_extends.php';

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

        $this->assertTrue(
            $zeek->structure_enable(true));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Structure correctly enabled!"}'));

    	// we check the structure
    	$zeek->structure_get();
    	$this->assertTrue(
            $zeek->checkOutput('{"structure":{"artist":{"name":{"type":"text","size":100,"db_type":"VARCHAR","db_size":100},"surname":{"type":"text","size":100,"db_type":"VARCHAR","db_size":100},"age":{"type":"number","min":0,"max":4294967295,"step":1,"db_type":"INT_U"},"subtitle":{"type":"text","size":300,"db_type":"VARCHAR","db_size":300},"biography":{"type":"textarea","size":1000,"db_type":"TEXT","db_size":1000},"skill":{"type":"text","size":100,"db_type":"VARCHAR","db_size":100}},"show":{"name":{"type":"text","size":100,"db_type":"VARCHAR","db_size":100},"date":{"type":"date","db_type":"DATE"},"hour":{"type":"time","db_type":"TIME"},"location":{"type":"text","size":300,"db_type":"VARCHAR","db_size":300}},"news":{"name":{"type":"text","size":100,"db_type":"VARCHAR","db_size":100},"date":{"type":"date","db_type":"DATE"},"comments":{"type":"text","size":100,"db_type":"VARCHAR","db_size":100}},"album":{"name":{"type":"text","size":100,"db_type":"VARCHAR","db_size":100},"duration":{"type":"number","min":0,"max":4294967295,"step":1,"db_type":"INT_U"},"comments":{"type":"textarea","size":1000,"db_type":"TEXT","db_size":1000}},"music":{"name":{"type":"text","size":100,"db_type":"VARCHAR","db_size":100},"date":{"type":"date","db_type":"DATE"},"duration":{"type":"number","min":0,"max":4294967295,"step":1,"db_type":"INT_U"},"comments":{"type":"textarea","size":1000,"db_type":"TEXT","db_size":1000}},"video":{"name":{"type":"text","size":100,"db_type":"VARCHAR","db_size":100},"date":{"type":"date","db_type":"DATE"},"duration":{"type":"number","min":0,"max":4294967295,"step":1,"db_type":"INT_U"},"comments":{"type":"textarea","size":1000,"db_type":"TEXT","db_size":1000}},"media":{"name":{"type":"text","size":100,"db_type":"VARCHAR","db_size":100},"date":{"type":"date","db_type":"DATE"},"comments":{"type":"textarea","size":1000,"db_type":"TEXT","db_size":1000}}}}'));

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


    public function test_structure()
    {
        $zeek = $this->zeek;

        $this->assertTrue(
            $zeek->connect('test', 'test', 'test'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

        $this->assertTrue($zeek->project_create('test'));

        $this->assertTrue(
            $zeek->checkOutput('{"redirect":"home.php"}'));

        $this->assertFalse(
            $zeek->structure_enable(false));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"error":"Structure already disabled!"}'));

        // we can check the activation of the structure
        $this->assertFalse($zeek->structure_is_enabled());

        $this->assertTrue(
            $zeek->checkOutput(
                '{"error":"Structure should be enabled!"}'));

        $this->assertTrue(
            $zeek->structure_enable(true));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Structure correctly enabled!"}'));

        $this->assertFalse(
            $zeek->structure_enable(true));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"error":"Structure already enabled!"}'));

        $this->assertTrue($zeek->option_get('plugins'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"zeekify":true,"MinifyCss":true,"MinifyJs":true}'));

        $this->assertTrue(
            $zeek->structure_enable(false));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Structure correctly disabled!"}'));

        $this->assertTrue($zeek->option_get('plugins'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"zeekify":"disabled","MinifyCss":true,"MinifyJs":true}'));

    	$this->assertFalse($zeek->structure_get_list(true));
        $this->assertTrue(
            $zeek->checkOutput(
                '{"error":"Structure should be enabled!"}'));

        $this->assertFalse(
            $zeek->structure_set(
                '{"console":{"test":{"sp_type":"TITLE","db_type":"","db_size":""}}}'));
        $this->assertTrue(
            $zeek->checkOutput(
                '{"error":"Structure should be enabled!"}'));

        $this->assertTrue(
            $zeek->structure_enable(true));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Structure correctly enabled!"}'));

        // we check the structure
    	$zeek->structure_get_list(true);
    	$this->assertTrue(
            $zeek->checkOutput('{"list":["TINYINT","TINYINT_U","SMALLINT","SMALLINT_U","MEDIUMINT","MEDIUMINT_U","INT","INT_U","BIGINT","BIGINT_U","DECIMAL","INTEGER","FLOAT","DOUBLE","REAL","DATE","TIME","DATETIME","YEAR","CHAR","VARCHAR","TINYTEXT","TEXT","MEDIUMTEXT","LONGTEXT","TINYBLOB","BLOB","MEDIUMBLOB","LONGBLOB","contents:images","contents:audio","contents:video","contents:application"]}'));

        $zeek->structure_get_list(false);
    	$this->assertTrue(
            $zeek->checkOutput('{"list":["TITLE","TEXT","INTEGER","NUMBER","DATE","TIME","YEAR","DATETIME","contents:images","contents:audio","contents:video","contents:application"]}'));

        // we check that it is not possible to dynamically modify the project structure
        // in this kind of project
        $this->assertFalse(
            $zeek->structure_set(
                '{"console":{"test":{"sp_type":"TITLE","db_type":"","db_size":""}}}'));

    	$this->assertTrue(
            $zeek->checkOutput(
                '{"error":"Deactivate project path to dynamically modify structure!"}'));

        $zeek->environment_clean();
    }

    public function test_user()
    {
        $zeek = $this->zeek;

        $this->assertTrue(
            $zeek->connect('test', 'test', 'test'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

        $this->assertTrue($zeek->project_create('test'));

        $this->assertTrue(
            $zeek->checkOutput('{"redirect":"home.php"}'));

        $this->assertFalse($zeek->user_add(0,'test', NULL, false));

        $this->assertTrue(
            $zeek->checkOutput('{"error":"Expecting valid user email!"}'));

        $this->assertFalse($zeek->user_add(1,'test', 'test_zeek.fr', false));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"error":"Expected a valid email adress, received \'test_zeek.fr\'!"}'));

        $zeek->send_email_output = false;
        $this->assertFalse($zeek->user_add(1,'test', 'test@zeek.fr', false));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"error":"Impossible to send email to \'test@zeek.fr\'!"}'));

        $zeek->send_email_output = true;
        $this->assertTrue($zeek->user_add(1,'test', 'test@zeek.fr', false));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"User \'test@zeek.fr\' correctly added & informed!"}'));

        $this->assertFalse($zeek->user_add(1,'test', 'test@zeek.fr', false));

        $this->assertTrue(
            $zeek->checkOutput('{"error":"The user \'test@zeek.fr\' already exist!"}'));

        $this->assertTrue($zeek->user_add(1,'test', 'test2@zeek.fr', false));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"User \'test2@zeek.fr\' correctly added & informed!"}'));

        $this->assertTrue($zeek->user_add(1,'test', 'test3@zeek.fr', false));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"User \'test3@zeek.fr\' correctly added & informed!"}'));

        $this->assertTrue($zeek->users_get_list(1));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"users":["test@zeek.fr","test2@zeek.fr","test3@zeek.fr"]}'));

        // we set options to the user
        $this->assertTrue(
            $zeek->user_get_tests(1, 'test@zeek.fr'));

        $this->assertTrue(
            $zeek->checkOutput('{"MinifyCss":true,"MinifyJs":true}'));

        $this->assertTrue($zeek->user_set_tests(1, 'test@zeek.fr', '{"MinifyCss": false,"MinifyJs":true}'));

        $this->assertTrue(
            $zeek->checkOutput('{"success":"User option \'test\' successfully written!"}'));

        $this->assertTrue(
            $zeek->user_get_tests(1, 'test@zeek.fr'));

        $this->assertTrue(
            $zeek->checkOutput('{"MinifyCss":false,"MinifyJs":true}'));

        $this->assertFalse(
            $zeek->user_set_tests(1, 'test@zeek.fr', '{"MinifyCss":false,"MinifyJs":true,"zeekify":true}'));

        $this->assertTrue(
            $zeek->checkOutput('{"error":"Unknown \'zeekify\' plugin or invalid status \'1\'!"}'));


        // we delete users now
        $this->assertTrue($zeek->user_delete(1, 'test@zeek.fr'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"User \'test@zeek.fr\' correctly deleted!"}'));

        $this->assertFalse($zeek->user_delete(1, 'test@zeek.fr'));

        $this->assertTrue($zeek->users_get_list(1));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"users":["test2@zeek.fr","test3@zeek.fr"]}'));

        $this->assertTrue($zeek->user_delete(1, 'test2@zeek.fr'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"User \'test2@zeek.fr\' correctly deleted!"}'));

        $this->assertTrue($zeek->user_delete(1, 'test3@zeek.fr'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"User \'test3@zeek.fr\' correctly deleted!"}'));

        $this->assertTrue($zeek->users_get_list(1));

        $this->assertTrue($zeek->checkOutput('{"users":[]}'));

        $zeek->environment_clean();
    }

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
        //$this->assertFalse(
        //$zeek->data_set('album', 'name=tutu&duration=toto&comments=hey'));
        //
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
    	        json_encode(array('type_list' => array("abap","actionscript","ada","apache_conf","applescript","asciidoc","assembly_x86","autohotkey","batchfile","c9search","c_cpp","cirru","clojure","cobol","coffee","coldfusion","csharp","css","curly","d","dart","diff","django","dockerfile","dot","eiffel","ejs","elixir","elm","erlang","forth","ftl","gcode","gherkin","gitignore","glsl","golang","groovy","haml","handlebars","haskell","haxe","html","html_ruby","ini","io","jack","jade","java","javascript","js", "json","jsoniq","jsp","jsx","julia","latex","less","liquid","lisp","livescript","logiql","lsl","lua","luapage","lucene","makefile","markdown","matlab","mel","mushcode","mysql","nix","objectivec","ocaml","pascal","perl","pgsql","php","plain_text","powershell","praat","prolog","properties","protobuf","python","r","rdoc","rhtml","ruby","rust","sass","scad","scala","scheme","scss","sh","sjs","smarty","snippets","soy_template","space","sql","stylus","svg","tcl","tex","text","textile","toml","twig","typescript","vala","vbscript","velocity","verilog","vhdl","xml","xquery","yaml")))));

        $this->assertFalse($zeek->file_create(
    	    'type', 'to_to', 'extension', false));
        //$this->assertTrue(
        //	    $zeek->checkOutput('{"error":"The filename \'to_to\' should only'
        //			     . ' contains letters & numbers!"}'));
        //
        $this->assertFalse($zeek->file_create('type', 'toto', 'extension', false));
        $this->assertTrue(
    	    $zeek->checkOutput('{"error":"The file type \'type\' is invalid!"}'));

        $this->assertTrue($zeek->file_create('css', 'toto', 'css', false));
        $this->assertTrue(
    	    $zeek->checkOutput(
    	        '{"success":"file \'toto.css\' with type \'css\' created!"}'));

        $this->assertTrue($zeek->file_set('test', 'toto.css', ' \d \e'));
        $this->assertTrue(
            $zeek->checkOutput('{"success":"/home/lmartin/zeek/projects//toto.css correctly updated"}'));

        $this->assertTrue($zeek->file_get('test', 'toto.css'));
        $this->assertTrue(
            $zeek->checkOutput('{"get":" \\\d \\\e","type":"css"}'));

        $this->assertTrue($zeek->file_create('css', 'tutu', 'css', false,
    					     'projects/css/toto.css'));
        $this->assertTrue(
    	    $zeek->checkOutput(
    	        '{"success":"file \'projects/css/toto.css\' stored as \'tutu.css\' with type \'css\' created!"}'));

        $this->assertTrue($zeek->file_delete('css/tutu.css'));
        $this->assertTrue(
    	    $zeek->checkOutput(
    	        '{"success":"The file \'css/tutu.css\' successfully deleted!"}'));

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
        $zeek->test('{"MinifyCss":false,"MinifyJs":true}');
        $this->assertTrue(
            $zeek->checkOutput('{"href":"projects/1/TEST/test"}'));

        // check the files have been correctly set & deploy
        $this->assertTrue($zeek->file_get('TEST_test', 'index.html'));
        $this->assertTrue(
            $zeek->checkOutput('{"get":"<!DOCTYPE html>\n<html>\n  <head>\n    <meta charset=\"utf-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n    <title>Generic</title>\n    <link rel=\"stylesheet\" href=\"generic.css\">\n  </head>\n  <body>\n      <!-- now you play!  -->\n  </body>\n</html>\n","type":"html"}'));

        $this->assertTrue($zeek->file_get('TEST_test', 'css/test.css'));
        $this->assertTrue(
            $zeek->checkOutput('{"get":"body {\n    /* now you play! */\n}\n","type":"css"}'));

        // delete project
        $this->assertTrue($zeek->project_delete('test'));
        $this->assertTrue(
    	    $zeek->checkOutput('{"success":"Project \'test\' correctly deleted!"}'));

        $zeek->environment_clean();
    }

    public function test_contents()
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

        $this->assertTrue($zeek->contents_get_type_list());

        $this->assertTrue($zeek->checkOutput(
            '{"content_types":{"images":["img","image/*","#FF0000"],"audio":["audio","audio/*","#00FF00"],"video":["video","video/*","#0000FF"],"application":["app","application/*","#000000"]}}'));

        $this->assertTrue(
            $zeek->contents_set_type("test", "t", "*/*"));

        $this->assertTrue($zeek->contents_get_type_list());

        $this->assertTrue(
            $zeek->checkOutput(
                '{"content_types":{"images":["img","image/*","#FF0000"],"audio":["audio","audio/*","#00FF00"],"video":["video","video/*","#0000FF"],"application":["app","application/*","#000000"],"test":["t","*/*",null]}}'));

        $this->assertTrue(
            $zeek->contents_modify_type("test", "#111111"));

        $this->assertTrue(
            $zeek->checkOutput('{"success":"Content type correctly modified!"}'));

        $this->assertTrue($zeek->contents_get_type_list());

        $this->assertTrue(
            $zeek->checkOutput(
                '{"content_types":{"images":["img","image/*","#FF0000"],"audio":["audio","audio/*","#00FF00"],"video":["video","video/*","#0000FF"],"application":["app","application/*","#000000"],"test":["t","*/*","#111111"]}}'));

        $this->assertTrue($zeek->contents_unset_type("test"));

        $this->assertTrue(
            $zeek->checkOutput('{"success":"Content type correctly deleted!"}'));


        $this->assertTrue($zeek->contents_get_type_list());

        $this->assertTrue($zeek->checkOutput(
            '{"content_types":{"images":["img","image/*","#FF0000"],"audio":["audio","audio/*","#00FF00"],"video":["video","video/*","#0000FF"],"application":["app","application/*","#000000"]}}'));


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

        $this->assertEquals(
            "begin Zeek name should be defined! middle Zeek 'toto' not found! Here is the list of albums: <p>album tuto (111): Attribute 'comment' not found!</p><p>album titi (321): Attribute 'comment' not found!</p> end",
            $zeek->zeekify(
                'begin <zeek  tutu=""    >hey!</zeek> middle '
              . '<zeek name="toto"  toto=""    >hoy!</zeek>'
              . ' Here is the list of albums: '
              . '<zeek name="album" offset="1" size="2"><p>album {{name}} ({{duration}}): {{comment}}</p></zeek> end'));

        // create new news element
        $news = array(
            'name=test1&date=2015-01-01&comments=Tropcool1!',
            'name=test2&date=2015-02-02&comments=Tropcool2!',
            'name=test3&date=2015-03-03&comments=Tropcool3!');

        foreach ($news as $new)
        {
            $this->assertTrue(
                $zeek->data_set('news', $new));

            // check the value has been correctly created
            $this->assertTrue(
                $zeek->checkOutput('{"success":"Value correctly inserted!"}'));
        }

        $this->assertEquals(
            "<p>test1 (2015-01-01): Tropcool1!</p><p>test2 (2015-02-02): Tropcool2!</p><p>test3 (2015-03-03): Tropcool3!</p>",
            $zeek->zeekify(
                '<zeek name="news"><p>{{name}} ({{date}}): {{comments}}</p></zeek>'));

        $this->assertEquals(
            "<p>test3 (2015-03-03): Tropcool3!</p><p>test2 (2015-02-02): Tropcool2!</p><p>test1 (2015-01-01): Tropcool1!</p>",
            $zeek->zeekify(
                '<zeek name="news" sort_by="date--"><p>{{name}} ({{date}}): {{comments}}</p></zeek>'));


        $zeek->environment_clean();
    }

    public function test_success()
    {
        $this->zeek->success('toto', array('tutu' => 'titi'));
        $this->assertTrue(
            $this->zeek->checkOutput(
                json_encode(array('success' => 'toto', 'tutu' => 'titi'))));


        $this->zeek->success('toto', NULL);
        $this->assertTrue(
            $this->zeek->checkOutput(
                json_encode(array('success' => 'toto'))));

        $result = json_decode(json_encode(array('success' => 'toto')));
        $this->assertEquals($result->success, 'toto');

        $this->zeek->environment_clean();
    }

    public function test_plugins()
    {
        $zeek = $this->zeek;

        $this->assertEquals(
            array("MinifyCss", "MinifyJs"),
            $zeek->plugins_get_list('files'));
    }

    public function test_options()
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

        // check the default options are correctly setted
        $this->assertTrue($zeek->option_get('editor'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"html":"#FF0000","css":"#00FF00","js":"#0000FF","php":"#000000"}'));

        $this->assertTrue($zeek->option_get('plugins'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"zeekify":"disabled","MinifyCss":true,"MinifyJs":true}'));

        // check a bad option can not be getted
        $this->assertFalse($zeek->option_get("error"));
        $this->assertTrue(
            $zeek->checkOutput(
                '{"error":"No option found with name \'error\'!"}'));

        // check we can set a new option
        $this->assertTrue($zeek->option_set(
            "test", json_encode(array("ceci", "est", "un", "test"))));
        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Option \'test\' successfully written!"}'));

        $this->assertTrue($zeek->option_get('test'));
        $this->assertTrue(
            $zeek->checkOutput('["ceci","est","un","test"]'));

        // check we can modified an option
        $this->assertTrue($zeek->option_set_plugins(
            json_encode(array('zeekify'   => true,
                              'MinifyCss' => false,
                              'MinifyJs'  => false))));
        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Plugins list successfully updated!"}'));

        $this->assertTrue($zeek->option_get_plugins());
        $this->assertTrue(
            $zeek->checkOutput(
                '{"project":{"zeekify":"disabled","MinifyCss":false,"MinifyJs":false},"user":{"MinifyCss":false,"MinifyJs":false}}'));

        $this->assertTrue($zeek->option_get('test'));
        $this->assertTrue(
            $zeek->checkOutput('["ceci","est","un","test"]'));

        $this->zeek->environment_clean();
    }

    public function test_install_piwik()
    {
        $zeek = $this->zeek;

        /* $zeek->project_download_piwik();
           $zeek->checkOutput(""); */
    }

    public function test_check()
    {
        $zeek = $this->zeek;

        $this->assertTrue($zeek->check_input("AAtoto_09"));
        $this->assertFalse($zeek->check_input("A  Atoto_09"));
        $this->assertFalse($zeek->check_input("A%Atoto_09"));
        $this->assertFalse($zeek->check_input("AAtoto_090SAAtoto_090SAAtoto_090S"));
    }
}
?>
