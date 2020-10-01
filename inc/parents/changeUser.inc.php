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

// Application::afficher_silent($User);

$newUser = isset($_POST['newUser']) ? $_POST['newUser'] : null;
$oldUser = isset($_SESSION['oldUser']) ? $_SESSION['oldUser'] : Null;

$oldUser = $User->getOldUser();

$User = new user($newUser, 'parent', $oldUser);

// Application::afficher_silent($User);

$_SESSION[APPLICATION] = serialize($User);

$nomEleve = $User->getNomEleve();

echo $nomEleve;
