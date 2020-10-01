<?php

require_once '../../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();
session_start();

if (!(isset($_SESSION[APPLICATION]))) {
    echo "<script type='text/javascript'>document.location.replace('".BASEDIR."');</script>";
    exit;
}

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = unserialize($_SESSION[APPLICATION]);

$matricule = $User->getMatricule();

require_once INSTALL_DIR."/inc/classes/classJdc.inc.php";
$Jdc = new Jdc();

$listeCours = $User->listeDetailCoursEleve();

$classe = $User->getClasse();


$ds = DIRECTORY_SEPARATOR;
require_once(INSTALL_DIR."/smarty/Smarty.class.php");
$smarty = new Smarty();
$smarty->template_dir = INSTALL_DIR.$ds."templates";
$smarty->compile_dir = INSTALL_DIR.$ds."templates_c";

$smarty->assign('listeCours', $listeCours);
$smarty->assign('classe', $classe);
$smarty->display('jdc/selectCoursClasse.tpl');
