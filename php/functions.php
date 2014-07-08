<?php

function connect($dname, $login, $password)
{
    try {
        new PDO("mysql:host=localhost;dname=$dname", $login, $password);
    }
    catch (Exception $e)
    {
        die("Impossible to connect with MySQL " . $e->getMessage());
    }
}
