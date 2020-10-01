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

$idOffre = isset($_POST['idOffre']) ? $_POST['idOffre'] : Null;

require_once INSTALL_DIR.'/inc/classes/class.remediation.php';
$Remediation = new Remediation();

// détails de l'offre visée
$offre = $Remediation->getOffre($idOffre);

// liste des offres auxquelles l'élève est invité
$listeOffres = $Remediation->getOffresRemediations($niveau, $classe, $listeCoursGrp, $listeMatieres);

$occupation = $Remediation->getOccupations(array($idOffre))[$idOffre];

// veiller à ce qu'un élève ne puisse être inscrit à une remédiation à laquelle il n'est pas invité
if (in_array($idOffre, array_keys($listeOffres)) AND $occupation <= $offre['places']) {
    $nb = $Remediation->subscribe($matricule, $idOffre);

    // les remédiations auxquelles l'élève est inscrit
    $remediationsEleve = $Remediation->getRemediationsEleve($matricule);

    require_once(INSTALL_DIR."/smarty/Smarty.class.php");
    $smarty = new Smarty();
    $smarty->template_dir = INSTALL_DIR."/templates";
    $smarty->compile_dir = INSTALL_DIR."/templates_c";

    $smarty->assign('remediationsEleve', $remediationsEleve);
    $smarty->display('remediation/prochainesRemediations.tpl');
}
