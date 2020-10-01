<?php

$listeDates = $Application->listeDatesReunion(true);

$smarty->assign('listeDates', $listeDates);
if (count($listeDates) == 1) {
    $idRP = current($listeDates);
} else {
    $idRP = isset($_POST['idRP']) ? $_POST['idRP'] : null;
}

$smarty->assign('idRP', $idRP);
$smarty->assign('selecteur', 'selecteurs/selectDate');

$smarty->assign('User', $User->getIdentite());
$matricule = $User->getIdentite()['matricule'];
$smarty->assign('matricule', $matricule);
$userName = $User->getIdentite()['userName'];
$smarty->assign('userName', $userName);

// une date de RP a été sélectionnée
if (isset($idRP)) {
    $infoRP = $Application->getInfoRp($idRP);
    $smarty->assign('infoRP', $infoRP);
    $smarty->assign('date', Application::datePHP($infoRP['date']));
    $OUVERT = $infoRP['generalites']['ouvert'];
    $ACTIVE = $infoRP['generalites']['active'];
    $typeRP = $infoRP['typeRP'];
    $smarty->assign('OUVERT', $OUVERT);
    $smarty->assign('ACTIVE', $ACTIVE);
    $smarty->assign('typeRP', $typeRP);

    // liste des périodes pour la liste d'attente
    $listePeriodes = $Application->getListePeriodes($idRP);
    $smarty->assign('listePeriodes', $listePeriodes);

    if ($ACTIVE == 1) {
        $listeRV = $Application->getRVeleve($matricule, $idRP);
        $smarty->assign('listeRV', $listeRV);
        $listeAttente = $Application->getListeAttenteEleve($matricule, $idRP);
        $smarty->assign('listeAttente', $listeAttente);
    }
    switch ($typeRP) {
        case 'profs':
            // s'agit-il d'une réunion de parents pour tous ls profs?
            $listeProfsCours = $Application->listeProfsCoursEleve($matricule);
            $smarty->assign('listeProfsCours', $listeProfsCours);

            $listeStatutsSpeciaux = $Application->listeStatutsSpeciaux();
            $smarty->assign('listeStatutsSpeciaux', $listeStatutsSpeciaux);

            $listeEncadrement = $Application->encadrement($listeProfsCours, $listeStatutsSpeciaux);
            $smarty->assign('listeEncadrement', $listeEncadrement);
            break;
        case 'titus':
            // ou seulement pour les titulaires?
            $listeEncadrement = $Application->listeTitulaires($matricule);
            $smarty->assign('listeEncadrement', $listeEncadrement);
            break;
        case 'cible':
            // ou pour seulement certains profs
            $listeProfs = $Application->listeProfsCibles($idRP);
            $smarty->assign('listeProfs', $listeProfs);
            break;
        default:
            $smarty->assign('OUVERT', 0);
            $smarty->assign('ACTIVE', 0);
            break;
        }
    }

$smarty->assign('statistiques', $Application->nbRv($idRP));

switch ($mode) {
    case 'saveRV':
        include_once 'inc/reunionParents/setRVParent.inc.php';
        $smarty->assign('message', array(
            'title' => 'Enregistrement de votre demande',
            'texte' => $texteMessage,
            'urgence' => $niveau,
        ));
        $listeRV = $Application->getRVeleve($matricule, $idRP);
        $smarty->assign('listeRV', $listeRV);
        break;

    case 'saveAttente':
        include_once 'inc/reunionParents/setAttente.inc.php';
        $listeAttente = $Application->getListeAttenteEleve($matricule, $idRP);
        $smarty->assign('listeAttente', $listeAttente);
        $smarty->assign('message', array(
            'title' => 'Enregistrement de votre demande',
            'texte' => $texteMessage,
            'urgence' => $niveau,
        ));
        break;

    case 'delRv':
        include_once 'inc/reunionParents/delRv.inc.php';
        $listeRV = $Application->getRVeleve($matricule, $idRP);
        $smarty->assign('listeRV', $listeRV);
        $smarty->assign('message', array(
            'title' => "Suppression d'un rendez-vous",
            'texte' => $texteMessage,
            'urgence' => $niveau,
        ));
        break;

    case 'delAttente':
        include_once 'inc/reunionParents/delAttente.inc.php';
        $listeAttente = $Application->getListeAttenteEleve($matricule, $idRP);
        $smarty->assign('listeAttente', $listeAttente);
        $smarty->assign('message', array(
            'title' => "Sortie de liste d'attente",
            'texte' => $texteMessage,
            'urgence' => $niveau,
        ));
        break;
}

$smarty->assign('corpsPage', 'reunionParents');
