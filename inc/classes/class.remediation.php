<?php

/**
 *
 */
class Remediation
{

    function __construct()
    {
        // code...
    }

    /**
     * renvoie les détails de la rémédiation $idOffre
     *
     * @param int $idOffre
     *
     * @return array
     */
    public function getOffre($idOffre){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idOffre, title, contenu, local, sexe, nom, prenom, DATE_FORMAT(startDate,"%d/%m/%Y") AS date, ';
        $sql .= 'TIME_FORMAT(startDate,"%H:%i") AS heure, TIMEDIFF(endDate, startDate) AS duree, places ';
        $sql .= 'FROM '.PFX.'remediationOffre AS offre ';
        $sql .= 'JOIN '.PFX.'profs AS profs ON profs.acronyme = offre.acronyme ';
        $sql .= 'WHERE idOffre = :idOffre ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idOffre', $idOffre, PDO::PARAM_INT);

        $offre = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $offre = $requete->fetch();
            $offre['initiale'] = mb_substr($offre['prenom'], 0, 1);
        }

        Application::DeconnexionPDO($connexion);

        return $offre;
    }

    /**
     * renvoie la liste des remédiations dans le futur auxquelles un élèves est attendu
     *
     * @param  int $matricule de l'élève
     *
     * @return array
     */
    public function getRemediationsEleve($matricule){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT remEleves.idOffre, matricule, obligatoire, DATE_FORMAT(startDate,"%d/%m/%Y") AS date, TIME_FORMAT(startDate,"%H:%i") AS heure, ';
        $sql .= 'TIMEDIFF(endDate, startDate) AS duree, title, type, cible, contenu, local, offre.acronyme, sexe, nom, prenom ';
        $sql .= 'FROM '.PFX.'remediationEleves AS remEleves ';
        $sql .= 'JOIN '.PFX.'remediationOffre AS offre ON offre.idOffre = remEleves.idOffre ';
        $sql .= 'JOIN '.PFX.'remediationCibles AS cibles ON cibles.idOffre = remEleves.idOffre ';
        $sql .= 'LEFT JOIN didac_profs AS profs ON profs.acronyme = offre.acronyme ';
        $sql .= 'WHERE matricule = :matricule AND startDate >= NOW() ';
        $sql .= 'ORDER BY startDate, cible, title ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $idOffre = $ligne['idOffre'];
                $ligne['initiale'] = mb_substr($ligne['prenom'], 0, 1);
                $liste[$idOffre] = $ligne;
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie la liste des remédiations possibles pour l'élève $matricule
     *
     * @param int $matricule
     * @param int $niveau : le niveau d'étude
     * @param string $classe : la classe de l'élève
     * @param array $listeCoursGrp : la liste des cours suivis par l'élève
     * @param array $listeMatieres : liste des matières de l'élève (cours sans le groupe)
     *
     * @return array
     */
    public function getOffresRemediations($niveau, $classe, $listeCoursGrp, $listeMatieres){
        $listeCoursGrpString = "'".implode("','", $listeCoursGrp)."'";
        $listeMatieresString = "'".implode("','", $listeMatieres)."'";
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DISTINCT offre.idOffre, type, offre.acronyme, title, contenu, local, cible, type, DATE_FORMAT(startDate,"%d/%m/%Y") AS date, ';
        $sql .= 'startDate, TIME_FORMAT(startDate,"%H:%i") AS heure, TIMEDIFF(endDate, startDate) AS duree, places, sexe, nom, prenom ';
        $sql .= 'FROM '.PFX.'remediationOffre AS offre ';
        $sql .= 'JOIN '.PFX.'remediationCibles AS cibles ON cibles.idOffre = offre.idOffre ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS profs ON profs.acronyme = offre.acronyme ';
        $sql .= 'WHERE (startDate > NOW() AND cache = 0) AND (cible = :niveau OR cible = :classe ';
        $sql .= 'OR type = "ecole" OR cible IN ('.$listeCoursGrpString.') OR cible IN ('.$listeMatieresString.')) ';
        $sql .= 'ORDER BY startDate, cible, title ';

        $requete = $connexion->prepare($sql);

        $requete->bindParam(':niveau', $niveau, PDO::PARAM_STR, 1);
        $requete->bindParam(':classe', $classe, PDO::PARAM_STR, 9);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $idOffre = $ligne['idOffre'];
                $ligne['initiale'] = mb_substr($ligne['prenom'], 0, 1);
                $liste[$idOffre] = $ligne;
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie les occupations actuelles des remédiations dont on fournit la liste
     *
     * @param array $liste
     *
     * @return array
     */
    public function getOccupations($listeRemediations){
        $listeRemediationsString = implode(',', $listeRemediations);
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT offre.idOffre, COUNT(eleves.idOffre) AS occupation ';
        $sql .= 'FROM '.PFX.'remediationOffre AS offre ';
        $sql .= 'JOIN '.PFX.'remediationEleves AS eleves ON offre.idOffre = eleves.idOffre ';
        $sql .= 'WHERE startDate >= NOW() AND offre.idOffre IN ('.$listeRemediationsString.') ';
        $sql .= 'GROUP BY idOffre ';
        $requete = $connexion->prepare($sql);

        $resultat = $requete->execute();
        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $idOffre = $ligne['idOffre'];
                $liste[$idOffre] = $ligne['occupation'];
            }
        }

        foreach ($listeRemediations as $idOffre) {
            if (!(isset($liste[$idOffre])))
                $liste[$idOffre] = 0;
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * enregistrer la participation de l'élève $matricule à la reméidation $idOffre
     *
     * @param int $matricule
     * @param int $idOffre
     *
     * @return boolean
     */
    public function subscribe ($matricule, $idOffre) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'INSERT IGNORE INTO '.PFX.'remediationEleves ';
        $sql .= 'SET matricule = :matricule, idOffre = :idOffre, obligatoire = 0 ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':idOffre', $idOffre, PDO::PARAM_INT);

        $resultat = $requete->execute();

        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * renvoie la liste des présences et absences pour l'élève $matricule
     *
     * @param int $matricule
     *
     * @return array
     */
    public function getPresences($matricule) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT eleves.idOffre, eleves.matricule, presence, obligatoire, ';
        $sql .= 'DATE_FORMAT(startDate,"%d/%m/%Y") AS date, TIME_FORMAT(startDate,"%H:%i") AS heure, ';
        $sql .= 'TIMEDIFF(endDate, startDate) AS duree, title, offre.acronyme, profs.sexe, profs.nom, profs.prenom ';
        $sql .= 'FROM '.PFX.'remediationEleves AS eleves ';
        $sql .= 'JOIN '.PFX.'remediationOffre AS offre ON offre.idOffre = eleves.idOffre ';
        $sql .= 'JOIN '.PFX.'eleves AS de ON de.matricule = eleves.matricule ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS profs ON profs.acronyme = offre.acronyme ';
        $sql .= 'WHERE eleves.matricule = :matricule ';
        $sql .= 'ORDER BY startDate ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $idOffre = $ligne['idOffre'];
                $ligne['initiale'] = mb_substr($ligne['prenom'], 0, 1);
                $liste[$idOffre] = $ligne;
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

}
