<?php

require_once '../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();
session_start();

$erreurSession = !(isset($_SESSION[APPLICATION]));
echo json_encode(array('ERREUR' => $erreurSession));
