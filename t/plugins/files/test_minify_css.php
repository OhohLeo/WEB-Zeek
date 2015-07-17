<?php

require_once 'plugins/files/minify_css.php';

class TestZeek extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $minify = new MinifyCss();

        $this->assertEquals($minify->on_input('
body {
    font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
    font-size: 16px;
}
p.message {
    border-color: #EEE;
    border-radius: 3px;
}'),
  'body{font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:16px}p.message{border-color:#EEE;border-radius:3px}');

    }
}
?>
