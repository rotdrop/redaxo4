<?php

// some general support functions

$mypage = "cafev";

$REX['ADDON']['version'][$mypage] = "0.1";
$REX['ADDON']['author'][$mypage] = "Claus-Justus Heine";
$REX['ADDON']['supportpage'][$mypage] = 'kommt.bitte.nicht.zu.mir.org';

$basedir = dirname(__FILE__);
require_once $basedir.'/classes/class.cafev.inc.php';

if ($REX['REDAXO'] !== false)
{
    // for backend only code
}
