<?php

/**
 * Entêtes suffisantes pour un accès sans authentification
 * --------------------------------------------------------.
 */

// définition de la class USER utilisée pour l'identité réseau seulement
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = new User();

// définition de la class Application
require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();



// définition de la class Chrono
require_once INSTALL_DIR.'/inc/classes/classChrono.inc.php';
$chrono = new chrono();

$Application->Normalisation();

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->assign('TITREGENERAL', TITREGENERAL);
$smarty->assign('ECOLE', ECOLE);
$smarty->assign('WEBECOLE', WEBECOLE);
$smarty->assign('ADRESSEECOLE', ADRESSEECOLE);

// toutes les informations d'identification réseau (adresse IP, jour et heure)
$smarty->assign('identiteReseau', $User->identiteReseau());

// récupération de 'action' et 'mode' qui définissent toujours l'action principale à prendre
// d'autres paramètres peuvent être récupérés plus loin
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : null;
$etape = isset($_REQUEST['etape']) ? $_REQUEST['etape'] : null;

/* pas de balise ?> finale, c'est volontaire */
