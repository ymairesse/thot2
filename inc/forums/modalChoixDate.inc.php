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

$laDate = isset($_POST['laDate']) ? $_POST['laDate'] : Null;

$test = ($laDate != '') ? explode('/', $laDate) : Null;

if ((count($test) == 3) && checkDate($test[1], $test[0], $test[2]))
    $today = trim($laDate);
    else $today = strftime('%d/%m/%Y');

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('today', $today);
$smarty->display('forums/modal/modalDate.tpl');
