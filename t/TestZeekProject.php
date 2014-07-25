<?php

require_once 'php/zeek.php';

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

    public function output($input)
    {
        echo "$input \n";
        $this->output = $input;
    }
}

class TestZeekProject extends PHPUnit_Framework_TestCase
{
    private $zeek_project;

    public function setUp()
    {
        $this->zeek_project = new ExtendsZeekProject();
    }

    public function test_project()
    {
        $this->assertFalse(
            $this->zeek_project->connect('test', 'test', 'test'));

        $this->assertFalse(
            $this->zeek_project->project_check('test'));

        $this->assertTrue(
            $this->zeek_project->project_add('test'));

        $this->assertFalse(
            $this->zeek_project->project_add('test'));

        $this->assertTrue(
            $this->zeek_project->project_check('test'));

        $this->assertTrue(
            $this->zeek_project->connect('test', 'test', 'test'));

        $this->assertTrue(
            $this->zeek_project->project_delete());

    }


    public function test_display_dynamic()
    {
        $this->assertEquals(
            $this->zeek_project->display_dynamic(''),
            '<div class="dynamic">
  </div>
');
    }

    public function test_display_post()
    {
        $this->assertEquals(
            $this->zeek_project->display_post(
                "div.modal-footer",
                "create_type",
                '$("div.modal").modal("hide");
      console.log("created new element");'),
            '$("div.modal-footer").on("click", function() {
  $.ajax({
    type: "POST",
    url: "php/zeek.php",
    data: {
      "method": "create_type",
      "project_id": "",
    },
    dataType: "text",
    success: function($input)
    {
      $("div.modal").modal("hide");
      console.log("created new element");    },
    error: function($request, $status, $error)
    {
      $("div.dynamic").replaceWith(
        \'<div class="dynamic"><h2>\' + $error + \'</h2></div>\');
    }
  });
});
');
    }

    public function test_display_modal()
    {
        $this->assertEquals(
            $this->zeek_project->display_modal(
                "Are you sure you want to disconnect from Zeek ?",
                false,
                NULL,
                NULL,
                $this->zeek_project->display_post(
                    "button.btn-modal",
                    "disconnect",
                    '$("div.modal-footer").show();
            $("div.modal-body").replaceWith(
            \'<div class="modal-body">
             </div>\');
             $("div.modal-footer").hide();
             $(location).attr("href", "welcome.php");')),
            '<script>
    $("h3.modal-body").text("Are you sure you want to disconnect from Zeek ?");
  $("div.modal-body").replaceWith(
  \'<div class="modal-body">
    </div>\');
    $("div.modal-footer").hide();
    $("div.modal").modal("show");
  $("button.btn-modal").on("click", function() {
  $.ajax({
    type: "POST",
    url: "php/zeek.php",
    data: {
      "method": "disconnect",
      "project_id": "",
    },
    dataType: "text",
    success: function($input)
    {
      $("div.modal-footer").show();
            $("div.modal-body").replaceWith(
            \'<div class="modal-body">
             </div>\');
             $("div.modal-footer").hide();
             $(location).attr("href", "welcome.php");    },
    error: function($request, $status, $error)
    {
      $("div.dynamic").replaceWith(
        \'<div class="dynamic"><h2>\' + $error + \'</h2></div>\');
    }
  });
});
</script>
');
    }

    public function test_input()
    {
        $this->assertFalse($this->zeek_project->input(NULL));

        $this->assertTrue($this->zeek_project->input(
            array('method' => 'clicked',
            'type' => 'artist',
            'project_id' => 1)));
    }

}
?>
