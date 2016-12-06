<?php // strict

namespace IO\Guards;

use IO\Helper\UserSession;

class AuthGuard extends AbstractGuard
{
    public function __construct()
    {
        
    }

    /**
     * @return bool
     */
    protected function assert()
    {
        return pluginApp(UserSession::class)->isContactLoggedIn();
    }
}