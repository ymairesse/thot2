<?php

require_once INSTALL_DIR.'/inc/classes/class.BullTQ.php';
$BullTQ = new bullTQ();

// liste de tous les cours suivis par cet élève durant la période $noBulletin (historique pris en compte)
$listeCoursGrp = $BullTQ->listeCoursGrpEleves($matricule);

if ($listeCoursGrp != null) {

    $smarty->assign('noBulletin', $noBulletin);

    $listeCoursGrp = $BullTQ->listeCoursGrpEleves($matricule);
    $listeCoursGrp = $listeCoursGrp[$matricule];
    $smarty->assign('listeCoursGrp', $listeCoursGrp);

    $listeProfsCoursGrp = $Application->listeProfsListeCoursGrp($listeCoursGrp);
    $smarty->assign('listeProfs', $listeProfsCoursGrp);

    $listeCompetences = $BullTQ->listeCompetencesListeCoursGrp($listeCoursGrp);
    $smarty->assign('listeCompetences', $listeCompetences);

    $listeCotesGlobales = $BullTQ->listeCotesGlobales($listeCoursGrp, $noBulletin);

    if ($listeCotesGlobales != null) {
        $smarty->assign('cotesGlobales', $listeCotesGlobales[$noBulletin]);
    } else {
        $smarty->assign('cotesGlobales', null);
    }

    $listeCotesGeneraux = $BullTQ->toutesCotesCoursGeneraux($listeCoursGrp, $matricule, $noBulletin);
    $smarty->assign('listeCotesGeneraux', $listeCotesGeneraux);

    $listeCommentaires = $BullTQ->listeCommentaires($matricule, $listeCoursGrp);
    $smarty->assign('commentaires', $listeCommentaires);

    $listeEpreuvesQualif = $BullTQ->listeEpreuvesQualif();
    $smarty->assign('listeEpreuvesQualif', $listeEpreuvesQualif);

    $qualification = $BullTQ->mentionsQualif($matricule);
    $smarty->assign('qualification', $qualification);

    $remarqueTitu = $BullTQ->remarqueTitu($matricule, $noBulletin);
    $smarty->assign('remarqueTitu', $remarqueTitu);
    $smarty->assign('corpsPage', 'bulletin/bulletinEcranTQ');

}
