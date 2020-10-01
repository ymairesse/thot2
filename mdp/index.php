<?php

require_once '../config.inc.php';
include INSTALL_DIR.'/inc/entetesMin.inc.php';
// ----------------------------------------------------------------------------
//
// les paramètres peuvent éventuellement servir; autant les passer à Smarty
$smarty->assign('action', $action);
$smarty->assign('mode', $mode);

// le $userName est passé en POST dans le formulaire de reset du passwd; $_REQUEST englobe $_POST et $_GET
$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;
// le $userName est passé en POST dans le formulaire de reset du passwd; $_REQUEST englobe $_POST et $_GET
$userName = isset($_REQUEST['userName']) ? $_REQUEST['userName'] : null;

switch ($mode) {
    case 'savePasswd':
        // étape finale: tout s'est bien passé et le mot de passe peut être enregistré
        // on vérifie que le $userName provenant du formulaire correspond bien à celui du token
        $userName = $Application->chercheToken($token, $userName);
        // le $userName est maintenant garanti car provenant de la BD
        $nb = $Application->savePasswd($_POST, $userName);
        if ($nb == 0) {
            $texte = 'Pas de modification enregistrée';
        }  // n'importe quel texte fait l'affaire...
        $smarty->assign('texte', $texte);
        $smarty->assign('corpsPage', 'passwdChange');
        break;

    case 'getPasswd':
        // on vérifie dans la BD si le userName correspond à un token non périmé;
        // $userName revient avec '' si token périmé ou introuvable dans la BD
        $userName = $Application->chercheToken($token, $userName);
        $identite = $Application->identiteParent($userName);
        if ($identite != '') {
            // parfait: on a un $userName et le $token correspondant, on peut proposer le reset du mdp pour ce user
            $smarty->assign('userName', $userName);
            $smarty->assign('token', $token);
            $smarty->assign('identite', $identite);
            $smarty->assign('mode', 'savePasswd');  // on force le mode à "savePasswd" dans le formulaire
            $smarty->assign('corpsPage', 'formPasswd');
        } else {
                // il y  a un souci sur le userName ou sur l'identité
                $smarty->assign('ADRESSETHOT', ADRESSETHOT);
                $smarty->assign('corpsPage', 'tokenIncorrect');
            }
        break;

    case 'sendMail':
        $identite = $Application->verifUser($userName, 'userName');

        // cela peut toujours servir
        $smarty->assign('MAILADMIN', MAILADMIN);
        $smarty->assign('identite', $identite);

        // maintenant, l'identité doit être connue (si un mail existant ou un userName existant ont été envoyés)
        if ($identite != null) {
            // retourne un lien aléatoire (md5(microtime())) enregistré dans la table lostPasswd
            $link = $Application->createPasswdLink($identite['userName']);
            // envoi effectif du mail
            $Application->mailPasswd($link, $identite, $User->identiteReseau());
        } else {
                $motifRefus = sprintf("Le nom d'utilisateur %s est inconnu.", $userName);
                $smarty->assign('motifRefus', $motifRefus);
            }
        $smarty->assign('corpsPage', 'sendMail');
        break;

    default:
        // des paramètres ont été passés, mais il manque, au moins, la valeur de $mode
        // il s'agit d'un lien incorrect
        $numArgs = count($_GET);
        if ($numArgs > 0) {
            $smarty->assign('ADRESSETHOT', ADRESSETHOT);
            $smarty->assign('corpsPage', 'tokenIncorrect');
        }
            // sinon, on présent la page "normale" pour l'indication du mail ou du userName
            else {
                $smarty->assign('corpsPage', 'userOrMail');
            }
        break;
}

//
// ----------------------------------------------------------------------------
$smarty->assign('executionTime', round($chrono->stop(), 6));
$smarty->display('index.tpl');
