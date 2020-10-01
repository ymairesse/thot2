<?php

class user
{
    private $userName;
    private $oldUser;          // sauvegarde d'un utilisateur dans la session
    private $section;           // section pour l'élève (TQ, GT, TT, ...)
    private $userType;            // eleve, parent ou prof
    private $identite;            // données personnelles
    private $identiteReseau;    // données réseau IP,...

    /**
     * constructeur de l'objet user.
     */
    public function __construct($userName = null, $userType = 'eleve', $oldUser = Null)
    {
        $this->identiteReseau = $this->identiteReseau();
        if (isset($userName)) {
            $this->userName = $userName;
            if ($oldUser == NULL){
                // c'est un nouveau login
                $this->oldUser = $userName;
                }
                else {
                    // c'est un changement de profil
                    $this->oldUser = $oldUser;
                }
            // parent ou eleve
            $this->userType = $userType;
            $this->setIdentite($userType);
        }
    }

    /**
     * recherche toutes les informations de la table des utilisateurs pour l'utilisateur actif et les reporte dans l'objet User.
     *
     * @param string $userType : parent ou eleve
     *
     * @return void
     */
    public function setIdentite($userType)
    {
        $userName = addslashes($this->userName);
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        switch ($userType) {
            case 'eleve':
                $sql = 'SELECT "eleve" AS type, el.matricule, nom, prenom, classe, groupe, section, ';
                $sql .= 'mailDomain, md5pwd, user, CONCAT(user,"@",mailDomain) AS mail ';
                $sql .= 'FROM '.PFX.'eleves AS el ';
                $sql .= 'JOIN '.PFX.'passwd AS ppw ON ppw.matricule = el.matricule ';
                $sql .= "WHERE ppw.user = '$userName' LIMIT 1 ";
                break;

            case 'parent':
                $sql = "SELECT 'parent' AS type, formule, userName, tp.matricule, tp.nom, tp.prenom, ";
                $sql .= 'lien, mail, classe, groupe, section, tp.md5pwd, ';
                $sql .= 'de.nom AS nomEl, de.prenom AS prenomEl ';
                $sql .= 'FROM '.PFX.'thotParents AS tp ';
                $sql .= 'JOIN '.PFX.'eleves AS de ON de.matricule = tp.matricule ';
                $sql .= 'JOIN '.PFX.'passwd AS pwd ON pwd.matricule = tp.matricule ';
                $sql .= "WHERE userName = '$userName' LIMIT 1 ";
                break;

            default:
                die('invalid userType');
                break;
        }

        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $resultat->fetch();

            $this->identite = $ligne;

        }
        Application::DeconnexionPDO($connexion);
    }

    /**
     * renvoie toutes les informations d'identité présentes dans l'objet User.
     *
     * @param void()
     *
     * @return array
     */
    public function getIdentite()
    {
        return $this->identite;
    }

    /**
     * renvoie le amtricule de l'utilisateur actif.
     *
     * @param void
     *
     * @return string
     */
    public function getMatricule()
    {
        $identite = $this->identite;

        return $identite['matricule'];
    }

    /**
     * renvoie le groupe classe dont fait partie l'utilisateur.
     *
     * @param void()
     *
     * @return string
     */
    public function getClasse()
    {
        $identite = $this->identite;
        $classe = $identite['groupe'];

        return $classe;
    }

    public function getSection()
    {
        $identite = $this->identite;
        $section = $identite['section'];

        return $section;
    }

    /**
     * retourne l'année d'étude de l'utilisateur sur la base de son groupe classe.
     *
     * @param void
     *
     * @return int
     */
    public function getAnnee()
    {
        $identite = $this->identite;
        $annee = $identite['groupe'][0];

        return $annee;
    }

    /**
     * renvoie le prénom et le nom de l'utilisateur.
     *
     * @param
     *
     * @return string
     */
    public function getNom()
    {
        $prenom = $this->identite['prenom'];
        $nom = $this->identite['nom'];

        return $prenom.' '.$nom;
    }

    /**
     * renvoie le nom d'utilisateur principal du parent connecté
     *
     * @param void
     *
     * @return string
     */
    public function getOldUser(){
        return $this->oldUser;
    }

    /**
     * renvoie le nom de l'élève correspondant au parent.
     *
     * @param void
     *
     * @return string
     */
    public function getNomEleve()
    {
        $prenom = isset($this->identite['prenomEl']) ? $this->identite['prenomEl'] : null;
        $nom = isset($this->identite['nomEl']) ? $this->identite['nomEl'] : null;
        if (($nom != null) && ($prenom != null)) {
            return $prenom.' '.$nom;
        } else {
            return;
        }
    }

    /**
     * Renvoie la liste des coursGrp suivis par l'utilisateur.
     *
     * @param void
     *
     * @return array
     */
    public function listeCoursGrpEleve()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $matricule = $this->getMatricule();
        $sql = 'SELECT coursGrp FROM '.PFX.'elevesCours ';
        $sql .= 'WHERE matricule = :matricule ';
        $connexion = $requete->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_STR, 6);
        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $liste[] = $ligne['coursGrp'];
            }
        }
        Application::deconnexionPDO($connexion);

        return $liste;
    }


    /**
     * Renvoie la liste des coursGrp suivis par l'utilisateur.
     *
     * @param void
     *
     * @return array
     */
    public function listeCoursEleve()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $matricule = $this->getMatricule();
        $sql = 'SELECT coursGrp FROM '.PFX.'elevesCours ';
        $sql .= "WHERE matricule = '$matricule' ";
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $liste[] = $ligne['coursGrp'];
            }
        }
        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie la liste des matières suivies par un élève dont on fournit
     * la liste des cours
     *
     * @param array $listeCours
     *
     * @return array
     */
    public function getListeMatieresEleve($listeCours){
        $listeMatieres = array();
        foreach ($listeCours as $unCours) {
            $matiere = explode('-', $unCours)[0];
            $listeMatieres[] = $matiere;
        }
        return $listeMatieres;
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
    public function listeDetailCoursEleve()
    {
        $matricule = $this->getMatricule();

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT DISTINCT ec.coursGrp, SUBSTR(ec.coursGrp, 1, LOCATE('-', ec.coursGrp)-1) AS cours, ";
        $sql .= 'sc.statut, pc.acronyme, dp.nom, dp.prenom, dp.sexe, nbheures, libelle ';
        $sql .= 'FROM '.PFX.'elevesCours AS ec ';
        $sql .= 'JOIN '.PFX."cours AS dcours ON (dcours.cours = SUBSTR(ec.coursGrp, 1,LOCATE('-',ec.coursGrp)-1)) ";
        $sql .= 'JOIN '.PFX.'eleves AS de ON (de.matricule = ec.matricule) ';
        $sql .= 'JOIN '.PFX.'profsCours AS pc ON (pc.coursGrp = ec.coursGrp) ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON (dp.acronyme = pc.acronyme) ';
        $sql .= 'JOIN '.PFX.'statutCours AS sc ON (sc.cadre = dcours.cadre ) ';
        $sql .= 'WHERE de.matricule = :matricule ';
        $sql .= 'ORDER BY nbheures DESC, libelle';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_STR, 6);
        $resultat = $requete->execute();

        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $coursGrp = $ligne['coursGrp'];
                $liste[$coursGrp]['dataCours'] = array('nbheures' => $ligne['nbheures'], 'libelle' => $ligne['libelle'], 'statut' => $ligne['statut']);
                $formule = ($ligne['sexe'] == 'F') ? 'Mme' : 'M.';
                $nom = sprintf('%s %s. %s', $formule, mb_substr($ligne['prenom'], 0, 1, 'UTF-8'), $ligne['nom']);
                $liste[$coursGrp]['profs'] = array('acronyme' => $ligne['acronyme'], 'nom' => $nom);
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * fournit le mot de passe MD5 de l'utilisateur.
     *
     * @param
     *
     * @return string
     */
    public function getPasswd()
    {
        return $this->identite['md5pwd'];
    }

    /**
     * fournit le nom d'utilisateur de l'utilisateur actif.
     *
     * @param void()
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * retourne le type d'utilisateur (parent ou eleve).
     *
     * @param void()
     *
     * @return string
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * retourne toutes les informations concernant l'élève utilisateur (ou le parent).
     *
     * @param void()
     *
     * @return array
     */
    public function getTousDetailsEleve()
    {
        $matricule = $this->getMatricule();
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT * FROM '.PFX.'eleves ';
        $sql .= "WHERE matricule = '$matricule ' ";
        $resultat = $connexion->query($sql);
        $eleve = null;
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $eleve = $resultat->fetch();
            $eleve['DateNaiss'] = Application::datePHP($eleve['DateNaiss']);
        }

        Application::DeconnexionPDO($connexion);

        return $eleve;
    }

    /**
     * retourne la liste détaillée des éducateurs de l'élève utilisateur (ou du parent)
     *
     * @param void
     *
     * @return array
     */
    public function getEducsEleve(){
        $groupe = $this->getClasse();
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT edcl.acronyme, sexe, nom, prenom, mail, titre, groupe ';
        $sql .= 'FROM '.PFX.'educsClasses AS edcl ';
        $sql .= 'JOIN '.PFX.'profs AS profs ON profs.acronyme = edcl.acronyme ';
        $sql .= 'WHERE groupe = :groupe ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':groupe', $groupe, PDO::PARAM_STR, 5);
        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $acronyme = $ligne['acronyme'];
                $sexe = $ligne['sexe'];
                $ligne['prenom'] = mb_substr($ligne['prenom'], 0, 1, 'UTF-8').'. ';
                $ligne['adresse'] = ($sexe == 'M') ? 'M. ' : 'Mme';
                $liste[$acronyme] = $ligne;
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne le nom de l'application; permet de ne pas confondre deux applications
     * différentes qui utiliseraient la variable de SESSION pour retenir MDP et USERNAME
     * de la même façon.
     *
     * @param
     *
     * @return string
     */
    public function applicationName()
    {
        return $this->applicationName;
    }

    /**
     * Vérifie si un nom d'utilisateur est déjà défini pour un parent.
     *
     * @param string $userName
     *
     * @return bool
     */
    public function userExists($userName)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT count(*) FROM '.PFX.'thotParents ';
        $sql .= 'WHERE userName = :userName ';
        $requete = $connexion->prepare($sql);
        $data = array(':userName' => $userName);
        $resultat = $requete->execute($data);
        $nb = $requete->fetchColumn();
        Application::DeconnexionPDO($connexion);

        return $nb > 0;
    }

    /**
     * Vérifie si une adresse mail est déjà utilisée par un parent.
     *
     * @param string $mail
     *
     * @return bool
     */
    public function mailExists($mail)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT count(*) FROM '.PFX.'thotParents ';
        $sql .= "WHERE mail = '$mail' ";
        $resultat = $connexion->query($sql);
        $nb = $resultat->fetchColumn();
        Application::DeconnexionPDO($connexion);

        return $nb > 0;
    }

    /**
     * vérifier que l'utilisateur dont on fournit le userName est signalé comme loggé depuis l'adresse ip dans la BD.
     *
     * @param $userName : string
     * @param $ip : string
     */
    public function islogged($userName, $ip)
    {
        $userName = $this->userName();
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT user, ip ';
        $sql .= 'FROM '.PFX.'sessions ';
        $sql .= "WHERE user='$userName' AND ip='$ip' ";
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $verif = $resultat->fetchAll();
        }
        Application::DeconnexionPDO($connexion);

        return count($verif) > 0;
    }

    /**
     * convertir l'objet $user en tableau.
     *
     * @param void()
     *
     * @return array
     */
    private function toArray()
    {
        return (array) $this;
    }

    /**
     * ajout de l'utilisateur dans le journal des logs.
     *
     * @param object $user
     *
     * @return int
     */
    public function logger($user)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $date = date('Y-m-d');
        $heure = date('H:i');
        $userName = $user->getuserName();
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'thotLogins ';
        $sql .= "SET user=:userName, date='$date', heure='$heure', ip='$ip', host=:hostname ";
        $requete = $connexion->prepare($sql);
        $data = array(':userName' => $userName, ':hostname' => $hostname);
        $n = $requete->execute($data);

        // indiquer une session ouverte depuis l'adresse IP correspondante
        $sql = 'INSERT INTO '.PFX.'thotSessions ';
        $sql .= "SET user=:userName, ip='$ip' ";
        $sql .= "ON DUPLICATE KEY UPDATE ip='$ip' ";
        $requete2 = $connexion->prepare($sql);

        $data = array(':userName' => $userName);
        $n = $requete2->execute($data);
        Application::DeconnexionPDO($connexion);

        return $n;
    }

    /**
     * délogger l'utilisateur indiqué de la base de données (table des sessions actives).
     *
     * @return int : nombre d'effacement dans la BD
     */
    public function delogger()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $userName = $this->userName();
        $sql = 'DELETE FROM '.PFX.'thotSessions ';
        $sql .= "WHERE user='$userName' ";
        $resultat = $connexion->exec($sql);
        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * renvoie le userName de l'utilisateur courant.
     *
     * @param
     *
     * @return string
     */
    public function userName()
    {
        return $this->userName;
    }

    /**
     * renvoie les informations d'identification réseau de l'utilisateur courant.
     *
     * @param
     *
     * @return array ip, hostname, date, heure
     */
    public static function identiteReseau()
    {
        $data = array();
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $data['hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $data['date'] = date('d/m/Y');
        $data['heure'] = date('H:i');

        return $data;
    }

    /**
     * renvoie l'adresse mail de l'utilisateur courant.
     */
    public function getMail()
    {
        return $this->identite['mail'];
    }

    /**
     * renvoie l'adresse IP de connexion de l'utilisateur actuel.
     *
     * @param
     *
     * @return string
     */
    public function getIP()
    {
        $data = $this->identiteReseau();

        return $data['ip'];
    }

    /**
     * renvoie le nom de l'hôte correspondant à l'IP de l'utilisateur en cours.
     *
     * @param
     *
     * @return string
     */
    public function getHostname()
    {
        $data = $this->identiteReseau();

        return $data['hostname'];
    }

    /**
     * renvoie la liste des logs de l'utilisateur en cours.
     *
     * @param $userName
     *
     * @return array
     */
    public function getLogins()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT * FROM '.PFX."logins WHERE user='".$this->getuserName()."' ORDER BY date,heure ASC";
        $resultat = $connexion->query($sql);
        $logins = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $logins = $resultat->fetchall();
        }
        Application::DeconnexionPDO($connexion);

        return $logins;
    }

    /**
     * liste les accès de l'utilisateur indiqué entre deux bornes.
     *
     * @param $user		nom de l'utilisateur concerné
     * @param $nombre  nombre d'accès à traiter
     * @param $from		nombre de lignes à laisser tomber en début
     *
     * @return array : liste des derniers accès à l'application
     */
    public function listeLogins()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT ip,host,date,DATE_FORMAT(heure,'%H:%i') as heure, reussi ";
        $sql .= 'FROM '.PFX.'logins ';
        $sql .= "WHERE user='$this->userName' ";
        $sql .= 'ORDER BY date DESC,heure DESC ';
        $resultat = $connexion->query($sql);
        $acces = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $ligne['date'] = Application::datePHP($ligne['date']);
                $acces[] = $ligne;
            }
        }
        Application::deconnexionPDO($connexion);

        return $acces;
    }

    /**
     * récupère la liste des comptes de fratrie liés à un compte donné
     *
     * @param string $user : utilisateur parent
     *
     * @return array : la liste des comptes associés
     */
    public function getComptesFratrie ($user){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT parent, fratrie ';
        $sql .= 'FROM '.PFX.'thotFratrie AS tfr ';
        $sql .= 'JOIN '.PFX.'thotParents AS parents ON parents.userName = tfr.fratrie ';
        $sql .= 'WHERE parent LIKE :user ';

        $requete = $connexion->prepare($sql);

        $requete->bindParam(':user', $user, PDO::PARAM_STR, 25);

        $resultat = $requete->execute();
        $liste = array();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $liste[] = $ligne['fratrie'];
            }
        }
        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne le nom de l'élève correspondant à un $user parent
     *
     * @param array $fratrie : tableau des comptes parents pour une fratrie
     *
     * @return array
     */
     public function getEleves4Parent($user){
     		$connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
     		// $fratrieString = "'".implode("','", $fratrie)."'";
     		// $sql = 'SELECT DISTINCT de.matricule, de.nom, de.prenom, groupe, pwd.user, parents.userName AS userParent ';
     		// $sql .= 'FROM '.PFX.'eleves AS de ';
     		// $sql .= 'JOIN '.PFX.'thotParents AS parents ON parents.matricule = de.matricule ';
     		// $sql .= 'JOIN '.PFX.'passwd AS pwd ON pwd.matricule = de.matricule ';
     		// $sql .= 'WHERE de.matricule IN (SELECT matricule FROM '.PFX.'thotParents WHERE userName IN ('.$fratrieString.')) ';

            $sql = 'SELECT parent, fratrie, de.nom, de.prenom, de.groupe, parents.matricule, pwd.user ';
            $sql .= 'FROM '.PFX.'thotFratrie ';
            $sql .= 'JOIN '.PFX.'thotParents AS parents ON parents.userName = fratrie ';
            $sql .= 'JOIN '.PFX.'eleves AS de ON de.matricule = parents.matricule ';
            $sql .= 'JOIN '.PFX.'passwd AS pwd ON pwd.matricule = parents.matricule ';
            $sql .= 'WHERE parent = :user ';
     		$requete = $connexion->prepare($sql);
// echo $sql;
            $requete->bindParam(':user', $user, PDO::PARAM_STR, 25);
// Application::afficher($user, true);

             $resultat = $requete->execute();

              $eleve = array();
              if ($resultat) {
     			 $requete->setFetchMode(PDO::FETCH_ASSOC);
                  while ($ligne = $requete->fetch()) {
                      $matricule = $ligne['matricule'];
                      $eleve[$matricule] = array(
                          'userParent' => $ligne['fratrie'],
                          'nom' => sprintf('%s %s [%s]', $ligne['nom'], $ligne['prenom'], $ligne['groupe']),
                          'userEleve' => $ligne['user']
                      );
                  }
              }

              Application::deconnexionPDO($connexion);
// Application::afficher($eleve, true);
              return $eleve;
          }

    /**
     * vérifie si oldUser et newUser font partie de la même fratrie (sécurité au moment de changer d'utilisateur)
     *
     * @param string $oldUser
     * @param string $newUser
     *
     * @return boolean
     */
    public function checkSameFratrie($oldUser, $newUser){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT parent FROM '.PFX.'thotFratrie ';
        $sql .= 'WHERE fratrie = :newUser AND parent = :oldUser ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':oldUser', $oldUser, PDO::PARAM_STR, 25);
        $requete->bindParam(':newUser', $newUser, PDO::PARAM_STR, 25);

        $resultat = $requete->execute();
        $test = false;
        if ($resultat){
            $ligne = $requete->fetch();
            $test = ($ligne['parent'] == $oldUser);
        }

        Application::deconnexionPDO($connexion);

        return $test;
    }

    /**
     * vérifier le mot de passe pour l'utilisateur parent donné
     *
     * @param string $userName : utilisateur "parent"
     * @param string $passwd : le mot de passe en clair
     *
     * @return boolean
     */
    public function checkParentPasswd($userName, $passwd){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT userName ';
        $sql .= 'FROM '.PFX.'thotParents ';
        $sql .= 'WHERE md5pwd = :md5pwd AND userName = :userName ';
        $requete = $connexion->prepare($sql);

        $md5pwd = md5($passwd);
        $requete->bindParam(':md5pwd', $md5pwd, PDO::PARAM_STR, 40);
        $requete->bindParam(':userName', $userName, PDO::PARAM_STR, 25);

        $resultat = $requete->execute();
        $user = '';
        if ($resultat) {
            $ligne = $requete->fetch();
            $user = $ligne['userName'];
        }

        Application::DeconnexionPDO($connexion);

        return $user == $userName;
    }

    /**
     * Initialise éventuellement une fratrie avec parent = UserName, fratrie = UserName
     *
     * @param string $userName
     *
     * @return void()
     */
    public function initFratrie($userName){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT IGNORE INTO '.PFX.'thotFratrie ';
        $sql .= 'SET parent = :userName, fratrie = :userName ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':userName', $userName, PDO::PARAM_STR, 25);

        $resultat = $requete->execute();

        Application::DeconnexionPDO($connexion);
    }

    /**
     * ajout d'un élève à une fratrie
     *
     * @param string $userName : nom de l'utilisateur courant
     * @param string $newUser : nom d'utilisateur correspondant à l'élève à ajouter
     *
     * @return int : nombre d'insertions dans la BD
     */
    public function add2Fratrie($userName, $newUser){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'thotFratrie ';
        $sql .= 'SET parent = :userName, fratrie = :newUser ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':userName', $userName, PDO::PARAM_STR, 25);
        $requete->bindParam(':newUser', $newUser, PDO::PARAM_STR, 25);

        $resultat = $requete->execute();

        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * changement d'utilisateur en gardant les droits admins (Application).
     *
     * @param $acronyme
     *
     * @return string
     */
    public function changeUser($userName)
    {
        // conserver la session de l'admin courant
        $this->oldUser = $_SESSION[APPLICATION];

        // prépartion d'un nouvel utilisateur "parent"
        $newUser = new user($userName, 'parent');

        $_SESSION[APPLICATION] = $newUser;
        $qui = $_SESSION[APPLICATION]->identite();

        return $qui['prenom'].' '.$qui['nom'].': '.$qui['acronyme'];
    }

    /**
     * rompre le lien de famille pour le parent actif $proprio et l'enfant $userName
     *
     * @param string $proprio
     * @param string $userName
     *
     * @return int : nombre de liens brisés (normalement, 1 ou 0)
     */
    public function unlink($proprio, $userParent) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'thotFratrie ';
        $sql .= 'WHERE parent = :proprio AND fratrie = :userParent ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':proprio', $proprio, PDO::PARAM_STR, 25);
        $requete->bindParam(':userParent', $userParent, PDO::PARAM_STR, 25);

// echo $sql;
// Application::afficher(array('proprio' => $proprio, 'userParent' =>  $userParent));

        $resultat = $requete->execute();

        $sql = 'DELETE FROM '.PFX.'thotFratrie ';
        $sql .= 'WHERE parent = :userParent AND fratrie = :proprio ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':proprio', $proprio, PDO::PARAM_STR, 25);
        $requete->bindParam(':userParent', $userParent, PDO::PARAM_STR, 25);

        // echo $sql;
        // Application::afficher(array('proprio' => $proprio, 'userParent' =>  $userParent), true);

        $resultat = $requete->execute();

        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

}
