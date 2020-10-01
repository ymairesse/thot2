<?php

require_once '../config.inc.php';

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

require_once INSTALL_DIR.'/inc/classes/Files.class.php';
$Files = new Files();

$fileId = isset($_POST['fileId']) ? $_POST['fileId'] : null;

$classe = $User->getClasse();
$niveau = substr($classe, 0, 1);
$listeCoursEleve = $User->listeCoursEleve();
$listeCoursString = "'".implode("','", $listeCoursEleve)."'";

$listeSharedFiles = $Files->getSharedFiles($matricule, $classe, $niveau, $listeCoursString);

if (in_array($fileId, $listeSharedFiles)) {
    $path = $Files->getPathByFileId($fileId);
    $infos = $Files->getFileData($fileId);
    $fileName = $infos['fileName'];
    $acronyme = $infos['acronyme'];

    $ds = DIRECTORY_SEPARATOR;
    require_once INSTALL_DIR.'/inc/classes/class.Treeview.php';

// die(INSTALL_ZEUS.$ds.'upload'.$ds.$acronyme.$path.$ds.$fileName);

    $Treeview = new Treeview(INSTALL_ZEUS.$ds.'upload'.$ds.$acronyme.$path.$ds.$fileName);

    require_once(INSTALL_DIR."/smarty/Smarty.class.php");
    $smarty = new Smarty();
    $smarty->template_dir = "../templates";
    $smarty->compile_dir = "../templates_c";

    $smarty->assign('tree', $Treeview->getTree());
    $smarty->assign('fileId', $fileId);
    echo $smarty->fetch('files/treeview.tpl');
}
