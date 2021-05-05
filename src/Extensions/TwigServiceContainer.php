<?php

namespace IO\Extensions;

use IO\Services\AuthenticationService;
use IO\Services\AvailabilityService;
use IO\Services\BasketService;
use IO\Services\CategoryService;
use IO\Services\CheckoutService;
use IO\Services\ContactBankService;
use IO\Services\ContactMailService;
use IO\Services\ContactMapService;
use IO\Services\CountryService;
use IO\Services\CouponService;
use IO\Services\CustomerService;
use IO\Services\FacetService;
use IO\Services\FakerService;
use IO\Services\ItemCrossSellingService;
use IO\Services\ItemLastSeenService;
use IO\Services\ItemListService;
use IO\Services\ItemSearchAutocompleteService;
use IO\Services\ItemService;
use IO\Services\ItemWishListService;
use IO\Services\LegalInformationService;
use IO\Services\LiveShoppingService;
use IO\Services\LocalizationService;
use IO\Services\NotificationService;
use IO\Services\OrderService;
use IO\Services\OrderTotalsService;
use IO\Services\PropertyFileService;
use IO\Services\SalesPriceService;
use IO\Services\SeoService;
use IO\Services\SessionStorageService;
use IO\Services\ShippingService;
use IO\Services\TagService;
use IO\Services\TemplateService;
use IO\Services\UnitService;
use IO\Services\UrlService;
use IO\Services\UserDataHashService;
use IO\Services\WebstoreConfigurationService;
use Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;

class TwigServiceContainer
{
    public function getAvailability(): AvailabilityService
    {
        return pluginApp(AvailabilityService::class);
    }

    public function getBasket(): BasketService
    {
        return pluginApp(BasketService::class);
    }

    public function getCategory(): CategoryService
    {
        return pluginApp(CategoryService::class);
    }

    public function getCheckout(): CheckoutService
    {
        return pluginApp(CheckoutService::class);
    }

    public function getCountry(): CountryService
    {
        return pluginApp(CountryService::class);
    }

    public function getCustomer(): CustomerService
    {
        return pluginApp(CustomerService::class);
    }

    public function getItem(): ItemService
    {
        return pluginApp(ItemService::class);
    }

    public function getItemList(): ItemListService
    {
        return pluginApp(ItemListService::class);
    }

    public function getOrder(): OrderService
    {
        return pluginApp(OrderService::class);
    }

    public function getSessionStorageRepository(): SessionStorageRepositoryContract
    {
        return pluginApp(SessionStorageRepositoryContract::class);
    }

    public function getSessionStorage(): SessionStorageService
    {
        return pluginApp(SessionStorageService::class);
    }

    public function getUnit(): UnitService
    {
        return pluginApp(UnitService::class);
    }

    public function getContactBank(): ContactBankService
    {
        return pluginApp(ContactBankService::class);
    }

    public function getTag(): TagService
    {
        return pluginApp(TagService::class);
    }

    public function getTemplate(): TemplateService
    {
        return pluginApp(TemplateService::class);
    }

    public function getNotifications(): NotificationService
    {
        return pluginApp(NotificationService::class);
    }

    public function getWebstoreConfigurationRepository(): WebstoreConfigurationRepositoryContract
    {
        return pluginApp(WebstoreConfigurationRepositoryContract::class);
    }

    public function getWebstoreConfig(): WebstoreConfigurationService
    {
        return pluginApp(WebstoreConfigurationService::class);
    }

    public function getLocalization(): LocalizationService
    {
        return pluginApp(LocalizationService::class);
    }

    public function getLocalizationRepository(): LocalizationRepositoryContract
    {
        return pluginApp(LocalizationRepositoryContract::class);
    }

    public function getCoupon(): CouponService
    {
        return pluginApp(CouponService::class);
    }

    public function getLegalInformation(): LegalInformationService
    {
        return pluginApp(LegalInformationService::class);
    }

    public function getSalesPrice(): SalesPriceService
    {
        return pluginApp(SalesPriceService::class);
    }

    public function getLastSeen(): ItemLastSeenService
    {
        return pluginApp(ItemLastSeenService::class);
    }

    public function getCrossSelling(): ItemCrossSellingService
    {
        return pluginApp(ItemCrossSellingService::class);
    }

    public function getWishList(): ItemWishListService
    {
        return pluginApp(ItemWishListService::class);
    }

    public function getContactMail(): ContactMailService
    {
        return pluginApp(ContactMailService::class);
    }

    public function getTotalsService(): OrderTotalsService
    {
        return pluginApp(OrderTotalsService::class);
    }

    public function getUrl(): UrlService
    {
        return pluginApp(UrlService::class);
    }

    public function getLiveShopping(): LiveShoppingService
    {
        return pluginApp(LiveShoppingService::class);
    }

    public function getAuthentication(): AuthenticationService
    {
        return pluginApp(AuthenticationService::class);
    }

    public function getUserDataHash(): UserDataHashService
    {
        return pluginApp(UserDataHashService::class);
    }

    public function getFaker(): FakerService
    {
        return pluginApp(FakerService::class);
    }

    public function getPropertyFile(): PropertyFileService
    {
        return pluginApp(PropertyFileService::class);
    }

    public function getFacet(): FacetService
    {
        return pluginApp(FacetService::class);
    }

    public function getContactMap(): ContactMapService
    {
        return pluginApp(ContactMapService::class);
    }

    public function getSearchAutocomplete()
    {
        return pluginApp(ItemSearchAutocompleteService::class);
    }

    public function getSEO(): SeoService
    {
        return pluginApp(SeoService::class);
    }

    public function getShipping(): ShippingService
    {
        return pluginApp(ShippingService::class);
    }
}
