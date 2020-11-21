<?php

class Jdc
{
    /**
     * constructeur de l'objet jdc.
     */
    public function __construct()
    {
    }

    /**
     * renvoie la liste d'événements entres deux dates start et end.
     *
     * @param int $start : date de début
     * @param int $end   : date de fin
     * @param int $niveau : niveau d'étude de l'élève
     * @param string $classe : classe de l'élève
     * @param int $matricule
     * @param string $listeCoursString
     * @param int $redacteur : matricule éventuel de l'élève rédacteur du JDC
     *
     * @return array
     */
    public function retreiveEvents($start, $end, $niveau, $classe, $matricule, $listeCoursString, $redacteur=Null)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, destinataire, idCategorie, type, proprietaire, redacteur, title, enonce, class, allDay, startDate, endDate ';
        $sql .= 'FROM '.PFX.'thotJdc ';
        $sql .= 'WHERE startDate BETWEEN :start AND :end ';
        $sql .= "AND destinataire in ($listeCoursString) OR destinataire LIKE :classe ";
        $sql .= "OR destinataire = :matricule OR destinataire = 'ecole' OR destinataire LIKE :niveau ";
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':classe', $classe, PDO::PARAM_STR, 6);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':niveau', $niveau, PDO::PARAM_STR, 1);
        $requete->bindParam(':start', $start, PDO::PARAM_STR, 20);
        $requete->bindParam(':end', $end, PDO::PARAM_STR, 20);

        $resultat = $requete->execute();
        $liste = array();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()) {
                $destinataire = $ligne['destinataire'];
                $type = $ligne['type'];
                if ($type == 'coursGrp') {
                    preg_match('/[0-9].*:(.*)-[0-9]*/', $destinataire, $matches);
                    $cours = $matches[1];
                }
                else $cours = '';
                $liste[] = array(
                    'id' => $ligne['id'],
                    'title' => $ligne['title'],
                    'enonce' => mb_strimwidth(strip_tags(html_entity_decode($ligne['enonce'])), 0, 200,'...'),
                    'className' => 'cat_'.$ligne['idCategorie'],
                    'start' => $ligne['startDate'],
                    'end' => $ligne['endDate'],
                    'allDay' => ($ligne['allDay'] != 0),
                    'destinataire' => $destinataire,
                    'cours' => $cours
                    );
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retrouve la liste des remédiations entre deux dates destinées à un élève dont on fournit le matricule
     *
     * @param string $start date de début
     * @param string $end date de fin
     * @param int $matricule
     *
     * @return array
     */
    public function retreiveRemediations ($start, $end, $matricule) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT eleves.idOffre, matricule, acronyme, title, contenu AS enonce, startDate, endDate, matricule AS destinataire ';
        $sql .= 'FROM '.PFX.'remediationEleves AS eleves ';
        $sql .= 'JOIN '.PFX.'remediationOffre AS offres ON offres.idOffre = eleves.idOffre ';
        $sql .= 'WHERE startDate BETWEEN :start AND :end ';
        $sql .= 'AND matricule = :matricule ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':start', $start, PDO::PARAM_STR, 20);
        $requete->bindParam(':end', $end, PDO::PARAM_STR, 20);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $liste[] = array(
                    'id' => 'Rem_'.$ligne['idOffre'],
                    'title' => '[Remédiation] '.$ligne['title'],
                    'enonce' => mb_strimwidth(strip_tags(html_entity_decode($ligne['enonce'])), 0, 200,'...'),
                    'className' => 'remediation',
                    'start' => $ligne['startDate'],
                    'end' => $ligne['endDate'],
                    'allDay' => 0,
                    'destinataire' => $matricule,
                    'cours' => $ligne['acronyme'],
                    'type' => 'regular',
                    );
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retrouve la liste des événements personnels entre deux dates destinées à un élève dont on fournit le matricule
     *
     * @param $start date de début
     * @param $end date de fin
     * @param int $matricule
     *
     * @return array
     */
    public function retreivePersonnalEvents($start, $end, $matricule, $startEditable = false) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, matricule, idCategorie, title, enonce, startDate, endDate, allDay, lastModif ';
        $sql .= 'FROM '.PFX.'thotJdcEleve AS jdceEleve ';
        $sql .= 'WHERE startDate BETWEEN :start AND :end ';
        $sql .= 'AND matricule = :matricule ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':start', $start, PDO::PARAM_STR, 20);
        $requete->bindParam(':end', $end, PDO::PARAM_STR, 20);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $liste[] = array(
                    'id' => 'Perso_'.$ligne['id'],
                    'title' => $ligne['title'],
                    'enonce' => mb_strimwidth(strip_tags(html_entity_decode($ligne['enonce'])), 0, 200,'...'),
                    'className' => 'jdcPerso',
                    'start' => $ligne['startDate'],
                    'end' => $ligne['endDate'],
                    'allDay' => ($ligne['allDay'] != 0) ? true : false,
                    'type' => 'personnal',
                    'startEditable' => $startEditable,
                    'durationEditable' => $startEditable,
                    );
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

        /**
     * retrouve la liste des événements personnels partagés avec l'utilisateur
     *
     * @param string $start : date de début
     * @param string $end : date de fin
     * @param int $matricule
     * @param boolean $startEditable = false : l'événement peut être modifié dans FC
     *
     * @return array
     */
    public function retreiveSharedEvents ($start, $end, $matricule, $startEditable = false) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idJdc, matricule, idCategorie, title, enonce, startDate, endDate, allDay, lastModif, proprietaire ';
        $sql .= 'FROM '.PFX.'thotJdcPartage AS partage ';
        $sql .= 'JOIN '.PFX.'thotJdcEleve AS tjdc ON tjdc.id = partage.idJdc ';
        $sql .= 'WHERE categorie = "classe" AND destinataire IN (SELECT groupe FROM '.PFX.'eleves WHERE matricule = :matricule) ';
        $sql .= 'OR categorie = "eleve" AND destinataire = :matricule ';
        $sql .= 'OR categorie = "coursGrp" AND destinataire IN (SELECT coursGrp FROM '.PFX.'elevesCours WHERE matricule = :matricule) ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':start', $start, PDO::PARAM_STR, 20);
        $requete->bindParam(':end', $end, PDO::PARAM_STR, 20);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $liste[] = array(
                    'id' => 'Shared_'.$ligne['idJdc'],
                    'title' => $ligne['title'],
                    'enonce' => mb_strimwidth(strip_tags(html_entity_decode($ligne['enonce'])), 0, 200,'...'),
                    'className' => 'jdcShared',
                    'start' => $ligne['startDate'],
                    'end' => $ligne['endDate'],
                    'allDay' => ($ligne['allDay'] != 0) ? true : false,
                    'proprietaire' => $ligne['proprietaire'],
                    'type' => 'shared',
                    'startEditable' => $startEditable,
                );
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    public function retreiveCoachingEvents($start, $end, $matricule) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, matricule, proprietaire, dp.nom, dp.prenom, dp.sexe, dp.mail, ';
        $sql .= 'date, heure, duree, jdc ';
        $sql .= 'FROM '.PFX.'athena AS da ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = da.proprietaire ';
        $sql .= 'WHERE matricule = :matricule AND jdc = 1 ';
        $sql .= 'AND date BETWEEN :start AND :end ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':start', $start, PDO::PARAM_STR, 20);
        $requete->bindParam(':end', $end, PDO::PARAM_STR, 20);
        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $sexe = $ligne['sexe'];
                $initiale = substr($ligne['prenom'], 0, 1);
                $formule = ($sexe == 'M') ? 'Monsieur' : 'Madame';
                $nomCoach = sprintf('%s %s. %s', $formule, $initiale, $ligne['nom']);
                $startDate = sprintf('%s %s', $ligne['date'], $ligne['heure']);
                $endTime = new DateTime($ligne['date']);
                $endTime->add(new DateInterval('PT'.$ligne['duree'].'M'));
                $endDate = $endTime->format('Y-m-d H:i');
                $liste[] = array (
                    'id' => 'Coach_'.$ligne['id'],
                    'title' => 'Coaching',
                    'enonce' => 'RV avec '.$nomCoach,
                    'className' => 'coaching',
                    'start' => $startDate,
                    'end' => $endDate,
                    'allDay' => 0,
                    'destinataire' => $matricule,
                    'cours' => 'Soutien scolaire',
                    'type' => 'coaching',
                );
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }


    /**
     * retrouve une notification dont on fournit l'identifiant.
     *
     * @param int $itemId : l'identifiant dans la BD
     *
     * @return array
     */
    public function getTravail($itemId)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = "SELECT destinataire, type, proprietaire, redacteur, title, enonce, class, id, DATE_FORMAT(startDate,'%d/%m/%Y') AS startDate, ";
        $sql .= "DATE_FORMAT(startDate,'%H:%i') AS heureDebut, DATE_FORMAT(endDate,'%H:%i') AS heureFin, TIMEDIFF(endDate, startDate) AS duree, allDay, DATE_FORMAT(lastModif, '%d/%m/%Y %H:%i') AS lastModif, ";
        $sql .= 'jdc.idCategorie, categorie, sexe, nom, prenom, libelle, nbheures, nomCours ';
        $sql .= 'FROM '.PFX.'thotJdc AS jdc ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS dp ON dp.acronyme = jdc.proprietaire ';
        $sql .= 'JOIN '.PFX.'thotJdcCategories AS cat ON cat.idCategorie = jdc.idCategorie ';
        $sql .= 'LEFT JOIN '.PFX."cours AS dc ON cours = SUBSTR(destinataire,1,LOCATE('-',destinataire)-1) ";
        $sql .= 'LEFT JOIN '.PFX.'profsCours AS dpc ON dpc.coursGrp = destinataire ';
        $sql .= 'WHERE id= :itemId ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        $travail = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $travail = $requete->fetch();

            if($travail != Null) {
                if (isset($travail['sexe'])) {
                    if ($travail['sexe'] == 'F') {
                        $nom = 'Mme ';
                    } else {
                        $nom = 'M. ';
                        }
                    }
                    else $nom = '';

                if ($travail['prenom'] != '') {
                    $nom .= mb_substr($travail['prenom'], 0, 1, 'UTF-8').'.';
                }
                $travail['nom'] = $nom.' '.$travail['nom'];

                $travail['heure'] = date('H:i', strtotime($travail['heureDebut']));
                $travail['heureFin'] = date('H:i', strtotime($travail['heureFin']));
                $travail['duree'] = date('H:i', strtotime($travail['duree']));
                if ($travail['allDay'] == 0) {
                    unset($travail['allDay']);
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $travail;
    }

    /**
     * retrouve la remédiation dont on fournit l'identifiant
     *
     * @param int $id : identifiant de la remédiation
     *
     * @return array
     */
    public function getRemediation($id) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT DATE_FORMAT(startDate, "%H:%i") AS heure, DATE_FORMAT(startDate,"%d/%m/%Y") AS date, ';
        $sql .= 'TIMEDIFF(endDate, startDate) AS duree, local, title, contenu AS enonce, ';
        $sql .= 'offre.acronyme AS proprietaire, nom, prenom, sexe ';
        $sql .= 'FROM '.PFX.'remediationOffre AS offre ';
        $sql .= 'JOIN '.PFX.'remediationEleves AS eleves ON eleves.idOffre = offre.idOffre ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS profs ON profs.acronyme = offre.acronyme ';
        $sql .= 'WHERE offre.idOffre = :id ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':id', $id, PDO::PARAM_INT);

        $travail = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $travail = $requete->fetch();
            $travail['nomProf'] = ($travail['sexe'] == 'F') ? 'Mme ' : 'M. ';
            $travail['nomProf'] .= mb_substr($travail['prenom'], 0, 1, 'UTF-8').'. '.$travail['nom'];
        }

        Application::DeconnexionPDO($connexion);

        return $travail;
    }

    /**
     * retrouve la note personnelle dont on fournit l'identifiant
     *
     * @param int $id : identifiant de la note personnelle
     *
     * @return array
     */
    public function getNotePerso($id){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, matricule, idCategorie, title, enonce, DATE_FORMAT(startDate,"%d/%m/%Y") AS startDate, ';
        $sql .= 'DATE_FORMAT(startDate,"%H:%i") AS heure, endDate, DATE_FORMAT(TIMEDIFF(endDate, startDate), "%H:%i") AS duree, allDay ';
        $sql .= 'FROM '.PFX.'thotJdcEleve ';
        $sql .= 'WHERE id = :id ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':id', $id, PDO::PARAM_INT);

        $travail = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $travail = $requete->fetch();
        }

        Application::DeconnexionPDO($connexion);

        return $travail;
    }

    /**
     * retrouve les informations sur le RV de coaching dont on fournit l'identifiant
     *
     * @param int $id : identifiant du RV coaching dans didac_athena
     *
     * @return array
     */
    public function getCoaching($id){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, matricule, DATE_FORMAT(date,"%d/%m/%Y") AS startDate, DATE_FORMAT(heure,"%H:%i") AS heureDebut, ';
        $sql .= 'duree, proprietaire, sexe, nom, prenom ';
        $sql .= 'FROM '.PFX.'athena AS da ';
        $sql .= 'LEFT JOIN '.PFX.'profs AS de ON de.acronyme = proprietaire ';
        $sql .= 'WHERE id = :id ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':id', $id, PDO::PARAM_INT);

        $travail = array();
        $resultat = $requete->execute();
        if ($resultat){
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            $travail = $requete->fetch();
            $travail['nomProf'] = ($travail['sexe'] == 'F') ? 'Mme ' : 'M. ';
            $travail['nomProf'] .= mb_substr($travail['prenom'], 0, 1, 'UTF-8').'. '.$travail['nom'];
            $travail['enonce'] = 'Travail avec '.$travail['nomProf'];
        }

        Application::DeconnexionPDO($connexion);

        return $travail;
    }

    /**
     * retrouve les PJ liées au JDC dont on fournit l'identifiant $idJdc
     *
     * @param int $idJdc : l'identifiant du journal de classe
     *
     * @return array : la liste des fichiers joints
     */
    public function getPj($idJdc) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT tjdc.shareId, shares.fileId, path, fileName ';
        $sql .= 'FROM '.PFX.'thotJdcPJ AS tjdc ';
        $sql .= 'JOIN '.PFX.'thotShares AS shares ON shares.shareId = tjdc.shareId ';
        $sql .= 'JOIN '.PFX.'thotFiles AS files ON files.fileId = shares.fileId ';
        $sql .= 'WHERE idJdc = :idJdc ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':idJdc', $idJdc, PDO::PARAM_INT);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $shareId = $ligne['shareId'];
                $liste[$shareId] = $ligne;
            }
        }

        Application::deconnexionPDO($connexion);

        return $liste;
    }

    /**
     * vérification du caractère "éditable" d'une note au JDC rédigée par un élève
     * la note est éditable si elle vient du formulaire 'write', quelle a été rédigée par l'élève $matricule
     * et que le prof ne l'a pas encore approuvée
     *
     * @param array $travail
     * @param int $redacteur : matricule de l'élève rédacteur
     * @param string $origine
     *
     * @return bool
     */
    public function editable($travail, $redacteur, $origine) {
        return ($travail['proprietaire'] == '') && ($travail['matricule'] = $redacteur) && ($origine == 'write');
    }

    /**
     * retourne les différentes catégories de travaux disponibles (interro, devoir,...).
     *
     * @param void
     *
     * @return array
     */
    public function categoriesTravaux()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT idCategorie, categorie ';
        $sql .= 'FROM '.PFX.'thotJdcCategories ';
        $sql .= 'ORDER BY ordre ';
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $id = $ligne['idCategorie'];
                $liste[$id] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * retourne true si l'élève dont on indique le matricule est en charge du JDC à la date actuelle
     *
     * @param int $matricule
     *
     * @return boolean
     */
    public function isChargeJDC($matricule) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT dateDebut, dateFin, NOW() AS today ';
        $sql .= 'FROM '.PFX.'thotJdcEleves ';
        $sql .= 'WHERE matricule = :matricule ';

        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $enCharge = false;
        $resultat = $requete->execute();
        if ($resultat) {
            $ligne = $requete->fetch();
            $dateDebut = isset($ligne['dateDebut']) ? $ligne['dateDebut'] : Null;
            $dateFin = isset($ligne['dateFin']) ? $ligne['dateFin'] : Null;
            $today = $ligne['today'];
            if (($dateDebut != Null && $dateFin != Null) && (($dateDebut <= $today) && ($dateFin >= $today)))
                $enCharge = true;
        }

        Application::DeconnexionPDO($connexion);

        return $enCharge;
    }

    /**
     * renvoie la liste des heures de cours données dans l'école.
     *
     * @param void
     *
     * @return array
     */
    public function lirePeriodesCours()
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT debut, fin ';
        $sql = "SELECT DATE_FORMAT(debut,'%H:%i') as debut, DATE_FORMAT(fin,'%H:%i') as fin ";
        $sql .= 'FROM '.PFX.'presencesHeures ';
        $sql .= 'ORDER BY debut, fin';

        $resultat = $connexion->query($sql);
        $listePeriodes = array();
        $periode = 1;
        if ($resultat) {
            while ($ligne = $resultat->fetch()) {
                $debut = $ligne['debut'];
                $fin = $ligne['fin'];
                $listePeriodes[$periode++] = array('debut' => $debut, 'fin' => $fin);
            }
        }
        Application::deconnexionPDO($connexion);

        return $listePeriodes;
    }

    /**
     * renvoie l'heure de la période de cours la plus proche de l'heure passée en argument
     *
     * @param string $heure
     *
     * @return string
     */
    public function heureLaPlusProche($heure){
        $listePeriodes = $this->lirePeriodesCours();
        $time = explode(':', $heure);
        $time = mktime($heure[0], $heure[1]);

        $n = 1;
        while (($listePeriodes[$n]['fin'] < $heure) && ($n < count($listePeriodes))) {
            $n++;
        }
        $timeDebut = explode(':', $listePeriodes[$n]['debut']);
        $timeFin = explode(':', $listePeriodes[$n]['fin']);

        if (((float) $time - (float) $timeDebut) > ((float) $timeFin - (float) $time))
            return $listePeriodes[$n]['debut'];
            else return $listePeriodes[$n]['fin'];
    }

    /**
     * enregistre une notification au JDC.
     *
     * @param array $post : tout le contenu du formulaire
     * @param int $matricule : matricule de l'élève
     *
     * @return int: nombre d'enreigstrements résussis (0 ou 1)
     */
    public function saveJdc($post, $matricule)
    {
        $id = isset($post['id']) ? $post['id'] : Null;
        $idCategorie = $post['idCategorie'];
        $date = Application::dateMysql($post['date']);
        $duree = isset($post['duree']) ? $post['duree'] : Null;
        $allDay = isset($post['journee']) ? 1 : 0;
        $titre = $post['titre'];
        $enonce = $post['enonce'];

        if ($allDay == 0) {
            $heure = $post['heure'];
            $startDate = $date.' '.$heure;
            // $duree peut être exprimé en minutes ou en format horaire hh:mm
            $duree = $post['duree'];
            if (!is_numeric($duree)) {
                if (strpos($duree,':') > 0) {
                    // c'est sans doute le format hh::mm
                    $duree = explode(':', $duree);
                    $duree = $duree[0] * 60 + $duree[1];
                }
                else $duree = 0;
            }

            $endDate = new DateTime($startDate);
            $endDate->add(new DateInterval('PT'.$duree.'M'));
            $endDate = $endDate->format('Y-m-d H:i');
        } else {
            $duree = null;
            $startDate = $date.' '.'00:00';
            $endDate = $startDate;
        }

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        if ($id == null) {
            // nouvel enregistrement
            $sql = 'INSERT INTO '.PFX.'thotJdcEleve ';
            $sql .= 'SET matricule = :matricule, idCategorie = :idCategorie, ';
            $sql .= 'title = :title, enonce = :enonce, startDate = :startDate, endDate = :endDate, allDay = :allDay, lastModif = NOW() ';
        } else {
            // simple mise à jour
            $sql = 'UPDATE '.PFX.'thotJdcEleve ';
            $sql .= 'SET matricule = :matricule, idCategorie = :idCategorie, ';
            $sql .= 'title = :title, enonce = :enonce, startDate = :startDate, endDate = :endDate, allDay = :allDay, lastModif = NOW() ';
            $sql .= 'WHERE id= :id ';
        }
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
        $requete->bindParam(':title', $titre, PDO::PARAM_STR, 40);
        $requete->bindParam(':enonce', $enonce, PDO::PARAM_STR);
        $requete->bindParam(':startDate', $startDate, PDO::PARAM_STR, 19);
        $requete->bindParam(':endDate', $endDate, PDO::PARAM_STR, 19);
        $requete->bindParam(':allDay', $allDay, PDO::PARAM_INT);
        if ($id != Null) {
            $requete->bindParam(':id', $id, PDO::PARAM_INT);
        }

        $resultat = $requete->execute();
        if ($id == null) {
            $id = $connexion->lastInsertId();
        }
        Application::DeconnexionPDO($connexion);

        return $id;
    }

    /**
     * Vérifie si la note d'identifiant $id a bien été rédigée par le rédacteur $matricule
     *
     * @param int $id : identifiant de la note au Jdc
     * @param int $matricule : de l'élève
     *
     * @return int l'identifiant s'il correspond au critère, sinon -1
     */
    public function verifIdRedacteur($id, $matricule) {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, redacteur ';
        $sql .= 'FROM '.PFX.'thotJdc ';
        $sql .= "WHERE id='$id' AND redacteur = '$matricule' ";

        $id = -1;
        $resultat = $connexion->query($sql);
        if ($resultat){
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            $ligne = $resultat->fetch();
            $id = $ligne['id'];
        }

        Application::deconnexionPDO($connexion);

        return $id;
    }

    /**
     * suppression d'une notification au journal de classe.
     *
     * @param int $id : l'identifiant de l'enregistrement
     * @param int $redacteur : le matricule de l'élève (sécurité)
     *
     * @return boolean
     */
    public function deleteJdc($id, $matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'DELETE FROM '.PFX.'thotJdcEleve ';
        $sql .= 'WHERE id = :id AND matricule = :matricule ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':id', $id, PDO::PARAM_INT);

        $resultat = $requete->execute();

        Application::DeconnexionPDO($connexion);

        return $resultat;
    }

    /**
     * renvoie les notes du JDC comprises entre la date "from" et la date "to"
     * en tenant compte des options d'impression: rien que les matières vues et/ou tout
     *
     * @param array $form : formulaire provenant de la boîte modale "modalPrintJDC"
     * @param string $acronyme : identifiant de l'utilisateur (sécurité)
     *
     * @return array
     */
    public function fromToJDCList($startDate, $endDate, $listeCours, $listeCategories) {
        $startDate = Application::dateMysql($startDate).' 00:00';
        $endDate = Application::dateMysql($endDate).' 23:59';

        $listeCoursString = is_array($listeCours) ? "'".implode("','", $listeCours)."'" : "'".$listeCours."'";
        $listeCategoriesString = is_array($listeCategories) ? implode(',', $listeCategories) : "'".$listeCategories."'";

        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, categorie, libelle, type, destinataire, title, enonce, proprietaire, startDate, endDate, dtjdc.idCategorie ';
        $sql .= 'FROM '.PFX.'thotJdc AS dtjdc ';
        $sql .= 'JOIN '.PFX.'thotJdcCategories AS cate ON cate.idCategorie = dtjdc.idCategorie ';
        $sql .= 'JOIN '.PFX."cours AS dc ON dc.cours = SUBSTR(destinataire, 1, LOCATE('-', destinataire)-1) ";
        $sql .= 'WHERE startDate >= :startDate AND endDate <= :endDate ';
        $sql .= "AND dtjdc.idCategorie IN (".$listeCategoriesString.") ";
        $sql .= 'AND destinataire IN ('.$listeCoursString.') ';
        $sql .= 'ORDER BY startDate, destinataire, dtjdc.idCategorie ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':startDate', $startDate, PDO::PARAM_STR, 15);
        $requete->bindParam(':endDate', $endDate, PDO::PARAM_STR, 15);

        $liste = array();
        $resultat = $requete->execute();
        if ($resultat) {
            $requete->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $requete->fetch()){
                $id = $ligne['id'];
                $startDate = explode(' ', $ligne['startDate']);
                $endDate = explode(' ', $ligne['endDate']);
                if ($startDate == $endDate) {
                  $ligne['startDate'] = 'Toute la journée';
                }
                else {
                  $ligne['startDate'] = Application::datePHP($startDate[0]);
                }
                $type = $ligne['type'];
                if ($type = 'coursGrp') {
                    $pattern = '/.*:([A-Z0-9]*)-[0-9]*/';
                    preg_match($pattern, $ligne['destinataire'], $matches);
                    $ligne['dest'] = $matches[1];
                }
                else $ligne['dest'] = '';

                $ligne['startHeure'] = $startDate[1];
                $ligne['endDate'] = Application::datePHP($endDate[0]);
                $ligne['endHeure'] = $endDate[1];
                $ligne['enonce'] = strip_tags($ligne['enonce'], '<br><p><a>');
                $liste[$id] = $ligne;
            }

        Application::deconnexionPDO($connexion);

        return $liste;
        }
    }

    /**
     * vérification qu'une note personnelle $id appartient bien à l'utilisateur $matricule
     *
     * @param string $id : identifiant de la note (préfixée de "Perso_")
     * @param int $matricule
     *
     * @return boolean
     */
    public function verifIdProprio($id, $matricule){
        $idPerso = explode('_', $id)[1];
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT id, matricule ';
        $sql .= 'FROM '.PFX.'thotJdcEleve ';
        $sql .= 'WHERE id = :id AND matricule = :matricule ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);
        $requete->bindParam(':id', $idPerso, PDO::PARAM_INT);

        $ok = false;
        $resultat = $requete->execute();
        if ($resultat) {
            $ligne = $requete->fetch();
            $leMatricule = $ligne['matricule'];
            $idPerso = $ligne['id'];

            $ok = ($leMatricule == $matricule) && ('Perso_'.$idPerso == $id);
        }

        Application::deconnexionPDO($connexion);

        return ($ok == 1) ? $id : -1;
    }

    /**
     * modification d'un événement existant dans le JDC perso
     *
     * @param string $id : identifiant de la note (préfixée par "Perso_")
     * @param string $start
     * @param string $end
     * @param int $allDay
     *
     * @return int : nombre d'enregistrements (0 ou 1)
      */
    public function modifEvent($id, $startDate, $endDate, $allDay){
        $idPerso = explode('_', $id)[1];
        $allDay = ($allDay == false) ? 0 : 1;
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'UPDATE '.PFX.'thotJdcEleve ';
        $sql .= 'SET startDate = :startDate, endDate = :endDate, allDay = :allDay ';
        $sql .= 'WHERE id = :id ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':startDate', $startDate, PDO::PARAM_STR, 15);
        $requete->bindParam(':endDate', $endDate, PDO::PARAM_STR, 15);
        $requete->bindParam(':allDay', $allDay, PDO::PARAM_INT);
        $requete->bindParam(':id', $idPerso, PDO::PARAM_INT);

        $resultat = $requete->execute();

        Application::deconnexionPDO($connexion);

        return $resultat;
    }


    /**
     * retourne l'image en base64 de l'horaire de l'élève $matricule depuis le répertoire $directory
     *
     * @param string $directory : le répertoire où se trouve l'image
     * @param int $matricule
     *
     * @return string
     */
    public function getHoraire($directory, $matricule){
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT nomImage, nomSimple ';
        $sql .= 'FROM '.PFX.'EDTeleves ';
        $sql .= 'WHERE matricule = :matricule ';
        $requete = $connexion->prepare($sql);

        $requete->bindParam(':matricule', $matricule, PDO::PARAM_INT);

        $src = '';
        $resultat = $requete->execute();
        if ($resultat) {
            $ligne = $requete->fetch();
			if ($ligne != false) {
				$image = $directory.'/'.$ligne['nomImage'];
				$imageData = base64_encode(file_get_contents($image));
				$src = 'data: '.mime_content_type($image).';base64,'.$imageData;
				}
            }

        Application::deconnexionPDO($connexion);

        return $src;
    }


}
