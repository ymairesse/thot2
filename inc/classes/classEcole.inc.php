<?php

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
/*
 * class Ecole
 */

class ecole
{
    /*
     * __construct
     * @param
     */
    public function __construct()
    {
    }

    /**
     * retourne une liste des profs ou des membres du personnel.
     *
     * @param $donneCours boolean  si "false", demande la liste de tous les membres du personnel; si "true", demande seulement les enseignants
     *
     * @return array liste de tous les profs de l'école
     */
    public function listeProfs($donneCours = false)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT * FROM '.PFX.'profs ';
        if ($donneCours) {
            $sql .= 'JOIN '.PFX.'profsCours ON ('.PFX.'profsCours.acronyme = '.PFX.'profs.acronyme) ';
            $sql .= "WHERE coursGrp != '' ";
        }
        $sql .= "ORDER BY REPLACE(REPLACE(REPLACE(nom, ' ', ''),'''',''),'-',''), prenom";
        $resultat = $connexion->query($sql);
        $listeProfs = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $acronyme = $ligne['acronyme'];
                $listeProfs[$acronyme] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeProfs;
    }

    /**
     * retourne la liste des titulaires pour chaque classe
     * tableau à éventuellement deux dimensions si plusieurs titulaires
     * dans la même classe.
     *
     * @param
     *
     * @return array
     */
    public function listeTitus()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT '.PFX."titus.acronyme,classe, CONCAT(prenom,' ',nom) AS nom ";
        $sql .= 'FROM '.PFX.'titus ';
        $sql .= 'JOIN '.PFX.'profs ON ('.PFX.'profs.acronyme = '.PFX.'titus.acronyme ) ';
        $sql .= 'ORDER BY classe, acronyme';
        $resultat = $connexion->query($sql);
        $listeTitus = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $classe = $ligne['classe'];
                $listeTitus[$classe]['acronyme'][] = $ligne['acronyme'];
                $listeTitus[$classe]['nom'][] = $ligne['nom'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeTitus;
    }

    /**
     * retourne la liste des profs titulaires avec la classe correspondante
     * par rapport à la fonction précédente, on retourne ici une liste des profs et non une liste des classes.
     *
     * @param void()
     *
     * @return array
     */
    public function listeProfsTitus()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT '.PFX.'titus.acronyme,classe, nom, prenom, mail ';
        $sql .= 'FROM '.PFX.'titus ';
        $sql .= 'JOIN '.PFX.'profs ON ('.PFX.'profs.acronyme = '.PFX.'titus.acronyme ) ';
        $sql .= 'ORDER BY classe,nom ';
        $resultat = $connexion->query($sql);
        $listeTitus = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $acronyme = $ligne['acronyme'];
                $classe = $ligne['classe'];
                $mail = $ligne['mail'];
                $listeTitus[$acronyme] = array(
                    'nom' => $ligne['nom'],
                    'prenom' => $ligne['prenom'],
                    'acronyme' => $acronyme,
                    'classe' => $ligne['classe'],
                    'mail' => $ligne['mail'],
                    );
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeTitus;
    }

    /**
     * retourne un tableau de la liste des profs titulaires d'un groupe donné
     *
     * @param string $groupe
     *
     * @return array
     */
    public static function titusDeGroupe($groupe)
    {
        if ($groupe    == null) {
            die('missing group');
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT titus.acronyme, sexe, nom, prenom ';
        $sql .= 'FROM '.PFX.'titus AS titus ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON (dp.acronyme = titus.acronyme) ';
        $sql .= 'WHERE titus.classe= :groupe ';
        $sql .= 'ORDER BY concat(nom, prenom) ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':groupe', $groupe, PDO::PARAM_STR, 7);
        $titulaires = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $acronyme = $ligne['acronyme'];
                $adresse = ($ligne['sexe'] == 'F') ? 'Mme' : 'M.';
                $nom = sprintf('%s %s. %s', $adresse, mb_substr($ligne['prenom'], 0, 1, 'UTF-8'), $ligne['nom']);
                $titulaires[$acronyme] = $nom;
            }
        }

        $titulaires = implode(', ', $titulaires);

        return $titulaires;
    }

    /**
     * retourne, sous forme de chaîne, la liste des profs qui donnent un cours donné
     *
     * @param string $coursGrp
     *
     * @return string
     */
    public function getProfs4CoursGrp ($coursGrp) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT dpc.acronyme, nom, prenom, sexe ';
        $sql .= 'FROM '.PFX.'profsCours AS dpc ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON dp.acronyme = dpc.acronyme ';
        $sql .= 'WHERE coursGrp = :coursGrp ';
        $sql .= 'ORDER BY nom, prenom ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':coursGrp', $coursGrp, PDO::PARAM_STR, 15);
        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $acronyme = $ligne['acronyme'];
                $adresse = ($ligne['sexe'] == 'F') ? 'Mme' : 'M.';
                $nom = sprintf('%s %s. %s', $adresse, mb_substr($ligne['prenom'], 0, 1, 'UTF-8'), $ligne['nom']);
                $liste[$acronyme] = $nom;
            }
        }

        $noms = implode(', ', $liste);

        Application::deconnexionPDO($connexion);

        return $noms;
    }

    /***
     * supprime la fonction de titulaire d'une classe $groupe aux profs de la liste passée en paramètre
     * @param $groupe
     * @param $listeAcronymes
     * @return nombre de suppressions
     */
    public function supprTitulariat($groupe, $listeAcronymes)
    {
        if (($groupe == null) || ($listeAcronymes == null)) {
            die();
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $resultat = 0;
        foreach ($listeAcronymes as $acronyme) {
            $sql = 'DELETE FROM '.PFX.'titus ';
            $sql .= "WHERE acronyme='$acronyme' AND classe='$groupe'";
            $resultat += $connexion->exec($sql);
        }
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

     /***
      * Ajoute les titulaires de la $listeAcronymes à une classe $groupe
      * @param $groupe
      * @param $listeAcronymes
      * @return integer : nombre d'écriture réussies dans la BD
      */
    public function addTitulariat($groupe, $listeAcronymes, $section)
    {
        if (($groupe == null) || ($listeAcronymes == null)) {
            die();
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $resultat = 0;
        foreach ($listeAcronymes as $acronyme) {
            $sql = 'INSERT IGNORE INTO '.PFX.'titus ';
            $sql .= "SET acronyme='$acronyme', classe='$groupe', section='$section' ";
            $resultat += $connexion->exec($sql);
        }

        return $resultat;
        Application::DeconnexionPDO($connexion);
    }

    /***
     * retourne la liste des classes d'un niveau donné
     * @param $niveau
     * @param $entite : groupe (regroupement de plusieurs classes) ou classe
     * @param $sections : tableau des sections concernées
     * @return $listeClasses
     */
    public function listeClassesNiveau($niveau, $entite = 'groupe', $sections = null)
    {
        if ($sections != null) {
            $sections = "'".implode("','", $sections)."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT DISTINCT $entite ";
        $sql .= 'FROM '.PFX.'eleves ';
        $sql .= "WHERE SUBSTR($entite, 1, 1) = '$niveau' ";
        if ($sections != null) {
            $sql .= "AND section IN ($sections) ";
        }
        $sql .= "ORDER BY $entite";
        $resultat = $connexion->query($sql);
        $lesClasses = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $lesClasses[] = $ligne[$entite];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $lesClasses;
    }

    /***
     * retourne la liste de toutes les classes existantes dans l'école pour les sections demandées
     * @param $sections = array des différentes sections existantes
     * @return array
     */
    public function listeClasses($sections = null)
    {
        if ($sections) {
            $sections = "'".implode("','", $sections)."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT classe FROM '.PFX.'eleves ';
        if ($sections) {
            $sql .= "WHERE section IN ($sections) ";
        } else {
            $sql .= "WHERE section != 'PARTI' ";
        }
        $sql .= 'ORDER BY classe, groupe';
        $resultat = $connexion->query($sql);
        $listeClasses = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $classe = $ligne['classe'];
                $listeClasses[] = $classe;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeClasses;
    }

    /**
     * retourne la liste de tous les groupes/classes existants dans l'école pour les sections demandées.
     *
     * @param array $sections liste des sections dont on souhaite connaître les groupes constitutifs
     *
     * @return array
     */
    public function listeGroupes($sections = null)
    {
        if ($sections) {
            $sections = "'".implode("','", $sections)."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT groupe ';
        $sql .= 'FROM '.PFX.'eleves ';
        if ($sections) {
            $sql .= "WHERE section IN ($sections) ";
        }
        $sql .= 'ORDER BY groupe ';
        $resultat = $connexion->query($sql);
        $listeGroupes = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $groupe = $ligne['groupe'];
                $listeGroupes[] = $groupe;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeGroupes;
    }

    /**
     * liste tous les groupes formés de plusieurs classes
     * pour chaque groupe, on indique les classes qui en font partie
     * seuls les groupes constitués de plusieurs classes sont retournés.
     *
     * @param $compact : true si on ne souhaite que les groupes effectivement formés de plusieurs classes
     * @param $section : section concernée; si Null, on cherche dans toutes les sections
     *
     * @return array
     */
    public static function listeGroupesEtClasses($compact = false, $sections = null)
    {
        if ($sections) {
            $sections = "'".implode("','", $sections)."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT groupe, classe FROM '.PFX.'eleves ';
        if ($sections) {
            $sql .= "WHERE section IN ($sections) ";
        }
        $sql .= 'ORDER BY groupe, classe';
        $resultat = $connexion->query($sql);
        $listeGroupes = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $groupe = $ligne['groupe'];
                $listeGroupes[$groupe][] = $ligne['classe'];
            }
        }
        Application::DeconnexionPDO($connexion);
        if ($compact) {
            foreach ($listeGroupes as $nomGroupe => $classes) {
                if (count($classes) > 1) {
                    $listeGroupes[$nomGroupe] = $classes;
                } else {
                    unset($listeGroupes[$nomGroupe]);
                }
            }
        }

        return $listeGroupes;
    }

    /*
     * function listeNiveaux
     * @param
     * liste de tous les niveaux d'études existants
     * habituellement, liste de 1 à 6
     */
    public static function listeNiveaux()
    {
        // si la liste des niveaux est définies dans les constantes
        if (LISTENIVEAUX) {
            return explode(',', LISTENIVEAUX);
        }
    }

    /**
     * si une photo est présente, retourne le matricule de l'élève; sinon, retourne la chaîne 'nophoto'.
     *
     * @param $acronyme
     *
     * @return string
     */
    public static function photo($matricule)
    {
        if (file_exists(INSTALL_DIR."/photos/$matricule.jpg")) {
            return $matricule;
        } else {
            return 'nophoto';
        }
    }

    /**
     * retourne la liste des élèves d'une classe ou d'un groupe.
     *
     * @param $critere
     * @param $entite
     *
     * @return array
     *               $critere est la classe, le degré ou le niveau demandé
     *               $entite est soit 'classe' (par défaut), soit 'groupe'
     *               liste de tous les élèves de l'école (matricule + nom + prénom)
     */
    public static function listeEleves($critere = null, $entite = 'classe', $partis = false, $extended = false)
    {
        $supSQL = array();
        if ($critere != null) {
            $supSQL[] = " $entite = '$critere' ";
        }
        // selon la cas, on liste ou pas les élèves marqués en section "PARTI"
        if ($partis == false) {
            $supSQL[] = " section != 'PARTI' ";
        }
        $supSQL = implode(' AND ', $supSQL);
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);

        $sql = 'SELECT de.matricule, nom, prenom, classe, DateNaiss, commNaissance, user, mailDomain ';
        if ($extended) {
            $sql .= ', sexe, nomResp, adresseResp, cpostResp, localiteResp ';
        }
        $sql .= 'FROM '.PFX.'eleves AS de ';
        $sql .= 'LEFT JOIN '.PFX.'passwd AS dp ON (de.matricule = dp.matricule) ';
        if ($supSQL != '') {
            $sql .= 'WHERE '.$supSQL;
        }
        $sql .= "ORDER BY REPLACE(REPLACE(REPLACE(nom, ' ', ''),'''',''),'-',''), prenom";

        $resultat = $connexion->query($sql);
        $listeEleves = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $dateNaiss = $ligne['DateNaiss'];
                $ligne['mail'] = $ligne['user'].'@'.$ligne['mailDomain'];
                $listeEleves[$matricule] = $ligne;

                $listeEleves[$matricule]['DateNaiss'] = Application::datePHP($dateNaiss);
                $listeEleves[$matricule]['photo'] = self::photo($matricule);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeEleves;
    }

    /**
     * retourne les détails (nom, prénom, classe, groupe) d'une liste d'élèves fournie.
     *
     * @param $listeEleves
     *
     * @return array
     */
    public function detailsDeListeEleves($listeEleves)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT de.matricule, nom, prenom, groupe, classe, DateNaiss, commNaissance, user, mailDomain ';
        $sql .= 'FROM '.PFX.'eleves AS de ';
        $sql .= 'LEFT JOIN '.PFX.'passwd AS dp ON (de.matricule = dp.matricule) ';
        $sql .= "WHERE de.matricule IN ($listeElevesString) ";
        $sql .= "ORDER BY classe, REPLACE(REPLACE (nom, ' ', ''),'''',''), prenom ";
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $dateNaiss = $ligne['DateNaiss'];
                $ligne['mail'] = $ligne['user'].'@'.$ligne['mailDomain'];
                $liste[$matricule] = $ligne;
                $liste[$matricule]['DateNaiss'] = Application::datePHP($dateNaiss);
                $liste[$matricule]['photo'] = self::photo($matricule);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie un tableau contenant l'élève précédent, l'élève courant et l'élève suivant
     * celui dont le matricule est passé en argument.
     *
     * @param $matricule
     * @param $listeEleves
     *
     * @return array ('prev', 'next')
     */
    public function prevNext($matricule, $listeEleves)
    {
        $listeEleves = array_keys($listeEleves);
        $pos = array_search($matricule, $listeEleves);
        $prev = ($pos > 0) ? $listeEleves[$pos - 1] : null;
        $next = ($pos < count($listeEleves) - 1) ? $listeEleves[$pos + 1] : null;

        return array('prev' => $prev, 'next' => $next);
    }

    /**
     * retourne la liste des élèves d'un niveau d'étude (1,2,3,4,5 ou 6).
     *
     * @param string|array $listeNiveaux
     *
     * @return array
     */
    public function listeElevesNiveaux($listeNiveaux, $partis = false)
    {
        if (is_array($listeNiveaux)) {
            $listeNiveauxString = implode(',', $listeNiveaux);
        } else {
            $listeNiveauxString = $listeNiveaux;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, nom, prenom, groupe, classe ';
        $sql .= 'FROM '.PFX.'eleves ';
        $sql .= "WHERE SUBSTR(groupe, 1,1) IN ($listeNiveauxString) ";
        if (!($partis)) {
            $sql .= "AND section != 'PARTI' ";
        }
        $sql .= "ORDER BY classe, REPLACE(REPLACE (nom, ' ', ''),'''',''), prenom";
        $resultat = $connexion->query($sql);
        $listeEleves = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $listeEleves[$matricule] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeEleves;
    }

     /**
      * retourne un tableau contenant x sous-tableaux
      * chacun contenant les élèves sélectionnés par niveau d'étude.
      *
      * @param $listeNiveaux
      *
      * @return array
      */
     public function listesElevesParNiveaux($listeNiveaux)
     {
         $listeEleves = $this->listeElevesNiveaux($listeNiveaux);
         $listesElevesNiveaux = array();
         foreach ($listeEleves as $matricule => $details) {
             $classe = $details['classe'];
             $niveau = substr($classe, 0, 1);
             $matricule = $details['matricule'];
             $listesElevesNiveaux[$niveau][$matricule] = $details;
         }

         return $listesElevesNiveaux;
     }

    /**
     * retourne la liste des élèves qui suivent un coursGrp donné.
     *
     * @param $cours
     *
     * @return array
     */
    public function listeElevesCours($coursGrp, $tri = null, $parti = false)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT '.PFX.'elevesCours.matricule, nom, prenom, classe, user, mailDomain ';
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'elevesCours.matricule) ';
        $sql .= 'LEFT JOIN '.PFX.'passwd ON ('.PFX.'eleves.matricule = '.PFX.'passwd.matricule) ';
        $sql .= "WHERE coursGrp = '$coursGrp' ";
        if ($parti == false) {
            $sql .= "AND section != 'PARTI' ";
        }
        if ($tri == 'alpha') {
            $sql .= "ORDER BY REPLACE(REPLACE(REPLACE(nom,' ',''),'-',''),'\'',''), prenom ";
        } else {
            $sql .= "ORDER BY groupe, REPLACE(REPLACE(REPLACE(nom,' ',''),'-',''),'\'',''), prenom ";
        }

        $resultat = $connexion->query($sql);
        $listeEleves = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $nom = $ligne['nom'];
                $prenom = $ligne['prenom'];
                $matricule = $ligne['matricule'];
                $classe = $ligne['classe'];
                $mail = $ligne['user'].'@'.$ligne['mailDomain'];
                $listeEleves[$matricule] = array(
                        'nom' => $ligne['nom'],
                        'prenom' => $ligne['prenom'],
                        'classe' => $ligne['classe'],
                        'mail' => $mail,
                        'photo' => self::photo($matricule),
                    );
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeEleves;
    }

     /**
      * retourne des listes d'élèves pour chacun des coursGrp passés en paramètre.
      *
      * @param $listeCoursGrp
      *
      * @return array
      */
     public function listeElevesDeListeCoursGrp($listeCoursGrp, $parti = false)
     {
         if (is_array($listeCoursGrp)) {
             $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
         } else {
             $listeCoursGrpString = "'".$listeCoursGrp."'";
         }
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         $sql = 'SELECT coursGrp,'.PFX."elevesCours.matricule, CONCAT(nom,' ',prenom) AS nom, classe ";
         $sql .= 'FROM '.PFX.'elevesCours ';
         $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'elevesCours.matricule) ';
         $sql .= "WHERE coursGrp IN ($listeCoursGrpString) ";
         if ($parti == false) {
             $sql .= "AND section != 'PARTI' ";
         }
         $sql .= "ORDER BY REPLACE(REPLACE(REPLACE(nom,' ',''),'-',''),'\'',''), prenom ";
         $resultat = $connexion->query($sql);
         $listesEleves = array();
         if ($resultat) {
             $resultat->setFetchMode(PDO::FETCH_ASSOC);
             while ($ligne = $resultat->fetch()) {
                 $coursGrp = $ligne['coursGrp'];
                 $matricule = $ligne['matricule'];
                 $nom = $ligne['nom'];
                 $classe = $ligne['classe'];
                 $photo = self::photo($matricule);
                 $listesEleves[$coursGrp][$matricule] = array(
                                'coursGrp' => $coursGrp,
                                'classe' => $classe,
                                'nom' => $nom,
                                // 'matricule'=>$matricule,
                                'photo' => $photo, );
             }
         }
         Application::DeconnexionPDO($connexion);

         return $listesEleves;
     }

        /**
         * renvoie le degré dans lequel se trouve une classe donnée.
         *
         * @param $classe
         *
         * @return int
         */
        public function degreDeClasse($classe)
        {
            $annee = substr($classe, 0, 1);
            $degre = 0;
            switch ($annee) {
                case 6: $degre = 3; break;
                case 5: $degre = 3; break;
                case 4: $degre = 2; break;
                case 3: $degre = 2; break;
                case 2: $degre = 1; break;
                case 1: $degre = 1; break;
                }

            return $degre;
        }

    /**
     * liste structurée des profs liés à une liste de coursGrp (liste indexée par coursGrp).
     *
     * @param string || array : $listeCoursGrp
     * @param string $type : retour = chaîne ou array?
     *
     * @return array || string
     */
    public function listeProfsListeCoursGrp($listeCoursGrp, $type = 'string')
    {
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT coursGrp, nom, prenom, sexe, '.PFX.'profsCours.acronyme ';
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
                $sexe = $ligne['sexe'];
                $ved = ($sexe == 'M') ? 'M. ' : 'Mme';
                if ($type == 'string') {
                    if (isset($liste[$coursGrp])) {
                        $liste[$coursGrp] .= ', '.$ved.' '.$ligne['prenom'].' '.$ligne['nom'];
                    } else {
                        $liste[$coursGrp] = $ved.' '.$ligne['prenom'].' '.$ligne['nom'];
                    }
                } else {
                    $liste[$coursGrp][$acronyme] = $ligne;
                }
                // on supprime le cours dont le prof a été trouvé
                if (is_array($listeCoursGrp))
                    unset($listeCoursGrp[$coursGrp]);
                    else $listeCoursGrp = Null;
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
        $sql .= 'ORDER BY nbheures DESC, libelle';

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['cours'];
                $coursGrp = $ligne['coursGrp'];
                $liste[$cours]['dataCours'] = array('nbheures' => $ligne['nbheures'], 'libelle' => $ligne['libelle'], 'statut' => $ligne['statut']);
                $liste[$cours]['profs'][$coursGrp] = array('acronyme' => $ligne['acronyme'], 'nom' => $ligne['nom'].' '.$ligne['prenom']);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne la liste des cours suivis par un groupe d'élèves dont on fournit la liste. On ne tient pas compte de l'historique.
     * convient bien pour un entête de tableau des cours.
     *
     * @param $listeEleves
     *
     * @return array : liste des cours avec caractéristiques: nbheures, statut, libelle
     */
    public function listeCoursGrpEleves($listeEleves)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT cours, statut, libelle, nbheures ';
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX."cours.cours = SUBSTR(coursGrp, 1, LOCATE('-',coursGrp)-1 )) ";
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= "WHERE matricule IN ($listeElevesString) ";
        $sql .= 'ORDER BY nbheures DESC, statut, cours ';
        $resultat = $connexion->query($sql);
        $listeCours = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['cours'];
                unset($ligne['cours']);
                $listeCours[$cours] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCours;
    }

	/**
     * retourne la liste des coursGrp d'un élève dont on fournit le matricule
     *
     * @param int $matricule
     *
     * @return array
     */
    public function getListeCoursGrp4eleve($matricule) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT coursGrp, nbheures, libelle, cours.cadre, section, statut, nbHeures ';
        $sql .= 'FROM '.PFX.'elevesCours AS ec ';
        $sql .= 'JOIN '.PFX.'cours AS cours ON cours.cours = SUBSTR(coursGrp, 1, LOCATE("-",coursGrp)-1 ) ';
        $sql .= 'JOIN '.PFX.'statutCours AS statut ON statut.cadre = cours.cadre ';
        $sql .= 'WHERE matricule = :matricule ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $listeCours = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $coursGrp = $ligne['coursGrp'];
                $listeCours[$coursGrp] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCours;
    }
    
    /**
     * retourne la liste des abréviations des cours (Ex: 5 GT:SC3-02 devient SC3) dont on fournit la liste
     *
     * @param $listeCoursGrp
     *
     * @return array
     */
    public function abrListeCoursGrp($listeCoursGrp) {
        foreach ($listeCoursGrp AS $coursGrp) {
            $listeCours[$coursGrp] = explode('-', explode(':', $coursGrp)[1])[0];
        }

        return $listeCours;
    }

    /***
     * retourne la liste de tous les cours qui se donnent dans une classe
     * chaque ligne contient
     *  - le cours comme clef
     *  - le nombre d'heures de cours et le libellé du cours
     * pour chaque cours, on distingue
     *  - les différents coursGrp
     *  - les références complètes du prof pour chaque coursGrp
     *
     * @param $classe
     * @return array
    */
    public function listeCoursClassePourDelibe($classe)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT '.PFX.'elevesCours.coursGrp, ';
        $sql .= 'SUBSTR('.PFX."elevesCours.coursGrp, 1,LOCATE('-',".PFX.'elevesCours.coursGrp)-1) AS cours, ';
        $sql .= PFX.'profsCours.acronyme, '.PFX.'profs.nom, '.PFX.'profs.prenom, nbheures, libelle, ';
        $sql .= PFX.'statutCours.statut ';
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX.'cours.cours = SUBSTR('.PFX."elevesCours.coursGrp, 1,LOCATE('-',coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'elevesCours.matricule) ';
        $sql .= 'JOIN '.PFX.'profsCours ON ('.PFX.'profsCours.coursGrp = '.PFX.'elevesCours.coursGrp) ';
        $sql .= 'JOIN '.PFX.'profs ON ('.PFX.'profs.acronyme = '.PFX.'profsCours.acronyme) ';
        $sql .= 'JOIN '.PFX.'statutCours ON ( '.PFX.'statutCours.cadre = '.PFX.'cours.cadre ) ';
        $sql .= "WHERE classe LIKE '$classe' ";
        $sql .= 'ORDER BY statut DESC, nbheures DESC, libelle';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['cours'];
                $liste[$cours]['cours'] = array('nbheures' => $ligne['nbheures'], 'libelle' => $ligne['libelle'], 'statut' => $ligne['statut']);
                $coursGrp = $ligne['coursGrp'];
                $acronyme = $ligne['acronyme'];
                $liste[$cours][$coursGrp]['profs'][$acronyme] = $ligne['nom'].' '.$ligne['prenom'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie la liste des cours suivis par la liste des élèves donnée.
     *
     * @param $listeEleves
     *
     * @return array : liste des cours suivis par chacun des élèves
     */
    public function listeCoursListeEleves($listeEleves)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT DISTINCT matricule, coursGrp, SUBSTR(coursGrp,1,LOCATE('-',coursGrp)-1) AS cours, libelle, nbheures ";
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX."cours AS dc ON (dc.cours = SUBSTR(coursGrp, 1, LOCATE ('-',coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'statutCours AS sc ON (sc.cadre = dc.cadre) ';
        $sql .= "WHERE matricule IN ($listeElevesString) ";
        $sql .= 'ORDER BY matricule, coursGrp ';

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $cours = $ligne['cours'];
                $liste[$matricule][$cours] = array('coursGrp' => $ligne['coursGrp'], 'nbheures' => $ligne['nbheures'], 'libelle' => $ligne['libelle']);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

     /**
      * retourne la liste des cours qui se donnent dans la liste des classes passée en argument.
      *
      * @param $listeClasses
      *
      * @return array
      */
     public function listeCoursListeClasses($listeClasses)
     {
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         if (is_array($listeClasses)) {
             $listeClassesString = "'".implode("','", $listeClasses)."'";
         } else {
             $listeClassesString = $listeClasses;
         }
         $sql = 'SELECT DISTINCT '.PFX.'elevesCours.coursGrp, ';
         $sql .= 'SUBSTR('.PFX."elevesCours.coursGrp, 1,LOCATE('-',".PFX.'elevesCours.coursGrp)-1) AS cours, ';
         $sql .= PFX.'profsCours.acronyme, '.PFX.'profs.nom, '.PFX.'profs.prenom, nbheures, libelle ';
         $sql .= 'FROM '.PFX.'elevesCours ';
         $sql .= 'JOIN '.PFX.'cours ON ('.PFX.'cours.cours = SUBSTR('.PFX."elevesCours.coursGrp, 1,LOCATE('-',coursGrp)-1)) ";
         $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'elevesCours.matricule) ';
         $sql .= 'JOIN '.PFX.'profsCours ON ('.PFX.'profsCours.coursGrp = '.PFX.'elevesCours.coursGrp) ';
         $sql .= 'JOIN '.PFX.'profs ON ('.PFX.'profs.acronyme = '.PFX.'profsCours.acronyme) ';
         $sql .= "WHERE classe IN ($listeClassesString) ";
         $sql .= 'ORDER BY nbheures DESC, libelle';
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

    /**
     * retourne la liste des cours qui se donnent dans une liste de sections passées en paramètre.
     *
     * @param $listeSections
     *
     * @return array
     */
    public static function listeCoursListeSections($listeSections)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        if (is_array($listeSections)) {
            $listeSectionsString = "'".implode("','", $listeSections)."'";
        } else {
            $listeSectionsString = "'".$listeSections."'";
        }
        $sql = 'SELECT DISTINCT cours, libelle ';
        $sql .= 'FROM '.PFX.'cours ';
        $sql .= "WHERE section IN ($listeSectionsString) ";
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

    /**
     * retourne la liste des coursGrp qui sont donnés dans une classe
     * ne tient pas compte de l'historique. Devrait disparaître au profit de
     * $Bulletin->listeCoursGrpEleves($listeEleves, $bulletin).
     *
     * @param $classe
     *
     * @return array
     */
    public function listeCoursGrpClasse($classe)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT '.PFX.'elevesCours.coursGrp, cours, libelle, nbheures, '.PFX.'statutCours.statut, ';
        $sql .= PFX.'profs.acronyme, '.PFX.'profs.nom, '.PFX.'profs.prenom ';
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'elevesCours.matricule = '.PFX.'eleves.matricule) ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX.'cours.cours = SUBSTR('.PFX."elevesCours.coursGrp, 1,LOCATE('-',coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'profsCours ON ('.PFX.'profsCours.coursGrp = '.PFX.'elevesCours.coursGrp) ';
        $sql .= 'JOIN '.PFX.'profs ON ('.PFX.'profs.acronyme = '.PFX.'profsCours.acronyme) ';
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= "WHERE classe = '$classe' ";
        $sql .= 'ORDER BY statut DESC, nbheures DESC, libelle';
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

        /***
        * Recherche de tous les élèves qui n'ont pas de cours
        * @param
        * @return array
        */
       public function listOrphanEleves()
       {
           $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
           $sql = 'SELECT matricule, nom, prenom, groupe ';
           $sql .= 'FROM '.PFX.'eleves ';
           $sql .= 'WHERE matricule NOT IN (SELECT matricule FROM '.PFX.'elevesCours)';
           $sql .= 'ORDER BY groupe, nom, prenom;';
           $resultat = $connexion->query($sql);
           $eleves = array();
           if ($resultat) {
               while ($ligne = $resultat->fetch()) {
                   $matricule = $ligne['matricule'];
                   $eleves[$matricule] = array('groupe' => $ligne['groupe'], 'nom' => $ligne['nom'], 'prenom' => $ligne['prenom']);
               }
           }
           Application::DeconnexionPDO($connexion);

           return $eleves;
       }

    /**
     * recherche tous les cours (les matières) qui ne sont affectés à aucun élève et aucun prof.
     *
     * @param
     *
     * @return array
     */
    public function listOrphanCours()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT cours ';
        $sql .= 'FROM '.PFX.'cours ';
        $sql .= "WHERE (cours NOT IN (SELECT SUBSTR(coursGrp, 1, LOCATE('-',coursGrp)-1) FROM ".PFX.'elevesCours) ';
        $sql .= "AND   (cours NOT IN (SELECT SUBSTR(coursGrp, 1, LOCATE('-',coursGrp)-1) FROM ".PFX.'profsCours))) ';
        $resultat = $connexion->query($sql);
        $listeCours = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['cours'];
                $listeCours[$cours] = $cours;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCours;
    }

    /**
     * retourne le nombre de classes dans l'école.
     *
     * @param
     *
     * @return int
     */
    public function nbClasses()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT COUNT(DISTINCT classe) FROM '.PFX.'eleves';
        $resultat = $connexion->query($sql);
        $nbClasses = $resultat->fetchColumn();
        Application::DeconnexionPDO($connexion);

        return $nbClasses;
    }

    /**
     * function nbEleves.
     *
     * @param
     * renvoie le nombre total d'élèves de l'école
     */
    public function nbEleves()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT COUNT(*) FROM '.PFX.'eleves ';
        $sql .= "WHERE section != 'PARTI' ";
        $resultat = $connexion->query($sql);
        $nbEleves = $resultat->fetchColumn();
        Application::DeconnexionPDO($connexion);

        return $nbEleves;
    }

    /**
     * retourne la liste des élèves dont l'annniversaire a lieu dans x jours.
     *
     * @param $jours
     *
     * @return array
     */
    public function AnniversairesDansxJours($jours)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT matricule, CONCAT(prenom,' ',nom) AS nomPrenom, groupe, ";
        $sql .= "DATE_FORMAT(DateNaiss,'%d-%m') as dateAnniv ";
        $sql .= 'FROM '.PFX.'eleves ';
        $sql .= "WHERE DATE_FORMAT(DateNaiss,'%m-%d') = DATE_FORMAT(now()+INTERVAL $jours DAY,'%m-%d') ";
        $sql .= 'ORDER BY groupe,nom ';
        $resultat = $connexion->query($sql);
        $anniversaires = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $anniversaires[] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $anniversaires;
    }

    /**
     * renvoie un tableau de la liste des anniversaires dans 0, 1, 2 et 3 jours.
     *
     * @param void()
     *
     * @return array
     */
    public function anniversaires()
    {
        $nbEleves = $this->nbEleves();
        $nbClasses = $this->nbClasses();
        $anniversaires = array();
        $anniversaires[1] = $this->AnniversairesDansxJours(0);
        $anniversaires[2] = $this->AnniversairesDansxJours(1);
        $anniversaires[3] = $this->AnniversairesDansxJours(2);
        $anniversaires[4] = $this->AnniversairesDansxJours(3);

        $statAccueil = array('nbEleves' => $nbEleves, 'nbClasses' => $nbClasses, 'listeAnniv' => $anniversaires);

        return $statAccueil;
    }

    /**
     * retourne un tableau contenant nom, prénom, classe et photo d'un élève dont on fournit la matricule.
     *
     * @param string $matrincule
     *
     * @return array
     */
    public function nomPrenomClasse($matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT nom, prenom, groupe ';
        $sql .= 'FROM '.PFX.'eleves ';
        $sql .= "WHERE matricule = '$matricule'";
        $resultat = $connexion->query($sql);
        $ligne = $resultat->fetch();
        Application::DeconnexionPDO($connexion);

        return array(
            'nom' => $ligne['nom'],
            'prenom' => $ligne['prenom'],
            'classe' => $ligne['groupe'],
            'photo' => self::photo($matricule), );
    }

    /**
     * renvoie la liste des adresses mail et des passwd des élèves dont on fournit le matricule.
     *
     * @param string|array $listeEleves
     *
     * @return array()
     */
    public function listeMailsEleves($listeEleves)
    {
        if (is_array($listeEleves)) {
            $listeElevesString = implode(',', array_keys($listeEleves));
        } else {
            $listeElevesString = $listeEleves;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, user, passwd, mailDomain ';
        $sql .= 'FROM '.PFX.'passwd ';
        $sql .= "WHERE matricule IN ($listeElevesString) ";
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $liste[$matricule] = array('mail' => $ligne['user'].$ligne['mailDomain'], 'passwd' => $ligne['passwd']);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * liste de tous les mots de passe associés aux matricules et noms d'utilisateurs des élèves.
     *
     * @param void()
     *
     * @return array
     */
    public function listeElevesPasswd()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, passwd, user FROM '.PFX.'passwd ';
        $resultat = $connexion->query($sql);
        $listeEleves = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $listeEleves[$matricule] = array('user' => $ligne['user'], 'passwd' => $ligne['passwd']);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeEleves;
    }

    /**
     * renvoie un caractère aléatoire dans la série transmise en paramètre.
     *
     * @param $serie string
     *
     * @return char
     */
    private function randomLettre($serie)
    {
        $rang = rand(0, strlen($serie) - 1);

        return $serie[$rang];
    }

     /**
      * attribue un mot de passe aléatoire basé sur l'alternance consonne/voyelle
      * d'une longueur de 'length' caractères.
      *
      * @param $length
      *
      * @return string
      */
     public function randomPasswdEleve($length)
     {
         $consonnes = 'bcdfghjklmnpqrstvwxz';
         $voyelles = 'aaeeiioouuy';
         $passwd = '';
         for ($n = 0;$n < $length;++$n) {
             if ($n % 2 == 0) {
                 $lettreSuivante = $this->randomLettre($consonnes);
             } else {
                 $lettreSuivante = $this->randomLettre($voyelles);
             }
             $passwd .= $lettreSuivante;
         }

         return strtolower($passwd);
     }

    public function userNameEleve($nom, $prenom, $matricule)
    {
        $p = substr(strtolower(Application::stripAccents($prenom)), 0, 1);
        $indesirables = array(' ', '-', "'");
        $n = str_replace($indesirables, '', substr(strtolower(Application::stripAccents($nom)), 0, 7));
        $matricule = trim($matricule);

        return $p.$n.$matricule;
    }

    /**
     * Attribution de mots de passe aléatoires aux élèves qui n'en ont pas encore reçu
     * les noms d'utilisateurs sont également définis dans $this->userNameEleve.
     *
     * @param int $longueur : longueur du mot de passe (par défaut: 8 caractères)
     *
     * @return int : nmbre de passwd attribués
     */
    public function attribPasswdEleves($longueur = 8)
    {
        // tous les élèves
        $listeTousEleves = $this->listeEleves();
        // liste des élèves qui ont déjà reçu un passwd
        $listeElevesPasswd = $this->listeElevesPasswd();

        // construire la liste des élèves sans MDP
        $listeElevesSansPasswd = array();
        foreach ($listeTousEleves as $matricule => &$eleve) {
            $passwd = isset($listeElevesPasswd[$matricule]['passwd']) ? $listeElevesPasswd[$matricule]['passwd'] : null;
            if ($passwd == null) {
                $listeElevesSansPasswd[$matricule] = array('nom' => $eleve['nom'], 'prenom' => $eleve['prenom']);
            }
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'passwd ';
        $sql .= 'SET passwd=:passwd, matricule=:matricule, user=:user ';
        $requete = $connexion->prepare($sql);
        $resultat = 0;
        foreach ($listeElevesSansPasswd as $matricule => $unEleve) {
            $user = $this->userNameEleve($unEleve['nom'], $unEleve['prenom'], $matricule);
            $passwd = $this->randomPasswdEleve($longueur);
            $data = array(':passwd' => $passwd, ':matricule' => $matricule, ':user' => $user);
            $resultat += $requete->execute($data);
        }
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * retourne le nom d'utilisateur et le mot de passe d'un élève
     * dont on fournit le matricule.
     *
     * @param $matriucle string
     *
     * @return array
     */
    public function getUserPasswdEleve($matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT user, passwd ';
        $sql .= 'FROM '.PFX.'passwd ';
        $sql .= "WHERE matricule = '$matricule'";
        $resultat = $connexion->query($sql);
        $data = array();
        if ($resultat) {
            $ligne = $resultat->fetch();
            $user = $ligne['user'];
            $passwd = $ligne['passwd'];
            $data = array('user' => $user, 'passwd' => $passwd);
        }
        Application::DeconnexionPDO($connexion);

        return $data;
    }

    /***
     * renvoie le nombre de modifications dans la base de données.
     * @param $groupe : le groupe dans lequel mettre les classes
     * @param $classes : plusieurs classes qui doivent former le même groupe
     * @return array nombre d'actions réussies sur la BD
     */
    public function saveGroupesClasses($groupe, $classes)
    {
        $resultat = 0;
        if ($groupe) {
            $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'UPDATE '.PFX.'eleves ';
            $sql .= 'SET groupe=:groupe ';
            $sql .= 'WHERE classe=:uneClasse ';
            $requete = $connexion->prepare($sql);
            foreach ($classes as $uneClasse) {
                $data = array(':groupe' => $groupe, ':uneClasse' => $uneClasse);
                $resultat += $requete->execute($data);
            }
            Application::DeconnexionPDO($connexion);
        }

        return $resultat;
    }

    /**
     * ré-initialise les groupes aux valeurs des classes pour le groupe donné.
     *
     * @param $groupe
     *
     * @return int : nombre d'actions réussies sur la BD
     */
    public function unGroup($groupe)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'UPDATE '.PFX.'eleves SET groupe=classe ';
        $sql .= "WHERE groupe = '$groupe'";
        $resultat = $connexion->exec($sql);
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    public function anneeDeClasse($classe)
    {
        return substr($classe, 0, 1);
    }

    /**
     * renvoie la liste des cours pour les niveaux (1,2,3,4,5,6) passés en paramètre.
     *
     * @param $listeNiveaux
     *
     * @return array
     */
    public static function listeCours($listeNiveaux)
    {
        if (is_array($listeNiveaux)) {
            $listeNiveauxString = implode(',', $listeNiveaux);
        } else {
            $listeNiveauxString = $listeNiveaux;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT cours, nbheures, libelle, '.PFX.'cours.cadre, section, statut ';
        $sql .= 'FROM '.PFX.'cours ';
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= "WHERE SUBSTR(cours, 1, 1) IN ($listeNiveauxString) ";
        $sql .= 'ORDER BY cours';
        $resultat = $connexion->query($sql);
        $listeCours = array();
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
     * retourne la liste ordonnée des coursGrp et de leurs profs titulaires pour les niveaux donnés.
     *
     * @param $listeNiveaux : array
     *
     * @return array
     */
    public function listeCoursGrpProf($listeNiveaux)
    {
        if (is_array($listeNiveaux)) {
            $listeNiveauxString = implode(',', $listeNiveaux);
        } else {
            $listeNiveauxString = $listeNiveaux;
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT acronyme, coursGrp ';
        $sql .= 'FROM '.PFX.'profsCours ';
        $sql .= "WHERE SUBSTR(coursGrp, 1, 1) IN ($listeNiveauxString) ";
        $sql .= 'ORDER  BY coursGrp, acronyme';
        $resultat = $connexion->query($sql);
        $listeCoursGrp = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $listeCoursGrp[$coursGrp][] = $ligne['acronyme'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCoursGrp;
    }

    /**
     * renvoie la liste des cours d'un prof donné liée à la liste des classes où ces cours sont donnés.
     *
     * @param $acronyme : acronyme du prof
     *
     * @return array
     */
    public function listeCoursProf($acronyme)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT classe, '.PFX.'profsCours.coursGrp, libelle, ';
        $sql .= 'SUBSTR('.PFX."profsCours.coursGrp,1,LOCATE(':', ".PFX.'profsCours.coursGrp)-1) as niveau, nomCours ';
        $sql .= 'FROM '.PFX.'profsCours ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX.'cours.cours = SUBSTR('.PFX."profsCours.coursGrp,1,LOCATE('-', ".PFX.'profsCours.coursGrp)-1)) ';
        $sql .= 'JOIN '.PFX.'elevesCours on ('.PFX.'elevesCours.coursGrp = '.PFX.'profsCours.coursGrp) ';
        $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'elevesCours.matricule) ';
        $sql .= "WHERE acronyme = '$acronyme' ";
        $sql .= 'ORDER BY coursGrp, libelle ';
        $resultat = $connexion->query($sql);
        $listeCoursGrp = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $libelle = $ligne['libelle'];
                $coursGrp = $ligne['coursGrp'];
                $classe = $ligne['classe'];
                $nomCours = $ligne['nomCours'];
                if (!(isset($listeCoursGrp[$coursGrp]))) {
                    $listeCoursGrp[$coursGrp] = array('libelle' => $libelle, 'nomCours' => $nomCours, 'classes' => $classe);
                } else {
                    $listeCoursGrp[$coursGrp]['classes'] .= ",$classe";
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCoursGrp;
    }

    /**
     * fournit la liste des coursGrp donnés aux différentes niveaux passés en paramètre
     * recherche en deux temps: 1. dans la table des élèves/cours & 2. dans la table des profs/cours
     * certains cours peuvent n'avoir aucun élève ou aucun prof titulaire.
     *
     * @param $listeNiveaux
     *
     * @return array
     */
    public function listeCoursGrp($listeNiveaux)
    {
        if (is_array($listeNiveaux)) {
            $listeNiveauxString = implode(',', $listeNiveaux);
        } else {
            $listeNiveauxString = $listeNiveaux;
        }

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        // recherche dans la table des élèves/cours
        $sql = 'SELECT DISTINCT '.PFX.'elevesCours.coursGrp, libelle, nbheures, '.PFX.'statutCours.statut, cours, acronyme ';
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX.'cours ON (SUBSTR('.PFX."elevesCours.coursGrp,1,LOCATE('-',".PFX.'elevesCours.coursGrp)-1) = '.PFX.'cours.cours) ';
        // LEFT JOIN pour retrouver les cours éventuellement sans profs
        $sql .= 'LEFT JOIN '.PFX.'profsCours ON ('.PFX.'elevesCours.coursGrp = '.PFX.'profsCours.coursGrp) ';
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= 'WHERE SUBSTR('.PFX."elevesCours.coursGrp,1,1) IN ($listeNiveauxString) ";
        $sql .= 'ORDER BY coursGrp, libelle ';

        $listeCoursGrp = array();
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $listeCoursGrp[$coursGrp] = $ligne;
            }
        }
        $listeConnusString = ("'".implode("','", array_keys($listeCoursGrp))."' ");

        // recherche dans la table des profs/cours en évitant les cours déjà connus provenant de la table des élèves
        $sql = 'SELECT  pc.coursGrp, libelle, nbheures, c.cadre, statut, nbheures, cours, acronyme ';
        $sql .= 'FROM '.PFX.'profsCours AS pc ';
        $sql .= 'JOIN '.PFX."cours AS c  ON (c.cours = SUBSTR(coursGrp, 1, LOCATE('-',coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'statutCours sc ON (sc.cadre = c.cadre) ';
        $sql .= "WHERE SUBSTR(pc.coursGrp,1,1) IN ($listeNiveauxString) ";
        $sql .= "AND pc.coursGrp NOT IN ($listeConnusString) ";
        $sql .= 'GROUP BY pc.coursGrp, acronyme ';
        $sql .= 'ORDER BY pc.coursGrp, acronyme ';

        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $listeCoursGrp[$coursGrp] = $ligne;
            }
        }

        Application::DeconnexionPDO($connexion);
        ksort($listeCoursGrp);

        return $listeCoursGrp;
    }

    /**
     * retourne la liste des coursGrp d'un élève dont on fournit le matricule.
     *
     * @param int $matricule
     *
     * @return array
     */
    public function listeCoursGrpEleve($matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT dp.acronyme, sexe, nom, prenom, ec.coursGrp, libelle, nbheures ';
        $sql .= 'FROM '.PFX.'elevesCours AS ec ';
        $sql .= 'JOIN '.PFX."cours AS dc ON (dc.cours = SUBSTR(coursGrp, 1, LOCATE('-', coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'profsCours AS pc ON pc.coursGrp = ec.coursGrp ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON dp.acronyme = pc.acronyme ';
        $sql .= 'WHERE matricule = :matricule ';
        $sql .= 'ORDER BY nbheures DESC, libelle ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $resultat = $requete->execute();
        $listeCoursGrp = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $ligne['prenom'] = mb_substr($ligne['prenom'], 0, 1, 'UTF-8').'. ';
                $adresse = ($ligne['sexe'] == 'M') ? 'M. ' : 'Mme';
                $ligne['nom'] = sprintf('%s %s %s', $adresse, $ligne['prenom'], $ligne['nom']);
                // si le cours est déjà passé, on rajoute le nom de l'autre titulaire
                if (isset($listeCoursGrp[$coursGrp])) {
                    $listeCoursGrp[$coursGrp]['nom'] .= ' & '.$ligne['nom'];
                    $listeCoursGrp[$coursGrp]['acronyme'] .= ' & '.$ligne['acronyme'];
                } else {
                    $listeCoursGrp[$coursGrp] = $ligne;
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCoursGrp;
    }


    /**
     * renvoie la liste des matières correspondant à une liste de $coursGrp
     *
     * @param array $listeCoursGrp
     *
     * @return array
     */
    public function getListeMatieresEleve($listeCoursGrp){
        $listeCours = Null;
        foreach ($listeCoursGrp as $unCoursGrp) {
            $unCours = explode('-', $unCoursGrp)[0];
            $listeCours[$unCours] = $unCours;
        }

        return $listeCours;
    }

    /**
     * retourne la liste des cours effectivement suivis par chacun des élèves d'une classe.
     *
     * @param $classe string: la classe de ces élèves
     *
     * @return array
     */
    public function listeCoursSuivis($classe, $type = 'coursGrp')
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT coursGrp, SUBSTR(coursGrp,1, LOCATE('-',coursGrp)-1) as cours, ".PFX.'elevesCours.matricule AS m ';
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'JOIN '.PFX.'eleves ON ('.PFX.'eleves.matricule = '.PFX.'elevesCours.matricule) ';
        $sql .= "WHERE classe = '$classe' ";
        $sql .= 'ORDER BY m, cours';
        $resultat = $connexion->query($sql);
        $listeCours = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['m'];
                if ($type == 'coursGrp') {
                    $cours = $ligne['coursGrp'];
                } else {
                    $cours = $ligne['cours'];
                }
                $listeCours[$matricule][] = $cours;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCours;
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
                /* $posDash = strpos($coursGrp,'-');
                if ($posDash != 0)
                    $cours = substr($coursGrp, 0, $posDash);
                    else $cours = $coursGrp; */

                $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
                //$sql = "SELECT cours, nbheures, libelle, statut, c.cadre, SUBSTR(cours,1,1) as annee, SUBSTR(cours, 2, LOCATE(':', cours)-2) as section, ";
                //$sql .= "SUBSTR(cours, LOCATE(':',cours)+1, 99) AS code ";
                //$sql .= "FROM ".PFX."cours AS c ";
                //$sql .= "JOIN ".PFX."statutCours ON (".PFX."statutCours.cadre = c.cadre) ";
                //$sql .= "WHERE cours = '$cours' ";

                $sql = 'SELECT cours, nbheures, libelle, statut, c.cadre, section ';
            $sql .= 'FROM '.PFX.'cours AS c ';
            $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = c.cadre) ';
            $sql .= "WHERE cours = '$cours' ";

            $ligne = array();
            $resultat = $connexion->query($sql);
            if ($resultat) {
                $resultat->setFetchMode(PDO::FETCH_ASSOC);
                $ligne = $resultat->fetch();
                $ligne['forme'] = $forme;
                $ligne['annee'] = $annee;
                $ligne['code'] = $code;
            }
            Application::DeconnexionPDO($connexion);
        }

        return $ligne;
    }

    /**
     * retourne la liste des profs en doublon sur un coursGrp
     * permet de repérer les remplacements terminés (interims).
     *
     * @param listeCoursGrpProfs
     *
     * @return array
     */
    public function listeRemplacants($listeCoursGrpProfs)
    {
        $listeRemplacements = array();
        foreach ($listeCoursGrpProfs as $coursGrp => $prof) {
            if (count($prof) > 1) {
                $listeRemplacements[$coursGrp] = $prof;
            }
        }

        return $listeRemplacements;
    }

    /**
     * retourne la liste des profs titulaires d'un coursGrp.
     *
     * @param $coursGrp
     *
     * @return array
     */
    public function listeProfsCoursGrp($coursGrp)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT '.PFX.'profsCours.acronyme, nom, prenom ';
        $sql .= 'FROM '.PFX.'profsCours ';
        $sql .= 'JOIN '.PFX.'profs ON ('.PFX.'profs.acronyme = '.PFX.'profsCours.acronyme) ';
        $sql .= "WHERE coursGrp = '$coursGrp' ";
        $sql .= 'ORDER BY acronyme, nom, prenom';
        $listeProfs = array();
        $resultat = $connexion->query($sql);
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $acronyme = $ligne['acronyme'];
                $listeProfs[$acronyme] = $ligne['nom'].' '.$ligne['prenom'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeProfs;
    }

    /**
     * affecter la liste de profs passée en argument au coursGrp indiqué.
     *
     * @param $listeProfs
     * @param $coursGrp
     */
    public function ajouterProfsCoursGrp($listeProfs, $coursGrp)
    {
        $nbResultats = 0;
        if ($listeProfs) {
            $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
            foreach ($listeProfs as $acronyme) {
                $sql = 'INSERT IGNORE INTO '.PFX.'profsCours ';
                $sql .= "SET acronyme='$acronyme', coursGrp='$coursGrp' ";
                $nbResultats += $connexion->exec($sql);
            }
            Application::DeconnexionPDO($connexion);
        }

        return $nbResultats;
    }

    /**
     * supprime la liste des profs de l'affectation du coursGrp passé en argument.
     *
     * @param $supprProf
     * @param $coursGrp
     */
    public function supprimerProfsCoursGrp($supprProfs, $coursGrp)
    {
        $nbResultats = 0;
        if ($supprProfs) {
            $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
            $debut = Application::chrono();
            foreach ($supprProfs as $acronyme) {
                $sql = 'DELETE FROM '.PFX.'profsCours ';
                $sql .= "WHERE coursGrp='$coursGrp' AND acronyme='$acronyme' ";
                $nbResultats += $connexion->exec($sql);
            }
            Application::DeconnexionPDO($connexion);
        }

        return $nbResultats;
    }

    /**
     * Ajouter la liste d'élèves fournie à la liste des cours(Grp) fournie
     * la table historique des mouvements est mise à jour en même temps.
     *
     * @param $listeEleves
     * @param $listeCoursGrp
     * @param $bulletin : la période à partir de laquelle a lieu le changement
     *
     * @return int : le nombre d'enregistrements effectués dans la BD
     */
    public function addListeElevesListeCoursGrp($listeEleves, $listeCoursGrp, $bulletin)
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
        // ajout dans la table Eleves-Cours
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'elevesCours ';
        $sql .= 'SET matricule=:matricule,coursGrp=:coursGrp';
        $requete1 = $connexion->prepare($sql);

        // ajout dans la table Historique
        $sql = 'INSERT IGNORE INTO '.PFX.'bullHistoCours ';
        $sql .= 'SET coursGrp=:coursGrp,matricule=:matricule,bulletin=:bulletin, ';
        $sql .= "mouvement = 'ajout'";
        $requete2 = $connexion->prepare($sql);

        $nbResultats = 0;
        foreach ($listeEleves as $matricule => $wtf) {
            foreach ($listeCoursGrp as $coursGrp => $wtf2) {
                // ajout dans la table Eleves-Cours
                $nb = $requete1->execute(array(
                                    ':matricule' => $matricule,
                                    ':coursGrp' => $coursGrp,
                                        ));
                // ajout dans la table Historique
                if ($nb > 0) {
                    $nb = $requete2->execute(array(
                            ':matricule' => $matricule,
                            ':coursGrp' => $coursGrp,
                            ':bulletin' => $bulletin, )
                            );
                    ++$nbResultats;
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $nbResultats;
    }

    /**
     * Supprimer la liste d'élèves communiquées de la liste des cours(Grp) fournie
     * la table historique des mouvements est mise à jour en même temps.
     *
     * @param $listeEleves
     * @param $listeCoursGrp
     * @param $bulletin : la période à partir de laquelle a lieu le changement
     *
     * @return int : le nombre d'enregistrements effectués dans la BD
     */
    public function delListeElevesListeCoursGrp($listeEleves, $listeCoursGrp, $bulletin)
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

        // suppression de la table Eleves-Cours
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'elevesCours ';
        $sql .= "WHERE coursGrp IN ($listeCoursGrpString) AND matricule IN ($listeElevesString)";
        $nbResultats = $connexion->exec($sql);

        // notifications dans l'historique
        if ($nbResultats > 0) {
            $sql = 'INSERT IGNORE INTO '.PFX.'bullHistoCours ';
            $sql .= 'SET coursGrp=:coursGrp,matricule=:matricule,bulletin=:bulletin, ';
            $sql .= "mouvement = 'suppr'";
            $requete = $connexion->prepare($sql);

            $nbResultats = 0;
            foreach ($listeCoursGrp as $coursGrp => $wtf) {
                foreach ($listeEleves as $matricule => $data) {
                    try {
                        $nbResultats += $requete->execute(array(
                            ':matricule' => $matricule,
                            ':coursGrp' => $coursGrp,
                            ':bulletin' => $bulletin, )
                            );
                    } catch (PDOException $e) {
                        die($e->getMessage());
                    }
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $nbResultats;
    }

    /**
     * retourne la liste des coursGrp correspondant à une matière donnée ($cours)
     * les données proviennent de la table des affectations profs->cours.
     *
     * @param $cours
     *
     * @return array
     */
    public function listeCoursGrpDeCours($cours)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);

        // recherche de la liste des cours basée sur la table des élèves/cours
        $sql = 'SELECT ec.coursGrp, COUNT(*) as nbEleves, libelle, '.PFX.'cours.cadre, '.PFX.'statutCours.statut, pc.acronyme, ';
        $sql .= "nbheures, CONCAT(nom,' ',prenom) AS nomProf, cours ";
        $sql .= 'FROM '.PFX.'elevesCours AS ec ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX."cours.cours = '$cours') ";
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        // LEFT JOIN pour le cas où un cours serait sans prof titulaire
        $sql .= 'LEFT JOIN '.PFX.'profsCours AS pc ON (pc.coursGrp = ec.coursGrp) ';
        $sql .= 'LEFT JOIN '.PFX.'profs ON ('.PFX.'profs.acronyme = pc.acronyme) ';
        $sql .= "WHERE SUBSTR(ec.coursGrp,1,LOCATE('-',ec.coursGrp)-1) = '$cours' ";
        $sql .= 'GROUP BY pc.acronyme, ec.coursGrp ';
        $sql .= 'ORDER BY ec.coursGrp, pc.acronyme ';

        $listeCoursGrp = array();
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $acronyme = $ligne['acronyme'];
                $nomProf = $ligne['nomProf'];
                if (isset($listeCoursGrp[$coursGrp])) {
                    $listeCoursGrp[$coursGrp]['acronyme'] .= ' / '.$acronyme;
                    $listeCoursGrp[$coursGrp]['nomProf'] .= ' / '.$nomProf;
                } else {
                    $listeCoursGrp[$coursGrp] = $ligne;
                }
            }
        }
        // on récupère la liste des cours déjà connus pour éviter de la retrouver dans la requê
        $listeConnusString = "'".implode("','", array_keys($listeCoursGrp))."'";

        // recherche de la liste des cours sur la tables des profs/cours (nb=0 car il n'y a forcément aucun élève inscrit
        // -on les aurait trouvés dans la liste précédente)
        $sql = 'SELECT  dpc.coursGrp, 0 AS nbEleves, libelle, '.PFX."cours.cadre, dsc.statut, dpc.acronyme, nbheures, CONCAT(nom,' ',prenom) AS nomProf, cours ";
        $sql .= 'FROM '.PFX.'profsCours as dpc ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON (dp.acronyme = dpc.acronyme) ';
        $sql .= 'JOIN '.PFX.'cours ON ('.PFX."cours.cours = '$cours') ";
        $sql .= 'JOIN '.PFX.'statutCours AS dsc ON (dsc.cadre = '.PFX.'cours.cadre) ';
        // LEFT JOIN pour le cas où aucun élève n'est inscrit au cours
        $sql .= 'LEFT JOIN '.PFX.'elevesCours ON ('.PFX.'elevesCours.coursGrp = dpc.coursGrp) ';
        $sql .= "WHERE (SUBSTR(dpc.coursGrp,1,LOCATE('-',dpc.coursGrp)-1) = '$cours') AND (dpc.coursGrp NOT IN ($listeConnusString)) ";
        $sql .= 'GROUP BY dpc.acronyme, dpc.coursGrp ';
        $sql .= 'ORDER BY dpc.coursGrp, nomProf ';

        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $acronyme = $ligne['acronyme'];
                $nomProf = $ligne['nomProf'];
                if (isset($listeCoursGrp[$coursGrp])) {
                    $listeCoursGrp[$coursGrp]['acronyme'] .= ' / '.$acronyme;
                    $listeCoursGrp[$coursGrp]['nomProf'] .= ' / '.$nomProf;
                } else {
                    $listeCoursGrp[$coursGrp] = $ligne;
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCoursGrp;
    }

    /**
     * retourne la liste de tous les cours en les triant par niveau d'étude.
     *
     * @params
     *
     * @return array
     */
    public function listeCoursNiveaux()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $listeCoursNiveaux = array();
        $sql = 'SELECT cours, libelle, nbheures, '.PFX.'statutCours.statut, ';
        $sql .= "SUBSTR(cours,1,LOCATE(':',cours)-1) AS niveau ";
        $sql .= 'FROM '.PFX.'cours ';
        $sql .= 'JOIN '.PFX.'statutCours ON ('.PFX.'statutCours.cadre = '.PFX.'cours.cadre) ';
        $sql .= 'ORDER BY niveau, cours, nbheures ';

        $resultat = $connexion->query($sql);
        $listeCoursNiveaux = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $cours = $ligne['cours'];
                $listeCoursNiveaux[$cours] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeCoursNiveaux;
    }

     /**
      * retourne l'historique des cours d'un élève dont on fournit le matricule.
      *
      * @param $matricule
      *
      * @return array
      */
     public function historiqueCoursEleve($matricule)
     {
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         $sql = 'SELECT coursGrp, bulletin, mouvement ';
         $sql .= 'FROM '.PFX.'bullHistoCours ';
         $sql .= "WHERE matricule = '$matricule' ";
         $resultat = $connexion->query($sql);
         $listeHistorique = array();
         if ($resultat) {
             $resultat->setFetchMode(PDO::FETCH_ASSOC);
             while ($ligne = $resultat->fetch()) {
                 $coursGrp = $ligne['coursGrp'];
                 $listeHistorique[$matricule][$coursGrp][] = $ligne;
             }
         }
         Application::DeconnexionPDO($connexion);

         return $listeHistorique;
     }

    /**
     * vérifie que l'élève dont on fournit le matricule existe dans la BD.
     *
     * @param $matricule: integer
     *
     * @return bool
     */
    public function eleveExiste($matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule ';
        $sql .= 'FROM '.PFX.'eleves ';
        $sql .= "WHERE matricule = '$matricule'";
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $ligne = $resultat->fetch();
        }
        Application::DeconnexionPDO($connexion);

        return $ligne['matricule'] == $matricule;
    }

     /**
      * Suppression définitive d'une liste d'élèves dans la BD
      * la vérification de l'intégrité référentielle doit être faite avant d'appeler cette fonction.
      *
      * @param $listeEleves : liste des élèves à supprimer de la BD
      *
      * @return int : le nombre d'élèves effectivement supprimés.
      */
     public function supprEleves($listeEleves)
     {
         $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
         $sql = 'DELETE FROM '.PFX.'eleves ';
         $sql .= 'WHERE matricule = :matricule';
         $requete = $connexion->prepare($sql);
         $nb = 0;
         foreach ($listeEleves as $matricule) {
             $nb += $requete->execute(array(':matricule' => $matricule));
         }
         Application::DeconnexionPDO($connexion);

         return $nb;
     }

    /**
     * retourne le nom complet du prof dont on fournit l'abréviation.
     *
     * @param $acronyme
     *
     * @return string : le nom du prof ou une chaîne vide si abréviation pas trouvée
     */
    public function abr2name($acronyme)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT nom, prenom ';
        $sql .= 'FROM '.PFX.'profs ';
        $sql .= "WHERE acronyme LIKE '%$acronyme%' ";
        $resultat = $connexion->query($sql);
        $nomPrenom = $acronyme;
        if ($resultat) {
            $ligne = $resultat->fetch();
            if ($ligne != '') {
                $nomPrenom = $ligne['prenom'][0].'. '.$ligne['nom'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $nomPrenom;
    }

    /***
     * retourne la liste des formes (GT, TT, TQ, C, S, D) existantes dans l'école
     * sur la base des noms des cours suivis par les élèves
     * @param
     * @return array
     */
    public function listeFormes()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT DISTINCT SUBSTR(coursGrp,2, LOCATE(':',coursGrp)-2) AS section ";
        $sql .= 'FROM '.PFX.'elevesCours ';
        $sql .= 'ORDER BY section ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $section = $ligne['section'];
                $liste[$section] = $section;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * liste des couples cadre / statut existants
     * le "cadre" d'un cours est un code conventionnel en Belgique qui correspond au statut d'un cours dans la formation
     * voir la table statutCours dans la base de données pour trouver les correspondances.
     *
     * @param
     *
     * @return array
     */
    public function listeCadresStatuts()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT cadre, statut ';
        $sql .= 'FROM '.PFX.'statutCours ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $cadre = $ligne['cadre'];
                $liste[$cadre] = $ligne['statut'];
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * enregistrement d'une nouvelle matière (méta-cours) dans la base de données
     * la fonction retourne le nombre d'enregistrements réalisés (normalement, un seul ou aucun) et le nom du cours enregistré
     * cette dernière information est utile si le cours a été édité.
     *
     * @param array $post
     *
     * @return array (integer, string)
     */
    public function enregistrerMatiere($post)
    {
        $fullEdition = isset($post['fullEdition']) ? $post['fullEdition'] : null;
        $libelle = $post['libelle'];
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        // s'il s'agit d'une édition d'un cours existant, on ne peut modifier que son libellé
        if ($fullEdition == 0) {
            $cours = $_POST['cours'];
            $sql = 'UPDATE '.PFX.'cours ';
            $sql .= "SET libelle = '$libelle' ";
            $sql .= "WHERE cours = '$cours' ";
            $nb = $connexion->exec($sql);
        } else {
            $annee = $post['niveau'];
            $section = $post['section'];
            $forme = $post['forme'];
            $code = $post['code'];
            $nbheures = $post['nbheures'];
            $cadre = $post['cadre'];
            $cours = $annee.$forme.':'.$code.$nbheures;
            $sql = 'INSERT INTO '.PFX.'cours ';
            $sql .= "SET cours = '$cours', nbheures='$nbheures', libelle='$libelle', cadre='$cadre', section='$section' ";
            $sql .= "ON DUPLICATE KEY UPDATE libelle='$libelle' ";
            $nb = $connexion->exec($sql);
        }
        Application::DeconnexionPDO($connexion);

        return array($nb, $cours);
    }

    /**
     * renvoie la liste des sections organisées à l'école.
     *
     * @param
     *
     * @return : array
     */
    public function listeSections()
    {
        $sections = explode(',', SECTIONS);

        return $sections;
    }

    /**
     * renvoie la liste des élèves, user et passwd pour un groupe classe donné.
     *
     * @param string $groupe
     *
     * @return array
     */
    public function listePasswd($groupe)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT de.matricule, nom, prenom, groupe, user, passwd ';
        $sql .= 'FROM '.PFX.'eleves AS de ';
        $sql .= 'JOIN '.PFX.'passwd AS dp ON (dp.matricule = de.matricule) ';
        $sql .= "WHERE groupe = '$groupe' ";
        $sql .= 'ORDER BY nom, prenom ';
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
}
