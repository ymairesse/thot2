<?php

// // définition de la class USER utilisée en variable de SESSION
// require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
// session_start();

if (!(isset($_SESSION[APPLICATION]))) {
    echo "<script type='text/javascript'>document.location.replace('".BASEDIR."');</script>";
    exit;
}

$User = unserialize($_SESSION['THOT']);
$matricule = $User->getMatricule();

$classe = $User->getClasse();

require_once INSTALL_DIR.'/inc/classes/classEcole.inc.php';
$Ecole = new Ecole();

$listeElevesClasse = $Ecole->listeEleves($classe, 'groupe');
$listeCours = $Ecole->listeCoursGrpEleve($matricule);

$smarty->assign('matricule', $matricule);
$smarty->assign('classe', $classe);
$smarty->assign('listeElevesClasse', $listeElevesClasse);
$smarty->assign('listeCours', $listeCours);

$smarty->assign('corpsPage', 'gestMails/listeMails');
