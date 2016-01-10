<?php
/* Trennlinie falls zwei oder mehr Konzertlisten untereinander  */
if (isset($itemCount) && $itemCount) {
  $out 	 	= '<div class="item-line">&nbsp;</div>';
} else {
  $out 	 	= '';
}

/**
 * Ausgabe Konzerteliste
 *
 */

$whichId    = ("REX_VALUE[1]" != '' && intval("REX_VALUE[1]")) ? "REX_VALUE[1]" : $this->getValue('category_id');
$howMany    = ("REX_VALUE[2]" != '' && intval("REX_VALUE[2]")) ? "REX_VALUE[2]" : false; // false -> alle anzeigen
$cat 	    = OOCategory::getCategoryById($whichId);
$article    = $cat->getArticles();

$itemCount  = 0;
if (is_array($article) && count($article)) {
  foreach ($article as $key => $var) {
    if (!$var->isOnline()) continue;
    $articleId   		= $var->getId();
    $articleName 		= $var->getName();
    $articleDescription = $var->getDescription();
    if (!$var->isStartpage()) {
      $slice = OOArticleSlice::getFirstSliceForArticle($articleId,false);
      //var_dump($slice);
      if ($slice) {
        $date     = $slice->getValue(1);
        $title    = nl2br($slice->getValue(2));
        $subtitle = nl2br($slice->getValue(3));
        $peoples  = nl2br($slice->getValue(7));
        $location = nl2br($slice->getValue(4));
        $teaser	  = $slice->getValue(5);

        $ocEventList = $slice->getValue(8) === 'on';
        if ($ocEventList) {
          $cafev = new Cafev();
          $concertData = $cafev->fetchProjectConcerts($articleId);

          if ($date === '') {
            $date = $concertData['date'];
          }
          if ($title === '') {
            $title = $concertData['title'];
          }
          if ($subtitle === '') {
            $subtitle = $concertData['subtitle'];
          }
          if ($location === '') {
            $location = implode('<br/>', $concertData['location']);
          }
          if ($teaser === '') {
            $teaser = '<p>'.$concertData['teaser'].'</p>';
          }
        }

        $teaser = str_replace("###","&#x20;",$teaser);
        $teaser = rex_article::replaceLinks($teaser); // rex-links zu htmllinks
        if ($teaser && $REX['GG']) { // nur im frontend span-mailtos ersetzen
          $teaser	= replaceMailto($teaser);
        }
        $image    = $slice->getMedia(1);

        if ($itemCount) { // wenn itemCount > 0 mit Linie
          $out .= '<div class="item-text no-margin clearfix">'."\n";
          $out .= '<p class="ornament"> </p>'."\n";
        } else {
          $out .= '<div class="item-text clearfix">'."\n";
        }

        if ($image) {
          $out .= '<div class="marginalie">'."\n";
          $out .= '<img src="'.$REX['HTDOCS_PATH'].'files/'.$image.'" alt="REX_VALUE[2]" />';
          $out .= '</div>'."\n";
          $out .= ($date) ? '<h3>'.$date.'</h3>'."\n" : '';
          $out .= ($title) ? '<h1>'.$title.'</h1>'."\n" : '';
          $out .= ($subtitle) ? '<h2>'.$subtitle.'</h2>'."\n" : '';
          $out .= ($peoples) ? '<p><em>'.$peoples.'</em></p>'."\n" : '';
          $out .= ($location) ? '<p><strong>'.$location.'</strong></p>'."\n" : '';


          $out .= ($teaser) ? $teaser."\n" : '';
          $out .= '<p><a href="'.rex_getUrl($articleId).'">Mehr Informationen »</a></p>';
        } else {
          $out .= '<div class="marginalie">'."\n";
          $out .= ($date) ? '<h3>'.$date.'</h3>'."\n" : '';
          $out .= ($title) ? '<h1>'.$title.'</h1>'."\n" : '';
          $out .= ($subtitle) ? '<h2>'.$subtitle.'</h2>'."\n" : '';
          $out .= ($peoples) ? '<p><em>'.$peoples.'</em></p>'."\n" : '';
          $out .= ($location) ? '<p><strong>'.$location.'</strong></p>'."\n" : '';
          $out .= '</div>'."\n";
          $out .= ($teaser) ? $teaser."\n" : '';
          $out .= '<p><a href="'.rex_getUrl($articleId).'">Mehr Informationen »</a></p>';
        }


        $out .= '</div>'."\n";
        $itemCount++;
        if ($howMany && $itemCount >= $howMany ) break;
      }

    }
  }
}
echo $out;
?>
