<?php

/**
 * CAFeV Addon
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 *
 */

$Basedir = dirname(__FILE__);

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

require $REX['INCLUDE_PATH'] . '/layout/top.php';

$subpages = array(
  array('', $I18N->msg('cafevdb_configuration')),
  array('example', $I18N->msg('cafevdb_example')),
  );

rex_title($I18N->msg('cafevdb_title'), $subpages);

switch ($subpage) {
case 'example':
  require $Basedir . '/example.inc.php';
  break;
default:
  require $Basedir . '/settings.inc.php';
}

require $REX['INCLUDE_PATH'] . '/layout/bottom.php';

?>
