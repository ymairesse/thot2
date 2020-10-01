<?php

$matricule = $User->getMatricule();
$anneeEtude = $User->getAnnee();
$classe = $User->getClasse();
$nomEleve = $User->getNomEleve();
$section = $User->getSection();

$eleve = $User->getIdentite();

$smarty->assign('eleve', $eleve);

require_once INSTALL_DIR.'/inc/classes/classBulletin.inc.php';
$Bulletin = new Bulletin();

require_once INSTALL_DIR.'/inc/classes/classEcole.inc.php';
$Ecole = new Ecole();

$listeCoursGrp = $Ecole->listeCoursGrpEleve($matricule);
$listeCoursGrpAbr = $Ecole->abrListeCoursGrp(array_keys($listeCoursGrp));
$listeCotes = $Bulletin->getCotes4listeCoursGrp($listeCoursGrp, $matricule);

$smarty->assign('abrCoursGrp', $listeCoursGrpAbr);
$smarty->assign('listeCotes', $listeCotes);
$smarty->assign('listeCoursGrp', $listeCoursGrp);
$smarty->assign('corpsPage', 'bulletin/repertoire');
