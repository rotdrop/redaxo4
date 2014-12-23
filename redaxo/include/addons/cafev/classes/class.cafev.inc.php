<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo4.2
 * @version svn:$Id$
 */

class cafev
{

  /**Quick and dirty event fetcher. Events are displayed in unordered lists. 
   *
   * @param $articleId What do you think ...
   *
   * @param $doTitle Whether or not to display the short title of the project
   */
  static public function displayProjectEvents($articleId, $doTitle = false)
  {
    $nl = "\n";
    $out = '';
    $request = "https://USER:PASS@HOST:PORT/OWNCLOUDPATH/ocs/v1.php";
    $request .= "/apps/cafevdb/projects/events/byWebPageId";
    $request .= "/".$articleId;
    $request .= "/".urlencode(urlencode("Europe/Berlin")); // Needs to be doubly encoded. This is a Symphony bug
    $request .= "/de_DE.UTF-8";
    $request .= "?format=json";    
    $eventData = file_get_contents($request);
    $eventData = json_decode($eventData, true);
    // Array ( [ocs] => Array ( [meta] => Array ( [status] => ok [statuscode] => 100 [message] => ) [data] => Array ( [RedaxoEnhancement2014] => Array ( [0] => Array ( [name] => Konzerte [events] => Array ( [0] => Array ( [times] => Array ( [start] => Array ( [date] => 27.10.2014 [time] => 03:13 [allday] => ) [end] => Array ( [date] => 27.10.2014 [time] => 06:13 [allday] => ) ) [summary] => Abschlusskonzert [location] => Engelboldstraße 97\, stuttgart [description] => Tutti ) ) ) [1] => Array ( [name] => Proben [events] => Array ( ) ) [2] => Array ( [name] => Sonstiges [events] => Array ( ) ) ) ) ) ) 
    if (is_array($eventData) && isset($eventData['ocs']) && $eventData['ocs']['meta']['statuscode'] == 100) {
      $eventData = $eventData['ocs']['data'];
      $titleDone = false;
      foreach($eventData as $project => $events) {
        if ($doTitle) {
          $titleDone = true;
          $out .= '<div class="marginalie">'.$nl;
          $out .= '<h1>';
          $out .= $project;
          $out .= '</h1>'.$nl;
          $out .= '</div>'.$nl;
        }
        $out .= '<h3>'.$project.'</h3>';
        foreach($events as $calendar) {
          if (count($calendar['events']) == 0) {
            continue;
          }
          $out .= '<h4>'.$calendar['name'].'</h4>'.$nl;
          $out .= '<ul class="cafevdb events">'.$nl;
          foreach($calendar['events'] as $event) {
            $out .= '<li>'.$nl;
            //$out .= '<p>'.$nl;
            $times = $event['times'];
            date_default_timezone_set($times['timezone']);
            $out .= '<strong>';
            setlocale(LC_TIME, $times['locale']);
            if ($times['start']['allday']) {
              $out .= strftime('%a, %x', $times['start']['stamp']);
              if ($times['start']['stamp'] != $times['end']['stamp']) {
                $out .= strftime(' - %a, %x', $times['end']['stamp']);
              }
            } else {
              $out .= strftime('%a, %x, %H:%M', $times['start']['stamp']);
              $out .= strftime(' - %H:%M', $times['end']['stamp']);
            }
            $out .= '</strong>';
            if ($event['summary'] != '') {
              // strip the project tag, it is redundant and only
              // bloats the output
              $summary = $event['summary'];
              $summary = preg_replace('/(,\s*)?'.$project.'/', '', $summary);              
              $out .= ': '.$summary;
            }
            if ($event['description'] != '') {
              $out .= '<br/>'.$event['description'];
            }
            if ($event['location'] != '') {
              $out .= '<br/>'.'Ort: '.$event['location'];
            }
            $out .= '</li>'.$nl;
          }
          $out .= '</ul>';
        }
      }
    }
    return $out;
  }
}

/*
 * Local Variables: ***
 * c-basic-offset: 2 ***
 * End: ***
 */
?>
