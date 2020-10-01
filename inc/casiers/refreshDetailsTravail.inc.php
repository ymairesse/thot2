<?php

require_once '../../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();
session_start();

if (!(isset($_SESSION[APPLICATION]))) {
    echo "<script type='text/javascript'>document.location.replace('".BASEDIR."');</script>";
    exit;
}

require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = unserialize($_SESSION['THOT']);
$matricule = $User->getMatricule();

$idTravail = $Application->postOrCookie('idTravail');
$coursGrp = $Application->postOrCookie('coursGrp');

require_once INSTALL_DIR.'/inc/classes/Files.class.php';
$Files = new Files();

$listeTravauxCours = $Files->getTravaux4Cours($coursGrp, array('readonly', 'readwrite', 'termine'), $matricule);

if (in_array($idTravail, array_keys($listeTravauxCours))) {
    $detailsTravail = $Files->getDetailsTravail($idTravail, $matricule);
    $listeCotes = $Files->getCotesTravail ($idTravail, $matricule);
    $totalTravail = $Files->totalisation($listeCotes);
    $onglet = isset($_COOKIE['ongletCasiers']) ? $_COOKIE['ongletCasiers'] : 'consignes';

    require_once(INSTALL_DIR.'/smarty/Smarty.class.php');
    $smarty = new Smarty();
    $smarty->template_dir = '../../templates';
    $smarty->compile_dir = '../../templates_c';

    $smarty->assign('idTravail', $idTravail);
    $smarty->assign('detailsTravail', $detailsTravail);
    $smarty->assign('onglet', $onglet);
    $smarty->assign('listeCotes', $listeCotes);
    $smarty->assign('totalTravail', $totalTravail);
    $smarty->display('casiers/detailsTravail.tpl');
}

else echo '';
