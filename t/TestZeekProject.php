<?php

require_once 'lib/zeek.php';

class ExtendsZeekProject extends ZeekProject
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

    public function database()
    {
        return $this->access;
    }

    public function output($input)
    {
        echo "$input \n";
        $this->output = $input;
    }

    protected function session_start()
    {
    }
}

class TestZeekProject extends PHPUnit_Framework_TestCase
{
    private $zeek_project;

    public function setUp()
    {
        $this->zeek_project = new ExtendsZeekProject();
        $this->zeek_project->config('t/test.ini');
    }

    /* public function test_environment() */
    /* { */
    /*     $this->assertTrue( */
    /*         $this->zeek_project->connect_to_database()); */

    /*     $this->zeek_project->environment_clean('zeek_test'); */

    /*     $this->assertTrue( */
    /*         $this->zeek_project->environment_setup('zeek_test', 'test', 'test')); */

    /*     $access = $this->zeek_project->database(); */

    /*     $this->assertTrue( */
    /*         $access->database_check('zeek_test')); */

    /*     $this->assertTrue( */
    /*         $access->table_check('user')); */

    /*     $result = $access->table_view( */
    /*         'user', '*', NULL, NULL, NULL, NULL)->fetch(); */

    /*     $this->assertEquals($result->id, 1); */
    /*     $this->assertEquals($result->name, "test"); */
    /*     $this->assertEquals($result->password, "test"); */

    /*     $this->assertTrue( */
    /*         $access->table_check('project')); */


    /*     $this->zeek_project->environment_clean('zeek_test'); */

    /*     $this->assertFalse( */
    /*         $access->database_check('zeek_test')); */
    /* } */


    /* public function test_user() */
    /* { */
    /*     $zeek = $this->zeek_project; */

    /*     $this->assertTrue( */
    /*         $zeek->connect_to_database()); */

    /*     $zeek->environment_clean('zeek_test'); */

    /*     $this->assertTrue( */
    /*         $zeek->environment_setup('zeek_test', 'test', 'test')); */

    /*     $this->assertTrue($zeek->user_get('test') !== NULL); */

    /*     $this->assertTrue($zeek->user_check('test', 'test')); */

    /*     $this->assertTrue($zeek->user_get('toto') == NULL); */

    /*     $this->assertFalse($zeek->user_check('toto', 'toto')); */

    /*     $this->assertTrue($zeek->user_add('toto', 'toto')); */

    /*     $this->assertFalse($zeek->user_add('toto', 'toto')); */

    /*     $this->assertTrue($zeek->user_get('toto') !== NULL); */

    /*     $this->assertTrue($zeek->user_check('toto', 'toto')); */

    /*     $this->assertFalse($zeek->user_get('toto') == NULL); */

    /*     $this->assertTrue($zeek->user_change_password( */
    /*         'toto', 'toto', 'titi')); */

    /*     $this->assertFalse($zeek->user_change_password( */
    /*         'tutu', 'toto', 'titi')); */

    /*     $this->assertFalse($zeek->user_change_password( */
    /*         'toto', 'toto', 'titi')); */

    /*     $this->assertTrue($zeek->user_change_password( */
    /*         'toto', 'titi', 'toto')); */

    /*     $this->assertFalse($zeek->user_change_password( */
    /*         'toto', 'titi', 'toto')); */

    /*     $this->assertFalse($zeek->user_change_password( */
    /*         'toto', 'titi', 'toto')); */

    /*     $this->assertTrue($zeek->user_remove('toto')); */
    /*     $this->assertFalse($zeek->user_remove('toto')); */
    /*     $this->assertFalse($zeek->user_remove('tutu')); */

    /*     $zeek->environment_clean('zeek_test'); */
    /* } */

    /* public function test_project() */
    /* { */
    /*     $this->assertTrue( */
    /*         $this->zeek_project->connect_to_database()); */

    /*     $this->zeek_project->environment_clean('zeek_test'); */

    /*     $this->assertTrue( */
    /*         $this->zeek_project->environment_setup('zeek_test', 'test', 'test')); */

    /*     $this->assertFalse( */
    /*         $this->zeek_project->project_check('test')); */

    /*     $this->assertTrue( */
    /*         $this->zeek_project->project_add('test')); */

    /*     $this->assertFalse( */
    /*         $this->zeek_project->project_add('test')); */

    /*     $this->assertTrue( */
    /*         $this->zeek_project->project_check('test')); */

    /*     $this->assertTrue( */
    /*         $this->zeek_project->connect_to_database()); */

    /*     $this->assertTrue( */
    /*         $this->zeek_project->project_delete()); */

