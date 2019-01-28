<?php

namespace IO\Extensions\Constants;

use IO\Helper\MemoryCache;
use IO\Helper\RouteConfig;
use IO\Services\CategoryService;
use IO\Services\SessionStorageService;
use IO\Services\UrlBuilder\CategoryUrlBuilder;
use IO\Services\UrlBuilder\UrlQuery;
use IO\Services\UrlService;
use IO\Services\WebstoreConfigurationService;

class ShopUrls
{
    use MemoryCache;

    public $appendTrailingSlash = false;
    public $trailingSlashSuffix = "";
    public $includeLanguage     = false;
    public $home                = "";
    public $basket              = "";
    public $cancellationForm    = "";
    public $cancellationRights  = "";
    public $contact             = "";
    public $legalDisclosure     = "";
    public $login               = "";
    public $gtc                 = "";
    public $myAccount           = "";
    public $privacyPolicy       = "";
    public $registration        = "";
    public $wishList            = "";

    public function __construct()
    {
        $this->appendTrailingSlash      = UrlQuery::shouldAppendTrailingSlash();
        $this->trailingSlashSuffix      = $this->appendTrailingSlash ? '/' : '';
        $this->includeLanguage = pluginApp(SessionStorageService::class)->getLang() !== pluginApp(WebstoreConfigurationService::class)->getDefaultLanguage();

        $this->home                     = pluginApp(UrlQuery::class, ['path' => '/'])->toRelativeUrl($this->includeLanguage);
        $this->basket                   = pluginApp(UrlQuery::class, ['path' => '/basket'] )->toRelativeUrl($this->includeLanguage);
        $this->cancellationForm         = pluginApp(UrlQuery::class, ['path' => '/cancellation-form'] )->toRelativeUrl($this->includeLanguage);
        $this->cancellationRights       = pluginApp(UrlQuery::class, ['path' => '/cancellation-rights'] )->toRelativeUrl($this->includeLanguage);
        $this->contact                  = pluginApp(UrlQuery::class, ['path' => '/contact'] )->toRelativeUrl($this->includeLanguage);
        $this->legalDisclosure          = pluginApp(UrlQuery::class, ['path' => '/legal-disclosure'] )->toRelativeUrl($this->includeLanguage);
        $this->login                    = pluginApp(UrlQuery::class, ['path' => '/login'] )->toRelativeUrl($this->includeLanguage);
        $this->gtc                      = pluginApp(UrlQuery::class, ['path' => '/gtc'] )->toRelativeUrl($this->includeLanguage);
        $this->myAccount                = pluginApp(UrlQuery::class, ['path' => '/my-account'] )->toRelativeUrl($this->includeLanguage);
        $this->privacyPolicy            = pluginApp(UrlQuery::class, ['path' => '/privacy-policy'] )->toRelativeUrl($this->includeLanguage);
        $this->registration             = pluginApp(UrlQuery::class, ['path' => '/register'] )->toRelativeUrl($this->includeLanguage);
        $this->wishList                 = pluginApp(UrlQuery::class, ['path' => '/wish-list'] )->toRelativeUrl($this->includeLanguage);

    }

    public function getCheckout()
    {
        return $this->fromMemoryCache("checkout", function()
        {
            $checkoutCategoryId = RouteConfig::getCategoryId( RouteConfig::CHECKOUT );
            if ( $checkoutCategoryId > 0 )
            {
                /** @var CategoryService $categoryService */
                $categoryService = pluginApp(CategoryService::class);
                $category = $categoryService->get( $checkoutCategoryId );

                if ( $category !== null )
                {

                    if($category->details !== null && strlen($category->details[0]->canonicalLink) > 0)
                    {
                        return $category->details[0]->canonicalLink;
                    }

                    /** @var CategoryUrlBuilder $categoryUrlBuilder */
                    $categoryUrlBuilder = pluginApp( CategoryUrlBuilder::class );
                    return $categoryUrlBuilder->buildUrl( $category->id )->toRelativeUrl($this->includeLanguage);
                }
            }

            return pluginApp(UrlQuery::class, ['path' => '/checkout'] )->toRelativeUrl($this->includeLanguage);
        });
    }
}