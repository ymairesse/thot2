<?php

$listeDatesRV = $Application->listeDatesRV($contact);
$smarty->assign('listeDatesRV',$listeDatesRV);

$smarty->assign('corpsPage','choixDateHeure');
