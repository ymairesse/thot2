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

$idRP = isset($_POST['idRP']) ? $_POST['idRP'] : null;

$identite = $User->getIdentite();

$listeRV = $Application->getRVeleve($matricule, $idRP);

require_once '../../smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('listeRV', $listeRV);
$smarty->assign('User', $identite);

$smarty->display('../../templates/reunionParents/panneauListeRV.tpl');
