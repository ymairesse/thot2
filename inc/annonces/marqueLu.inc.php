<?php

require_once '../../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();
session_start();

require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = unserialize($_SESSION['THOT']);

$matricule = $User->getMatricule();

$notifId = isset($_POST['notifId']) ? $_POST['notifId'] : Null;

if ($notifId != Null) {
    $Application->marqueLu($matricule, $notifId);
    return $notifId;
}
