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
$classe = $User->getClasse();
$niveau = substr($classe, 0, 1);

$formulaire = isset($_POST['formulaire']) ? $_POST['formulaire'] : Null;
$form = array();
parse_str($formulaire, $form);

$idCategorie = isset($form['idCategorie']) ? $form['idCategorie'] : Null;
$idSujet = isset($form['idSujet']) ? $form['idSujet'] : Null;
$postId = isset($form['postId']) ? $form['postId'] : Null;
$myPost = isset($form['myPost']) ? $form['myPost'] : Null;

$isAbonne = isset($form['subscribe']) ? $form['subscribe'] : Null;

require_once INSTALL_DIR.'/inc/classes/classEcole.inc.php';
$Ecole = new Ecole();

// vérifier que l'élève est invité sur ce sujet pour un de ses cours ou pour une matière
$listeCoursGrp = $Ecole->getListeCoursGrp4eleve($matricule);
if ($listeCoursGrp != Null) {
    $listeCoursGrp = array_reverse($listeCoursGrp);
    $listeMatieres = $Ecole->getListeMatieresEleve(array_keys($listeCoursGrp));
    }
    else $listeMatieres = Null;

require_once INSTALL_DIR.'/inc/classes/class.thotForum.php';
$Forum = new ThotForum();

// vérifier que l'utilisateur courant a accès à la catégorie et au sujet
$okAcces = $Forum->verifieAccess($idSujet, $idCategorie, $classe, $niveau, $listeCoursGrp, $listeMatieres);

// convertir les balises http(s) en vrais liens cliquables
$myPost = preg_replace('$(\s|^)(https?://[a-z0-9_./?=&-]+)(?![^<>]*>)$i', ' <a href="$2" target="_blank">$2</a>', $myPost." ");

if ($okAcces) {
    $postId = $Forum->saveEditedPost($myPost, $idSujet, $idCategorie, $postId);
    if ($isAbonne != Null)
        $Forum->setAbonnement($matricule, $idCategorie, $idSujet);
        else $Forum->desAbonnement($matricule, $idCategorie, $idSujet);
    echo $postId;
}
