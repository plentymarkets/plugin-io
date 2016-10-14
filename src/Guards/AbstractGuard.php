<?php // strict

namespace LayoutCore\Guards;

use LayoutCore\Helper\AbstractFactory;

abstract class AbstractGuard
{
    /**
     * @return mixed
     */
    protected abstract function assert();

    public static function assertOrRedirect( $expected, string $redirectUrl )
    {
        $guard = AbstractFactory::create(get_called_class());
        if ( $guard->assert() !== $expected )
        {
            $session = AbstractFactory::create(\Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract::class);
            $session->getPlugin()->setValue( "BACKLINK_URL", self::getUrl() );

            self::redirect( $redirectUrl );
        }
    }

    public static function redirect( string $uri )
    {
        $url = self::getUrl( $uri );
        header( 'Location: ' . $url );
        exit;
    }

    public static function getBacklinkUrl()
    {
        $session = AbstractFactory::create(\Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract::class);
        $url = $session->getPlugin()->getValue( "BACKLINK_URL" );

        return $url;
    }

    private static function getUrl( string $uri = null )
    {
        if( $uri === null )
        {
            $_SERVER['REQUEST_URI'];
        }

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
        $separator = substr( $uri, -1 ) == '/' ? '' : '/';

        return $protocol . "://" . $_SERVER['SERVER_NAME'] . $separator . $uri;
    }



}