<?php

require_once 'config.inc.php';

// définition de la class Application
require_once 'inc/classes/classApplication.inc.php';
$Application = new Application();

// définition de la class User
require_once 'inc/classes/classUser.inc.php';
$User = new User();
session_start();

// définition de la class Chrono
require_once 'inc/classes/classChrono.inc.php';
$chrono = new chrono();

$Application->Normalisation();

$message = isset($_REQUEST['message']) ? $_REQUEST['message'] : null;

require_once 'smarty/Smarty.class.php';
$smarty = new Smarty();

// toutes les informations d'identification réseau (adresse IP, jour et heure)
$smarty->assign('identiteReseau', user::identiteReseau());
$smarty->assign('message', $message);
$smarty->assign('TITREGENERAL', TITREGENERAL);
$smarty->assign('ECOLE', ECOLE);
$smarty->assign('WEBECOLE', WEBECOLE);
$smarty->assign('ADRESSEECOLE', ADRESSEECOLE);

$smarty->assign('executionTime', round($chrono->stop(), 6));
$smarty->display('accueil.tpl');
