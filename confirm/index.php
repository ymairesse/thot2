<?php

require_once '../config.inc.php';
include INSTALL_DIR.'/inc/entetesMin.inc.php';

// information provenant du lien cliqué par le candidat utilisateur de la plate-forme
$userName = isset($_GET['userName']) ? $_GET['userName'] : Null;
$mail = isset($_GET['mail']) ? $_GET['mail'] : null;
$token = isset($_GET['token']) ? $_GET['token'] : null;

$confirmation = $Application->confirmeParent($userName, $mail, $token);

$smarty->assign('confirmation', $confirmation);
$smarty->assign('ADRESSETHOT', ADRESSETHOT);
$smarty->assign('corpsPage', 'confirmationParent');

//
// ----------------------------------------------------------------------------
$smarty->assign('executionTime', round($chrono->stop(), 6));
$smarty->display('index.tpl');
