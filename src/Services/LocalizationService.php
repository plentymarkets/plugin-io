<?php //strict

namespace LayoutCore\Services;

use LayoutCore\Services\SessionStorageService;
use LayoutCore\Services\CountryService;
use LayoutCore\Services\WebstoreConfigurationService;
use LayoutCore\Services\CheckoutService;

class LocalizationService
{
    public function __construct()
    {
        
    }
    
    public function getLocalizationData()
    {
        $sessionStorage = pluginApp(SessionStorageService::class);
        $country        = pluginApp(CountryService::class);
        $webstoreConfig = pluginApp(WebstoreConfigurationService::class);
        $checkout       = pluginApp(CheckoutService::class);
        
        $lang = $sessionStorage->getLang();
        
        return [
            'activeShippingCountries'  => $country->getActiveCountriesList($lang),
            'activeShopLanguageList'   => $webstoreConfig->getActiveLanguageList(),
            'currentShippingCountryId' => $checkout->getShippingCountryId(),
            'shopLanguage'             => $lang
        ];
    }
}