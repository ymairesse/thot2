<?php

$matricule = $User->getMatricule();
$classe = $User->getClasse();
$niveau = substr($classe, 0, 1);
$listeCoursEleve = $User->listeCoursEleve();
$listeCoursString = "'".implode("','", $listeCoursEleve)."'";

require_once INSTALL_DIR.'/inc/classes/Files.class.php';
$Files = new Files();

require_once INSTALL_DIR.'/inc/fonctions.inc.php';

$listeDocs = $Files->listeElevesShares($matricule, $classe, $niveau, $listeCoursString);

// retrier les documents des cours par cours
if (isset($listeDocs['coursGrp']))
    $listeDocs['coursGrp'] = $Files->sortByCours($listeDocs['coursGrp']);

$listeFavoris = $Files->getListeFavs($matricule);

$smarty->assign('matricule', $matricule);
$smarty->assign('listeDocs', $listeDocs);
$smarty->assign('listeFavoris', $listeFavoris);

$smarty->assign('corpsPage', 'documents');
