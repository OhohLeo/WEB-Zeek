<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/output.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/zeek.php';

$zeek = new Zeek();
$zeek->start('config.ini');

if (isset($_POST)) {
    $zeek->input($_POST);
}

?>
