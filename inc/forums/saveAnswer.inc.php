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

// abonnement ou désabonnement à ce sujet
$isAbonne = isset($form['subscribe']) ? $form['subscribe'] : Null;

require_once INSTALL_DIR.'/inc/classes/classEcole.inc.php';
$Ecole = new Ecole();

// vérifier que l'élève est invité sur ce sujet
$listeCoursGrp = $Ecole->getListeCoursGrp4eleve($matricule);
$listeCoursGrp = isset($listeCoursGrp) ? array_reverse($listeCoursGrp) : Null;
$listeMatieres = isset($listeCoursGrp) ? $Ecole->getListeMatieresEleve(array_keys($listeCoursGrp)) : Null;

require_once INSTALL_DIR.'/inc/classes/class.thotForum.php';
$Forum = new ThotForum();

// vérifier que l'utilisateur courant a accès à la catégorie et au sujet
$okAcces = $Forum->verifieAccess($idSujet, $idCategorie, $classe, $niveau, $listeCoursGrp, $listeMatieres);

// s'il s'agit d'une réponse vérifier que le post ancien fait bien partie de la catégorie et du sujet
if ($postId != 0)
    $okPost = $Forum->verifiePost($postId, $idCategorie, $idSujet);
    else $okPost = true;

if ($okAcces && $okPost) {
	// convertir les balises http(s) en vrais liens cliquables
	$myPost = preg_replace('$(\s|^)(https?://[a-z0-9_./?=&#\$-]+)(?![^<>]*>)$i', ' <a href="$2" target="_blank">$2</a>', $myPost." ");
    $newPostId = $Forum->saveNewPost($myPost, $idSujet, $idCategorie, $postId, $matricule);

    // enregistrement éventuel de l'abonnement
    if ($isAbonne != Null)
        $Forum->setAbonnement($matricule, $idCategorie, $idSujet);
        else $Forum->desAbonnement($matricule, $idCategorie, $idSujet);

    echo $newPostId;
}
