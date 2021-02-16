<?php

namespace IO\Guards;

use IO\Helper\Utils;

/**
 * Class AuthGuard
 *
 * Extends from AbstractGuard and implements an own assert function.
 *
 * @package IO\Guards
 */
class AuthGuard extends AbstractGuard
{
    public function __construct()
    {
    }

    /**
     * Returned true if a contact is logged in.
     *
     * @return bool
     */
    protected function assert()
    {
        return Utils::isContactLoggedIn();
    }
}
