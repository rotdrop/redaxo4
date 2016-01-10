<?php

// some general support functions

$mypage = "cafev";

$REX['ADDON']['name'][$mypage] = 'Camerata DB';
$REX['ADDON']['version'][$mypage] = "0.1";
$REX['ADDON']['author'][$mypage] = "Claus-Justus Heine";
$REX['ADDON']['supportpage'][$mypage] = 'kommt.bitte.nicht.zu.mir.org';

if ($REX['REDAXO'] !== false) {
    // for backend only code
    $I18N->appendFile($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/lang/');
}

require_once $REX['INCLUDE_PATH'].'/addons/'.$mypage.'/classes/class.cafev.inc.php';
