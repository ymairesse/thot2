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

$idCategorie = isset($_POST['idCategorie']) ? $_POST['idCategorie'] : Null;


// définition de la class Ecole
require_once INSTALL_DIR.'/inc/classes/classEcole.inc.php';
$Ecole = new Ecole();

$listeCoursGrp = array_reverse($Ecole->getListeCoursGrp4eleve($matricule));
$listeMatieres = $Ecole->getListeMatieresEleve(array_keys($listeCoursGrp));

// définition de la class Forum
require_once INSTALL_DIR.'/inc/classes/class.thotForum.php';
$Forum = new ThotForum();

$listeSujets = $Forum->getListeSujets4categorie($idCategorie, $matricule, $classe, $niveau, $listeMatieres, $listeCoursGrp);

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('idCategorie', $idCategorie);
$smarty->assign('listeSujets', $listeSujets);

$smarty->display('forums/listeSujets.tpl');
