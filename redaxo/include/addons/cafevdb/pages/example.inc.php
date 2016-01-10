<?php

$displayMethod = new ReflectionMethod('cafevdb', 'displayProjectEvents');
$filename = $displayMethod->getFileName();
$startLine = $displayMethod->getStartLine() - 1;
$endLine = $displayMethod->getEndLine();
$length = $endLine - $startLine;
$displaySource = implode("", array_slice(file($filename), $startLine, $length));
$displaySource = '<?php
'.$displaySource.'
?>';

$rehearsalInput = '<strong>Projekt Name</strong><br />
<input type="text" size="80" name="VALUE[1]" value="REX_VALUE[1]" />
<br /><br />
<strong>Probenplan</strong><br />
<label for="externalEventList">
<input id="externalEventList" type="checkbox" name="VALUE[4]" value="on"
            <?php echo ("REX_VALUE[4]" == "on" ? \'checked="checked"\' : \'\'); ?> />
Owncloud Event-Liste</label>
<textarea name="VALUE[2]" class="tinyMCEEditor" style="width:700px; height:150px;">
REX_VALUE[2]
</textarea>
<br /><br />
<strong>Weitere Details falls nötig</strong><br />
<textarea name="VALUE[3]" class="tinyMCEEditor" style="width:700px; height:450px;">
REX_VALUE[3]
</textarea>
';

$rehearsalOutput = '<div class="item-text">
<?php
$out =\'\';
$nl = "\n";

if (REX_IS_VALUE[2] || REX_IS_VALUE[4]) {
  if (REX_IS_VALUE[1]) {
    $out .= \'<div class="marginalie">\'.$nl;
    $out .= \'<h1>\';
    $out .=<<<EOD
REX_HTML_VALUE[1]
EOD;
    $out .= \'</h1>\'.$nl;
    $out .= \'</div>\'.$nl;
  }
  if (REX_IS_VALUE[4]) {
    $cafevdb = new cafevdb();
    $out .= $cafevdb->displayProjectEvents(REX_ARTICLE_ID, !REX_IS_VALUE[1]);
  }
  $out .=<<< EOD
REX_HTML_VALUE[2]
EOD;
}
if (REX_IS_VALUE[3]) {
  $out .= \'<div class="marginalie">\'.$nl;
  $out .= \'<h2>Weitere Informationen</h1>\'.$nl;
  $out .= \'</div>\'.$nl;
  $out .=<<<EOD
<HR/>
REX_HTML_VALUE[3]
EOD;
}
if ($REX[\'GG\']) {
  echo replaceMailto($out);
} else {
  echo $out;
}
?>
</div>';

?>

<div class="rex-addon-output">
  <h2 class="rex-hl2"><?php echo $I18N->msg('cafevdb_example_module_headline'); ?></h2>
  <h3 class="rex-hl3"><?php echo $I18N->msg('cafevdb_example_input'); ?></h3>
  <div class="rex-addon-content">
    <?php rex_highlight_string($rehearsalInput); ?>
  </div>
  <h3 class="rex-hl3"><?php echo $I18N->msg('cafevdb_example_output'); ?></h3>
  <div class="rex-addon-content">
    <?php rex_highlight_string($rehearsalOutput); ?>
  </div>
  <h2 class="rex-hl2"><?php echo $I18N->msg('cafevdb_display_code'); ?></h2>
  <div class="rex-addon-content">
    <?php rex_highlight_string($displaySource); ?>
  </div>
</div>
