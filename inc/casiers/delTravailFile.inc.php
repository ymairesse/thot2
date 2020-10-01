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

require_once INSTALL_DIR.'/inc/classes/Files.class.php';
$Files = new Files();

$idTravail = isset($_POST['idTravail']) ? $_POST['idTravail'] : Null;
$fileName = isset($_POST['fileName']) ? $_POST['fileName'] : Null;

$dataTravail = $Files->getDetailsTravail($idTravail, $matricule);
$acronyme = $dataTravail['acronyme'];

$ds = DIRECTORY_SEPARATOR;
// chemin complet vers le fichier (l'utilisateur courant ne peut effacer que des fichiers dans le répertoire $matricule)
$path = INSTALL_ZEUS.$ds.'upload'.$ds.$acronyme.$ds.'#thot'.$ds.$idTravail.$ds.$matricule.$ds.$fileName;

if (@unlink($path)) {
    echo $idTravail;
    }
    else echo -1;
