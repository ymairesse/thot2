<?php

class Application
{
    public function __construct()
    {
        self::lireConstantes();
        // sorties PHP en français
        setlocale(LC_ALL, 'fr_FR.utf8');
    }

    /**
     * lecture de toutes les constantes du fichier config.ini.
     *
     * @param void()
     */
    public static function lireConstantes()
    {
        // lecture des paramètres généraux dans le fichier .ini, y compris la constante "PFX"
        $constantes = parse_ini_file(INSTALL_DIR.'/config.ini');
        foreach ($constantes as $key => $value) {
            define("$key", $value);
        }

        // lecture dans la table PFX."config" de la BD
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT parametre,valeur ';
        $sql .= 'FROM '.PFX.'config ';
        $resultat = $connexion->query($sql);
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $key = $ligne['parametre'];
                $valeur = $ligne['valeur'];
                if (!(defined($key)))
					define("$key", $valeur);
            }
        } else {
            die('config table not present');
        }
        self::DeconnexionPDO($connexion);
    }

    /**
     * suppression de tous les échappements automatiques dans le tableau passé en argument.
     *
     * @param $tableau
     *
     * @return array
     */
    private function Normaliser($tableau)
    {
        foreach ($tableau as $clef => $valeur) {
            if (!is_array($valeur)) {
                $tableau [$clef] = stripslashes($valeur);
            } else {
                // appel récursif
                $tableau [$clef] = self::Normaliser($valeur);
            }
        }

        return $tableau;
    }

    ### --------------------------------------------------------------------###
    public function Normalisation()
    {
        // si magic_quotes est "ON",
        if (get_magic_quotes_gpc()) {
            $_POST = self::Normaliser($_POST);    // normaliser les $_POST
            $_GET = self::Normaliser($_GET);        // normaliser les $_GET
            $_REQUEST = self::Normaliser($_REQUEST);    // normaliser les $_REQUEST
            $_COOKIE = self::Normaliser($_COOKIE);    // normaliser les $_COOKIE
        }
    }

    /**
     * afficher proprement le contenu d'une variable précisée
     * le programme est éventuellement interrompu si demandé.
     *
     * @param :    $data n'importe quel tableau ou variable
     * @param bool $die  : si l'on souhaite interrompre le programme avec le dump
     * */
    static function afficher($data, $die = false)
    {
        if (($data == Null)) {
            echo 'Tableau vide';
        } else {
            echo '<pre>';
            print_r($data);
            echo '</pre>';
            echo '<hr />';
        }
        if ($die) {
            die();
        }
    }

    /**
     * afficher proprement le contenu d'une variable
     * sans affichage visible à l'écran
     * le programme est éventuellement interrompu si demandé.
     *
     * @param $data
     * @param bool $die: si l'on souhaite interrompre le programme avec le dump
     *
     * */
     static function afficher_silent($tableau, $die = false)
     {
         echo '<!-- ';
         self::afficher($tableau, $die);
         echo '-->';
     }

    /**
     * retourne la valeur "POSTEE" ou, si pas de POST, la valeur du COOKIE
     * fixe la valeur du COOKIE à la valeur "POSTEE"
     *
     * @param  string $name  $_POST[$name]
     * @param  int $duree: durée de validité du cookie
     *
     * @return string
     */
    public static function postOrCookie ($name, $duree=Null) {
        if ($duree == Null)
            $duree = time() + 365 * 24 * 3600;
        if (isset($_POST[$name])) {
            $value = $_POST[$name];
            setcookie($name, $value, $duree);
        } else {
                $value = (isset($_COOKIE[$name])) ? $_COOKIE[$name] : null;
            }
        return $value;
    }

    /**
     * renvoie le temps écoulé depuis le déclenchement du chrono.
     *
     * @param
     *
     * @return string
     */
    public static function chrono()
    {
        $temps = explode(' ', microtime());

        return $temps[0] + $temps[1];
    }

    /**
     * Connexion à la base de données précisée.
     *
     * @param PARAM_HOST : serveur hôte
     * @param PARAM_BD : nom de la base de données
     * @param PARAM_USER : nom d'utilisateur
     * @param PARAM_PWD : mot de passe
     *
     * @return connexion à la BD
     */
    public static function connectPDO($host, $bd, $user, $mdp)
    {
        try {
            // indiquer que les requêtes sont transmises en UTF8
            // INDISPENSABLE POUR EVITER LES PROBLEMES DE CARACTERES ACCENTUES
            $connexion = new PDO('mysql:host='.$host.';dbname='.$bd, $user, $mdp,
                                array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        } catch (Exception $e) {
            $date = date('d/m/Y H:i:s');
            echo "<style type='text/css'>";
            echo '.erreurBD {width: 500px; margin-left: auto; margin-right: auto; border: 1px solid red; padding: 1em;}';
            echo '.erreurBD .erreur {color: green; font-weight: bold}';
            echo '</style>';

            echo "<div class='erreurBD'>";
            echo '<h3>A&iuml;e, a&iuml;e, a&iuml;e... Caramba...</h3>';
            echo "<p>Une erreur est survenue lors de l'ouverture de la base de donn&eacute;es.<br>";
            echo "Si vous &ecirc;tes l'administrateur et que vous tentez d'installer le logiciel, veuillez v&eacute;rifier le fichier config.inc.php </p>";
            echo "<p>Si le probl&egrave;me se produit durant l'utilisation r&eacute;guli&egrave;re du programme, essayez de rafra&icirc;chir la page (<span style='color: red;'>touche F5</span>)<br>";
            echo "Dans ce cas, <strong>vous n'&ecirc;tes pour rien dans l'apparition du souci</strong>: le serveur de base de donn&eacute;es est sans doute trop sollicit&eacute;...</p>";
            echo "<p>Veuillez rapporter le message d'erreur ci-dessous &agrave; l'administrateur du syst&egrave;me.</p>";
            echo "<p class='erreur'>Le $date, le serveur dit: ".$e->getMessage().'</p>';
            echo '</div>';
            die();
        }

        return $connexion;
    }

    /**
     * Déconnecte la base de données.
     *
     * @param $connexion
     */
    public static function DeconnexionPDO($connexion)
    {
        $connexion = null;
    }

    /**
     * retourne le nom du répertoire actuel.
     *
     * @param void()
     *
     * @return string
     */
    public static function repertoireActuel()
    {
        $dir = array_reverse(explode('/', getcwd()));

        return $dir[0];
    }

    /**
     * convertir les dates au format usuel jj/mm/AAAA en YY-mm-dd pour MySQL.
     *
     * @param string $date date au format usuel
     *
     * @return string date au format MySQL
     */
    public static function dateMysql($date)
    {
        $dateArray = explode('/', $date);
        $sqlArray = array_reverse($dateArray);
        $date = implode('-', $sqlArray);

        return $date;
    }

    /**
     * convertir les date au format MySQL vers le format usuel.
     *
     * @param string $date date au format MySQL
     *
     * @return string date au format usuel français
     */
    public static function datePHP($dateMysql)
    {
        $dateArray = explode('-', $dateMysql);
        $phpArray = array_reverse($dateArray);
        $date = implode('/', $phpArray);

        return $date;
    }

    /**
     * convertir les heures au format MySQL vers le format ordinaire à 24h.
     *
     * @param string $heure l'heure à convertir
     *
     * @return string l'heure au format usuel
     */
    public static function heureMySQL($heure)
    {
        $heureArray = explode(':', $heure);
        $sqlArray = array_reverse($heureArray);
        $heure = implode(':', $sqlArray);

        return $heure;
    }

    /**
     * converir les heures au format PHP vers le format MySQL.
     *
     * @param string $heure
     *
     * @return string
     */
    public static function heurePHP($heure)
    {
        $heureArray = explode(':', $heure);
        $sqlArray = array_reverse($heureArray);
        $heure = implode(':', $sqlArray);

        return $heure;
    }


    /**
     * convertir un datetime (date et heure) MySQL en date et heure conventionnel en français
     *
     * @param string dateTime : au format YYYY-MM-DD hh:mm
     *
     * @return string : DD-MM-YYYY hh:mm
     */
    public function dateTimeFr($dateTime) {
        if ($dateTime != Null) {
            $dateEnvoi = explode(' ', $dateTime);
            $date = self::datePHP($dateEnvoi[0]);

            return sprintf('%s %s', $date, $dateEnvoi[1]);
        }
        else return Null;
    }

    /**
     * retourne le jour de la semaine correspondant à une date au format MySQL.
     *
     * @param string $dataMySQL
     *
     * @return string
     */
    public static function jourSemaineMySQL($dateMySQL)
    {
        $timeStamp = strtotime($dateMySQL);

        return strftime('%A', $timeStamp);
    }

    /**
     * Fonction de conversion de date du format français (JJ/MM/AAAA) en Timestamp.
     *
     * @param string $date Date au format français (JJ/MM/AAAA)
     *
     * @return int Timestamp en seconde
     *             http://www.julien-breux.com/2009/02/17/fonction-php-date-francaise-vers-timestamp/
     */
    public static function dateFR2Time($date)
    {
        list($day, $month, $year) = explode('/', $date);
        $timestamp = mktime(0, 0, 0, $month, $day, $year);

        return $timestamp;
    }

    /**
     * date d'aujourd'hui.
     *
     * @param void()
     *
     * @return string
     */
    public static function dateNow()
    {
        return date('d/m/Y');
    }

    /**
     * Suppression de la virgule et remplacement par un point dans les nombres + suppression des espaces.
     *
     * @param $nombre string
     *
     * @return string
     */
    public static function sansVirg($nombre)
    {
        $nombre = preg_replace("/,/", ".",$nombre);
        $nombre = filter_var($nombre, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        return $nombre;
    }

    /**
     * filtrage des actions par utilisateur.
     *
     * @param $action : action envisagée
     * @param $userType : type d'utilisateur
     *
     * @return string : l'action permise ou Null
     */
    public function filtreAction($action, $userType)
    {
        switch ($userType) {
            case 'eleve':
                $permis = array('bulletin', 'repertoire', 'remediation', 'documents', 'casiers', 'anniversaires', 'jdc', 'parents', 'logoff', 'annonces', 'contact', 'info', 'mails', 'comportement', 'forums');
                if (!(in_array($action, $permis))) {
                    $action = null;
                }
                break;
            case 'parent':
                $permis = array('bulletin', 'repertoire', 'remediation', 'documents', 'casiers', 'jdc', 'profil', 'frereSoeur', 'logoff', 'annonces', 'contact', 'reunionParents', 'info', 'comportement');
                if (!(in_array($action, $permis))) {
                    $action = null;
                }
                break;
            case 'admin':
                break;
            default:
                // wtf
                break;
        }

        return $action;
    }

    /**
     * retourne la liste des élèves pour une classe donnée.
     *
     * @param string $classe
     *
     * @return array()
     */
    public function listeEleves($classe)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT userName, nom, prenom, statut, mail ';
        $sql .= 'FROM '.PFX.'users ';
        $sql .= "WHERE classe = '$classe' ";
        $sql .= 'ORDER  BY nom, prenom ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $userName = $ligne['userName'];
                $liste[$userName] = $ligne;
            }
        }
        self::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * liste structurée des profs liés à une liste de coursGrp (liste indexée par coursGrp).
     *
     * @param string | array : $listeCoursGrp
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
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
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
                unset($listeCoursGrp[$coursGrp]);
            }
        }
        self::DeconnexionPDO($connexion);
            // on rajoute tous les cours dont les affectations de profs sont inconnues
            if ($listeCoursGrp != null) {
                foreach ($listeCoursGrp as $coursGrp => $wtf) {
                    $liste[$coursGrp] = PROFNONDESIGNE;
                }
            }

        return $liste;
    }

    /**
     * retourne la liste des profs titulaires pour un élève dont on fourni le matricule.
     *
     * @param $matricule
     *
     * @return array
     */
    public function listeTitulaires($matricule)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT dt.acronyme, dt.classe, dp.nom, dp.prenom, dp.sexe ';
        $sql .= 'FROM '.PFX.'eleves AS de ';
        $sql .= 'LEFT JOIN '.PFX.'titus AS dt ON dt.classe = de.groupe ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON dp.acronyme = dt.acronyme ';
        $sql .= "WHERE matricule = '$matricule' ";
        $sql .= 'ORDER BY nom, prenom ';

        $liste = array();
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $acronyme = $ligne['acronyme'];
                $liste[$acronyme] = $ligne;
            }
        }
        self::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne la liste des utilisateurs uniques connectés depuis une date donnée.
     *
     * @param $date
     *
     * @return array
     */
    public function listeConnectesDate($date)
    {
        $date = $this->dateMysql($date);
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT user, classe, nom, prenom ';
        $sql .= 'FROM '.PFX.'parentLogins AS lo ';
        $sql .= 'JOIN '.PFX.'users AS users ON users.userName = lo.user ';
        $sql .= "WHERE date >= '$date' ";
        $sql .= 'ORDER by classe, nom, prenom ';

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $userName = $ligne['user'];
                $liste[$userName] = $ligne;
            }
            self::DeconnexionPDO($connexion);

            return $liste;
        }
    }

    /**
     * retourne le nom en français du cours dont on fournit le code (Ex: 2C:INFO2-03)
     *
     * @param string $coursGrp
     *
     * @return string
     */
    public function nomCours ($coursGrp) {
        $cours = substr($coursGrp, 0, strpos($coursGrp,'-'));

        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT libelle, nbheures ';
        $sql .= 'FROM '.PFX.'cours ';
        $sql .= 'WHERE cours=:cours ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':cours', $cours, PDO::PARAM_STR, 16);
        $resultat = $requete->execute();
        $nomCours = '';
        if ($resultat) {
            $ligne = $requete->fetch();
            $nomCours = sprintf('%s %dh', $ligne['libelle'], $ligne['nbheures']);
        }

        self::DeconnexionPDO($connexion);

        return $nomCours;
    }

    /**
     * détermine la nature précise du destinataire d'une annonce
     *
     * @param $type : le type général de destinataire (ecole, niveau, classe, cours)
     * @param $destinataire : précision du destinataire: $niveau, $classe, $cours
     *
     * @return string
     */
    public function pourQui ($type, $destinataire, $matricule, $nomEleve) {
        if ($matricule == $destinataire)
            return $nomEleve;
            else  {
                switch ($type) {
                    case 'ecole':
                        return 'TOUS';
                        break;
                    case 'niveau':
                        return sprintf('Élèves de %de année', $destinataire);
                        break;
                    case ('coursGrp'):
                        return sprintf('Les élèves du cours %s', self::nomCours($destinataire));
                        break;
                    case ('classes'):
                        return sprintf('Les élèves de %s', $destinataire);
                        break;
                    }
            }
    }

    /**
     * renvoie les détails d'une notification dont on fournit l'id dans la base de données
     *
     * @param $notifId : l'id de la notification dans la BD
     *
     * @return array
     */
    public function getNotification($notifId)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, type, proprietaire, objet, texte, dateDebut, dateFin, destinataire, mail, accuse, freeze ';
        $sql .= 'FROM '.PFX.'thotNotifications ';
        $sql .= 'WHERE id= :notifId ';
        $requete = $connexion->prepare($sql);

        $notification = Null;
        $requete->bindParam(':notifId', $notifId, PDO::PARAM_INT);
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $notification = $requete->fetch();
            $notification['dateDebut'] = self::datePHP($notification['dateDebut']);
            $notification['dateFin'] = self::datePHP($notification['dateFin']);
        }
        Application::deconnexionPDO($connexion);

        return $notification;
    }

    /**
     * retourne la liste structurée des annonces destinées à l'élève dont on donne le matricule et la classe.
     *
     * @param int $matricule
     * @param string $classe
     * @param array $listeCours
     * @param array $listeMatieres
     * @param string $nomEleve ??????????
     *
     * @return array
     */
    public function listeAnnonces($matricule, $classe, $listeCours, $listeMatieres, $nomEleve)
    {
        $niveau = substr($classe, 0, 1);
        $listeCoursString = "'".implode('\',\'', $listeCours)."'";
        $listeMatieresString = "'".implode('\',\'', $listeMatieres)."'";
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT dtn.id, type, proprietaire, destinataire, objet, texte, dateDebut, dateFin, dtn.mail, accuse, dp.nom, dp.sexe, dateEnvoi ';
        $sql .= 'FROM '.PFX.'thotNotifications AS dtn ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = dtn.proprietaire ';
        $sql .= "WHERE destinataire IN ('$matricule', '$classe', '$niveau', 'ecole', $listeCoursString, $listeMatieresString) ";
        $sql .= 'AND (dateFin > NOW() AND dateDebut <= NOW()) ';
        $sql .= 'ORDER BY dateEnvoi DESC, dateDebut DESC ';

        $resultat = $connexion->query($sql);
        $listeAnnonces = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $id = $ligne['id'];
                $ligne['dateDebut'] = self::datePHP($ligne['dateDebut']);
                $ligne['dateFin'] = self::datePHP($ligne['dateFin']);
                $ligne['dateEnvoi'] = self::dateTimeFr($ligne['dateEnvoi']);
                $ligne['pourQui'] = self::pourQui($ligne['type'], $ligne['destinataire'], $matricule, $nomEleve);
                if ($ligne['nom'] != '') {
                    switch ($ligne['sexe']) {
                        case 'M':
                            $ligne['proprietaire'] = 'M. '.$ligne['nom'];
                            break;
                        case 'F':
                            $ligne['proprietaire'] = 'Mme '.$ligne['nom'];
                            break;
                    }
                }
                $listeAnnonces[$id] = $ligne;
            }
        }
        self::DeconnexionPDO($connexion);

        return $listeAnnonces;
    }

    /**
     * Fusionne la liste des annonces et la liste des flags
     *
     * @param $listeAnnonces : liste des annonces triées par id de l'annonce
     * @param $listeFlag : liste des demandes d'accusés de lecture triées par id
     *
     * @return array : combinaison des deux arrays de données
     */
    public function comboAnnoncesFlags($listeAnnonces, $listeFlagsAnnonces)
    {
        foreach ($listeAnnonces as $id => $dataAnnonce) {
            if (isset($listeFlagsAnnonces[$id])) {
                $listeAnnonces[$id]['flags'] = $listeFlagsAnnonces[$id];
                }
                else $listeAnnonces[$id]['flags'] = array('lu' => Null, 'dateHeure' => Null);
        }

        return $listeAnnonces;
    }

    /**
     * fustionne la liste des annonces et la liste des PJ
     *
     * @param array $listeAnnonces : liste des annonces triées sur le le notifId
     * @param array $listePJ : liste des shareId des PJ pour triées sur les notifId
     *
     * @return array
     */
    public function comboAnnoncesPJ($listeAnnonces, $listePJ) {
        foreach ($listeAnnonces as $notifId => $dataAnnonce) {
            $listeAnnonces[$notifId]['PJ'] = isset($listePJ[$notifId]) ? $listePJ[$notifId] : Null;
            }

        return $listeAnnonces;
    }

    /**
     * retourne le nombre d'accusés de lecture manquants pour un élève; on fournit la liste des annonces comboAnnoncesFlags
     *
     * @param array $listeAnnoncesCombo
     *
     * @return int
     */
    public function nbAccusesManquants($listeAnnoncesCombo) {
        $nb = 0;
        foreach ($listeAnnoncesCombo as $id => $uneAnnonce) {
            if ($uneAnnonce['accuse'] == 1) {
                if ($uneAnnonce['flags']['dateHeure'] == Null) {
                    $nb++;
                    }
                }
            }

        return $nb;
    }

    /**
     * retourne le nombre de messages non lus pour un élève dont on fournit la liste des annonces comboAnnoncesFlags
     *
     * @param array $listeAnnoncesCombo
     *
     * @return int
     */
    public function nbNonLus($listeAnnoncesCombo) {
        $nb = 0;
        foreach ($listeAnnoncesCombo as $id => $uneAnnonce) {
            if ($uneAnnonce['flags']['lu'] == 0) {
                $nb++;
                }
            }

        return $nb;
    }

    /**
     * marque un accusé de lecture d'une notification  pour un élève donné.
     *
     * @param $matricule: identité de l'élève
     * @param $id : id de la notification
     *
     * @return string: jour et heure de lecture
     */
    public function marqueAccuse($matricule, $id)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'thotNotifFlags ';
        $sql .= 'SET id=:id, dateHeure = NOW(), matricule=:matricule ';
        $sql .= 'ON DUPLICATE KEY UPDATE dateHeure = NOW() ';
        $requete = $connexion->prepare($sql);

        $dateHeure = '';
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':id', $id, PDO::PARAM_INT);
        $resultat = $requete->execute();
        if ($resultat) {
            $sql = 'SELECT dateHeure ';
            $sql .= 'FROM '.PFX.'thotNotifFlags ';
            $sql .= 'WHERE id=:id AND matricule=:matricule ';
            $requete = $connexion->prepare($sql);
            $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
            $requete->bindParam(':id', $id, PDO::PARAM_INT);

            $resultat = $requete->execute();
            if ($resultat) {
                $ligne = $requete->fetch();
                $dateHeure = $this->dateHeure($ligne['dateHeure']);
            }
        }
        self::DeconnexionPDO($connexion);

        return $dateHeure;
    }

    /**
     * note la lecture de la notification $id pour un élève $matricule donné.
     *
     * @param $matricule: identité de l'élève
     * @param $id : id de la notification
     *
     * @return string: jour et heure de lecture
     */
    public function marqueLu($matricule, $id)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'thotNotifFlags ';
        $sql .= 'SET id=:id, lu=1, matricule=:matricule ';
        $sql .= 'ON DUPLICATE KEY UPDATE lu = 1 ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':id', $id, PDO::PARAM_INT);
        $resultat = $requete->execute();

        self::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * renvoie un tableau de tous les flags existants pour la liste d'annonce passée en paramètre
     *
     * @param array $listeAnnonces: liste **des clefs** pour les annonces
     * @param int $matricule : le matricule de l'élève concerné
     *
     * @return array
     */
    public function listeFlagsAnnonces($listeAnnonces, $matricule) {
        $listeAnnoncesString = implode(', ', $listeAnnonces);
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, matricule, dateHeure, lu ';
        $sql .= 'FROM '.PFX.'thotNotifFlags ';
        $sql .= "WHERE matricule=:matricule AND id IN ($listeAnnoncesString) ";
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $resultat = $requete->execute();
        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $id = $ligne['id'];
                $liste[$id] = array(
                    'dateHeure' => $this->dateHeure($ligne['dateHeure']),
                    'lu' => $ligne['lu']
                    );
                }
            }

        self::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie un tableau des shareIds correspondant aux notifIds
     *
     * @param array $listeAnnonces : liste des clefs pour les annonces
     * @param int $matricule : matricule de l'élève
     *
     * @return array
     */
    public function getPJ4notifs ($listeAnnonces, $matricule) {
        if (is_array($listeAnnonces))
            $listeAnnoncesString = implode(', ', array_keys($listeAnnonces));
            else $listeAnnoncesString = $listeAnnonces;

        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT notifId, dtnpj.shareId, dts.fileId, fileName ';
        $sql .= 'FROM '.PFX.'thotNotifPJ AS dtnpj ';
        $sql .= 'JOIN '.PFX.'thotShares AS dts ON dts.shareId = dtnpj.shareId ';
        $sql .= 'JOIN '.PFX.'thotFiles AS dtf ON dtf.fileId = dts.fileId ';
        $sql .= 'WHERE notifId IN ('.$listeAnnoncesString.')';

        $requete = $connexion->prepare($sql);
        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $notifId = $ligne['notifId'];
                $fileId = $ligne['fileId'];
                $liste[$notifId][$fileId] = $ligne['fileName'];
                }
        }

        self::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie le texte d'une annonce dont on fournit l'id
     *
     * @param int $id
     *
     * @return string
     */
    public function getTexteAnnonce($id) {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT texte ';
        $sql .= 'FROM '.PFX.'thotNotifications ';
        $sql .= 'WHERE id = :id ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':id', $id, PDO::PARAM_INT);
        $texte = '';
        $resultat = $requete->execute();
        if ($resultat) {
            $ligne = $requete->fetch();
            $texte = $ligne['texte'];
        }

        self::DeconnexionPDO($connexion);

        return $texte;
    }

    /**
     * conversion des dateHeures comprenant la date et l'heure au format "classique" pour les dates et
     * en ajustant aux minutes pour les heures.
     *
     * @param $dateHeure : combinaison de date et d'heure au format MySQL Ex: "2015-07-30 11:33:59"
     *
     * @return string : la même chose au format "30/07/2015 11:33"
     */
    private function dateHeure($dateHeure)
    {
        if($dateHeure != '') {
            $dateHeure = explode(' ', $dateHeure);
            $date = $dateHeure[0];
            $date = self::datePHP($date);
            $dateHeure = $date.' à '.substr($dateHeure[1], 0, 5);
        }
        return $dateHeure;
    }

    /**
     * liste des parents déclarés pour un utilisateur "élève", d'après son matricule.
     *
     * @param $matricule
     *
     * @return array
     */
    public function listeParents($matricule)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT nom, prenom, formule, userName, mail, lien, md5pwd ';
        $sql .= 'FROM '.PFX.'thotParents ';
        $sql .= "WHERE matricule = '$matricule' ";
        $sql .= 'ORDER BY nom, prenom, userName ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $userName = $ligne['userName'];
                $liste[$userName] = $ligne;
            }
        }
        self::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * Vérification de l'existence éventuelle d'un utilisateur "parent".
     *
     * @param $userName
     *
     * @return bool
     */
    public function parentExiste($userName)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELCT * FROM '.PFX.'thotParents ';
        $sql .= "WHERE userName = '$userName' ";
        $resultat = $connexion->query($sql);
        self::DeconnexionPDO($connexion);

        return $resultat > 0;
    }

    /**
     * Enregistre les informations relatives à un parent et provenant d'un formulaire.
     *
     * @param $post : array le contenu du formulaire
     *
     * @return interger nombre d'enregistrement réussis
     */
    public function saveParent($post)
    {
        $ok = true;
        $formule = $post['formule'];
        if ($formule == '') {
            $ok = false;
        }
        $nomParent = $post['nomParent'];
        if ($nomParent == '') {
            $ok = false;
        }
        $prenomParent = $post['prenomParent'];
        if ($prenomParent == '') {
            $ok = false;
        }
        $userName = $post['userName'];
        if ($userName == '') {
            $ok = false;
        }
        $mail = $post['mail'];
        if ($mail == '') {
            $ok = false;
        }
        $matricule = $post['matricule'];
        if ($matricule == '') {
            $ok = false;
        }
        $lien = $post['lien'];
        if ($lien == '') {
            $ok = false;
        }
        $passwd = $post['passwd'];
        $passwd2 = $post['passwd2'];
        if (($passwd == '') || ($passwd2 != $passwd)) {
            $ok = false;
        }
        $resultat = 0;
        if ($ok == true) {
            $passwd = md5($passwd);
            $userName = $userName.$matricule;
            $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'INSERT INTO '.PFX.'thotParents ';
            $sql .= 'SET userName=:userName, matricule=:matricule, formule=:formule, nom=:nomParent, prenom=:prenomParent, ';
            $sql .= 'mail=:mail, lien=:lien, md5pwd=:passwd ';
            $sql .= 'ON DUPLICATE KEY UPDATE ';
            $sql .= 'formule=:formule, nom=:nomParent, prenom=:prenomParent, ';
            $sql .= 'mail=:mail, lien=:lien, md5pwd=:passwd ';
            $requete = $connexion->prepare($sql);
            $data = array(
                    ':userName' => $userName,
                    ':matricule' => $matricule,
                    ':formule' => $formule,
                    ':nomParent' => $nomParent,
                    ':prenomParent' => $prenomParent,
                    ':mail' => $mail,
                    ':lien' => $lien,
                    ':passwd' => $passwd,
                    ':formule' => $formule,
                    ':nomParent' => $nomParent,
                    ':prenomParent' => $prenomParent,
                    ':mail' => $mail,
                    ':lien' => $lien,
                    ':passwd' => $passwd,

                );
            $resultat = $requete->execute($data);
            if ($resultat) {
                $resultat = 1;
            }  // pour éviter 2 modifications si DUPLICATE KEY
            self::DeconnexionPDO($connexion);
        }

        return $resultat;
    }

    /**
     * Enregistrement d'un profil modifié dans le formulaire ad-hoc.
     *
     * @param $post : le contenu du formulaire
     *
     * @return bool
     */
    public function saveProfilParent($post, $userName)
    {
        $ok = true;
        $formule = $post['formule'];
        if ($formule == '') {
            $ok = false;
        }
        $nom = $post['nom'];
        if ($nom == '') {
            $ok = false;
        }
        $prenom = $post['prenom'];
        if ($prenom == '') {
            $ok = false;
        }
        $mail = $post['mail'];
        if ($mail == '') {
            $ok = false;
        }
        $lien = $post['lien'];
        if ($lien == '') {
            $ok = false;
        }
        $passwd = $post['passwd'];
        $sqlPasswd = '';
        if ($passwd != '') {
            $passwd2 = $post['passwd2'];
            if ($passwd == $passwd2) {
                $md5pwd = md5($passwd);
                $sqlPasswd = ",md5pwd = :md5pwd ";
            } else {
                $ok = false;
            }
        }
        $nb = 0;
        if ($ok == true) {
            $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'UPDATE '.PFX.'thotParents ';
            $sql .= 'SET formule = :formule, nom = :nom, prenom = :prenom, mail = :mail, lien = :lien ';
            if (isset($md5pwd))
                $sql .= ',md5pwd = :sqlPasswd ';
            $sql .= 'WHERE userName = :userName ';
            $requete = $connexion->prepare($sql);

            $requete->bindParam(':formule', $formule, PDO::PARAM_STR, 4);
            $requete->bindParam(':nom', $nom, PDO::PARAM_STR, 50);
            $requete->bindParam(':prenom', $prenom, PDO::PARAM_STR, 50);
            $requete->bindParam(':mail', $mail, PDO::PARAM_STR, 60);
            $requete->bindParam(':lien', $lien, PDO::PARAM_STR, 20);
            $requete->bindParam(':userName', $userName, PDO::PARAM_STR, 25);

            if (isset($md5pwd))
                $requete->bindParam(':md5pwd', $md5pwd, PDO::PARAM_STR, 40);

            $resultat = $requete->execute();

            $nb = $requete->rowCount();

            self::DeconnexionPDO($connexion);
        }

        return $nb;
    }

    /**
     * recherche les informations sur les parents d'un élève dont on fournit le matricule.
     *
     * @param $matricule : le matricule de l'élève (figure dans la fiche "parent")
     *
     * @return array
     */
    public function infoParents($matricule)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT formule, nom, prenom, userName, mail, lien, md5pwd ';
        $sql .= 'FROM '.PFX.'thotParents ';
        $sql .= "WHERE matricule = '$matricule' ";
        $resultat = $connexions->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $userName = $ligne['userName'];
                $liste[$userName] = $ligne;
            }
        }
        self::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * recherche les informations d'identité d'un parent dont on fournit le userName.
     *
     * @param string $userName
     *
     * @return array
     */
    public function identiteParent($userName)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT tp.matricule, userName, formule, tp.nom, tp.prenom, mail, lien, de.nom AS nomEl, de.prenom AS prenomEl ';
        $sql .= 'FROM '.PFX.'thotParents AS tp ';
        $sql .= 'JOIN '.PFX.'eleves AS de ON de.matricule = tp.matricule ';
        $sql .= "WHERE userName = '$userName' ";
        $resultat = $connexion->query($sql);
        $ligne = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $resultat->fetch();
        }
        self::DeconnexionPDO($connexion);

        return $ligne;
    }

    /**
     * recherche la présence d'un token donné dans la BD pour un utilisateur donné.
     *
     * @param $token : le token cherché
     * @param $user : le nom d'utilisateur correspondant au token
     *
     * @return bool
     */
    public function chercheToken($token, $user)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT user, token, date ';
        $sql .= 'FROM '.PFX.'lostPasswd ';
        $sql .= "WHERE token='$token' AND user='$user' AND date >= NOW() ";
        $sql .= 'LIMIT 1 ';

        $resultat = $connexion->query($sql);
        $userName = '';
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $resultat->fetch();
            $userName = $ligne['user'];
        }
        self::DeconnexionPDO($connexion);

        return $userName;
    }

    /**
     * Enregistre le mot de passe provenant du formulaire et correspondant à l'utilisateur indiqué.
     *
     * @param array  $post     : contenu du formulaire
     * @param string $userName : nom d'utilisateur
     *
     * @return nombre d'enregistrements réussis (normalement 1)
     */
    public function savePasswd($post, $userName)
    {
        $passwd = isset($post['passwd']) ? $post['passwd'] : null;
        $passwd2 = isset($post['passwd2']) ? $post['passwd2'] : null;
        $nb = 0;
        if (($passwd == $passwd2) && ($passwd != '') && (strlen($passwd) >= 9)) {
            $passwd = md5($passwd);
            $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'UPDATE '.PFX.'thotParents ';
            $sql .= "SET md5pwd = '$passwd' ";
            $sql .= "WHERE userName = '$userName' ";
            $resultat = $connexion->exec($sql);
            if ($resultat) {
                $nb = 1;
            }
            // suppression de tous les tokens de cet utilisateur dans la table des mots de passe à récupérer
            $sql = 'DELETE FROM '.PFX.'lostPasswd ';
            $sql .= "WHERE user = '$userName' ";
            $resultat = $connexion->exec($sql);
            self::DeconnexionPDO($connexion);
        }

        return $nb;
    }

    /**
     * Vérification de l'existence d'un utilisateur dont on fournit l'identifiant ou l'adresse mail.
     *
     * @param string $parametre : identifiant ou adresse mail
     * @param string $critere   : 'userName' ou 'mail'
     *
     * @return array : l'identité complète de l'utilisateur ou Null
     */
    public function verifUser($parametre, $critere)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, formule, nom, prenom, userName, mail, md5pwd, lien ';
        $sql .= 'FROM '.PFX.'thotParents ';

        if ($critere == 'userName') {
            $sql .= "WHERE userName = '$parametre' ";
            $sql .= 'LIMIT 1 ';
        } else {
            $sql .= "WHERE mail = '$parametre' ";
            $sql .= 'LIMIT 1 ';
        }

        $resultat = $connexion->query($sql);
        $identite = null;
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $identite = $resultat->fetch();
        }
        self::DeconnexionPDO($connexion);

        return $identite;
    }

    /**
     * Création d'un lien enregistré dans la base de données pour la récupération du mdp.
     *
     * @param void()
     *
     * @return string
     */
    public function createPasswdLink($userName)
    {
        $link = md5(microtime());
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'lostPasswd ';
        $sql .= "SET user='$userName', token='$link', date=NOW() + INTERVAL 2 DAY ";
        $sql .= "ON DUPLICATE KEY UPDATE token='$link', date=NOW() + INTERVAL 2 DAY ";
        $resultat = $connexion->exec($sql);
        self::DeconnexionPDO($connexion);

        return $link;
    }

    /**
     * Envoie un mail de rappel de mot de passe à l'utlisateur dont on a l'adresse.
     *
     * @param $link : le lien de l'adresse où changer le mdp
     * @param $identite	: toutes les informations d'identité de l'utilisateur
     * @param $identiteReseau : informations relatives à la connexion (IP,...)
     *
     * @return bool
     */
    public function mailPasswd($link, $identite, $identiteReseau)
    {
        $jSemaine = strftime('%A');
        $date = date('d/m/Y');
        $heure = date('H:i');

        $smarty = new Smarty();
        $smarty->assign('date', $date);
        $smarty->assign('heure', $heure);
        $smarty->assign('jour', $jSemaine);
        $smarty->assign('expediteur', MAILADMIN);
        $smarty->assign('identiteReseau', $identiteReseau);
        $smarty->assign('identite', $identite);
        $smarty->assign('ECOLE', ECOLE);
        $smarty->assign('ADRESSETHOT', ADRESSETHOT);
        $smarty->assign('link', $link);
        $texteFinal = $smarty->fetch('../mdp/templates/texteMailmdp.tpl');

        require_once '../phpMailer/class.phpmailer.php';
        $mail = new PHPmailer();
        $mail->IsHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->From = MAILADMIN;
        $mail->FromName = ADMINNAME;
        $mail->AddAddress($identite['mail']);
        $mail->Subject = RESET;
        $mail->Body = $texteFinal;

        return !$mail->Send();
    }

    /**
     * Envoi du mail de confirmation d'inscription sur la plate-forme
     *
     * @param $userName : nom d'utilisateur du parent
     *
     * @return bool : le mail a été envoyé
     */
    public function sendConfirmMail($userName) {
        // matricule, formule, nom, prenom, userName, mail, md5pwd, lien
        $identite = $this->verifUser($userName,'userName');

        $smarty = new Smarty();
        $smarty->assign('MAILADMIN', MAILADMIN);
        $smarty->assign('ADMINNAME', ADMINNAME);

        $smarty->assign('identite', $identite);
        $smarty->assign('ECOLE', ECOLE);
        $smarty->assign('ADRESSETHOT', ADRESSETHOT);
        $token = substr($identite['md5pwd'], 0, 20);
        $link = ADRESSETHOT.'/confirm/index.php?token='.$token.'&amp;mail='.$identite['mail'].'&amp;userName='.$identite['userName'];
        $smarty->assign('link', $link);
        $texteMail = $smarty->fetch('../templates/parents/texteConfirmation.tpl');

        require_once INSTALL_DIR.'/phpMailer/class.phpmailer.php';
        $mail = new PHPmailer();
        $mail->IsHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->From = MAILADMIN;
        $mail->FromName = ADMINNAME;
        $mail->AddAddress($identite['mail']);
        $mail->Subject = CONFIRM;
        $mail->Body = $texteMail;

        return !$mail->Send();
    }

    /**
     * Confirmation de l'adresse mail d'un parent dans la base de données
     *
     * @param string $token
     * @param string $mail
     * @param $string userName
     *
     * @return int : -1 = pas trouvé, 0 = déjà confirmé, 1 = confirmation OK
     */
    public function confirmeParent($userName, $mail, $token) {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT userName, mail, confirme ';
        $sql .= 'FROM '.PFX.'thotParents ';
        $sql .= 'WHERE userName =:userName AND mail =:mail AND SUBSTR(md5pwd, 1, 20) =:token ';
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':userName', $userName, PDO::PARAM_STR, 25);
        $requete->bindParam(':mail', $mail, PDO::PARAM_STR, 60);
        $requete->bindParam(':token', $token, PDO::PARAM_STR, 20);
        $resultat = $requete->execute();

        if ($resultat) {
            $ligne = $requete->fetch();
            if ($ligne['userName'] == Null)
                return -1;

            $confirme = $ligne['confirme'];
            if ($confirme == 0) {
                $sql = 'UPDATE '.PFX.'thotParents ';
                $sql .= 'SET confirme = 1 ';
                $sql .= 'WHERE userName =:userName AND mail =:mail AND SUBSTR(md5pwd, 1, 20) =:token ';
                $requete = $connexion->prepare($sql);
                $requete->bindParam(':userName', $userName, PDO::PARAM_STR, 25);
                $requete->bindParam(':mail', $mail, PDO::PARAM_STR, 60);
                $requete->bindParam(':token', $token, PDO::PARAM_STR, 20);
                $resultat = $requete->execute();
                return $resultat;
            }
            else return 0;
        }
        else return -1;
    }

    /**
     * Effacement de toutes les notifications périmées et qui ne sont pas gelées par leur propriétaire.
     *
     * @param void()
     *
     * @return void()
     */
    public function delPerimes()
    {
        $date = date('Y-m-d');
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'thotNotifications ';
        $sql .= "WHERE dateFin < '$date' AND freeze = 0 ";

        $resultat = $connexion->exec($sql);

        // suppression des accusés de lecture devenus sans objet (plus de notification correspondante)
        $sql = 'DELETE FROM '.PFX.'thotAccuse ';
        $sql .= 'WHERE id NOT IN (SELECT id FROM '.PFX.'thotNotifications) ';
        $requete = $connexion->prepare($sql);

        $resultat = $requete->execute();

        self::DeconnexionPDO($connexion);
    }

    /**
     * retourne les éléments d'identité d'un prof dont on fournit l'acronyme.
     *
     * @param $acronyme
     *
     * @return array : formule, nom, prenom, mail
     */
    public function identiteProf($acronyme)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT sexe, nom, prenom, mail ';
        $sql .= 'FROM '.PFX."profs WHERE acronyme='$acronyme' ";
        $resultat = $connexion->query($sql);
        $ligne = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $resultat->fetch();
            if ($ligne['sexe'] == 'F') {
                $ligne['formule'] = 'Mme';
            } else {
                $ligne['formule'] = 'M.';
            }
            $ligne['initiale'] = substr($ligne['prenom'], 0, 1).'. ';
        }
        self::DeconnexionPDO($connexion);

        return $ligne;
    }

    /**
     * renvoie la liste des locaux pour une RP de date donnée.
     *
     * @param $date
     *
     * @return array
     */
        public function listeLocaux($idRP) {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT acronyme, local ';
        $sql .= 'FROM '.PFX.'thotRpLocaux ';
        $sql .= 'WHERE idRP = :idRP ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);

        $resultat = $requete->execute();
        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $acronyme = $ligne['acronyme'];
                $liste[$acronyme] = $ligne['local'];
            }
        }
        self::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * Renvoie la liste des dates de réunions de parents prévues.
     *
     * @param $active : la réunion de parents est active et donc visible
     * @param $ouvert : la réunion de parents est ouverte à l'inscription
     *
     * @return array
     */
    public function listeDatesReunion($active = 0, $ouvert = 0)
    {
        // une réunion de parents inactive n'est certainement pas ouverte
        $ouvert = ($active == 0) ? 0 : $ouvert;
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT idRP, DATE_FORMAT(date,'%d/%m/%Y') AS date, ouvert, active, notice ";
        $sql .= 'FROM '.PFX.'thotRp ';
        if ($ouvert != 0 && $active != 0) {
            $sql .= "WHERE active = :active AND ouvert = :ouvert ";
        } elseif ($active != 0) {
            $sql .= "WHERE active = '1' ";
        } elseif ($ouvert != 0) {
            $sql .= "WHERE ouvert = '1' ";
        }
        $requete = $connexion->prepare($sql);

        if ($ouvert != 0 && $active != 0){
            $requete->bindParam(':ouvert', $ouvert, PDO::PARAM_INT);
            $requete->bindParam(':active', $active, PDO::PARAM_INT);
        } elseif ($active != 0) {
            $requete->bindParam(':active', $active, PDO::PARAM_INT);
        } elseif ($ouvert !=0) {
            $requete->bindParam(':ouvert', $ouvert, PDO::PARAM_INT);
        }

        $resultat = $requete->execute();
        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $idRP = $ligne['idRP'];
                $liste[$idRP] = $ligne;
            }
        }

        self::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie la liste des RV pris pour un élève donné et pour une RP donnée.
     *
     * @param int $matricule : le matricule de l'élève
     * @param int $idRP : l'identifiant de la réunion de parents
     *
     * @return array
     */
    public function getRVeleve($matricule, $idRP) {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT idRP, idRV, rv.matricule, DATE_FORMAT(heure,'%H:%i') AS heure, rv.acronyme, ";
        $sql .= 'dp.sexe, dp.nom, dp.prenom, userParent, ';
        $sql .= "'' AS formule, '' AS nomParent, '' AS prenomParent ,'' AS lien ";
        $sql .= 'FROM '.PFX.'thotRpRv AS rv ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON rv.acronyme = dp.acronyme ';
        $sql .= 'WHERE rv.matricule = :matricule AND idRP = :idRP ';
        $sql .= 'ORDER BY heure ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $listeBrute = array();
        $resultat = $requete->execute();;
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $heure = $ligne['heure'];
                // on suppose qu'il n'y a pas deux RV à la même période
                $listeBrute[$heure] = $ligne;
            }
        }
        self::deconnexionPDO($connexion);

        // établir le lien avec la table des parents
        $listeUserParents = array_filter(array_column($listeBrute, 'userParent'));
        $listeParents = $this->listeParentsUserNames($listeUserParents);

        foreach ($listeBrute as $heure => $data) {
            $userParent = $data['userParent'];
            // s'il y a un userParent défini (inscription réalisée par un parent et non par le secrétariat)
            if ($userParent != '') {
                $parent = $listeParents[$userParent];
                $listeBrute[$heure]['formule'] = $parent['formule'];
                $listeBrute[$heure]['nomParent'] = $parent['nom'];
                $listeBrute[$heure]['prenomParent'] = $parent['prenom'];
                $listeBrute[$heure]['mail'] = $parent['mail'];
                $listeBrute[$heure]['lien'] = $parent['lien'];
                $listeBrute[$heure]['userName'] = $parent['userName'];
            }
        }

        // établir le lien avec la table des locaux
        $listeLocaux = $this->listeLocaux($idRP);
        foreach ($listeBrute as $heure => $data) {
            $acronyme = $listeBrute[$heure]['acronyme'];
            $listeBrute[$heure]['local'] = (isset($listeLocaux[$acronyme])) ? $listeLocaux[$acronyme] : null;
        }

        return $listeBrute;
    }

    /**
     * renvoie la liste des RV pour l'élève $matricule classés par idRV lors de la réunion $idRP
     *
     * @param int $matricule
     * @param int $idRP
     *
     * @return array
     */
    public function getListeRVEleve($matricule){
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idRV, idRP, acronyme, heure, userParent ';
        $sql .= 'FROM '.PFX.'thotRpRV ';
        $sql .= 'WHERE idRP = :idRP AND matricule = :matricule ';
        $sql .= 'ORDER BY heure ASC ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idRV = $ligne['idRV'];
                $liste[$idRV] = $ligne;
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renoive la liste des RV du prof $acronyme pour la réunion $idRP
     * en les indexant par heure
     *
     * @param int $idRP
     * @param string $acronyme
     *
     * @return array
     */
    public function getListRVProf($idRP, $acronyme){
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idRV, heure, acronyme, matricule, userParent, dispo ';
        $sql .= 'FORM '.PFX.'thotRpRv ';
        $sql .= 'WHERE idRP = :idRP AND acronyme = :acronyme ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if (resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $heure = $ligne['heure'];
                $liste[$heure] = $ligne;
                }
        }
        Application::deconnexionPDO($connexion);
        return $liste;
    }

    /**
     * retourne la liste d'attentes des RV de l'élève dont on fournit le matricule pour la réunion dont on indique la date.
     *
     * @param $matricule : matricule de l'élève
     * @param $date : date de la réunion
     *
     * @return array
     */
    public function getListeAttenteEleve($matricule, $idRP) {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT at.acronyme, dp.sexe, dp.nom AS nomProf, dp.prenom AS prenomProf, ';
        $sql .= 'at.userName, periode, tp.formule, tp.nom AS nomParent, tp.prenom AS prenomParent ';
        $sql .= 'FROM '.PFX.'thotRpAttente AS at ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON dp.acronyme = at.acronyme ';
        $sql .= 'LEFT JOIN '.PFX.'thotParents AS tp ON tp.userName = at.userName ';
        $sql .= 'WHERE idRP = :idRP AND at.matricule = :matricule ';
        $sql .= 'ORDER BY periode, acronyme ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $periode = $ligne['periode'];
                // on suppose qu'il n'y a pas deux RV à la même période
                $liste[] = $ligne;
            }
        }
        self::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * Envoie en liste d'attente un élève dont on donne le matricule,
     * pour le prof dont on indique l'acronyme
     * pour la RP dont on indique la date avec la période indiquée (entre 1 et 3).
     *
     * @param $matricule: le matricule de l'élève
     * @param $acronyme : l'acronyme du prof
     * @param $date : la date de la RP
     * @param $periode : la période choisie pour un RV éventuel
     *
     * @return int : le nombre d'insertions (en principe, 1 ou 0 si échec de l'enregistrement)
     */
    public function setListeAttenteEleve($userName, $matricule, $acronyme, $idRP, $periode) {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT IGNORE INTO '.PFX.'thotRpAttente ';
        $sql .= 'SET userName = :userName, matricule = :matricule, acronyme = :acronyme, idRP = :idRP, periode = :periode ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':userName', $userName, PDO::PARAM_STR, 20);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':acronyme', $acronyme, PDO::PARAM_STR, 7);
        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);
        $requete->bindParam(':periode', $periode, PDO::PARAM_INT);

        $resultat = $requete->execute();

        self::deconnexionPDO($connexion);

        return $resultat;
    }



    /**
     * Suppression d'une présence en liste d'attente pour le prof dont on fournit l'acronyme et pour la période donnée.
     *
     * @param $acronyme : l'acronyme du prof
     * @param $periode : la période demandée pour le RV éventuel
     *
     * @return int : le nombre de suppressions (0 ou 1)
     */
    public function delAttente($acronyme, $periode)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'thotRpAttente ';
        $sql .= 'WHERE acronyme = :acronyme AND periode = :periode ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':periode', $periode, PDO::PARAM_INT);
        $requete->bindParam(':acronyme', $acronyme, PDO::PARAM_STR, 7);

        $requete->execute();

        $nb = $requete->rowCount();

        self::deconnexionPDO($connexion);

        return $nb;
    }

    /**
     * retourne la liste des cours d'un élève dont on fournit le matricule.
     *
     * @param $matricule
     *
     * @return array
     */
    public function listeProfsCoursEleve($matricule)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT ec.coursGrp, SUBSTR(ec.coursGrp,1,LOCATE('-',ec.coursGrp)-1)  AS cours, ";
        $sql .= 'libelle, nbheures, pc.acronyme, nom, prenom, sexe ';
        $sql .= 'FROM '.PFX.'elevesCours AS ec ';
        $sql .= 'JOIN '.PFX.'profsCours AS pc ON pc.coursGrp = ec.coursGrp ';
        $sql .= 'JOIN '.PFX."cours AS dc ON dc.cours = SUBSTR(ec.coursGrp,1,LOCATE('-',ec.coursGrp)-1) ";
        $sql .= 'JOIN '.PFX.'profs AS dp ON dp.acronyme = pc.acronyme ';
        $sql .= "WHERE matricule = '$matricule' ";
        $sql .= 'ORDER BY nom, prenom ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $acronyme = $ligne['acronyme'];
                $liste[$acronyme] = $ligne;
            }
        }
        self::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne une liste cohérente du personnel d'encadrement (prof + direction et al)
     * à partir de la liste des profs (avec cours) et de la liste du personnel à statut "spécial".
     *
     * @param $listeProfsCours : liste des profs avec leurs coursGrp
     * @param $listeStatutsSpeciaux : liste des membres du personnel à statut "spécial"
     *
     * @return array
     */
    public function encadrement($listeProfsCours, $listeStatutsSpeciaux)
    {
        $listeEncadrement = $listeProfsCours;
        foreach ($listeStatutsSpeciaux as $acronyme => $data) {
            $listeEncadrement[$acronyme] = array(
                'coursGrp' => '',
                'cours' => '',
                'libelle' => $data['titre'],
                'nbheures' => '',
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'sexe' => $data['sexe'],
            );
        }

        return $listeEncadrement;
    }

    /**
     * renvoie la liste des profs impliqués dans la RP ciblée $idRP
     *
     * @param int $idRP
     *
     * @return array
     */
    public function listeProfsCibles($idRP){
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT rprv.acronyme, nom, prenom, sexe ';
        $sql .= 'FROM '.PFX.'thotRpRv AS rprv ';
        $sql .= 'JOIN '.PFX.'profs AS profs ON profs.acronyme = rprv.acronyme ';
        $sql .= 'WHERE rprv.idRP = :idRP ';
        $sql .= 'ORDER BY nom, prenom ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);

        $liste =array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $acronyme = $ligne['acronyme'];
                $liste[$acronyme] = $ligne;
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * recherche les informations d'un RV dont on fournit l'idRP et $idRV
     *
     * @param int $idRP
     * @param int $idRV : l'identifiant du RV
     *
     * @return array
     */
    public function getInfoRV($idRP, $idRV)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT acronyme, rv.matricule, formule, nom, prenom, userParent, ';
        $sql .= "DATE_FORMAT(heure,'%Hh%i') AS heure, dispo, mail ";
        $sql .= 'FROM '.PFX.'thotRpRv AS rv ';
        $sql .= 'LEFT JOIN '.PFX.'thotParents AS tp ON tp.matricule = rv.matricule ';
        $sql .= 'WHERE idRV = :idRV AND idRP = :idRP ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRV', $idRV, PDO::PARAM_INT);
        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);

        $ligne = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
        }
        self::deconnexionPDO($connexion);

        return $ligne;
    }

    /**
     * inscription à un RV donné des parents d'un élève dont on fournit le amtricule
     * procédure pour l'admin afin d'inscrire un parent dont on a reçu une demande de RV "papier".
     * le nombre maximum de rendez-vous est passé en paramètre.
     *
     * @param int $idRP : identifiant de la RP
     * @param int $idRV : l'identifiant du RV
     * @param $matricule : le matricule de l'élève dont on inscrit un parent
     * @param $max : le nombre max de RV
     *
     * @return int : -1  si inscription over quota ($max), 0 si écriture impossible dans la BD, 1 si tout OK
     */
    public function inscriptionEleve($idRP, $idRV, $matricule, $userParent = null) {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'UPDATE '.PFX.'thotRpRv ';
        $sql .= 'SET matricule = :matricule, userParent = :userParent, dispo = 0 ';
        $sql .= 'WHERE idRV = :idRV AND idRP = :idRP ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);
        $requete->bindParam(':idRV', $idRV, PDO::PARAM_INT);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':userParent', $userParent, PDO::PARAM_STR, 25);

        $resultat = $requete->execute();

        self::deconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * retourne un RV éventuel à l'heure $heure dans la RP $idRP pour l'élève $matricule
     *
     * @param int $idRP
     * @param string $heure
     * @param int $matricule
     *
     * @return array
     */
    public function rvRp4heure($idRP, $matricule, $heure){
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT heure ';
        $sql .= 'FROM '.PFX.'thotRpRv ';
        $sql .= 'WHERE idRP = :idRP AND heure = :heure AND matricule = :matricule ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':heure', $heure, PDO::PARAM_STR, 5);

        $liste = Null;
        $resultat = $requete->execute();
        if ($resultat) {
            $liste = $requete->fetchAll();
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * Effacement d'un RV dont on fournit l'identifiant.
     *
     * @param int $idRP : identifiant de la RP
     * @param int $idRV : l'identifiant du RV
     *
     * @return int : le nombre de suppressions (0 ou 1)
     */
    public function delRV($idRP, $idRV)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'UPDATE '.PFX.'thotRpRv ';
        $sql .= 'SET matricule = Null, userParent = Null, dispo = 1 ';
        $sql .= 'WHERE idRP = :idRP AND idRV = :idRV ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);
        $requete->bindParam(':idRV', $idRV, PDO::PARAM_INT);

        $resultat = $requete->execute();

        $nb = $requete->rowCount();

        self::deconnexionPDO($connexion);

        return $nb;
    }

    /**
     * recherche les caractéristiques d'une réunion de parents dont on fournit la date.
     *
     * @param $date
     *
     * @return array
     */
    public function getInfoRp($idRP) {
        $heuresLimites = $this->heuresLimite($idRP);
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT rp.idRP, date, ouvert, active, notice, typeRP, ';
        $sql .= "DATE_FORMAT(minPer1,'%H:%i') AS minPer1, DATE_FORMAT(maxPer1,'%H:%i') AS maxPer1, ";
        $sql .= "DATE_FORMAT(minPer2,'%H:%i') AS minPer2, DATE_FORMAT(maxPer2,'%H:%i') AS maxPer2, ";
        $sql .= "DATE_FORMAT(minPer3,'%H:%i') AS minPer3, DATE_FORMAT(maxPer3,'%H:%i') AS maxPer3 ";
        $sql .= 'FROM '.PFX.'thotRp AS rp ';
        $sql .= 'JOIN '.PFX.'thotRpHeures AS rh ON rh.idRP = rp.idRP ';
        $sql .= 'WHERE rp.idRP = :idRP ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);

        $resultat = $requete->execute();
        $ligne = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
        }
        self::deconnexionPDO($connexion);
        $tableau = array(
            'idRP' => $idRP,
            'date' => $ligne['date'],
            'heuresLimites' => $heuresLimites,
            'typeRP' => $ligne['typeRP'],
            'generalites' => array('ouvert' => $ligne['ouvert'], 'active' => $ligne['active'], 'notice' => $ligne['notice']),
            'heures' => array(
                'minPer1' => $ligne['minPer1'],
                'minPer2' => $ligne['minPer2'],
                'minPer3' => $ligne['minPer3'],
                'maxPer1' => $ligne['maxPer1'],
                'maxPer2' => $ligne['maxPer2'],
                'maxPer3' => $ligne['maxPer3'], ),
            );

        return $tableau;
    }

    /**
     * retourne les heures de début et de fin d'une réunion dont on fournit la date.
     *
     * @param $date
     *
     * @return array : les deux limites
     */
    public function heuresLimite($idRP)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT MIN(heure) AS min, MAX(heure) AS max ';
        $sql .= 'FROM '.PFX.'thotRpRv ';
        $sql .= 'WHERE idRP = :idRP ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);

        $ligne = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
        }
        self::deconnexionPDO($connexion);

        return $ligne;
    }

        /**
         * retourne la liste des nom, prenom et classe des élèves dont on passe la liste des matricules.
         *
         * @param $matricules : array|integer
         *
         * @return array : trié sur les matricules
         */
        public function listeElevesMatricules($listeEleves)
        {
            if (is_array($listeEleves)) {
                $listeMatricules = implode(',', $listeEleves);
            } else {
                $listeMatricules = $listeEleves;
            }

            $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'SELECT matricule, groupe, nom, prenom ';
            $sql .= 'FROM '.PFX.'eleves ';
            $sql .= "WHERE matricule IN ($listeMatricules) ";
            $resultat = $connexion->query($sql);
            $listeEleves = array();
            if ($resultat) {
                $resultat->setFetchMode(PDO::FETCH_ASSOC);
                while ($ligne = $resultat->fetch()) {
                    $matricule = $ligne['matricule'];
                    $listeEleves[$matricule] = $ligne;
                }
            }
            self::deconnexionPDO($connexion);

            return $listeEleves;
        }

        /**
         * retourne la liste des nom, prenom, mail des parents dont on fournit la liste des userNames.
         *
         * @param array (ou pas) de la liste des userNames
         *
         * @return array
         */
        public function listeParentsUserNames($listeUserNames)
        {
            if (is_array($listeUserNames)) {
                $listeUserNamesString = "'".implode("','", $listeUserNames)."'";
            } else {
                $listeUserNamesString = "'".$listeUserNames."'";
            }
            $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'SELECT formule, nom, prenom, mail, lien, userName ';
            $sql .= 'FROM '.PFX.'thotParents ';
            $sql .= "WHERE userName IN ($listeUserNamesString) ";

            $resultat = $connexion->query($sql);
            $listeParents = array();
            if ($resultat) {
                $resultat->setFetchMode(PDO::FETCH_ASSOC);
                while ($ligne = $resultat->fetch()) {
                    $userName = $ligne['userName'];
                    $listeParents[$userName] = $ligne;
                }
            }
            self::deconnexionPDO($connexion);

            return $listeParents;
        }

    /**
     * renvoie la liste des RV pris pour un prof donné et pour une date donnée.
     *
     * @param $acronyme : l'acronyme du profs
     * @param $date : la date de la réunion de parents
     *
     * @return array
     */
    public function getRVprof($acronyme, $idRP) {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT idRV, rv.matricule, userParent, TIME_FORMAT(heure,'%H:%i') AS heure, dispo, ";
        $sql .= "'' AS formule, '' AS nomParent, '' AS prenomParent, '' AS userName, '' AS mail, '' AS lien, ";
        $sql .= "'' AS nom, '' AS prenom, '' AS groupe ";
        $sql .= 'FROM '.PFX.'thotRpRv AS rv ';
        $sql .= 'WHERE acronyme = :acronyme AND idRP = :idRP ';
        $sql .= 'ORDER BY heure ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);
        $requete->bindParam(':acronyme', $acronyme, PDO::PARAM_STR, 7);

        $listeBrute = array();
        $resultat = $requete->execute();

        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $idRV = $ligne['idRV'];
                $matricule = $ligne['matricule'];
                $listeBrute[$idRV] = $ligne;
            }
        }
        self::deconnexionPDO($connexion);

        // retrouver les caractéristiques des élèves qui figurent dans le tableau des RV
        $listeMatricules = array_filter(array_column($listeBrute, 'matricule'));
        $listeEleves = $this->listeElevesMatricules($listeMatricules);

        // retrouver les caractéristiques des parents qui figurent dans le tableau des RV
        $listeUserParents = array_filter(array_column($listeBrute, 'userParent'));
        $listeParents = $this->listeParentsUserNames($listeUserParents);

        // recombinaison des trois listes
        foreach ($listeBrute as $idRV => $data) {
            if ($data['matricule'] != '') {
                $matricule = $data['matricule'];
                $eleve = $listeEleves[$matricule];
                $listeBrute[$idRV]['nom'] = $eleve['nom'];
                $listeBrute[$idRV]['prenom'] = $eleve['prenom'];
                $listeBrute[$idRV]['groupe'] = $eleve['groupe'];
            }
            if ($data['userParent'] != '') {
                $userName = $data['userParent'];
                $parent = $listeParents[$userName];
                $listeBrute[$idRV]['formule'] = $parent['formule'];
                $listeBrute[$idRV]['nomParent'] = $parent['nom'];
                $listeBrute[$idRV]['prenomParent'] = $parent['prenom'];
                $listeBrute[$idRV]['mail'] = $parent['mail'];
                $listeBrute[$idRV]['lien'] = $parent['lien'];
                $listeBrute[$idRV]['userName'] = $parent['userName'];
            }
        }

        return $listeBrute;
    }

    /**
     * retourne les périodes pour les listes d'attente pour une RP dont on donne la date.
     *
     * @param $date
     *
     * @return array
     */
    public function getListePeriodes($idRP)
    {
        $infoRp = $this->getInfoRp($idRP);
        $liste = $infoRp['heures'];
        $listeHeures = array(
            '1' => array('min' => $liste['minPer1'], 'max' => $liste['maxPer1']),
            '2' => array('min' => $liste['minPer2'], 'max' => $liste['maxPer2']),
            '3' => array('min' => $liste['minPer3'], 'max' => $liste['maxPer3']),
        );

        return $listeHeures;
    }

    /**
     * vérification que l'id passé est compatible avec la date de la RP envisagée.
     *
     * @param $id : l'id de la réunion de parent
     * @param $date
     *
     * @return bool
     */
    public function validIdRpIdRv($idRP, $idRV) {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT * FROM '.PFX.'thotRpRv ';
        $sql .= 'WHERE idRV = :idRV AND idRP = :idRP ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);
        $requete->bindParam(':idRV', $idRV, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $liste[] = $ligne;
            }
        }
        self::deconnexionPDO($connexion);

        return count($liste) == 1;
    }

    /**
     * vérification que lA DATE passéE est vraiment une date de RP.
     *
     * @param $date
     *
     * @return bool
     */
    public function validDate($idRP) {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT * FROM '.PFX.'thotRp ';
        $sql .= 'WHERE idRP = :idRP ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            while ($ligne = $requete->fetch()) {
                $liste[] = $ligne;
            }
        }
        self::deconnexionPDO($connexion);

        return count($liste) == 1;
    }

    /**
     * Vérifier que le RV avec le prof donné n'est pas un doublon (déjà RV).
     *
     * @param $matricule : le matricule de l'élève
     * @param $acronyme  : l'acronyme du profs
     * @param $date: la date de la RP
     *
     * @return bool
     */
    public function listeProfsRencontres($matricule, $idRP) {
        $listeRVEleve = $this->getRVeleve($matricule, $idRP);

        $listeProfsRencontres = array();
        // recherche de tous les profs rencontrés pour l'élève
        foreach ($listeRVEleve as $heure => $data){
            $listeProfsRencontres[] = $data['acronyme'];
        }

        return $listeProfsRencontres;
    }

    /**
     * retourne la liste des membres du peresonnel à statut spécial (direction, PMS,...)
     * qui doivent apparaître dans liste des RV possibles.
     *
     * @param void()
     *
     * @return array
     */
    public function listeStatutsSpeciaux()
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT rv.acronyme,  nom, prenom, sexe, titre ';
        $sql .= 'FROM '.PFX.'thotRpRv AS rv ';
        $sql .= 'JOIN '.PFX.'profs AS dp ON dp.acronyme = rv.acronyme ';
        $sql .= "WHERE rv.statut = 'dir' ";
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $acronyme = $ligne['acronyme'];
                $liste[$acronyme] = $ligne;
            }
        }
        self::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * vérifie qu'un membre du personnel dont l'acronyme est fourni existe.
     *
     * @param $acronyme
     *
     * @return bool
     */
    public function profExiste($acronyme)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT acronyme FROM '.PFX.'profs ';
        $sql .= "WHERE acronyme='$acronyme' ";
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $resultat->fetch();
            $acro = $ligne['acronyme'];
        }

        self::deconnexionPDO($connexion);

        return $acronyme = $acro;
    }

    /**
     * Vérifier que le propriétaire de l'entrevue $id est bien l'utilisateur actuel $userName.
     *
     * @param int $idRP : l'identifiant de la RP
     * @param int $idRV : identifiant du RV
     * @param bool
     *
     * @return bool
     */
    public function isOwnerRV($idRP, $idRV, $userName)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT userParent ';
        $sql .= 'FROM '.PFX.'thotRpRv ';
        $sql .= 'WHERE idRP = :idRP AND idRV = :idRV ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);
        $requete->bindParam(':idRV', $idRV, PDO::PARAM_INT);

        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
            $userParent = $ligne['userParent'];
        }

        self::deconnexionPDO($connexion);

        return $userParent == $userName;
    }

    /**
     * Vérifier que le propriétaire de la demande de liste d'attente ($acronyme du prof et période) est bien l'utilisateur actuel $userName.
     *
     * @param $acronyme : acronyme du prof
     * @param $periode : période demandée
     * @param $userName : nom de l'utilisateur courant
     *
     * @return bool
     */
    public function isOwnerAttente($acronyme, $periode, $userParent)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT userName ';
        $sql .= 'FROM '.PFX.'thotRpAttente ';
        $sql .= "WHERE acronyme='$acronyme' AND periode='$periode' AND userName='$userParent' ";
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $resultat->fetch();
            $userName = $ligne['userName'];
        }

        self::deconnexionPDO($connexion);

        return $userParent == $userName;
    }

    /**
     * retourne le nombre de RV déjà pris pour la RP de la date donnée.
     *
     * @param $date : la date de la RP
     *
     * @return int
     */
    public function nbRv($idRP) {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT COUNT(*) AS nb ';
        $sql .= 'FROM '.PFX."thotRpRv WHERE matricule != '' AND idRP = :idRP ";
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idRP', $idRP, PDO::PARAM_INT);

        $resultat = $requete->execute();

        if ($resultat) {
            $ligne = $requete->fetch();
        }
        self::deconnexionPDO($connexion);

        return $ligne['nb'];
    }

