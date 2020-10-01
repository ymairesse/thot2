<?php

require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = unserialize($_SESSION[APPLICATION]);
$matricule = $User->getMatricule();

$Application->delPerimes();

$classe = $User->getClasse();
$niveau = substr($classe,0,1);
$listeCoursEleve = $User->listeCoursEleve();
$listeMatieresEleve = $User->getListeMatieresEleve($listeCoursEleve);
// Application::afficher($listeMatieresEleve);

$nomEleve = $User->getNom();

// création de la liste des annonces pour l'élève, fonction de son matricule, de sa classe
// -et donc de son niveau d'étude- et de sa liste de cours pour chacune des catégories: élève, cours, classe, niveau, école
$listeAnnonces = $Application->listeAnnonces($matricule, $classe, $listeCoursEleve, $listeMatieresEleve, $User->getNom());

$listeFlagsAnnonces = $Application->listeFlagsAnnonces(array_keys($listeAnnonces), $matricule);
$listePJ = $Application->getPJ4notifs($listeAnnonces, $matricule);

// mise en concordance des annonces et des accusés de lecture
$listeAnnonces = $Application->comboAnnoncesFlags($listeAnnonces, $listeFlagsAnnonces);
// mise en concordance des annonces et des PJ
$listeAnnonces = $Application->comboAnnoncesPJ($listeAnnonces, $listePJ);

$nbAccuses = $Application->nbAccusesManquants($listeAnnonces);
$nbNonLus = $Application->nbNonLus($listeAnnonces);

$smarty->assign('listeAnnonces', $listeAnnonces);
$smarty->assign('nbAccuses', $nbAccuses);
$smarty->assign('nbNonLus', $nbNonLus);
$smarty->assign('matricule', $matricule);
$smarty->assign('classe', $classe);
$smarty->assign('niveau', $niveau);
$smarty->assign('nom', $User->getNom());
$smarty->assign('nomEleve', $User->getNomEleve());

$smarty->assign('corpsPage','annonces');
