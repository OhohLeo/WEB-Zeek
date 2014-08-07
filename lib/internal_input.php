<?php

session_start();

require_once '/home/leo/zeek/lib/output.php';
require_once '/home/leo/zeek/lib/zeek.php';

$zeek = new Zeek();
$zeek->start('config.ini');

if (isset($_POST)) {
    $zeek->input($_POST);
}

?>
