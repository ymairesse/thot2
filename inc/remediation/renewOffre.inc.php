<?php

require_once '../../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();
session_start();

if (!(isset($_SESSION[APPLICATION]))) {
    echo "<script type='text/javascript'>document.location.replace('".BASEDIR."');</script>";
    exit;
}

require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = unserialize($_SESSION[APPLICATION]);

$eleve = $User->getIdentite();
$matricule = $eleve['matricule'];
$niveau = SUBSTR($eleve['groupe'],0,1);
$classe = $eleve['groupe'];

require_once INSTALL_DIR.'/inc/classes/classBulletin.inc.php';
$Bulletin = new Bulletin();

// liste des cours actuellement suivis par l'élève
$listeCoursGrp = array_keys(current($Bulletin->listeCoursGrpActuelsEleve($eleve['matricule'])));
$listeMatieres = array_keys($Bulletin->listeCoursEleves($matricule));

require_once INSTALL_DIR.'/inc/classes/class.remediation.php';
$Remediation = new Remediation();

// liste des offres auxquelles l'élève est invité
$listeOffres = $Remediation->getOffresRemediations($niveau, $classe, $listeCoursGrp, $listeMatieres);
$occupations = $Remediation->getOccupations(array_keys($listeOffres));

require_once(INSTALL_DIR."/smarty/Smarty.class.php");
$smarty = new Smarty();
$smarty->template_dir = INSTALL_DIR."/templates";
$smarty->compile_dir = INSTALL_DIR."/templates_c";

$smarty->assign('listeOffres', $listeOffres);
$smarty->assign('occupations', $occupations);

$smarty->display('remediation/offresRemediation.tpl');
