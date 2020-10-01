<?php

class Files
{
    public function __construct()
    {
        setlocale(LC_ALL, 'fr_FR.utf8');
    }

    /**
     * Effacement complet d'un répertoire et des fichiers/répertoires contenus.
     *
     * @param $dir : le répertoire à effacer
     *
     * @return bool : 1 si OK, 0 si pas OK
     */
    public function delTree($dir)
    {
        $files = glob($dir.'*', GLOB_MARK);
        $resultat = true;
        foreach ($files as $file) {
            if (substr($file, -1) == '/') {
                if ($resultat == true) {
                    $resultat = $this->delTree($file);
                }
            } else {
                $resultat = unlink($file);
            }
        }

        if (is_dir($dir) && ($resultat == true)) {
            $resultat = rmdir($dir);
        }

        return ($resultat == true) ? 1 : 0;
    }

    /**
     * recherche de l'id d'un fichier dont on fournit le nom et le path.
     *
     * @param $fileName : le nom du fichier
     * @param $path : le path
     * @param $acronyme : l'abréviation de l'utilisateur actif
     *
     * @return is_integer
     */
    public function findFileId($path, $fileName, $acronyme)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id FROM '.PFX.'thotFiles ';
        $sql .= "WHERE acronyme='$acronyme' AND path='$path' AND fileName='$fileName' ";
        $resultat = $connexion->query($sql);
        $id = null;
        if ($resultat) {
            $ligne = $resultat->fetch();
            $id = $ligne['id'];
        }

