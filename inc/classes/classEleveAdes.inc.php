<?php

/**
 * class EleveAdes.
 */
class EleveAdes
{

    public function __construct() {
        }

    /**
     * renvoie la liste structurée des faits disciplinaires d'un élève donné.
     *
     * @param int : $matricule
     *
     * @return array
     */
    public function getListeFaits($matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT af.*, nom, prenom, sexe ';
        $sql .= 'FROM '.PFX.'adesFaits AS af ';
        $sql .= 'JOIN '.PFX.'adesTypesFaits AS atf ON (af.type = atf.type) ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS ap ON (ap.acronyme = af.professeur) ';
        $sql .= 'WHERE matricule =:matricule AND atf.print = 1 ';
        $sql .= 'ORDER BY anneeScolaire DESC, atf.ordre, ladate, idFait ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $resultat = $requete->execute();
        $listeFaits = array(ANNEESCOLAIRE => null);
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $anneeScolaire = $ligne['anneeScolaire'];
                $idfait = $ligne['idfait'];
                $type = $ligne['type'];
                $ligne['ladate'] = Application::datePHP($ligne['ladate']);
                if ($ligne['nom'] != Null) {
                    $ligne['professeur'] = $ligne['sexe']=='F' ? 'Mme' : 'M.';
                    $ligne['professeur'] .= sprintf(' %s. %s', mb_substr($ligne['prenom'], 0, 1, "UTF-8"), $ligne['nom']);
                    }
                    // else $ligne['professeur'] = $ligne['acronyme'];
                $listeFaits[$anneeScolaire][$type][$idfait] = $ligne;
            }
        }
        Application::deconnexionPDO($connexion);

        return $listeFaits;
    }

    /**
     * renvoie la liste des idRetenues pour l'élève dont on fournit le matricule.
     *
     * @param int $matricule
     *
     * @return array
     */
    public function getListeRetenuesEleve($matricule)
    {
        // recherche de toutes les retenues dans la table des faits disciplinaires
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idretenue ';
        $sql .= 'FROM '.PFX.'adesFaits ';
        $sql .= "WHERE matricule = '$matricule' AND idretenue != '' ";
        $sql .= 'ORDER BY ladate ';
        $resultat = $connexion->query($sql);
        $listeRetenues = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $idretenue = $ligne['idretenue'];
                $listeRetenues[] = $idretenue;
            }
        }

        // recherche des détails pratiques concernant ces retenues dans la table des retenues
        $listeRetenuesString = implode(',', $listeRetenues);
        $sql = 'SELECT type, idretenue, dateRetenue, heure, duree, local ';
        $sql .= 'FROM '.PFX.'adesRetenues ';
        $sql .= "WHERE idRetenue IN ($listeRetenuesString) ";
        $sql .= 'ORDER BY type, dateRetenue, heure ';

        $resultat = $connexion->query($sql);
        $listeRetenues = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $idretenue = $ligne['idretenue'];
                $ligne['dateRetenue'] = Application::datePHP($ligne['dateRetenue']);
                $listeRetenues[$idretenue] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $listeRetenues;
    }


     /**
      * return liste de tous les types de faits avec leur description (champs nécessaires).
      *
      * @param
      *
      * @return array
      */
    public function getListeTypesFaits()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT type, titreFait, couleurFond, couleurTexte, typeRetenue, ordre, champ, print ';
        $sql .= ' FROM '.PFX.'adesTypesFaits AS adtf ';
        $sql .= 'JOIN '.PFX.'adesChampsFaits AS adcf ON adtf.type = adcf.typeFait ';
        $sql .= 'ORDER BY ordre ';
        $requete = $connexion->prepare($sql);

        $resultat = $requete->execute();
        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $type = $ligne['type'];
                $champ = $ligne['champ'];
                if (!isset($liste[$type])) {
                    unset($ligne['champ']);
                    $liste[$type] = $ligne;
                }
                $liste[$type]['listeChamps'][] = $champ;
            }
        }
        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * Lecture de la description des champs dans la BD.
     *
     * @param
     *
     * @return array
     */
    public function listeChamps()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT * FROM '.PFX.'adesChamps ';
        $sql .= 'ORDER BY champ ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $champ = $ligne['champ'];
                $ligne['contextes'] = explode(',', $ligne['contextes']);
                $liste[$champ] = $ligne;
            }
        }
        Application::deconnexionPDO($connexion);

        return $liste;
    }

}
