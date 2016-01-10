<div class="item-text">
<?php
$out ='';

if (REX_IS_VALUE[2] || REX_IS_VALUE[4]) {
  if (REX_IS_VALUE[1]) {
    $out .= '<div class="marginalie">'."\n";
    $out .= '<h1>';
    $out .=<<<EOD
REX_HTML_VALUE[1]
EOD;
    $out .= '</h1>'."\n";
    $out .= '</div>'."\n";
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
  $out .= '<div class="marginalie">'."\n";
  $out .= '<h2>Weitere Informationen</h1>'."\n";
  $out .= '</div>'."\n";
  $out .=<<<EOD
<HR/>
REX_HTML_VALUE[3]
EOD;
}
if ($REX['GG']) echo replaceMailto($out);
else   echo $out;
?>
</div>
