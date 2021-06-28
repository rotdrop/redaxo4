<?php

/**
 * Funktionen zum handeln von magic_quotes=off
 * @package redaxo4
 * @version svn:$Id$
 */

if (!function_exists('get_magic_quotes_gpc') || !get_magic_quotes_gpc()) {
    function addSlashesOnArray(&$theArray)
    {
        if (is_array($theArray)) {
            reset($theArray);
            foreach ($theArray as $Akey=>$AVal) {
                if (is_array($AVal)) {
                    addSlashesOnArray($theArray[$Akey]);
                } else {
                    $theArray[$Akey] = addslashes($AVal);
                }
            }
            reset($theArray);
        }
    }

    if (is_array($_GET)) {
            addSlashesOnArray($_GET);
    }

    if (is_array($_POST)) {
            addSlashesOnArray($_POST);
    }

    if (is_array($_REQUEST)) {
            addSlashesOnArray($_REQUEST);
    }

}
