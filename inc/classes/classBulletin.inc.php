
<?php

class Bulletin
{
    /**
     * retourne un array contenant une liste des périodes de l'année scolaire.
     *
     * @param $nbBulletins
     *
     * @return array
     */
    public function listePeriodes($nbBulletins, $debut = 1)
    {
        return range($debut, $nbBulletins);
    }

    /**
     * fonction booleenne: détermine si une valeur est numérique et non Null.
     *
     * @param $data : valeur à tester
     *
     * @return bool
     */
    public function isNumericNotNull($data)
    {
        return ($data != null) && (is_numeric($data)) && ($data != '');
    }

    /**
     * retourne les pondérations pour le cours (groupe) donné et le bulletin donné
     * si le numéro du bulletin n'est pas précisé, retourne toutes les pondérations pour le cours (groupe)
     * si la pondération contient un matricule, c'est qu'il y a un cas particulier
     * pour l'élève dont le matricule est renvoyé; sinon, c'est 'all'.
     *
     * @param $coursGrp
     * @param $bulletin
     */
    public function getPonderations($listeCoursGrp, $bulletin = null)
    {
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT coursGrp, periode, matricule, form, cert ';
        $sql .= 'FROM '.PFX.'bullPonderations ';
        $sql .= "WHERE coursGrp IN ($listeCoursGrpString) ";
        if ($bulletin != null) {
            $sql .= "AND periode = '$bulletin' ";
        }
        $sql .= 'ORDER BY matricule, periode ASC ';

        $resultat = $connexion->query($sql);
        $listePonderations = array();
        while ($ligne = $resultat->fetch()) {
            $matricule = $ligne['matricule'];
            $periode = $ligne['periode'];
            $coursGrp = $ligne['coursGrp'];
            $listePonderations[$coursGrp][$matricule][$periode] = array('form' => $ligne['form'], 'cert' => $ligne['cert']);
        }
        Application::DeconnexionPDO($connexion);

        return $listePonderations;
    }

    /**
     * retourne les sommes des pondérations [all] et par matricule pour un cours donné
     * fonction artificielle pour déterminer s'il faut ouvrir ou non les cases de poids de bulletin dans le carnet de cotes
     * si les sommes des pondérations pour une périoe ([all] et par matricule) sont 0, alors c'est que personne n'a de pondération pour la période
     * et il ne faut pas ouvrir la possibilité de mettre un poids pour la compétence correspondante.
     */
    public function sommesPonderations($coursGrp)
    {
        $listePonderations = current(self::getPonderations($coursGrp));
        $liste = array();
        foreach ($listePonderations as $key => $lesPonderations) {
            foreach ($lesPonderations as $periode => $ponderation) {
                if (!(isset($liste[$periode]))) {
                    $liste[$periode] = array('form' => 0, 'cert' => 0);
                }
                $liste[$periode]['form'] += $ponderation['form'];
                $liste[$periode]['cert'] += $ponderation['cert'];
            }
        }

        return $liste;
    }

    /**
     * recherche les pondérations applicable aux cours de la liste fournie
     * pour le bulletin numéro $bulletin.
     *
     * @param $listeCours
     * @param $bulletin
     *
     * @return array
     */
    public function getPonderationsBulletin($listeCours, $bulletin)
    {
        if (is_array($listeCours)) {
            $listeCoursString = "'".implode('\',\'', array_keys($listeCours))."'";
        } else {
            $listeCoursString = $listeCours;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT form, cert, matricule, coursGrp ';
        $sql .= 'FROM '.PFX.'bullPonderations ';
        $sql .= "WHERE coursGrp IN ($listeCoursString) AND periode = '$bulletin'";

        $resultat = $connexion->query($sql);
        $listePonderations = array();
        while ($ligne = $resultat->fetch()) {
            $coursGrp = $ligne['coursGrp'];
            $matricule = $ligne['matricule'];
            $listePonderations[$coursGrp][$matricule] = array('form' => $ligne['form'], 'cert' => $ligne['cert']);
        }
        Application::DeconnexionPDO($connexion);

        return $listePonderations;
    }

    /**
     * retourne une liste des élèves d'un groupe classe, ordonnée sur les matricules ,
     * reprenant tous les cours de chacun de ces élèves.
     *
     * @param $classe
     *
     * @return array
     */
    public function listeElevesCoursDeGroupe($classe)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $listeElevesCours = array();
        $sql = 'SELECT '.PFX.'elevesCours.matricule, coursGrp ';
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'elevesCours.matricule) ';
        $sql .= "WHERE groupe = '$classe' ";
        $resultat = $connexion->query($sql);
        $listeElevesCours = array();
        while ($ligne = $resultat->fetch()) {
            $matricule = $ligne['matricule'];
            if (!(isset($listeElevesCours[$matricule]))) {
                $listeElevesCours[$matricule] = array();
            }
            $listeElevesCours[$matricule][] = $ligne['coursGrp'];
        }
        Application::DeconnexionPDO($connexion);

        return $listeElevesCours;
    }

    /**
     * retourne la liste des classes représentées parmi les élèves d'un coursGrp.
     *
     * @param $coursGrp
     *
     * @return array
     */
    public function classesDansCours($coursGrp)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT groupe ';
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'elevesCours.matricule) ';
        $sql .= 'WHERE '.PFX."elevesCours.coursGrp = '$coursGrp'";
        $resultat = $connexion->query($sql);
        $listeClasses = array();
        while ($ligne = $resultat->fetch()) {
            $listeClasses[] = $ligne['groupe'];
        }
        Application::DeconnexionPDO($connexion);

