<?php

require 'functions.php';

$access = new DataBaseAccess();

# on g�re ici la requ�te d'initialisation
if (isset($_POST['dname'])
    && isset($_POST['login'])
    && isset($_POST['password']))
{
  $access->connect($_POST['dname'], $_POST['login'], $_POST['password']);
}
