<?php
require_once("../../config.inc.php");

session_start();

$acronyme = isset($_POST['acronyme'])?$_POST['acronyme']:Null;
$date = isset($_POST['date'])?$_POST['date']:Null;

require_once(INSTALL_DIR.'/inc/classes/classApplication.inc.php');
$Application = new Application();

// récupérer la liste des RV
$listePeriodes = $Application->getListePeriodes($date);

require_once(INSTALL_DIR."/smarty/Smarty.class.php");
$smarty = new Smarty();
$smarty->template_dir = "../templates";
$smarty->compile_dir = "../templates_c";

$smarty->assign('listePeriodes',$listePeriodes);
$smarty->assign('acronyme',$acronyme);
$smarty->assign('date',$date);

$smarty->display('../../templates/reunionParents/listeAttente.tpl');
