<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo4.2
 * @version svn:$Id$
 */

class cafevdb
{
  const NAME = 'cafevdb';
  const MCRYPT_CIPHER = MCRYPT_RIJNDAEL_128;
  const MCRYPT_MODE = MCRYPT_MODE_ECB;

  public $url = '';
  public $user = '';
  public $password = '';

  function cafevdb()
  {
    global $REX;

    $settings = rex_path::addonData(self::NAME, 'settings.inc.php');
    if (file_exists($settings)) {
      include $settings;
    }
  }

  private function encryptionKey()
  {
    global $REX;

    $key = $REX['INSTNAME'];

    $keySize  = mcrypt_module_get_algo_key_size(self::MCRYPT_CIPHER);
    $keySizes = mcrypt_module_get_supported_key_sizes(self::MCRYPT_CIPHER);
    if (count($keySizes) == 0) {
      $keySizes = array($keySize);
    }
    sort($keySizes);
    $maxSize = $keySizes[count($keySizes) - 1];
    $klen = strlen($key);
    if ($klen > $maxSize) {
      $key = substr($key, 0, $maxSize);
    } else {
      foreach($keySizes as $size) {
        if ($size >= $klen) {
          $key = str_pad($key, $size, "\0");
          break;
        }
      }
    }

    return $key;
  }

  public function encrypt($value)
  {
    $enckey = $this->encryptionKey();
    $value = base64_encode(mcrypt_encrypt(self::MCRYPT_CIPHER,
                                          $enckey,
                                          trim($value),
                                          self::MCRYPT_MODE));
    return $value;
  }

  private function decrypt($value)
  {
    $enckey = $this->encryptionKey();
    $value = trim(mcrypt_decrypt(self::MCRYPT_CIPHER,
                                 $enckey,
                                 base64_decode($value),
                                 self::MCRYPT_MODE));

    return $value;
  }

  private function cafevdbURI()
  {
    $uri = preg_replace('@(https?)://@', '${1}://'.$this->user.':'.$this->password.'@', $this->url);
    return $uri;
  }

