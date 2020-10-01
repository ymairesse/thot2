<?php

require_once '../config.inc.php';

require_once INSTALL_DIR.'/inc/classes/classApplication.inc.php';
$Application = new Application();

require_once INSTALL_DIR.'/inc/classes/classJdc.inc.php';
$jdc = new Jdc();

$event_id = isset($_POST['event_id']) ? $_POST['event_id'] : null;

if ($event_id != null) {
    $travail = $jdc->getTravail($event_id);

    require_once INSTALL_DIR.'/smarty/Smarty.class.php';
    $smarty = new Smarty();
    $smarty->template_dir = '../templates';
    $smarty->compile_dir = '../templates_c';

    $smarty->assign('travail', $travail);
    $smarty->display('unTravail.tpl');
}
