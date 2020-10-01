<?php

require_once '../../config.inc.php';

session_start();

// définition de la class Application
require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = unserialize($_SESSION[APPLICATION]);

$matricule = $User->getMatricule();

$coursGrp = isset($_POST['coursGrp']) ? $_POST['coursGrp'] : Null;

require_once INSTALL_DIR.'/inc/classes/classEcole.inc.php';
$Ecole = new Ecole();

$listeElevesCours = $Ecole->listeElevesCours($coursGrp);

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = INSTALL_DIR.'/templates';
$smarty->compile_dir = INSTALL_DIR.'/templates_c';

$smarty->assign('listeElevesCours', $listeElevesCours);
$smarty->display('gestMails/listeAdressesCours.tpl');
