<?php

require('../../config.inc.php');

require_once(INSTALL_DIR.'/inc/classes/classApplication.inc.php');
$Application = new Application();

$date = isset($_POST['date'])?$_POST['date']:Null;

if ($date != Null) {
    require_once(INSTALL_DIR.'/smarty/Smarty.class.php');
    $smarty = new Smarty();
    $smarty->template_dir = "../templates";
    $smarty->compile_dir = "../templates_c";

    $listeHeuresRV = $Application->listeHeuresRV($date);
    $smarty->assign('listeHeures', $listeHeuresRV);
    $divHeures = $smarty->fetch('listeHeuresRV.tpl');
    echo $divHeures;
}
