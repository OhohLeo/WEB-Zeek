<?php

require 'database_access.php';

$access = new DataBaseAccess();

# on g�re ici la requ�te d'initialisation
if (isset($_POST['dname'])
    && isset($_POST['login'])
    && isset($_POST['password']))
{
  $access->connect($_POST['dname'], $_POST['login'], $_POST['password']);
}

/* Groupe : */
/* - nom */
/* - date de cr�ation */
/* - phrase d'accroche */
/* - biographie */
/* - hyperliens */
/* Artiste : */
/* - nom */
/* - instruments */
/* - date d'entr�e dans le groupe */
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
/* - dur�e */
/* - commentaire */
/* - hyperlien */
/* Music : */
/* - date */
/* - titre */
/* - dur�e */
/* - commentaire */
/* - hyperlien */
/* Vid�o : */
/* - date */
/* - titre */
/* - dur�e */
/* - commentaire */
/* - hyperlien */
/* M�dia */
/* - date */
/* - titre */
/* - images */
/* - hyperlien */

/* eBand configuration : */
/* - changer le mot de passe */
/* - r�initialiser toutes les donn�es */
/* - supprimer le compte */
