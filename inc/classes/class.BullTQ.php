<?php

/*
 * class bullTQ
 */
class bullTQ
{
    /*
     * __construct
     * @param
     */
    public function __construct()
    {
    }

    /**
     * renvoie la liste des cours d'un type ('general' <> 'option').
     *
     * @param
     *
     * @return array : liste des cours généraux séparés par des virgules
     */
    private function listeCours($type)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT coursGrp FROM '.PFX.'bullTQtypologie ';
        $sql .= "WHERE type = '$type' ";
        $resultat = $connexion->query($sql);
        $listeCours = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $listeCours[$coursGrp] = $coursGrp;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCours;
    }

    /**
     * retourne une version imprimable sous fpdf d'une chaîne utf8.
     *
     * @param string $argument
     *
     * @return string
     */
    public function utf8($argument)
    {
        return utf8_decode($argument);
    }

    /**
     * retourne une valeur booleenne selon que le cours est "général" ou fait partie de l'option.
     *
     * @param $coursGrp
     *
     * @return bool
     */
    public function estGeneral($coursGrp)
    {
        return in_array($this->coursSansGrp($coursGrp), $this->listeCours('general'));
    }

    /**
     * renvoie la liste des cotes globales pour un coursGrp donné et une période donnée.
     *
     * @param $coursGrp : le coursGroupe dont on veut les cotes
     * @param $bulletin : la période que l'on veut
     *
     * @return array : la liste de cotes organisées par élève (basé sur le matricule)
     */
    public function listeCotesGlobales($listeCoursGrp, $bulletin)
    {
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT coursGrp, matricule, Tj, Ex, periode, global ';
        $sql .= 'FROM '.PFX.'bullTQCotesGlobales ';
        $sql .= "WHERE coursGrp IN ($listeCoursGrpString) AND bulletin = '$bulletin' ";

        $resultat = $connexion->query($sql);
        $listeCotes = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $matricule = $ligne['matricule'];
                $listeCotes[$bulletin][$coursGrp][$matricule] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCotes;
    }

    /**
     * renvoie la liste des cotes des cours généraux pour une liste de cours donnée, une liste d'élèves donnée et un bulletin donné.
     *
     * @param $listeCoursGrp : la liste des coursGroupes dont on veut les cotes
     * @param $listeEleves : la liste des matricules des élèves concernés
     * @param $bulletin : la période que l'on veut ou toutes les périodes
     *
     * @return array : la liste de cotes organisées par élève (basé sur le matricule)
     */
    public function toutesCotesCoursGeneraux($listeCoursGrp, $listeEleves, $bulletin = null)
    {
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT coursGrp, matricule, Tj, Ex, idComp ';
        $sql .= 'FROM '.PFX.'bullTQCotesCompetences ';
        $sql .= 'JOIN '.PFX."cours ON (SUBSTR(coursGrp,1,LOCATE('-',coursGrp)-1) = ".PFX.'cours.cours) ';
        $sql .= "WHERE coursGrp IN ($listeCoursGrpString) AND matricule IN ($listeElevesString) ";
        $sql .= "AND SUBSTRING(coursGrp,1,LOCATE('-',coursGrp)-1) IN (SELECT coursGrp FROM ".PFX."bullTQtypologie WHERE type='general') ";
        if ($bulletin != null) {
            $sql .= "AND bulletin = '$bulletin' ";
        }

        $resultat = $connexion->query($sql);
        $listeCotes = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $matricule = $ligne['matricule'];
                $idComp = $ligne['idComp'];
                $listeCotes[$bulletin][$matricule][$coursGrp][$idComp] = array('Tj' => $ligne['Tj'], 'Ex' => $ligne['Ex']);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCotes;
    }

    /**
     * retourne toutes les informations (libelle, heures, statut,...) d'un coursGrp passé en argument.
     *
     * @param $coursGrp
     *
     * @return $array
     */
    public function intituleCours($coursGrp)
    {
        $cours = $this->coursDeCoursGrp($coursGrp);
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT cours, coursGrp, nomCours, libelle, nbheures, statut, SUBSTR(coursGrp,1,LOCATE(':',coursGrp)-1) as annee ";
        $sql .= 'FROM '.PFX.'profsCours ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX."cours.cours = '$cours') ";
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= "WHERE coursGrp = '$coursGrp'";
        $resultat = $connexion->query($sql);
        $intitule = null;
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $intitule = $resultat->fetchall();
        }
        Application::DeconnexionPDO($connexion);

        return $intitule[0];
    }

    /**
     * retourne le cours correspondant au coursGrp passé en argument.
     *
     * @param $coursGrp
     *
     * @return string
     */
    public function coursDeCoursGrp($coursGrp)
    {
        return substr($coursGrp, 0, strpos($coursGrp, '-'));
    }

    /**
     * retourne la liste de tous les commentaires pour un cours donné pour toutes les périodes pour tous les élèves d'une liste donnée.
     *
     * @param $listeEleves
     * @param $coursGrp
     * @result array
     */
    public function listeCommentaires($listeEleves, $listeCoursGrp)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }

        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT coursGrp, matricule, bulletin, commentaire ';
        $sql .= 'FROM '.PFX.'bullTQCommentProfs ';
        $sql .= "WHERE matricule IN ($listeElevesString) AND coursGrp IN ($listeCoursGrpString) ";
        $sql .= 'ORDER BY matricule, coursGrp';
        $resultat = $connexion->query($sql);
        $listeCommentaires = array();
        while ($ligne = $resultat->fetch()) {
            $coursGrp = $ligne['coursGrp'];
            $matricule = $ligne['matricule'];
            $bulletin = $ligne['bulletin'];
            $listeCommentaires[$bulletin][$coursGrp][$matricule] = stripslashes($ligne['commentaire']);
        }
        Application::DeconnexionPDO($connexion);

        return $listeCommentaires;
    }

    /*
     * organisation rationnelle des cotes: une ligne par compétence extraite du formulaire de rédaction du bulletin par cours
     * @param $post
     * @return array
     */
    public function organiserData($post)
    {
        $coursGrp = isset($post['coursGrp']) ? $post['coursGrp'] : null;
        $bulletin = isset($post['bulletin']) ? $post['bulletin'] : null;

        $listeCotesParCompetences = array();
        $listeCotesPeriode = array();

        $listeCommentaires = array();

        $genresPermis = array('coteTJ', 'coteEX', 'TJ', 'EX', 'PERIODE', 'GLOBAL');
        foreach ($post as $uneInfo => $value) {
            $value = htmlspecialchars($value);
            $data = explode('-', $uneInfo);
            switch ($data[0]) {
                case 'coteTJ' :
                    $matricule = $data[1];
                    $idComp = substr($data[2], strpos($data[2], '_') + 1);
                    $listeCotesParCompetences[$matricule][$idComp]['coteTJ'] = trim($value);
                    break;
                case 'coteEX' :
                    $matricule = $data[1];
                    $idComp = substr($data[2], strpos($data[2], '_') + 1);
                    $listeCotesParCompetences[$matricule][$idComp]['coteEX'] = trim($value);
                    break;
                case 'TJ' :
                    $matricule = $data[1];
                    $listeCotesPeriode[$matricule]['TJ'] = trim($value);
                    break;
                case 'EX' :
                    $matricule = $data[1];
                    $listeCotesPeriode[$matricule]['EX'] = trim($value);
                    break;
                case 'PERIODE' :
                    $matricule = $data[1];
                    $listeCotesPeriode[$matricule]['PERIODE'] = trim($value);
                    break;
                case 'GLOBAL' :
                    $matricule = $data[1];
                    $listeCotesPeriode[$matricule]['GLOBAL'] = trim($value);
                    break;
                case 'COMMENTAIRE' :
                    $matricule = $data[1];
                    $listeCommentaires[$matricule] = $value;
                    break;

                default :
                    // on passe, ce champ n'est pas significatif
                    break;
            }
        }

        return array('cotes' => $listeCotesParCompetences,
                     'periode' => $listeCotesPeriode,
                     'commentaires' => $listeCommentaires,
                    );
    }

    /**
     * Enregistrement effectif des données réorganisées par la fonction organiserData.
     *
     * @param $data
     *
     * @return int : nombre d'enregistrements réalisés
     */
    public function enregistrer($data, $coursGrp, $bulletin)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $cotesCompetences = $data['cotes'];
        $cotesPeriode = $data['periode'];
        $commentaires = $data['commentaires'];
        $nbResultats = 0;

        // traitement des cotes par compétences
        $sql = 'INSERT INTO '.PFX.'bullTQCotesCompetences SET ';
        $sql .= 'matricule=:matricule, coursGrp=:coursGrp, bulletin=:bulletin, idComp=:idComp, TJ=:Tj, Ex=:Ex ';
        $sql .= 'ON DUPLICATE KEY UPDATE TJ=:Tj, Ex=:Ex ';
        $requete = $connexion->prepare($sql);
        foreach ($cotesCompetences as $matricule => $lesCompetences) {
            foreach ($lesCompetences as $idComp => $lesCotes) {
                $Tj = $lesCotes['coteTJ'];
                $Ex = $lesCotes['coteEX'];
                $data = array(':matricule' => $matricule, ':coursGrp' => $coursGrp, ':bulletin' => $bulletin, ':idComp' => $idComp, ':Tj' => $Tj, ':Ex' => $Ex);
                $nbResultats += $requete->execute($data);
            }
        }

        // traitement des cotes globales de période
        $sql = 'INSERT INTO '.PFX.'bullTQCotesGlobales SET ';
        $sql .= 'matricule=:matricule, coursGrp=:coursGrp, bulletin=:bulletin, TJ=:Tj, Ex=:Ex, periode=:periode, global=:global ';
        $sql .= 'ON DUPLICATE KEY UPDATE TJ=:Tj, Ex=:Ex, periode=:periode, global=:global ';
        $requete = $connexion->prepare($sql);

        foreach ($cotesPeriode as $matricule => $data) {
            $Tj = $data['TJ'];
            $Ex = $data['EX'];
            $periode = $data['PERIODE'];
            $global = $data['GLOBAL'];
            $data = array(':matricule' => $matricule, ':coursGrp' => $coursGrp, ':bulletin' => $bulletin, ':Tj' => $Tj, ':Ex' => $Ex, ':periode' => $periode, ':global' => $global);
            $nbResultats += $requete->execute($data);
        }

        // traitement des commentaires
        $sql = 'INSERT INTO '.PFX.'bullTQCommentProfs SET ';
        $sql .= 'matricule=:matricule, coursGrp=:coursGrp, bulletin=:bulletin, commentaire=:commentaire ';
        $sql .= 'ON DUPLICATE KEY UPDATE commentaire=:commentaire ';
        $requete = $connexion->prepare($sql);
        foreach ($commentaires as $matricule => $commentaire) {
            $data = array(':matricule' => $matricule, ':coursGrp' => $coursGrp, ':bulletin' => $bulletin, ':commentaire' => $commentaire);
            $nbResultats += $requete->execute($data);
        }

        Application::DeconnexionPDO($connexion);

        return $nbResultats;
    }

    /**
     * enregistrement de la remarque du titulaire pour l'élève dont le matricule est indiqué, pour le bulletin donné.
     *
     * @param $commentaire
     * @param $matricule
     * @param $bulletin
     *
     * @return integer: nombre d'insertions dans la BD (en principe, 1)
     */
    public function enregistrerRemarque($remarque, $matricule, $bulletin)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $remarque = addslashes(htmlspecialchars($remarque));
        $sql = 'INSERT INTO '.PFX."bullTQTitus SET remarque = '$remarque',";
        $sql .= "matricule ='$matricule', bulletin='$bulletin' ";
        $sql .= "ON DUPLICATE KEY UPDATE remarque = '$remarque'";
        $resultat = $connexion->exec($sql);
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * Établissement de la liste des coursGrp des élèves passés en argument.
     *
     * @param string|array $listeEleves : liste des élèves concernés
     *
     * @return array : liste des cours suivis par la liste des élèves à la période $bulletin
     */
    public function listeCoursGrpEleves($listeEleves)
    {
        if (is_array($listeEleves)) {
            $listeMatricules = implode(',', array_keys($listeEleves));
        } else {
            $listeMatricules = $listeEleves;
        }

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT '.PFX.'elevesCours.coursGrp, cours, libelle, nbheures, type, ';
        $sql .= PFX.'statutCours.statut, section, rang, matricule, nom, prenom, '.PFX.'profsCours.acronyme ';
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX."cours.cours = SUBSTR(coursGrp, 1,LOCATE('-',coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= 'JOIN '.PFX.'bullTQtypologie ON ('.PFX.'bullTQtypologie.coursGrp = cours) ';
        // LEFT JOIN pour les cas où un élève aurait été affecté à un cours qui n'existe plus dans la table des profs
        $sql .= 'LEFT JOIN '.PFX.'profsCours ON ('.PFX.'profsCours.coursGrp = '.PFX.'elevesCours.coursGrp) ';
        $sql .= 'LEFT JOIN '.PFX.'profs ON ('.PFX.'profs.acronyme = '.PFX.'profsCours.acronyme) ';
        $sql .= "WHERE matricule IN ($listeMatricules) ";
        $sql .= 'ORDER BY type DESC, nbheures DESC, rang, libelle';

        $resultat = $connexion->query($sql);
        $listeCours = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $coursGrp = $ligne['coursGrp'];
                preg_match('/\:([A-Z:0-9]+)\-/', $coursGrp, $matches);
                $ligne['shortCours'] = $matches[1];
                $listeCours[$matricule][$coursGrp] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCours;
    }

    /**
     * liste des commentaires pour tous les cours d'une liste d'élèves passée en argument
     * pour un bulletin éventuellement donné
     * si pas de bulletin précisé, renvoie les commentaires de tous les bulletins
     * Typiquement pour la génération d'un bulletin d'élève.
     *
     * @param $listeEleves
     * @param $bulletin
     *
     * @return array
     */
    public function listeCommentairesTousCours($listeEleves, $bulletin = null)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        if (is_array($bulletin)) {
            $listeBulletinsString = implode(',', $bulletin);
        } else {
            $listeBulletinsString = $bulletin;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, coursGrp, commentaire, bulletin ';
        $sql .= 'FROM '.PFX.'bullTQCommentProfs ';
        $sql .= "WHERE matricule IN ($listeElevesString) ";
        if ($bulletin != null) {
            $sql .= "AND bulletin IN ($listeBulletinsString) ";
        }
        $resultat = $connexion->query($sql);
        $listeCommentaires = array();
        while ($ligne = $resultat->fetch()) {
            $commentaire = $ligne['commentaire'];
            $matricule = $ligne['matricule'];
            $coursGrp = $ligne['coursGrp'];
            $bulletin = $ligne['bulletin'];
            $listeCommentaires[$matricule][$coursGrp][$bulletin] = stripslashes($commentaire);
        }
        Application::DeconnexionPDO($connexion);

        return $listeCommentaires;
    }

    /**
     * retourne les remarques du titulaire pour une liste d'élèves donnée et pour un bulletin donné
     * si bulletin non précisé, donne toutes les remarques pour tous les bulletins.
     *
     * @param array|string $listeEleves
     * @param int          $bulletin
     *
     * @return array
     */
    public function remarqueTitu($listeEleves, $bulletin = null)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, bulletin, remarque ';
        $sql .= 'FROM '.PFX.'bullTQTitus ';
        $sql .= "WHERE matricule IN ($listeElevesString) ";
        if ($bulletin != null) {
            $sql .= "AND bulletin='$bulletin' ";
        }
        $resultat = $connexion->query($sql);
        $listeRemarques = array();
        while ($ligne = $resultat->fetch()) {
            $matricule = $ligne['matricule'];
            $bulletin = $ligne['bulletin'];
            $listeRemarques[$bulletin][$matricule] = $ligne['remarque'];
        }
        Application::DeconnexionPDO($connexion);

        return $listeRemarques;
    }

    /**
     * retourne les mentions globales à faire figurer au bulletin.
     *
     * @param $bulletin : numéro du bulletin
     * @param $listeEleves: matricules des élèves
     *
     * @return array
     */
    public function mentionsBulletin($listeEleves, $bulletin)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, type, mention ';
        $sql .= 'FROM '.PFX.'bullTQMentions ';
        $sql .= "WHERE matricule IN ($listeEleves) AND periode='$bulletin' AND type IN ('global_final', 'option_final') ";
        $resultat = $connexion->query($sql);
        $mentions = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $type = $ligne['type'];
                $mention = $ligne['mention'];
                $matricule = $ligne['matricule'];
                $mentions[$matricule][$type] = $mention;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $mentions;
    }

    /**
     * renvoie la liste de toutes les cotes globales pour tous les cours suivis par un élève dont on fournit le matricule.
     *
     * @param $listeCours
     * @param $matricule
     *
     * @return array
     */
    public function globalAnneeEnCours($listeCoursGrp, $matricule)
    {
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT coursGrp, bulletin, global ';
        $sql .= 'FROM '.PFX.'bullTQCotesGlobales ';
        $sql .= "WHERE coursGrp IN ($listeCoursGrpString) AND matricule = '$matricule' ";

        $resultat = $connexion->query($sql);
        $listeCotesGlobales = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $bulletin = $ligne['bulletin'];
                $global = $ligne['global'];
                $listeCotesGlobales[$bulletin][$coursGrp] = $global;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCotesGlobales;
    }

    /**
     * retourne la liste des titulariats d'un prof dont on fournit l'acronyme, pour le bulletin TQ.
     *
     * @param $acronyme
     *
     * @return array
     */
    public function tituTQ($acronyme)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT classe ';
        $sql .= 'FROM '.PFX.'titus ';
        $sql .= "WHERE acronyme = '$acronyme' AND section = 'TQ' ";
        $sql .= 'ORDER BY classe ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $classe = $ligne['classe'];
                $liste[$classe] = $classe;
            }
        }
        Application::DeconnexionPDO($connexion);
        // suppression des entrées vides...
        $liste = array_filter($liste);

        return $liste;
    }

    /**
     * retire le groupe d'un cours dont on passe le coursGroupe.
     *
     * @param $coursGrp
     *
     * @return string : cours
     */
    private function coursSansGrp($coursGrp)
    {
        return substr($coursGrp, 0, strpos($coursGrp, '-', 0));
    }

    /**
     * retourne la liste ordonnées de toutes les compétences pour tous les cours passé en argument.
     *
     * @param $listeCours
     *
     * @return array
     */
    public function listeCompetencesListeCours($listeCours)
    {
        if (is_array($listeCours)) {
            $listeCoursString = "'".implode("','", array_keys($listeCours))."'";
        } else {
            $listeCoursString = "'".$listeCours."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, cours, ordre, libelle ';
        $sql .= 'FROM '.PFX.'bullTQCompetences ';
        $sql .= "WHERE cours IN ($listeCoursString) ";
        $sql .= 'ORDER BY ordre ';
        $resultat = $connexion->query($sql);
        $listeCompetences = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['cours'];
                $idComp = $ligne['id'];
                $listeCompetences[$cours][$idComp] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCompetences;
    }

    /**
     * renvoie la liste des compétences pour tous les coursGrp passés en argument après avoir cherché les "cours" correspondants.
     *
     * @listeCoursGrp : array
     *
     * @return array
     */
    public function listeCompetencesListeCoursGrp($listeCoursGrp)
    {
        $listeCours = array();
        foreach ($listeCoursGrp as $coursGrp => $data) {
            $cours = $this->coursSansGrp($coursGrp);
            $listeCours[$cours] = $data;
        }

        return $this->listeCompetencesListeCours($listeCours);
    }

    /**
     * liste des compétences appliquées à un cours dont on fournit le "coursGrp"; il suffit donc de supprimer le groupe.
     *
     * @param $coursGrp
     *
     * @return array : liste de compétences ordonnées
     */
    public function listeCompetences($coursGrp)
    {
        $cours = $this->coursDeCoursGrp($coursGrp);
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, cours, ordre, libelle ';
        $sql .= 'FROM '.PFX.'bullTQCompetences ';
        $sql .= "WHERE cours='$cours' ";
        $sql .= 'ORDER BY ordre, libelle';
        $resultat = $connexion->query($sql);
        $listeCompetences = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
        }
        while ($ligne = $resultat->fetch()) {
            $idComp = $ligne['id'];
            $cours = $ligne['cours'];
            $listeCompetences[$cours][$idComp] = $ligne;
        }
        Application::DeconnexionPDO($connexion);

        return $listeCompetences;
    }

    /**
     * liste structurée des profs liés à une liste de coursGrp (liste indexée par coursGrp).
     *
     * @param array $listeCoursGrp
     * @param $type : default string // si string, renvoie un array simple $coursGrp -> nom du prof
     *
     * @return array
     */
    public function listeProfsListeCoursGrp($listeCoursGrp, $type = 'string')
    {
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT coursGrp, nom, prenom, '.PFX.'profsCours.acronyme ';
        $sql .= 'FROM '.PFX.'profsCours ';
        $sql .= 'JOIN '.PFX.'profs ON ('.PFX.'profsCours.acronyme = '.PFX.'profs.acronyme) ';
        $sql .= "WHERE coursGrp IN ($listeCoursGrpString) ";
        $sql .= 'ORDER BY nom';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $acronyme = $ligne['acronyme'];
                if ($type == 'string') {
                    if (isset($liste[$coursGrp])) {
                        $liste[$coursGrp] .= ', '.$ligne['prenom'].' '.$ligne['nom'];
                    } else {
                        $liste[$coursGrp] = $ligne['prenom'].' '.$ligne['nom'];
                    }
                } else {
                    $liste[$coursGrp][$acronyme] = $ligne;
                }
                // on supprime le cours dont le prof a été trouvé
                unset($listeCoursGrp[$coursGrp]);
            }
        }
        Application::DeconnexionPDO($connexion);
        // on rajoute tous les cours dont les affectations de profs sont inconnues
        if ($listeCoursGrp != null) {
            foreach ($listeCoursGrp as $coursGrp => $wtf) {
                $liste[$coursGrp] = PROFNONDESIGNE;
            }
        }

        return $liste;
    }

    /**
     * renvoie la liste des périodes (de délibés) et leur nom.
     *
     * @param $delibe : boolean souhaite-t-on uniquement les périodes de délibé (true) ou toutes
     *
     * @return array
     */
    public function listePeriodes($delibes = false)
    {
        $nomsPeriodes = array_combine(range(1, NBPERIODES), explode(',', NOMSPERIODES));
        if ($delibes) {
            $periodesDelibes = explode(',', str_replace(' ', '', PERIODESDELIBES));
            foreach ($nomsPeriodes as $no => $unePeriode) {
                if (!(in_array($no, $periodesDelibes))) {
                    unset($nomsPeriodes[$no]);
                }
            }
        }

        return $nomsPeriodes;
    }

    /**
     * renvoie la liste des cotes de situations pour la période demandée.
     *
     * @param $classe : string la classe concernée
     * @prame $periode : période
     *
     * @return array
     */
    public function listeSituationsClasse($classe, $periode)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT cg.matricule, coursGrp, SUBSTR(coursGrp,1,LOCATE('-',coursGrp)-1) AS cours, bulletin, global, groupe ";
        $sql .= 'FROM '.PFX.'bullTQCotesGlobales as cg ';
        $sql .= 'JOIN '.PFX.'eleves AS e ON (e.matricule = cg.matricule) ';
        $sql .= "WHERE bulletin ='$periode' AND groupe = '$classe' ";
        $sql .= "ORDER BY REPLACE(REPLACE(REPLACE(nom,' ',''),'-',''),'\'',''), prenom ";
        $resultat = $connexion->query($sql);
        $listeSituations = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $bulletin = $ligne['bulletin'];
                $matricule = $ligne['matricule'];
                $cours = $ligne['cours'];
                $coursGrp = $ligne['coursGrp'];
                $listeSituations[$matricule][$cours][$bulletin] = array('coursGrp' => $ligne['coursGrp'], 'cote' => $ligne['global']);
            }
        }
        Application::deconnexionPDO($connexion);

        return $listeSituations;
    }

    /**
     * retourne la liste de tous les cours qui se donnent dans une classe
     * chaque ligne contient
     *  - le cours
     *  - le coursGrp
     *  - les références complètes du/des profs pour ce cours
     *  - le nombre d'heures de cours et le libellé du cours.
     *
     * @param $classe
     *
     * @return array
     */
    public function listeCoursClasse($classe)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT '.PFX.'elevesCours.coursGrp, ';
        $sql .= 'SUBSTR('.PFX."elevesCours.coursGrp, 1,LOCATE('-',".PFX.'elevesCours.coursGrp)-1) AS cours, '.PFX.'statutCours.statut, ';
        $sql .= PFX.'profsCours.acronyme, '.PFX.'profs.nom, '.PFX.'profs.prenom, nbheures, libelle ';
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX.'cours.cours = SUBSTR('.PFX."elevesCours.coursGrp, 1,LOCATE('-',coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'elevesCours.matricule) ';
        $sql .= 'JOIN '.PFX.'profsCours ON ('.PFX.'profsCours.coursGrp = '.PFX.'elevesCours.coursGrp) ';
        $sql .= 'JOIN '.PFX.'profs ON ('.PFX.'profs.acronyme = '.PFX.'profsCours.acronyme) ';
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre ) ';
        $sql .= "WHERE classe LIKE '$classe' ";
        $sql .= 'ORDER BY statut DESC, nbheures DESC, libelle';

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['cours'];
                $coursGrp = $ligne['coursGrp'];
                $acronyme = $ligne['acronyme'];
                $liste[$cours]['dataCours'] = array('nbheures' => $ligne['nbheures'], 'libelle' => $ligne['libelle'], 'statut' => $ligne['statut']);
                $liste[$cours]['profs'][$coursGrp][$acronyme] = $ligne['nom'].' '.$ligne['prenom'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * création d'une image d'un texte sur base des paramètres.
     *
     * @param $largeur
     * @param $hauteur
     * @param $texte
     * @param $taillePolice
     * @param $font
     * @param $nomImage
     */
    public function creeTexteVerticalPng($largeur, $hauteur, $texte, $taillePolice, $font, $nomImage)
    {
        // crée un texte disposé verticalement pour les entêtes des feuilles de cotes
        $im = imagecreate($largeur, $hauteur);

        // couleur de fond de l'image
        $gris = imagecolorallocate($im, 0xdd, 0xdd, 0xdd);
        $white = imagecolorallocate($im, 0xff, 0xff, 0xff);
        $black = imagecolorallocate($im, 0x00, 0x00, 0x00);

        // angle d'écriture = 90
        imagettftext($im, $taillePolice, 90, $taillePolice + 3, $hauteur - 4, $white, $font, $texte);
        imagettftext($im, $taillePolice, 90, $taillePolice + 4, $hauteur - 5, $black, $font, $texte);

        // Sauvegarde l'image
        imagepng($im, "$nomImage");
        imagedestroy($im);
    }

    /**
     * création des étiquettes verticales "images" pour les cours.
     *
     * @param $hauteur
     *
     * @return array
     */
    public function imagesPngBranches($hauteur)
    {
        $listeBranches = Ecole::listeCoursListeSections('TQ');
        $largeur = 18;
        $fontSize = 10;
        $font = '../inc/font/LiberationMono-Bold.ttf';
        $liste = array();
        foreach ($listeBranches as $uneBranche => $data) {
            $nomImage = $uneBranche;
            $texte = $data['libelle'];
            $this->creeTexteVerticalPng($largeur, $hauteur, $texte, $fontSize, $font, "imagesCours/$nomImage.png");
            $liste[] = array('nomImage' => $nomImage, 'texte' => $texte);
        }

        return $liste;
    }

    /**
     * Enregistre les informations passées dans le formulaire posté.
     *
     * @param post
     *
     * @return bool
     */
    public function enregistrerDelibe($post)
    {
        $nb = 0;
        if (($post['matricule'] != '') && ($post['classe'] != '')) {
            $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
            $matricule = $post['matricule'];
            foreach ($post as $key => $value) {
                $data = explode('-', $key);
                $key = $data[0];
                $boutSql = '';
                switch ($key) {
                case 'synthese':
                    $type = $data[1];
                    $element = explode('_', $data[2]);
                    $periode = $element[1];
                    $typesAutorises = array(
                        'stage_depart', 'stage_final', 'option_depart', 'option_final',
                        'global_depart', 'global_final', );
                    if (in_array($type, $typesAutorises)) {
                        $sql = 'INSERT INTO '.PFX.'bullTQMentions ';
                        $sql .= "SET matricule ='$matricule', type='$type', mention='$value', periode='$periode' ";
                        $sql .= "ON DUPLICATE KEY UPDATE type='$type', mention='$value',periode='$periode'";
                        $resultat = $connexion->exec($sql);
                        ++$nb;
                    }
                    break;
                case 'qualif':
                    $element = explode('_', $data[1]);
                    $epreuve = $element[1];
                    $typesAutorises = array('E1', 'E2', 'E3', 'E4', 'JURY', 'TOTAL');
                    if (in_array($epreuve, $typesAutorises)) {
                        $sql = 'INSERT INTO '.PFX.'bullTQQualif ';
                        $sql .= "SET matricule='$matricule', epreuve='$epreuve', mention='$value' ";
                        $sql .= "ON DUPLICATE KEY UPDATE epreuve='$epreuve',mention='$value' ";
                        $resultat = $connexion->exec($sql);
                        ++$nb;
                    }
                    break;
                default:
                    // wtf
                    break;
                }
            }
            Application::DeconnexionPDO($connexion);
        }

        return $nb;
    }

    /**
     * retourne l'ensemble des cotes d'un élève dont on fournit le matricule.
     *
     * @param $matricule
     *
     * @return array
     */
    public function cotesEleve($matricule, $listePeriodes)
    {
        $listePeriodesString = implode(',', array_keys($listePeriodes));
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT cg.coursGrp, cg.matricule, cg.bulletin, global, ';
        $sql .= "SUBSTR(cg.coursGrp, 1, LOCATE('-',cg.coursGrp)-1) AS cours, libelle, nbheures, sc.statut, ";
        $sql .= 'p.acronyme, p.nom, p.prenom ';
        $sql .= 'FROM '.PFX.'bullTQCotesGlobales AS cg ';
        $sql .= 'JOIN '.PFX.'profsCours AS pc ON (pc.coursGrp = cg.coursGrp) ';
        $sql .= 'JOIN '.PFX.'profs AS p ON (p.acronyme = pc.acronyme) ';
        $sql .= 'JOIN '.PFX."cours AS c ON (c.cours = SUBSTR(cg.coursGrp, 1, LOCATE('-',cg.coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'statutCours AS sc ON (sc.cadre = c.cadre) ';
        $sql .= "WHERE cg.matricule = '$matricule' AND bulletin IN ($listePeriodesString) ";
        $sql .= 'ORDER BY cg.bulletin, statut, nbheures, libelle, rang ';
        $resultat = $connexion->query($sql);
        $cotesEleve = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $bulletin = $ligne['bulletin'];
                $coursGrp = $ligne['coursGrp'];
                $cotesEleve[$bulletin][$coursGrp] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $cotesEleve;
    }

    /**
     * retourne un tableau statistique des différentes cotes trouvées
     * le type peut être
     * 	'' = tout venant
     *  'stage' = cotes de stage uniquement
     *  'OG' = cotes de l'option groupée uniquement.
     *
     * @param $cotesParPeriode
     * @param $type
     * @param $periode
     *
     * @return array
     */
    public function tableauStatistique($cotesParPeriode, $type)
    {
        $cotesPossibles = array('E', 'TB', 'B', 'AB', 'S', 'I', 'TI');
        $lesEchecs = array('I', 'TI');
        $init = array('nbCotes' => 0, 'nbheures' => 0);

        $tableauStat = array();
        foreach ($cotesParPeriode as $periode => $cotesDePeriode) {
            // initialisation des données statistiques
            $tableauStat[$periode] = array(
                'E' => $init,    'TB' => $init,    'B' => $init,    'AB' => $init,
                'S' => $init,    'I' => $init,    'TI' => $init,
                );
            $tableauStat[$periode]['nbEchecs'] = 0;
            $tableauStat[$periode]['nbheuresEchecs'] = 0;

            // on passe en revue toutes les cotes de la période
            foreach ($cotesDePeriode as $coursGrp => $unCours) {
                $laCote = trim($unCours['global'], '*[]+- ');
                $nbheures = $unCours['nbheures'];
                $leStatut = $unCours['statut'];

                if (in_array($laCote, $cotesPossibles)) {
                    if (($leStatut == $type) || ($type == '')) {
                        ++$tableauStat[$periode][$laCote]['nbCotes'];
                        $tableauStat[$periode][$laCote]['nbheures'] += $nbheures;
                        if (in_array($laCote, $lesEchecs)) {
                            ++$tableauStat[$periode]['nbEchecs'];
                            $tableauStat[$periode]['nbheuresEchecs'] += $nbheures;
                        }
                    }
                }
            }
        }

        return $tableauStat;
    }

    /**
     * coupe un tableau en plusieurs sous-tableau en fonction des types passés dans un autre tabeleau, en deuxième argument.
     *
     * @param $coursPeriode
     * @param $tableTypes
     *
     * @return array
     */
    public function cotesParTypes($cotesParPeriode, $tableTypes)
    {
        $coursParTypes = array();
        foreach ($cotesParPeriode as $periode => $listeCours) {
            foreach ($listeCours as $coursGrp => $unCours) {
                $statut = $unCours['statut'];
                $key = array_search($statut, $tableTypes);
                // === pour éviter de confondre "1" avec "true"
                if ($key === false) {
                    $statut = 'cours';
                }
                $coursParTypes[$statut][$coursGrp]['libelle'] = $unCours['libelle'];
                $coursParTypes[$statut][$coursGrp]['coursGrp'] = $unCours['coursGrp'];
                $coursParTypes[$statut][$coursGrp]['global'][$periode] = $unCours['global'];
                $coursParTypes[$statut][$coursGrp]['acronyme'] = $unCours['acronyme'];
                $coursParTypes[$statut][$coursGrp]['nomProf'] = $unCours['prenom'].' '.$unCours['nom'];
                $coursParTypes[$statut][$coursGrp]['nbheures'] = $unCours['nbheures'];
                $coursParTypes[$statut][$coursGrp]['statut'] = $unCours['statut'];
            }
        }

        return $coursParTypes;
    }

    /**
     * retourne la liste des cours d'un élève organisés par type comme dans la fonction "cotesParTypes".
     *
     * @param $listeCours : l'ensemble des cours de l'élève
     * @param $tableTypes : les clefs de classement
     *
     * @return array
     */
    public function listeCoursParType($listeCours, $tableTypes)
    {
        $coursParTypes = array();
        foreach ($listeCours as $cours => $dataCours) {
            $statut = $dataCours['statut'];
            $key = array_search($statut, $tableTypes);
            // on donne le statut 'cours' au tout-venant, non prévu dans la table des types
            if ($key === false) {
                $statut = 'cours';
            }
            $coursGrp = $dataCours['coursGrp'];
            $coursParTypes[$statut][$coursGrp] = array(
                    'statut' => $dataCours['statut'],
                    'libelle' => $dataCours['libelle'],
                    'nbheures' => $dataCours['nbheures'],
                    'profs' => $dataCours['profs'],
                );
        }

        return $coursParTypes;
    }

    /**
     * lecture des mentions de qualification (E1 -5e, E2 -5e, E3 -6e, E4 -6e, JURY -6e, TOTAL -6e) pour un élève donné
     * cotes provenant de la 5e et de la 6e
     * la cote de 5e doit être conservée à l'issue de l'année scolaire pour être prise en compte en 6e.
     *
     * @param $matricule
     *
     * @return array
     */
    public function mentionsQualif($matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT epreuve, mention ';
        $sql .= 'FROM '.PFX.'bullTQQualif ';
        $sql .= "WHERE matricule='$matricule' ";
        $resultat = $connexion->query($sql);
        $mentionsQualif = array();
        while ($ligne = $resultat->fetch()) {
            $mention = $ligne['mention'];
            $epreuve = $ligne['epreuve'];
            $mentionsQualif[$epreuve] = $mention;
        }
        Application::DeconnexionPDO($connexion);

        return $mentionsQualif;
    }

    /**
     * renvoie les mentions manuelles provenant de la table des mentions pour l'élève dont on indique le matricule
     * les mentions manuelles sont fixées sur la base des résultats de l'élève dans les différents cours
     * mention manuelle de départ (médiane des différents cours) & mention manuelle finale (après tenue en compte des échecs)
     * Il y a trois mentions manuelles (x2 pour départ/final) pour : jury, OG et global.
     *
     * @param $matricule
     *
     * @return array
     */
    public function mentionsManuelles($matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT periode, type, mention ';
        $sql .= 'FROM '.PFX.'bullTQMentions ';
        $sql .= "WHERE matricule = '$matricule'";
        $resultat = $connexion->query($sql);
        $mentionsManuelles = array();
        while ($ligne = $resultat->fetch()) {
            $periode = $ligne['periode'];
            $type = $ligne['type'];
            $mention = $ligne['mention'];
            $mentionsManuelles[$periode][$type] = $mention;
        }
        Application::DeconnexionPDO($connexion);
        // la dernière période contient la cote de jury
        /* $mentionsManuelles['jury'] = isset($mentionsManuelles[5]['jury'])?$mentionsManuelles[5]['jury']:Null;;
        $mentionsManuelles['option']= self::coteOption($matricule, $periode);
        $mentionsManuelles['stage']= self::coteStage($matricule, $periode); */
        return $mentionsManuelles;
    }


    /**
     * Initialisation de la table passée en paramètre.
     *
     * @param $table string
     *
     * @return nombre d'actions réussies sur la BD
     */
    public function init($table)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'bullTQ'.$table.' WHERE 1 > 0 ';
        $resultat = $connexion->exec($sql);
        Application::deconnexionPDO($connexion);

        return 1;
    }

    /**
     * enregistrement des compétences provenant du formulaire "adminCompetences.tpl".
     *
     * @param $post
     *
     * @return int : nombre de modifications dans la BD
     */
    public function enregistrerCompetences($post)
    {
        $cours = $post['cours'];
        $resultat = 0;
        // mise en ordre des données reçues
        $dataExiste = array();
        $dataNew = array();
        foreach ($post as $field => $value) {
            $champ = explode('_', $field);
            // mises à jour et suppression des compétences
            if ($champ[0] == 'libelle') {
                $idComp = $champ[1];
                $dataExiste[$idComp]['libelle'] = addslashes($value);
            }
            if ($champ[0] == 'ordre') {
                $idComp = $champ[1];
                $dataExiste[$idComp]['ordre'] = addslashes($value);
            }

            // nouvelles compétences
            if ($field == 'newComp') {
                $dataNew = $value;
            }
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        foreach ($dataExiste as $idComp => $data) {
            if ($data['libelle'] == '') {
                $sql = 'DELETE FROM '.PFX.'bullTQCompetences ';
                $sql .= "WHERE id='$idComp'";
                $resultat += $connexion->exec($sql);
            } else {
                $ordre = $data['ordre'];
                $libelle = $data['libelle'];
                $sql = 'UPDATE '.PFX.'bullTQCompetences ';
                $sql .= "SET ordre='$ordre', libelle='$libelle' ";
                $sql .= "WHERE id = '$idComp'";
                $resultat += $connexion->exec($sql);
            }
        }
        foreach ($dataNew as $libelle) {
            $libelle = addslashes($libelle);
            if ($libelle != '') {
                $sql = 'INSERT INTO '.PFX.'bullTQCompetences ';
                $sql .= "SET libelle='$libelle', cours='$cours'";
                $resultat += $connexion->exec($sql);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * retourne la liste des cours pour la liste de niveaux donnée.
     *
     * @param $listeNiveaux
     *
     * @return array
     */
    public function listeCoursNiveaux($listeNiveaux)
    {
        if (is_array($listeNiveaux)) {
            $listeNiveauxString = implode(',', $listeNiveaux);
        } else {
            $listeNiveauxString = $listeNiveaux;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT cours, libelle, nbheures, section, statut ';
        $sql .= 'FROM '.PFX.'cours ';
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= "WHERE SUBSTR(cours, 1,1) IN ($listeNiveauxString) AND section='TQ' ";
        $sql .= 'ORDER BY libelle';
        $listeCours = array();
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['cours'];
                $listeCours[$cours] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCours;
    }

    /**
     * retourne les types définis (option ou formation générale) pour les cours dont on fournit la liste.
     *
     * @param array $listeCours
     *
     * @return array liste des types associés aux cours
     */
    public function listeTypes($listeCours)
    {
        if (is_array($listeCours)) {
            $listeCoursString = "'".implode("','", array_keys($listeCours))."'";
        } else {
            $listeCoursString = "'".$liseCours."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT libelle, coursGrp, type ';
        $sql .= 'FROM '.PFX.'bullTQtypologie AS tp ';
        $sql .= 'JOIN '.PFX.'cours AS dc ON dc.cours = tp.coursGrp ';
        $sql .= "WHERE coursGrp IN ($listeCoursString) ";
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['coursGrp'];
                $listeCours[$cours]['type'] = $ligne['type'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCours;
    }

    /**
     * enregistre les associations cours / type (option ou général) provenant du formulaire ad-hoc.
     *
     * @param $post array : provenant du formulaire
     *
     * @return int : nombre de modifications dans la BD
     */
    public function enregistrerTypes($post)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $nbResultats = 0;
        foreach ($post as $champ => $value) {
            if (substr($champ, 0, 6) == 'field_') {
                $champ = explode('_', $champ);
                $champ = str_replace('~', ' ', $champ[1]);
                $sql = 'INSERT INTO '.PFX.'bullTQtypologie ';
                $sql .= "SET coursGrp='$champ', type='$value' ";
                $sql .= "ON DUPLICATE KEY UPDATE type='$value' ";
                $nbResultats += $connexion->exec($sql);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $nbResultats;
    }

    /**
     * enregistrement de la décision du Conseil de Classe provenant de la feuille de délibé individuelle.
     *
     * @param $post
     *
     * @return int : normalement, 1
     */
    public function enregistrerDecision($post)
    {
        $matricule = $post['matricule'];
        $decision = $post['decision'];
        if ($decision == 'Restriction') {
            $restriction = $post['restriction'];
        } else {
            $restriction = '';
        }
        $mail = isset($post['mail']) && ($post['mail'] == true) ? 1 : 0;
        $notification = isset($post['notification']) && ($post['notification'] == true) ? 1 : 0;
        $mailEleve = $post['mailEleve'];
        $adresseMail = ($post['adresseMail'] != $mailEleve) ? $post['adresseMail'] : '';
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'bullDecisions ';
        $sql .= "SET matricule='$matricule', decision='$decision', restriction='$restriction', mail='$mail', notification='$notification', ";
        $sql .= "adresseMail='$adresseMail' ";
        $sql .= 'ON DUPLICATE KEY UPDATE ';
        $sql .= "decision='$decision', restriction='$restriction', mail='$mail', notification='$notification', ";
        $sql .= "adresseMail='$adresseMail' ";

        $resultat = $connexion->exec($sql);
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * renvoie les décisions de délibération pour la liste d'élèves indiqués.
     *
     * @param $matricule / liste de matricules
     *
     * @return array
     */
    public function listeDecisions($listeEleves)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT dp.matricule, user, mailDomain, decision, restriction, notification, mail, adresseMail, DATE_FORMAT(quand, '%d/%m %H:%i') AS quand, ";
        $sql .= "CONCAT(nom,' ',prenom) AS nom, groupe ";
        $sql .= 'FROM '.PFX.'passwd AS dp ';
        $sql .= 'LEFT JOIN '.PFX.'bullDecisions AS dbd ON dbd.matricule = dp.matricule ';
        $sql .= 'JOIN '.PFX.'eleves AS de ON de.matricule = dp.matricule ';
        $sql .= "WHERE dp.matricule IN ($listeElevesString) ";
        $resultat = $connexion->query($sql);
        $listeDecisions = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                if (($ligne['decision'] == '') || ($ligne['quand'] != '')) {
                    $ligne['okEnvoi'] = false;
                } else {
                    $ligne['okEnvoi'] = true;
                }
                if ($ligne['adresseMail'] == '') {
                    $ligne['adresseMail'] = $ligne['user'].'@'.$ligne['mailDomain'];
                }
                $listeDecisions[$matricule] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeDecisions;
    }

    /**
     * établir la liste de synthèse des décisions prises pour les élèves dont la liste est fournie.
     *
     * @param $listeEleves
     * @result array
     */
    public function listeSynthDecisions($listeEleves)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT bd.matricule, decision, restriction, mail, notification, adresseMail, quand, ';
        $sql .= 'nom, prenom, user, mailDomain ';
        $sql .= 'FROM '.PFX.'bullDecisions AS bd ';
        $sql .= 'JOIN '.PFX.'eleves AS de ON de.matricule = bd.matricule ';
        $sql .= 'JOIN '.PFX.'passwd AS dpw ON dpw.matricule = bd.matricule ';
        $sql .= "WHERE bd.matricule IN ($listeElevesString) ";
        $sql .= "ORDER BY REPLACE(REPLACE(REPLACE(nom,' ',''),'-',''),'\'',''), prenom ";

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $photo = Ecole::photo($matricule);
                $ligne['photo'] = $photo;
                $liste[$matricule] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * dater (et donc clôturer) les décisions de C.Cl pour les élèves dont la liste est fournie en paramètre.
     *
     * @param array $listeEleves
     *
     * @return $nb : integer
     */
    public function daterDecisions($listeEleves)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $nb = 0;
        foreach ($listeEleves as $matricule) {
            $sql = 'UPDATE '.PFX.'bullDecisions ';
            $sql .= 'SET quand=NOW() ';
            $sql .= "WHERE matricule = '$matricule' ";
            $resultat = $connexion->exec($sql);
            ++$nb;
        }
        Application::DeconnexionPDO($connexion);

        return $nb;
    }

    /**
     * recherche des mentions globales finales obtenues par la liste des élèves passée en argument.
     *
     * @param $listeEleves
     *
     * @return array: liste des mentions par élève et par période de délibé
     */
    public function mentionsGlobalesFinales($listeEleves)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, periode, mention ';
        $sql .= 'FROM '.PFX.'bullTQMentions ';
        $sql .= "WHERE matricule IN ($listeElevesString) AND type='global_final' ";
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $periode = $ligne['periode'];
                $liste[$matricule][$periode] = $ligne['mention'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne la liste des profs qui donnent un cours dans la section indiquée
     * la section doit être mentionnée dans les libellés techniques des cours Ex: 6 TQ:MATH2-03 où TQ désigne la section.
     *
     * @param $section
     *
     * @return array
     */
    public function listeProfsSection($section)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT pc.acronyme, nom, prenom ';
        $sql .= 'FROM '.PFX.'profsCours AS pc ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON dp.acronyme = pc.acronyme ';
        $sql .= "WHERE SUBSTR(coursGrp,1,LOCATE(':',coursGrp)-1) LIKE '%".$section."%' ";
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $acronyme = $ligne['acronyme'];
                if (!(isset($ligne[$acronyme]))) {
                    $liste[$acronyme] = $ligne;
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne la liste des élèves suivis par le prof dont on fournit l'acronyme.
     *
     * @param $acronyme
     *
     * @return array
     */
    public function listeStagesSuivis($acronyme)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT st.matricule, nom, prenom ';
        $sql .= 'FROM '.PFX.'bullTQstages AS st ';
        $sql .= 'JOIN '.PFX.'eleves AS de ON de.matricule = st.matricule ';
        $sql .= "WHERE acronyme='$acronyme' ";
        $sql .= 'ORDER BY groupe, nom, prenom ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $liste[$matricule] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * enregistre l'association prof / élève suivi pour la liste d'élèves et le prof correspondant.
     *
     * @param $listeEleves : array of $matricules
     * @param $acronyme du prof
     *
     * @return int : le nombre d'enregistrements réussis
     */
    public function saveStagesSuivis($listeEleves, $acronyme)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT IGNORE INTO '.PFX.'bullTQstages ';
        $sql .= 'SET acronyme=:acronyme, matricule=:matricule ';
        $requete = $connexion->prepare($sql);
        $nb = 0;
        foreach ($listeEleves as $wtf => $matricule) {
            $data = array(':acronyme' => $acronyme, ':matricule' => $matricule);
            $nb += $requete->execute($data);
        }

        Application::DeconnexionPDO($connexion);

        return $nb;
    }

    /**
     * supprime l'élève dont on passe la matricule de la liste des stages du prof $acronyme.
     *
     * @param $matricule
     * @param $acronyme
     *
     * @return int : le nombre de suppressions effectuées (0 ou 1)
     */
    public function delStagesSuivis($matricule, $acronyme)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'bullTQstages ';
        $sql .= 'WHERE matricule=:matricule AND acronyme=:acronyme ';
        $requete = $connexion->prepare($sql);
        $data = array(':matricule' => $matricule, ':acronyme' => $acronyme);
        $resultat = $requete->execute($data);

        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * retourne toutes les évaluations de stages pour les élèves dont on fournit la liste.
     *
     * @param $listeEleves
     *
     * @return array
     */
    public function evalStagesEleve($matricule, $listeChamps)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, epreuve, mention ';
        $sql .= 'FROM '.PFX.'bullTQQualif ';
        $sql .= "WHERE matricule='$matricule' ";
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $epreuve = $ligne['epreuve'];
                $liste[$matricule][$epreuve] = $ligne['mention'];
            }
        }
        Application::DeconnexionPDO($connexion);

        // on comble les trous: il n'y a pas encore de données dans la BD
        if (!(isset($liste[$matricule]))) {
            $liste[$matricule] = array();
        }
        foreach ($listeChamps as $wtf => $epreuve) {
            $sigle = $epreuve['sigle'];
            if (!(isset($liste[$matricule][$sigle]))) {
                $liste[$matricule][$sigle] = '';
            }
        }

//        foreach ($listeChamps as $epreuve => $legende) {
//            if (!(isset($liste[$matricule][$epreuve]))) {
//                $liste[$matricule][$epreuve] = '';
//            }
//        }

        return $liste;
    }

    /**
     * enregistre une évaluation pour laquelle on fournit le matricule de l'élève, le nom de l'épreuve et la mention obtenue.
     *
     * @param $matricule
     * @param $epreuve : string
     * @param $mention : string
     *
     * @return int : le nombre d'enregistrements réussis (0 ou 1)
     */
    public function saveEvalQualif($matricule, $epreuve, $mention)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'bullTQQualif ';
        $sql .= 'SET matricule=:matricule, epreuve=:epreuve, mention=:mention ';
        $sql .= 'ON DUPLICATE KEY UPDATE mention=:mention ';
        $requete = $connexion->prepare($sql);
        $data = array(':matricule' => $matricule, ':epreuve' => $epreuve, ':mention' => $mention);
        $resultat = $requete->execute($data);

        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * renvoie la iste des épreuves de qualification pour toutes les années d'étude.
     *
     * @param void()
     *
     * @return array
     */
    public function listeEpreuvesQualif()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT annee, sigle, legende ';
        $sql .= 'FROM '.PFX.'bullTQdetailsStages ';

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $liste = $resultat->fetchAll();
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }
}
