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
$User = unserialize($_SESSION['THOT']);

$matricule = $User->getMatricule();

require_once INSTALL_DIR.'/inc/classes/Files.class.php';
$Files = new Files();

$coursGrp = isset($_POST['coursGrp']) ? $_POST['coursGrp'] : null;

// vérifier que l'élève fait bien partie du coursGrp
$listeCours = $User->listeCoursEleve();
if (in_array($coursGrp, $listeCours)) {
    $listeArchives = $Files->getTravaux4Cours($coursGrp, array('archive'), $matricule);

    require_once(INSTALL_DIR.'/smarty/Smarty.class.php');
    $smarty = new Smarty();
    $smarty->template_dir = '../../templates';
    $smarty->compile_dir = '../../templates_c';

    $smarty->assign('listeTravauxCours', $listeArchives);
    $smarty->display('casiers/listeArchives.tpl');
}
