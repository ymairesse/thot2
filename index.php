<?php

session_start();

require_once 'config.inc.php';
include 'inc/entetes.inc.php';

$matricule = $User->getMatricule();
$classe = $User->getClasse();
$annee = SUBSTR($classe, 0, 1);
$userType = $User->getUserType();
if ($userType == 'parent') {
    $userName = $User->getUserName();
}
else $userName = Null;

$smarty->assign('userName', $userName);
$smarty->assign('identite', $User->getIdentite());
$smarty->assign('matricule', $matricule);
$smarty->assign('nom', $User->getNom());
$smarty->assign('nomEleve', $User->getNomEleve());
$smarty->assign('userType', $userType);
$smarty->assign('ECOLE', ECOLE);
$smarty->assign('WEBECOLE', WEBECOLE);
$smarty->assign('ADRESSEECOLE', ADRESSEECOLE);

// filtrer les actions possibles selon le type d'utilisateur; si pas d'accès pour une "action", la fonction renvoie Null
$action = $Application->filtreAction($action, $userType);

switch ($action) {
    case 'annonces':
        require_once 'inc/annonces.inc.php';
        break;
    case 'documents':
        require_once 'inc/documents.inc.php';
        break;
    case 'casiers':
        require_once 'inc/casiers.inc.php';
        break;
    case 'bulletin':
        require_once 'inc/bulletin.inc.php';
        break;
    case 'repertoire':
        require_once 'inc/evaluations.inc.php';
        break;
    case 'remediation':
        require_once 'inc/remediation.inc.php';
        break;
    case 'anniversaires':
        require_once 'inc/anniversaires.inc.php';
        break;
    case 'jdc':
        require_once 'inc/jdc.inc.php';
        break;
    case 'parents':
        require_once 'inc/parents.inc.php';
        break;
    case 'frereSoeur':
        require_once 'inc/parents/frereSoeur.inc.php';
        break;
    case 'profil':
        require_once 'inc/profil.inc.php';
        break;
    case 'contact':
        require_once 'inc/contact.inc.php';
        break;
    case 'comportement':
        require_once 'inc/comportement.inc.php';
        break;
    case 'reunionParents':
        require_once 'inc/reunionParents.inc.php';
        break;
    case 'mails':
        require_once 'inc/gestMails.inc.php';
        break;
    case 'form':
        require_once 'inc/formulaires.inc.php';
        break;
    case 'logoff':
        include_once 'logout.php';
        break;
    case 'info':
        require_once 'inc/info.inc.php';
        break;
    case 'forums':
        require_once 'inc/forums/gestForums.php';
        break;
    default:
        require_once 'inc/annonces.inc.php';
        break;
}

$smarty->assign('action', $action);
$smarty->assign('User', $User->getIdentite());

// toutes les informations d'identification réseau (adresse IP, jour et heure)
$smarty->assign('identiteReseau', user::identiteReseau());

$smarty->assign('TITREGENERAL', TITREGENERAL);
$smarty->assign('executionTime', round($chrono->stop(), 6));
$smarty->display('index.tpl');
