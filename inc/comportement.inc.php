<?php

$matricule = $User->getMatricule();
$anneeEtude = $User->getAnnee();
$classe = $User->getClasse();
$nomEleve = $User->getNomEleve();
$section = $User->getSection();

$eleve = $User->getIdentite();

$smarty->assign('eleve', $eleve);

$anScol = $Application->getCurrentAnneeScolaire();

require_once INSTALL_DIR."/inc/classes/classEleveAdes.inc.php";
$EleveAdes = new EleveAdes($matricule);

$listeTousFaits = $EleveAdes->getListeFaits($matricule);

$listeFaits = isset($listeTousFaits[$anScol]) ? $listeTousFaits[$anScol] : Null;
$listeRetenues = $EleveAdes->getListeRetenuesEleve($matricule);
$listeTypesFaits = $EleveAdes->GETlisteTypesFaits();
$descriptionChamps = $EleveAdes->listeChamps();

$userType = $User->getUserType();
$smarty->assign('userType', $userType);

$smarty->assign('listeFaits', $listeFaits);
$smarty->assign('listeRetenues', $listeRetenues);
$smarty->assign('listeTypesFaits', $listeTypesFaits);
$smarty->assign('descriptionChamps', $descriptionChamps);

$smarty->assign('corpsPage', 'infoDisciplinaires');

$smarty->assign('ECOLE', ECOLE);
$smarty->assign('ADRESSE', ADRESSE);
$smarty->assign('TELEPHONE', TELEPHONE);
$smarty->assign('COMMUNE', COMMUNE);
$smarty->assign('ADRESSEECOLE', ADRESSEECOLE);
$smarty->assign('DATE', $Application->dateNow());
$smarty->assign('BASEDIR', BASEDIR);
$smarty->assign('ANNEESCOLAIRE', $anScol);

$smarty->assign('eleve', $eleve);
$smarty->assign('listeFaits', $listeFaits);
$smarty->assign('listeRetenues', $listeRetenues);
$smarty->assign('listeTypesFaits', $listeTypesFaits);
$smarty->assign('listeRetenuesEleves', $listeRetenues);

$smarty->assign('descriptionChamps', $descriptionChamps);
$smarty->assign('corpsPage', 'infoDisciplinaires');
