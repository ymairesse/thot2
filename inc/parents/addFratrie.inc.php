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
$userName = $User->getUserName();

$newUser = (isset($_POST['userName'])) ? $_POST['userName'] : null;
$passwd = (isset($_POST['passwd'])) ? $_POST['passwd'] : null;

if ($User->checkParentPasswd($newUser, $passwd) == true) {
    // initialisation éventuelle de la fratrie ($userName avec $userName)
    $User->initFratrie($userName);
    // initialisation éventuelle pour le $newUser
    $User->initFratrie($newUser);
    // ajout du lien de fraternité
    $nb = $User->add2Fratrie($userName, $newUser);
    // ajout du lien inverse
    $nb = $User->add2Fratrie($newUser, $userName);
    if ($nb == 0)
        $message = 'Rien à enregistrer';
        else $message = 'Le lien de famille a été enregistré';
}
else $message = 'Mot de passe incorrect';

echo $message;
