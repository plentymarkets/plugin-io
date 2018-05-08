<?php

namespace IO\Extensions\Constants;

use IO\Services\UrlBuilder\UrlQuery;

class ShopUrls
{
    public $appendTrailingSlash = false;
    public $trailingSlashSuffix = "";
    public $basket              = "";
    public $cancellationForm    = "";
    public $cancellationRights  = "";
    public $checkout            = "";
    public $contact             = "";
    public $legalDisclosure     = "";
    public $gtc                 = "";
    public $myAccount           = "";
    public $privacyPolicy       = "";
    public $registration        = "";
    public $wishList            = "";

    public function __construct()
    {
        $this->appendTrailingSlash      = UrlQuery::shouldAppendTrailingSlash();
        $this->trailingSlashSuffix      = $this->appendTrailingSlash ? '/' : '';

        $this->basket                   = pluginApp(UrlQuery::class, ['path' => '/basket'] )->toRelativeUrl();
        $this->cancellationForm         = pluginApp(UrlQuery::class, ['path' => '/cancellation-form'] )->toRelativeUrl();
        $this->cancellationRights       = pluginApp(UrlQuery::class, ['path' => '/cancellation-rights'] )->toRelativeUrl();
        $this->contact                  = pluginApp(UrlQuery::class, ['path' => '/contact'] )->toRelativeUrl();
        $this->checkout                 = pluginApp(UrlQuery::class, ['path' => '/checkout'] )->toRelativeUrl();
        $this->legalDisclosure          = pluginApp(UrlQuery::class, ['path' => '/legal-disclosure'] )->toRelativeUrl();
        $this->gtc                      = pluginApp(UrlQuery::class, ['path' => '/gtc'] )->toRelativeUrl();
        $this->myAccount                = pluginApp(UrlQuery::class, ['path' => '/my-account'] )->toRelativeUrl();
        $this->privacyPolicy            = pluginApp(UrlQuery::class, ['path' => '/privacy-policy'] )->toRelativeUrl();
        $this->registration             = pluginApp(UrlQuery::class, ['path' => '/register'] )->toRelativeUrl();
        $this->wishList                 = pluginApp(UrlQuery::class, ['path' => '/wish-list'] )->toRelativeUrl();
    }
}