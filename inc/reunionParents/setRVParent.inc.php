<?php

$idRP = isset($_POST['idRP']) ? $_POST['idRP'] : Null;
$idRV = isset($_POST['rv']) ? $_POST['rv'] : Null;

$identite = $User->getIdentite();

$matricule = $identite['matricule'];
$userName = $identite['userName'];

DEFINE ('MAX', 3);

// vérifier que la date de RP existe
if ($idRP != Null) {
    // la liste des dates possibles
    $listeRP = $Application->listeDatesReunion();
    if (in_array($idRP, array_keys($listeRP))) {
        // rechercher les informations sur la RP
        $infoRP = $Application->getInfoRp($idRP);
        // la RP est-elle ouverte?
        if ($infoRP['generalites']['ouvert'] == 1) {
            // la date est-elle compatible avec l'id
            if ($Application->validIdRpIdRv($idRP, $idRV)) {
                // l'élève a-t-il déjà un RV à ce prof ($acronyme)?
                $acronyme = $Application->getInfoRV($idRP, $idRV)['acronyme'];
                if (!($Application->rdvIsDoublon($matricule, $acronyme, $idRP))) {
                    // la fonction "inscription" gère le nombre max de RV et la concommiance des RV
                    $resultat = $Application->inscriptionEleve($idRP, $idRV, $matricule, MAX, $userName);
                    switch ($resultat) {
                        case '1':
                            $texteMessage = "<i class='fa fa-thumbs-up fa-2x'></i>  Votre rendez-vous a été enregistré.";
                            $niveau = 'success';
                            break;
                        case '-1':
                            $texteMessage = "<i class='fa fa-warning fa-2x'></i> Vous avez déjà trois rendez-vous.";
                            $niveau = 'warning';
                            break;
                        case '-2':
                            $texteMessage = "<i class='fa fa-warning fa-2x'></i> Un rendez-vous a déjà été fixé à cette heure-là";
                            $niveau = 'danger';
                            break;
                        case '-3':
                            $texteMessage = "<i class='fa fa-warning fa-2x'></i> Une autre personne vient, à l'instant, de choisir cette période de RV.";
                            $texteMessage .= "Veuillez faire un nouveau choix.";
                            $niveau = 'danger';
                            break;
                        }
                    }  // rdvIsDoublon
                    else {
                        $texteMessage = "<i class='fa fa-warning fa-2x'></i> Vous avez déjà un RV avec cette personne";
                        $niveau = 'warning';
                        }  // rdvIsDoublon

                    }  // valideIdDate
                else {
                    $texteMessage = "<i class='fa fa-warning fa-2x'></i> Impossible de prendre ce RV: la date ne correspond à aucune réunion de parents";
                    $niveau = 'danger';
                    }  // valideIdDate

                }  // ouvert
                else {
                $texteMessage = "<i class='fa fa-warning fa-2x'></i> Cette réunion de parents n'est pas ou plus ouverte à l'inscription";
                $niveau = 'warning';
                }
            }
            else {
                    $texteMessage = "<i class='fa fa-warning fa-4x'></i> Il n'y a pas de réunion de parents à cette date";
                    $niveau = 'danger';
                }

            }  // date != Null
        else {
            $texteMessage = "<i class='fa fa-warning fa-4x'></i> Aucune date de réunion de parents n'a été sélectionnée";
            $niveau = 'danger';
        }   // idRP != Null
