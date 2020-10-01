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
$User = unserialize($_SESSION[APPLICATION]);
$matricule = $User->getMatricule();

require_once INSTALL_DIR.'/inc/classes/Files.class.php';
$Files = new Files();

$idTravail = $Application->postOrCookie('idTravail');

// détails du travail et liste des fichiers déposés
$detailsTravail = $Files->getDetailsTravail($idTravail, $matricule);
$listeCotes = $Files->getCotesTravail($idTravail, $matricule);
$totalTravail = $Files->totalisation($listeCotes);

require_once(INSTALL_DIR.'/smarty/Smarty.class.php');
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('idTravail', $idTravail);
$smarty->assign('detailsTravail', $detailsTravail);
$smarty->assign('listeCotes', $listeCotes);
$smarty->assign('totalTravail', $totalTravail);

$smarty->display('casiers/detailsUpload.inc.tpl');
