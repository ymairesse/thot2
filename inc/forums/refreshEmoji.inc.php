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
$postId = isset($_POST['postId']) ? $_POST['postId'] : Null;
$emoji = isset($_POST['emoji']) ? $_POST['emoji'] : Null;

// définition de la class Forum
require_once INSTALL_DIR.'/inc/classes/class.thotForum.php';
$Forum = new ThotForum();

$like = $Forum->getEmoji4user($idCategorie, $idSujet, $postId, $matricule);
$emoji = ($like != Null) ? key($like) : Null;

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('postId', $postId);
$smarty->assign('idCategorie', $idCategorie);
$smarty->assign('idSujet', $idSujet);
$smarty->assign('emoji', $emoji);

$smarty->display('forums/fbReactions.tpl');
