<?php

/**Use an OwnCloud instance on the same web-space for authentication
   in order to have SSO.
 */
class auth_owncloud extends rex_backend_login
{
    var $tableName;
    private $clearTextPassword;
    private $rexLoginQuery;

    /*public*/ function auth_owncloud($tableName)
    {
        global $REX;

        parent::rex_backend_login($tableName);
    }

    /** Remember the real password. In principle we could also disable
        encryption, but just change as few things as possible in the
        parent.
     */
    /*public*/ function setLogin($usr_login, $usr_psw)
    {
        $this->clearTextPassword = $usr_psw;
        parent::setLogin($usr_login, $usr_psw);
    }

    /**Recurse to Owncloud, but allow only users also registered with
     * Redaxo. One could perhaps auto-add users, but we don't.
     */
    /*public*/ function checkLogin()
    {
        global $REX;

        if (($this->cache && $this->login_status != 0) || $this->logout || $this->usr_login == '') {
            // directly to parent class
            return parent::checkLogin();
        }

        if ($this->doAuthOwncloud()) {
            // In order to delegate the authentication to Owncloud, we
            // remove the USR_PSW from the where clause
            $this->rexLoginQuery = $this->login_query;
            $this->setLoginquery(preg_replace('|AND\s+`?psw`?`?\s+=\s+"USR_PSW"\s+|', '', $this->login_query));
            return parent::checkLogin();
        } else if ($REX['AUTH_NEXTCLOUD_ALLOWREX']) {
            return parent::checkLogin();
        } else {
            return false;
        }
    }

    /* Validate user via ownCloud. We use the provisioning API
     * available since OC 8. The user counts as authenticated if the
     * API call succeeds and contains all relevant fields.
     */
    private function doAuthOwncloud()
    {
        global $REX;

        $url = $REX['AUTH_NEXTCLOUD_URL'].'/'.'ocs/v1.php/cloud/users/'.$this->usr_login;
        $url .= "?format=json";

        if (function_exists('curl_version')) {

            //error_log(__METHOD__.': use curl');

            $c = curl_init();
            curl_setopt($c, CURLOPT_VERBOSE, 0);
            curl_setopt($c, CURLOPT_URL, $url);
            curl_setopt($c, CURLOPT_HTTPHEADER, [ 'OCS-APIRequest: true' ]);
            curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($c, CURLOPT_USERPWD, $this->usr_login.':'.$this->clearTextPassword);
            curl_setopt($c, CURLOPT_HTTPHEADER, [ "OCS-APIRequest:true" ]);
            if ($REX['AUTH_NEXTCLOUD_VERIFY_SSL'] === false) {
              curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
              curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
            }
            if (false) {
                curl_setopt($c, CURLOPT_HEADERFUNCTION, function($curl, $headerline) {
                        error_log($headerline);
                        return strlen($headerline);
                    });
            }
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($c);
            curl_close($c);
        } else {

            //error_log(__METHOD__.': do not use curl');

            $auth = 'Authorization: Basic '.base64_encode($this->usr_login.':'.$this->clearTextPassword);
            $context = stream_context_create(
                array(
                    'http' => array(
                        'method' => 'GET',
                        'header' => $auth
                        )
                    )
                );
            $fp = fopen($url, 'rb', false, $context);
            if ($fp === false) {
                return false;
            }
            $result = stream_get_contents($fp);
            fclose($fp);
        }
        $result = json_decode($result, true);
        if (!is_array($result) ||
            !isset($result['ocs']) ||
            !isset($result['ocs']['data']) ||
            !isset($result['ocs']['meta']) ||
            !isset($result['ocs']['meta']['statuscode']) |
            $result['ocs']['meta']['statuscode'] != 100) {
            return false;
        }
        if (false) {
          $data = $result['ocs']['data'];
          if (!$data['enabled']) {
            return false;
          }
        }
        return true;
    }
}
