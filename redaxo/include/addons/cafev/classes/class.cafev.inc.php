<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo4.2
 * @version svn:$Id$
 */

class cafev
{
  const NAME = 'cafev';
  const MCRYPT_CIPHER = MCRYPT_RIJNDAEL_128;
  const MCRYPT_MODE = MCRYPT_MODE_ECB;

  public $url = '';
  public $user = '';
  public $password = '';

  function cafev()
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

  private function cafevURI()
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
      $out .= '<h3>'.$I18N->msg('cafev_project_events_error').'</h3>';
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

  /**Quick and dirty event fetcher. Events are displayed in unordered
   * lists. The OwnCloud cafevdb app internally stores a table which
   * links Redaxo article ids to orchestra events.
   *
   * @param $articleId The article id.
   *
   * @return Array with all associated events or false
   *
   */
  public function fetchProjectEvents($articleId)
  {
    $request  = $this->cafevURI();
    $request .= '/ocs/v1.php';
    $request .= "/apps/cafevdb/projects/events/byWebPageId";
    $request .= "/".$articleId;
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
