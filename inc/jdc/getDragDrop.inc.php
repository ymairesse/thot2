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



$id = isset($_POST['id']) ? $_POST['id'] : null;
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;
$allDay = isset($_POST['allDay']) ? $_POST['allDay'] : false;

require_once INSTALL_DIR."/inc/classes/classJdc.inc.php";
$Jdc = new Jdc();

// après un retour de position 'allDay', la date de fin est invalide
if ($endDate == 'Invalid date')
    $endDate = $startDate;

$startTime = explode(' ', $startDate)[1];
$endTime = explode(' ', $endDate)[1];

 if (($endTime == '00:00') && ($startTime == '00:00')) {
     $allDay = 1;
 } else {
        $allDay = 0;
    }

// Application::afficher($startDate);
// si l'événement ne commence pas à zéro heure, il n'est pas pour toute la journée
if ($startTime != '00:00') {
    $allDay = 0;
}

if ($id != null) {
    if ($id != $Jdc->verifIdProprio($id, $matricule))
        die('Cette note au JDC ne vous appartient pas');

    $resultat = $Jdc->modifEvent($id, $startDate, $endDate, $allDay);

    if ($resultat != 0) {
        echo "coucou";
        $id = explode('_', $id)[1];
        $travail = $Jdc->getNotePerso($id);
        // $categories = $Jdc->categoriesTravaux();
// Application::afficher($travail);
        $ds = DIRECTORY_SEPARATOR;
        require_once INSTALL_DIR.'/smarty/Smarty.class.php';
        $smarty = new Smarty();
        $smarty->template_dir = INSTALL_DIR.$ds.'templates';
        $smarty->compile_dir = INSTALL_DIR.$ds.'templates_c';

        $smarty->assign('travail', $travail);

        $smarty->display('jdc/notePerso.tpl');
    }
}
