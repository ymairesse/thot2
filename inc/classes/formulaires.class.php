<?php

class Formulaires
{
    public function __construct()
    {
    }

    /**
     * retourne la liste structurée par type de destinataire des formulaires destinés à l'élève dont on donne le matricule et la classe.
     *
     * @param $matricule
     * @param $classe
     * @param $niveau
     *
     * @return array
     */
    public function listeFormulaires($matricule, $classe, $niveau, $listeCours)
    {
        $niveau = substr($classe, 0, 1);
        $listeCoursString = "'".implode('\',\'', $listeCours)."'";
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $sql = 'SELECT dtf.id, type, destinataire, dtf.titre, explication, dateDebut, dateFin ';
        $sql .= 'FROM '.PFX.'thotForm AS dtf ';
        $sql .= "WHERE destinataire IN ('$matricule', '$classe', '$niveau', 'ecole', $listeCoursString) ";
        $sql .= 'AND (dateFin >= NOW() AND dateDebut <= NOW()) ';
        $sql .= 'ORDER BY dateDebut ';
        
        $resultat = $connexion->query($sql);
        $liste = array();
        if ($resultat) {
            $resultat->setFetchMode(PDO::FETCH_ASSOC);
            while ($ligne = $resultat->fetch()) {
                $type = $ligne['type'];
                // $destinataire = $ligne['destinataire'];
                $id = $ligne['id'];
                $ligne['dateDebut'] = Application::datePHP($ligne['dateDebut']);
                $ligne['dateFin'] = Application::datePHP($ligne['dateFin']);

                $liste[$id] = $ligne;
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * renvoie la liste des questions pour chacun des formulaires passés en argument.
     *
     * @param array $listeFormulaires
     *
     * @return array
     */
    public function listeQuestions($listeFormulaires)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $liste = array();
        foreach ($listeFormulaires as $id => $leFormulaire) {
            $sql = 'SELECT id, numQuestion, question, type, reponses, validate ';
            $sql .= 'FROM '.PFX.'thotFormQuestions ';
            $sql .= "WHERE id='$id' ";
            $resultat = $connexion->query($sql);
            if ($resultat) {
                $resultat->setFetchMode(PDO::FETCH_ASSOC);
                while ($ligne = $resultat->fetch()) {
                    $type = $ligne['type'];
                    $reponses = $ligne['reponses'];
                    switch ($type) {
                        case 'select':
                            $ligne['reponses'] = explode('#|#', $reponses);
                            break;
                        case 'checkbox':
                            $ligne['reponses'] = explode('#|#', $reponses);
                            break;
                    }
                    $liste[$id][] = $ligne;
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * lecture de la liste des réponses aux formulaires déjà postées.
     *
     * @param array $listeFormulaires
     * @param $matricule : matricule de l'utilisateur
     *
     * @return array
     */
    public function listeReponses($listeFormulaires, $matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $liste = array();
        foreach ($listeFormulaires as $id => $leFormulaire) {
            $sql = 'SELECT id, numQuestion,reponse ';
            $sql .= 'FROM '.PFX.'thotFormReponses ';
            $sql .= "WHERE matricule='$matricule' ";

            $resultat = $connexion->query($sql);
            if ($resultat) {
                $resultat->setFetchMode(PDO::FETCH_ASSOC);
                while ($ligne = $resultat->fetch()) {
                    $id = $ligne['id'];
                    $numQuestion = $ligne['numQuestion'];
                    $liste[$id][$numQuestion][] = $ligne['reponse'];
                }
            }
        }

        Application::DeconnexionPDO($connexion);

        return $liste;
    }

    /**
     * enregistrement du contenu du formulaire.
     *
     * @param $post : le contenu du formulaire
     * @param $matricule : le matricule de l'élève
     *
     * @return int : le nombre de réponses enregistrées
     */
    public function enregistrer($post, $matricule)
    {
        $connexion = Application::connectPDO(SERVEUR, BASE, NOM, MDP);
        $form_id = isset($post['form_id']) ? $post['form_id'] : null;
        $nb = 0;
        if ($form_id != null) {
            $sql = 'INSERT INTO '.PFX.'thotFormReponses ';
            $sql .= 'SET id=:id, matricule=:matricule, numQuestion=:numQuestion, reponse=:reponse ';
            $sql .= 'ON DUPLICATE KEY UPDATE ';
            $sql .= 'reponse=:reponse ';
            $requete = $connexion->prepare($sql);
            foreach ($post as $fieldName => $value) {
                // on va chercher tous les champs nommés typeQ_xx et traiter les réponses xx en fonction
                $field = explode('_', $fieldName);
                if ($field[0] == 'typeQ') {
                    $numQuestion = $field[1];

                    $reponse = isset($post['R'][$numQuestion])?$post['R'][$numQuestion]:null;
                    if ($reponse != null) {
                        switch ($value) {
                            case 'select':
                                $data = array(
                                ':id' => $form_id,
                                ':matricule' => $matricule,
                                ':numQuestion' => $numQuestion,
                                ':reponse' => $reponse,
                                );
                                $nb += $requete->execute($data);
                                break;
                            case 'checkbox':
                                // plusieurs réponses sont possibles, on les enregistre tour à tour
                                foreach ($reponse as $wtf=>$laReponse) {
                                    $data = array(
                                    ':id' => $form_id,
                                    ':matricule' => $matricule,
                                    ':numQuestion' => $numQuestion,
                                    ':reponse' => $laReponse,
                                    );
                                    $nb += $requete->execute($data);
                                }
                                break;
                        }
                    }
                }
            }
        }
        Application::DeconnexionPDO($connexion);

        return $nb;
    }
}
