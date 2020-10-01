<?php

require_once '../../config.inc.php';

require_once '../../inc/classes/classApplication.inc.php';
$Application = new Application();

session_start();

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
$User = isset($_SESSION[APPLICATION]) ? unserialize($_SESSION[APPLICATION]) : null;

// si pas d'utilisateur authentifié en SESSION et répertorié dans la BD, on renvoie à l'accueil
if ($User == null) {
    header('Location: accueil.php');
}

$typeDoc = isset($_POST['typeDoc']) ? $_POST['typeDoc'] : null;

$matricule = $User->getMatricule();
$anneeEtude = $User->getAnnee();
$classe = $User->getClasse();
$nomEleve = $User->getNomEleve();

$laDate = $Application->getDocDate($matricule, $typeDoc);

require_once '../../inc/classes/classBulletin.inc.php';
$Bulletin = new Bulletin();

$listeCours = $Bulletin->listeCoursEleves($matricule);
$listeCompetences = $Bulletin->listeCompetencesListeCours($listeCours);
$sommeCotes = $Bulletin->sommeToutesCotes(array($matricule=>$matricule), $listeCours, $listeCompetences);
$listeAcquis = $Bulletin->listeAcquis($sommeCotes);

require_once INSTALL_DIR.'/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../templates';
$smarty->compile_dir = '../templates_c';

$smarty->assign('classe', $classe);
$smarty->assign('listeCours', $listeCours);
$smarty->assign('listeCompetences', $listeCompetences);
$smarty->assign('listeAcquis', $listeAcquis);

$smarty->assign('laDate', $laDate);
$smarty->assign('typeDoc', $typeDoc);
$smarty->assign('matricule', $matricule);
// $smarty->assign('signature', $signature);
$smarty->assign('DIRECTION', DIRECTION);
$smarty->assign('ECOLE', ECOLE);
$smarty->assign('ADRESSE', ADRESSE);
$smarty->assign('VILLE', VILLE);

require_once INSTALL_DIR.'/html2pdf/html2pdf.class.php';
$html2pdf = new HTML2PDF('P', 'A4', 'fr');

$eleve = $User->getTousDetailsEleve();

$smarty->assign('unEleve', $eleve);

$doc4PDF = $smarty->fetch('../../templates/e-docs/piaCompetences2pdf.tpl');

$html2pdf->WriteHTML($doc4PDF);

$nomFichier = md5(sprintf('%s_%s', $typeDoc, $matricule));
$nomFichier = sprintf('%s_%s.pdf', $typeDoc, $nomFichier);
$smarty->assign('nomFichier', $nomFichier);

$chemin = INSTALL_DIR."/e-docs/pdf/";

$html2pdf->Output($chemin.$nomFichier, 'F');

$link = $smarty->fetch('../../templates/e-docs/lienDocument.tpl');
echo $link;
