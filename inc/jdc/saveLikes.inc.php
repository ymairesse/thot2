<?php

require_once '../../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();
session_start();

if (!(isset($_SESSION[APPLICATION]))) {
    echo "<script type='text/javascript'>document.location.replace('".BASEDIR."');</script>";
    exit;
}

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = unserialize($_SESSION[APPLICATION]);

$matricule = $User->getMatricule();

require_once INSTALL_DIR."/inc/classes/classJdc.inc.php";
$jdc = new Jdc();

$id = isset($_POST['id']) ? $_POST['id'] : null;
$mode = isset($_POST['mode']) ? $_POST['mode'] : null;
$commentaire = isset($_POST['commentaire']) ? $_POST['commentaire'] : null;

$vote = ($mode == 'like') ? 1 : 0;

$jdc->saveLikes($id, $matricule, $vote, $commentaire);

echo json_encode($jdc->countLikes($id));