    /*     $this->zeek_project->environment_clean('zeek_test'); */
    /* } */


/*     public function test_display_dynamic() */
/*     { */
/*         $this->assertEquals( */
/*             $this->zeek_project->display_dynamic(''), */
/*             '<div class="dynamic"> */
/*   </div> */
/* '); */
/*     } */

/*     public function test_display_post() */
/*     { */
/*         $this->assertEquals( */
/*             $this->zeek_project->display_post( */
/*                 "div.modal-footer", */
/*                 "create_type", */
/*                 '$("div.modal").modal("hide"); */
/*       console.log("created new element");'), */
/*             '$("div.modal-footer").on("click", function() { */
/*   $.ajax({ */
/*     type: "POST", */
/*     url: "lib/zeek.php", */
/*     data: { */
/*       "method": "create_type", */
/*       "project_id": "", */
/*     }, */
/*     dataType: "text", */
/*     success: function($input) */
/*     { */
/*       $("div.modal").modal("hide"); */
/*       console.log("created new element");    }, */
/*     error: function($request, $status, $error) */
/*     { */
/*       $("div.dynamic").replaceWith( */
/*         \'<div class="dynamic"><h2>\' + $error + \'</h2></div>\'); */
/*     } */
/*   }); */
/* }); */
/* '); */
/*     } */

/*     public function test_display_modal() */
/*     { */
/*         $this->assertEquals( */
/*             $this->zeek_project->display_modal( */
/*                 "Are you sure you want to disconnect from Zeek ?", */
/*                 false, */
/*                 NULL, */
/*                 NULL, */
/*                 $this->zeek_project->display_post( */
/*                     "button.btn-modal", */
/*                     "disconnect", */
/*                     '$("div.modal-footer").show(); */
/*             $("div.modal-body").replaceWith( */
/*             \'<div class="modal-body"> */
/*              </div>\'); */
/*              $("div.modal-footer").hide(); */
/*              $(location).attr("href", "index.php");')), */
/*             '<script> */
/*     $("h3.modal-body").text("Are you sure you want to disconnect from Zeek ?"); */
/*   $("div.modal-body").replaceWith( */
/*   \'<div class="modal-body"> */
/*     </div>\'); */
/*     $("div.modal-footer").hide(); */
/*     $("div.modal").modal("show"); */
/*   $("button.btn-modal").on("click", function() { */
/*   $.ajax({ */
/*     type: "POST", */
/*     url: "lib/zeek.php", */
/*     data: { */
/*       "method": "disconnect", */
/*       "project_id": "", */
/*     }, */
/*     dataType: "text", */
/*     success: function($input) */
/*     { */
/*       $("div.modal-footer").show(); */
/*             $("div.modal-body").replaceWith( */
/*             \'<div class="modal-body"> */
/*              </div>\'); */
/*              $("div.modal-footer").hide(); */
/*              $(location).attr("href", "index.php");    }, */
/*     error: function($request, $status, $error) */
/*     { */
/*       $("div.dynamic").replaceWith( */
/*         \'<div class="dynamic"><h2>\' + $error + \'</h2></div>\'); */
/*     } */
/*   }); */
/* }); */
/* </script> */
/* '); */
/*     } */


    public function test_connect()
    {
        $zeek = $this->zeek_project;

        $this->assertTrue(
            $zeek->connect('project', 'test', 'test'));

        $this->assertTrue(
            $zeek->checkOutput(
                '{"success":"Connection accepted, now create new project!","action":"project_create"}'));

        $zeek->create_new_project('project');

        $this->assertTrue(
            $zeek->connect('project', 'test', 'test'));
        $this->assertTrue(
            $zeek->checkOutput('{"redirect":"home.php"}'));

        $zeek->project_delete();
        $zeek->environment_clean('zeek_test');
    }

    /* public function test_input() */
    /* { */
    /*     $this->assertFalse($this->zeek_project->input(NULL)); */

    /*     $this->assertTrue($this->zeek_project->input( */
    /*         array('method' => 'clicked', */
    /*         'type' => 'artist', */
    /*         'project_id' => 1))); */
    /* } */



    /* public function test_success() */
    /* { */
    /*     $this->zeek_project->success('toto', array('tutu' => 'titi')); */
    /*     $this->assertTrue( */
    /*         $this->zeek_project->checkOutput( */
    /*             json_encode(array('success' => 'toto', 'tutu' => 'titi')))); */


    /*     $this->zeek_project->success('toto', NULL); */
    /*     $this->assertTrue( */
    /*         $this->zeek_project->checkOutput( */
    /*             json_encode(array('success' => 'toto')))); */
    /* } */

}
?>
