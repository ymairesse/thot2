<?php

require_once '../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

// définition de la class USER utilisée en variable de SESSION
require_once INSTALL_DIR.'/inc/classes/classUser.inc.php';
session_start();

if (!(isset($_SESSION[APPLICATION]))) {
    echo "<script type='text/javascript'>document.location.replace('".BASEDIR."');</script>";
    exit;
}

$User = unserialize($_SESSION['THOT']);
$matricule = $User->getMatricule();

require_once INSTALL_DIR.'/inc/classes/Files.class.php';
$Files = new Files();

$idTravail = isset($_POST['idTravail']) ? $_POST['idTravail'] : null;

$detailsTravail = $Files->getDetailsTravail($idTravail, $matricule);
$verif = $Files->verifEleve4Travail($matricule, $idTravail);

$ds = DIRECTORY_SEPARATOR;

if (!empty($_FILES) && ($verif == true)) {
    $idTravail = $detailsTravail['idTravail'];
    $acronyme = $detailsTravail['acronyme'];
    $tempFile = $_FILES['file']['tmp_name'];
    $targetPath = INSTALL_ZEUS.$ds.'upload'.$ds.$acronyme.$ds.'#thot'.$ds.$idTravail.$ds.$matricule;
    $targetFile = $targetPath.$ds.$_FILES['file']['name'];
    // créer le répertoire s'il n'existe pas encore
    if (!is_dir($targetPath)) {
        mkdir($targetPath, 0700, true);
		}
    if (move_uploaded_file($tempFile, $targetFile)) {
        $Files->ajusteDocumentsRemis($idTravail, $matricule, +1);
        return true;
		}
    else {
        return false;
    }

}
