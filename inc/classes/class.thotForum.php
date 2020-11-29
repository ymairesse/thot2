<?php

/**
 *
 */
class ThotForum
{

	CONST acceptedTags = "<a><b><i><u><span><iframe><img><div><table><tbody><tr><td><ul><li><br><p><strike><pre><h1><h2><pre><blockquote><sub><sup>";

    function __construct()
    {
        // code...
    }

    /**
     * renvoie la liste des sujets dans lesquels l'élève $matricule a été invité
     *
     * @param int $matricule
     * @param string $classe
     * @param int $niveau
     * @param array $listeMatieres
     * @param array $listeCoursGrp
     * @param array $listeGroupes
     *
     * @return array
     */
    public function getListeSujets4eleve($matricule, $classe, $niveau, $listeMatieres, $listeCoursGrp, $listeGroupes=Null){
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", $listeCoursGrp)."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }

        if (is_array($listeMatieres)) {
            $listeMatieresString = "'".implode("','", array_keys($listeMatieres))."'";
        } else {
            $listeMatieresString = "'".$listeMatieres."'";
        }

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT access.idCategorie, access.idSujet, type, cible, libelle, forums.parentId, ';
        $sql .= 'sujet, sujets.acronyme, dateCreation, modifParEleve, modifParAuteur, sexe, nom, prenom, forumActif, ';
        $sql .= 'DATE_FORMAT(dateCreation, "%d/%m/%Y") AS ladate, DATE_FORMAT(dateCreation, "%H:%i") AS heure ';
        $sql .= 'FROM '.PFX.'thotForumsAccess AS access ';
        $sql .= 'JOIN '.PFX.'thotForums AS forums ON forums.idCategorie = access.idCategorie ';
        $sql .= 'JOIN '.PFX.'thotForumsSujets AS sujets ON sujets.idCategorie = access.idCategorie AND sujets.idSujet = access.idSujet ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = sujets.acronyme ';
		$sql .= 'WHERE userStatus = "eleves" AND cible LIKE :niveau OR cible IN ("all", :matricule, :classe, '.$listeMatieresString.', '.$listeCoursGrpString.') ';
        $sql .= 'AND forumActif = 1 ';
        $sql .= 'ORDER BY cible ';

        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':classe', $classe, PDO::PARAM_STR, 7);
        $requete->bindParam(':niveau', $niveau, PDO::PARAM_INT);

        $liste = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $type = $ligne['type'];
                $cible = $ligne['cible'];
                $libelle = $ligne['libelle'];
                $idCategorie = $ligne['idCategorie'];
                $idSujet = $ligne['idSujet'];
                $ligne['nomProf'] = $this->nomProf($ligne['sexe'], $ligne['prenom'], $ligne['nom']);
                $liste[$type][$cible][$idCategorie][$idSujet] = $ligne;
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }


    /**
     * recherche la liste des posts d'un élève dont on fournit le matricule, la classe
     * la liste des matières suivies, la liste des cours suivsi, la liste des groupes dont il fait partie
     *
     * @param int $matricule
     * @param string $classe
     * @param int $niveau
     * @param array $listeMatieres
     * @param array $listeCoursGrp
     * @param array $listeGroupes
     */
    public function getListeCatSujets4user($matricule, $classe, $niveau, $listeMatieres, $listeCoursGrp, $listeGroupes = Null) {
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }
        if (is_array($listeMatieres)) {
            $listeMatieresString = "'".implode("','", array_keys($listeMatieres))."'";
        } else {
            $listeMatieresString = "'".$listeMatieres."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT access.idCategorie, access.idSujet, type, cible, sujet, ';
        $sql .= 'sujets.acronyme, sujets.dateCreation, forums.libelle, forums.parentId AS parentCat, forums.userStatus ';
        $sql .= 'FROM '.PFX.'thotForumsAccess AS access ';
        $sql .= 'JOIN '.PFX.'thotForumsSujets AS sujets ON (sujets.idCategorie = access.idCategorie AND sujets.idSujet = access.idSujet) ';
        $sql .= 'JOIN '.PFX.'thotForums AS forums ON forums.idCategorie = access.idCategorie ';
        $sql .= 'WHERE cible IN (:matricule, :classe, :niveau, '.$listeMatieresString.','.$listeCoursGrpString.')';
        $sql .= 'ORDER BY cible ';

        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':classe', $classe, PDO::PARAM_STR, 7);
        $requete->bindParam(':niveau', $niveau, PDO::PARAM_INT);

        $liste = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $type = $ligne['type'];
                $cible = $ligne['cible'];
                $libelle = $ligne['libelle'];
                $idCategorie = $ligne['idCategorie'];
                $idSujet = $ligne['idSujet'];
                $liste[$type][$cible][$idCategorie] = array('libelle' => $libelle, 'parentCat'=> $ligne['parentCat'], 'data' => $ligne);
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * constuit l'arbre correspondant à la structure linéaire extraite de la  BD
     * @param  array   $elements tableau linéaire des catégories
     * @param  integer $parentId identifiant du parent de l'élément actuellement examiné
     * @return array    arborescence des catégories
     */
    private function buildTree(array $elements, $parentId = 0) {
    $branch = array();

    foreach ($elements as $element) {
        if ($element['parentId'] == $parentId) {
            $children = $this->buildTree($elements, $element['idCategorie']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
            }
        }

    return $branch;
    }

    /**
     * recherche la liste des catégories pour le niveau d'utilisateur précisé
     *
     * @param string $userStatus
     *
     * @return array
     */
    public function getListeCategories($userStatus = Null) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCategorie, libelle, parentId, userStatus ';
        $sql .= 'FROM '.PFX.'thotForums ';
        if ($userStatus != Null)
            $sql .= 'WHERE userStatus = :userStatus ';
        $sql .= 'ORDER BY libelle ';

        $requete = $connexion->prepare($sql);

        if ($userStatus != Null)
            $requete->bindParam(':userStatus', $userStatus, PDO::PARAM_STR, 7);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idCategorie = $ligne['idCategorie'];
                $liste[$idCategorie] = $ligne;
            }
        }
        $tree = $this->buildTree($liste);

        Application::deconnexionPDO($connexion);

        return $tree;
    }

    /**
     * renvoie un tableau de toutes les catégories existantes
     *
     * @param void
     *
     * @return array
     */
    public function getAllCategories(){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCategorie, libelle, parentId, userStatus ';
        $sql .= 'FROM '.PFX.'thotForums ';
        $requete = $connexion->prepare($sql);

        $resultat = $requete->execute();

        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $idCategorie = $ligne['idCategorie'];
                $liste[$idCategorie] = $ligne;
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie les informations sur la catégorie $idCategorie
     *
     * @param int $idCategorie
     *
     * @return array
     */
    public function getInfoCategorie($idCategorie){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCategorie, libelle, parentId, userStatus ';
        $sql .= 'FROM '.PFX.'thotForums ';
        $sql .= 'WHERE idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);

        $categorie = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $categorie = $requete->fetch();
        }

        Application::deconnexionPDO($connexion);

        return $categorie;
    }

    /**
     * renvoie les informations générales sur le sujet dont on fournit le $idSujet
     * pour la catégorie $idCategorie
     *
     * @param int $idSujet
     *
     * @return array
     */
    public function getInfoSujet($idCategorie, $idSujet){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT sujets.idCategorie, idsujet, sujet, acronyme, dateCreation, libelle, fbLike ';
        $sql .= 'FROM '.PFX.'thotForumsSujets AS sujets ';
        $sql .= 'JOIN '.PFX.'thotForums AS forums ON forums.idCategorie = sujets.idCategorie ';
        $sql .= 'WHERE idSujet = :idSujet AND sujets.idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

        $sujet = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $sujet = $requete->fetch();
        }

        Application::deconnexionPDO($connexion);

        return $sujet;
    }

    /**
     * renvoie les caractéristiques d'un post dont on fournit $postId, $idCategorie, $idSujet
     *
     * @param int $idCategorie
     * @param int $idSujet
     * @param int $postId
     *
     * @return array
     */
    public function getInfoPost($idCategorie, $idSujet, $postId){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT postId, posts.idCategorie, libelle, posts.idSujet, sujet, posts.parentId, ';
        $sql .= 'DATE_FORMAT(date, "%d/%m/%Y") AS ladate, DATE_FORMAT(date, "%H:%i") AS heure, ';
        $sql .= 'auteur, posts.userStatus, post, modifie, ';
        $sql .= 'profs.sexe AS sexeProf, profs.nom AS nomProf, profs.prenom AS prenomProf, ';
        $sql .= 'de.groupe, de.nom AS nomEleve, de.prenom AS prenomEleve ';
        $sql .= 'FROM '.PFX.'thotForumsPosts AS posts ';
        $sql .= 'JOIN '.PFX.'thotForums AS forums ON forums.idCategorie = posts.idCategorie ';
        $sql .= 'JOIN '.PFX.'thotForumsSujets AS sujets ON sujets.idCategorie = posts.idCategorie AND sujets.idSujet = posts.idSujet ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS profs ON profs.acronyme = auteur ';
        $sql .= 'LEFT JOIN '.PFX.'eleves AS de ON de.matricule = auteur ';
        $sql .= 'WHERE postId = :postId AND posts.idSujet = :idSujet AND posts.idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);
        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

        $ligne = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
            if ($ligne) {
                $ligne['post'] = strip_tags($ligne['post'], self::acceptedTags);
                $ligne['post'] = nl2br($ligne['post']);

                $userStatus = $ligne['userStatus'];
                if ($ligne['userStatus'] == 'prof') {
                    $appel = ($ligne['sexeProf'] == 'M') ? 'M.' : 'Mme';
                    $user = sprintf('%s %s. %s', $appel, mb_substr($ligne['prenomProf'], 0, 1), $ligne['nomProf']);
                    }
                    else {
                        $user = sprintf('%s %s [%s]', $ligne['prenomEleve'], $ligne['nomEleve'], $ligne['groupe']);
                    }
                $ligne['from'] = $user;
            }
        }

        Application::deconnexionPDO($connexion);

        return $ligne;
    }


    /**
     * renvoie la liste des ascendants jusqu'à la racine pour une catégorie donnée
     *
     * @param int $idCategorie
     *
     * @return array
     */
    public function ancestors4categorie($idCategorie) {
        $listeCategories = $this->getAllCategories();
        $ancestors = array();
        do {
            $parentId = $listeCategories[$idCategorie]['parentId'];
            array_push($ancestors, $parentId);
            $idCategorie = (int)$parentId;
            }
            while ($parentId != 0);

        return array_reverse($ancestors);
    }

    /**
     * renvoie un tableau de tous les posts qui font partie du même sujet que le post $postId
     *
     * @param int $postId
     *
     * @return array
     */
    private function getFamily4postId($postId) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT postId, parentId ';
        $sql .= 'FROM '.PFX.'thotForumsPosts ';
        $sql .= 'WHERE idSujet = (SELECT idSujet FROM '.PFX.'thotForumsPosts WHERE postId = :postId) ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $postId = $ligne['postId'];
                $liste[$postId] = $ligne['parentId'];
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie la liste des ascendants jusqu'à la racine pour un $postId donné
     *
     * @param int $postId
     *
     * @return array
     */
    public function getAncestors4post($postId){
        $ancestors = array();
        $liste = $this->getFamily4postId($postId);
        $ancestors = array();
        do {
            $parentId = $liste[$postId];
            array_push($ancestors, $parentId);
            $postId = (int)$parentId;
        } while ($parentId != 0);

        return array_reverse($ancestors);
    }


    /**
     * renvoie une liste ordonnée des abonnés au sujet $idSujet de la catégorie $idCategorie
     *
     * @param int $idSujet
     * @param int $idCategorie
     *
     * @return array
     */
    public function listeAbonnesSujet($idSujet, $idCategorie){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idSujet, idCategorie, type, cible ';
        $sql .= 'FROM '.PFX.'thotForumsAccess ';
        $sql .= 'WHERE idSujet = :idSujet AND idCategorie = :idCategorie ';
        $sql .= 'ORDER BY type ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $type = $ligne['type'];
                $cible = $ligne['cible'];
                $liste[$type][$cible] = $ligne['cible'];
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * enregistrement d'un nouveau post sur le sujet $idSujet dans la catégorie $idCategorie
     * avec le parent $parentId avec le texte $reponse
     *
     * @param string $reponse
     * @param int $idSujet
     * @param int $parentId
     * @param int $idCategorie
     * @param string $auteur ($acronyme ou $matricule)
     * @param bool $modifie (1 si le post a été modifié, sinon 0)
     *
     * @return int le $id du nouveau post
     */
    public function saveNewPost($post, $idSujet, $idCategorie, $parentId, $auteur){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'thotForumsPosts ';
        $sql .= 'SET idCategorie = :idCategorie, idSujet = :idSujet, parentId = :parentId, ';
        $sql .= 'date = NOW(), auteur = :auteur, userStatus = "eleve", post = :post, modifie = 0 ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':parentId', $parentId, PDO::PARAM_INT);
        $requete->bindParam(':auteur', $auteur, PDO::PARAM_STR, 7);
        $requete->bindParam(':post', $post, PDO::PARAM_STR);

        $postId = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $postId = $connexion->lastInsertId();
        }

        Application::deconnexionPDO($connexion);

        return $postId;
    }

	/**
	 * Enregistre la prise d'abonnement par l'utilisateur $acronyme au $idSujet dans $idCategorie
	 *
	 * @param string $acronyme
	 * @param int $idCatgorie
	 * @param int $idSujet
	 *
	 * @return int
	 */
	 public function setAbonnement($matricule, $idCategorie, $idSujet){
		 $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
		 $sql = 'INSERT IGNORE INTO '.PFX.'thotForumsSubscribe ';
		 $sql .= 'SET user = :matricule, idCategorie = :idCategorie, idSujet = :idSujet ';
		 $requete = $connexion->prepare($sql);

		 $requete->bindParam(':matricule', $matricule, PDO::PARAM_STR, 7);
		 $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
		 $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

		 $resultat = $requete->execute();

		 $nb = $requete->rowCount();

		 Application::deconnexionPDO($connexion);

		 return $nb;
	 }

	 /**
	  * Vérifie la prise d'abonnement par l'utilisateur $acronyme au $idSujet dans $idCategorie
	  *
	  * @param string $acronyme
	  * @param int $idCatgorie
	  * @param int $idSujet
	  *
	  * @return int : 0 ou 1 (abonnement existe)
	  */
	  public function getAbonnement($matricule, $idCategorie, $idSujet){
		  $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
		  $sql = 'SELECT user FROM '.PFX.'thotForumsSubscribe ';
		  $sql .= 'WHERE user = :matricule AND idCategorie = :idCategorie AND idSujet = :idSujet ';
		  $requete = $connexion->prepare($sql);

		  $requete->bindParam(':matricule', $matricule, PDO::PARAM_STR, 7);
		  $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
		  $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

		  $resultat = $requete->execute();
		  $abonne = Null;
		  if ($resultat) {
			  $ligne = $requete->fetch();
			  $abonne = $ligne['user'];
		  }

		  Application::deconnexionPDO($connexion);

		  return ($abonne == $matricule);
	  }

	  /**
	   * Résilie l'abonnement par l'utilisateur $acronyme au $idSujet dans $idCategorie
	   *
	   * @param string $acronyme
	   * @param int $idCatgorie
	   * @param int $idSujet
	   *
	   * @return void
	   */
	   public function desAbonnement($matricule, $idCategorie, $idSujet){
		   $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
		   $sql = 'DELETE FROM '.PFX.'thotForumsSubscribe ';
		   $sql .= 'WHERE user = :matricule AND idCategorie = :idCategorie AND idSujet = :idSujet ';
		   $requete = $connexion->prepare($sql);

		   $requete->bindParam(':matricule', $matricule, PDO::PARAM_STR, 7);
		   $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
		   $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

		   $resultat = $requete->execute();

		   $nb = $requete->rowCount();

		   Application::deconnexionPDO($connexion);

		   return ($nb);
	   }

	   /**
		* renvoie la liste des abonnées au $idSujet de la $idCatgorie
		*
		* @param $idCategorie
		* @param $idSujet
		*
		* @return array
		*/
		public function getListeAbonnes($idCategorie, $idSujet) {
			$connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
			$sql = 'SELECT subscr.user, de.nom AS nomEleve, de.prenom AS prenomEleve, de.groupe, ';
			$sql .= 'CONCAT(pwd.user,"@", mailDomain) AS mailEleve, ';
			$sql .= 'profs.nom AS nomProf, profs.prenom AS prenomProf, profs.sexe, profs.mail AS mailProf ';
			$sql .= 'FROM '.PFX.'thotForumsSubscribe AS subscr ';
			$sql .= 'LEFT JOIN '.PFX.'eleves AS de ON de.matricule = user ';
			$sql .= 'LEFT JOIN '.PFX.'profs AS profs ON profs.acronyme = user ';
			$sql .= 'LEFT JOIN '.PFX.'passwd AS pwd ON pwd.matricule = de.matricule ';
			$sql .= 'WHERE idCategorie = :idCategorie AND idSujet = :idSujet ';
			$requete = $connexion->prepare($sql);

			$requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
			$requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

			$liste = array();
			$resultat = $requete->execute();

			if ($resultat) {
				$requete->setFetchMode(PDO::FETCH_ASSOC);
				while ($ligne = $requete->fetch()) {
					$user = $ligne['user'];
					$liste[$user] = $ligne;
				}
			}

			Application::deconnexionPDO($connexion);

			return $liste;
		}

	/**
         * renvoie la liste des abonnements de l'élève $matricule
         *
         * @param int $matricule
         *
         * @return array
         */
         public function getListeAbonnements($matricule) {
 			$connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
            $sql = 'SELECT count(sujet) AS nbPosts, subscr.idCategorie, subscr.idSujet, libelle, sujet, user ';
            $sql .= 'FROM '.PFX.'thotForumsSubscribe AS subscr JOIN didac_thotForums AS forums ON forums.idCategorie = subscr.idCategorie ';
            $sql .= 'JOIN '.PFX.'thotForumsSujets AS sujet ON sujet.idCategorie = subscr.idCategorie AND sujet.idSujet = subscr.idSujet ';
            $sql .= 'JOIN '.PFX.'thotForumsPosts AS posts ON sujet.idCategorie = posts.idCategorie AND sujet.idSujet = posts.idSujet ';
            $sql .= 'WHERE user = :user ';
            $sql .= 'GROUP BY idCategorie, idSujet ';

 			$requete = $connexion->prepare($sql);

 			$requete->bindParam(':user', $matricule, PDO::PARAM_INT);

 			$liste = array();
 			$resultat = $requete->execute();

 			if ($resultat) {
 				$requete->setFetchMode(PDO::FETCH_ASSOC);
 				while ($ligne = $requete->fetch()) {
                    $idCategorie = $ligne['idCategorie'];
 					$idSujet = $ligne['idSujet'];
 					$liste[$idCategorie][$idSujet] = $ligne;
 				}
 			}

 			Application::deconnexionPDO($connexion);

 			return $liste;
 		}

    /**
     * enregistrement d'un post ÉDITÉ $postId sur le sujet $idSujet dans la catégorie $idCategorie
     * avec le parent $parentId avec le texte $reponse
     *
     * @param string $reponse
     * @param int $idSujet
     * @param int $parentId
     * @param int $idCategorie
     * @param string $auteur ($acronyme ou $matricule)
     * @param bool $modifie (1 si le post a été modifié, sinon 0)
     *
     * @return int le $id du nouveau post
     */
	public function saveEditedPost($post, $idSujet, $idCategorie, $postId){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'UPDATE '.PFX.'thotForumsPosts ';
        $sql .= 'SET date = NOW(), userStatus = "eleve", post = :post, modifie = "1" ';
        $sql .= 'WHERE postId = :postId AND idSujet = :idSujet AND idCategorie = :idCategorie ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);
        $requete->bindParam(':post', $post, PDO::PARAM_STR);

        $resultat = $requete->execute();
        if ($resultat) {
            $retour = $postId;
            }
            else $retour = Null;

        Application::deconnexionPDO($connexion);

        return $retour;
    }

    /**
     * construit l'arbre correspondant à la structure linéaire extraite de la  BD
     *
     * @param array   $elements tableau linéaire des catégories
     * @param int $parentId identifiant du parent de l'élément actuellement examiné
     *
     * @return array    arborescence des catégories
     */
    private function buildPostTree(array $elements, $parentId = 0) {
    $branch = array();

    foreach ($elements as $element) {
        if ($element['parentId'] == $parentId) {
            $children = $this->buildPostTree($elements, $element['postId']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
            }
        }

    return $branch;
    }

    /**
     * retourne la liste de tous les posts de la catégorie $idCategorie pour le sujet $idSujet
     *
     * @param int $idCategorie
     * @param int $idSujet
     *
     * @return array
     */
    public function getPosts4subject($idCategorie, $idSujet){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT postId, parentId, DATE_FORMAT(date, "%d/%m") AS ladate, DATE_FORMAT(date, "%H:%i") AS heure, ';
        $sql .= 'DATE_FORMAT(dateModif,"%d/%m") as dateModif, DATE_FORMAT(dateModif, "%H:%i") AS heureModif, ';
        $sql .= 'auteur, userStatus, post, modifie, posts.idCategorie, posts.idSujet, ';
        $sql .= 'sujets.acronyme, profs.sexe AS sexeProf, profs.nom AS nomProf, profs.prenom AS prenomProf, ';
        $sql .= 'eleves.sexe AS sexeEleve, eleves.nom AS nomEleve, eleves.prenom AS prenomEleve, eleves.groupe ';
        $sql .= 'FROM '.PFX.'thotForumsPosts AS posts ';
        $sql .= 'JOIN '.PFX.'thotForumsSujets AS sujets ON sujets.idSujet = posts.idSujet AND sujets.idCategorie = posts.idCategorie ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS profs ON profs.acronyme = auteur ';
        $sql .= 'LEFT JOIN '.PFX.'eleves AS eleves ON eleves.matricule = auteur ';
        $sql .= 'WHERE posts.idCategorie = :idCategorie AND posts.idSujet = :idSujet ';
        $sql .= 'ORDER BY date DESC ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $postId = $ligne['postId'];

                $ligne['post'] = strip_tags($ligne['post'], self::acceptedTags);
                $ligne['post'] = nl2br($ligne['post']);

                if ($ligne['userStatus'] == 'prof') {
                    $appel = ($ligne['sexeProf'] == 'M') ? 'M.' : 'Mme';
                    $user = sprintf('%s %s. %s', $appel, mb_substr($ligne['prenomProf'], 0, 1), $ligne['nomProf']);
                    }
                    else {
                        $user = sprintf('%s %s [%s]', $ligne['prenomEleve'], $ligne['nomEleve'], $ligne['groupe']);
                    }
                $ligne['user'] = $user;

                $liste[$postId] = $ligne;
            }
        }

        Application::deconnexionPDO($connexion);

        $tree = $this->buildPostTree($liste);

        return $tree;
    }

