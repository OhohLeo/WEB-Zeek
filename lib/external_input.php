<?php

session_start();

require_once '/mnt/132/sdb/e/e/zeekadmin/lib/output.php';
require_once '/mnt/132/sdb/e/e/zeekadmin/lib/zeek.php';

$zeek = new Zeek();
$zeek->start('config.ini');

if (isset($_POST)) {
    $zeek->input($_POST);
}

?>
