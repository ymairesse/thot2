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

$idRP = isset($_POST['idRP']) ? $_POST['idRP'] : Null;
$idRV = isset($_POST['idRV']) ? $_POST['idRV'] : Null;

$identite = $User->getIdentite();

$matricule = $identite['matricule'];
$userName = $identite['userName'];

DEFINE ('MAX', 3);

$erreur = 0;

if ($idRP == Null) {
    $erreur = 1;
}

if ($erreur == 0) {
    $listeRP = $Application->listeDatesReunion();
    // cette RP n'existe pas
    if (!(in_array($idRP, array_keys($listeRP))))
        $erreur = 2;
}

if ($erreur == 0) {
    $infoRP = $Application->getInfoRp($idRP);
    // la RP est-elle ouverte?
    if ($infoRP['generalites']['ouvert'] == 0)
        $erreur = 3;
}

if ($erreur == 0) {
    // l'élève a-t-il déjà un RV à ce prof ($acronyme)?
    $acronyme = $Application->getInfoRV($idRP, $idRV)['acronyme'];
    $listeProfsRencontres = $Application->listeProfsRencontres($matricule, $idRP);
    if (in_array($acronyme, $listeProfsRencontres)){
        $erreur = 4;
    }
}

if ($erreur == 0){
    // l'élève n'a pas plus de MAX RV
    $listeRVEleve = $Application->getListeRVEleve($matricule);
    if (count($listeRVEleve) >= MAX)
        $erreur = 5;
}

if ($erreur == 0){
    // l'élève a déjà un RV à cette heure-là
    $infoRV = $Application->getInfoRV($idRP, $idRV);
    $heureRV = $infoRV['heure'];
    $listeRVEleve = $Application->getListeRVEleve($matricule);
    // Application::afficher($listeRVEleve);
    // Application::afficher($infoRV);
    if ((in_array($heureRV, $listeRVEleve)))
        $erreur = 6;
}

if ($erreur == 0) {
    // la fonction "inscription" gère le nombre max de RV et la concommiance des RV
    $resultat = $Application->inscriptionEleve($idRP, $idRV, $matricule, $userName);
    if ($resultat != 2)
        $erreur = 7;
}

$ok = false;
switch ($erreur) {
    case 0:
        $message = 'Votre RV est enregistré';
        $ok = true;
        break;
    case 1:
        $message = 'Réunion de parents non précisée';
        break;
    case 2:
        $message = 'Cette réunion de parents n\'existe plus';
        break;
    case 3:
        $message = 'Cette réunion de parents n\'est pas/plus ouverte';
        break;
    case 4:
        $message = 'Vous avez déjà un RV pris avec ce professeur';
        break;
    case 5:
        $message = 'Vous avez atteint le nombre maximum de RV';
        break;
    case 6:
        $message = 'Vous avez déjà un RV à cette heure-là';
        break;
    case 7:
        $message = 'L\'enregistrement de votre demande s\'est mal passée pour des raisons inconnues';
        break;
}

echo json_encode(array('ok' => $ok, 'message' => $message));
