<?php

session_start();

require_once 'config.inc.php';

// définition de la class Application
require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

// définition de la class User
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';

// extraire l'identifiant et le mot de passe
// l'identifiant est passé en majuscules => casse sans importance
$userName = (isset($_POST['userName'])) ? $_POST['userName'] : null;
$mdp = (isset($_POST['mdp'])) ? $_POST['mdp'] : null;
$userType = isset($_POST['userType']) ? $_POST['userType'] : null;

// Les données userName et mdp ont été postées dans le formulaire de la page accueil.php
if (!empty($userName) && (!empty($mdp)) && !(empty($userType))) {
    // recherche de toutes les informations sur l'utilisateur et les applications activées
    $User = new user($userName, $userType);

    // vérification du mot de passe
    if ($User->getPasswd() == md5($mdp)) {
        // mettre à jour la session avec les infos de l'utilisateur
        $_SESSION[APPLICATION] = serialize($User);
        header('Location: index.php');
    } else {
        header('Location: accueil.php?message=faux');
    }
} else {
        // le nom d'utilisateur ou le mot de passe n'ont pas été donnés
    header('Location: accueil.php?message=manque');
    }
