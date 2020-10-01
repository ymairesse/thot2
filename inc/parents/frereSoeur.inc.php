<?php

$userName = $User->getUserName();
$fratrie = $User->getComptesFratrie($userName);

$eleves = $User->getEleves4Parent($userName);

$smarty->assign('nomEleve', $User->getNomEleve());
$smarty->assign('fratrie', $fratrie);
$smarty->assign('eleves', $eleves);

$smarty->assign('corpsPage', 'parents/frereSoeur');
