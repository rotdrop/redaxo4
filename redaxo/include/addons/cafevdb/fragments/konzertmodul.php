<?php
$title = '';
$subtitle = '';
$location = '';
$date = '';

if (REX_IS_VALUE[8]) {
  $cafevdb = new cafevdb();
  $concertData = $cafevdb->fetchProjectConcerts(REX_ARTICLE_ID);
  $title = $concertData['title'];
  $subtitle = $concertData['subtitle'];
  $location = implode('<br/>', $concertData['location']);
  $date = $concertData['date'];
}

// Date
$val1 = '';
if (REX_IS_VALUE[1]) {
  $val1 = htmlspecialchars("REX_VALUE[1]");
} else if ($date !== '') {
  $val1 = $date;
}
if ($val1 !== '') {
  $val1 = '<h3>'.$val1.'</h3>'."\n";
}

// Short title
$val2 = '';
if (REX_IS_VALUE[2]) {
  $val2 = htmlspecialchars_decode("REX_VALUE[2]");
  $val2 = str_replace("<br />","",$val2);
  $val2 = rex_a79_textile($val2);
  $val2 = str_replace("###","&#x20;",$val2);
  $val2 = strip_tags($val2,"<br>");
} else if ($title !== '') {
  $val2 = $title;
}
if ($val2 !== '') {
  $val2 = '<h1>'.$val2.'</h1>'."\n";
}

// Subtitle
$val3 = '';
if (REX_IS_VALUE[3]) {
  $val3 = htmlspecialchars_decode("REX_VALUE[3]");
  error_log(__METHOD__.' "'.$val3.'"');
  $val3 = str_replace("<br />","",$val3);
  $val3 = rex_a79_textile($val3);
  $val3 = str_replace("###","&#x20;",$val3);
  $val3 = strip_tags($val3,"<br>");
} else if ($subtitle !== '') {
  $val3 = $subtitle;
}
if ($val3 !== '') {
  $val3 = '<h2>'.$val3.'</h2>'."\n";
}

// Location
$val4 = '';
if (REX_IS_VALUE[4]) {
  $val4 = htmlspecialchars_decode("REX_VALUE[4]");
  $val4 = str_replace("<br />","",$val4);
  $val4 = rex_a79_textile($val4);
  $val4 = str_replace("###","&#x20;",$val4);
  $val4 = strip_tags($val4,"<br>");
} else if ($location !== '') {
  $val4 = $location;
}
if ($val4 !== '') {
  $val4 = '<p><strong>'.$val4.'</strong></p>'."\n";
}

// Participants
$val7 = '';
if (REX_IS_VALUE[7]) {
  $val7 = htmlspecialchars_decode("REX_VALUE[7]");
  $val7 = str_replace("<br />","",$val7);
  $val7 = rex_a79_textile($val7);
  $val7 = str_replace("###","&#x20;",$val7);
  $val7 = strip_tags($val7,"<br>");
  $val7 = '<p><em>'.$val7.'</em></p>'."\n";
}

?>
<div class="item-text">
<?php if ("REX_FILE[1]" != "") { // mit Bild ?>
<div class="marginalie">
   <img src="<?=$REX['HTDOCS_PATH']?>files/REX_FILE[1]" alt="REX_VALUE[2]" />
   </div>
<?=$val1?>
<?=$val2?>
<?=$val3?>
<?=$val7?>
<?=$val4?>
<?php } else { ?>
<div class="marginalie">
<?=$val1?>
<?=$val2?>
<?=$val3?>
<?=$val7?>
<?=$val4?>
   </div>
<?php } ?>
<?php
if (REX_IS_VALUE[6]) {
  $wysiwygeditor =<<<EOD
REX_HTML_VALUE[6]
EOD;
  if ($REX['GG']) { // nur im frontend span-mailtos ersetzen
    echo replaceMailto($wysiwygeditor);
  } else {
    echo $wysiwygeditor;
  }
}
?>
</div>
