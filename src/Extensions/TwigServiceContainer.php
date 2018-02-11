<?php

namespace IO\Extensions;

use IO\Services\AvailabilityService;
use IO\Services\BasketService;
use IO\Services\CategoryService;
use IO\Services\CheckoutService;
use IO\Services\ContactBankService;
use IO\Services\ContactMailService;
use IO\Services\CountryService;
use IO\Services\CouponService;
use IO\Services\CustomerService;
use IO\Services\ItemCrossSellingService;
use IO\Services\ItemLastSeenService;
use IO\Services\ItemLoader\Loaders\LastSeenItemList;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\ItemService;
use IO\Services\ItemWishListService;
use IO\Services\LegalInformationService;
use IO\Services\LocalizationService;
use IO\Services\NotificationService;
use IO\Services\OrderService;
use IO\Services\OrderTotalsService;
use IO\Services\SalesPriceService;
use IO\Services\SessionStorageService;
use IO\Services\TemplateService;
use IO\Services\UnitService;
use IO\Services\UrlService;
use IO\Services\WebstoreConfigurationService;

class TwigServiceContainer
{
    public function getAvailability()
    {
        return pluginApp( AvailabilityService::class );
    }

    public function getBasket()
    {
        return pluginApp( BasketService::class );
    }

    public function getCategory()
    {
        return pluginApp( CategoryService::class );
    }

    public function getCheckout()
    {
        return pluginApp( CheckoutService::class );
    }

    public function getCountry()
    {
        return pluginApp( CountryService::class );
    }

    public function getCustomer()
    {
        return pluginApp( CustomerService::class );
    }

    public function getItem()
    {
        return pluginApp( ItemService::class );
    }

    public function getItemLoader()
    {
        return pluginApp(ItemLoaderService::class );
    }

    public function getOrder()
    {
        return pluginApp( OrderService::class );
    }

    public function getSessionStorage()
    {
        return pluginApp( SessionStorageService::class );
    }

    public function getUnit()
    {
        return pluginApp( UnitService::class );
    }

    public function getContactBank()
    {
        return pluginApp( ContactBankService::class );
    }

    public function getTemplate()
    {
        return pluginApp( TemplateService::class );
    }

    public function getNotifications()
    {
        return pluginApp( NotificationService::class );
    }

    public function getWebstoreConfig()
    {
        return pluginApp( WebstoreConfigurationService::class );
    }

    public function getLocalization()
    {
        return pluginApp( LocalizationService::class );
    }

    public function getCoupon()
    {
        return pluginApp( CouponService::class );
    }

    public function getLegalInformation()
    {
        return pluginApp( LegalInformationService::class );
    }

    public function getSalesPrice()
    {
        return pluginApp( SalesPriceService::class );
    }

    public function getLastSeen()
    {
        return pluginApp( ItemLastSeenService::class );
    }

    public function getCrossSelling()
    {
        return pluginApp( ItemCrossSellingService::class );
    }

    public function getWishList()
    {
        return pluginApp( ItemWishListService::class );
    }

    public function getContactMail()
    {
        return pluginApp( ContactMailService::class );
    }

    public function getTotalsService()
    {
        return pluginApp( OrderTotalsService::class );
    }

    public function getUrl()
    {
        return pluginApp( UrlService::class );
    }
}