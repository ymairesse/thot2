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

$formulaire = isset($_POST['formulaire']) ? $_POST['formulaire'] : null;
$form = array();
parse_str($formulaire, $form);

$classe = $User->getClasse();

$startDate = $form['dateStart'];
$endDate = $form['dateEnd'];

if ($form['coursGrpClasse'] == 'all') {
    $listeCours = array_keys($User->listeDetailCoursEleve());
    array_push($listeCours, $classe);
    }
    else $listeCours = $form['coursGrpClasse'];

if ($form['categories'] == 'all')
    $listeCategories = array_keys($Jdc->categoriesTravaux());
    else $listeCategories = $form['categories'];

$listeNotes = $Jdc->fromToJDCList($startDate, $endDate, $listeCours, $listeCategories);

$ds = DIRECTORY_SEPARATOR;
require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = INSTALL_DIR.$ds.'templates';
$smarty->compile_dir = INSTALL_DIR.$ds.'templates_c';

$smarty->assign('listeNotes', $listeNotes);
$smarty->display('jdc/listeNotes.tpl');
