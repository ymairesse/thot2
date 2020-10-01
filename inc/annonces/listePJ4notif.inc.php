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

$notifId = isset($_POST['notifId']) ? $_POST['notifId'] : Null;

$listePJ = $Application->getPJ4notifs($notifId, $matricule);
if ($listePJ != Null)
    $listePJ = $listePJ[$notifId];

require_once(INSTALL_DIR.'/smarty/Smarty.class.php');
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('listePJ', $listePJ);
$smarty->display('annonces/PJlist4download.tpl');
