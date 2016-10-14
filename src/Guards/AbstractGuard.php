<?php // strict

namespace LayoutCore\Guards;

use LayoutCore\Helper\AbstractFactory;

abstract class AbstractGuard
{
    /**
     * @return mixed
     */
    protected abstract function assert();

    public function assertOrRedirect( $expected, string $redirectUrl )
    {
        if ( $this->assert() !== $expected )
        {
            self::redirect( $redirectUrl, ["backlink" => self::getUrl()] );
        }
    }

    public static function redirect( string $uri, array $params = [] )
    {
        $url = self::getUrl( $uri );

        $queryParams = [];
        foreach( $params as $key => $value )
        {
            $param = rawurlencode( $key ) . "=" . rawurlencode( $value );
            array_push( $queryParams, $param );
        }

        $query = "";
        if( count( $queryParams ) > 0 )
        {
            $query = "?" . implode("&", $queryParams);
        }

        header( 'Location: ' . $url . $query );
        exit;
    }

    private static function getUrl( string $uri = null )
    {
        if( $uri === null )
        {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';

        return $protocol . "://" . $_SERVER['SERVER_NAME'] . $uri;
    }



}