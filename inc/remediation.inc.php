<?php

require_once INSTALL_DIR.'/inc/classes/classBulletin.inc.php';
$Bulletin = new Bulletin();

require_once INSTALL_DIR.'/inc/classes/class.remediation.php';
$Remediation = new Remediation();

$eleve = $User->getIdentite();
$matricule = $eleve['matricule'];
$niveau = SUBSTR($eleve['groupe'],0,1);
$classe = $eleve['groupe'];

$listeCoursGrp = current($Bulletin->listeCoursGrpActuelsEleve($eleve['matricule']));
// tenir compte des cours qui ont été ajoutés dans l'historique
$historique = current($Bulletin->listeCoursAddHistorique($matricule));

if ($historique != Null)
    $listeCoursGrp = (array_merge($listeCoursGrp, $historique));
$listeCoursGrp = array_keys($listeCoursGrp);

$listeMatieres = array_keys($Bulletin->listeCoursEleves($matricule));

// les remédiations auxquelles l'élève est inscrit
$remediationsEleve = $Remediation->getRemediationsEleve($matricule);

// les offres de remédiation pour cet élève
$listeOffres = $Remediation->getOffresRemediations($niveau, $classe, $listeCoursGrp, $listeMatieres);
$occupations = $Remediation->getOccupations(array_keys($listeOffres));

// les présences aux remédiations de cet élève
$listePresences = $Remediation->getPresences($matricule);

$smarty->assign('listeCoursGrp', $listeCoursGrp);
$smarty->assign('listeMatieres', $listeMatieres);
$smarty->assign('listeOffres', $listeOffres);
$smarty->assign('occupations', $occupations);
$smarty->assign('remediationsEleve', $remediationsEleve);
$smarty->assign('listePresences', $listePresences);
$smarty->assign('eleve', $eleve);

$smarty->assign('corpsPage', 'remediation/remediation');
