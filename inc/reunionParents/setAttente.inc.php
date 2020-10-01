<?php

$idRP = isset($_POST['idRP']) ? $_POST['idRP'] : Null;
$acronyme = isset($_POST['acronyme']) ? $_POST['acronyme'] : Null;
$periode = isset($_POST['periode']) ? $_POST['periode'] : Null;

if (($periode < 1) || ($periode > 3))
    die("Cette période $periode n'existe pas");
if (!($Application->profExiste($acronyme)))
    die("Ce professeur n'existe pas");

$identite = $User->getIdentite();

$matricule = $identite['matricule'];
$userName = $identite['userName'];

if ($idRP != Null) {
    // la liste des dates possibles
    $listeDates = $Application->listeDatesReunion();
    if (in_array($idRP, array_keys($listeDates))) {
        // rechercher les informations sur la RP
        $infoRP = $Application->getInfoRp($idRP);
        // la RP est-elle ouverte?
        if ($infoRP['generalites']['ouvert'] == 1) {
            // la date est-elle compatible avec l'id
            if ($Application->validDate($idRP)) {

                $resultat = $Application->setListeAttenteEleve($userName, $matricule, $acronyme, $idRP, $periode);
                if ($resultat == 1) {
                    $texteMessage = "<i class='fa fa-warning fa-thumbs-up fa-2x'></i> Votre inscription en liste d'attente est enregistrée";
                    $niveau = 'success';
                    }
                    else {
                        $texteMessage = "<i class='fa fa-warning fa-2x'></i> Aucun enregistrement possible. Étes-vous déjà inscrit pour cette période-là?";
                        $niveau = 'warning';
                    }

            }  // validIdDate
            else {
                $texteMessage = "<i class='fa fa-warning fa-2x'></i> Impossible de prendre ce RV: la date ne correspond à aucune réunion de parents";
                $niveau = 'danger';
                }  // valideIdDate

        } // ouvert
        else {
        $texteMessage = "<i class='fa fa-warning fa-2x'></i> Cette réunion de parents n'est pas ou plus ouverte à l'inscription";
        $niveau = 'warning';
        }

    }  // la date existe dans la liste
    else {
            $texteMessage = "<i class='fa fa-warning fa-2x'></i> Il n'y a pas de réunion de parents à cette date";
            $niveau = 'danger';
        }

}  // date != Null
else {
    $texteMessage = "<i class='fa fa-warning fa-2x'></i> Aucune date de réunion de parents n'a été sélectionnée";
    $niveau = 'danger';
    }   // date != Null
