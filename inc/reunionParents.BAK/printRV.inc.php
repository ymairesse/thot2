<?php

require_once("../../config.inc.php");

require_once(INSTALL_DIR.'/inc/classes/classUser.inc.php');
session_start();

require_once(INSTALL_DIR.'/inc/classes/classApplication.inc.php');
$Application = new Application();

$User = unserialize($_SESSION[APPLICATION]);
$identiteParent = $User->getIdentite();
$userName = $identiteParent['userName'];
$matricule = $identiteParent['matricule'];
$identiteEleve = $Application->listeElevesMatricules($matricule)[$matricule];

$date = isset($_POST['date'])?$_POST['date']:Null;
$module = isset($_POST['module'])?$_POST['module']:Null;

$listeRV = $Application->getRVeleve($matricule, $date);
$listeAttente = $Application->getListeAttenteEleve($matricule, $date);
$listeProfs = $Application->listeProfsCoursEleve($matricule);
$listePeriodes = $Application->getListePeriodes($date);
$listeEducs = $User->getEducsEleve();

require_once(INSTALL_DIR.'/smarty/Smarty.class.php');
$smarty = new Smarty();
$smarty->template_dir = "../templates";
$smarty->compile_dir = "../templates_c";

$smarty->assign('parent', $identiteParent);
$smarty->assign('eleve', $identiteEleve);
$smarty->assign('listeRV', $listeRV);
$smarty->assign('listeAttente', $listeAttente);
$smarty->assign('listeProfs', $listeProfs);
$smarty->assign('listeEducs', $listeEducs);
$smarty->assign('listePeriodes', $listePeriodes);
$smarty->assign('entete', sprintf('%s %s',ECOLE, ADRESSEECOLE));
$smarty->assign('date', $date);

$rv4PDF =  $smarty->fetch(INSTALL_DIR.'/templates/reunionParents/RVParents2pdf.tpl');

require_once(INSTALL_DIR."/html2pdf/html2pdf.class.php");
$html2pdf = new HTML2PDF('P','A4','fr');
$html2pdf->WriteHTML($rv4PDF);
$nomFichier = sprintf('%s.pdf',$userName);
$html2pdf->Output(INSTALL_DIR."/PDF/$nomFichier",'F');
