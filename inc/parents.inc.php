<?php

switch ($mode) {
    case 'addParent':
        // vérifier que l'adresse mail net le nom d'utilisateur ne sont pas déjà utilisés
        $mail = $_POST['mail'];
        $matricule = $_POST['matricule'];
        $userName = $_POST['userName'];

        $motifRefus = '';

        // le parent aurait-il ajouté lui-même le matricule de l'enfant?
        $end = substr($userName, strlen($userName) - strlen($matricule), strlen($matricule));
        $problemeUserName = ($end == $matricule);
        if ($problemeUserName) {
            $motifRefus .= "N'indiquez pas le matricule de votre enfant; il sera ajouté automatiquement.";
        }

        $userName = $_POST['userName'].$matricule;
        $problemeUserName = $User->userExists($userName);
        if ($problemeUserName) {
            $motifRefus .= "Le nom d'utilisateur <strong>$userName</strong> est déjà utilisé pour une autre personne.<br>";
        }
        $smarty->assign('motifRefus', $motifRefus);

        if ($motifRefus == '') {
            // on enregistre ces informations s'il n'y a pas de motif de refus
            $nb = $Application->saveParent($_POST);
            $message = array(
                'title' => 'Confirmation nécessaire',
                'texte' => sprintf('Un mail vient d\'être envoyé à l\'adresse %s; veuillez cliquer sur le lien qui y figure pour confirmer cette adresse.', $mail),
                'urgence' => DANGER, );
            $smarty->assign('message', $message);
            // on envoie le mail de demande de confirmation de l'adresse
            $mail = $Application->sendConfirmMail($userName);
        } else {
                // sinon, on renvoie toutes les informations dans le formulaire
                $smarty->assign('formule', $_POST['formule']);
                $smarty->assign('nomParent', $_POST['nomParent']);
                $smarty->assign('prenomParent', $_POST['prenomParent']);
                if ($problemeUserName == false) {
                    // supprimer le matricule du nom d'utilisateur à présenter
                    $to = strrpos($userName, $matricule, -1);
                    $userName = substr($userName, 0, $to);
                    $smarty->assign('userName', $userName);
                }

                $smarty->assign('mail', $mail);
                $smarty->assign('matricule', $matricule);
                $smarty->assign('lien', $_POST['lien']);
                $smarty->assign('onglet', 'inviter');
            }
        break;

    default:
        # code...
        break;
    }

$listeParents = $Application->listeParents($matricule);
$smarty->assign('listeParents', $listeParents);
$smarty->assign('matricule', $matricule);
$smarty->assign('corpsPage', 'parents');