// Fonctions pour la gestion des RV hors des réunions de parents **************************

    /**
     * retourne les dates pour lesquelles un RV est encore possible avec le membre du personnel mentionné.
     *
     * @param $acronyme : le membre du personnel
     *
     * @return array : la liste des dates au double format PHP et MySQL
     */
    public function listeDatesRV($acronyme)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT date ';
        $sql .= 'FROM '.PFX.'thotRv ';
        $sql .= "WHERE contact = '$acronyme' AND md5conf is Null ";

        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $date = $ligne['date'];
                $jourSemaine = $this->jourSemaineMySQL($date);
                $datePHP = $this->datePHP($ligne['date']);
                $ligne = array('date' => $date, 'datePHP' => $datePHP, 'jourSemaine' => $jourSemaine);
                $liste[] = $ligne;
            }
        }

        self::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie la liste des RV disponibles pour une date donnée.
     *
     * @param $date : la date visée
     * @param $confirme : boolean false (défaut) si l'on ne souhatie que les plages encore libres
     *
     * @return array
     */
    public function listeHeuresRV($date, $confirme = false)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, heure ';
        $sql .= 'FROM '.PFX.'thotRv ';
        $sql .= "WHERE date = '$date' ";
        if ($confirme == false) {
            $sql .= 'AND md5conf is Null ';
        }
        $sql .= 'ORDER BY heure ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $id = $ligne['id'];
                $liste[$id] = $ligne;
            }
        }

        self::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie les caractéristiques d'un moment de RV dont on fournit l'identifiant.
     *
     * @param $id : l'identifiant du RV dans la BD
     *
     * @return array
     */
    public function getRvById($id)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT id, contact, DATE_FORMAT(date,'%d/%m/%Y') AS date, DATE_FORMAT(heure,'%Hh%i') AS heure, ";
        $sql .= 'nom, prenom, email, dateHeure, md5conf, confirme ';
        $sql .= 'FROM '.PFX.'thotRv ';
        $sql .= "WHERE id='$id' ";
        $resultat = $connexion->query($sql);
        $ligne = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $resultat->fetch();
        }
        self::deconnexionPDO($connexion);

        return $ligne;
    }

    /**
     * renvoie les caractéristiques d'un moment de RV dont on fournit le token de réservation.
     *
     * @param $token : le token de réservation du RV dans la BD
     *
     * @return array
     */
    public function getRvByToken($token)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT id, contact, DATE_FORMAT(date,'%d/%m/%Y') AS date, DATE_FORMAT(heure,'%Hh%i') AS heure, ";
        $sql .= 'nom, prenom, email, dateHeure, md5conf, confirme ';
        $sql .= 'FROM '.PFX.'thotRv ';
        $sql .= "WHERE md5conf='$token' ";
        $resultat = $connexion->query($sql);
        $ligne = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $resultat->fetch();
        }
        self::deconnexionPDO($connexion);

        return $ligne;
    }

    /**
     * vérifier qu'un moment de RV est encore libre.
     *
     * @param $id : l'identifiant du RV
     *
     * @return bool
     */
    public function isFreeRV($id)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT md5conf, confirme ';
        $sql .= 'FROM '.PFX.'thotRv ';
        $sql .= "WHERE id='$id' ";
        $resultat = $connexion->query($sql);
        // si l'identifiant n'existe pas, le RV n'est pas disponible
        $free = false;
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $resultat->fetch();
            $md5conf = $ligne['md5conf'];
            $confirme = $ligne['confirme'];
            // si le RV n'est ni en attente de confirmation, ni confirme, il est disponible
            $free = ($md5conf == null);
        }
        self::deconnexionPDO($connexion);

        return $free;
    }

    /**
     * enregistrement d'une réservation pour un RV; la confirmation devra suivre.
     *
     * @param $post : les informations provenant du formulaire de réservation du RV
     *
     * @return string: l'adresse mail déclarée encryptée en md5
     */
    public function saveRV($post)
    {
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        if ($this->isFreeRV($id)) {
            $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'UPDATE '.PFX.'thotRv ';
            $sql .= 'SET nom=:nom, prenom=:prenom, email=:email, dateHeure=NOW(), md5conf=:md5conf ';
            $sql .= 'WHERE id=:id ';
            $requete = $connexion->prepare($sql);
            $md5conf = md5($post['email'].time());
            $data = array(
                ':nom' => $post['nom'],
                ':prenom' => $post['prenom'],
                ':email' => $post['email'],
                ':md5conf' => $md5conf,
                ':id' => $id,
            );
            $resultat = $requete->execute($data);

            self::deconnexionPDO($connexion);

            return $md5conf;
        }

        return;
    }

    /**
     * Confirmation du RV correspondant à un id donné.
     *
     * @param $id
     *
     * @return string : le token si l'opération a réussi sinon une chaîne vide
     */
    public function confirmRv($id)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'UPDATE '.PFX.'thotRv ';
        $sql .= "SET confirme='1' ";
        $sql .= "WHERE id='$id' ";
        $resultat = $connexion->exec($sql);
        if ($resultat) {
            $nb = 1;
        } else {
            $nb = 0;
        }
        self::deconnexionPDO($connexion);

        return $nb;
    }

    /**
     * mise à jour des demandes de RV non confirmées (fonction appelée à chaque entrée sur l'application).
     *
     * @param $heures : le délai de péremption en heures
     */
    public function refreshTableRv($heures)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        // remise à zéro des lignes périmées depuis plus de 4 heures et qui n'ont jamais été confirmées
        $sql = "UPDATE '.PFX.'thotRv SET md5conf = Null WHERE (dateHeure < NOW() - INTERVAL $heures HOUR AND confirme = 0) ";
        $connexion->exec($sql);
        self::deconnexionPDO($connexion);
    }

    // fonctions pour la gestion des e-docs

    /**
     * retourne la liste des e-docs disponibles pour un élève dont on fournit le matricules.
     *
     * @param $matricule
     *
     * @return array
     */
    public function listeEdocs($matricule)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT matricule, edoc, date ';
        $sql .= 'FROM '.PFX.'thotEdocs ';
        $sql .= "WHERE matricule = '$matricule' ";

        $liste = array();
        $resultat = $connexion->query($sql);
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $matricule = $ligne['matricule'];
                $edoc = $ligne['edoc'];
                $date = $this->datePhp($ligne['date']);
                $liste[$matricule][] = array('date' => $date, 'doc' => $edoc);
            }
        }
        self::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne la date déclarée pour un e-doc donné pour un élève donné.
     *
     * @param $matricule
     * @param $typeEdoc (pia, competences)
     *
     * @return string
     */
    public function getDocDate($matricule, $typeDoc)
    {
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT date FROM '.PFX.'thotEdocs ';
        $sql .= "WHERE matricule='$matricule' AND edoc='$typeDoc' ";
        $resultat = $connexion->query($sql);
        $date = null;
        if ($resultat) {
            $ligne = $resultat->fetch();
            $date = $ligne['date'];
        }
        self::deconnexionPDO($connexion);

        return self::datePHP($date);
    }

    /**
     * retourne l'année scolaire en cours Ex: 2017-2018 sur base de la date courante
     *
     * @param void
     *
     * @return string
     */
    public function getCurrentAnneeScolaire(){
        $date = $this->dateNow();
        $date = explode('/', $date);
        if ((int)$date[1] >= 9)
            $anScol = sprintf('%s-%s', $date[2], $date[2]+1);
            else $anScol = sprintf('%s-%s', $date[2]-1, $date[2]);
        return $anScol;
    }

    /**
     * retourne l'identité d'un élève dont on fournit le matricule
     *
     * @param int $matricule
     *
     * @return array
     */
    public function getIdentiteEleve($matricule){
        $connexion = self::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT nom, prenom, groupe ';
        $sql .= 'FROM '.PFX.'eleves ';
        $sql .= 'WHERE matricule = :matricule ';

        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $identite = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $identite = $requete->fetch();
        }


        Application::deconnexionPDO($connexion);

        return $identite;
    }

}
