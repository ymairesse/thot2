<?php

switch ($mode) {
    case 'envoyer':
        $acronyme = isset($_POST['acronyme']) ? $_POST['acronyme'] : null;
        $objet = isset($_POST['objet']) ? $_POST['objet'] : null;
        $texte = isset($_POST['texte']) ? $_POST['texte'] : null;
        $prof = $Application->identiteProf($acronyme);
        require_once INSTALL_DIR.'/phpMailer/class.phpmailer.php';

        $mailProf = new PHPmailer();
        $mailProf->IsHTML(true);
        $mailProf->CharSet = 'UTF-8';
        $mailProf->From = $User->getMail();
        $mailProf->FromName = $User->getNom();
        $mailProf->AddAddress($prof['mail']);
        $mailProf->Subject = $objet;
        $mailProf->Body = $texte;
        $profOK = true; $parentOK = false;
        // envoi du mail au prof
        $profOK = $mailProf->Send();

        // envoi d'une copie du mail au parent si mail au prof = OK
        if ($profOK) {
            $mailParent = new PHPmailer();
            $mailParent->IsHTML(true);
            $mailParent->CharSet = 'UTF-8';
            $mailParent->From = NOANSWER;
            $mailParent->FromName = NAMENOANSWER;
            $mailParent->AddAddress($User->getMail());
            $mailParent->Subject = $objet;
            $avertissement = file_get_contents('templates/contact/avertissementMail.tpl');
            $mailParent->Body = sprintf('Copie de votre mail à %s %s %s', $prof['formule'], $prof['initiale'], $prof['nom']).'<br>'.$texte.$avertissement;
            $mailParent->AddAddress($User->getNom());
            $parentOK = $mailParent->Send();
        }

        // message par défaut; pourrait être remplacé ensuite
        $texte = "L'envoi du message a échoué. Veuillez vérifier votre connexion à l'Internet.";
        if ($profOK) {
            $texte = sprintf('Votre message à %s %s %s a été envoyé', $prof['formule'], $prof['initiale'], $prof['nom']);
        }
        if ($parentOK) {
            $texte .= sprintf('<br>Une copie de votre message a été transmise à votre adresse %s', $User->getMail());
        } else {
            $texte .= sprintf('<br>Nous n\'avons pas pu envoyer une copie à votre adresse', $User->getMail());
        }
        if ($profOK && $parentOK) {
            $urgence = 'warning';
        } else {
            $urgence = 'danger';
        }
        $message = array(
                'title' => "Envoi d'un message",
                'texte' => $texte,
                'urgence' => $urgence,
                );

        $smarty->assign('message', $message);
        // break;  pas de break;
    default:
        $smarty->assign('corpsPage', 'contact/formContact');
        break;
    }

$classe = $User->getClasse();

require_once INSTALL_DIR.'/inc/classes/classEcole.inc.php';
$Ecole = new Ecole();
$titus = $Ecole->titusDeGroupe($classe);

require_once INSTALL_DIR.'/inc/classes/classBulletin.inc.php';
$Bulletin = new Bulletin();
$listeCours = $Bulletin->listeCoursEleves($matricule);

$listeCoursGrp = array();
foreach ($listeCours as $cours => $data) {
    $coursGrp = $data[$matricule]['coursGrp'];
    $listeCoursGrp[$coursGrp] = $coursGrp;
}
$smarty->assign('titus', $titus);

$user = (array) $User->getIdentite($userType);

$smarty->assign('user', $user);

$listeProfs = $Bulletin->listeProfsDeListeCoursGrp($listeCoursGrp);
$listeEducs = $User->getEducsEleve();
$smarty->assign('listeProfs', $listeProfs);
$smarty->assign('listeEducs', $listeEducs);
