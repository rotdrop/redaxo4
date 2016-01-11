<?php
/* Trennlinie falls zwei oder mehr Probenpläne untereinander  */
if (isset($itemCount) && $itemCount) {
  $out = '<div class="item-line">&nbsp;</div>';
} else {
  $out = '';
}

/**
 * Ausgabe Probenpläne
 *
 */
$whichId = ("REX_VALUE[1]" != '' && intval("REX_VALUE[1]")) ? "REX_VALUE[1]" : $this->getValue('category_id');
$howMany = ("REX_VALUE[2]" != '' && intval("REX_VALUE[2]")) ? "REX_VALUE[2]" : false; // false -> alle anzeigen
$cat     = OOCategory::getCategoryById($whichId);
$article = $cat->getArticles();

$itemCount  = 0;
if (is_array($article) && count($article)) {
  foreach ($article as $key => $var) {
    if (!$var->isOnline()) continue;
    $articleId              = $var->getId();
    $articleName            = $var->getName();
    $articleDescription = $var->getDescription();
    if (!$var->isStartpage()) {
      $slice = OOArticleSlice::getFirstSliceForArticle($articleId,false);
      if ($slice) {
        $name   = $slice->getValue(1);
        $teaser = $slice->getValue(2);
        $image  = $slice->getMedia(1);
        $full   = $slice->getValue(3);
        $auto   = $slice->getValue(4);

        // rex-links zu htmllinks
        $teaser = str_replace("###","&#x20;",$teaser);
        $teaser = rex_article::replaceLinks($teaser);

        if ($itemCount) { // wenn itemCount > 0 mit Linie
          $out .= '<div class="item-text no-margin clearfix">'."\n";
          $out .= '<p class="ornament"> </p>'."\n";
        } else {
          $out .= '<div class="item-text clearfix">'."\n";
        }

        if ($name) {
          $out .= '<div class="marginalie">'."\n";
          $out .= '<h1>'.$name.'</h1>'."\n";
          $out .= '</div>'."\n";
        }
        if ($auto) {
          $cafevdb = new cafevdb();
          $out .= $cafevdb->displayProjectEvents($articleId, $name == '');
        }
        if ($teaser) {
          $out .= $teaser."\n";
        }
        if ($full) {
          $out .= '<p><a href="'.rex_getUrl($articleId).'">Mehr über <em>'.$name.'</em> »</a></p>';
        }

        $out .= '</div>'."\n";
        $itemCount++;
        if ($howMany && $itemCount >= $howMany ) break;
      }
    }
  }
}
echo $out;

/*
 * LocalVariables: ***
 * c-basic-offset: 2 ***
 * End: ***
 */

?>
