<?php
/**
 * Darstellung des ersten Konzerts auf
 * http://camerata/index.php?article_id=16
 */

$max	 = "REX_VALUE[1]";
$cat 	 = OOCategory::getCategoryById(16);
$article = $cat->getArticles();
$out 	 = '';

// Startseite Article Anzahl abholen (Textblock ID=1)/ clang false
$startSlices = OOArticleSlice::getSlicesForArticleOfType($REX['START_ARTICLE_ID'],1);

if (is_array($article) && count($article)) {
  $itemCount = 0;
  foreach ($article as $key => $var) {
    if (!$var->isOnline()) continue;
    $articleId   		= $var->getId();
    $articleName 		= $var->getName();
    $articleDescription = $var->getDescription();
    if (!$var->isStartpage()) {
      if (!$var->isOnline()) continue;
      $slice = OOArticleSlice::getFirstSliceForArticle($articleId,false);
      //var_dump($slice);
      if ($slice) {
        $itemCount++;
        $date     = $slice->getValue(1);
        $title    = nl2br($slice->getValue(2));
        $subtitle = nl2br($slice->getValue(3));
        $peoples  = nl2br($slice->getValue(7));
        $location = nl2br($slice->getValue(4));
        $teaser	  = $slice->getValue(5);

        $ocEventList = $slice->getValue(8) === 'on';
        if ($ocEventList) {
          $cafevdb = new Cafev();
          $concertData = $cafevdb->fetchProjectConcerts($articleId);

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
        //$text	  = $slice->getValue(6);
        $out .= '<div class="item-text item-teaser clearfix">'."\n";
        $out .= '<div class="marginalie">'."\n";
        $out .= ($date) ? '<h3>'.$date.'</h3>'."\n" : '';
        $out .= ($title) ? '<h1>'.$title.'</h1>'."\n" : '';
        $out .= ($subtitle) ? '<h2>'.$subtitle.'</h2>'."\n" : '';
        $out .= ($peoples) ? '<p><em>'.$peoples.'</em></p>'."\n" : '';
        $out .= ($location) ? '<p><strong>'.$location.'</strong></p>'."\n" : '';
        $out .= '</div>'."\n";
        $out .= ($teaser) ? $teaser."\n" : '';
        $out .= '<p><a href="'.rex_getUrl($articleId).'">Mehr Informationen »</a></p>';

        // ornament ausgeben wenn letzter KonzertTeaser + folgender Textblock
        if ($itemCount >= $max && count($startSlices)) {
          $out .= '<p class="ornament"> </p>'."\n";
        }
        $out .= '</div>'."\n";

        if ($itemCount >= $max) break;
      }

      //echo '<a href="'.rex_getUrl($articleId).'" class="faq">'.$articleName.'</a><br />';
    }
  }
}
echo $out;
?>
