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

$id = isset($_POST['id']) ? $_POST['id'] : null;
$show = isset($_POST['show']) ? $_POST['show'] : null;

$travail = $Jdc->getTravail($id);

require_once(INSTALL_DIR."/smarty/Smarty.class.php");
$smarty = new Smarty();
$smarty->template_dir = INSTALL_DIR."/templates";
$smarty->compile_dir = INSTALL_DIR."/templates_c";

$smarty->assign('show', $show);
if ($show == 1)
    $smarty->assign('travail', $travail['enonce']);
    else $smarty->assign('travail', strip_tags($travail['enonce'], '<br><p><a>'));

$smarty->display('jdc/detailTravail.tpl');
