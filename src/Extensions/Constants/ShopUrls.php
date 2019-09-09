<?php

namespace IO\Extensions\Constants;

use IO\Helper\MemoryCache;
use IO\Helper\RouteConfig;
use IO\Services\CategoryService;
use IO\Services\SessionStorageService;
use IO\Services\UrlBuilder\CategoryUrlBuilder;
use IO\Services\UrlBuilder\UrlQuery;
use IO\Services\WebstoreConfigurationService;
use Plenty\Modules\Frontend\Events\FrontendLanguageChanged;
use Plenty\Plugin\Events\Dispatcher;

class ShopUrls
{
    use MemoryCache;

    public $appendTrailingSlash = false;
    public $trailingSlashSuffix = "";
    public $includeLanguage     = false;

    public $basket              = "";
    public $cancellationForm    = "";
    public $cancellationRights  = "";
    public $checkout            = "";
    public $confirmation        = "";
    public $contact             = "";
    public $gtc                 = "";
    public $home                = "";
    public $legalDisclosure     = "";
    public $login               = "";
    public $myAccount           = "";
    public $passwordReset       = "";
    public $privacyPolicy       = "";
    public $registration        = "";
    public $search              = "";
    public $termsConditions     = "";
    public $wishList            = "";
    public $returnConfirmation  = "";

    public function __construct(Dispatcher $dispatcher, SessionStorageService $sessionStorageService)
    {
        $this->init($sessionStorageService->getLang());
        $dispatcher->listen(FrontendLanguageChanged::class, function(FrontendLanguageChanged $event)
        {
            $this->init($event->getLanguage());
        });
    }

    private function init($lang)
    {
        $this->resetMemoryCache();
        $this->appendTrailingSlash      = UrlQuery::shouldAppendTrailingSlash();
        $this->trailingSlashSuffix      = $this->appendTrailingSlash ? '/' : '';
        $this->includeLanguage          = $lang !== pluginApp(WebstoreConfigurationService::class)->getDefaultLanguage();

        $this->basket                   = $this->getShopUrl(RouteConfig::BASKET);
        $this->cancellationForm         = $this->getShopUrl(RouteConfig::CANCELLATION_FORM);
        $this->cancellationRights       = $this->getShopUrl(RouteConfig::CANCELLATION_RIGHTS);
        $this->checkout                 = $this->getShopUrl(RouteConfig::CHECKOUT);
        $this->confirmation             = $this->getShopUrl(RouteConfig::CONFIRMATION);
        $this->contact                  = $this->getShopUrl(RouteConfig::CONTACT);
        $this->gtc                      = $this->getShopUrl(RouteConfig::TERMS_CONDITIONS);

        // Homepage URL may not be used from category. Even if linked to category, the homepage url should be "/"
        $this->home                     = pluginApp(UrlQuery::class, ['path' => '/'])->toRelativeUrl($this->includeLanguage);
        $this->legalDisclosure          = $this->getShopUrl(RouteConfig::LEGAL_DISCLOSURE);
        $this->login                    = $this->getShopUrl(RouteConfig::LOGIN);
        $this->myAccount                = $this->getShopUrl(RouteConfig::MY_ACCOUNT);
        $this->passwordReset            = $this->getShopUrl(RouteConfig::PASSWORD_RESET);
        $this->privacyPolicy            = $this->getShopUrl(RouteConfig::PRIVACY_POLICY);
        $this->registration             = $this->getShopUrl(RouteConfig::REGISTER);
        $this->search                   = $this->getShopUrl(RouteConfig::SEARCH);
        $this->termsConditions          = $this->getShopUrl(RouteConfig::TERMS_CONDITIONS);
        $this->wishList                 = $this->getShopUrl(RouteConfig::WISH_LIST);
        $this->returnConfirmation       = $this->getShopUrl(RouteConfig::ORDER_RETURN_CONFIRMATION, "return-confirmation");
    }

    public function returns($orderId)
    {
        return $this->getShopUrl(RouteConfig::ORDER_RETURN, "returns", $orderId);
    }

    public function orderPropertyFile($path)
    {
        return $this->getShopUrl(RouteConfig::ORDER_PROPERTY_FILE, null, $path);
    }

    private function getShopUrl( $route, $url = null, ...$routeParams )
    {
        return $this->fromMemoryCache($route, function() use ($route, $url, $routeParams)
        {
            $categoryId = RouteConfig::getCategoryId( $route );
            if ( $categoryId > 0 )
            {
                /** @var CategoryService $categoryService */
                $categoryService = pluginApp(CategoryService::class);
                $category = $categoryService->get( $categoryId );

                if ( $category !== null )
                {
                    /** @var CategoryUrlBuilder $categoryUrlBuilder */
                    $categoryUrlBuilder = pluginApp( CategoryUrlBuilder::class );
                    return $this->applyParams(
                        $categoryUrlBuilder->buildUrl( $category->id ),
                        $routeParams
                    );
                }
            }

            return $this->applyParams(
                pluginApp(UrlQuery::class, ['path' => ($url ?? $route)] ),
                $routeParams
            );
        });
    }

    private function applyParams( $url, $routeParams )
    {
        $routeParam = array_shift($routeParams);
        while(!is_null($routeParam))
        {
            $url->join($routeParam);
            $routeParam = array_shift($routeParams);
        }

        return $url->toRelativeUrl($this->includeLanguage);
    }

    public function equals($routeUrl, $url)
    {
        return $routeUrl === $url || $routeUrl === $url . '/';
    }
}
