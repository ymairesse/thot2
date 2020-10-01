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

$formulaire = isset($_POST['formulaire']) ? $_POST['formulaire'] : Null;
$form = array();
parse_str($formulaire, $form);

$idCategorie = isset($form['idCategorie']) ? $form['idCategorie'] : Null;
$idSujet = isset($form['idSujet']) ? $form['idSujet'] : Null;
$postId = isset($form['postId']) ? $form['postId'] : Null;

require_once INSTALL_DIR.'/inc/classes/class.thotForum.php';
$Forum = new ThotForum();

$okProprio = $Forum->verifProprio($matricule, $postId, $idSujet, $idCategorie);
$hasChildren = $Forum->hasChildren($idCategorie, $idSujet, $postId);

if ($okProprio) {
    if ($hasChildren == 1) {
        $nb = $Forum->clearPost($matricule, $postId, $idSujet, $idCategorie);
        echo 1;
        }
        else {
            $nb = $Forum->delPost($matricule, $postId, $idSujet, $idCategorie);
            echo 0;
        }
}
else echo "Vous n'êtes par l'auteur de ce post";