        return $id;
    }

    /**
     * retrouve le path à partir du fileId d'un partage de répertoire.
     *
     * @param $fileId : identifiant du répertoire dans la BD
     *
     * @return string
     */
    public function getPathByFileId($fileId)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT path ';
        $sql .= 'FROM '.PFX.'thotFiles ';
        $sql .= 'WHERE fileId=:fileId ';
        $requete = $connexion->prepare($sql);
        $path = '';
        if (is_numeric($fileId)) {
            $data = array(':fileId' => $fileId);
            $resultat = $requete->execute($data);
            if ($resultat) {
                $requete->setFetchMode(PDO::FETCH_ASSOC);
                $ligne = $requete->fetch();
                $path = $ligne['path'];
            }
        } else {
            die('bad fileId');
        }

        Application::DeconnexionPDO($connexion);

        return $path;
    }

    /**
     * Enregistrement du partage d'un fichier.
     *
     * @param $post : contenu du formulaire
     *
     * @return int : nombre d'enregistrements de partage
     */
    public function share($post, $acronyme)
    {
        $fileName = $post['fileName'];
        $path = $post['path'];
        $type = $post['type'];
        $groupe = $post['groupe'];
        $commentaire = $post['commentaire'];
        $tous = isset($post['TOUS']) ? $post['TOUS'] : null;
        $membres = isset($post['membres']) ? $post['membres'] : null;

        $id = $this->findFileId($path, $fileName, $acronyme);

        $resultat = null;
        if ($id != 0) {
            $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
            // enregistrer les partages
            $sql = 'INSERT IGNORE INTO '.PFX.'thotShares ';
            $sql .= 'SET id=:id, type=:type, groupe=:groupe, commentaire=:commentaire, destinataire=:destinataire ';
            $requete = $connexion->prepare($sql);
            $resultat = 0;
            $data = array(':id' => $id, ':type' => $type, ':groupe' => $groupe, ':commentaire' => $commentaire);
            if ($tous != null) {
                $data[':destinataire'] = 'all';
                $resultat = $requete->execute($data);
            } else {
                if ($membres != null) {
                    foreach ($membres as $unMembre) {
                        $data[':destinataire'] = $unMembre;
                        $resultat += $requete->execute($data);
                    }
                }
            }
            Application::DeconnexionPDO($connexion);
        }

        return $resultat;
    }

    /**
     * retourne le nombre de partages d'un fichier dont on fournit l'identifiant en BD.
     *
     * @param $id
     *
     * @return int
     */
    public function nbShares($id)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT count(*) AS nb ';
        $sql .= 'FROM '.PFX.'thotShares ';
        $sql .= "WHERE id='$id' ";
        $resultat = $connexion->query($sql);
        $nb = 0;
        if ($resultat) {
            $ligne = $resultat->fetch();
            $nb = $ligne['nb'];
        }
        Application::DeconnexionPDO($connexion);

        return $nb;
    }

    /**
     * retourne la liste des fileIds qui sont accessibles à l'élève dont on fournit le matricule, la classe, le niveau d'étude et la liste des cours.
     *
     * @param $matricule
     *
     * @return array
     */
    public function getSharedFiles($matricule, $classe, $niveau, $listeCoursString)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT files.fileId ';
        $sql .= 'FROM '.PFX.'thotShares AS share ';
        $sql .= 'JOIN '.PFX.'thotFiles AS files ON files.fileId = share.fileId ';
        $sql .= "WHERE destinataire = '$matricule' ";
        $sql .= "OR groupe = '$classe' ";
        $sql .= "OR groupe = 'niveau' AND destinataire = '$niveau' ";
        $sql .= "OR groupe IN ($listeCoursString) ";
        $sql .= "OR groupe = 'ecole' ";

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $fileId = $ligne['fileId'];
                array_push($liste, $fileId);
            }
        };

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne la liste des documents partagés avec l'élève dont on fournit le matricule, etc.
     *
     * @param $matricule
     * @param $classe
     * @param $niveau
     * @param $listeCoursString : ses cours (chaînes séparées par des virgules)
     *
     * @return array : la liste des documents classés par école, niveau, classe, coursGrp
     */
    public function listeElevesShares($matricule, $classe, $niveau, $listeCoursString)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT files.fileId, share.shareId, type, groupe, destinataire, commentaire, path, fileName, ';
        $sql .= 'files.acronyme, nom, prenom, sexe, libelle, dirOrFile, fav.matricule AS fav ';
        $sql .= 'FROM '.PFX.'thotShares AS share ';
        $sql .= 'JOIN '.PFX.'thotFiles AS files ON files.fileId = share.fileId ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = files.acronyme ';
        $sql .= 'LEFT JOIN '.PFX.'cours AS dc ON SUBSTR(share.groupe, 1, LOCATE("-",share.groupe)-1) = dc.cours ';
        $sql .= 'LEFT JOIN '.PFX.'thotSharesFav AS fav ON fav.shareId = share.shareId ';
        $sql .= "WHERE destinataire = :matricule ";
        $sql .= "OR (groupe = :classe AND destinataire = 'all') ";
        $sql .= "OR groupe IN ($listeCoursString) AND (destinataire = 'all') ";
        $sql .= "OR (groupe = :niveau ) ";
        $sql .= "OR groupe = 'ecole' ";
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':classe', $classe, PDO::PARAM_STR, 8);
        $requete->bindParam(':niveau', $niveau, PDO::PARAM_INT);

        $resultat = $requete->execute();
        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                // pallier le problème de la graphie 'classe' ou 'classes'
                $type = $ligne['type'];
                if ($type == 'classe')
                    $type = 'classes';
                $fileId = $ligne['fileId'];
                $liste[$type][$fileId] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);
    
    return $liste;
    }

    /**
     * renvoie la liste des documents fournie en arguments en triant les documents par cours.
     *
     * @param $listeDocuments : array
     *
     * @return array
     */
    public function sortByCours($listeDocs)
    {
        $liste = array();
        foreach ($listeDocs as $fileId => $dataDoc) {
            $nomCours = str_replace('.','-', $dataDoc['libelle']);
            $liste[$nomCours][$fileId] = $dataDoc;
        }
        ksort($liste);

        return $liste;
    }

    /**
     * retourne la liste des 'fileId' des documents auxquels un élève a accès.
     *
     * @param $matricule
     * @param $classe
     * @param $niveau
     * @param $listeCoursString : ses cours (chaînes séparées par des virgules)
     *
     * @return array : le tableau de la liste des fileId's
     */
    public function listeDocsEleve($matricule, $classe, $niveau, $listeCoursString)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT share.fileId, shareId ';
        $sql .= 'FROM '.PFX.'thotShares AS share ';
        $sql .= 'JOIN '.PFX.'thotFiles AS files ON files.fileId = share.fileId ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = files.acronyme ';
        $sql .= 'LEFT JOIN didac_cours AS dc ON SUBSTR(share.groupe, 1, LOCATE("-",share.groupe)-1) = dc.cours ';
        $sql .= 'WHERE destinataire = :matricule ';
        $sql .= 'OR (groupe = :classe AND destinataire = "all") ';
        $sql .= 'OR (groupe = :niveau) ';
        $sql .= 'OR (groupe IN ('.$listeCoursString.') AND destinataire = "all") ';
        $sql .= 'OR groupe = "ecole" ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':classe', $classe, PDO::PARAM_STR, 6);
        $requete->bindParam(':niveau', $niveau, PDO::PARAM_INT);

        $resultat = $requete->execute();
        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $fileId = $ligne['fileId'];
                $liste[$fileId] = array('fileId' => $fileId, 'shareId' => $ligne['shareId']);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retrouver le path et le fileName d'un fichier dont on fournit l'identifiant.
     *
     * @param $id : l'identifiant du fichier dans la BD
     *
     * @return array ('path'=> $path, 'fileName'=>$fileName)
     */
    public function getFileData($fileId)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT path, fileName, acronyme ';
        $sql .= 'FROM '.PFX.'thotFiles ';
        $sql .= "WHERE fileId='$fileId' ";
        $resultat = $connexion->query($sql);
        $data = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $resultat->fetch();
            $data = array(
                    'path' => $ligne['path'],
                    'fileName' => $ligne['fileName'],
                    'acronyme' => $ligne['acronyme'],
                );
        }
        Application::DeconnexionPDO($connexion);

        return $data;
    }

    /**
     * retourne les caractéristiques d'un éventuel espion sur le fichier shareId
     *
     * @param  int $shareId
     *
     * @return array
     */
    public function getSpyInfo4ShareId ($shareId) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT spyId, dtss.shareId, dtss.isDir, dtss.fileId, acronyme ';
        $sql .= 'FROM '.PFX.'thotSharesSpy AS dtss ';
        $sql .= 'JOIN '.PFX.'thotShares AS dts ON dtss.shareId = dts.shareId ';
        $sql .= 'JOIN '.PFX.'thotFiles AS dtf ON dtf.fileId = dts.fileId ';
        $sql .= 'WHERE dtss.shareId = :shareId ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':shareId', $shareId, PDO::PARAM_INT);
        $resultat = $requete->execute();
        $ligne = Null;
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
        }

        Application::DeconnexionPDO($connexion);

        return $ligne;
    }

    /**
     * note le téléchargement d'un fichier référence par $spyId par l'utilisateur $acronyme
     * s'il s'agit d'un fichier dans un dossier, $fileId indique le fichier correspondant
     *
     * @param string $acronyme
     * @param int $spyId
     * @param int $fileId (éventuellement Null)
     *
     * @return void()
     */
    public function setSpiedDownload ($userName, $userType, $spyId, $path=Null, $fileName=Null) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'thotSharesSpyUsers ';
        $sql .= 'SET spyId = :spyId, userName = :userName, date = NOW(), userType = :userType, ';
        $sql .= 'path = :path, fileName = :fileName ';
        $sql .= 'ON DUPLICATE KEY UPDATE date=NOW() ';

        $path = ($path != Null) ? $path : '';
        $fileName = ($fileName != Null) ? $fileName : '';

        $requete = $connexion->prepare($sql);
        $requete->bindParam(':spyId', $spyId, PDO::PARAM_INT);
        $requete->bindParam(':userType', $userType, PDO::PARAM_STR, 6);
        $requete->bindParam(':userName', $userName, PDO::PARAM_STR, 7);
        $requete->bindParam(':path', $path, PDO::PARAM_STR, 255);
        $requete->bindParam(':fileName', $fileName, PDO::PARAM_STR, 255);
        $resultat = $requete->execute();

        Application::DeconnexionPDO($connexion);

        return ;
    }

    /**
     * retourne la liste des documents attendus pour chaque cours de la liste fournie en paramètre
     * pour l'utilisateur dont on donne le matricule.
     *
     * @param $listeCoursSTring : string
     * @param $matricule
     *
     * @return array
     */
    public function listeDocumentsCasiers($listeCoursString, $matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT tt.idTravail, tt.acronyme, nom, prenom, coursGrp, tt.titre, consigne, dateDebut, dateFin, tt.statut, ';
        $sql .= 'remarque, evaluation, libelle, nbheures, idCompetence, dttc.max ';
        $sql .= 'FROM '.PFX.'thotTravaux AS tt ';
        $sql .= 'JOIN '.PFX.'thotTravauxRemis AS tr ON tt.idTravail = tr.idTravail ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON dp.acronyme = tt.acronyme ';
        $sql .= 'JOIN '.PFX."cours AS dc ON (dc.cours = SUBSTR(coursGrp, 1, LOCATE('-', coursGrp)-1)) ";
        $sql .= 'LEFT JOIN '.PFX.'thotTravauxCompetences AS dttc ON dttc.idTravail = tt.idTravail ';
        $sql .= "WHERE coursGrp IN ($listeCoursString) AND matricule='$matricule' ";
        $sql .= 'ORDER BY nbheures, libelle ';

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $idTravail = $ligne['idTravail'];
                $acronyme = $ligne['acronyme'];
                $fileInfos = $this->getMultiFileInfos($matricule, $idTravail, $acronyme);
                $ligne['dateDebut'] = Application::datePHP($ligne['dateDebut']);
                $ligne['dateFin'] = Application::datePHP($ligne['dateFin']);
                $ligne['fileInfos'] = $fileInfos;
                $libelle = sprintf('%s : %dh', $ligne['libelle'], $ligne['nbheures']);
                if (!(isset($liste[$coursGrp]))) {
                    $liste[$coursGrp] = array('libelle' => $libelle, 'travaux' => array());
                }
                $liste[$coursGrp]['travaux'][$idTravail] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne la liste des résultats, par compétence, pour chacun des travaux dans les casiers
     * et pour un élève dont on fournit le matricule
     *
     * @param int : $matricule
     *
     * @return array
     */
    public function listeCotesCasiers($matricule) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, dtte.idTravail, dtte.idCompetence, dtte.cote, dttc.max, libelle ';
        $sql .= 'FROM '.PFX.'thotTravauxEvaluations AS dtte ';
        $sql .= 'JOIN '.PFX.'thotTravauxCompetences AS dttc ON dttc.idTravail = dtte.idTravail AND dttc.idCompetence = dtte.idCompetence ';
        $sql .= 'JOIN '.PFX.'bullCompetences AS dbc ON dbc.id = dttc.idCompetence ';
        $sql .= 'WHERE matricule =:matricule ';
        $requete = $connexion->prepare($sql);

        $liste = array();
        $requete->bindValue(':matricule', $matricule, PDO::PARAM_INT);
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $idTravail = $ligne['idTravail'];
                $idCompetence = $ligne['idCompetence'];
                $liste[$idTravail][$idCompetence] = $ligne;
                if (!(isset($liste[$idTravail]['total'])))
                    $liste[$idTravail]['total'] = array('cote' => '', 'max' => '');
                $liste[$idTravail]['total']['cote'] += $ligne['cote'];
                $liste[$idTravail]['total']['max'] += $ligne['max'];
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne les cotes par compétence pour un travail dont on fournit le 'idTravail' pour l'élève $matricule
     *
     * @param int $idTravail
     * @param int $matricule : matricule de l'élève
     *
     * @return array
     */
    public function getCotesTravail ($idTravail, $matricule){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCompetence, max, formCert, libelle, "-" AS cote ';
        $sql .= 'FROM '.PFX.'thotTravauxCompetences AS ttc ';
        $sql .= 'JOIN '.PFX.'bullCompetences AS bc ON bc.id = ttc.idCompetence ';
        $sql .= 'WHERE idTravail =:idTravail ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idTravail', $idTravail, PDO::PARAM_INT);
        $resultat = $requete->execute();

        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idCompetence = $ligne['idCompetence'];
                $liste[$idCompetence] = $ligne;
            }
        }
        $listeCompetences = implode(',', array_keys($liste));

        $sql = 'SELECT idCompetence, cote ';
        $sql .= 'FROM '.PFX.'thotTravauxEvaluations ';
        $sql .= "WHERE matricule=:matricule AND idTravail =:idTravail AND idCompetence IN ($listeCompetences) ";
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':idTravail', $idTravail, PDO::PARAM_INT);
        $resultat = $requete->execute();

        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idCompetence = $ligne['idCompetence'];
                $liste[$idCompetence]['cote'] = $ligne['cote'];
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne la totalisation des cotes pour les différentes compétences d'un travail
     *
     * @param array $listeCotes : liste des cotes par compétences
     *
     * @return array('cote' => valeur, 'max' => valeur)
     */
    public function totalisation($listeCotes){
        $total = array('cote' => Null, 'max' => Null);
        foreach ($listeCotes as $idCompetence => $evaluation) {
            $cote = (float) Application::sansVirg($evaluation['cote']);
            if ($cote != Null)
                $total['cote'] += $cote;
            $max = (float) Application::sansVirg($evaluation['max']);
            if ($max != Null)
                $total['max'] += $max;
        }

        return $total;
    }

    /**
     * retourne la liste des compétences pour une liste de coursGrp donnée
     *
     * @param $listeCoursGrp
     *
     * @return array
     */
    public function listeCompetences($listeCoursGrp) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $listeCours = array();
        foreach ($listeCoursGrp as $coursGrp) {
            $listeCours[] = substr($coursGrp, 0, strpos($coursGrp, '-'));
            }
        $listeCoursString = "'".implode('\',\'', $listeCours)."'";
        $sql = 'SELECT id, cours, ordre, libelle ';
        $sql .= 'FROM '.PFX.'bullCompetences ';
        $sql .= "WHERE cours IN ($listeCoursString) ";
        $sql .= 'ORDER  BY cours, ordre ';
        $requete = $connexion->prepare($sql);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $cours = $ligne['cours'];
                $id = $ligne['id'];
                $liste[$cours][$id] = $ligne['libelle'];
                }
            }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * recherche les détails relatifs à un fichier déposé par l'élève $matricule pour un $idTravail donné.
     *
     * @param $matricule
     * @param $idTravail
     *
     * @return array
     */
    public function getFileInfos($matricule, $idTravail, $fileName, $acronyme)
    {
        $ds = DIRECTORY_SEPARATOR;
        $dir = INSTALL_ZEUS.$ds.'upload'.$ds.$acronyme.$ds.'#thot'.$ds.$idTravail.$ds.$matricule;
        $infos = array('fileName' => Null, 'size' => '', 'dateRemise' => '');
        $files = @scandir($dir);
        // Le fichier existe-t-il dans ce répertoire?
        if (in_array($fileName, $files)) {
            $infos = array(
                'fileName' => $fileName,
                'size' => $this->unitFilesize(filesize($dir.'/'.$fileName)),
                'dateRemise' => strftime('%x %X', filemtime($dir.'/'.$fileName)),
            );
        }

        return $infos;
    }

    /**
    * recherche les détails relatifs à tous les fichiers déposés
    * par l'élève $matricule pour un $idTravail donné.
    *
    * @param $matricule
    * @param $idTravail
    *
    * @return array
    */
   public function getMultiFileInfos($matricule, $idTravail, $acronyme) {
       $ds = DIRECTORY_SEPARATOR;
       $dir = INSTALL_ZEUS.$ds.'upload'.$ds.$acronyme.$ds.'#thot'.$ds.$idTravail.$ds.$matricule;
       $infos = array('fileName' => null, 'size' => '', 'dateRemise' => '');
       $files = @scandir($dir);
       // ce répertoire est-il défini?
       if ($files != Null) {
           $listeInfos = array();
           $files = array_diff($files, array('..', '.'));
           foreach ($files as $oneFile) {
               $detailOneFile = array(
                   'fileName' => $oneFile,
                   'size' => $this->unitFilesize(filesize($dir.'/'.$oneFile)),
                   'dateRemise' => strftime('%x %X', filemtime($dir.'/'.$oneFile)),
               );
               $listeInfos[] = $detailOneFile;
           }
       }

       return $listeInfos;
   }

    /**
     * convertit les tailles de fichiers en valeurs usuelles avec les unités.
     *
     * @param $bytes : la taille en bytes
     *
     * @return string : la taille en unités usuelles
     */
    public function unitFilesize($size)
    {
        $precision = ($size > 1024) ? 2 : 0;
        $units = array('octet(s)', 'Ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;

        return number_format($size / pow(1024, $power), $precision, '.', ',').' '.$units[$power];
    }

    /**
     * renvoie la liste indexée sur $idTravail des différents travaux existants pour le cours $coursGrp
     *
     * @param string $coursGrp
     * @param array $listeStatuts : liste des statuts souhaités (Ex: pas les hidden)
     *
     * @return array
     */
    public function getTravaux4Cours($coursGrp, $listeStatuts = Null, $matricule) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT tr.idTravail, acronyme, coursGrp, titre, consigne, dateDebut, dateFin, statut, remis ';
        $sql .= 'FROM '.PFX.'thotTravaux AS tr ';
        $sql .= 'LEFT JOIN '.PFX.'thotTravauxRemis AS ttr ON tr.idTravail = ttr.idTravail ';
        $sql .= 'WHERE coursGrp=:coursGrp ';
        if ($listeStatuts != Null) {
            $listeStatutsString = "'".implode("','", $listeStatuts)."'";
            $sql .= "AND statut IN ($listeStatutsString) ";
        }
        $sql .= 'ORDER BY dateDebut, titre ';
        $requete = $connexion->prepare($sql);

        $requete->bindValue(':coursGrp', $coursGrp, PDO::PARAM_STR);
        $resultat = $requete->execute();
        $liste = array();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idTravail = $ligne['idTravail'];
                $acronyme = $ligne['acronyme'];
                $ligne['dateDebut'] = Application::datePHP($ligne['dateDebut']);
                $ligne['dateFin'] = Application::datePHP($ligne['dateFin']);
                $ligne['fileInfo'] = $this->getMultiFileInfos($matricule, $idTravail, $acronyme);
                $liste[$idTravail] = $ligne;
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne les détails d'un travail dont on fournit l'idTravail et le matricule de l'élève.
     *
     * @param int $idTravail
     * @param int $matricule
     *
     * @return array
     */
    public function getDetailsTravail($idTravail, $matricule) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT tt.idTravail, tt.acronyme, nom, prenom, coursGrp, tt.titre, consigne, dateDebut, dateFin, tt.statut, ';
        $sql .= 'tr.remis, cote, max, evaluation, remarque, libelle, nbheures, nbPJ ';
        $sql .= 'FROM '.PFX.'thotTravaux AS tt ';
        $sql .= 'JOIN '.PFX.'thotTravauxRemis AS tr ON tt.idTravail = tr.idTravail ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON dp.acronyme = tt.acronyme ';
        $sql .= 'JOIN '.PFX."cours AS dc ON (dc.cours = SUBSTR(coursGrp, 1, LOCATE('-', coursGrp)-1)) ";
        $sql .= 'WHERE tt.idTravail=:idTravail AND matricule=:matricule ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idTravail', $idTravail, PDO::PARAM_INT);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $details = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $details = $requete->fetch();
            if ($details != Null) {
                $acronyme = $details['acronyme'];
                $details['dateDebut'] = Application::datePHP($details['dateDebut']);
                $details['dateFin'] = Application::datePHP($details['dateFin']);

                $fileInfos = $this->getMultiFileInfos($matricule, $idTravail, $acronyme);

                $details['fileInfos'] = $fileInfos;
            }
        }

        Application::DeconnexionPDO($connexion);

        return $details;
    }

    /**
     *
     * retourne les caractéristiques de l'évaluation du travail idTravail pour l'élève $matricule.
     *
     * @param $idTravail
     * @param $matricule
     *
     * @return array
     */
    public function getEvaluationTravail($idTravail, $matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);

        // recherche des compétences et des maximas pour ce travail
        $sql = 'SELECT idCompetence, max, dbc.libelle, formCert ';
        $sql .= 'FROM '.PFX.'thotTravauxCompetences AS dttc ';
        $sql .= 'JOIN '.PFX.'bullCompetences AS dbc ON dbc.id = dttc.idCompetence ';
        $sql .= 'WHERE idTravail =:idTravail ';
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':idTravail', $idTravail, PDO::PARAM_INT);
        $resultat = $requete->execute();

        $listeCompetences = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idCompetence = $ligne['idCompetence'];
                $listeCompetences[$idCompetence] = $ligne;
                // $listeCompetences[$idCompetence]['max'] = $ligne['max'];
                // $listeCompetences[$idCompetence]['libelle'] = $ligne['libelle'];
            }
        }

        // recherche des cotes obenues pour chaque compétences
        $sql = 'SELECT idCompetence, cote ';
        $sql .= 'FROM '.PFX.'thotTravauxEvaluations ';
        $sql .= 'WHERE matricule =:matricule AND idTravail =:idTravail ';
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':idTravail', $idTravail, PDO::PARAM_INT);

        $resultat = $requete->execute();
        $listeResulats = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $idCompetence = $ligne['idCompetence'];
                $listeResultats['cotes'][$idCompetence] = array(
                    'libelle' => $listeCompetences[$idCompetence]['libelle'],
                    'formCert' => $listeCompetences[$idCompetence]['formCert'],
                    'cote' => $ligne['cote'],
                    'max' => $listeCompetences[$idCompetence]['max']
                );
                }
            }

        // recherche du commentaire professeur pour cette évaluation
        $sql = 'SELECT evaluation, acronyme, consigne, dateDebut, dateFin ';
        $sql .= 'FROM '.PFX.'thotTravauxRemis AS dtr ';
        $sql .= 'JOIN '.PFX.'thotTravaux AS dt ON dt.idTravail = dtr.idTravail ';
        $sql .= 'WHERE dtr.idTravail =:idTravail AND matricule =:matricule ';
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':idTravail', $idTravail, PDO::PARAM_INT);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $resultat = $requete->execute();
        $commentaire = '';
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
            $commentaire = $ligne['evaluation'];
        }
        $listeResultats['commentaire'] = $commentaire;

        return $listeResultats;
    }


    /**
     * vérifie que l'élève $matricule est effectivement affecté à un travail dont on fournit l'idTravail.
     *
     * @param $matricule
     * @param $idTravail
     *
     * @return bool
     */
    public function verifEleve4Travail($matricule, $idTravail)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT coursGrp ';
        $sql .= 'FROM '.PFX.'thotTravaux ';
        $sql .= 'WHERE idTravail=:idTravail ';
        $requete = $connexion->prepare($sql);
        $requete->bindValue(':idTravail', $idTravail, PDO::PARAM_INT);
        $verif = false;
        $resultat = $requete->execute();
        if ($resultat) {
            $ligne = $requete->fetch();
            $coursGrp = $ligne['coursGrp'];
            $sql = 'SELECT matricule ';
            $sql .= 'FROM '.PFX.'elevesCours ';
            $sql .= 'WHERE coursGrp =:coursGrp';
            $requete = $connexion->prepare($sql);
            $requete->bindValue(':coursGrp', $coursGrp, PDO::PARAM_STR);
            $resultat = $requete->execute();
            if ($resultat) {
                $requete->setFetchMode(PDO::FETCH_ASSOC);
                while ($ligne = $requete->fetch()) {
                    $leMatricule = $ligne['matricule'];
                    $liste[] = $leMatricule;
                }
                $verif = in_array($matricule, $liste);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $verif;
    }

    /**
     * enregistre une remarque de l'élève $matricule pour un travail dont on fournit l'$idTravail.
     *
     * @param $idTravail
     * @param $matricule
     * @param $matricule
     *
     * @return int : nombre d'enregistrements réussis (0 ou 1)
     */
    public function saveRemarqueEleve($remarque, $idTravail, $matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'thotTravauxRemis ';
        $sql .= 'SET remarque=:remarque, matricule=:matricule, idTravail=:idTravail ';
        $sql .= 'ON DUPLICATE KEY UPDATE remarque=:remarque ';
        $requete = $connexion->prepare($sql);
        $data = array(':remarque' => $remarque, ':matricule' => $matricule, ':idTravail' => $idTravail);
        $resultat = $requete->execute($data);

        $nb = ($resultat > 0) ? 1 : 0;

        Application::DeconnexionPDO($connexion);

        return $nb;
    }

    /**
     * marque le travail $idTravail comme remis par l'élève $matricule.
     *
     * @param $idTravail : identifiant du travail
     * @param $matricule : identifiant de l'élève
     */
    public function travailRemis($idTravail, $matricule, $remis = true)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'thotTravauxRemis ';
        if ($remis == true) {
            $sql .= 'SET remis = 1, idTravail=:idTravail, matricule=:matricule ';
        } else {
            $sql .= 'SET remis = 0, idTravail=:idTravail, matricule=:matricule ';
        }
        $sql .= 'ON DUPLICATE KEY UPDATE ';
        if ($remis == true) {
            $sql .= 'remis = 1 ';
        } else {
            $sql .= 'remis = 0 ';
        }

        $requete = $connexion->prepare($sql);
        $data = array(':matricule' => $matricule, ':idTravail' => $idTravail);
        $resultat = $requete->execute($data);
        Application::DeconnexionPDO($connexion);

        return;
    }

    /**
     * met en favori ou supprime le statut de favori pour le fichier $shareId
     * de l'élève $matricule
     *
     * @param int $shareId : shareid du fichier
     * @param int $matricle : matricule de l'élève
     *
     * @return bool : true si favori marqué, false si favori retiré
     */
    public function favUnfav($shareId, $matricule){
        // recherche du statut actuel
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, shareId ';
        $sql .= 'FROM '.PFX.'thotSharesFav ';
        $sql .= 'WHERE shareId = :shareId AND matricule = :matricule ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':shareId', $shareId, PDO::PARAM_INT);

        $resultat = $requete->execute();
        if ($resultat){
            $ligne = $requete->fetch();
            $temp = $ligne['shareId'];
        }
        // si la valeur retournée est bien $shareId, l'enregistrement existe
        if ($temp == $shareId){
            // alors on le supprime
            $sql = 'DELETE FROM '.PFX.'thotSharesFav ';
            $sql .= 'WHERE shareId = :shareId AND matricule = :matricule ';
            $requete = $connexion->prepare($sql);
            }
            else {
                // sinon, on l'ajoute
                $sql = 'INSERT INTO '.PFX.'thotSharesFav ';
                $sql .= 'SET shareId = :shareId, matricule = :matricule ';
                $requete = $connexion->prepare($sql);
            }

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':shareId', $shareId, PDO::PARAM_INT);

        $resultat = $requete->execute();

        Application::DeconnexionPDO($connexion);

        return ($temp == $shareId);
    }

    /**
     * retourne les informations détaillées sur un cours/groupe donné ou le cours correspondant
     * s'il s'agit d'un coursGrp, on ne prend que la partie avant le "-".
     *
     * @param string $coursGrp/$cours
     *
     * @return array
     */
    public function detailsCours($coursGrp)
    {
        $pattern = '/([0-9])( {0,1}[A-Z]*):([A-Z]*)[0-9a-z]*/';
        $ligne = array();

        if (preg_match($pattern, $coursGrp, $matches)) {
            $cours = $matches[0];
            $annee = $matches[1];
            $forme = $matches[2];
            $code = $matches[3];

            $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'SELECT cours, nbheures, libelle, statut, c.cadre, section ';
            $sql .= 'FROM '.PFX.'cours AS c ';
            $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = c.cadre) ';
            $sql .= 'WHERE cours = :cours ';
            $requete = $connexion->prepare($sql);

            $requete->bindParam(':cours', $cours, PDO::PARAM_STR, 17);

            $ligne = array();
            $resultat = $requete->execute();
            if ($resultat) {
                $requete->setFetchMode(PDO::FETCH_ASSOC);
                $ligne = $requete->fetch();
                $ligne['forme'] = $forme;
                $ligne['annee'] = $annee;
                $ligne['code'] = $code;
            }
            Application::DeconnexionPDO($connexion);
        }

        return $ligne;
    }

    /**
     * retourne les détails concernant la matière $cours indiquée (ne pas confondre avec coursGrp)
     *
     * @param string $cours
     *
     * @return array
     */
    public function getDetailsMatiere($cours){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT cours, nbheures, libelle, statut, c.cadre, section ';
        $sql .= 'FROM '.PFX.'cours AS c ';
        $sql .= 'JOIN '.PFX.'statutCours AS statut ON (statut.cadre = c.cadre) ';
        $sql .= 'WHERE cours = :cours ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':cours', $cours, PDO::PARAM_STR, 17);

        $details = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $details = $requete->fetch();
        }

        Application::DeconnexionPDO($connexion);

        return $details;
    }

    /**
     * retourne les informations essentielles concernant un élève: nom, prenom, classe
     *
     * @param int $matricule
     *
     * @return array
     */
    public function getMinDetailsEleve($matricule){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, nom, prenom, classe, groupe ';
        $sql .= 'FROM '.PFX.'eleves ';
        $sql .= 'WHERE matricule = :matricule ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $npc = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $npc = $requete->fetch();
        }

        Application::deconnexionPDO($connexion);

        return $npc;
    }

    /**
     * retrouve le véritable destinataire d'une notification dont on fournit
     * le $type et le $destinataire pour la notification donnée
     *
     * @param string $type
     * @param string $destinataire
     * @param array $uneNotification
     *
     * @return string
     */
    public function getTrueDestinataire($type, $destinataire){
        switch ($type) {
            case 'ecole':
                $destinataire = 'Tous les élèves';
                break;
            case 'niveau':
                $niveau = $destinataire;
                $destinataire = sprintf('Élèves de %de année', $niveau);
                break;
            case 'classes':
                $classe = $destinataire;
                $destinataire = sprintf('Élèves de %s', $classe);
                break;
            case 'cours':
                $cours = $destinataire;
                $details = $this->getDetailsMatiere($cours);
                $destinataire = sprintf('[%s] %s %dh', $details['cours'], $details['libelle'], $details['nbheures']);
                break;
            case 'coursGrp':
                $coursGrp = $destinataire;
                $details = $this->detailsCours($coursGrp);
                if ($details != Null)
                    $destinataire = sprintf('[%s] %s %s %dh', $coursGrp, $details['statut'], $details['libelle'], $details['nbheures']);
                    else $destinataire = Null;
                break;
            case 'groupe':
                $groupe = $destinataire;
                $details = $this->getData4groupe($groupe);
                $destinataire = sprintf('[%s] %s', $groupe, $details['intitule']);
                break;
            case 'eleves':
                $matricule = $destinataire;
                $details = $this->getMinDetailsEleve($matricule);
                $destinataire = sprintf('%s %s %s', $details['nom'], $details['prenom'], $details['classe']);
                break;
        }

        return $destinataire;
    }

    /**
     * retourne la liste des favoris pour l'élève $matricule
     *
     * @param int $matricule
     *
     * @return array
     */
    public function getListeFavs($matricule){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT fav.shareId, fav.matricule, shares.fileId, dirOrFile, ';
        $sql .= 'files.acronyme, sexe, nom, prenom, commentaire, type, groupe, destinataire, ';
        $sql .= 'path, fileName, dirOrFile ';
        $sql .= 'FROM '.PFX.'thotSharesFav AS fav ';
        $sql .= 'JOIN '.PFX.'thotShares AS shares ON shares.shareId = fav.shareId ';
        $sql .= 'JOIN '.PFX.'thotFiles AS files ON files.fileId = shares.fileId ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS profs ON profs.acronyme = files.acronyme ';
        $sql .= 'WHERE fav.matricule = :matricule ';
        $sql .= 'ORDER BY type, groupe, destinataire ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $resultat = $requete->execute();

        $liste = array();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                // Application::afficher($ligne);
                $shareId = $ligne['shareId'];
                if ($ligne['nom'] != Null) {
                    $formule = ($ligne['sexe'] == 'F') ? 'Mme' : 'M.';
                    $ligne['nomProf'] = sprintf('%s %s. %s', $formule, $ligne['prenom'][0], $ligne['nom']);
                }
                else $ligne['nomProf'] = $ligne['acronyme'];
                $liste[$shareId] = $ligne;
                }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

}
