<?php

namespace IO\Services\UrlBuilder;

use IO\Services\SessionStorageService;
use IO\Services\WebstoreConfigurationService;

class UrlQuery
{
    private $domain;
    private $path;
    private $lang;

    public function __construct( $path = null, $lang = null )
    {
        $this->domain = pluginApp(WebstoreConfigurationService::class )->getWebstoreConfig()->domainSsl;
        $this->path = $path;

        if ( $path !== null )
        {
            if (substr($this->path, 0, 1) !== "/") {
                $this->path = "/" . $this->path;
            }

            if (substr($this->path, strlen($this->path) - 1, 1) === "/") {
                $this->path = substr($this->path, 0, strlen($this->path) - 1);
            }
        }


        if ( $lang === null )
        {
            $this->lang = pluginApp( SessionStorageService::class )->getLang();
        }
        else
        {
            $this->lang = $lang;
        }
    }

    public function append( $suffix ): UrlQuery
    {
        $this->path = $this->path . $suffix;

        return $this;
    }

    public function join( $path ): UrlQuery
    {
        if ( substr( $path, 0, 1 ) !== "/" )
        {
            $path = "/" . $path;
        }

        if ( substr( $path, strlen($path)-1, 1 ) === "/" )
        {
            $path = substr( $path, 0, strlen( $path ) - 1 );
        }

        return $this->append( $path );
    }

    public function toAbsoluteUrl( bool $includeLanguage = false )
    {
        if ( $this->path === null )
        {
            return null;
        }

        return $this->domain . $this->toRelativeUrl( $includeLanguage );
    }

    public function toRelativeUrl( bool $includeLanguage = false )
    {
        if ( $this->path === null )
        {
            return null;
        }

        if ( $includeLanguage )
        {
            return '/' . $this->lang . $this->path;
        }

        return $this->path;
    }

    public function getPath( bool $includeLanguage = false )
    {
        if ( $this->path === null )
        {
            return null;
        }

        return substr( $this->toRelativeUrl( $includeLanguage ), 1 );
    }
}