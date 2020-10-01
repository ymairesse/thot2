<?php

$idRV = isset($_POST['idRV']) ? $_POST['idRV'] : null;
$idRP = isset($_POST['idRP']) ? $_POST['idRP'] : null;

$identite = $User->getIdentite();

// $matricule = $identite['matricule'];
$userName = $identite['userName'];

// le RV $id appartient-il à l'utilisateur actuel?
if ($Application->isOwnerRV($idRP, $idRV, $userName)) {
    $nb = $Application->delRV($idRP, $idRV);
    if ($nb == 1) {
        $texteMessage = "<i class='fa fa-thumbs-up fa-2x'></i> Ce rendez-vous a été annulé";
        $niveau = 'success';
    } else {
        $texteMessage = "<i class='fa fa-warning fa-4x'></i> Ce rendez-vous n'a pas été annulé pour des raisons inconnues. Veuillez contacter l'administrateur.";
        $niveau = 'danger';
    }
} else {
    $texteMessage = "<i class='fa fa-warning fa-2x'></i> Seul l'auteur de la demande de rendez-vous par Internet peut annuler ce rendez-vous.";
    $niveau = 'danger';
}
