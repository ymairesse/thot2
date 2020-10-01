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

$idRP = isset($_POST['idRP']) ? $_POST['idRP'] : null;
$acronyme = isset($_POST['acronyme']) ? $_POST['acronyme'] : null;
$periode = isset($_POST['periode']) ? $_POST['periode'] : null;

$identite = $User->getIdentite();

$matricule = $identite['matricule'];
$userName = $identite['userName'];

$erreur = 0;

// le RV $id appartient-il à l'utilisateur actuel?
if (!($Application->isOwnerAttente($acronyme, $periode, $userName))) {
    $erreur = 1;
    }

if ($erreur == 0) {
    $nb = $Application->delAttente($acronyme, $periode);
    if ($nb != 1)
        $erreur = 2;
}

$ok = false;
switch ($erreur) {
    case 0:
        $message = 'Votre demande de liste d\'attente a bien été annulée';
        $ok = true;
        break;
    case 1:
        $massage = 'Seul l\'auteur de la demande de rendez-vous par Internet peut annuler ce rendez-vous.';
        $break;
    default:
        $message = 'Votre demande n\'a pas été annulée pour des raisons inconnues. Veuillez contacter l\'administrateur.';
        break;
}

echo json_encode(array('ok' => $ok, 'message' => $message));
