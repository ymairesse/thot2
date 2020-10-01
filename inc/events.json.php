<?php

require_once '../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();
session_start();

if (!(isset($_SESSION[APPLICATION]))) {
    echo "<script type='text/javascript'>document.location.replace('".BASEDIR."');</script>";
    exit;
}

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';

$User = isset($_SESSION[APPLICATION]) ? unserialize($_SESSION[APPLICATION]) : null;

$listeCoursEleve = $User->listeCoursEleve();
$listeCoursString = "'".implode("','", $listeCoursEleve)."'";

$matricule = $User->getMatricule();
$identite = $User->getIdentite();
// $matricule = $identite['matricule'];
$classe = $identite['groupe'];
$niveau = substr($classe, 0, 1);

$start = $_GET['start'];
$end = $_GET['end'];

require_once INSTALL_DIR.'/inc/classes/classJdc.inc.php';
$Jdc = new Jdc();

// Journal de classe "ordinaire"
$listeJDC = $Jdc->retreiveEvents($start, $end, $niveau, $classe, $matricule, $listeCoursString);
// Journal de classe des remédiations
$listeRemediations = $Jdc->retreiveRemediations($start, $end, $matricule);

// Journal de classe personnel
$personnalEvents = $Jdc->retreivePersonnalEvents($start, $end, $matricule);

// événements du JDC personnel et partagés
$sharedEvents = $Jdc->retreiveSharedEvents($start, $end, $matricule);

// RV coaching scolaire (voir Athena dans l'application Zeus)
$coachingEvents = $Jdc->retreiveCoachingEvents($start, $end, $matricule);

// $liste = array_merge($listeJDC, $listeRemediations, $personnalEvents);
$liste = array_merge($listeJDC, $listeRemediations, $personnalEvents, $sharedEvents, $coachingEvents);

echo json_encode($liste);
