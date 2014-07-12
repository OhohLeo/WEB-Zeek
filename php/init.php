<?php

require 'database_access.php';

$access = new DataBaseAccess();

# on gère ici la requête d'initialisation
if (isset($_POST['dname'])
    && isset($_POST['login'])
    && isset($_POST['password']))
{
  $access->connect($_POST['dname'], $_POST['login'], $_POST['password']);
}

/* Groupe : */
/* - nom */
/* - date de création */
/* - phrase d'accroche */
/* - biographie */
/* - hyperliens */
/* Artiste : */
/* - nom */
/* - instruments */
/* - date d'entrée dans le groupe */
/* - phrase d'accroche */
/* - bibliographie */
/* - hyperliens */
/* Concert : */
/* - date */
/* - heure */
/* - nom du lieu */
/* - adresse */
/* - hyperlien */
/* News : */
/* - date */
/* - titre */
/* - commentaires */
/* Album : */
/* - date */
/* - titre */
/* - durée */
/* - commentaire */
/* - hyperlien */
/* Music : */
/* - date */
/* - titre */
/* - durée */
/* - commentaire */
/* - hyperlien */
/* Vidéo : */
/* - date */
/* - titre */
/* - durée */
/* - commentaire */
/* - hyperlien */
/* Média */
/* - date */
/* - titre */
/* - images */
/* - hyperlien */

/* eBand configuration : */
/* - changer le mot de passe */
/* - réinitialiser toutes les données */
/* - supprimer le compte */
