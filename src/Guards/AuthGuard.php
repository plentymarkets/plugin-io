<?php // strict

namespace LayoutCore\Guards;

use LayoutCore\Helper\UserSession;

class AuthGuard extends AbstractGuard
{
    /**
     * @var UserSession
     */
    private $session;

    public function __construct( UserSession $session )
    {
        $this->session = $session;
    }

    /**
     * @return bool
     */
    protected function assert()
    {
        return $this->session->isContactLoggedIn();
    }
}