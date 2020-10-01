<?php

// définition de la class Formulaires
require_once INSTALL_DIR.'/inc/classes/formulaires.class.php';
$Formulaires = new Formulaires();

require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = unserialize($_SESSION['THOT']);

$matricule = $User->getMatricule();
$classe = $User->getClasse();
$niveau = substr($classe, 0, 1);
$listeCoursEleve = $User->listeCoursEleve();

// création de la liste des formulaires pour l'élève, fonction de son matricule, de sa classe
// -et donc de son niveau d'étude- et de sa liste de cours pour chacune des catégories: élève, cours, classe, niveau, école
$listeFormulaires = $Formulaires->listeFormulaires($matricule, $classe, $niveau, $listeCoursEleve);

if ($mode == 'enregistrer') {
    $listeReponses = $Formulaires->listeReponses($listeFormulaires, $matricule);
    // vérifier que les réponses n'existent pas déjà avant d'enregistrer
    if ($listeReponses == null) {
        $nb = $Formulaires->enregistrer($_POST, $matricule);
        $message = array(
            'title' => SAVE,
            'texte' => sprintf('%d information(s) enregistrée(s)', $nb),
            'urgence' => 'success',
            );
        $smarty->assign('message', $message);
    }
}

$listeQuestions = $Formulaires->listeQuestions($listeFormulaires);
$listeReponses = $Formulaires->listeReponses($listeFormulaires, $matricule);
$smarty->assign('listeFormulaires', $listeFormulaires);
$smarty->assign('listeQuestions', $listeQuestions);
$smarty->assign('listeReponses', $listeReponses);

// présenter les formulaires
$smarty->assign('corpsPage', 'formulaire');