  /**Quick and dirty event fetcher. Events are displayed in unordered
   * lists. The OwnCloud cafevdb app internally stores a table which
   * links Redaxo article ids to orchestra events.
   *
   * @param $articleId The article id.
   *
   * @param $doTitle Whether or not to display the short title of the project
   *
   * @return The html fragment wit the events associated to the given
   * article id.
   *
   * @bug This class probably should only export the event-data and
   * leave the display to a properly defined "module".
   */
  public function displayProjectEvents($articleId, $doTitle = false)
  {
    global $I18N;

    $nl = "\n";
    $out = '';

    $eventData = $this->fetchProjectEvents($articleId);
    if ($eventData === false) {
      $out .= '<h3>'.$I18N->msg('cafevdb_project_events_error').'</h3>';
    } else {
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

              if ($times['start']['date'] != $times['end']['date']) {
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

  /**Fetch just all concerts for the given article and parse it in to
   * an array
   *
   * array('date', 'title', 'subtitle', 'location');
   *
   * for use with the concert templates.
   */
  public function fetchProjectConcerts($articleId)
  {
    global $I18N;

    $data = array('date' => '',
                  'title' => '',
                  'subtitle' => '',
                  'teaser' => '',
                  'location' => array());

    $concertData = $this->fetchProjectEvents($articleId, 'concerts');
    if ($concertData === false) {
      $data['date'] = $I18N->msg('cafevdb_project_events_error');
      return $data;
    }

    // take just the first value, if we have more than one project
    // then something is fishy anyway.
    $events = reset($concertData);
    $project = key($concertData);
    // however, here it is clear that there is only a single instance by request
    $calendar = $events[0]['name'];
    $events = $events[0]['events'];
    if (count($events) == 1) {
      $event = $events[0];
      // strip the project tag, it is redundant and only bloats the
      // output
      $title = $event['summary'];
      $data['title'] = preg_replace('/(,\s*)?'.$project.'/', '', $title);
      $data['subtitle'] = $event['description'];
      $data['location'][] = $event['location'];

      $times = $event['times'];
      setlocale(LC_TIME, $times['locale']);
      if ($times['start']['allday']) {
        $date = strftime('%A, %x', $times['start']['stamp']);
        // Usually, we want to omit the end-time of a concert, but if
        // it really spans more than one day ...
        if ($times['start']['date'] != $times['end']['date']) {
          $date .= strftime(' - %A, %x', $times['end']['stamp']);
        }
      } else {
        $date = strftime('%A, %x, %H:%M', $times['start']['stamp']);
        // Usually, we want to omit the end-time of a concert
        // $date .= strftime(' - %H:%M', $times['end']['stamp']);
      }
      $data['date'] = $date;
    } else {
      // Somewhat tricky.
      //
      // * the individual event-dates will got to the location field
      //
      // * we use the first non-empty title and description
      //
      // * the date field will just carry the list of months
      $briefDates = array();
      $years = array();
      $months = array();
      foreach ($events as $event) {
        // strip the project tag, it is redundant and only bloats the
        // output
        $title = $event['summary'];
        $title = preg_replace('/(,\s*)?'.$project.'/', '', $title);
        $subtitle = $event['description'];
        $location = $event['location'];

        if ($data['title'] === '') {
          $data['title'] = $title;
        }
        if ($data['subtitle'] === '') {
          $data['subtitle'] = $subtitle;
        }

        $times = $event['times'];
        setlocale(LC_TIME, $times['locale']);
        if ($times['start']['allday']) {
          $date = strftime('%a, %x', $times['start']['stamp']);
          // Usually, we want to omit the end-time of a concert, but if
          // it really spans more than one day ...
          if ($times['start']['date'] != $times['end']['date']) {
            $date .= strftime(' - %a, %x', $times['end']['stamp']);
          }
        } else {
          $date = strftime('%a, %x, %H:%M', $times['start']['stamp']);
          // Usually, we want to omit the end-time of a concert
          // $date .= strftime(' - %H:%M', $times['end']['stamp']);
        }
        // As a brief header we construct a list of month and year
        // from the respective start date
        $year = strftime('%Y', $times['start']['stamp']);
        $month = strftime('%B', $times['start']['stamp']);
        $shortMonth = strftime('%b', $times['start']['stamp']);

        if (isset($briefDates[$year])) {
          $briefDates[$year]['long'][] = $month;
          $briefDates[$year]['short'][] = $shortMonth;
        } else {
          $briefDates[$year] = array(
            'long' => array($month),
            'short' => array($shortMonth)
            );
        }

        if ($location !== '') {
          $date .= ' - '.$location;
        }
        $data['location'][] = $date;
      }

      $longDate = array();
      $shortDate = array();
      foreach ($briefDates as $year => $months) {
        $longDate[] = implode(', ', array_unique($months['long'])).' '.$year;
        $shortDate[] = implode(', ', array_unique($months['short'])).' '.$year;
      }
      $longDate = implode('; ', $longDate);
      $shortDate = implode('; ', $shortDate);
      if (strlen($longDate) > 40) {
        $data['date'] = $shortDate;
      } else {
        $data['date'] = $longDate;
      }
    }

    return $data;
  }

  /**Quick and dirty event fetcher. Events are displayed in unordered
   * lists. The OwnCloud cafevdb app internally stores a table which
   * links Redaxo article ids to orchestra events.
   *
   * @param $articleId The article id.
   *
   * @return Array with all associated events or false
   *
   */
  public function fetchProjectEvents($articleId, $calendar = 'all')
  {
    $request  = $this->cafevdbURI();
    $request .= '/ocs/v1.php';
    $request .= "/apps/cafevdb/projects/events/byWebPageId";
    $request .= "/".$articleId;
    $request .= "/".$calendar;
    $request .= "/".urlencode(urlencode("Europe/Berlin")); // Needs to be doubly encoded. This is a Symphony bug
    $request .= "/de_DE.UTF-8";
    $request .= "?format=json";
    $eventData = file_get_contents($request);
    $eventData = json_decode($eventData, true);

    // Array ( [ocs] => Array ( [meta] => Array ( [status] => ok [statuscode] => 100 [message] => ) [data] => Array ( [RedaxoEnhancement2014] => Array ( [0] => Array ( [name] => Konzerte [events] => Array ( [0] => Array ( [times] => Array ( [start] => Array ( [date] => 27.10.2014 [time] => 03:13 [allday] => ) [end] => Array ( [date] => 27.10.2014 [time] => 06:13 [allday] => ) ) [summary] => Abschlusskonzert [location] => Engelboldstraße 97\, stuttgart [description] => Tutti ) ) ) [1] => Array ( [name] => Proben [events] => Array ( ) ) [2] => Array ( [name] => Sonstiges [events] => Array ( ) ) ) ) ) )

    if (is_array($eventData) && isset($eventData['ocs']) && $eventData['ocs']['meta']['statuscode'] == 100) {
      return $eventData['ocs']['data'];
    } else {
      return false;
    }
  }
}

/*
 * Local Variables: ***
 * c-basic-offset: 2 ***
 * End: ***
 */
?>
