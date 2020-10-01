<?php

$acronyme = isset($_POST['acronyme']) ? $_POST['acronyme'] : null;
$periode = isset($_POST['periode']) ? $_POST['periode'] : null;

$identite = $User->getIdentite();

$matricule = $identite['matricule'];
$userName = $identite['userName'];

// le RV $id appartient-il à l'utilisateur actuel?
if ($Application->isOwnerAttente($acronyme, $periode, $userName)) {
    $nb = $Application->delAttente($acronyme, $periode);
    if ($nb == 1) {
        $texteMessage = "<i class='fa fa-thumbs-up fa-2x'></i> Votre demande de liste d'attente a été annulée";
        $niveau = 'success';
        } else {
            $texteMessage = "<i class='fa fa-warning fa-4x'></i> Votre demande n'a pas été annulée pour des raisons inconnues. Veuillez contacter l'administrateur.";
            $niveau = 'danger';
            }
}
else {
    $texteMessage = "<i class='fa fa-warning fa-2x'></i> Seul l'auteur de la demande de rendez-vous par Internet peut annuler ce rendez-vous.";
    $niveau = 'danger';
}
