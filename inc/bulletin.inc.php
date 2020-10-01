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

// déterminer le dernier bulletin auquel cet élève a accès
$bulletinAccessible = $Bulletin->accesBulletin($matricule);
if ($bulletinAccessible != 0) {
    $dernier = $bulletinAccessible;
} else {
    $dernier = 0;
}
// quel est le bulletin demandé? Si rien demandé, on prend le dernier accessible par cet élève
$noBulletin = isset($_POST['noBulletin']) ? $_POST['noBulletin'] : $dernier;

// si le bulletin existe (entre 1 et dernier bulletin) et qu'un élève a été choisi
if (($noBulletin <= $dernier) && ($noBulletin >= 0) && ($matricule != '')) {
    $smarty->assign('noBulletin', $noBulletin);
    // ********************************************************
    // sélection du bulletin en fonction de la section
    $bulletinTQ = array('TQ');
    $bullISND = array('GT', 'TT', 'S', '');
    if (in_array($section, $bullISND)) {
        include 'inc/bulletin/bulletinGTTT.inc.php';
    } else {
        include 'inc/bulletin/bulletinTQ.inc.php';
    }
} else {
    // POUR PARER À UNE TENTATIVE D'ACCES À UN BULLETIN NON PUBLIÉ ;O)
    $smarty->assign('noBulletin', $dernier);
    $smarty->assign('corpsPage', 'default');
}
$smarty->assign('matricule', $matricule);
$smarty->assign('DERNIERBULLETIN', $dernier);
$smarty->assign('listeBulletins', range(0, $dernier));
$smarty->assign('selecteur', 'selectBulletin');
