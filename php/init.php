<?php

require 'functions.php';

# on gère ici la requête d'initialisation
if (isset($_POST['dname'])
    && isset($_POST['login'])
    && isset($_POST['password']))
{
  connect($_POST['dname'], $_POST['login'], $_POST['password']);
}
