<?php

session_start();

$global_path = getcwd();

require_once $global_path . '/lib/output.php';
require_once $global_path . '/lib/zeek.php';

$zeek = new Zeek();
$zeek->start('config.ini');

if (isset($_POST)) {
    $zeek->input($_POST);
}

?>
