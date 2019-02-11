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

    public function __construct()
    {
        $this->appendTrailingSlash      = UrlQuery::shouldAppendTrailingSlash();
        $this->trailingSlashSuffix      = $this->appendTrailingSlash ? '/' : '';
        $this->includeLanguage = pluginApp(SessionStorageService::class)->getLang() !== pluginApp(WebstoreConfigurationService::class)->getDefaultLanguage();
    }

    public function getBasket()
    {
        return $this->getShopUrl(RouteConfig::BASKET);
    }

    public function getCancellationForm()
    {
        return $this->getShopUrl( RouteConfig::CANCELLATION_FORM );
    }

    public function getCancellationRights()
    {
        return $this->getShopUrl(RouteConfig::CANCELLATION_RIGHTS);
    }

    public function getCheckout()
    {
        return $this->getShopUrl(RouteConfig::CHECKOUT);
    }

    public function getConfirmation()
    {
        return $this->getShopUrl(RouteConfig::CONFIRMATION);
    }

    public function getContact()
    {
        return $this->getShopUrl(RouteConfig::CONTACT);
    }

    public function getGtc()
    {
        return $this->getShopUrl(RouteConfig::TERMS_CONDITIONS);
    }

    public function getHome()
    {
        return $this->getShopUrl(RouteConfig::HOME);
    }

    public function getLegalDisclosure()
    {
        return $this->getShopUrl(RouteConfig::LEGAL_DISCLOSURE);
    }

    public function getLogin()
    {
        return $this->getShopUrl(RouteConfig::LOGIN);
    }

    public function getMyAccount()
    {
        return $this->getShopUrl(RouteConfig::MY_ACCOUNT);
    }

    public function getPasswordReset()
    {
        return $this->getShopUrl(RouteConfig::PASSWORD_RESET);
    }

    public function getPrivacyPolicy()
    {
        return $this->getShopUrl(RouteConfig::PRIVACY_POLICY);
    }

    public function getRegistration()
    {
        return $this->getShopUrl(RouteConfig::REGISTER);
    }

    public function getSearch()
    {
        return $this->getShopUrl(RouteConfig::SEARCH);
    }

    public function getTermsConditions()
    {
        return $this->getShopUrl(RouteConfig::TERMS_CONDITIONS);
    }

    public function getWishList()
    {
        return $this->getShopUrl(RouteConfig::WISH_LIST);
    }

    private function getShopUrl( $route )
    {
        return $this->fromMemoryCache($route, function() use ($route)
        {
            $categoryId = RouteConfig::getCategoryId( $route );
            if ( $categoryId > 0 )
            {
                /** @var CategoryService $categoryService */
                $categoryService = pluginApp(CategoryService::class);
                $category = $categoryService->get( $categoryId );

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

            return pluginApp(UrlQuery::class, ['path' => $route] )->toRelativeUrl($this->includeLanguage);
        });
    }
}