<?php

// définition de la class Application, y compris la lecture de config.ini
require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = isset($_SESSION[APPLICATION]) ? unserialize($_SESSION[APPLICATION]) : null;

// si pas d'utilisateur authentifié en SESSION et répertorié dans la BD, on renvoie à l'accueil
if ($User == null) {
	header('Location: accueil.php');
	exit;
}
// définition de la class Chrono
require_once INSTALL_DIR.'/inc/classes/classChrono.inc.php';
$chrono = new chrono();

$Application->Normalisation();
$module = $Application->repertoireActuel();

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->assign('TITREGENERAL', TITREGENERAL);
$smarty->assign('ECOLE', ECOLE);
$smarty->assign('WEBECOLE', WEBECOLE);
$smarty->assign('ADRESSEECOLE', ADRESSEECOLE);

// toutes les informations d'identité, y compris nom, prénom,,...
$smarty->assign('identite', $User->getIdentite());

// toutes les informations d'identification réseau (adresse IP, jour et heure)
$smarty->assign('identiteReseau', $User->identiteReseau());
$smarty->assign('nom', $User->userName());

// récupération de 'action' et 'mode' qui définissent toujours l'action principale à prendre
// d'autres paramètres peuvent être récupérés plus loin
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : null;
$etape = isset($_REQUEST['etape']) ? $_REQUEST['etape'] : null;
