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
        } else if ($REX['AUTH_ALLOWREX']) {
            return parent::checkLogin();
        } else {
            return false;
        }
    }

    /* Validate user via ownCloud
     */
    private function doAuthOwncloud()
    {
        global $REX;

        // one could argue about error_reportint() .... ;) However, we
        // simply save and restore the settings active in
        // owncloud. Otherwise the owncloud.log will be bloated with
        // all kind of DW warnings
        register_shutdown_function(create_function('', 'error_reporting(0);'));
        $savedReporting = error_reporting();

        // Yet another difficulty: just include base.php from Owncloud
        // has all sorts of side effects; sets sessions cookies and so
        // on. In the standard configuration we only have to disable
        // the session cookies, otherwise it will destroy the
        // browser's cookie storage. Still base.php _WILL_ change a
        // lot of things. We also save and restore the session life-time

        $savedSession    = session_name();
        $savedSessionId  = session_id();
        $savedUseCookies = ini_get('session.use_cookies');
        $savedLifeTime   = ini_get('gc_maxlifetime');
        $savedCookieParams = session_get_cookie_params();
        session_write_close();

        ini_set('session.use_cookies', 0);
        define('REDAXO_INCLUDE', true);

        session_id($savedSessionId."DUMMY");
        require_once($REX['OWNCLOUDPATH'].'/lib/base.php');
        restore_error_handler();

        if (!session_id()) {
            session_start();
        }
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"],
                      $params["domain"], $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        ini_set('session.use_cookies', $savedUseCookies);
        ini_set('gc_maxlifeTime', $savedLifeTime);
        session_set_cookie_params($savedCookieParams['lifetime'],
                                  $savedCookieParams['path'],
                                  $savedCookieParams['domain'],
                                  $savedCookieParams['secure'],
                                  $savedCookieParams['httponly']);
        session_name($savedSession);
        session_id($savedSessionId);
        session_start();

        error_reporting($savedReporting);

		// Check if ownCloud is installed or in maintenance (update) mode
        if (!OC_Config::getValue('installed', false)) {
            return false;
        }

        return (OC_USER::checkPassword($this->usr_login, $this->clearTextPassword) !== false);
    }
}

