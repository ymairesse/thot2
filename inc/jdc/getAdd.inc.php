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

require_once INSTALL_DIR."/inc/classes/classJdc.inc.php";
$Jdc = new Jdc();

$categories = $Jdc->categoriesTravaux();

$heure = isset($_POST['heure']) ? $_POST['heure'] : null;
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;

if ($heure != Null) {
    $heure = $Jdc->heureLaPlusProche($heure);
}

$listePeriodes = $Jdc->lirePeriodesCours();

$travail = array(
    'startDate' => $startDate,
    'heure' => $heure,
    'idCategorie' => Null,
    );

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('categories', $categories);
$smarty->assign('listePeriodes', $listePeriodes);

$smarty->assign('startDate', $startDate);
$smarty->assign('heure', $heure);
$smarty->assign('travail', $travail);


$smarty->display('jdc/jdcEdit.tpl');
