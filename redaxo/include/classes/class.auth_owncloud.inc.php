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

        // In order to delegate the authentication to Owncloud, we
        // remove the USR_PSW from the where clause
        $this->rexLoginQuery = $this->login_query;
        $this->setLoginquery(preg_replace('|AND\s+`?psw`?`?\s+=\s+"USR_PSW"\s+|', '', $this->login_query));
    }

    /** Remember the real password. In principle we could also disable
        encryption, but just change as few things as possible in the
        parent.
     */
    /*public*/ function setLogin($usr_login, $usr_psw)
    {
        $this->clearTextPassWord = $usr_psw;
        parent::setLogin($usr_login, $usr_psw);
    }

    /**Recurse to Owncloud, but allow only users also registered with
     * Redaxo. One could perhaps auto-add users, but we don't.
     */
    /*public*/ function checkLogin()
    {
        global $REX;

        return parent::checkLogin();

        if ($this->authOwncloud() && parent::checkLogin()) {
            return true;
        } else if ($REX['AUTH_ALLOWREX']) {
            $this->login_status = 0;
            $this->setLoginquery($rexLoginQuery);
            return parent::checkLogin();
        }
    }

    /* Validate user via ownCloud
     */
    private function authOwncloud()
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

        $savedSession = session_name();
        $savedUseCookies = ini_get('session.use_cookies');
        $savedLifeTime = ini_get('gc_maxlifetime');
        $savedCookieParams = session_get_cookie_params();
        session_write_close();

        ini_set('session.use_cookies', 0);
        require_once($REX['OWNCLOUDPATH'].'/lib/base.php');
        //       	session_destroy(); Don't
        session_write_close();

        error_reporting($savedReporting);

		// Check if ownCloud is installed or in maintenance (update) mode
        if (!OC_Config::getValue('installed', false)) {
            return false;
        }

        return OC_USER::checkPassword($this->usr_login, $this->clearTextPassword);
    }
}

