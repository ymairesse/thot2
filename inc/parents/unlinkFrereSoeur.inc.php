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
$proprio = $User->getUserName();

$identite = $User->getIdentite();
$proprio = $identite['userName'];

$userParent = isset($_POST['userParent']) ? $_POST['userParent'] : Null;

$nb = $User->unlink($proprio, $userParent);

if ($nb == 1)
    $message = 'Le lien de famille a été supprimé';
    else $message = 'Le lien n\'a pas été supprimé';

echo json_encode(array('nb' => $nb, 'messag' => $message));
