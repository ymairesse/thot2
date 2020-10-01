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

$userName = $User->getUserName();
$nomEleve = $User->getNomEleve();
$fratrie = $User->getComptesFratrie($userName);

$eleves = $User->getEleves4Parent($userName);
$matricule = $User->getMatricule();
$identite = $User->getIdentite();

require_once(INSTALL_DIR.'/smarty/Smarty.class.php');
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('fratrie', $fratrie);
$smarty->assign('eleves', $eleves);
$smarty->assign('matricule', $matricule);
$smarty->assign('userName', $userName);
$smarty->assign('nomEleve', $nomEleve);
$smarty->assign('identite', $identite);


$smarty->display('parents/fratrie.tpl');
