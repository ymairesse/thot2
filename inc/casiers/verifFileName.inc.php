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

require_once INSTALL_DIR.'/inc/classes/Files.class.php';
$Files = new Files();

$idTravail = isset($_POST['idTravail']) ? $_POST['idTravail'] : Null;
$fileName = isset($_POST['fileName']) ? $_POST['fileName'] : Null;

require_once '../../smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

if ($Files->verifEleve4Travail($matricule, $idTravail)) {
    $details = $Files->getDetailsTravail($idTravail, $matricule);
    $acronyme = $details['acronyme'];
    $fileInfos = $Files->getFileInfos($matricule, $idTravail, $fileName, $acronyme);
    $size = $fileInfos['size'];

    $smarty->assign('fileName', $fileName);
    $smarty->assign('size', $size);
    $smarty->assign('idTravail', $idTravail);

    $smarty->display('casiers/modal/modalDelFile.tpl');
}