        return $listeClasses;
    }

    /**
     * retourne toutes les informations (libelle, heures, statut,...)
     * d'un coursGrp passé en argument.
     *
     * @param $cours
     *
     * @return array
     */
    public function intituleCours($coursGrp)
    {
        $cours = self::coursDeCoursGrp($coursGrp);
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
     * liste des compétences appliquées à un cours dont on fournit le "coursGrp"; il suffit donc de supprimer le groupe.
     *
     * @param $coursGrp
     *
     * @return array
     */
    public function listeCompetences($coursGrp)
    {
        $cours = self::coursDeCoursGrp($coursGrp);
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, cours, ordre, libelle ';
        $sql .= 'FROM '.PFX.'bullCompetences ';
        $sql .= "WHERE cours='$cours' ";
        $sql .= 'ORDER BY ordre, libelle';
        $resultat = $connexion->query($sql);
        $listeCompetences = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $idComp = $ligne['id'];
                $cours = $ligne['cours'];
                $listeCompetences[$cours][$idComp] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCompetences;
    }

    /**
     * retire le groupe d'un cours dont on passe le coursGroupe.
     *
     * @param sting $coursGrp
     *
     * @return string
     */
    private function coursSansGrp($coursGrp)
    {
        return substr($coursGrp, 0, strpos($coursGrp, '-', 0));
    }

    /**
     * retourne la liste ordonnées de toutes les compétences pour tous les cours passés en argument.
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
        $sql .= 'FROM '.PFX.'bullCompetences ';
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

    public function listeCompetencesListeCoursGrp($listeCoursGrp)
    {
        $listeCours = array();
        foreach ($listeCoursGrp as $coursGrp => $data) {
            $cours = self::coursSansGrp($coursGrp);
            $listeCours[$cours] = $data;
        }

        return self::listeCompetencesListeCours($listeCours);
    }

    /**
     * retourne la liste des compétences liées à un cours.
     *
     * @param $cours
     *
     * @return array
     */
    public function listeCompetencesCours($cours)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, ordre, libelle ';
        $sql .= 'FROM '.PFX.'bullCompetences ';
        $sql .= "WHERE cours = '$cours' ";
        $sql .= 'ORDER BY ordre, libelle';
        $resultat = $connexion->query($sql);
        $listeCompetences = array();
        while ($ligne = $resultat->fetch()) {
            $id = $ligne['id'];
            $ordre = $ligne['ordre'];
            $libelle = $ligne['libelle'];
            $listeCompetences[$id] = $libelle;
        }
        Application::DeconnexionPDO($connexion);

        return $listeCompetences;
    }

     /**
      * retourne la liste de toutes les compétences pour tous les cours passés en argument.
      *
      * @param $listeCoursGrp
      *
      * @return array : liste ordonnée sur le idComp
      */
     public function listeCompetencesTousEleves($listeCoursGrpEleves)
     {
         $listeCours = array();
         foreach ($listeCoursGrpEleves as $matricule => $coursGrpEleve) {
             foreach ($coursGrpEleve as $coursGrp) {
                 $cours = $this->coursSansGrp($coursGrp['coursGrp']);
                 // évitons les doublons
                 $listeCours[$cours] = $cours;
             }
         }
         $listeCoursString = "'".implode("','", array_keys($listeCours))."'";
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         $sql = 'SELECT id, cours, libelle ';
         $sql .= 'FROM '.PFX.'bullCompetences ';
         $sql .= "WHERE cours IN ($listeCoursString)";
         $resultat = $connexion->query($sql);
         $listeCompetences = array();
         while ($ligne = $resultat->fetch()) {
             $idComp = $ligne['id'];
             $listeCompetences[$idComp] = $ligne['libelle'];
         }
         Application::DeconnexionPDO($connexion);

         return $listeCompetences;
     }

    /**
     * retourne la liste des fiches d'élèves bloquées pour la page d'encodage du bulletin
     * la liste d'élèves arrive sous la forme d'un tableau.
     *
     * @param $listeEleves
     * @param $coursGrp
     * @param $periode
     */
    public function listeLocksBulletin($listeEleves, $listeCoursGrp, $periode)
    {
        $listeElevesString = implode(',', array_keys($listeEleves));
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, coursGrp, locked FROM '.PFX.'bullLockElevesCours ';
        $sql .= "WHERE matricule IN ($listeElevesString) AND coursGrp IN ($listeCoursGrpString) AND periode='$periode' ";

        $resultat = $connexion->query($sql);
        $listeLocks = array();
        while ($ligne = $resultat->fetch()) {
            $matricule = $ligne['matricule'];
            $coursGrp = $ligne['coursGrp'];
            $listeLocks[$matricule][$coursGrp] = $ligne['locked'];
        }
        Application::DeconnexionPDO($connexion);

        return $listeLocks;
    }

    /**
     * retourne une liste des élèves et de leurs cours à un niveau donné.
     *
     * @param $niveau
     */
    public function listeElevesCoursGrpNiveau($niveau)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT '.PFX."elevesCours.matricule, coursGrp, classe, CONCAT(nom,' ',prenom) AS nomPrenom ";
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'elevesCours.matricule) ';
        $sql .= 'WHERE SUBSTR('.PFX."eleves.groupe,1,1) = '$niveau' ";
        $sql .= 'ORDER BY classe';
        $resultat = $connexion->query($sql);
        $listeElevesCoursGrp = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $coursGrp = $ligne['coursGrp'];
                $classe = $ligne['classe'];
                $nomPrenom = $ligne['nomPrenom'];
                $listeElevesCoursGrp[$classe][$matricule][$coursGrp] = $nomPrenom;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeElevesCoursGrp;
    }

    /**
     * retourne la liste de tous les cours suivis par chaque élève.
     *
     * @param void()
     *
     * @return array()
     */
    public function listeElevesCoursGrp($niveau = null)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT dc.matricule, coursGrp, classe, CONCAT(nom,' ',prenom) AS nomPrenom ";
        $sql .= 'FROM '.PFX.'elevesCours AS dc ';
        $sql .= 'JOIN '.PFX.'eleves AS de ON (de.matricule = dc.matricule) ';
        if ($niveau != null) {
            $sql .= "WHERE SUBSTR(classe,1,1) = '$niveau' ";
        }
        $sql .= 'ORDER BY classe ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $liste = $resultat->fetchAll();
        }
        Application::DeconnexionPDO($connexion);
        $listeElevesCours = array();
        while ($liste != null) {
            $data = array_pop($liste);
            $matricule = $data['matricule'];
            $coursGrp = $data['coursGrp'];
            $listeElevesCours[$matricule][$coursGrp] = array('classe' => $data['classe'], 'nomPrenom' => $data['nomPrenom']);
        }

        return $listeElevesCours;
    }

    /**
     * retourne la liste des profs pour la liste des coursGrp donnée.
     *
     * @param $listeCoursGrp : liste des coursGrp
     *
     * @return array : liste des profs (acronyme, nom, prénom, matière)
     */
    public function listeProfsDeListeCoursGrp($listeCoursGrp)
    {
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT pc.acronyme, sexe, nom, prenom, coursGrp, libelle, nbheures ';
        $sql .= 'FROM '.PFX.'profsCours AS pc ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON dp.acronyme = pc.acronyme ';
        $sql .= 'JOIN '.PFX."cours AS dc ON dc.cours = SUBSTR(coursGrp,1,LOCATE('-',coursGrp)-1) ";
        $sql .= "WHERE coursGrp IN ($listeCoursGrpString) ";
        $sql .= 'ORDER BY nbheures DESC, libelle, nom ';
        $resultat = $connexion->query($sql);
        $liste = array();
        $i = 0;
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $sexe = $ligne['sexe'];
                $ligne['prenom'] = mb_substr($ligne['prenom'], 0, 1, 'UTF-8').'. ';
                $ligne['adresse'] = ($sexe == 'M') ? 'M. ' : 'Mme';
                $liste[$i] = $ligne;
                $i++;
            }
        }
        Application::DeconnexionPDO($connexion);
        return $liste;
    }

    /**
     * retourne la liste des verrous ouverts/fermés selon la valeur du paramètre $etatVerrou
     * pour la période donnée
     * pour un niveau donné
     * résultat par classe puis par cours.
     *
     * @param $periode
     * @param $niveau
     * @param $etatVerrou
     *
     * @return array : liste des verrous pour la période
     */
    public function listeLocksPeriode($periode, $niveau, $etatVerrou)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT '.PFX."eleves.matricule, coursGrp, classe, CONCAT(nom,' ',prenom) AS nomPrenom ";
        $sql .= 'FROM '.PFX.'bullLockElevesCours ';
        $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'bullLockElevesCours.matricule) ';
        $sql .= "WHERE periode = '$periode' AND locked = '$etatVerrou' AND SUBSTR(classe,1,1) = $niveau ";
        $sql .= 'ORDER BY classe, coursGrp, nomPrenom';

        $resultat = $connexion->query($sql);
        $listeLocks = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $classe = $ligne['classe'];
                $niveau = substr($classe, 0, 1);
                $coursGrp = $ligne['coursGrp'];
                $nomPrenom = $ligne['nomPrenom'];
                $matricule = $ligne['matricule'];
                $listeLocks[$niveau][$classe][$coursGrp][$matricule] = $nomPrenom;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeLocks;
    }

    /**
     * Vidage de la table des verrous.
     *
     * @param
     *
     * @return bool : true si l'opération s'est bien passée
     */
    private function resetLocks()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        // suppression de tous les verrous existants par vidage de la table
        $sql = 'TRUNCATE TABLE '.PFX.'bullLockElevesCours ';
        $resultat = $connexion->exec($sql);
        Application::DeconnexionPDO($connexion);
        if ($resultat) {
            return true;
        }
    }

    /**
     * Réinitialisation de tous les verrous à l'état "ouvert"
     * à faire en début d'année scolaire.
     *
     * @param
     *
     * @return int : nombre d'enregistrements modifiés dans la BD
     */
    public function renewAllLocks()
    {
        // $this->resetLocks();
        $listePeriodes = $this->listePeriodes(NBPERIODES);
        $listeElevesCours = $this->listeElevesCoursGrp();
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT IGNORE INTO '.PFX.'bullLockElevesCours ';
        $sql .= "SET matricule=:matricule, coursGrp=:coursGrp, periode=:periode, locked='0' ";
        $requete = $connexion->prepare($sql);
        $resultat = 0;
        $liste = array();
        foreach ($listeElevesCours as $matricule => $listeCoursGrp) {
            foreach ($listeCoursGrp as $coursGrp => $wtf) {
                foreach ($listePeriodes as $periode) {
                    $data = array(':matricule' => $matricule, ':coursGrp' => $coursGrp, ':periode' => $periode);
                    $resultat += $requete->execute($data);
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * Enregistrement des positions des verrous depuis le formulaire.
     *
     * @param $post
     * @param $periode
     *
     * @return nombre d'enregistrements réalisés
     */
    public function saveLocksBulletin($post, $periode)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'bullLockElevesCours SET ';
        $sql .= 'matricule=:matricule, coursGrp=:coursGrp, locked=:locked, periode=:periode ';
        $sql .= 'ON DUPLICATE KEY UPDATE locked=:locked';

        $requete = $connexion->prepare($sql);
        $nbResultats = 0;
        foreach ($post as $unChamp => $value) {
            $data = explode('#', $unChamp);
            if ($data[0] == 'lock') {
                // dans le coursGrp, un espace éventuel avait été remplacé par un "~" dans le template;
            // il faut remettre l'espace
            $coursGrp = str_replace('~', ' ', $data[1]);
                $matricule = $data[2];
                $locked = $value;
                $verrou = array(
                ':matricule' => $matricule,
                ':coursGrp' => $coursGrp,
                ':locked' => $value,
                ':periode' => $periode,
                );

                $nbResultats += $requete->execute($verrou);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $nbResultats;
    }

    /**
     * pose les verrous sur tous les cours des classes passées dans $post
     * $post provenant du formulaire de l'administrateur du bulletin.
     *
     * @param $post
     *
     * @return nombre de verrous enregistrés
     */
    public function saveLocksAdmin($post, $bulletin)
    {
        $verrouiller = isset($post['verrou']) ? $post['verrou'] : null;
        $nbResultats = 0;

        if (($verrouiller == 0) || ($verrouiller == 1)) {
            $listeEleves = array();
            foreach ($post as $key => $value) {
                $data = explode('_', $key);
                if ($data[0] == 'classe') {
                    $classe = $data[1];
                    $listeEleves[$classe] = self::listeElevesCoursDeGroupe($classe);
                }
            }
            $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'INSERT INTO '.PFX.'bullLockElevesCours ';
            $sql .= "SET matricule=:matricule, coursGrp=:coursGrp, locked='$verrouiller', periode='$bulletin' ";
            $sql .= "ON DUPLICATE KEY UPDATE locked='$verrouiller'";
            $requete = $connexion->prepare($sql);
            foreach ($listeEleves as $classe => $eleves) {
                foreach ($eleves as $matricule => $listeCours) {
                    foreach ($listeCours as $no => $coursGrp) {
                        $data = array(':matricule' => $matricule, ':coursGrp' => $coursGrp);
                        $nbResultats += $requete->execute($data);
                    }
                }
            }
            Application::DeconnexionPDO($connexion);
        }

        return $nbResultats;
    }

    /**
     * Enregistre le verrouillage / déverrouillage des cotes par classe, par cours et par élève
     * On déverrouille ou on verrouille (voir le contenu de la variable $_POST['verrouiller']) au cas par cas
     * à envisager: un verrouillage pour l'ensemble d'une classe => 1 enregistrement dans la BD / classe
     *  et un verrouillage pour l'ensemble d'un cours => 1 enregistrement dans la BD / cours.
     *
     * @param post
     *
     * @return nombre d'enregistrements
     */
    public function saveLocksClasseCoursEleve($post)
    {
        $verrouiller = isset($_POST['verrouiller']) ? $_POST['verrouiller'] : null;
        if (!(in_array($verrouiller, array('0', '1')))) {
            die('illegal lock');
        }
        $bulletin = isset($_POST['bulletin']) ? $_POST['bulletin'] : null;
        if (!($bulletin)) {
            die('illegal period');
        }

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'bullLockElevesCours ';
        $sql .= "SET matricule=:matricule, coursGrp=:coursGrp, locked=:verrouiller, periode='$bulletin' ";
        $sql .= 'ON DUPLICATE KEY UPDATE locked=:verrouiller';
        $requete = $connexion->prepare($sql);
        $nb = 0;
        foreach ($post as $key => $value) {
            // récupérer les champs dont le nom commence par 'eleve' comme 'eleve%4087_coursGrp%4#GT:REL2-01'
            if (preg_match('/^eleve/', $key)) {
                $data = explode('_', $key);
                $eleve = $data[0];
                $dataEleve = explode('%', $eleve);
                $matricule = $dataEleve[1];

                $coursGrp = $data[1];
                $dataCours = explode('%', $coursGrp);

                // remplacement du code '#' utilisé pour remplacer le caractère ' ' dans le nom de champ
                $coursGrp = str_replace('#', ' ', $dataCours[1]);
                $data = array(
                        ':matricule' => $matricule,
                        ':coursGrp' => $coursGrp,
                        ':verrouiller' => $verrouiller,
                        );
                $nb += $requete->execute($data);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $nb;
    }

    /**
     * function listeAttitudes.
     *
     * @param $listeEleves
     * @parame $coursGrp
     *
     * @param $bulletin
     *
     * liste des attitudes pour tous les élèves d'une liste donnée pour les cours
     * donnés. Pour un bulletin donné
     */
    public function listeAttitudes($listeEleves, $coursGrp, $bulletin)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        // attribution de l'attitude "Acquis" par défaut pour chacune des attitudes
        // pour chaque élève
        // permet de traiter les élèves qui n'ont pas encore d'attitude dans la BD
        $listeAttitudes = array();
        foreach ($listeEleves as $matricule => $data) {
            $listeAttitudes[$matricule] = array(1 => 'N', 2 => 'N', 3 => 'N', 4 => 'N');
        }

        // recherche et rectifications dans la base de données
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, coursGrp, att1, att2, att3, att4 ';
        $sql .= 'FROM '.PFX.'bullAttitudes ';
        $sql .= "WHERE matricule IN ($listeElevesString) and coursGrp = '$coursGrp' ";
        $sql .= "AND bulletin = '$bulletin' ";
        $sql .= 'ORDER BY matricule';

        $resultat = $connexion->query($sql);
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $listeAttitudes[$matricule] = array(1 => $ligne['att1'], 2 => $ligne['att2'], 3 => $ligne['att3'], 4 => $ligne['att4']);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeAttitudes;
    }

    /**
     * retourne la liste de tous les commentaires pour un cours donné pour toutes les périodes pour tous les élèves d'une liste donnée.
     *
     * @param $listeEleves
     * @param $coursGrp
     * @result array
     */
    public function listeCommentaires($listeEleves, $coursGrp)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, bulletin, commentaire ';
        $sql .= 'FROM '.PFX.'bullCommentProfs ';
        $sql .= "WHERE matricule IN ($listeElevesString) AND coursGrp = '$coursGrp' ";
        $sql .= 'ORDER BY matricule, coursGrp';
        $resultat = $connexion->query($sql);
        $listeCommentaires = array();
        while ($ligne = $resultat->fetch()) {
            $matricule = $ligne['matricule'];
            // $cours = $ligne['coursgrp'];
            $bulletin = $ligne['bulletin'];
            $listeCommentaires[$matricule][$bulletin] = stripslashes($ligne['commentaire']);
        }
        Application::DeconnexionPDO($connexion);

        return $listeCommentaires;
    }

    /**
     * liste des commentaires pour tous les cours d'une liste d'élèves passée en argument
     * pour un bulletin éventuellement donné
     * si pas de bulletin précisé, renvoie les commentaires de tous les bulletins.
     *
     * Typiquement pour la génération d'un bulletin d'élève
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
        $sql .= 'FROM '.PFX.'bullCommentProfs ';
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
     * produire un tableau reprenant la somme des cotes de TJ et les sommes des cotes CERT
     * pour une liste d'élèves donnés et pour un bulletin donné
     * la liste est indexée sur les matricules et les cours concernés.
     *
     * @param $listeEleves
     * @param $bulletin
     *
     * @return array
     */
    public function sommesTjCertEleves($listeEleves, $bulletin)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, coursGrp, idComp, form, maxForm, cert, maxCert ';
        $sql .= 'FROM '.PFX.'bullDetailsCotes ';
        $sql .= "WHERE (matricule IN ($listeElevesString)) ";
        $sql .= "AND (bulletin = '$bulletin') ";
        $sql .= 'ORDER BY matricule, coursGrp, idComp';

        $cotesVides = array(
                    'form' => array('cote' => '', 'max' => ''),
                    'cert' => array('cote' => '', 'max' => ''),
                    );
        $listeCotes = array();
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $coursGrp = $ligne['coursGrp'];
                $idComp = $ligne['idComp'];
                $form = $this->sansVirg($ligne['form']);
                $maxForm = $this->sansVirg($ligne['maxForm']);
                $cert = $this->sansVirg($ligne['cert']);
                $maxCert = $this->sansVirg($ligne['maxCert']);
                if (!(isset($listeCotes[$matricule][$coursGrp]))) {
                    $listeCotes[$matricule][$coursGrp] = $cotesVides;
                }

                // s'il y a du **Formatif** pour cet enregistrement, on l'additionne
                if (($form != '') && ($maxForm != '')) {
                    // si form et maxForm sont des informations numériques, on les additionne
                    if (is_numeric($form) && is_numeric($maxForm)) {
                        $listeCotes[$matricule][$coursGrp]['form']['cote'] += $form;
                        $listeCotes[$matricule][$coursGrp]['form']['max'] += $maxForm;
                    }
                }  // if $form...
                // s'il y a du **Certificatif** pour cet enregistrement, on l'additionne
                if (($cert != '') && ($maxCert != '')) {
                    // si cert et maxCert sont des informations numériques, on les additionne
                    if (is_numeric($cert) && is_numeric($maxCert)) {
                        $listeCotes[$matricule][$coursGrp]['cert']['cote'] += $cert;
                        $listeCotes[$matricule][$coursGrp]['cert']['max'] += $maxCert;
                    }
                }  // if $cert...
            }  // while
        } // if $resultat
        Application::DeconnexionPDO($connexion);

        return $listeCotes;
    }

    /**
     * produire un tableau reprenant les sommes des cotes TJ et les sommes des cotes CERT
     * pour une liste d'élèves, une liste de cours, une liste de compétences et un bulletin donnés.
     *
     * @param $listeEleves: tableau dont les keys sont les $matricule
     * @param $listeCoursGrp: liste des coursGrp pour ces élèves
     * @param $listeCompetences: liste des compétences existantes pour ce cours
     * @param $bulletin: n° du bulletin concerné
     *
     * @return array tableau de la somme des cotes par élève, par cours et par compétence
     */
    public function listeCotes($listeEleves, $listeCoursGrp, $listeCompetences, $bulletin)
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
        $sql = 'SELECT matricule, coursGrp, idComp, form, maxForm, cert, maxCert ';
        $sql .= 'FROM '.PFX.'bullDetailsCotes ';
        $sql .= "WHERE (matricule IN ($listeElevesString)) AND (coursGrp IN ($listeCoursGrpString)) ";
        $sql .= "AND (bulletin = '$bulletin') ";
        $sql .= 'ORDER BY matricule, idComp ';

        $cotesVides = array(
                            'form' => array('cote' => '', 'maxForm' => '', 'echec' => false),
                            'cert' => array('cote' => '', 'maxCert' => '', 'echec' => false),
                            );
        $listeCotes = array();
        $resultat = $connexion->query($sql);
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $coursGrp = $ligne['coursGrp'];
                $idComp = $ligne['idComp'];

                // remplir le tableau avec des vides si on n'a pas encore vu
                // de competences pour cet élève (il n'est pas encore passé)
                if (!(isset($listeCotes[$matricule][$coursGrp]))) {
                    // on a besoin du cours pour trouver les compétences
                    $cours = self::coursDeCoursGrp($coursGrp);
                    $competences = $listeCompetences[$cours];
                    foreach ($competences as $id => $data) {
                        $listeCotes[$matricule][$coursGrp][$id] = $cotesVides;
                    }
                }

                // s'il y a du **Formatif** pour la compétence $idComp, on le note pour cet élève et ce cours
                if (($ligne['form'] != '') && ($ligne['maxForm'] != '')) {
                    $listeCotes[$matricule][$coursGrp][$idComp]['form']['cote'] = $this->sansVirg($ligne['form']);
                    $listeCotes[$matricule][$coursGrp][$idComp]['form']['maxForm'] = $this->sansVirg($ligne['maxForm']);
                    if ($ligne['maxForm'] > 0) {
                        if (($ligne['form'] / $ligne['maxForm']) < 0.5) {
                            $listeCotes[$matricule][$coursGrp][$idComp]['form']['echec'] = 'echec';
                        }
                    }
                }

                // ----------------------------------------------------------------
                // s'il n'y a pas de cote mais seulement un maximum pour cette compétence (cote en attente)
                if (($ligne['maxForm'] != '') && ($ligne['form'] == '')) {
                    $listeCotes[$matricule][$coursGrp][$idComp]['form']['maxForm'] = $this->sansVirg($ligne['maxForm']);
                    $listeCotes[$matricule][$coursGrp][$idComp]['form']['echec'] = '';
                }
                // ----------------------------------------------------------------

                // ----------------------------------------------------------------
                // s'il n'y a qu'une mention non numérique (Abs, CM, NR, ...)
                if (!is_numeric($this->sansVirg($ligne['form']))) {
                    $listeCotes[$matricule][$coursGrp][$idComp]['form']['cote'] = $ligne['form'];
                    $listeCotes[$matricule][$coursGrp][$idComp]['form']['maxForm'] = $ligne['maxForm'];
                }
                // ----------------------------------------------------------------

                // s'il y a du **Certificatif** pour la compétence $competence, on le note pour cet élève et ce cours
                if (($ligne['cert'] != '') && ($ligne['maxCert'] != '')) {
                    $listeCotes[$matricule][$coursGrp][$idComp]['cert']['cote'] = $this->sansVirg($ligne['cert']);
                    $listeCotes[$matricule][$coursGrp][$idComp]['cert']['maxCert'] = $this->sansVirg($ligne['maxCert']);
                    if ($ligne['maxCert'] > 0) {
                        if (($ligne['cert'] / $ligne['maxCert']) < 0.5) {
                            $listeCotes[$matricule][$coursGrp][$idComp]['cert']['echec'] = 'echec';
                        }
                    }
                }

                // ----------------------------------------------------------------
                // s'il n'y a pas de cote mais seulement un maximum pour cette compétence (cote en attente)
                if (($ligne['maxCert'] != '') && ($ligne['cert'] == '')) {
                    $listeCotes[$matricule][$coursGrp][$idComp]['cert']['maxCert'] = $this->sansVirg($ligne['maxCert']);
                    $listeCotes[$matricule][$coursGrp][$idComp]['cert']['echec'] = '';
                }
                // ----------------------------------------------------------------

                // ----------------------------------------------------------------
                // s'il n'y a qu'une mention non numérique (Abs, CM, NR, ...)
                if (!is_numeric($this->sansVirg($ligne['cert']))) {
                    $listeCotes[$matricule][$coursGrp][$idComp]['cert']['cote'] = $ligne['cert'];
                    $listeCotes[$matricule][$coursGrp][$idComp]['cert']['maxCert'] = $ligne['maxCert'];
                }
                // ----------------------------------------------------------------
            }// while
        } // if $resultat
        Application::DeconnexionPDO($connexion);

        return $listeCotes;
    }

    /**
     * calcule les cotes de périodes pondérées pour tous les cours d'un élèves
     * les sommes des TJ et des Cert sont passées dans le tableau $sommesTjCert,
     * les pondérations à appliquer sont passées dans le tableau $ponderations.
     *
     * @param $sommesTjCert array
     * @param $ponderations array
     *
     * @return array
     */
    public function cotesPeriodePonderees($sommesTjCert, $ponderations)
    {
        $listeCotesPonderees = array();
        foreach ($sommesTjCert as $matricule => $lesCours) {
            foreach ($lesCours as $coursGrp => $lesCotes) {
                $laPonderation = $ponderations[$coursGrp];
                if (in_array($matricule, $laPonderation)) {
                    $ponderation = $laPonderation[$matricule];
                } else {
                    $ponderation = $laPonderation['all'];
                }
                if (is_numeric($lesCotes['form']['cote']) && is_numeric($lesCotes['form']['max'])) {
                    $coteForm100 = $lesCotes['form']['cote'] / $lesCotes['form']['max'];
                    $listeCotesPonderees[$matricule][$coursGrp]['form']['cote'] = round($coteForm100 * $ponderation['form'], 1);
                    $listeCotesPonderees[$matricule][$coursGrp]['form']['max'] = $ponderation['form'];
                    $mention = $this->calculeMention($coteForm100 * 100);
                    $listeCotesPonderees[$matricule][$coursGrp]['form']['mention'] = str_replace('+', 'plus', $mention);
                }
                if (is_numeric($lesCotes['cert']['cote']) && is_numeric($lesCotes['cert']['max'])) {
                    $coteCert100 = $lesCotes['cert']['cote'] / $lesCotes['cert']['max'];
                    $listeCotesPonderees[$matricule][$coursGrp]['cert']['cote'] = round($coteCert100 * $ponderation['cert'], 1);
                    $listeCotesPonderees[$matricule][$coursGrp]['cert']['max'] = $ponderation['cert'];
                    $mention = $this->calculeMention($coteCert100 * 100);
                    $listeCotesPonderees[$matricule][$coursGrp]['cert']['mention'] = str_replace('+', 'plus', $mention);
                }
            }
        }

        return $listeCotesPonderees;
    }

    /**
     * retourne les cotesGlobales pondérées, formatives et certificatives
     * à partir des cotes brutes et des pondérations pour la période donnée.
     *
     * @param $listeCotes
     * @param $ponderations
     * @param $periode
     */
    public function listeGlobalPeriodePondere($listeCotes, $ponderations, $periode)
    {
        $listeCotesGlobales = array();
        foreach ($listeCotes as $matricule => $lesCoursGrp) {
            foreach ($lesCoursGrp as $coursGrp => $lesCompetences) {
                $coteForm = 0;
                $maxForm = 0;
                $coteCert = 0;
                $maxCert = 0;
                foreach ($lesCompetences as $idComp => $lesCotes) {
					if (is_numeric($this->sansVirg($lesCotes['form']['cote'])))
						$coteForm += $this->sansVirg($lesCotes['form']['cote']);
                    if (($lesCotes['form']['cote'] != '') && (is_numeric($this->sansVirg($lesCotes['form']['cote'])))) {
                        $maxForm += $this->sansVirg($lesCotes['form']['maxForm']);
                    }
					if (is_numeric($this->sansVirg($lesCotes['cert']['cote'])))
						$coteCert += $this->sansVirg($lesCotes['cert']['cote']);
                    if (($lesCotes['cert']['cote'] != '') && (is_numeric($this->sansVirg($lesCotes['cert']['cote'])))) {
                        $maxCert += $this->sansVirg($lesCotes['cert']['maxCert']);
                    }
                }
                $poids = $ponderations[$coursGrp];
                // y a-t-il une pondération spéciale pour l'élève à ce matricule?
                if (in_array($matricule, array_keys($poids))) {
                    $ponderation = $poids[$matricule];
                } else {
                    $ponderation = $poids['all'];
                }
                $cotesPonderees = array('form' => array('cote' => null, 'max' => null, 'echec' => null),
                                        'cert' => array('cote' => null, 'max' => null, 'echec' => null), );

                // s'il y a du formatif
                if ($ponderation[$periode]['form'] && $maxForm > 0) {
                    $cote = $coteForm / $maxForm * $ponderation[$periode]['form'];
                    $cote = ($cote > 50) ? round($cote, 0) : round($cote, 1);
                    $max = $ponderation[$periode]['form'];
                    if ($max > 0) {
                        $echec = ($cote / $max < 0.5) ? 'echec' : '';
                    } else {
                        $echec = '';
                    }
                    $cotesPonderees['form']['cote'] = $cote;
                    $cotesPonderees['form']['max'] = $max;
                    $cotesPonderees['form']['echec'] = $echec;
                }

                // s'il y a du certificatif
                if ($ponderation[$periode]['cert'] && $maxCert > 0) {
                    $cote = $coteCert / $maxCert * $ponderation[$periode]['cert'];
                    $cote = ($cote > 50) ? round($cote, 0) : round($cote, 1);
                    $max = $ponderation[$periode]['cert'];
                    if ($max > 0) {
                        $echec = ($cote / $max < 0.5) ? 'echec' : '';
                    } else {
                        $echec = '';
                    }
                    $cotesPonderees['cert']['cote'] = $cote;
                    $cotesPonderees['cert']['max'] = $max;
                    $cotesPonderees['cert']['echec'] = $echec;
                }

                $listeCotesGlobales[$matricule][$coursGrp] = $cotesPonderees;
            }
        }

        return $listeCotesGlobales;
    }

    /**
     * retourne une liste des situations et cotes de délibé
     * pour la liste d'élèves donnée
     * pour une période donnée
     * la liste est indexée sur le cours et contient
     *  - coursGrp
     *  - situation
     *  - maxSituation
     *  - sitDelibe
     *  - degre, hook, star
     *  - statut du cours (AC, FC,...).
     *
     * @param $listeEleves
     * @param $bulletin
     *
     * @return array
     */
    public function listeSituationsClassePourDelibe($listeEleves, $bulletin)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, coursGrp, situation, maxSituation, choixProf, attributProf, sitDelibe, attributDelibe, cours, statut ';
        $sql .= 'FROM '.PFX.'bullSituations ';
        $sql .= 'JOIN '.PFX.'cours ON (SUBSTR('.PFX."bullSituations.coursGrp,1, LOCATE('-',".PFX.'bullSituations.coursGrp)-1) = '.PFX.'cours.cours) ';
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= "WHERE matricule IN ($listeElevesString) AND bulletin='$bulletin' ";
        $resultat = $connexion->query($sql);
        $liste = array();

        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $coursGrp = $ligne['coursGrp'];
                $cours = $ligne['cours'];
                $attributDelibe = $ligne['attributDelibe'];
                $ligne['sitDelibe'] = sansVirg($ligne['sitDelibe']);
                if ($attributDelibe == 'hook') {
                    $ligne['sitDelibe'] = '['.$ligne['sitDelibe'].']';
                }
                $ligne['echec'] = (($ligne['sitDelibe'] < 50) && ($ligne['sitDelibe'] != '')  && ($attribut != 'hook')) ? 'echec' : '';
                $ligne['symbole'] = self::attribut2Symbole($attribut);
                $liste[$matricule][$cours] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne le symbole correspondant à un attribut de cote de délibé.
     *
     * @param string $attribut
     *
     * @return char
     */
    public static function attribut2Symbole($attribut)
    {
        $symbolesAttributs = array(
            '' => '',
            'hook' => '',
            'degre' => '²',
            'star' => '<i class="fa fa-star"></i>',
            'magique' => '<i class="fa fa-magic"></i>',
            'externe' => '<i class="fa fa-graduation-cap"></i>',
            );
        $symbole = $symbolesAttributs[$attribut];

        return $symbole;
    }

    /**
     * retourne la liste des situations de délibés provenant des bulletins
     * pour une liste d'élèves données
     * pour une liste de cours donnée et
     * pour un bulletin donné
     * Si le numéro du bulletin n'est pas précisé, la liste revient avec tous les bulletins.
     *
     * @param string|array $listeEleves   : matricule ou liste des matricules des élèves concernés
     * @param string|array $listeCoursGrp : liste des coursGrp concernés
     * @param $bullletin : numéro du bulletin concerné
     *
     * @return array : liste des situations dans la liste de coursGrp indiqué
     */
    public function listeSituationsCours($listeEleves, $listeCoursGrp, $bulletin = null, $isDelibe = true)
    {
        // la liste d'élèves est fournie comme un tableau indexé sur les matricules
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", $listeCoursGrp)."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT coursGrp, matricule, bulletin, situation, maxSituation, nbheures, libelle, ';
        $sql .= 'choixProf, sitDelibe, cours, statut, attributProf, attributDelibe ';
        $sql .= 'FROM '.PFX.'bullSituations ';
        $sql .= 'JOIN '.PFX."cours ON (cours = SUBSTR(coursGrp,1,LOCATE('-',coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'cours.cadre = '.PFX.'statutCours.cadre) ';
        $sql .= "WHERE matricule in ($listeElevesString) AND (coursGrp IN ($listeCoursGrpString)) ";
        if ($bulletin) {
            $sql .= "AND bulletin='$bulletin' ";
        }
        $sql .= 'ORDER BY bulletin, matricule';

        $resultat = $connexion->query($sql);
        $listeSituationsCours = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $bulletin = $ligne['bulletin'];
                $coursGrp = $ligne['coursGrp'];
                $statut = $ligne['statut'];
                $cours = $ligne['cours'];
                $nbheures = $ligne['nbheures'];
                $libelle = $ligne['libelle'];
                $choixProf = $ligne['choixProf'];
                $attributProf = $ligne['attributProf'];
                $sitDelibe = $ligne['sitDelibe'];
                $attributDelibe = $ligne['attributDelibe'];
                $maxSituation = $ligne['maxSituation'] == null ? null : $ligne['maxSituation'];
                // s'il n'y a pas de max, alors il n'y a pas de situation
                $situation = $this->sansVirg(($maxSituation == null) ? null : $ligne['situation']);
                // à partir du pivot 50/100, on arrondit à l'unité s'il y a une cote de situation
                if ($situation != null) {
                    $situation = ($situation > 50) ? round($situation, 0) : round($situation, 1);
                }
                $situationPourCent = ($situation == null) ? null : round(100 * $situation / $maxSituation, 0);

                // sommes-nous dans une période avec délibé?
                if ($isDelibe) {
                    $sitDelibe = $ligne['sitDelibe'];
                    $echec = (($sitDelibe < 50) && ($sitDelibe != '') && ($attributDelibe != 'hook')) ? 'echec' : '';
                    $choixProf = $ligne['choixProf'];
                } else {
                    $sitDelibe = null;
                    $choixProf = null;
                    $echec = null;
                }

                $listeSituationsCours[$matricule][$coursGrp][$bulletin] = array(
                    'sit' => $situation,
                    'max' => $maxSituation,
                    'pourcent' => $situationPourCent,
                    'echec' => $echec,
                    'choixProf' => $choixProf,
                    'attributProf' => $attributProf,
                    'sitDelibe' => $sitDelibe,
                    'attributDelibe' => $attributDelibe,
                    'statut' => $statut,
                    'cours' => $cours,
                    'nbheures' => $nbheures,
                    'libelle' => $libelle,
                    );
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeSituationsCours;
    }

    /**
     * liste des situations de délibés par cours (et non par coursGrp).
     *
     * @param $listeEleves
     * @param $listeCoursGrp
     * @param $bulletin
     */
    public function listeSituationsDelibe($listeEleves, $listeCoursGrp, $bulletin)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", $listeCoursGrp)."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT bs.coursGrp, bs.matricule, nbheures, libelle, sitDelibe, choixProf, situation, maxSituation, cours, statut, attributProf, attributDelibe ';
        $sql .= 'FROM '.PFX.'bullSituations AS bs ';
        $sql .= 'JOIN '.PFX."cours ON (cours = SUBSTR(coursGrp,1,LOCATE('-',coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'cours.cadre = '.PFX.'statutCours.cadre) ';
        $sql .= "WHERE bs.matricule in ($listeElevesString) AND (bs.coursGrp IN ($listeCoursGrpString)) ";
        $sql .= "AND bulletin='$bulletin' ";
        $sql .= 'ORDER BY bulletin, bs.matricule ';

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $cours = $ligne['cours'];
                $sitDelibe = trim($ligne['sitDelibe']);
                $attributDelibe = $ligne['attributDelibe'];
                $echec = (($sitDelibe < 50) && ($sitDelibe != '') && ($attributDelibe != 'hook')) ? 'echec' : '';

                $ligne['echec'] = $echec;
                $liste[$matricule][$cours] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * recherche la dernière cotes de situation connue, avant le bulletin $bulletin
     * parmi les situations transmises par $listeSituations
     * cette variable contenant toutes les situations pour toutes les périodes.
     *
     * @param $listeSituations
     * @param $bulletin
     *
     * @return array : liste des situations précédentes
     */
    public function situationsPrecedentes($listeSituations, $bulletin)
    {
        $sitPrecedentes = array();
        foreach ($listeSituations as $matricule => $cotesCoursGrp) {
            foreach ($cotesCoursGrp as $coursGrp => $periodes) {
                $sitPrecedentes[$matricule][$coursGrp] = $this->situationPrecedente($periodes, $bulletin);
            }
        }

        return $sitPrecedentes;
    }

    /**
     * renvoie la dernière situation précédente connue avant le bulletin $bulletin pour le cours $coursGrp
     * la fonction retourne dans les périodes précédentes juqu'à trouver une sitPrec
     * fonction appelée par self:situationsPrecedentes.
     *
     * @param $coursGrp
     * @param $bulletin
     *
     * @return array
     */
    private function situationPrecedente($situations, $bulletin)
    {
        $sitPrec = array('sit' => null, 'maxSit' => null);
        $noBull = $bulletin - 1;
        $found = false;
        // on va, éventuellement, jusqu'au bulletin 0 (correspondant au report de l'année précédente)
        while (($noBull >= 0) && !($found)) {
            if (isset($situations[$noBull]) && isset($situations[$noBull]['max']) && (trim($situations[$noBull]['sit']) != '')) {
                $sitPrec['sit'] = $situations[$noBull]['sit'];
                $sitPrec['maxSit'] = $situations[$noBull]['max'];
                $found = true;
            }
            --$noBull;
        }

        return $sitPrec;
    }

    /**
     * calcule la nouvelle situation à partir de la situation précédente ($listeSituations)
     * et de la cote globale de période actuelle
     * pour le bulletin donné.
     *
     * @param $listeSituations
     * @param $listeGlobalPeriodePondere
     * @param $bulletin
     */
    public function calculeNouvellesSituations($listeSituations, $listeGlobalPeriodePondere, $bulletin)
    {
        foreach ($listeGlobalPeriodePondere as $matricule => $unCours) {
            foreach ($unCours as $coursGrp => $lesCotes) {
                // on recherche la situation précédente dans tous les bulletins précédents
                $sitPrec = $this->situationPrecedente($listeSituations[$matricule][$coursGrp], $bulletin);
                // au cas où, la situation précédente est recopiée dans la liste (cas d'un bulletin très ancien)
                $listeSituations[$matricule][$coursGrp][$bulletin - 1] = $sitPrec;
                $listeSituations[$matricule][$coursGrp][$bulletin]['sit'] =
                    $sitPrec['sit']
                    + $listeGlobalPeriodePondere[$matricule][$coursGrp]['form']['cote']
                    + $listeGlobalPeriodePondere[$matricule][$coursGrp]['cert']['cote'];

                $listeSituations[$matricule][$coursGrp][$bulletin]['max'] =
                    $sitPrec['maxSit']
                    + $listeGlobalPeriodePondere[$matricule][$coursGrp]['form']['max']
                    + $listeGlobalPeriodePondere[$matricule][$coursGrp]['cert']['max'];

                // limiter le nombre de décimales si > PIVOT
                if ($listeSituations[$matricule][$coursGrp][$bulletin]['max'] > 50) {
                    $listeSituations[$matricule][$coursGrp][$bulletin]['sit'] = round($listeSituations[$matricule][$coursGrp][$bulletin]['sit'], 0);
                } else {
                    $listeSituations[$matricule][$coursGrp][$bulletin]['sit'] = round($listeSituations[$matricule][$coursGrp][$bulletin]['sit'], 1);
                }

                // s'il n'y a pas de valeur pour le max, alors il n'y a pas de situation
                if ($listeSituations[$matricule][$coursGrp][$bulletin]['max'] == 0) {
                    $listeSituations[$matricule][$coursGrp][$bulletin]['sit'] = null;
                    $listeSituations[$matricule][$coursGrp][$bulletin]['max'] = null;
                }
                // si le max est > 0, on peut calculer un pourcentage
                if ($listeSituations[$matricule][$coursGrp][$bulletin]['max'] > 0) {
                    $listeSituations[$matricule][$coursGrp][$bulletin]['pourcent'] =
                    round(100 * $listeSituations[$matricule][$coursGrp][$bulletin]['sit'] / $listeSituations[$matricule][$coursGrp][$bulletin]['max'], 0);
                } else {
                    $listeSituations[$matricule][$coursGrp][$bulletin]['pourcent'] = null;
                }
            }
        }

        return $listeSituations;
    }

     /**
      * Enregistrement des cotes de situations recalculées après remplissage du bulletin.
      *
      * @param $listeNouvellesSituations
      * @param $bulletin
      * @result Null
      */
     public function enregistrerSituations($listeSituations, $bulletin)
     {
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         foreach ($listeSituations as $matricule => $cours) {
             foreach ($cours as $coursGrp => $lesSituations) {
                 $situation = $lesSituations[$bulletin]['sit'];
                 $maxSituation = $lesSituations[$bulletin]['max'];
                 $sql = 'INSERT INTO '.PFX.'bullSituations ';
                 $sql .= "SET matricule='$matricule', coursGrp='$coursGrp', bulletin='$bulletin', ";
                 $sql .= "situation='$situation', maxSituation = '$maxSituation' ";
                 $sql .= "ON DUPLICATE KEY UPDATE situation='$situation', maxSituation='$maxSituation'";
                 $resultat = $connexion->exec($sql);
             }
         }
         Application::DeconnexionPDO($connexion);
     }

    /**
     * enregistrement des cotes de situations de l'ensemble d'une classe
     * en provenance du formulaire de l'admin.
     *
     * @param $post
     *
     * @return int : nombre d'enregistrements dans la BD
     */
    public function enregistrerSituationsClasse($post)
    {
        $bulletin = $post['bulletin'];
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sqlSit = 'INSERT INTO '.PFX.'bullSituations ';
        $sqlSit .= "SET situation=:sit, bulletin='$bulletin',matricule=:matricule, coursGrp=:coursGrp ";
        $sqlSit .= 'ON DUPLICATE KEY UPDATE situation=:sit';

        $sqlMax = 'INSERT INTO '.PFX.'bullSituations ';
        $sqlMax .= "SET maxSituation=:max, bulletin='$bulletin',matricule=:matricule, coursGrp=:coursGrp ";
        $sqlMax .= 'ON DUPLICATE KEY UPDATE maxSituation=:max';
        $requeteSit = $connexion->prepare($sqlSit);
        $requeteMax = $connexion->prepare($sqlMax);
        $nbResultats = 0;
        foreach ($post as $eleveCours => $cote) {
            $eleveCours = explode('#', $eleveCours);
            $type = $eleveCours[0];
            if (($type == 'max') || ($type == 'sit')) {
                $matricule = explode('_', $eleveCours[1]);
                $matricule = $matricule[1];
                $coursGrp = explode('_', $eleveCours[2]);
                // dans le fichier .tpl de la grille des situations, les caractères "espace" sont remplacés par des "!"
                // il faut remettre les " " pour rétablir les noms des cours
                $coursGrp = str_replace('!', ' ', $coursGrp[1]);
            }
            switch ($type) {
                case 'sit':
                    $nbResultats += $requeteSit->execute(array(
                        ':matricule' => $matricule,
                        ':coursGrp' => $coursGrp,
                        ':sit' => $cote,
                        ));
                    break;
                case 'max':
                    $nbResultats += $requeteMax->execute(array(
                        ':matricule' => $matricule,
                        ':coursGrp' => $coursGrp,
                        ':max' => $cote,
                        ));
                    break;
                default:
                    break;
                }
        }
        Application::DeconnexionPDO($connexion);

        return $nbResultats;
    }

    public function sommesBrutesFormCert($bulletin, $classe)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT '.PFX.'bullDetailsCotes.matricule, coursGrp, form, maxForm,cert, maxCert ';
        $sql .= 'FROM '.PFX.'bullDetailsCotes ';
        $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'bullDetailsCotes.matricule) ';
        $sql .= "WHERE classe = '$classe' AND bulletin = '$bulletin'";
        $resultat = $connexion->query($sql);
        $listeSommes = array();
        while ($ligne = $resultat->fetch()) {
            $matricule = $ligne['matricule'];
            $coursGrp = $ligne['coursGrp'];
            if (!(isset($listeSommes[$matricule][$coursGrp]))) {
                $listeSommes[$matricule][$coursGrp] = array('form' => '', 'maxForm' => '', 'cert' => '', 'maxCert' => '');
            }
            if ((is_numeric($ligne['form'])) && (is_numeric($ligne['maxForm']))) {
                $listeSommes[$matricule][$coursGrp]['form'] += $ligne['form'];
                $listeSommes[$matricule][$coursGrp]['maxForm'] += $ligne['maxForm'];
            }
            if ((is_numeric($ligne['cert'])) && (is_numeric($ligne['maxCert']))) {
                $listeSommes[$matricule][$coursGrp]['cert'] += $ligne['cert'];
                $listeSommes[$matricule][$coursGrp]['maxCert'] += $ligne['maxCert'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeSommes;
    }

     /**
      * recalcul des cotes de situation au bulletin après modification manuelle des cotes de situation dans le bulletin n-1.
      *
      * @param $bulletin
      * @param $classe
      *
      * @return  array : cotes recalculées pour la période $bulletin
      */
     public function recalculSituationsClasse($bulletin, $classe)
     {
         $Ecole = new Ecole();
         $listeEleves = $Ecole->listeEleves($classe, 'groupe');
         $listeSitPrec = $this->getSituations($bulletin - 1, $listeEleves);
         $sommesPeriode = $this->sommesBrutesFormCert($bulletin, $classe);
         $listeCoursGrpClasse = $Ecole->listeCoursGrpClasse($classe);
         $ponderations = $this->getPonderations($listeCoursGrpClasse);

         $listeGlobalPeriode = array();
         foreach ($sommesPeriode as $matricule => $listeSommes) {
             foreach ($listeSommes as $coursGrp => $cotes) {
                 $periode = array();
                 $lesPonderations = $ponderations[$coursGrp];
                // recherche de la pondération pour l'élève et pour la période
                // $lesPonderations est vide si l'élève n'a pas ce coursGrp à la période suivante (voir historique)
                // ne pas faire de calculs dans ce cas
                if (isset($lesPonderations)) {
                    if (in_array($matricule, array_keys($lesPonderations))) {
                        $ponderation = $lesPonderations[$matricule][$bulletin];
                    } else {
                        $ponderation = $lesPonderations['all'][$bulletin];
                    }
                    if ((trim($cotes['form']) != '') && (trim($cotes['maxForm']) != '')) {
                        $form = $cotes['form'];
                        $maxForm = $cotes['maxForm'];
                        $periode['form'] = round($form / $maxForm * $ponderation['form'], 1);
                    } else {
                        $periode['form'] = null;
                    }

                    if ((trim($cotes['cert']) != '') && (trim($cotes['maxCert']) != '')) {
                        $cert = $cotes['cert'];
                        $maxCert = $cotes['maxCert'];
                        $periode['cert'] = round($cert / $maxCert * $ponderation['cert'], 1);
                    } else {
                        $periode['cert'] = null;
                    }
                    if ($periode['form'] || $periode['cert']) {
                        $listeGlobalPeriode[$matricule][$coursGrp] = array(
                            'periode' => array_sum($periode), 'maxPeriode' => $ponderation['form'] + $ponderation['cert'],
                            );
                    }
                }    // if ($lesPonderations)
             }
         }

         $listeSitAct = array();
         foreach ($listeSitPrec as $matricule => $listeCours) {
             foreach ($listeCours as $coursGrp => $cotes) {
                 $sitPrec = isset($listeSitPrec[$matricule][$coursGrp]['sit']) ? $listeSitPrec[$matricule][$coursGrp]['sit'] : null;
                 $sitPeriode = isset($listeGlobalPeriode[$matricule][$coursGrp]['periode']) ? $listeGlobalPeriode[$matricule][$coursGrp]['periode'] : null;
                 if (($sitPrec == null) || ($sitPeriode == null)) {
                     $listeSitAct[$matricule][$coursGrp]['sit'] = null;
                 } else {
                     $listeSitAct[$matricule][$coursGrp]['sit'] = $sitPrec + $sitPeriode;
                 }

                 $maxPrec = isset($listeSitPrec[$matricule][$coursGrp]['max']) ? $listeSitPrec[$matricule][$coursGrp]['max'] : null;
                 $maxPeriode = isset($listeGlobalPeriode[$matricule][$coursGrp]['maxPeriode']) ? $listeGlobalPeriode[$matricule][$coursGrp]['maxPeriode'] : null;
                 if (($maxPrec == null) || ($maxPeriode == null)) {
                     $listeSitAct[$matricule][$coursGrp]['max'] = null;
                 } else {
                     $listeSitAct[$matricule][$coursGrp]['max'] = $maxPrec + $maxPeriode;
                 }
             }
         }

         return $listeSitAct;
     }

     /**
      * Enregistrement de situations telles que recalculées après modification des situations.
      *
      * @param $listeSitActuelles
      * @param $bulletin :  période à enregistrer
      *
      * @return $nombre d'enregistrements
      */
     public function enregistrerSituationsRecalc($listeSitActuelles, $bulletin)
     {
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         $sql = 'INSERT INTO '.PFX.'bullSituations ';
         $sql .= "SET matricule=:matricule, bulletin='$bulletin', coursGrp=:coursGrp, ";
         $sql .= 'situation=:situation, maxSituation=:maxSituation ';
         $sql .= 'ON DUPLICATE KEY UPDATE situation=:situation, maxSituation=:maxSituation';
         $requete = $connexion->prepare($sql);
         $nbResultats = 0;
         foreach ($listeSitActuelles as $matricule => $listeCoursGrp) {
             foreach ($listeCoursGrp as $coursGrp => $cotes) {
                 $data = array(
                    ':matricule' => $matricule,
                    ':coursGrp' => $coursGrp,
                    ':situation' => $cotes['sit'],
                    ':maxSituation' => $cotes['max'],
                    );
                 $nbResultats += $requete->execute($data);
             }
         }
         Application::DeconnexionPDO($connexion);

         return $nbResultats;
     }

    /**
     * liste des élèves sous forme de tableau avec le suivant et le précédent
     * utile pour des listes chaînées.
     *
     * @param $listeEleves
     */
    public function listeElevesSuivPrec($listeEleves)
    {
        $liste = array();
        $precedent = null;
        foreach ($listeEleves as $matricule => $unEleve) {
            $liste[$matricule]['prev'] = $precedent;
            $precedent = $matricule;
        }
        $listeEleves = array_reverse($listeEleves, true);
        $suivant = null;
        foreach ($listeEleves as $matricule => $unEleve) {
            $liste[$matricule]['next'] = $suivant;
            $suivant = $matricule;
        }

        return $liste;
    }

    /**
     * totaux bruts pour le Formatif et le Certificatif
     * pour la liste des cotes d'une période donnée
     * concerne tous les élèves d'un cours.
     *
     * @param $listeCotes
     *
     * @return array
     */
    public function listeSommesFormCert($listeCotes)
    {
        $sommesFormCert = array();
        foreach ($listeCotes as $matricule => $lesCours) {
            foreach ($lesCours as $coursGrp => $competences) {
                $sommeForm = 0;
                $sommeMaxForm = 0;
                $sommeCert = 0;
                $sommeMaxCert = 0;
                foreach ($competences as $idComp => $lesCotes) {
                    // il n'y a qu'un seul cours, en fait
                    $form = $lesCotes['form']['cote'];
                    $maxForm = $lesCotes['form']['maxForm'];
                    $cert = $lesCotes['cert']['cote'];
                    $maxCert = $lesCotes['cert']['maxCert'];

                    // on n'additionne que si les cotes et les max sont numériques simultanément
                    if (is_numeric($form) && is_numeric($maxForm)) {
                        $sommeForm += $form;
                        $sommeMaxForm += $maxForm;
                    }
                    if (is_numeric($cert) && is_numeric($maxCert)) {
                        $sommeCert += $cert;
                        $sommeMaxCert += $maxCert;
                    }
                }
                // s'il n'y a pas de max, au total, il n'y a pas de cote formative
                $sommeMaxForm = ($sommeMaxForm == 0) ? '' : $sommeMaxForm;
                $sommeForm = ($sommeMaxForm == '') ? '' : $sommeForm;
                $pourcentForm = ($sommeMaxForm > 0) ? round(100 * $sommeForm / $sommeMaxForm) : null;
                // s'il n'y a pas de max, au total, il n'y a pas de cote EX
                $sommeMaxCert = ($sommeMaxCert == 0) ? '' : $sommeMaxCert;
                $sommeCert = ($sommeMaxCert == '') ? '' : $sommeCert;
                $pourcentCert = ($sommeMaxCert > 0) ? round(100 * $sommeCert / $sommeMaxCert) : null;

                $sommesFormCert[$matricule] = array(
                    'totalForm' => $sommeForm,
                    'maxForm' => $sommeMaxForm,
                    'pourcentForm' => $pourcentForm,
                    'totalCert' => $sommeCert,
                    'maxCert' => $sommeMaxCert,
                    'pourcentCert' => $pourcentCert,
                    );
            }
        }

        return $sommesFormCert;
    }

    /**
     * retourne la liste des cotes étoilées (cote certificative > cote globale).
     *
     * @param $listeSommesFormCert
     *
     * @return array : liste des cotes à étoiler; vide si pas étoilé
     */
    public function listeCotesEtoilees($listeSommesFormCert, $listeSituations, $coursGrp, $bulletin)
    {
        $liste = array();
        foreach ($listeSommesFormCert as $matricule => $cotes) {
            $liste[$matricule] = '';
            if (($cotes['pourcentCert'] != null) && (isset($listeSituations[$matricule][$coursGrp][$bulletin]['pourcent']))) {
                if ($cotes['pourcentCert'] > $listeSituations[$matricule][$coursGrp][$bulletin]['pourcent']) {
                    $liste[$matricule] = $cotes['pourcentCert'];
                }
            }
        }

        return $liste;
    }

    /**
     * retourne les cotes de CEB d'un élève dont on fournit le matricule.
     *
     * @param $matricule
     *
     * @return array
     */
    public function getCEB($matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT fr, math, sc, hg, l2 ';
        $sql .= 'FROM '.PFX.'bullCE1B ';
        $sql .= "WHERE matricule = '$matricule' ";
        $resultat = $connexion->query($sql);
        $CEB = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $CEB = $resultat->fetch();
        }
        Application::DeconnexionPDO($connexion);

        return $CEB;
    }

    /**
     * organisation rationnelle des cotes: une ligne par compétence extraite
     * du formulaire de rédaction du bulletin par cours.
     *
     * @param array $post
     *
     * @return array
     */
    public function organiserData($post)
    {
        $listeCotesParCompetences = array();
        $listeCotesPeriode = array();
        $listeAttitudes = array();
        $listeCommentaires = array();
        $listeChoixProf = array();
        $listeSituations = array();
        $listeAttributs = array();
        $genresPermis = array('form', 'maxForm', 'cert', 'maxCert');

        foreach ($post as $uneInfo => $value) {
            $value = htmlspecialchars($value);
            $data = explode('-', $uneInfo);
            switch ($data[0]) {
                case 'cote' :
                    // on retient les cotes Form et Cert
                    $matricule = substr($data[1], strpos($data[1], '_') + 1);
                    $idComp = substr($data[2], strpos($data[2], '_') + 1);
                    $genre = $data[3];
                    if (in_array($genre, $genresPermis)) {
                        $listeCotesParCompetences[$matricule][$idComp][$genre] = $this->sansVirg($value);
                    }
                    // fonction $this->sansVirg pour transformer les "," en "." dans les nombres décimaux
                    // et supprime les espaces
                    break;
                case 'periode' :
                    die();
                    $matricule = substr($data[1], strpos($data[1], '_') + 1);
                    $type = substr($data[2], strpos($data[2], '_') + 1);
                    if (!(isset($listeCotesPeriode[$matricule]))) {
                        $listeCotesPeriode[$matricule][$type] = array('sit' => '', 'maxForm' => '');
                    }
                    $listeCotesPeriode[$matricule][$type] = $value;
                    break;
                case 'attitudes' :
                    // on retient les attitudes
                    $matricule = substr($data[1], strpos($data[1], '_') + 1);
                    $attitude = $data[2];
                    $listeAttitudes[$matricule][$attitude] = $value;
                    break;
                case 'commentaire' :
                    // on retient les commentaires
                    $matricule = substr($data[1], strpos($data[1], '_') + 1);
                    $listeCommentaires[$matricule] = $value;
                    break;
                case 'sitDelibe' :
                    $matricule = substr($data[1], strpos($data[1], '_') + 1);
                    $attributDelibe = $post['attributDelibe-matricule_'.$matricule];
                    $listeSituations[$matricule] = array('sitDelibe' => $value, 'attributDelibe' => $attributDelibe);
                    break;
                case 'choixProf' :
                    $matricule = substr($data[1], strpos($data[1], '_') + 1);
                    $attributProf = $post['attributProf-matricule_'.$matricule];
                    $listeChoixProf[$matricule] = array('choixProf' => $value, 'attributProf' => $attributProf);
                    break;
                default :
                    // on passe, ce champ n'est pas significatif
                    break;
            }
        }

        return array('cotes' => $listeCotesParCompetences,
                     'attitudes' => $listeAttitudes,
                     'commentaires' => $listeCommentaires,
                     'choixProf' => $listeChoixProf,
                     'sitDelibe' => $listeSituations,
                );
    }

    /**
     * enregistrement des données provenant de la page d'encodage du bulletin
     * $dataBulletin est un tableau structuré contenant les informations à enregistrer
     * $bulletin est le numéro du bulletin.
     *
     * @param $dataBulletin
     * @parame $bulletin
     *
     * @return array : une table des erreurs dans les encodages des cotes
     */
    public function enregistrerBulletin($dataBulletin, $coursGrp, $bulletin)
    {
        $tableErreurs = array();
        $texteLicite = explode(',', COTEABS);
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        foreach ($dataBulletin as $table => $data) {
            switch ($table) {
                case 'cotes' :
                    foreach ($data as $matricule => $competences) {
                        foreach ($competences as $idComp => $lesCotes) {

                            // vérification des données du "formatif"
                            $form = (isset($lesCotes['form'])) ? strtoupper(trim($lesCotes['form'])) : null;
                            $maxForm = (isset($lesCotes['maxForm'])) ? strtoupper(trim($lesCotes['maxForm'])) : null;
                            if (is_numeric($form)) {
                                if (is_numeric($maxForm)) {
                                    $formOK = ($form <= $maxForm) ? true : false;
                                }
                            } elseif ($form == '') {
                                $formOK = true;
                            } else {
                                $formOK = (in_array($form, $texteLicite));
                            }

                            if ($formOK) {
                                $boutSql = "form='$form', maxForm='$maxForm'";
                                $sql = 'INSERT INTO '.PFX.'bullDetailsCotes SET ';
                                $sql .= "matricule='$matricule', coursGrp='$coursGrp', bulletin='$bulletin', ";
                                $sql .= "idComp='$idComp', form='$form', maxForm='$maxForm' ";
                                $sql .= "ON DUPLICATE KEY UPDATE form='$form', maxForm='$maxForm'";
                                $resultat = $connexion->exec($sql);
                            } else {
                                $tableErreurs[$matricule][$idComp] = true;
                            }

                            // vérification des données du Certificatif
                            $cert = (isset($lesCotes['cert'])) ? strtoupper(trim($lesCotes['cert'])) : null;
                            $maxCert = (isset($lesCotes['maxCert'])) ? strtoupper(trim($lesCotes['maxCert'])) : null;
                            if (is_numeric($cert)) {
                                if (is_numeric($maxCert)) {
                                    $certOK = ($cert <= $maxCert);
                                } else {
                                    $certOK = false;
                                }
                            } elseif ($cert == '') {
                                $certOK = true;
                            } else {
                                $certOK = (in_array($cert, $texteLicite));
                            }

                            if ($certOK) {
                                $boutSql = "cert='$cert', maxCert='$maxCert'";
                                $sql = 'INSERT INTO '.PFX.'bullDetailsCotes SET ';
                                $sql .= "matricule='$matricule', coursGrp='$coursGrp', bulletin='$bulletin', ";
                                $sql .= "idComp='$idComp', cert='$cert', maxCert='$maxCert' ";
                                $sql .= "ON DUPLICATE KEY UPDATE cert='$cert', maxCert='$maxCert'";

                                $resultat = $connexion->exec($sql);
                            } else {
                                $tableErreurs[$matricule][$idComp] = true;
                            }
                        }
                    }
                    break;
                case 'attitudes' :
                    $lesAttitudes = array('att1', 'att2', 'att3', 'att4');
                    foreach ($data as $matricule => $attitudes) {
                        foreach ($attitudes as $noAttitude => $value) {
                            // vérifier que l'attitude figure parmi celles qui sont attendues
                            if (in_array($noAttitude, $lesAttitudes)) {
                                $boutSql = "$noAttitude='$value'";
                                $sql = 'INSERT INTO '.PFX.'bullAttitudes ';
                                $sql .= "SET matricule='$matricule', coursGrp='$coursGrp', ";
                                $sql .= "bulletin='$bulletin', ".$boutSql.' ';
                                $sql .= 'ON DUPLICATE KEY UPDATE '.$boutSql;
                                $resultat = $connexion->exec($sql);
                            } else {
                                die("out of range $noAttitude");
                            }
                        }
                    }
                    break;
                case 'commentaires' :
                    // rubrique "commentaire" du prof pour le cours
                    foreach ($data as $matricule => $commentaire) {
                        $commentaire = addslashes($commentaire);
                        $sql = 'INSERT INTO '.PFX.'bullCommentProfs ';
                        $sql .= "SET matricule='$matricule', coursGrp='$coursGrp', ";
                        $sql .= "bulletin='$bulletin', commentaire = '$commentaire' ";
                        $sql .= "ON DUPLICATE KEY UPDATE commentaire = '$commentaire'";
                        $resultat = $connexion->exec($sql);
                    }
                    break;
                case 'choixProf' :
                    // cotes de périodes pondérées choisie par le titulaire du cours
                    foreach ($data as $matricule => $situation) {
                        $sitProf = $this->sansVirg($situation['choixProf']);
                        $attributProf = $situation['attributProf'];

                        $boutSql = "choixProf='$sitProf', attributProf='$attributProf' ";
                        $sql = 'INSERT INTO '.PFX.'bullSituations ';
                        $sql .= "SET matricule='$matricule', coursGrp='$coursGrp', ";
                        $sql .= "bulletin='$bulletin', $boutSql ";
                        $sql .= "ON DUPLICATE KEY UPDATE $boutSql ";
                        $resultat = $connexion->exec($sql);
                    }
                    break;
                case 'sitDelibe':
                    // cote de délibération pour la période
                    foreach ($data as $matricule => $situation) {
                        $sitDelibe = $this->sansVirg($situation['sitDelibe']);
                        $attributDelibe = $situation['attributDelibe'];

                        $boutSql = "sitDelibe='$sitDelibe', attributDelibe='$attributDelibe' ";
                        $sql = 'INSERT INTO '.PFX.'bullSituations ';
                        $sql .= "SET matricule='$matricule', coursGrp='$coursGrp', ";
                        $sql .= "bulletin='$bulletin', $boutSql ";
                        $sql .= "ON DUPLICATE KEY UPDATE $boutSql ";
                        $resultat = $connexion->exec($sql);

                        if (($sitDelibe < 0) || ($sitDelibe > 100)) {
                            $tableErreurs[$matricule]['sitDelibe'] = true;
                        }
                    }
                    break;
                default :
                    die('data error');
            }
        }
        Application::DeconnexionPDO($connexion);

        return $tableErreurs;
    }

    /**
     * Récapitulatif des cotes par compétence et des cotes globales
     * pour les élèves de la liste donnée, pour le cours donné et
     * pour un bulletin donné.
     *
     * @param $$listeEleves
     * @param $listeCompetences
     * @param $coursGrp
     * @param $bulletin
     *
     * @return array
     */
    public function recapCotesCours($listeEleves, $listeCompetences, $coursGrp, $bulletin)
    {
        $listeCotes = self::listeCotes($listeEleves, $coursGrp, $listeCompetences, $bulletin);
        $ponderation = self::getPonderations($coursGrp, $bulletin);
        $cotesGlobales = self::listeSommesFormCert($listeCotes);
        foreach ($listeEleves as $matricule => $unEleve) {
            // on prend le premier élément de listeCotes qui contient les cotes
            // pour le cours
            $serieCotes = isset($listeCotes[$matricule]) ? $listeCotes[$matricule] : null;
            // s'il y a des cotes, on les traite
            if ($serieCotes) {
                $listeEleves[$matricule]['cotes'] = $serieCotes;
                foreach ($serieCotes as $idComp => $cotes) {
                    $listeEleves[$matricule]['cotes'][$idComp] = $cotes;
                }
            }
            // s'il y a des cotes globales pour la période, on les ajoute
            if (isset($cotesGlobales[$matricule])) {
                $listeEleves[$matricule]['globalPeriode'] = $cotesGlobales[$matricule];
            } else {
                $listeEleves[$matricule]['globalPeriode'] = null;
            }
        }

        return $listeEleves;
    }

    /**
     * liste de tous les cours qui se donnent à une liste d'élèves
     * sans tenir compte des cours-groupes éventuels.
     *
     * @param $listeEleves
     * @param $highStatus : liste des statuts de cours de haut niveau ('FC','OB') -> permet d'écarter les cours de Rem, AC,... Utile pour les synthèses générales
     *
     * @return array
     */
    public function listeCoursEleves($listeEleves, $highStatus = false)
    {
        if (is_array($listeEleves)) {
            $listeMatricules = implode(',', array_keys($listeEleves));
        } else {
            $listeMatricules = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT DISTINCT coursGrp, SUBSTR(coursGrp, 1,LOCATE('-',coursGrp)-1) AS cours, matricule, ";
        $sql .= 'libelle, nbheures, statut, section ';
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX."cours.cours = SUBSTR(coursGrp, 1,LOCATE('-',coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= "WHERE matricule IN ($listeMatricules) ";
        // si l'on veut négliger les cours moins importants
        if ($highStatus) {
            $sql .= "AND statut IN ('FC','OB') ";
        }
        $sql .= 'ORDER BY nbheures DESC, libelle ';

        $resultat = $connexion->query($sql);
        $listeCours = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['cours'];
                $matricule = $ligne['matricule'];
                $listeCours[$cours][$matricule] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCours;
    }

    /**
     * retourne la liste des coursGrp actuels d'une liste d'élèves dont on fournit les matricules
     *
     * @param int $listeMatricules
     *
     * @return array
     */
    public function listeCoursGrpActuelsEleve($listeMatricules) {
        if (is_array($listeMatricules)) {
            $listeMatricules = implode(',', array_keys($listeMatricules));
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT elCours.coursGrp, matricule, cours, libelle, nbheures, ';
        $sql .= 'statut.statut, section, rang, nom, prenom, pCours.acronyme ';
        $sql .= 'FROM '.PFX.'elevesCours AS elCours ';
        $sql .= 'JOIN '.PFX.'cours AS cours ON cours.cours = SUBSTR(coursGrp, 1,LOCATE("-",coursGrp)-1) ';
        $sql .= 'JOIN '.PFX.'statutCours AS statut ON statut.cadre = cours.cadre ';
        // LEFT JOIN pour les cas où un élève aurait été affecté à un cours qui n'existe plus dans la table des profs
        $sql .= 'LEFT JOIN '.PFX.'profsCours AS pCours ON pCours.coursGrp = elCours.coursGrp ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS profs ON profs.acronyme = pCours.acronyme ';
        $sql .= 'WHERE matricule IN ('.$listeMatricules.') ';
        $sql .= 'ORDER BY statut DESC, nbheures DESC, rang, libelle ';
        $requete = $connexion->prepare($sql);

        $resultat = $requete->execute();
        $listeCours = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $matricule = $ligne['matricule'];
                $coursGrp = $ligne['coursGrp'];
                $listeCours[$matricule][$coursGrp] = $ligne;
            }
        }

        Application::DeconnexionPDO($connexion);

        return $listeCours;
    }

    /**
     * renvoie la liste des cours qui ont été ajoutés un jour (même s'ils ont été retirés ensuite)
     * pour la liste d'élèves fournie en argument
     *
     * @param int|array $listeMatricules
     *
     * @return array
     */
    public function listeCoursAddHistorique($listeMatricules) {
        if (is_array($listeMatricules)) {
            $listeMatricules = implode(',', array_keys($listeMatricules));
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT histo.coursGrp, matricule, mouvement,  bulletin, statut.statut, ';
        $sql .= 'cours, libelle, nbheures, rang, section, rang, nom, prenom, pCours.acronyme ';
        $sql .= 'FROM '.PFX.'bullHistoCours AS histo ';
        $sql .= 'JOIN '.PFX.'profsCours AS pCours ON pCours.coursGrp = histo.coursGrp ';
        $sql .= 'JOIN '.PFX.'cours AS cours ON cours.cours = SUBSTR(pCours.coursGrp, 1, LOCATE("-", pCours.coursGrp)-1) ';
        $sql .= 'JOIN '.PFX.'statutCours AS statut ON statut.cadre = cours.cadre ';
        $sql .= 'JOIN '.PFX.'profs AS profs ON profs.acronyme = pCours.acronyme ';
        $sql .= 'WHERE matricule IN ('.$listeMatricules.') ';
        $requete = $connexion->prepare($sql);

        $resultat = $requete->execute();
        $listeCours = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $matricule = $ligne['matricule'];
                $coursGrp = $ligne['coursGrp'];
                $listeCours[$matricule][$coursGrp] = $ligne;
            }
        }

        Application::DeconnexionPDO($connexion);

        return $listeCours;
    }


    /**
     * Établissement de la liste des coursGrp des élèves passés en argument
     * la liste est valable pour le bulletin donné et tient compte de l'historique des mouvements.
     *
     * @param string|array $listeEleves : liste des élèves concernés
     * @param int          $bulletin    : numéro du bulletin à considérer (important pour l'historique)
     *
     * @return array : liste des cours suivis par la liste des élèves à la période $bulletin
     */
    public function listeCoursGrpEleves($listeMatricules, $bulletin)
    {
        $listeCours = $this->listeCoursGrpActuelsEleve($listeMatricules);
        // if (is_array($listeMatricules)) {
        //     $listeMatricules = implode(',', array_keys($listeMatricules));
        // }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        // $sql = 'SELECT DISTINCT '.PFX.'elevesCours.coursGrp, cours, libelle, nbheures, ';
        // $sql .= PFX.'statutCours.statut, section, rang, matricule, nom, prenom, '.PFX.'profsCours.acronyme ';
        // $sql .= 'FROM '.PFX.'elevesCours ';
        // $sql .= 'JOIN '.PFX.'cours ON ('.PFX."cours.cours = SUBSTR(coursGrp, 1,LOCATE('-',coursGrp)-1)) ";
        // $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        // // LEFT JOIN pour les cas où un élève aurait été affecté à un cours qui n'existe plus dans la table des profs
        // $sql .= 'LEFT JOIN '.PFX.'profsCours ON ('.PFX.'profsCours.coursGrp = '.PFX.'elevesCours.coursGrp) ';
        // $sql .= 'LEFT JOIN '.PFX.'profs ON ('.PFX.'profs.acronyme = '.PFX.'profsCours.acronyme) ';
        // $sql .= "WHERE matricule IN ($listeMatricules) ";
        // $sql .= 'ORDER BY statut DESC, nbheures DESC, rang, libelle ';
        //
        // $resultat = $connexion->query($sql);
        // $listeCours = array();
        // if ($resultat) {
        //     $resultat->setFetchMode(PDO::FETCH_ASSOC);
        //     while ($ligne = $resultat->fetch()) {
        //         $matricule = $ligne['matricule'];
        //         $coursGrp = $ligne['coursGrp'];
        //         $listeCours[$matricule][$coursGrp] = $ligne;
        //     }
        // }

        // tenir compte de l'historique
        $sql = 'SELECT matricule, '.PFX.'bullHistoCours.coursGrp, mouvement,  bulletin, '.PFX.'statutCours.statut, ';
        $sql .= 'cours, libelle, nbheures, rang, section, rang, nom, prenom, '.PFX.'profsCours.acronyme ';
        $sql .= 'FROM '.PFX.'bullHistoCours ';
        $sql .= 'JOIN '.PFX.'profsCours ON ('.PFX.'profsCours.coursGrp = '.PFX.'bullHistoCours.coursGrp) ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX.'cours.cours = SUBSTR('.PFX."profsCours.coursGrp, 1, LOCATE('-',".PFX.'profsCours.coursGrp)-1)) ';
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= 'JOIN '.PFX.'profs ON ('.PFX.'profs.acronyme = '.PFX.'profsCours.acronyme) ';
        $sql .= "WHERE matricule IN ($listeMatricules)";

        $resultat = $connexion->query($sql);
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $coursGrp = $ligne['coursGrp'];
                $mouvement = $ligne['mouvement'];
                $depuis = $ligne['bulletin'];
                // si le $bulletin est antérieur à la période où a eu lieu le mouvement
                if ($bulletin < $depuis) {
                    // et qu'il s'agit d'un ajout
                    if ($mouvement == 'ajout') {
                        // il faut supprimer le cours qui a donc été ajouté ultérieurement
                        unset($listeCours[$matricule][$coursGrp]);
                    }
                        // sinon, c'est une suppression qui a été effectuée plus tard, et il faut rétablir ce cours à cet élève
                        else {
                            $listeCours[$matricule][$coursGrp] = array(
                            'coursGrp' => $coursGrp,                    'cours' => $ligne['cours'],
                            'libelle' => $ligne['libelle'],            'nbheures' => $ligne['nbheures'],
                            'statut' => $ligne['statut'],                'section' => $ligne['section'],
                            'rang' => $ligne['rang'],                'matricule' => $matricule,
                            'nom' => $ligne['nom'],                    'prenom' => $ligne['prenom'],
                            'acronyme' => $ligne['acronyme'],
                            );
                        }
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCours;
    }

    /**
     * liste de tous les coursGrp ayant été affectés à un élève, y compris les cours supprimés dans l'historique
     * cette liste peut servir pour établir une liste cohérente des situations tout au long de l'année
     * une case est prévue pour tous les cours pour toutes les périodes, même si l'élève n'a pas/plus ce cours.
     *
     * @param $listeEleves : liste des élèves concernés
     *
     * @return array
     */
    public function listeFullCoursGrpActuel($listeEleves)
    {
        $listeCours = $this->listeCoursGrpActuelsEleve($listeEleves);
        if (is_array($listeEleves)) {
            $listeMatricules = implode(',', array_keys($listeEleves));
        } else {
            $listeMatricules = $listeEleves;
        }
        // // liste des cours figurant officiellement dans la liste actuelle des cours des élèves concernés
        // $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        // $sql = 'SELECT DISTINCT ec.coursGrp, cours, libelle, nbheures, sc.statut, section, rang, matricule, nom, prenom, pc.acronyme ';
        // $sql .= 'FROM '.PFX.'elevesCours AS ec ';
        // $sql .= 'JOIN '.PFX."cours AS c ON (c.cours = SUBSTR(ec.coursGrp, 1,LOCATE('-',ec.coursGrp)-1)) ";
        // $sql .= 'JOIN '.PFX.'statutCours AS sc ON (sc.cadre = c.cadre) ';
        // $sql .= 'JOIN '.PFX.'profsCours AS pc ON (pc.coursGrp = ec.coursGrp) ';
        // $sql .= 'JOIN '.PFX.'profs AS p ON (p.acronyme = pc.acronyme) ';
        // $sql .= "WHERE matricule IN ($listeMatricules) ";
        // $sql .= 'ORDER BY statut DESC, nbheures DESC, rang, libelle';
        //
        // $resultat = $connexion->query($sql);
        // $listeCours = array();
        // if ($resultat) {
        //     $resultat->setFetchMode(PDO::FETCH_ASSOC);
        //     while ($ligne = $resultat->fetch()) {
        //         $matricule = $ligne['matricule'];
        //         $coursGrp = $ligne['coursGrp'];
        //         $listeCours[$matricule][$coursGrp] = $ligne;
        //     }
        // }

        // on ajoute tous les cours qui ont été ajoutés un jour... (même s'ils ont été supprimés ensuite)
        $sql = 'SELECT '.PFX.'bullHistoCours.coursGrp, acronyme, nom, prenom, cours, libelle, nbheures, statut, section, rang ';
        $sql .= 'FROM '.PFX.'bullHistoCours ';
        $sql .= 'JOIN '.PFX.'profsCours ON ('.PFX.'profsCours.coursGrp = '.PFX.'bullHistoCours.coursGrp) ';
        $sql .= 'JOIN '.PFX.'profs ON ('.PFX.'profsCours.acronyme = '.PFX.'profs.acronyme) ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX.'cours.cours = SUBSTR('.PFX."bullHistoCours.coursGrp, 1, LOCATE('-',".PFX.'bullHistoCours.coursGrp)-1)) ';
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= "WHERE matricule IN ($listeMatricules) AND mouvement = 'ajout' ";
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $coursGrp = $ligne['coursGrp'];
                $listeCours[$matricule][$coursGrp] = array(
                            'coursGrp' => $coursGrp,                    'cours' => $ligne['cours'],
                            'libelle' => $ligne['libelle'],            'nbheures' => $ligne['nbheures'],
                            'statut' => $ligne['statut'],                'section' => $ligne['section'],
                            'rang' => $ligne['rang'],                'matricule' => $matricule,
                            'nom' => $ligne['nom'],                    'prenom' => $ligne['prenom'],
                            'acronyme' => $ligne['acronyme'],
                            );
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCours;
    }

    /**
     * recherche de la situation pour le bulletin actuel sur base de la liste de l'ensemble des situations
     * de toutes les périodes de cours ($listeSituations).
     *
     * @param array $listeSituations : liste détaillée des situations de toutes les périodes
     * @param $bulletin : numéro du bulletin pour lequel on souhaite la liste des situations
     *
     * @return array : la liste des situations par élève et par cours
     */
    public function situationsPeriode($listeSituations, $bulletin)
    {
        $situations = array();
        foreach ($listeSituations as $matricule => $dataCours) {
            foreach ($dataCours as $coursGrp => $dataSit) {
                // s'il y a un max, il doit y avoir autre chose; hope so...

                if (isset($dataSit[$bulletin]['max'])) {
                    $cotesPeriode = $dataSit[$bulletin];
                    $sit = $this->sansVirg(trim($cotesPeriode['sit']));
                    $sit = ($sit > 50) ? round($sit, 0) : round($sit, 1);    // l'arrondi qui va bien
                    $max = $this->sansVirg($cotesPeriode['max']);
                    $pourcent = $cotesPeriode['pourcent'];
                    $sitDelibe = isset($cotesPeriode['sitDelibe']) ? $cotesPeriode['sitDelibe'] : null;
                    $sitDelibe = $this->sansVirg(trim($sitDelibe, '*[]²'));
                    $attributDelibe = $cotesPeriode['attributDelibe'];
                    if ($attributDelibe == 'hook') {
                        $sitDelibe = '['.$sitDelibe.']';
                    }
                    $symbole = self::attribut2Symbole($attributDelibe);
                    $situations[$matricule][$coursGrp] = array(
                        'sit' => $sit,
                        'maxSit' => $max,
                        'pourcent' => $pourcent,
                        'sitDelibe' => $sitDelibe,
                        'symbole' => $symbole,
                        );
                }
            }
        }

        return $situations;
    }

    /***
     * retourne le cours correspondant au coursGrp passé en argument
     * @param $coursGrp
     */
    public function coursDeCoursGrp($coursGrp)
    {
        return substr($coursGrp, 0, strpos($coursGrp, '-'));
    }

    /**
     * retourne la mention accordée par le conseil de classe pour une période donnée
     * à une liste d'élèves donnée.
     *
     * @param $matricule
     * @param $periode
     * @param $annee
     * @param $anscol // année scolaire pour laquelle on souhaite les mentions
     *
     * @return array
     */
    public function listeMentions($listeEleves, $periode = null, $annee = null, $anscol = null)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, mention, periode, annee, anscol ';
        $sql .= 'FROM '.PFX.'bullMentions ';
        $sql .= "WHERE matricule IN ($listeElevesString) ";
        if ($periode != null) {
            $sql .= "AND periode = '$periode' ";
        }
        if ($annee != null) {
            $sql .= "AND annee = '$annee' ";
        }
        if ($anscol != null) {
            $sql .= "AND anscol='$anscol' ";
        }
        $resultat = $connexion->query($sql);
        $listeMentions = array();
        while ($ligne = $resultat->fetch()) {
            $matricule = $ligne['matricule'];
            $annee = $ligne['annee'];
            $anScol = $ligne['anscol'];
            $mention = $ligne['mention'];
            $periode = $ligne['periode'];
            $listeMentions[$matricule][$anScol][$annee][$periode] = $mention;
        }
        Application::DeconnexionPDO($connexion);

        return $listeMentions;
    }

        /**
         * retourne la liste des commentaires "educ" par bulletin et par educ
         * pour la liste d'élèves donnée
         *
         * @param  array $listeEleves : liste des élèves concernés
         * @param int $bulletin : numéro du bulletin (éventuellement)
         * @return array liste des commentaires
         */
        public function listeCommentairesEduc($listeEleves, $bulletin = Null) {
            if (is_array($listeEleves)) {
                $listeElevesString = implode(',', array_keys($listeEleves));
            } else {
                $listeElevesString = $listeEleves;
            }
            $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'SELECT matricule, bulletin, fiche, commentaire, dbe.acronyme, nom, prenom, sexe, titre ';
            $sql .= 'FROM '.PFX.'bullEducs AS dbe ';
            $sql .= 'JOIN '.PFX.'profs AS dp ON dp.acronyme = dbe.acronyme ';
            $sql .= 'WHERE matricule in ('.$listeElevesString.') ';
            if ($bulletin != Null)
                $sql .= 'AND bulletin=:bulletin ';

            $requete = $connexion->prepare($sql);
            if ($bulletin != Null)
                $requete->bindParam(':bulletin', $bulletin, PDO::PARAM_INT);

            $resultat = $requete->execute();
            $liste = array();
            if ($resultat) {
                $requete->setFetchMode(PDO::FETCH_ASSOC);
                while ($ligne = $requete->fetch()) {
                    $matricule = $ligne['matricule'];
                    $bulletin = $ligne['bulletin'];
                    $acronyme = $ligne['acronyme'];
                    $commentaire = trim($ligne['commentaire']);
                    if ($commentaire != '')
                        $liste[$matricule][$bulletin][$acronyme] = $ligne;
                }
            }

            Application::DeconnexionPDO($connexion);

            return $liste;
        }

    /**
     * retourne un tableau des 4 attitudes pour tous les élèves de la liste
     * pour tous les cours de l'élève pour le bulletin donné
     * si pas de bulletin passé, on prend tous les bulletins.
     *
     * @param string | array $listeEleves : matricule ou liste de matricules d'élèves concernés
     * @parame integer $bulletin : numéro du bulletin à imprimer
     *
     * @return array
     */
    public function tableauxAttitudes($listeEleves, $bulletin = null)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT bulletin, matricule, coursGrp, att1, att2, att3, att4 ';
        $sql .= 'FROM '.PFX.'bullAttitudes ';
        $sql .= "WHERE matricule IN ($listeElevesString) ";
        if ($bulletin != null) {
            $sql .= "AND bulletin = '$bulletin' ";
        }
        $sql .= 'ORDER BY bulletin';

        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
        }
        $tableauAttitudes = array();
        while ($ligne = $resultat->fetch()) {
            $matricule = $ligne['matricule'];
            $coursGrp = $ligne['coursGrp'];
            $ligne['cours'] = substr($coursGrp, 0, strpos($coursGrp, '-'));
            $bulletin = $ligne['bulletin'];
            $tableauAttitudes[$bulletin][$matricule][$coursGrp] = $ligne;
        }
        Application::DeconnexionPDO($connexion);

        return $tableauAttitudes;
    }

    /*
     * function noteDirection
     * @param $$annee, $bulletin
     *
     * retourne les notes de la direction ou des coordinateurs à indiquer au bulletin donné
     * dans l'année d'étude donnée
     */
    public function noteDirection($annee, $bulletin)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT remarque FROM '.PFX.'bullNotesDirection ';
        $sql .= "WHERE annee='$annee' AND bulletin='$bulletin'";
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
        }
        $listeNotes = array();
        $ligne = $resultat->fetch();
        $listeNotes = $ligne['remarque'];
        Application::DeconnexionPDO($connexion);

        return $listeNotes;
    }
    
      /**
     * renvoie les informations de remarques sur le parcours scolaire pour un élève
     * dont on founit le matricule pour l'année d'étude en cours (si précisé)
     * ou pour toutes les années scolaires
     *
     * @param int $matricule
     * @param int $anScol : année scolaire en cours
     *
     * @return array : pour chaque année scolaire, la notice "parcours"
     */
    public function getNoticesParcours($matricule, $annee){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT parcours, annee ';
        $sql .= 'FROM '.PFX.'bullParcours ';
        $sql .= 'WHERE matricule = :matricule ';
        if ($annee != Null) {
            $sql .= 'AND annee = :annee ';
            $sql .= 'ORDER BY annee DESC ';
        }
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':annee', $annee, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            while ($ligne = $requete->fetch()) {
                $annee = $ligne['annee'];
                $liste[$annee] = $ligne['parcours'];
            }
        }

        Application::deconnexionPDO($connexion);

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
     * @return array : liste des images créées
     */
    public function imagesPngBranches($hauteur)
    {
        $listeBranches = Ecole::listeCours(Ecole::listeNiveaux());
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

    /*
     * function enregistrerPonderations
     * @param $post : formulaire à décoder
     * @return integer : nombre de pondérations enregistrées
     *
     * enregistrement des informations de pondérations passées depuis le forumlaire
     */
    public function enregistrerPonderations($post, $nbPeriodes)
    {
        $listeCoursGrp = $post['listeCoursGrp'];
        $nbResultats = 0;
        if (count($listeCoursGrp) > 0) {
            $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'INSERT INTO '.PFX.'bullPonderations ';
            $sql .= "SET coursGrp=:coursGrp, periode=:periode, matricule='all', form=:form, cert=:cert ";
            $sql .= 'ON DUPLICATE KEY UPDATE form=:form, cert=:cert ';
            $requete = $connexion->prepare($sql);
            for ($periode = 1;$periode <= $nbPeriodes; ++$periode) {
                $champForm = "formatif_$periode";
                $form = $post[$champForm];
                $champCertif = "certif_$periode";
                $cert = $post[$champCertif];
                foreach ($listeCoursGrp as $coursGrp) {
                    $resultat = $requete->execute(array(
                                ':coursGrp' => $coursGrp,
                                ':periode' => $periode,
                                ':form' => $form,
                                ':cert' => $cert, )
                                );
                    if ($resultat) {
                        $nbResultats++;
                    }
                }
            }
            Application::DeconnexionPDO($connexion);
        }

        return $nbResultats;
    }

    /***
     * retourne la liste des cours pour la liste de niveaux donnée
     * @param $listeNiveaux
     *
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
        $sql .= "WHERE SUBSTR(cours, 1,1) IN ($listeNiveauxString)";
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
      * retourne la liste des cours réellemnent suivis par des élèves à un certain niveau d'études.
      *
      * @param $niveau
      *
      * @return array
      */
     public function listeCoursSuivisNiveau($niveau)
     {
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         $sql = 'SELECT DISTINCT coursGrp, libelle, cours, nbheures ';
         $sql .= 'FROM '.PFX.'elevesCours ';
         $sql .= 'JOIN '.PFX."cours AS dc ON (dc.cours = SUBSTR(coursGrp,1,LOCATE('-',coursGrp)-1)) ";
         $sql .= "WHERE SUBSTR(coursGrp,1,1) = '$niveau' ";
         $sql .= 'ORDER BY libelle, coursGrp ';
         $resultat = $connexion->query($sql);
         $liste = array();
         if ($resultat) {
             $resultat->setFetchMode(PDO::FETCH_ASSOC);
             while ($ligne = $resultat->fetch()) {
                 $cours = $ligne['cours'];
                 $liste[$cours] = $ligne;
             }
         }
         Application::DeconnexionPDO($connexion);

         return $liste;
     }

    /***
     * enregistrement des compétences provenant du formulaire "adminCompetences.tpl"
     * @param $post
     * @return integer : nombre de modifications dans la BD
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
                $sql = 'DELETE FROM '.PFX.'bullCompetences ';
                $sql .= "WHERE id='$idComp'";
                $resultat += $connexion->exec($sql);
            } else {
                $ordre = $data['ordre'];
                $libelle = $data['libelle'];
                $sql = 'UPDATE '.PFX.'bullCompetences ';
                $sql .= "SET ordre='$ordre', libelle='$libelle' ";
                $sql .= "WHERE id = '$idComp'";
                $resultat += $connexion->exec($sql);
            }
        }
        foreach ($dataNew as $libelle) {
            $libelle = addslashes($libelle);
            if ($libelle != '') {
                $sql = 'INSERT INTO '.PFX.'bullCompetences ';
                $sql .= "SET libelle='$libelle', cours='$cours'";
                $resultat += $connexion->exec($sql);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /***
     * retourne le code "cadre" qui indique le statut des cours
     * cette fonction permet le lien entre le code "cadre" et les abréviations FC, OC, OB, OG, ...
     * @param
     */
    public function listeStatutCours()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT cadre, statut, rang, legende ';
        $sql .= 'FROM '.PFX.'statutCours ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cadre = $ligne['cadre'];
                $liste[$cadre] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /*
     * function listOrphanCoursGrp
     * @param
     *
     * retourne la liste des cours sans élève et sans professeur
     */
    public function listOrphanCoursGrp()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        // liste des cours associés à des élèves
        $sql = "SELECT DISTINCT SUBSTR(coursGrp, 1, LOCATE('-',coursGrp)-1) AS cours ";
        $sql .= 'FROM '.PFX.'elevesCours ORDER BY cours';
        $resultat = $connexion->query($sql);
        $listeElevesCours = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['cours'];
                $listeElevesCours[$cours] = $ligne;
            }
        }

        // liste des cours associés à des profs
        $sql = "SELECT DISTINCT SUBSTR(coursGrp, 1, LOCATE('-',coursGrp)-1) AS cours ";
        $sql .= 'FROM '.PFX.'profsCours ORDER BY cours';
        $resultat = $connexion->query($sql);
        $listeProfsCours = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['cours'];
                $listeProfsCours[$cours] = $ligne;
            }
        }

        // liste de tous les cours existants dans la table des cours
        $sql = 'SELECT cours, libelle, statut, nbheures ';
        $sql .= 'FROM '.PFX.'cours ';
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= 'ORDER BY libelle, cours';
        $resultat = $connexion->query($sql);
        $listeCours = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['cours'];
                $listeCours[$cours] = $ligne;
            }
        }

        // sélection de tous les cours qui ne sont ni dans la liste des élèves ni dans la liste des profs
        $listeOrphanCours = array_diff_key($listeCours, $listeElevesCours, $listeProfsCours);

        return $listeOrphanCours;
    }

    /**
     * supprime la liste des cours passés en argument de la table des cours
     * ces cours sont, en principe, orphelins (pas d'élève, pas de prof).
     *
     * @param array/string $listeOrphans
     *
     * @return int : nombre de suppressions de la BD
     */
    public function deleteOrphanCours($listeOrphans)
    {
        if (is_array($listeOrphans)) {
            $listeOrphansString = "'".implode("','", $listeOrphans)."'";
        } else {
            $listeOrphansString = "'".$listeOrphans."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'cours ';
        $sql .= "WHERE cours IN ($listeOrphansString) ";
        $resultat = $connexion->exec($sql);
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * retourne une synthèse de toutes les cotes de l'élève pour toutes les années d'étude
     * sauf si l'année est précisée.
     *
     * @param $matricule : le matricule de l'élève concerné
     * @param $annee : l'année d'étude (en principe, entre 1 et 6, si nécessaire)
     *
     * @return array
     */
    public function syntheseToutesAnnees($matricule, $annee = null)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT annee AS anScolaire, SUBSTR(cours,1,1) as annee, bulletin, statut, '.PFX.'bullSitArchives.coursGrp, situation, maxSituation, ';
        $sql .= 'round(situation*100/maxSituation) as pourcent, sitDelibe, cours, nbheures, libelle, attribut ';
        $sql .= 'FROM '.PFX.'bullSitArchives ';
        $sql .= 'LEFT JOIN '.PFX.'cours ON ('.PFX."cours.cours = SUBSTR(coursGrp, 1, LOCATE('-', coursGrp)-1)) ";
        $sql .= 'LEFT JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= "WHERE matricule = '$matricule' ";
        if ($annee != null) {
            $sql .= "AND SUBSTR(cours,1,1)='$annee' ";
        }
        $sql .= 'ORDER BY anScolaire DESC, annee DESC, bulletin, statut DESC, nbheures DESC ';

        $synthese = array();
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $anScolaire = $ligne['anScolaire'];
                $bulletin = $ligne['bulletin'];
                $annee = $ligne['annee'];
                $sitDelibe = $ligne['sitDelibe'];
                if (($sitDelibe != '') && ($sitDelibe < 50)) {
                    $ligne['echec'] = 'echec';
                }
                $pourcent = $ligne['pourcent'];
                $ligne['mention'] = $this->calculeMention($pourcent);
                $pourcent = ($pourcent == null) ? '' : $pourcent.'%';
                $ligne['pourcent'] = $pourcent;
                $attribut = $ligne['attribut'];
                $coursGrp = $ligne['coursGrp'];
                $synthese[$anScolaire][$annee]['resultats'][$bulletin][$coursGrp] = $ligne;
            }
        }

        // recherche de la liste de tous les cours qui figurent dans la synthèse
        foreach ($synthese as $anScolaire => $dataAnScol) {
            $listeCours = array();
            foreach ($dataAnScol as $annee => $data) {
                foreach ($synthese[$anScolaire][$annee]['resultats'] as $periode => $details) {
                    foreach ($details as $unCoursGrp => $details) {
                        // on ne reprend pas les cours pour lesquels il n'y a pas de cote de situation...
                        // => pas de colonne vide pour les élèves qui ont changé de cours avant le bulletin 1
                        if ($details['situation'] != '') {
                            $coursGrp = $details['coursGrp'];
                            if (!isset($listeCours[$coursGrp])) {
                                $listeCours[$coursGrp] = array(
                                    'cours' => $details['cours'],
                                    'libelle' => $details['libelle'],
                                    'nbheures' => $details['nbheures'],
                                    'statut' => $details['statut'],
                                    );
                            }
                        }
                    }
                }
                $synthese[$anScolaire][$annee]['listeCours'] = $listeCours;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $synthese;
    }

    /**
     * tableau des situations et des délibés pour l'année scolaire en cours
     * ce tableau est établi sur la base de la liste déclarée des cours suivis ou ayant été suivis par l'élève
     * puis par recherche de chacun de ces cours dans la table des bullSituation.
     *
     * @param $listeCoursGrp
     *
     * @return array : liste des situations pour chaque période
     */
    public function syntheseAnneeEnCours($listeCoursActuelle, $matricule)
    {
        $stringListeCoursActuelle = "'".implode("','", array_keys($listeCoursActuelle))."'";
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT coursGrp, bulletin, situation, maxSituation, sitDelibe, attribut, SUBSTR(coursGrp,1,LOCATE('-', coursGrp)-1) as cours, ";
        $sql .= 'SUBSTR(cours,1,1) as annee, statut, situation, maxSituation, sitDelibe, nbheures, libelle ';
        $sql .= 'FROM '.PFX.'bullSituations ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX."cours.cours = SUBSTR(coursGrp,1,LOCATE('-', coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= "WHERE matricule = '$matricule' ";
        $sql .= "AND coursGrp IN ($stringListeCoursActuelle) ";
        $sql .= 'ORDER BY bulletin';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $bulletin = $ligne['bulletin'];
                $annee = $ligne['annee'];
                $sitDelibe = $ligne['sitDelibe'];
                $attribut = $ligne['attribut'];
                $situation = $this->sansVirg($ligne['situation']);
                $maxSituation = $this->sansVirg($ligne['maxSituation']);
                $pourcent = ($maxSituation != 0) ? round(100 * $situation / $maxSituation, 0) : null;
                $ligne['mention'] = $this->calculeMention($pourcent);
                $pourcent = ($pourcent == null) ? '' : $pourcent.'%';
                $ligne['pourcent'] = $pourcent;
                if ($sitDelibe != '') {
                    if ($sitDelibe < 50) {
                        $ligne['echec'] = 'echec';
                    }
                }
                $coursGrp = $ligne['coursGrp'];
                $liste[$bulletin][$coursGrp] = $ligne;
            }
            Application::DeconnexionPDO($connexion);

            return $liste;
        }
    }

    /**
     * retourne les remarques du titulaire
     * pour une liste d'élèves donnée et
     * pour un  bulletin donné.
     *
     * @param $listeEleves
     * @param $bulletin
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
        $sql .= 'FROM '.PFX.'bullTitus ';
        $sql .= "WHERE matricule IN ($listeElevesString) ";
        if ($bulletin != null) {
            $sql .= "AND bulletin='$bulletin'";
        }
        $resultat = $connexion->query($sql);
        $listeRemarques = array();
        while ($ligne = $resultat->fetch()) {
            $matricule = $ligne['matricule'];
            $bulletin = $ligne['bulletin'];
            $listeRemarques[$matricule][$bulletin] = $ligne['remarque'];
        }
        Application::DeconnexionPDO($connexion);

        return $listeRemarques;
    }

    /**
     * enregistrement de la remarque du titulaire pour l'élève dont le matricule est indiqué, pour le bulletin donné.
     *
     * @param $commentaire
     * @param $matricule
     * @param $bulletin
     *
     * @return $resultat : nombre d'enregistrements réussis (normalement, 1)
     */
    public function enregistrerRemarque($remarque, $matricule, $bulletin)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $remarque = addslashes(self::corrigeWord($remarque));
        $sql = 'INSERT INTO '.PFX."bullTitus SET remarque = '$remarque',";
        $sql .= "matricule ='$matricule', bulletin='$bulletin' ";
        $sql .= "ON DUPLICATE KEY UPDATE remarque = '$remarque'";
        $resultat = $connexion->exec($sql);
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /***
     * retourne la liste des classes en détaillant par degré et par année
     * @param
     * @return array
     */
    public function listeStructClasses()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT groupe ';
        $sql .= 'FROM '.PFX.'eleves ';
        $sql .= 'ORDER BY groupe';
        $resultat = $connexion->query($sql);
        $liste = array();
        while ($ligne = $resultat->fetch()) {
            $classe = $ligne['groupe'];
            $annee = substr($classe, 0, 1);
            if (in_array($annee, range(1, 2))) {
                $degre = 1;
            } elseif (in_array($annee, range(3, 4))) {
                $degre = 2;
            } elseif (in_array($annee, range(5, 6))) {
                $degre = 3;
            }
            $liste[$degre][$annee][] = $classe;
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

     /**
      * retourne la liste des cotes de situation sit/max par cours pour la liste d'élèves indiquée.
      *
      * @param $bulletin : le bulletin concerné
      * @param $listeEleves : liste des matricules des élèves
      *
      * @return array
      */
     public function getSituations($bulletin, $listeEleves)
     {
         if (is_array($listeEleves)) {
             $listeElevesString = implode(',', array_keys($listeEleves));
         } else {
             $listeElevesString = $listeEleves;
         }
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         $sql = 'SELECT matricule, coursGrp, cours, situation, maxSituation ';
         $sql .= 'FROM '.PFX.'bullSituations ';
         $sql .= 'JOIN '.PFX."cours ON (SUBSTR(coursGrp,1,LOCATE('-',coursGrp)-1) = ".PFX.'cours.cours) ';
         $sql .= "WHERE matricule IN ($listeElevesString) AND bulletin = '$bulletin' ";

         $resultat = $connexion->query($sql);
         $listeSituations = array();
         while ($ligne = $resultat->fetch()) {
             $matricule = $ligne['matricule'];
             $cours = $ligne['cours'];
             $coursGrp = $ligne['coursGrp'];
             $sit = $this->sansVirg($ligne['situation']);
             $max = $this->sansVirg($ligne['maxSituation']);
             $listeSituations[$matricule][$cours] = array('coursGrp' => $coursGrp, 'sit' => $sit, 'max' => $max);
         }
         Application::DeconnexionPDO($connexion);

         return $listeSituations;
     }

    /***
     * Calcule la mention obetnue par un élève selon les règles en vigueur à l'ISND
     * @param $moyenne exprimée sur 100
     * return $string : mention obtenue
     */
     public function calculeMention($moyenne)
     {
         if (is_numeric($moyenne)) {
             $moyenneEntiere = intval($moyenne / 10);
             switch ($moyenneEntiere) {
                case 10: $mention = 'E'; break;
                case 9: $mention = 'E'; break;
                case 8: if ($moyenne >= 85) {
     $mention = 'TB+';
 } else {
                        $mention = 'TB';
                    } break;
                case 7: if ($moyenne >= 75) {
     $mention = 'B+';
 } else {
                        $mention = 'B';
                    } break;
                case 6: if ($moyenne >= 65) {
     $mention = 'AB';
 } else {
                        $mention = 'S';
                    } break;
                case 5: $mention = 'F'; break;
                default: $mention = 'I';
                }
         } else {
             $mention = '';
         }

         return $mention;
     }

    /**
     * retourne les situations sur 100 basées sur les cotes de situation calculées
     * pour le bulletin n° $bulletin
     * et pour les élèves dont la liste est fournie.
     *
     * @param $bulletin
     * @param $eleves
     *
     * @return array : coursGrp, sit100, mention
     */
    public function getSituations100($bulletin, $listeEleves)
    {
        $listeSituations = $this->getSituations($bulletin, $listeEleves);
        $listeSituations100 = array();
        foreach ($listeSituations as $matricule => $listeCours) {
            foreach ($listeCours as $cours => $cotes) {
                $coursGrp = $cotes['coursGrp'];
                $sit = $cotes['sit'];
                $max = $cotes['max'];
                if (($max != '') && ($sit != '')) {
                    $sit100 = round(100 * $sit / $max);
                    // suppression des + et -
                    $mention = trim($this->calculeMention($sit100), '+-');
                } else {
                    $sit100 = '';
                    $mention = '';
                }
                $listeSituations100[$matricule][$cours] = array('coursGrp' => $coursGrp, 'sit100' => $sit100, 'mention' => $mention);
            }
        }

        return $listeSituations100;
    }

    /*
     *
     * CARNET DE COTES  CARNET DE COTES
     * CARNET DE COTES  CARNET DE COTES
     * CARNET DE COTES  CARNET DE COTES
     *
     * */

    /**
     * retourne la liste de tous les travaux cotés pour le coursGrp et le bulletin indiqués
     *
     * @param string $coursGrp
     * @param int $bulletin
     *
     * @return array
     */
    public function listeTravaux($coursGrp, $bulletin)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCarnet, coursGrp, cc.libelle, date, max, idComp, formCert, bulletin, ';
        $sql .= 'remarque, neutralise, ordre ';
        $sql .= 'FROM '.PFX.'bullCarnetCotes as cc ';
        $sql .= 'JOIN '.PFX.'bullCompetences as c ON (c.id = cc.idComp) ';
        $sql .= "WHERE coursGrp = '$coursGrp' AND bulletin = '$bulletin' ";
        $sql .= 'ORDER BY formCert, date, ordre ';
        $listeTravaux = array();
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $idCarnet = $ligne['idCarnet'];
                $ligne['date'] = $this->datePHPglue($ligne['date'], '/');
                $listeTravaux[$idCarnet] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeTravaux;
    }

    /**
     * retroune la liste de toutes les cotes dans le carnet de cotes pour la liste des cours donnée
     * pour l'élève dont on fournit le matricule
     *
     * @param array|string $listeCoursGrp
     * @param int $matricule
     *
     * @return array
     */
    public function getCotes4listeCoursGrp($listeCoursGrp, $matricule) {
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode('\',\'', array_keys($listeCoursGrp))."'";
        } else {
            $listeCoursGrpString = $listeCoursGrp;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT cote, max, coursGrp, dbcc.libelle, dbcp.libelle AS competence, date, formCert, bulletin, publie ';
        $sql .= 'FROM '.PFX.'bullCarnetCotes AS dbcc ';
        $sql .= 'JOIN '.PFX.'bullCarnetEleves AS dbce ON dbce.idCarnet = dbcc.idCarnet ';
        $sql .= 'JOIN '.PFX.'bullCompetences AS dbcp ON dbcp.id = dbcc.idComp ';
        $sql .= 'WHERE matricule = :matricule AND coursGrp IN ('.$listeCoursGrpString.') AND publie = 1 AND neutralise = 0 ';
        $sql .= 'ORDER BY bulletin, date ';

        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $bulletin = $ligne['bulletin'];
                $ligne['date'] = Application::datePHP($ligne['date']);
                if (($ligne['max'] > 0) && ($ligne['cote'] != '') && (!in_array($ligne['cote'], explode(',', COTEABS)))) {
                    $ligne['echec'] = ($ligne['cote'] / $ligne['max']) < 0.5;
                } else {
                    $ligne['echec'] = false;
                }
                $liste[$coursGrp][$bulletin][] = $ligne;
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * conversion d'une date au format MySQL vers un format usuel
     * lors de la conversion, le symbole de séparation des nombres peut être choisi ($glue).
     *
     * @param string $dateMysql
     * @param char   $glue
     *
     * @return string
     */
    public function datePHPglue($dateMysql, $glue)
    {
        $dateArray = explode('-', $dateMysql);
        $phpArray = array_reverse($dateArray);
        $date = implode($glue, $phpArray);

        return $date;
    }

    /**
     * convertit une date contenant le séparateur $glue en date au format MySQL.
     *
     * @param $date : la date à convertir (Exemple: 07/10/2014)
     * @param $glue : le liant utilisé dans la date (dans l'exemple, le caractère "/")
     */
    public function dateMysqlGlue($date, $glue)
    {
        $dateArray = explode($glue, $date);
        $sqlArray = array_reverse($dateArray);
        $date = implode('-', $sqlArray);

        return $date;
    }

    /**
     * retourne les listes de cotes correspondant à
     * la liste des travaux passée en argument.
     *
     * @param $listeTravaux
     *
     * @return array
     */
    public function listeCotesCarnet($listeTravaux)
    {
        $listeTravauxString = implode(',', array_keys($listeTravaux));
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT '.PFX.'bullCarnetCotes.idCarnet, matricule, ';
        $sql .= 'cote, '.PFX.'bullCarnetCotes.remarque, '.PFX.'bullCarnetCotes.neutralise, max ';
        $sql .= 'FROM '.PFX.'bullCarnetEleves ';
        $sql .= 'JOIN '.PFX.'bullCarnetCotes ON ('.PFX.'bullCarnetCotes.idCarnet = '.PFX.'bullCarnetEleves.idCarnet) ';
        $sql .= 'WHERE '.PFX."bullCarnetCotes.idCarnet in ($listeTravauxString) ";
        $sql .= 'ORDER BY '.PFX.'bullCarnetCotes.idCarnet';
        $resultat = $connexion->query($sql);
        $listeCotes = array();
        while ($ligne = $resultat->fetch()) {
            $matricule = $ligne['matricule'];
            $idCarnet = $ligne['idCarnet'];
            $cote = $ligne['cote'];
            $trimCote = trim($cote, '!');
            $remarque = $ligne['remarque'];
            $neutralise = $ligne['neutralise'];
            $max = $ligne['max'];
            if (is_numeric($this->sansVirg($trimCote))) {
                $erreur = (($max < $trimCote) || ($trimCote < 0));
            } else {
                $erreur = false;
            }
            if (($max > 0) && ($cote != '') && (!in_array($cote, explode(',', COTEABS)))) {
                $echec = ($cote / $max) < 0.5;
            } else {
                $echec = false;
            }
            $listeCotes[$matricule][$idCarnet] = array(
                                'cote' => $cote,
                                'remarque' => $remarque,
                                'neutralise' => $ligne['neutralise'],
                                'erreurEncodage' => $erreur,
                                'max' => $max,
                                'echec' => $echec,
                                );
        }
        Application::DeconnexionPDO($connexion);

        return $listeCotes;
    }

     /**
      * retourne la liste des moyennes des cotes correspondant à la liste des cotes
      * passée en paramètre.
      *
      * @param $listeCotesCarnet
      *
      * @return array
      */
     public function listeMoyennesCarnet($listeCotesCarnet)
     {
         $moyennes = array();
         $sommes = array();
         if ($listeCotesCarnet) {
             foreach ($listeCotesCarnet as $matricule => $listeCotes) {
                 foreach ($listeCotes as $noCarnet => $data) {
                     // on additionne pour la moyenne
                    // s'il y a une cote et que ce n'est pas une mention d'absence (constante COTEABS)
                    if (($data['cote'] != '') && (!in_array($data['cote'], explode(',', COTEABS)))) {
                        if (isset($sommes[$noCarnet])) {
                            $sommes[$noCarnet]['total'] += $data['cote'];
                            ++$sommes[$noCarnet]['nbCotes'];
                        } else {
                            $sommes[$noCarnet]['total'] = $data['cote'];
                            $sommes[$noCarnet]['nbCotes'] = 1;
                        }
                    }
                 }
             }
             if ($sommes) {
                 foreach ($sommes as $noCarnet => $data) {
                     if ($sommes[$noCarnet]['nbCotes'] > 0) {
                         $moyennes[$noCarnet] = $sommes[$noCarnet]['total'] / $sommes[$noCarnet]['nbCotes'];
                     } else {
                         $moyennes[$noCarnet] = '';
                     }
                 }
             }
         }

         return $moyennes;
     }


    /**
     * retourne un tableau de la liste des poids des compétences
     * pour le certificatif et le formatif
     * par période.
     *
     * @param $coursGrp
     * @param $listeCompetences
     *
     * @return array
     */
    public function listePoidsCompetences($coursGrp, $listeCompetences)
    {
        $listeCompetencesString = implode(',', array_keys($listeCompetences));
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT bulletin, certForm, poids, idComp ';
        $sql .= 'FROM '.PFX.'bullCarnetPoidsCompetences ';
        $sql .= "WHERE coursGrp = '$coursGrp' AND idComp IN ($listeCompetencesString) ";
        $sql .= 'ORDER BY bulletin, idComp';
        $resultat = $connexion->query($sql);
        $listePoids = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $bulletin = $ligne['bulletin'];
                $certForm = $ligne['certForm'];
                $idComp = $ligne['idComp'];
                $poids = $ligne['poids'];
                $listePoids[$bulletin][$idComp][$certForm] = $poids;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listePoids;
    }


    /**
     * retourne la liste des colonnes de cotes à additionner
     * en séparant par compétence et form ou cert.
     *
     * @param coursGrp
     * @param bulletin
     *
     * @return array
     */
    public function colonnesCotesBulletin($coursGrp, $bulletin)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCarnet, idComp, formCert, max, '.PFX.'bullCompetences.libelle ';
        $sql .= 'FROM '.PFX.'bullCarnetCotes ';
        $sql .= 'JOIN '.PFX.'bullCompetences ON ('.PFX.'bullCompetences.id = '.PFX.'bullCarnetCotes.idComp) ';
        $sql .= "WHERE coursGrp = '$coursGrp' AND bulletin = $bulletin AND neutralise = 0";

        $resultat = $connexion->query($sql);
        $listeEvaluations = array();
        while ($ligne = $resultat->fetch()) {
            $idCarnet = $ligne['idCarnet'];
            $idComp = $ligne['idComp'];
            $formCert = $ligne['formCert'];
            $max = $ligne['max'];
            $libelle = $ligne['libelle'];
            $listeEvaluations[$idCarnet] = array('idComp' => $idComp, 'formCert' => $formCert, 'max' => $max, 'libelle' => $libelle);
        }
        Application::DeconnexionPDO($connexion);

        return $listeEvaluations;
    }

    /**
     * retourne l'entête de la liste des colonnes de cotes à additionner
     * en séparant par compétence et form ou cert.
     *
     * @param coursGrp
     * @param bulletin
     *
     * @return array
     */
    public function listeCompetencesBulletin($coursGrp, $bulletin)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCarnet, idComp, formCert, max, '.PFX.'bullCompetences.libelle ';
        $sql .= 'FROM '.PFX.'bullCarnetCotes ';
        $sql .= 'JOIN '.PFX.'bullCompetences ON ('.PFX.'bullCompetences.id = '.PFX.'bullCarnetCotes.idComp) ';
        $sql .= "WHERE coursGrp = '$coursGrp' AND bulletin = $bulletin AND neutralise = 0 ";
        $sql .= 'ORDER BY formCert, ordre';
        $resultat = $connexion->query($sql);
        $listeEvaluations = array();
        while ($ligne = $resultat->fetch()) {
            // $idCarnet = $ligne['idCarnet'];
            $idComp = $ligne['idComp'];
            $formCert = $ligne['formCert'];
            $max = $ligne['max'];
            $libelle = $ligne['libelle'];
            $listeEvaluations[$idComp][$formCert] = array('libelle' => $libelle, 'max' => $max);
        }
        Application::DeconnexionPDO($connexion);

        return $listeEvaluations;
    }

    /**
     * renvoie un tableau de toutes les cotes dont les entêtes sonf passées
     * dans $listeColonnes.
     *
     * @param $listeColonnes
     *
     * @return array
     */
    public function listeCotesCompFormCert($listeColonnes)
    {
        $listeIdCarnetString = implode(',', array_keys($listeColonnes));
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCarnet, matricule, cote FROM '.PFX.'bullCarnetEleves ';
        $sql .= "WHERE idCarnet IN ($listeIdCarnetString) ";
        $sql .= 'ORDER BY idCarnet';
        $resultat = $connexion->query($sql);
        $listeCotes = array();
        while ($ligne = $resultat->fetch()) {
            $idCarnet = $ligne['idCarnet'];
            $matricule = $ligne['matricule'];
            $cote = $ligne['cote'];
            $max = $listeColonnes[$idCarnet]['max'];
            $idComp = $listeColonnes[$idCarnet]['idComp'];
            $formCert = $listeColonnes[$idCarnet]['formCert'];
            $libelle = $listeColonnes[$idCarnet]['libelle'];
            $listeCotes[$matricule][$idComp][$formCert][] = array('cote' => $cote, 'max' => $max, 'libelle' => $libelle);
        }
        Application::DeconnexionPDO($connexion);

        return $listeCotes;
    }

    /**
     * vérifie qu'une cote passée en argument vaut pour "0"
     * la liste des cotes valant pour "0" se trouve dans la constante COTENULLE.
     *
     * @param $cote
     *
     * @return bool
     */
    private function estCoteNulle($cote)
    {
        return in_array(strtolower($cote), explode(',', COTENULLE));
    }

    /**
     * Vérifie qu'une conte passée en argument est licite
     * il faut qu'elle soit numérique et non vide
     * ou qu'elle soit équivalent à un "0" (cote nulle).
     *
     * @param $cote
     *
     * @return bool
     * */
    private function estLicite($cote)
    {
        return (is_numeric($cote) && ($cote != '')) || (self::estCoteNulle($cote));
    }


    /**
     * calculs des cotes moyennes, des nombres d'heures d'échec, des nombres d'échecs, de la mention
     * pour chaque élève d'une classe.
     *
     * @param $listeSituations
     *
     * @return array
     */
    public function echecMoyennesDecisions($listeSituations)
    {
        $liste = array();
        foreach ($listeSituations as $matricule => $dataCours) {
            $liste[$matricule] = array();
            $total = 0;
            $nbCours = 0;
            $nbEchecs = 0;
            $listeCoursEchec = 0;
            $nbCoursEchec = 0;
            $nbHeuresEchec = 0;
            $coursEchecs = array();
            $moyenne = '';
            $mention = '';
            foreach ($dataCours as $cours => $infos) {
                $sitDelibe = trim($infos['sitDelibe']);
                $attributDelibe = $infos['attributDelibe'];
                $echec = $infos['echec'];
                if (($attributDelibe != 'hook') && ($sitDelibe != '')) {
                    $total += $sitDelibe;
                    ++$nbCours;
                    if (($echec == 'echec') && ($infos['statut'] != 'AC')) {
                        $libelle = $infos['libelle'];
                        $nbHeuresEchec += $infos['nbheures'];
                        ++$nbEchecs;
                        $coursEchecs[] = $libelle;
                    }
                }
            }

            if ($nbCours != 0) {
                $moyenne = round($total / $nbCours, 1);
                $mention = self::calculeMention($moyenne);
            }
            $liste[$matricule] = array(
                        'total' => $total, 'nbCours' => $nbCours, 'moyenne' => $moyenne, 'mention' => $mention,
                        'nbEchecs' => $nbEchecs, 'nbHeuresEchec' => $nbHeuresEchec, 'coursEchec' => implode('<br>', $coursEchecs),
                        );
        }

        return $liste;
    }

    /**
     * calcul des moyennes des situations sur la base d'une liste des situations dans chaque cours.
     *
     * @param array $listeSituations
     * @param array $listePeriodes
     *
     * @return array
     */
    public function moyennesSituations($listeSituations, $listePeriodes)
    {
        foreach ($listePeriodes as $periode) {
            $data[$periode] = array('somme' => 0, 'nbCours' => 0, 'nbEchecs' => 0, 'nbHeuresEchec' => 0, 'cours' => array(), 'moyenne' => '', 'attribut' => '');
        }
        // somme des situations
        if ($listeSituations) {
            foreach ($listeSituations as $coursGrp => $lesSituations) {
                foreach ($listePeriodes as $periode) {
                    // si la période existe déjà dans la BD
                    if (isset($lesSituations[$periode])) {
                        $sit = $this->sansVirg(trim($lesSituations[$periode]['sitDelibe']));
                        $attribut = $lesSituations[$periode];

                        // si l'information est numérique, on en tient compte
                        if (is_numeric($sit)) {
                            // les cotes entre crochets sont négligées
                            if ($lesSituations[$periode]['attributDelibe'] != 'hook') {
                                $data[$periode]['somme'] += $sit;
                                ++$data[$periode]['nbCours'];
                                // les AC ne comptent pas comme échec
                                if (($sit < 50) && (($lesSituations[$periode]['statut'] != 'AC'))) {
                                    ++$data[$periode]['nbEchecs'];
                                    $data[$periode]['nbHeuresEchec'] += $lesSituations[$periode]['nbheures'];
                                    $data[$periode]['cours'][] = $lesSituations[$periode]['libelle'];
                                }
                            }
                        }
                    }
                }
            }
            foreach ($listePeriodes as $periode) {
                if (isset($data[$periode])) {
                    if ($data[$periode]['nbCours'] > 0) {
                        $data[$periode]['moyenne'] = round($this->sansVirg($data[$periode]['somme']) / $data[$periode]['nbCours'], 1);
                    } else {
                        $data[$periode]['moyenne'] = '';
                    }
                    $data[$periode]['cours'] = implode(', ', $data[$periode]['cours']);
                }
            }
        }

        return $data;
    }


    // --------------------------------------------------------------------
    // retourne la notice "coordinateurs" pour le bulletin donné au niveau donné
    public function noticeCoordinateurs($bulletin, $annee)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT remarque FROM '.PFX.'bullNotesDirection ';
        $sql .= "WHERE bulletin='$bulletin' AND SUBSTR(annee,1,1) = $annee ";
        $notice = '';
        $resultat = $connexion->query($sql);
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $notice = $ligne['remarque'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $notice;
    }

    /**
     * retourne la liste des élèves d'un niveau donné (de la 1e à la 6e) pour une année scolaire indiquée.
     *
     * @param $annee string : annee scolaire passée (Ex: 2012-2013)
     * @param $niveau integer : niveau d'étude
     *
     * @return array : liste des élèves
     */
    public function listeElevesArchives($annee, $niveau)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT lematricule, nomPrenom, classe ';
        $sql .= 'FROM '.PFX.'bullArchives ';
        $sql .= "WHERE annee = '$annee' AND SUBSTR(classe,1,1) = '$niveau' ";
        $resultat = $connexion->query($sql);
        $listeEleves = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['lematricule'];
                $nomPrenom = $ligne['nomPrenom'];
                $listeEleves[$matricule] = $nomPrenom;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeEleves;
    }

    /**
     * retourne la liste des fiches disciplinaires pour la liste d'élèves fournie
     *
     * @param  array $listeEleves
     *
     * @return array
     */

    public function listeNotesFiches($listeEleves)
    {
        $listeElevesString = implode(', ', array_keys($listeEleves));
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, fiche, bulletin ';
        $sql .= 'FROM '.PFX.'bullEducs ';
        $sql .= "WHERE matricule IN ($listeElevesString)";

        $resultat = $connexion->query($sql);
        $liste = array();
        while ($ligne = $resultat->fetch()) {
            $matricule = $ligne['matricule'];
            $bulletin = $ligne[$bulletin];
            $liste[$bulletin]['$matricule'] = $ligne['fiche'];
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    public function mergeElevesDiscNotes($listeEleves, $listeFaitsDisc, $listeNotesFiches)
    {
        foreach ($listeEleves as $matricule => $data) {
            $listeEleves[$matricule]['disc'] = isset($listeFaitsDisc[$matricule]['disc']) ? $listeFaitsDisc[$matricule]['disc'] : null;
            $listeEleves[$matricule]['fiche'] = isset($listeNotesFiches[$matricule]) ? $listeNotesFiches[$matricule] : null;
        }

        return $listeEleves;
    }

    public function selectEleveFromPageDisc($classe, $date1, $date2)
    {
        $date1 = $this->dateMysqlGlue($date1, '-');
        $date2 = dateMysqlGlue($date2, '-');
        $listeEleves = Ecole::listeEleves($classe, 'groupe');
        $listeNotesFiches = $this->listeNotesFiches($listeEleves);
        $listeFaitsDisc = $this->listeFaitsDisc($listeEleves, $date1, $date2);
        $listeElevesDisc = $this->mergeElevesDiscNotes($listeEleves, $listeFaitsDisc, $listeNotesFiches);

        return $listeElevesDisc;
    }

     /**
      * retourne les totalisation  des cotes par élèves, per cours et par compétence
      * pour l'ensemble de l'année scolaire.
      *
      * @param $listeEleves: tableau dont les keys sont les $matricule
      * @param $listeCours: tableau de la liste des cours dont les keys sont les abréviations des cours
      * @param $listeCompetences: liste des compétences existantes pour ce cours
      *
      * @return tableau de la somme des cotes par élève, par cours et par compétence
      */
     public function sommeToutesCotes($listeEleves, $listeCours, $listeCompetences)
     {
         $listeElevesString = implode(',', array_keys($listeEleves));
         if (is_array($listeCours)) {
             $listeCoursString = "'".implode('\',\'', array_keys($listeCours))."'";
         } else {
             $listeCoursString = $listeCours;
         }
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         $sql = "SELECT matricule, coursGrp, idComp, form, maxForm, cert, maxCert, SUBSTR(coursGrp,1,LOCATE('-',coursGrp)-1) AS cours ";
         $sql .= 'FROM '.PFX.'bullDetailsCotes ';
         $sql .= "WHERE (matricule IN ($listeElevesString)) AND (SUBSTR(coursGrp,1,LOCATE('-',coursGrp)-1) IN ($listeCoursString)) ";
         $sql .= 'ORDER BY matricule, idComp';

         $resultat = $connexion->query($sql);
         $listeCotes = array();
         while ($ligne = $resultat->fetch()) {
             $matricule = $ligne['matricule'];
             $cours = $ligne['cours'];
             $idComp = $ligne['idComp'];
             $form = isset($ligne['form']) ? $this->sansVirg(trim($ligne['form'])) : null;
             $maxForm = isset($ligne['maxForm']) ? $this->sansVirg(trim($ligne['maxForm'])) : null;
             $cert = isset($ligne['cert']) ? $this->sansVirg(trim($ligne['cert'])) : null;
             $maxCert = isset($ligne['maxCert']) ? $this->sansVirg(trim($ligne['maxCert'])) : null;
             if (isset($form)) {
                 if (isset($listeCotes[$matricule][$cours][$idComp]['cote'])) {
                     $listeCotes[$matricule][$cours][$idComp]['cote'] += $form;
                 } else {
                     $listeCotes[$matricule][$cours][$idComp]['cote'] = $form;
                 }
                 if (isset($listeCotes[$matricule][$cours][$idComp]['max'])) {
                     $listeCotes[$matricule][$cours][$idComp]['max'] += $maxForm;
                 } else {
                     $listeCotes[$matricule][$cours][$idComp]['max'] = $maxForm;
                 }
             }
             if (isset($cert)) {
                 if (isset($listeCotes[$matricule][$cours][$idComp]['cote'])) {
                     $listeCotes[$matricule][$cours][$idComp]['cote'] += $cert;
                 } else {
                     $listeCotes[$matricule][$cours][$idComp]['cote'] = $cert;
                 }
                 if (isset($listeCotes[$matricule][$cours][$idComp]['max'])) {
                     $listeCotes[$matricule][$cours][$idComp]['max'] += $maxCert;
                 } else {
                     $listeCotes[$matricule][$cours][$idComp]['max'] = $maxCert;
                 }
             }
         }
         Application::DeconnexionPDO($connexion);

         return $listeCotes;
     }

    public function listeAcquis($listeCotes)
    {
        foreach ($listeCotes as $matricule => $listeCours) {
            foreach ($listeCours as $cours => $listeCompetences) {
                foreach ($listeCompetences as $idComp => $evaluation) {
                    if ($evaluation['max'] > 0) {
                        if ($evaluation['cote'] / $evaluation['max'] >= 0.5) {
                            $listeCotes[$matricule][$cours][$idComp]['acq'] = 'Acquis';
                        } else {
                            $listeCotes[$matricule][$cours][$idComp]['acq'] = 'Non acquis';
                        }
                    } else {
                        unset($listeCotes[$matricule][$cours][$idComp]);
                    }
                }
            }
        }

        return $listeCotes;
    }

    /**
     * vérifie qu'au moins une compétence a reçu un poids avant transfert vers le bulletin.
     *
     * @param $listePoidsCompetences
     *
     * @return bool
     */
    public function poidsCompetencesVide($listePoidsCompetences)
    {
        $vide = true;
        foreach ($listePoidsCompetences as $idComp => $poids) {
            $vide = $vide && (trim($poids['cert']) == '') && (trim($poids['form']) == '');
        }

        return $vide;
    }

    /**
     * retourne la liste de situations des élèves pour la liste des cours passés en argument
     * on soustrait le situation de la période 0 de celle de la période $bulletin.
     *
     * @param string|array $listeEleves    : liste des élèves
     * @param string|array $listeCoursGrp: liste des coursGrp concernés
     * @param int          $bulletin       : numéro du bulletin en cours
     *
     * @return array listeSituationsDeuxiemes
     */
    public function situationsDeuxieme($listeEleves, $listeCoursGrp, $bulletin)
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
        $sql = 'SELECT bulletin, matricule, coursGrp, situation, maxSituation ';
        $sql .= 'FROM '.PFX.'bullSituations ';
        $sql .= "WHERE bulletin BETWEEN 0 AND $bulletin AND coursGrp IN ($listeCoursGrpString) AND matricule IN ($listeElevesString) ";
        $resultat = $connexion->query($sql);
        $listeSituations = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $periode = $ligne['bulletin'];
                $coursGrp = $ligne['coursGrp'];
                $matricule = $ligne['matricule'];
                $listeSituations[$coursGrp][$matricule][$periode] = array(
                    'sit' => $ligne['situation'],
                    'max' => $ligne['maxSituation'],
                    );
            }
        }
        foreach ($listeSituations as $coursGrp => $eleves) {
            foreach ($eleves as $matricule => $cotes) {
                if (isset($cotes[$bulletin]) && $this->isNumericNotNull($cotes[$bulletin]['sit']) && isset($cotes[0]) && $this->isNumericNotNull($cotes[0]['sit'])) {
                    // cote initiale
                $sit0 = $cotes[0]['sit'];
                    $max0 = $cotes[0]['max'];
                // cote jusqu'à la période actuelle $bulletin
                $sitPeriode = $cotes[$bulletin]['sit'];
                    $maxPeriode = $cotes[$bulletin]['max'];
                // on soustrait la période 0 (report de l'année précédente) de la situation totale du degré
                $sit2 = $sitPeriode - $sit0;
                    $max2 = $maxPeriode - $max0;
                    $listeSituations[$coursGrp][$matricule]['sit2'] = array('sit' => $sit2, 'max' => $max2);
                    if ($max2 > 0) { // pas de division par zéro
                    $listeSituations[$coursGrp][$matricule]['sit2']['pourcent'] = round(100 * $sit2 / $max2);
                    }
                } else {
                    $listeSituations[$coursGrp][$matricule]['sit2']['pourcent'] = null;
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeSituations;
    }

    /**
     * rédaction du bulletin d'un élève.
     *
     * @param array  $dataEleve : tableau contenant $matricule, $annee, $degre et noms des titulaires
     * @param int    $bulletin  : numéro du bulletin
     * @param string $acronyme  : identifiant de l'utilisateur en cours
     *
     * @return lien vers le fichier PDF créé
     */
    public function createPDFeleve($dataEleve, $bulletin, $acronyme)
    {
        $matricule = $dataEleve['matricule'];
        $annee = $dataEleve['annee'];
        $degre = $dataEleve['degre'];
        $titulaires = $dataEleve['titulaires'];

        $eleve = new Eleve($matricule);
        $infoPerso = $eleve->getDetailsEleve();
        $Ecole = new Ecole();

        // liste des coursGrp tenant compte de l'historique
        $listeCoursGrp = $this->listeCoursGrpEleves($matricule, $bulletin);
        if ($listeCoursGrp) {
            // il n'y a qu'un élève, il n'y aura donc qu'une seule liste de pondérations
            $listeCoursGrp = $listeCoursGrp[$matricule];
            $listeProfsCoursGrp = $Ecole->listeProfsListeCoursGrp($listeCoursGrp);

            $listeSituations = $this->listeSituationsCours($matricule, array_keys($listeCoursGrp), null, true);
            $sitPrecedentes = $this->situationsPrecedentes($listeSituations, $bulletin);
            $sitActuelles = $this->situationsPeriode($listeSituations, $bulletin);
            $listeCompetences = $this->listeCompetencesListeCoursGrp($listeCoursGrp);
            $listeCotes = $this->listeCotes($matricule, $listeCoursGrp, $listeCompetences, $bulletin);
            $ponderations = $this->getPonderations($listeCoursGrp, $bulletin);
            $cotesPonderees = $this->listeGlobalPeriodePondere($listeCotes, $ponderations, $bulletin);
            $commentairesCotes = $this->listeCommentairesTousCours($matricule, $bulletin);
            $mention = $this->listeMentions($matricule, $bulletin, $annee);
            $ficheEduc = $this->listeFichesEduc($matricule, $bulletin);
            $remarqueTitulaire = $this->remarqueTitu($matricule, $bulletin);
            $tableauAttitudes = $degre == 1 ? $this->tableauxAttitudes($matricule, $bulletin) : null;
            $noticeDirection = $this->noteDirection($annee, $bulletin);

            $detailsBulletin = array(
                'annee' => $annee,                            'degre' => $degre,
                'titulaires' => $titulaires,                'listeCoursEleve' => $listeCoursGrp,
                'listeProfs' => $listeProfsCoursGrp,        'baremes' => $ponderations,
                'infoPerso' => $infoPerso,                    'sitPrec' => $sitPrecedentes,
                'sitActuelles' => $sitActuelles,            'detailCotes' => $listeCotes,
                'listeCompetences' => $listeCompetences,        'cotesPonderees' => $cotesPonderees,
                'listeProfs' => $listeProfsCoursGrp,        'commentairesCotes' => $commentairesCotes,
                'ficheEduc' => $ficheEduc,                    'attitudes' => $tableauAttitudes,
                'remTitu' => $remarqueTitulaire,            'mention' => $mention,
                'noticeDirection' => $noticeDirection,
                );

            $pdf = new PDF_HTML('P', 'mm', 'A4');
            $pdf->SetFillColor(230);

            $this->createBulletinEleve($pdf, $detailsBulletin, $bulletin);
            $pdf->Output("$matricule.pdf", 'D');
        }
    }

    // --------------------------------------------------------------------

    private function utf8($argument)
    {
        return utf8_decode($argument);
    }

    // --------------------------------------------------------------------

    /**
     * /!\ patch! /!\ patch! /!\ patch! /!\ patch! /!\ patch! /!\ patch! /!\ patch! /!\ patch! /!\ patch!
     * réparation du hiatus entre la table des situations et la table des cours réellement suivis par les élèves.
     *
     * @param
     *
     * @return int : nombre de rectifications
     *             /!\ patch! /!\ patch! /!\ patch! /!\ patch! /!\ patch! /!\ patch! /!\ patch! /!\ patch! /!\ patch!
     */
    public function listeErreursCours()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT '.PFX.'eleves.matricule, classe, nom, prenom, '.PFX.'elevesCours.coursGrp AS correct, '.PFX.'bullSituations.coursGrp AS incorrect ';
        $sql .= 'FROM '.PFX.'eleves ';
        $sql .= 'JOIN '.PFX.'bullSituations ON ( '.PFX.'bullSituations.matricule = '.PFX.'eleves.matricule ) ';
        $sql .= 'JOIN '.PFX.'elevesCours ON ( '.PFX.'elevesCours.matricule = '.PFX.'bullSituations.matricule ) ';
        $sql .= 'WHERE SUBSTR( '.PFX."bullSituations.coursGrp, 1, LOCATE( '-', ".PFX.'bullSituations.coursGrp ) -1 ) = SUBSTR( '.PFX."elevesCours.coursGrp, 1, LOCATE( '-', ".PFX.'elevesCours.coursGrp ) -1 ) ';
        $sql .= 'AND ('.PFX.'elevesCours.coursGrp != '.PFX.'bullSituations.coursGrp) ';
        $sql .= 'ORDER BY classe, '.PFX.'elevesCours.coursGrp, nom ';
        $resultat = $connexion->query($sql);
        $listeErreurs = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $correct = $ligne['correct'];
                $incorrect = $ligne['incorrect'];
                $listeErreurs[$matricule][$incorrect] = $correct;
            }
            $nb = 0;
            foreach ($listeErreurs as $matricule => $listeCours) {
                foreach ($listeCours as $erreur => $correct) {
                    $sql = 'UPDATE '.PFX.'bullSituations ';
                    $sql .= "SET coursGrp = '$correct' ";
                    $sql .= "WHERE coursGrp = '$erreur' AND matricule = '$matricule' ";
                    $resultat = $connexion->exec($sql);
                    $nb += $resultat;
                }
            }
            Application::DeconnexionPDO($connexion);

            return $nb;
        }
    }

    /**
     * Suppression de la virgule et remplacement par un point dans les nombres + suppression des espaces.
     *
     * @param $nombre string
     *
     * @return string
     */
    private function sansVirg($nombre)
    {
        $nombre = str_replace(',', '.', $nombre);
        $nombre = str_replace(' ', '', $nombre);

        return $nombre;
    }

    /**
     * recherche de tous les coursGrp correspondant à un $cours donné
     * la fonction retourne des array (matricule, coursGrp).
     *
     * @param string $cours
     *
     * @return array
     */
    public function elevesCoursGrpDeCours($cours)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule,coursGrp FROM '.PFX.'elevesCours ';
        $sql .= "WHERE SUBSTR(coursGrp,1,LOCATE('-',coursGrp)-1)='$cours' ";
        $sql .= 'ORDER BY coursGrp ';
        $resultat = $connexion->query($sql);
        $resultat->setFetchMode(PDO::FETCH_ASSOC);
        $elevesCoursGrp = $resultat->fetchall();
        Application::DeconnexionPDO($connexion);

        return $elevesCoursGrp;
    }

    /**
     * retourne le tableau des élèves par cours quand on fournit un tableau des élèves par coursGrp.
     *
     * @param array $listeCoursGrpListeEleves
     *
     * @return array
     */
    public function listeCoursSansGrp($listeCoursGrpListeEleves)
    {
        $liste = array();
        foreach ($listeCoursGrpListeEleves as $matricule => $listeCoursGrp) {
            foreach ($listeCoursGrp as $coursGrp => $wtf) {
                $cours = self::coursDeCoursGrp($coursGrp);
                $liste[$matricule][$cours] = '';
            }
        }

        return $liste;
    }

    /**
     * initialisation de la table des résultats d'épreuves externes
     * les enregistrements déjà existants ne sont pas écrasés!!!
     *
     * @param array $elevesCoursGrp
     *
     * @return int $nb : nombre de nouveaux enregistrements dans la table
     */
    public function initEpreuvesExternes($elevesCoursGrp, $anscol)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'bullEprExterne ';
        $sql .= "SET matricule=:matricule, coursGrp=:coursGrp, anscol='$anscol' ";
        $requete = $connexion->prepare($sql);
        $nb = 0;
        foreach ($elevesCoursGrp as $wtf => $data) {
            $matricule = $data['matricule'];
            $coursGrp = $data['coursGrp'];
            $nb += $requete->execute($data);
        }
        Application::DeconnexionPDO($connexion);

        return $nb;
    }


    /**
     * recherche la liste des cotes d'épreuve externe pour un cours-groupe donné.
     *
     * @param string $coursGrp
     *
     * @return array
     */
    public function listeCotesEprExterne($coursGrp, $anscol)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, coteExterne, choixCote, coursGrp ';
        $sql .= 'FROM '.PFX.'bullEprExterne ';
        $sql .= "WHERE coursGrp = '$coursGrp' AND anscol='$anscol' ";

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
     * retourne la liste des cotes des épreuves externes d'un élève donné
     * la liste est triée par année scolaire puis par cours.
     *
     * @param $matricule
     *
     * @return array
     */
    public function cotesExternesPrecedentes($matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT anscol, coursGrp, coteExterne ';
        $sql .= 'FROM '.PFX.'bullExterneArchives ';
        $sql .= "WHERE matricule = '$matricule' ";
        $sql .= 'ORDER BY anscol DESC ';

        $resultat = $connexion->query($sql);
        $listeCotes = array();
        while ($ligne = $resultat->fetch()) {
            $anneeScolaire = $ligne['anscol'];
            $cours = $this->coursDeCoursGrp($ligne['coursGrp']);
            $cote = $ligne['coteExterne'];
            $listeCotes[$anneeScolaire][$cours] = $cote;
        }
        Application::DeconnexionPDO($connexion);

        return $listeCotes;
    }

    /**
     * retourne la liste des cours d'un prof qui sont concernés par une épreuve externe.
     *
     * @param array $listeCoursProf
     *
     * @return array
     */
    public function listeCoursEprExterne($listeCoursProf, $anscol)
    {
        $listeCoursProf = "'".implode("','", array_keys($listeCoursProf))."'";
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT ext.coursGrp, statut, libelle, nbheures, cours, nomCours, SUBSTR(ext.coursGrp,1,1) AS annee ';
        $sql .= 'FROM '.PFX.'bullEprExterne AS ext ';
        $sql .= 'JOIN '.PFX."cours AS dc ON (dc.cours = SUBSTR(coursGrp, 1, LOCATE('-',coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'statutCours AS sc ON ( sc.cadre = dc.cadre ) ';
        $sql .= 'JOIN '.PFX.'profsCours AS pc ON pc.coursGrp = ext.coursGrp ';
        $sql .= "WHERE ext.coursGrp IN ($listeCoursProf) AND ext.anscol='$anscol' ";
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $liste[$coursGrp] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * enregistrement des cotes d'épreuves externes en provenance d'un formulaire POST.
     *
     * @param array post
     *
     * @return int nombre d'enregistrements modifiés
     */
    public function enregistrerEprExternes($post, $anscol)
    {
        $coursGrp = $post['coursGrp'];
        $sql = 'UPDATE '.PFX.'bullEprExterne ';
        $sql .= "SET coteExterne=:cote, choixCote=:choixCote, anscol='$anscol' ";
        $sql .= 'WHERE matricule=:matricule AND coursGrp=:coursGrp ';
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $requete = $connexion->prepare($sql);
        $coteabs = explode(',', COTEABS);
        $nb = 0;
        $tableErreurs = array();
        foreach ($post as $field => $value) {
            $fieldCote = explode('_', $field);

            // le nom du champ contenant la cote de l'épreuve externe commence par "cote" suivi de "_xxxxx" où xxxxx est le matricule
            if ($fieldCote[0] == 'cote') {
                $matricule = $fieldCote[1];
                $cote = strtoupper(sansVirg($value));
                $type = 'choix_'.$matricule;
                $choix = isset($post[$type]) ? $post[$type] : null;
                $erreur = false;

                // la cote externe doit être numérique et comprise entre 0 et 100
                if (is_numeric($cote) && (($cote > 100) || ($cote < 0))) {
                    $erreur = true;
                }
                // si cote d'absence, le choix ne peut être sur la cote externe
                if ((in_array($cote, $coteabs)) && ($choix == 'coteExterne')) {
                    $erreur = true;
                }
                if ($erreur == true) {
                    $tableErreurs[$matricule] = $cote;
                } else {
                    if ($choix == 'coteExterne') {
                        $cote = $cote;
                    }  // ;o)
                        // if ($choix == 'reussite') $cote = 50;  // on laisse la cote de l'épreuve externe; le 50% sera rétabli dans la feuille de délibé car choix='reussite50'
                        if ($choix == 'sitDelibe') {
                            // si la cote n'est pas une mention d'absence régulière, on l'efface
                            if (!in_array($cote, $coteabs)) {
                                $cote = '';
                            }
                        }
                    $data = array(':cote' => $cote, ':choixCote' => $choix, ':matricule' => $matricule, ':coursGrp' => $coursGrp);
                    $nb += $requete->execute($data);
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return array('nb' => $nb, 'erreurs' => $tableErreurs);
    }

    /**
     * retourne la liste liste des cotes de situation lors de la prise en compte de l'épreuve externe
     * soit la cote de l'épreuve externe, soit 50% (cas de la réussite interne), soit la cote de situation
     * pour chaque élève et pour chacun de ses cours
     * => destination: feuille de délibés par classe.
     *
     * @param array $listeSituationsActuelles
     * @param array $listeEleves
     * @param array $listeCoursGrp            : liste des coursGrp dans la classe
     *
     * @return array
     */
    //public function listeSitDelibeExternes($situationsActuelles, $listeEleves, $listeCoursGrp) {
    //	if (is_array($listeEleves))
    //		$listeElevesString = implode(',', array_keys($listeEleves));
    //	else $listeElevesString = $listeEleves;
    //	if (is_array($listeCoursGrp))
    //		$listeCoursGrpString = "'" . implode("','", array_keys($listeCoursGrp)) . "'";
    //		else $listeCoursGrpString = "'" . $listeCoursGrp . "'";
    //	$connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
    //	$sql = "SELECT matricule, coursGrp, coteExterne, choixCote ";
    //	$sql .= "FROM ".PFX."bullEprExterne ";
    //	$sql .= "WHERE matricule IN ($listeElevesString) AND coursGrp IN ($listeCoursGrpString) ";
    //
    //	$resultat = $connexion->query($sql);
    //	if ($resultat) {
    //		$resultat->setFetchMode(PDO::FETCH_ASSOC);
    //		while ($ligne = $resultat->fetch()) {
    //			$matricule = $ligne['matricule'];
    //			$coursGrp = $ligne['coursGrp'];
    //			$cours = self::coursSansGrp($coursGrp);
    //			// si une cote de délibé a été choisie pour cet élève et ce cours, on évalue l'épreuve externe
    //			if (isset($situationsActuelles[$matricule][$cours]) && trim($situationsActuelles[$matricule][$cours]['sitDelibe']) != '') {
    //				$coteExterne = trim($ligne['coteExterne']);
    //				$choixCote = $ligne['choixCote'];
    //				switch ($choixCote) {
    //					case 'coteExterne':
    //						// on retient la situation interne
    //						$situationsActuelles[$matricule][$cours]['sitInterne'] = $situationsActuelles[$matricule][$cours]['sitDelibe'];
    //						// la cote externe remplace la situation de délibé (interne)
    //						$situationsActuelles[$matricule][$cours]['sitDelibe']=$coteExterne;
    //						$situationsActuelles[$matricule][$cours]['echec']=(($coteExterne < 50)&& (trim($coteExterne) != ''))?'echec':Null;
    //						$situationsActuelles[$matricule][$cours]['attribut']='externe';
    //						if ($coteExterne != '')
    //							$situationsActuelles[$matricule][$cours]['symbole'] = self::attribut2Symbole('externe');
    //							else $situationsActuelles[$matricule][$cours]['symbole'] = '';
    //					break;
    //					case 'reussite':
    //						// on retient la situation interne
    //						$situationsActuelles[$matricule][$cours]['sitInterne'] = $situationsActuelles[$matricule][$cours]['sitDelibe'];
    //						// la cote externe remplace la situation de délibé (interne)
    //						$situationsActuelles[$matricule][$cours]['sitDelibe']=50;
    //						$situationsActuelles[$matricule][$cours]['echec']=Null;
    //						$situationsActuelles[$matricule][$cours]['attribut']='reussite50';
    //						$situationsActuelles[$matricule][$cours]['symbole']= self::attribut2Symbole('reussite50');
    //					break;
    //					case 'sitDelibe':
    //						// do nothing : la cote est le situation choisie par le titulaire du cours dans le bulletin
    //					break;
    //					}
    //				}
    //			}
    //		}
    //	Application::DeconnexionPDO($connexion);
    //	return $situationsActuelles;
    //	}
    //
    ///**
    // * retourne les cotes de situation en tenant compte des épreuves externes pour un élève donné
    // * soit la cote de l'épreuve externe, soit 50% (cas de la réussite interne), soit la cote de situation
    // * => destination: feuille de délibé individuelle par élève
    // * @param array $listeSituations : la liste de situations de délibé pour chaque cours
    // * @param integer $matricule : le matricule de l'élève
    // * @return $listeSituations
    // */
    //public function eleveSitDelibeExternes($matricule, $listeSituations) {
    //	$connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
    //	$sql = "SELECT coursGrp, coteExterne, choixCote ";
    //	$sql .= "FROM ".PFX."bullEprExterne ";
    //	$sql .= "WHERE matricule = '$matricule' ";
    //	$resultat = $connexion->query($sql);
    //	if ($resultat) {
    //		$resultat->setFetchMode(PDO::FETCH_ASSOC);
    //		while ($ligne = $resultat->fetch()) {
    //			$coursGrp = $ligne['coursGrp'];
    //			$coteExterne = $ligne['coteExterne'];
    //			$choixCote = $ligne['choixCote'];
    //			if (isset($listeSituations[$coursGrp][NBPERIODES]['sitDelibe']) && (trim($listeSituations[$coursGrp][NBPERIODES]['sitDelibe']) != '')) {
    //				switch ($choixCote) {
    //					case 'coteExterne':
    //						$listeSituations[$coursGrp][NBPERIODES]['sitInterne']=$listeSituations[$coursGrp][NBPERIODES]['sitDelibe'];
    //						$listeSituations[$coursGrp][NBPERIODES]['sitDelibe']=$coteExterne;
    //						$listeSituations[$coursGrp][NBPERIODES]['symbole']=self::attribut2Symbole('externe');
    //						$listeSituations[$coursGrp][NBPERIODES]['echec']=($coteExterne < 50)?'echec':Null;
    //					break;
    //					case 'reussite':
    //						$listeSituations[$coursGrp][NBPERIODES]['sitInterne']=$listeSituations[$coursGrp][NBPERIODES]['sitDelibe'];
    //						$listeSituations[$coursGrp][NBPERIODES]['sitDelibe']='50';
    //						$listeSituations[$coursGrp][NBPERIODES]['symbole']=self::attribut2Symbole('reussite50');
    //						$listeSituations[$coursGrp][NBPERIODES]['echec']=Null;
    //					break;
    //					case 'sitDelibe':
    //						// do nothing : la cote est le situation choisie par le titulaire du cours dans le bulletin
    //					break;
    //					}
    //				}
    //			}
    //		}
    //	Application::DeconnexionPDO($connexion);
    //	return $listeSituations;
    //	}

    /**
     * 	retourne la liste des coursGrp pour lesquels une épreuve externe est définie dans la table correspondante et pour le niveau donné.
     *
     * 	@param $niveau
     *
     * 	@return array
     */
    public function listeEprExterne($niveau)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT ee.coursGrp, cours, ds.statut, nbheures, libelle, dp.acronyme, nom, prenom ';
        $sql .= 'FROM '.PFX.'bullEprExterne AS ee ';
        $sql .= 'JOIN '.PFX."cours AS dc ON (dc.cours = SUBSTR(coursGrp,1, LOCATE('-',coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'profsCours AS dpc ON (dpc.coursGrp = ee.coursGrp) ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON (dp.acronyme = dpc.acronyme) ';
        $sql .= 'JOIN '.PFX.'statutCours AS ds ON ( ds.cadre = dc.cadre ) ';
        $sql .= "WHERE SUBSTR(ee.coursGrp,1,1) = '$niveau' ";
        $sql .= 'ORDER BY libelle, coursGrp ';

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $acronyme = $ligne['acronyme'];
                $statut = $ligne['statut'];
                $nbheures = $ligne['nbheures'];
                if (isset($liste[$coursGrp])) {
                    $liste[$coursGrp]['acronyme'] .= ",$acronyme";
                } else {
                    $liste[$coursGrp] = array(
                            'acronyme' => $acronyme,
                            'coursGrp' => $coursGrp,
                            'statut' => $statut,
                            'nbheures' => $nbheures,
                            'libelle' => $ligne['libelle'], );
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne les résultats des épreuves externes dans les branches indiquées.
     *
     * @param array $listeBranches
     *
     * @return array
     */
    public function cotesEprExternes()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT de.matricule, nom, prenom, classe, SUBSTR(coursGrp,1,LOCATE('-',coursGrp)-1) AS cours, coteExterne ";
        $sql .= 'FROM '.PFX.'eleves AS de ';
        $sql .= 'LEFT JOIN '.PFX.'bullEprExterne AS ex ON (ex.matricule = de.matricule) ';
        $sql .= "WHERE SUBSTR(classe,1,1) = '2' AND classe != '2D' ";
        $sql .= 'ORDER BY groupe, nom, prenom, cours ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                if (!isset($liste[$matricule])) {
                    $liste[$matricule]['matricule'] = $ligne['matricule'];
                    $liste[$matricule]['nom'] = $ligne['nom'];
                    $liste[$matricule]['prenom'] = $ligne['prenom'];
                    $liste[$matricule]['classe'] = $ligne['classe'];
                }
                $cours = $ligne['cours'];
                $liste[$matricule]['cotes'][] = $ligne['coteExterne'];
            }
        }
        Application::DeconnexionPDO($connexion);

        $texte = '"Matricule", "Classe","Nom","Prénom","coteFR","coteMATH","coteNL"'.chr(10);
        foreach ($liste as $matricule => $data) {
            $classe = $data['classe'];
            $nom = $data['nom'];
            $prenom = $data['prenom'];
            $cote0 = $data['cotes'][0];
            $cote1 = $data['cotes'][1];
            $cote2 = $data['cotes'][2];
            $texte .= "\"$matricule\", \"$classe\", \"$nom\", \"$prenom\", \"$cote0\",\"$cote1\", \"$cote2\"".chr(10);
        }
        if (!($fp = fopen('eprExternes.csv', 'w'))) {
            die("erreur à l'ouverture du fichier");
        }
        fwrite($fp, $texte);
        fclose($fp);

        return $liste;
    }

     /**
      * enregistrement des noms attribués aux cours en provenance du formulaire ad hoc pour l'utilisateur actuel $acronyme.
      *
      * @param array : $post
      * @param string : $acronyme
      *
      * @return int : nombre d'enregistrements réalisés
      */
     public function enregistrerNomsCours($post, $acronyme)
     {
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         $resultat = 0;
         foreach ($post as $champ => $value) {
             if (substr($champ, 0, 6) == 'field_') {
                 $coursGrp = explode('_', $champ);
                // dans le coursGrp, un espace éventuel avait été remplacé par un "~" dans le template;
                // il faut remettre l'espace
                $coursGrp = str_replace('~', ' ', $coursGrp[1]);
                 $nomCours = addslashes($value);
                 $sql = 'INSERT INTO '.PFX.'profsCours ';
                 $sql .= "SET acronyme='$acronyme', coursGrp='$coursGrp', nomCours='$nomCours' ";
                 $sql .= "ON DUPLICATE KEY UPDATE nomCours='$nomCours' ";
                 $resultat += $connexion->exec($sql);
             }
         }
         Application::DeconnexionPDO($connexion);

         return $resultat;
     }

    /**
     * Effacement de toutes les cotes d'un bulletin donné (avant transfert du carnet de cotes).
     *
     * @param $bulletin
     * @param $listeCompetences
     * @param $coursGrp
     */
    public function effaceDetailsBulletin($bulletin, $listeCompetences, $coursGrp, $listeLocks, $listeEleves)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        foreach ($listeCompetences as $idCompetence => $wtf) {
            foreach ($listeEleves as $matricule => $wtf2) {
                // on n'efface que ce qui n'est pas verrouillé
                if (isset($listeLocks[$matricule][$coursGrp]) && $listeLocks[$matricule][$coursGrp] == 0) {
                    $sql = 'UPDATE '.PFX.'bullDetailsCotes ';
                    $sql .= "SET form='', maxForm='', cert='', maxCert='' ";
                    $sql .= "WHERE idComp='$idCompetence' AND coursGrp='$coursGrp' AND matricule='$matricule' AND bulletin='$bulletin' ";
                    $resultat = $connexion->exec($sql);
                }
            }
        }
        Application::DeconnexionPDO($connexion);
    }

    /**
     * retourne le numéro du bulletin accessible pour l'élève donné.
     *
     * @param $leMatricule : le matricule de l'élève (pas $matricule qui sera modifié dans la procédure)
     *
     * @return int : le numero du dernier bulletin accessible
     */
    public function accesBulletin($matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT bulletin, matricule ';
        $sql .= 'FROM '.PFX.'thotBulletin ';
        $sql .= "WHERE matricule='$matricule' ";
        $resultat = $connexion->query($sql);
        $bulletin = 0;
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $bulletin = $ligne['bulletin'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $bulletin;
    }
}
