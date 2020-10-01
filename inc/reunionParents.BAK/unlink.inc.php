<?php

require_once("../../config.inc.php");

require_once(INSTALL_DIR.'/inc/classes/classUser.inc.php');
session_start();

require_once(INSTALL_DIR.'/inc/classes/classApplication.inc.php');
$Application = new Application();

$User = unserialize($_SESSION[APPLICATION]);
$identiteParent = $User->getIdentite();
$matricule = $identiteParent['matricule'];

$nomFichier = sprintf("%s.pdf",$matricule);

unlink("../../PDF/$nomFichier");
