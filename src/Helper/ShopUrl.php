<?php

namespace IO\Helper;

use IO\Services\WebstoreConfigurationService;

class ShopUrl
{
    private $domain;
    private $path;

    public function __construct( WebstoreConfigurationService $webstoreConfigurationService, string $path = null )
    {
        $this->domain = $webstoreConfigurationService->getWebstoreConfig()->domainSsl;
        $this->path = $path;
        if ( substr( $this->path, 0, 1 ) !== "/" )
        {
            $this->path = "/" . $this->path;
        }

        if ( substr( $this->path, strlen($this->path-1), 1 ) === "/" )
        {
            $this->path = substr( $this->path, 0, strlen( $this->path - 1 ) );
        }
    }

    public function toAbsoluteUrl()
    {
        if ( $this->path === null )
        {
            return null;
        }

        return $this->domain . $this->path;
    }

    public function toRelativeUrl()
    {
        if ( $this->path === null )
        {
            return null;
        }

        return $this->path;
    }

    public function append( $path )
    {
        if ( substr( $path, 0, 1 ) !== "/" )
        {
            $path = "/" . $path;
        }

        $this->path = $this->path . $path;

        return $this;
    }

    public function __toString()
    {
        return $this->toAbsoluteUrl();
    }
}