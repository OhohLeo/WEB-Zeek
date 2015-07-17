<?php

require_once 'plugins/files/minify_js.php';

class TestZeek extends PHPUnit_Framework_TestCase
{
    public function test_minify()
    {
        $minify = new MinifyJs();

        $this->assertEquals($minify->on_input('
$alert = $("div.alert");
$alert.hide();

$danger = $("div.error");
$success = $("div.success");
', 'js'), '$alert=$("div.alert");$alert.hide();$danger=$("div.error");$success=$("div.success");'
        );
    }
}
?>
