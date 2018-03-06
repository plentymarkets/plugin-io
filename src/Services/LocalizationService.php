<?php //strict

namespace IO\Services;

use IO\Services\SessionStorageService;
use IO\Services\CountryService;
use IO\Services\WebstoreConfigurationService;
use IO\Services\CheckoutService;
use Plenty\Modules\Frontend\Services\LocaleService;
use Plenty\Plugin\Data\Contracts\Resources;

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
        if(is_null($lang) || !strlen($lang))
        {
            $lang = 'de';
        }

        $currentShippingCountryId = $checkout->getShippingCountryId();
        if($currentShippingCountryId <= 0)
        {
            $currentShippingCountryId = $webstoreConfig->getDefaultShippingCountryId();
        }

        return [
            'activeShippingCountries'  => $country->getActiveCountriesList($lang),
            'activeShopLanguageList'   => $webstoreConfig->getActiveLanguageList(),
            'currentShippingCountryId' => $currentShippingCountryId,
            'shopLanguage'             => $lang
        ];
    }

    public function setLanguage($newLanguage, $fireEvent = true)
    {
        $localeService = pluginApp(LocaleService::class);
        $localeService->setLanguage($newLanguage, $fireEvent);
    }

    public function getTranslations( string $plugin, string $group, $lang = null )
    {
        if ( $lang === null )
        {
            $lang = pluginApp(SessionStorageService::class)->getLang();
        }

        /** @var Resources $resource */
        $resource = pluginApp( Resources::class );

        try
        {
            return $resource->load( "$plugin::lang/$lang/$group" )->getData();
        }
        catch( \Exception $e )
        {
            // TODO: get fallback language from webstore configuration
            return $resource->load( "$plugin::lang/en/$group")->getData();
        }
    }
}