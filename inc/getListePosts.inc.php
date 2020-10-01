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

$idCategorie = isset($_POST['idCategorie']) ? $_POST['idCategorie'] : Null;
$idSujet = isset($_POST['idSujet']) ? $_POST['idSujet'] : Null;


// définition de la class Forum
require_once INSTALL_DIR.'/inc/classes/class.thotForum.php';
$Forum = new ThotForum();

$listePosts = $Forum->getPosts4subject($idCategorie, $idSujet);

$FBstats = $Forum->getFBstats4subject($idCategorie, $idSujet);

$likes4user = $Forum->getLikesOnSubject4user($matricule, $idCategorie, $idSujet);

$infoSujet = $Forum->getInfoSujet($idCategorie, $idSujet);

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('idCategorie', $idCategorie);
$smarty->assign('idSujet', $idSujet);

$smarty->assign('listePosts', $listePosts);
$smarty->assign('likes4user', $likes4user);
$smarty->assign('infoSujet', $infoSujet);
$smarty->assign('FBstats', $FBstats);

$smarty->assign('matricule', $matricule);

$smarty->display('forums/treeviewPosts.tpl');
