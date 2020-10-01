<?php

$token = isset($_GET['token']) ? $_GET['token'] : null;

$rv = $Application->getRvByToken($token);

if ($rv == null) {
    $nb = 0;
} else {
    $id = $rv['id'];
    $nb = $Application->confirmRv($id);
    $smarty->assign('rv', $rv);
    $texteMail = $smarty->fetch('templates/mail/texteMailConf.tpl');
    $objet = 'ISND: Confirmation de votre demande de RV';
    $mailParent = $rv['email'];
    $nomParent = sprintf('Au parent de %s %s', $rv['nom'], $rv['prenom']);
    // envoi du mail avec le lien
    $expediteur = 'admin@isnd.be';
    $smarty->assign('expediteur', $expediteur);
    $nomExpediteur = 'Administrateur ISND';
    $smarty->assign('nomExpediteur', $nomExpediteur);

    // ajout de la signature
    $signature = $smarty->fetch('templates/mail/signature.tpl');
    $texteMail .= $signature;
    $disclaimer = "<div style='font-size:small'><a href='http://www.isnd.be/disclaimer/disclaimer.htm'>Clause de non responsabilité</a></div>";
    $texteMail .= "<hr> $disclaimer";

    require_once '../phpMailer/class.phpmailer.php';
    $mail = new PHPmailer();
    $mail->IsHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->From = $expediteur;
    $mail->FromName = $nomExpediteur;

    // envoi du mail au parent
    $mail->AddAddress($mailParent, $nomParent);
    $mail->Subject = $objet;
    $mail->Body = $texteMail;

    $envoiMail = ($mail->Send());
}

$smarty->assign('token', $token);
$smarty->assign('nb', $nb);
$smarty->assign('corpsPage', 'confirmation');
