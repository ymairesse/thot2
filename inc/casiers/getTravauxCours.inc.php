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

$coursGrp = isset($_POST['coursGrp']) ? $_POST['coursGrp'] : Null;
$idTravail = isset($_POST['idTravail']) ? $_POST['idTravail'] : Null;

$listeTravauxCours = $Files->getTravaux4Cours($coursGrp, array('readonly', 'readwrite', 'termine'), $matricule);
$listeArchives = $Files->getTravaux4Cours($coursGrp, array('archive'), $matricule);

require_once(INSTALL_DIR.'/smarty/Smarty.class.php');
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$onglet = isset($_COOKIE['ongletCasiers']) ? $_COOKIE['ongletCasiers'] : 'consignes';

$smarty->assign('listeTravauxCours', $listeTravauxCours);
$smarty->assign('listeArchives', $listeArchives);
$smarty->assign('coursGrp', $coursGrp);
$smarty->assign('idTravail', $idTravail);
$smarty->assign('onglet', $onglet);

$smarty->display('casiers/listeTravauxCours.tpl');
