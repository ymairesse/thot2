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

$id = isset($_POST['id'])?$_POST['id']:Null;

$travail = $Jdc->getNotePerso($id);

if ($travail['matricule'] == $matricule) {
    $startDate = $travail['startDate'];

    require_once(INSTALL_DIR."/smarty/Smarty.class.php");
    $smarty = new Smarty();
    $smarty->template_dir = "../../templates";
    $smarty->compile_dir = "../../templates_c";

    $smarty->assign('travail',$travail);
    $smarty->assign('startDate',$startDate);

    $smarty->display('jdc/modalDel.tpl');
}
else {
    die('Cette note ne vous appartient pas');
}
