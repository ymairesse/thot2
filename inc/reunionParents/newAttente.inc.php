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

$identite = $User->getIdentite();

$matricule = $identite['matricule'];
$userName = $identite['userName'];

$idRP = isset($_POST['idRP']) ? $_POST['idRP'] : Null;
$acronyme = isset($_POST['acronyme']) ? $_POST['acronyme'] : Null;
$periode = isset($_POST['periode']) ? $_POST['periode'] : Null;

$erreur = 0;

if ($idRP == Null) {
    $erreur = 1;
}

if ($erreur == 0){
    // une période a-t-elle été choisie
    if ($periode == Null)
        $erreur = 2;
}

if ($erreur == 0){
    if (($periode < 1) || ($periode > 3))
        // cette période existe?
        $erreur = 3;
}

if ($erreur == 0) {
    // ce professeur existe?
    if (!($Application->profExiste($acronyme)))
        $erreur = 4;
}

if ($erreur == 0) {
    // cet idRP correspond vraiment à une RP?
    $listeDates = $Application->listeDatesReunion();
    if (!(in_array($idRP, array_keys($listeDates))))
        $erreur = 5;
}

if ($erreur == 0){
    // cette RP est-elle ouverte?
    $infoRP = $Application->getInfoRp($idRP);
    if ($infoRP['generalites']['ouvert'] != 1)
        $erreur = 6;
}

if ($erreur == 0){
    $resultat = $Application->setListeAttenteEleve($userName, $matricule, $acronyme, $idRP, $periode);
    if ($resultat != 1)
        $erreur = 7;
}

$ok = false;
switch ($erreur) {
    case 0:
        $message = 'Votre demande est enregistrée';
        $ok = true;
        break;
    case 1:
        $message = 'Aucune réunion de parentes n\'est définie';
        break;
    case 2:
        $message = 'Veuillez sélectionner une des périodes proposées';
        break;
    case 3:
        $message = 'Cette période n\'est pas disponible';
        break;
    case 4:
        $message = 'Ce professeur n\'existe pas';
        break;
    case 5:
        $message = 'Cette réunion de parents n\'existe plus';
        break;
    case 6:
        $message = 'Cette réunion de parents est fermée à l\'inscription';
        break;
    case 7:
        $message = 'L\'enregistrement de votre demande s\'est mal passée pour des raisons inconnues';
}

echo json_encode(array('ok' => $ok, 'message' => $message));
