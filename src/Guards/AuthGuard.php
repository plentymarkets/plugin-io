<?php // strict

namespace IO\Guards;

use IO\Helper\Utils;

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
        return Utils::isContactLoggedIn();
    }
}