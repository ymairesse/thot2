<?php

session_start();
require_once '../config.inc.php';
include '../inc/entetes.inc.php';
// ----------------------------------------------------------------------------
//
// les paramètres peuvent éventuellement servir; autant les passer à Smarty
$smarty->assign('action', $action);
$smarty->assign('mode', $mode);

switch ($mode) {
    case 'choixAC1':
        require_once INSTALL_DIR.'inc/choixAC1.inc.php';
        break;

}

//
// ----------------------------------------------------------------------------
$smarty->assign('executionTime', round($chrono->stop(), 6));
$smarty->display('index.tpl');
