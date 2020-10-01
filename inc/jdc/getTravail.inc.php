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
$Jdc = new Jdc();

$id = isset($_POST['id']) ? $_POST['id'] : null;

if ($id != null) {
    // recherche du préfixe de l'identifiant ("Rem, par exemple")
    $arrayId = explode('_', $id);
    $id = (isset($arrayId[1])) ? $arrayId[1] : $id;
    $type = (isset($arrayId[1])) ? $arrayId[0] : Null;

    $titus = Null;

    switch ($type) {
        case 'Rem':
            $travail = $Jdc->getRemediation($id);
            $pj = Null;
            break;
        case 'Perso':
            $travail = $Jdc->getNotePerso($id);
            $pj = Null;
            break;
        case 'Shared':
            $travail = $Jdc->getShared($id);
            $pj = Null;
            break;
        case 'Coach':
            $travail = $Jdc->getCoaching($id);
            $pj = Null;
            break;
        default:
            $travail = $Jdc->getTravail($id);
            $pj = $Jdc->getPj($id);
            break;
        }

    if (isset($travail['proprietaire']) && $travail['proprietaire'] != '') {
        if ($type == 'Shared') {
            $matricule = $travail['proprietaire'];
            $identite = $Application->getIdentiteEleve($matricule);
            $nom = sprintf('%s %s, %s', $identite['prenom'], $identite['nom'], $identite['groupe']);
        }
        else {
            $acronyme = $travail['proprietaire'];
            $identite = $Application->identiteProf($acronyme);
            $adresse = ($identite['sexe'] == 'F') ? 'Mme' : 'M.';
            $nom = sprintf('%s %s. %s', $adresse, mb_substr($identite['prenom'], 0, 1, 'UTF-8'), $identite['nom']);
            $titus = $nom;
        }
    }

    require_once INSTALL_DIR.'/smarty/Smarty.class.php';
    $smarty = new Smarty();
    $smarty->template_dir = '../../templates';
    $smarty->compile_dir = '../../templates_c';

    $smarty->assign('travail', $travail);

    switch ($type) {
        case 'Rem':
            $smarty->display('jdc/uneRemediation.tpl');
            break;
        case 'Perso':
            $smarty->display('jdc/notePerso.tpl');
            break;
        case 'Shared':
            $smarty->assign('nom', $nom);
            $smarty->display('jdc/shared.tpl');
            break;
        case 'Coach':
            $smarty->display('jdc/unCoaching.tpl');
            break;
        default:
            $smarty->assign('matricule', $matricule);
            $smarty->assign('titus', $titus);
            $smarty->assign('pj', $pj);
            $smarty->display('jdc/unTravail.tpl');
            break;
    }
}
