<?php

if ($mode == 'editProfil') {
	
	$nb = $Application->saveProfilParent($_POST, $userName);

	$message = array(
			'title' => SAVE,
			'texte' => sprintf('%d enregistrement(s)', $nb),
			'urgence' => SUCCES, );
	$smarty->assign('message', $message);
	$User->setIdentite('parent');
		// mettre à jour la session avec les infos de l'utilisateur
	$_SESSION[APPLICATION] = serialize($User);
    
};

$identite = $User->getIdentite();

$smarty->assign('identite', $identite);
$smarty->assign('corpsPage', 'parents/profilParents');
