<?php

require_once 'inc/classes/classEcole.inc.php';
$Ecole = new Ecole();
$anniversaires = $Ecole->anniversaires();
$anniversaires = $anniversaires['listeAnniv'][1];
$smarty->assign('anniversaires', $anniversaires);
$smarty->assign('corpsPage', 'anniversaires');
