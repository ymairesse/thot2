<?php

$md5conf = $Application->saveRv($_POST);

$rv = $Application->getRvById($_POST['id']);
$mailParent = $rv['email'];
$nomParent = sprintf('Au parent de %s %s', $rv['nom'], $rv['prenom']);

$smarty->assign('rv',$rv);
$texteMail = $smarty->fetch('templates/mail/texteMail.tpl');
$disclaimer = "<div style='font-size:small'><a href='http://www.isnd.be/disclaimer/disclaimer.htm'>Clause de non responsabilité</a></div>";
$texteMail .= "<hr> $disclaimer";
$objet = "ISND: Veuillez confirmer votre demande de RV";

// envoi du mail avec le lien
$expediteur = 'admin@isnd.be';
$smarty->assign('expediteur',$expediteur);
$nomExpediteur = 'Administrateur ISND';
$smarty->assign('nomExpediteur',$nomExpediteur);

// ajout de la signature
$signature = $smarty->fetch('templates/mail/signature.tpl');
$texteMail .= $signature;

require_once '../phpMailer/class.phpmailer.php';
$mail = new PHPmailer();
$mail->IsHTML(true);
$mail->CharSet = 'UTF-8';
$mail->From = $expediteur;
$mail->FromName = $nomExpediteur;
//
// envoi du mail au parent
$mail->AddAddress($mailParent, $nomParent);

// envoyer le mail à l'expéditeur aussi
$mail->AddBCC($expediteur, $nomExpediteur);

$mail->Subject = $objet;
$mail->Body = $texteMail;

$envoiMail = ($mail->Send());

$smarty->assign('corpsPage','rvAconfirmer');
