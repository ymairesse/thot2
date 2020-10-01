<?php

require_once '../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
session_start();
$User = isset($_SESSION[APPLICATION]) ? unserialize($_SESSION[APPLICATION]) : null;

$listeCoursEleve = $User->listeCoursEleve();
$listeCoursString = "'".implode("','", $listeCoursEleve)."'";

$identite = $User->getIdentite();
$matricule = $identite['matricule'];
$classe = $identite['groupe'];
$niveau = substr($classe, 0, 1);

$start = $_GET['start'];
$end = $_GET['end'];

require_once INSTALL_DIR.'/inc/classes/classJdc.inc.php';
$Jdc = new Jdc();

$liste = $Jdc->retreiveEvents($start, $end, $niveau, $classe, $matricule, $listeCoursString, $matricule);

echo json_encode($liste);
