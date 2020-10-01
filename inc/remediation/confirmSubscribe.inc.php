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

$idOffre = isset($_POST['idOffre']) ? $_POST['idOffre'] : Null;

require_once INSTALL_DIR.'/inc/classes/class.remediation.php';
$Remediation = new Remediation();

$offre = $Remediation->getOffre($idOffre);

require_once(INSTALL_DIR.'/smarty/Smarty.class.php');
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('offre', $offre);
$smarty->assign('idOffre', $idOffre);

$smarty->display('remediation/subscribe.tpl');
