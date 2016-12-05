<?php // strict

namespace LayoutCore\Guards;

use LayoutCore\Helper\UserSession;

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