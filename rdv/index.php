<?php

require_once '../config.inc.php';
include INSTALL_DIR.'/inc/entetesMin.inc.php';

// ----------------------------------------------------------------------------
//
// les paramètres peuvent éventuellement servir; autant les passer à Smarty
$smarty->assign('action', $action);
$smarty->assign('mode', $mode);

// $contact = isset($_REQUEST['contact'])?$_REQUEST['contact']:Null;
$contact = 'TAH';

// raffraîchissement de la table des RV pour les RV non confirmés depuis plus de 4 heures
$Application->refreshTableRv(4);

switch ($action) {

    case 'confirm':
        require_once('inc/confirmRV.inc.php');
        break;
    case 'save':
        require_once('inc/saveRv.inc.php');
        break;
    default:
        require_once('inc/choixRV.inc.php');
        break;
}

//
// ----------------------------------------------------------------------------
$smarty->assign('executionTime', round($chrono->stop(), 6));
$smarty->display('index.tpl');
