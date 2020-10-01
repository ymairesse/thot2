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

$idRV = isset($_POST['idRV']) ? $_POST['idRV'] : null;
$idRP = isset($_POST['idRP']) ? $_POST['idRP'] : null;

$identite = $User->getIdentite();
$userName = $identite['userName'];

$ok = false;
// le RV $id appartient-il à l'utilisateur actuel?
if ($Application->isOwnerRV($idRP, $idRV, $userName)) {
    // si ok, alors on supprime le RV
    $nb = $Application->delRV($idRP, $idRV);
    if ($nb == 1) {
        $message = "Ce rendez-vous a été annulé";
        $ok = true;
        }
        else {
            $message = "Ce rendez-vous n'a pas été annulé pour des raisons inconnues. Veuillez contacter l'administrateur.";
        }
    } else {
        $message = "<i class='fa fa-warning fa-2x'></i> Seul l'auteur de la demande de rendez-vous par Internet peut annuler ce rendez-vous.";
        }

echo json_encode(array('ok' => $ok, 'message' => $message));
