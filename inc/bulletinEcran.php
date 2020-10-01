<?php

// liste de tous les cours suivis par cet élève durant la période $bulletin (historique pris en compte)
$listeCoursGrp = $Bulletin->listeCoursGrpEleves($matricule, $bulletin);
// il n'y a qu'un élève, il n'y aura donc qu'une seule liste de pondérations
if ($listeCoursGrp) {
    $listeCoursGrp = $listeCoursGrp[$matricule];
    $listeProfsCoursGrp = $Application->listeProfsListeCoursGrp($listeCoursGrp);
    $listeSituations = $Bulletin->listeSituationsCours($matricule, array_keys($listeCoursGrp), null, true);

    $sitPrecedentes = $Bulletin->situationsPrecedentes($listeSituations, $bulletin);
    $sitActuelles = $Bulletin->situationsPeriode($listeSituations, $bulletin);
    $listeCompetences = $Bulletin->listeCompetencesListeCoursGrp($listeCoursGrp);
    $listeCotes = $Bulletin->listeCotes($matricule, $listeCoursGrp, $listeCompetences, $bulletin);

    $ponderations = $Bulletin->getPonderations($listeCoursGrp, $bulletin);
    $cotesPonderees = $Bulletin->listeGlobalPeriodePondere($listeCotes, $ponderations, $bulletin);

    $commentairesCotes = $Bulletin->listeCommentairesTousCours($matricule, $bulletin);
    $mentions = $Bulletin->listeMentions($matricule, $bulletin);
    $ficheEduc = $Bulletin->listeFichesEduc($matricule, $bulletin);
    $remarqueTitulaire = $Bulletin->remarqueTitu($matricule, $bulletin);
    if ($remarqueTitulaire != null) {
        $remarqueTitulaire = $remarqueTitulaire[$matricule][$bulletin];
    }
    $tableauAttitudes = $Bulletin->tableauxAttitudes($matricule, $bulletin);

    $noticeDirection = $Bulletin->noteDirection($annee, $bulletin);

    $smarty->assign('annee', $annee);
    $smarty->assign('ANNEESCOLAIRE', ANNEESCOLAIRE);
    $smarty->assign('infoPerso', $infoPersoEleve);
    $smarty->assign('listeCoursGrp', $listeCoursGrp);
    $smarty->assign('listeProfsCoursGrp', $listeProfsCoursGrp);
    $smarty->assign('sitPrecedentes', $sitPrecedentes);
    $smarty->assign('sitActuelles', $sitActuelles);
    $smarty->assign('listeCotes', $listeCotes);
    $smarty->assign('listeCompetences', $listeCompetences);

    $smarty->assign('cotesPonderees', $cotesPonderees);
    $smarty->assign('commentaires', $commentairesCotes);
    $smarty->assign('attitudes', $tableauAttitudes);
    $smarty->assign('ficheEduc', $ficheEduc);
    $smarty->assign('remTitu', $remarqueTitulaire);
    $smarty->assign('mention', $mentions);
    $smarty->assign('noticeDirection', $noticeDirection);
}
$smarty->assign('corpsPage', 'showEleve');
