<?php

session_start();
require_once '../config.inc.php';

// définition de la class Application
require_once '../inc/classes/classApplication.inc.php';
$Application = new Application();

$classe = isset($_POST['classe']) ? $_POST['classe'] : null;
if ($classe == null) {
    die();
}

$listeEleves = $Application->listeEleves($classe);

require_once '../smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty->template_dir = '../templates';
$smarty->compile_dir = '../templates_c';

$smarty->assign('listeEleves', $listeEleves);
$smarty->display('listeEleves.tpl');