/**
 * renvoie la liste des sujets de la catégorie $idCategorie accessibles à l'élève $matricule
 * de la classe $classe, du niveau $niveau, pour la liste des matières et la liste des cours donnés
 *
 * @param int $matricule
 * @param string $classe
 * @param int $niveau
 * @param array $listeMatieres
 * @param array $listeCoursGrp
 *
 * @return array
 */
public function getListeSujets4categorie($idCategorie, $matricule, $classe, $niveau, $listeMatieres, $listeCoursGrp){
    if (is_array($listeMatieres)) {
        $listeMatieresString = "'".implode("','", array_keys($listeMatieres))."'";
    } else {
        $listeMatieresString = "'".$listeMatieres."'";
    }
    if (is_array($listeCoursGrp)) {
        $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
    } else {
        $listeCoursGrpString = "'".$listeCoursGrp."'";
    }
    $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
    $sql = 'SELECT access.idSujet, access.idCategorie, access.type, cible, sujet, sujets.acronyme, ';
    $sql .= 'DATE_FORMAT(dateCreation, "%d/%m/%Y") AS ladate, DATE_FORMAT(dateCreation, "%H:%i") AS heure, ';
    $sql .= 'modifParAuteur, modifParEleve, nom, prenom, sexe, libelle ';
    $sql .= 'FROM '.PFX.'thotForumsAccess AS access ';
    $sql .= 'JOIN '.PFX.'thotForumsSujets AS sujets ON access.idCategorie = sujets.idCategorie AND access.idSujet = sujets.idSujet ';
    $sql .= 'JOIN '.PFX.'thotForums AS forums ON forums.idCategorie = access.idCategorie ';
    $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = sujets.acronyme ';
    $sql .= 'WHERE access.idCategorie = :idCategorie AND cible IN (:matricule, :classe, :niveau,'.$listeMatieresString.', '.$listeCoursGrpString.') ';
    $sql .= 'ORDER BY dateCreation DESC, libelle, sujet ';
    $requete = $connexion->prepare($sql);

    $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
    $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
    $requete->bindParam(':classe', $classe, PDO::PARAM_STR, 7);
    $requete->bindParam(':niveau', $niveau, PDO::PARAM_INT);

    $liste = Null;
    $resultat = $requete->execute();
    if ($resultat){
        $requete->setFetchMode(PDO::FETCH_ASSOC);
        while ($ligne = $requete->fetch()){
            $idSujet = $ligne['idSujet'];
            $ligne['nomProf'] = self::nomProf($ligne['sexe'], $ligne['prenom'], $ligne['nom']);
            $liste[$idSujet] = $ligne;
        }
    }

    Application::DeconnexionPDO($connexion);

    return $liste;
}
    /**
     * vérifie si l'élève $matricule a accès au sujet $idSujet de la catégorie $idCategorie
     * en checkant $matricule, $classe, $niveau, $listeCoursGrp, $listeMatieres
     *
     * @param int $matricule
     * @param int $idSujet
     * @param int $idCategorie
     * @param string $classe
     * @param int $niveau
     * @param array $listeCoursGrp
     * @param array $listeMatieres
     *
     * @return bool
     */
    public function verifieAccess($idSujet, $idCategorie, $classe, $niveau, $listeCoursGrp, $listeMatieres){
        if (is_array($listeMatieres)) {
            $listeMatieresString = "'".implode("','", array_keys($listeMatieres))."'";
        } else {
            $listeMatieresString = "'".$listeMatieres."'";
        }
        if (is_array($listeCoursGrp)) {
            $listeCoursGrpString = "'".implode("','", array_keys($listeCoursGrp))."'";
        } else {
            $listeCoursGrpString = "'".$listeCoursGrp."'";
        }
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT cible ';
        $sql .= 'FROM '.PFX.'thotForumsAccess AS access ';
        $sql .= 'WHERE idSujet = :idSujet AND idCategorie = :idCategorie ';
        $sql .= 'AND cible IN ("all", :classe, :niveau,'.$listeMatieresString.', '.$listeCoursGrpString.') ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':classe', $classe, PDO::PARAM_STR, 7);
        $requete->bindParam(':niveau', $niveau, PDO::PARAM_INT);

        $cible = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $cible = $ligne['cible'];
            }
        }

        Application::DeconnexionPDO($connexion);

        return $cible != Null;
    }

    /**
     * vérifier que le post $postId fait bien partie du sujet $idSujet de la catégorie $idCategorie
     *
     * @param int $postId
     * @param int $idCategorie
     * @param int $idSujet
     *
     * @return bool
     */
    public function verifiePost($postId, $idCategorie, $idSujet){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT postId, idCategorie, idSujet ';
        $sql .= 'FROM '.PFX.'thotForumsPosts ';
        $sql .= 'WHERE postId = :postId AND idCategorie = :idCategorie AND idSujet = :idSujet ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);

        $ligne = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
        }

        Application::DeconnexionPDO($connexion);

        return $ligne != Null;
    }

    /**
     * vérifie que l'utilisateur $matricule est propriétaire du post $postId
     * du sujet $idSujet de la catégorie $idCategorie
     *
     * @param int $matricule
     * @param int $postId
     * @param int $idSujet
     * @param int $idCategorie
     *
     * @return bool
     */
    public function verifProprio($matricule, $postId, $idSujet, $idCategorie){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT postId, idCategorie, idSujet, auteur ';
        $sql .= 'FROM '.PFX.'thotForumsPosts ';
        $sql .= 'WHERE postId = :postId AND idCategorie = :idCategorie AND idSujet = :idSujet AND auteur = :matricule ';
        $sql .= 'AND userStatus = "eleve" ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $ligne = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
        }

        Application::DeconnexionPDO($connexion);

        return $ligne != Null;
    }

    /**
     * Vérifie si le post $postId du sujet $idSujet et $idCategorie a des enfants
     *  (auquel cas, il peut être supprimé)
     *
     * @param int $idCategorie
     * @param int $idSujet
     * @param int $postId
     *
     * @return bool
     */
    public function hasChildren($idCategorie, $idSujet, $postId){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCategorie, idSujet, postId ';
        $sql .= 'FROM '.PFX.'thotForumsPosts ';
        $sql .= 'WHERE idCategorie = :idCategorie AND idSujet = :idSujet AND parentId = :postId ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);

        $ligne = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $requete->fetch();
        }

        Application::DeconnexionPDO($connexion);

        return $ligne != Null;
    }

    /**
     * Efface le contenu du post $postId de l'utilisateur $matricule pour le sujet
     * $idSujet de la catégorie $idCategorie
     *
     * @param int $matricule
     * @param int $postId
     * @param int $idSujet
     * @param int $idCategorie
     *
     * @return int : nombre d'effacements (0 ou 1)
     */
    public function clearPost($matricule, $postId, $idSujet, $idCategorie){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'UPDATE '.PFX.'thotForumsPosts ';
        $sql .= 'SET post = NULL ';
        $sql .= 'WHERE postId = :postId AND idCategorie = :idCategorie AND idSujet = :idSujet AND auteur = :matricule ';
        $sql .= 'AND userStatus = "eleve" ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $ligne = Null;
        $resultat = $requete->execute();

        $nb = $requete->rowcount();

        Application::DeconnexionPDO($connexion);

        return $nb;
    }

    /**
     * Efface le contenu du post $postId de l'utilisateur $matricule pour le sujet
     * $idSujet de la catégorie $idCategorie
     *
     * @param int $matricule
     * @param int $postId
     * @param int $idSujet
     * @param int $idCategorie
     *
     * @return int : nombre d'effacements (0 ou 1)
     */
    public function delPost($matricule, $postId, $idSujet, $idCategorie){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'thotForumsPosts ';
        $sql .= 'WHERE postId = :postId AND idCategorie = :idCategorie AND idSujet = :idSujet AND auteur = :matricule ';
        $sql .= 'AND userStatus = "eleve" ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $ligne = Null;
        $resultat = $requete->execute();

        $nb = $requete->rowcount();

        Application::DeconnexionPDO($connexion);

        return $nb;
    }
    /**
     * enregistre ou met à jour les informations de Like d'un $post de $matricule
     * pour la catégorie $idCategorie, le sujet $idSujet et le post $postId
     *
     * @param int $idCatgorie
     * @param int $idSujet
     * @param int $postId
     * @param int $matricule
     * @param string $emoji
     *
     * @return int : nombre d'enregistrements ou modficiations
     */
    public function saveLike($idCategorie, $idSujet, $postId, $matricule, $emoji){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT INTO '.PFX.'thotForumsLikes ';
        $sql .= 'SET idCategorie = :idCategorie, idSujet = :idSujet, postId = :postId, ';
        $sql .= 'likeLevel = :emoji, user = :matricule, userStatus = "eleve" ';
        $sql .= 'ON DUPLICATE KEY UPDATE likeLevel = :emoji ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':emoji', $emoji, PDO::PARAM_STR, 10);

        $nb = $requete->execute();

        Application::DeconnexionPDO($connexion);

        return $nb;
    }

    /**
     * supprime un like pour le post $postId du sujet $idSujet pour la catégorie
     * $idCategorie et pour l'utilisateur $matricule
     *
     * @param int $idCategorie
     * @param int $idSujet
     * @param int postId
     * @param int $matricule
     *
     * @return int : nombre de suppressions (0 ou 1)
     */
    public function delLike($idCategorie, $idSujet, $postId, $matricule){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'thotForumsLikes ';
        $sql .= 'WHERE idCategorie = :idCategorie AND idSujet = :idSujet AND postId = :postId ';
        $sql .= 'AND user = :user ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);
        $user = (string)$matricule;
        $requete->bindParam(':user', $user, PDO::PARAM_STR, 7);

        $resultat = $requete->execute();

        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * renvoie les stats du nombre d'utilisateurs par emoji pour le post $postId
     * de la catégorie $idCatgorie our le sujet $idSujet
     *
     * @param int $idCategorie
     * @param int $idSujet
     * @param int $postId
     *
     * @return array
     */
    public function statsByemoji($idCatgorie, $idSujet, $postId) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT likelevel, COUNT(*) AS nb ';
        $sql .= 'FROM '.PFX.'thotForumsLikes ';
        $sql .= 'WHERE idCategorie = :idCategorie AND idSujet = :idSujet AND postId = :postId ';
        $sql .= 'GROUP BY likeLevel ';
        $sql .= 'ORDER BY likelevel ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);

        $liste = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $likelevel = $ligne['likelevel'];
                $liste[$likelevel] = $ligne;
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie les likeleve éventuel pour les utilisateurs qui ont liké le post
     * pour le post $postId de la catégorie $idCatgorie du sujet $idSujet
     *
     * @param int $idCatgorie
     * @param int $idSujet
     * @param int $postId
     *
     * @return string
     */
    public function getEmoji4user($idCategorie, $idSujet, $postId, $matricule){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT likelevel, user, de.nom AS nomEleve, de.prenom AS prenomEleve, de.groupe, ';
        $sql .= 'dp.nom AS nomProf, dp.prenom AS prenomProf, dp.sexe ';
        $sql .= 'FROM '.PFX.'thotForumsLikes AS fb ';
        $sql .= 'LEFT JOIN '.PFX.'eleves AS de ON de.matricule = fb.user ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = fb.user ';
        $sql .= 'WHERE idCategorie = :idCategorie AND idSujet = :idSujet AND postId = :postId ';
        $sql .= 'ORDER BY likelevel, de.groupe, nomEleve, prenomEleve, nomProf, prenomProf ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);

        $liste = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $likelevel = $ligne['likelevel'];
                $user = $ligne['user'];
                $liste[$likelevel][$user] = $ligne;
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne les likes de l'utilisateur courant sur le sujet $idSujet
     * de la catégorie $idCategorie
     *
     * @param int $matricule
     * @param int $idCatgorie
     * @param int $idSujet
     *
     * @return array
     */
    public function getLikesOnSubject4user($matricule, $idCategorie, $idSujet){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT user, likeLevel, idCategorie, idSujet, postId ';
        $sql .= 'FROM '.PFX.'thotForumsLikes ';
        $sql .= 'WHERE idCategorie = :idCategorie AND idSujet = :idSujet AND user = :user ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $user = (string)$matricule;
        $requete->bindParam(':user', $user, PDO::PARAM_STR, 7);

        $liste = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $likeLevel = $ligne['likeLevel'];
                if ($likeLevel != 'null') {
                    $postId = $ligne['postId'];
                    $liste[$postId] = $likeLevel;
                }
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie les statistiques de likes pour le post $postId de la catégorie $idCategorie
     * pour le sujet $idSujet
     *
     * @param int $idCatgorie
     * @param int $idSujet
     * @param int $postId
     *
     * @return array
     */
    public function getFBstats4postId($idCategorie, $idSujet, $postId){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT likelevel, user, userStatus, ';
        $sql .= 'de.nom AS nomEleve, de.prenom AS prenomEleve, de.groupe, ';
        $sql .= 'dp.nom AS nomProf, dp.prenom AS prenomProf, dp.sexe ';
        $sql .= 'FROM '.PFX.'thotForumsLikes AS likes ';
        $sql .= 'LEFT JOIN '.PFX.'eleves AS de ON de.matricule = likes.user ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = likes.user ';
        $sql .= 'WHERE likes.idCategorie = :idCategorie AND likes.idSujet = :idSujet AND likes.postId = :postId ';
        $sql .= 'ORDER BY likelevel, nomEleve, nomProf ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);
        $requete->bindParam(':postId', $postId, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                // Application::afficher($ligne);
                $likelevel = $ligne['likelevel'];
                if ($likelevel != 'null') {
                    if ($ligne['userStatus'] == 'eleve') {
                        $ligne['nom'] = sprintf('%s %s [%s]', $ligne['prenomEleve'], $ligne['nomEleve'], $ligne['groupe']);
                        }
                        else {
                            $ligne['nom'] = $this->nomProf($ligne['sexe'], $ligne['prenomProf'], $ligne['nomProf']);
                        }
                    $liste[$likelevel][] = $ligne['nom'];
                }
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie les statistiques de likes pour tous les posts sur un sujet $idSujet
     * de la catégorie $idCatgorie
     *
     * @param int $idCatgorie
     * @param int $idSujet
     *
     * @return array
     */
    public function getFBstats4subject($idCategorie, $idSujet){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT likes.postId, likes.idSujet, likes.idCategorie, likelevel, user, userStatus, ';
        $sql .= 'de.nom AS nomEleve, de.prenom AS prenomEleve, de.groupe, ';
        $sql .= 'dp.nom AS nomProf, dp.prenom AS prenomProf, dp.sexe ';
        $sql .= 'FROM '.PFX.'thotForumsLikes AS likes ';
        $sql .= 'LEFT JOIN '.PFX.'eleves AS de ON de.matricule = likes.user ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = likes.user ';
        $sql .= 'WHERE likes.idCategorie = :idCategorie AND likes.idSujet = :idSujet ';
        $sql .= 'ORDER BY likelevel, nomEleve, nomProf ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':idSujet', $idSujet, PDO::PARAM_INT);

        $liste = Null;
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $likelevel = $ligne['likelevel'];
                if ($likelevel != 'null') {
                    $postId = $ligne['postId'];
                    if ($ligne['userStatus'] == 'eleve') {
                        $ligne['nom'] = sprintf('%s %s [%s]', $ligne['prenomEleve'], $ligne['nomEleve'], $ligne['groupe']);
                        }
                        else {
                            $ligne['nom'] = $this->nomProf($ligne['sexe'], $ligne['prenomProf'], $ligne['nomProf']);
                        }
                    $liste[$postId][$likelevel][] = $ligne['nom'];
                }
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie le nom du prof avec la formule d'appel qui convient
     *
     * @param string $sexe
     * @param string $prenom
     * @param string $nom
     *
     * @return string : Mme J. Dupont
     */
    public function nomProf($sexe, $prenom, $nom){
        $appel = ($sexe == 'F') ? 'Mme' : 'M.';
        $nom = sprintf('%s %s. %s', $appel, mb_substr($prenom, 0, 1, 'UTF-8'), $nom);
        return $nom;
    }

}
