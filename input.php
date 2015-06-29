<?php

session_start();

$global_path = getcwd();

require_once $global_path . '/lib/output.php';
require_once $global_path . '/lib/zeek.php';

$zeek = new Zeek();
if ($zeek->start('config.ini') == false) {
    header('HTTP/1.1 500 Internal Server Error');
}

if (isset($_POST)) {
    $zeek->input($_POST);
}

?>
