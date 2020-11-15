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

// définition de la class Forum
require_once INSTALL_DIR.'/inc/classes/class.thotForum.php';
$Forum = new ThotForum();

$isAbonne = $Forum->getAbonnement($matricule, $idCategorie, $idSujet);

if ($postId != 0) {
    // c'est une réponse à un post précédent
    $postAncien = $Forum->getInfoPost($idCategorie, $idSujet, $postId);
    }
    else {
        // c'est un post à la racine
        $postAncien = array(
            'idCategorie' => $idCategorie,
            'idSujet' => $idSujet,
            'postId' => $postId
            );
    }

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('postAncien', $postAncien);
$smarty->assign('isAbonne', $isAbonne);

$smarty->display('forums/modal/modalAnswerPost.tpl');
