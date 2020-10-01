<?php

require_once '../../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();
session_start();

if (!(isset($_SESSION[APPLICATION]))) {
    echo "<script type='text/javascript'>document.location.replace('".BASEDIR."');</script>";
    exit;
}

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = unserialize($_SESSION[APPLICATION]);
$matricule = $User->getMatricule();

require_once INSTALL_DIR.'/inc/classes/Files.class.php';
$Files = new Files();

$idTravail = isset($_POST['idTravail']) ? $_POST['idTravail'] : null;
$remarque = isset($_POST['remarque']) ? $_POST['remarque'] : null;

// vérifier que l'élève doit effectivement remettre ce travail
if ($verif = $Files->verifEleve4Travail($matricule, $idTravail))
{
    $nb = $Files->saveRemarqueEleve($remarque, $idTravail, $matricule);
    echo $nb;
}
else echo 0;
