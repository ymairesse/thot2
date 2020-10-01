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

$matricule = $User->getMatricule();

require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = unserialize($_SESSION[APPLICATION]);

$matricule = $User->getMatricule();

require_once INSTALL_DIR.'/inc/classes/Files.class.php';
$Files = new Files();

$idTravail = isset($_POST['idTravail']) ? $_POST['idTravail'] : null;
$coursGrp = isset($_POST['coursGrp']) ? $_POST['coursGrp'] : null;
$cours = substr($coursGrp, 0, strpos($coursGrp, '-', 0));

$evaluation = $Files->getEvaluationTravail($idTravail, $matricule);

// selon la section dans laquelle se trouve l'élève, il faut chercher les "compétences"
// dans le bulltin GT ou dans le bulletin TQ
$section = $User->getSection();
$bulletinTQ = array('TQ');
$bullISND = array('G', 'TT', 'S');

if (in_array($section, $bullISND)) {
    require_once INSTALL_DIR.'/inc/classes/classBulletin.inc.php';
    $Bulletin = new Bulletin();
    $listeCompetences = $Bulletin->listeCompetencesListeCours($cours);
} else {
    require_once INSTALL_DIR.'/inc/classes/class.BullTQ.php';
    $BullTQ = new bullTQ();
    $listeCompetences = $BullTQ->listeCompetencesListeCours($cours);
}

$listeCompetences = $listeCompetences[$cours];

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../../templates';
$smarty->compile_dir = '../../templates_c';

$smarty->assign('evaluation', $evaluation);
$smarty->assign('listeCompetences', $listeCompetences);

echo $smarty->fetch('files/detailsResultat.tpl');
