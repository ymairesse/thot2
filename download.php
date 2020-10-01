<?php

require_once 'config.inc.php';

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

$userName = $User->getUserName();
$userType = $User->getUserType();

// téléchargement sur base du fileId ou du nom du fichier et du path?
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
// éventuellement, le $fileId
$fileId = isset($_REQUEST['fileId']) ? $_REQUEST['fileId'] : null;
// éventuellement le idTravail
$idTravail = isset($_GET['idTravail']) ? $_GET['idTravail'] : null;
// éventuellement, le nom du fichier et son path depuis le répertoire partagé
$fileName = isset($_REQUEST['fileName']) ? $_REQUEST['fileName'] : null;

$fileNotFound = 'Document non identifié';
$noAccess = 'Vous n\'avez pas accès à ce document';

// vérifier dans la table des shares si l'utilisateur courant a accès au fichier
$classe = $User->getClasse();
$niveau = substr($classe, 0, 1);
$listeCoursEleve = $User->listeCoursEleve();
$listeCoursString = "'".implode("','", $listeCoursEleve)."'";

$ds = DIRECTORY_SEPARATOR;

switch ($type) {
    case 'pfN':  // par fileName, pour les documents partagés par répertoires
        // il nous faut le fileName et le fileId
        if (($fileName == null) || ($fileId == null)) {
            die($fileNotFound);
        }

        // liste des documents auquel l'élève a accès
        $listeDocs = $Files->listeDocsEleve($matricule, $classe, $niveau, $listeCoursString);

        // si le répertoire $fileId est dans les documents partagés avec cet élève
        if (in_array($fileId, array_keys($listeDocs))) {
            $shareId = $listeDocs[$fileId]['shareId'];
            $fileData = $Files->getFileData($fileId);

            // le fichier qui sera réellement téléchargé dans le répertoire partagé
            // nécessaire pour le suivi des téléchargements
            $downloadedFileInfo = array(
                'path' => $fileData['path'].$ds.$fileData['fileName'],
                'fileName' => substr($fileName, strrpos($fileName, '/') + 1),
            );
            $download_path = INSTALL_ZEUS.$ds.'upload'.$ds.$fileData['acronyme'].$fileData['path'].$ds.$fileData['fileName'];

            // le document est-il dans un sous-répertoire? On l'extrait du fileName
            $sousRepertoire = substr($fileName, 0, strrpos($fileName,'/') + 1);
            // ce qui suit le dernier "/" est le nom du fichier
            $fileName = substr($fileName, strrpos($fileName, '/') + 1);

            // on ajoute le sous-repertoire au path
            $download_path .= $ds.$sousRepertoire;
            // suppression des "//" doubles
            $download_path = preg_replace('~/+~', '/', $download_path);

        } else {
            die($noAccess);
        }
        break;
    case 'tr':  // récupération d'un travail personnel
        // il nous faut un idTravail et un fileName
        if (($idTravail == null) || ($fileName == null)) {
            die($fileNotFound);
        }
        $travailData = $Files->getDetailsTravail($idTravail, $matricule);
        $acronyme = $travailData['acronyme'];
        $download_path = INSTALL_ZEUS.$ds.'upload'.$ds.$acronyme.$ds.'#thot'.$ds.$idTravail.$ds.$matricule.$ds;
        $download_path = preg_replace('~/+~', '/', $download_path);
        break;

    case 'pId':   // lecture d'un fichier partagé par fileId
        if ($fileId == null) {
            die($fileNotFound);
        }
        $listeDocs = $Files->listeDocsEleve($matricule, $classe, $niveau, $listeCoursString);

        // si le fichier figure parmi les documents partagés avec cet élève
        if (in_array($fileId, array_keys($listeDocs))) {
            // récupérer les données du fichier
            $shareId = $listeDocs[$fileId]['shareId'];
            $fileData = $Files->getFileData($fileId);
            $fileName = $fileData['fileName'];
            $download_path = INSTALL_ZEUS.$ds.'upload'.$ds.$fileData['acronyme'].$fileData['path'].$ds;
        } else {
            die($noAccess);
        }
        break;
    default:
        die('unknown type');
        break;
}

if (file_exists($download_path.$ds.$fileName)) {
    $args = array(
            'download_path' => $download_path,
            'file' => $fileName,
            'extension_check' => true,
            'referrer_check' => false,
            'referrer' => null,
            );
} else {
    die('Fichier inexistant');
}

require_once INSTALL_DIR.'/inc/classes/class.chip_download.php';
$download = new chip_download($args);

/*
|-----------------
| Pre Download Hook
|------------------
*/

$download_hook = $download->get_download_hook();

if ($download_hook['download'] != 1) {
    echo "Ce type de fichier n'est pas autorisé";
}
// $download->chip_print($download_hook);
// exit;

/*
|-----------------
| Download
|------------------
*/

if ($download_hook['download'] == true) {

    /* You can write your logic before proceeding to download */
    // enregistrement du suivi de téléchargement pour le document
    if (isset($shareId))
    $spyInfo = $Files->getSpyInfo4ShareId($shareId);

    // il y a un espion sur le fichier ou le répertoire
    if (!(empty($spyInfo))) {
        $spyId = $spyInfo['spyId'];
        $path = (isset($downloadedFileInfo['path'])) ? $downloadedFileInfo['path'] : Null;
        // suppression des doubles "/" éventuels
        $path = preg_replace('~/+~', '/', $path);
        $fileName = (isset($downloadedFileInfo['fileName'])) ? $downloadedFileInfo['fileName'] : Null;
        $Files->setSpiedDownload($userName, $userType, $spyId, $path, $fileName);
    }

    /* Let's download file */
    $download->get_download();
}
