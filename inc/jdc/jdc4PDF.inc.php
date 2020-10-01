<?php

require_once '../../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
session_start();

if (!(isset($_SESSION[APPLICATION]))) {
    echo "<script type='text/javascript'>document.location.replace('".BASEDIR."');</script>";
    exit;
}

require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = unserialize($_SESSION[APPLICATION]);

$matricule = $User->getMatricule();
$classe = $User->getClasse();

$ds = DIRECTORY_SEPARATOR;
require_once INSTALL_DIR.$ds."inc/classes/classJdc.inc.php";
$Jdc = new Jdc();

$startDate =  $_POST['dateStart'];
$endDate =  $_POST['dateEnd'];

if ( $_POST['coursGrpClasse'] == 'all') {
    $listeCours = array_keys($User->listeDetailCoursEleve());
    array_push($listeCours, $classe);
    }
    else $listeCours =  $_POST['coursGrpClasse'];

if ( $_POST['categories'] == 'all')
    $listeCategories = array_keys($Jdc->categoriesTravaux());
    else $listeCategories =  $_POST['categories'];

$jdcExtract = $Jdc->fromToJDCList($startDate, $endDate, $listeCours, $listeCategories);

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = INSTALL_DIR.$ds.'templates';
$smarty->compile_dir = INSTALL_DIR.$ds.'templates_c';

$DATE = $Application::dateNow();
$smarty->assign('ECOLE', ECOLE);
$smarty->assign('ADRESSE', ADRESSE);
$smarty->assign('TELEPHONE', TELEPHONE);
$smarty->assign('COMMUNE', COMMUNE);
$smarty->assign('DATE', $DATE);
$smarty->assign('ANNEESCOLAIRE', ANNEESCOLAIRE);
$smarty->assign('dateDebut', $startDate);
$smarty->assign('dateFin', $endDate);

$userName = $User->getNom();
$smarty->assign('userName', $userName);

$smarty->assign('jdcExtract', $jdcExtract);

$jdc4PDF = $smarty->fetch('jdc/jdc4PDF.tpl');

require_once INSTALL_DIR.'/html2pdf/html2pdf.class.php';
$html2pdf = new Html2PDF('P', 'A4', 'fr');

$html2pdf->WriteHTML($jdc4PDF);

$html2pdf->Output('JournalDeClasse.pdf');
