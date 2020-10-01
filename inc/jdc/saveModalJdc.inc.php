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

$matricule = $User->getMatricule();

require_once INSTALL_DIR."/inc/classes/classJdc.inc.php";
$Jdc = new Jdc();

// récupérer le formulaire d'encodage du JDC
$formulaire = isset($_POST['formulaire']) ? $_POST['formulaire'] : null;
$form = array();
parse_str($formulaire, $form);

$enonce = isset($_POST['enonce']) ? $_POST['enonce'] : null;

$form['enonce'] = $enonce;

$id = isset($form['id']) ? $form['id'] : Null;

// est-ce une mise à jour d'un enregistrement existant?
if ($id != null) {
    $verifId = $Jdc->verifIdRedacteur($id, $matricule);
    if ($id == $verifId) {
        $nb = $Jdc->saveJdc($form, $matricule);
        } else {
            die('Ce journal de classe ne vous appartient pas');
            }
    }
    // ou est-ce une nouvelle notification? // alors, on n'a pas encore d'id
    else {
        // on récupère l'id de l'enregistrement qui est renvoyé par la procédure
        $id = $Jdc->saveJdc($form, $matricule);
        $nb = ($id != null) ? 1 : 0;
    }

echo $id;
