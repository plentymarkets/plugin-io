<?php //strict

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\Frontend\Services\LocaleService;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\Data\Contracts\Resources;

class LocalizationService
{
    public function __construct()
    {
    }

    public function getLocalizationData()
    {
        $country = pluginApp(CountryService::class);
        /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
        $checkout = pluginApp(CheckoutService::class);

        $lang = Utils::getLang();
        if (is_null($lang) || !strlen($lang)) {
            $lang = $webstoreConfigurationRepository->getWebstoreConfiguration()->defaultLanguage;
        }

        $currentShippingCountryId = $checkout->getShippingCountryId();
        if ($currentShippingCountryId <= 0) {
            $currentShippingCountryId = $webstoreConfigurationRepository->getDefaultShippingCountryId();
        }

        return [
            'activeShippingCountries' => $country->getActiveCountriesList($lang),
            'activeShopLanguageList' => $webstoreConfigurationRepository->getActiveLanguageList(),
            'currentShippingCountryId' => $currentShippingCountryId,
            'shopLanguage' => $lang
        ];
    }

    public function setLanguage($newLanguage, $fireEvent = true)
    {
        $localeService = pluginApp(LocaleService::class);
        $localeService->setLanguage($newLanguage, $fireEvent);
    }

    public function getTranslations(string $plugin, string $group, $lang = null)
    {
        if ($lang === null) {
            $lang = Utils::getLang();
        }

        /** @var Resources $resource */
        $resource = pluginApp(Resources::class);

        try {
            return $resource->load("$plugin::lang/$lang/$group")->getData();
        } catch (\Exception $e) {
            // TODO: get fallback language from webstore configuration
            return $resource->load("$plugin::lang/en/$group")->getData();
        }
    }

    public function hasCountryStates($countryId): bool
    {
        /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
        $country = pluginApp(CountryService::class);
        $lang = Utils::getLang();

        if (is_null($lang) || !strlen($lang)) {
            $lang = $webstoreConfigurationRepository->getWebstoreConfiguration()->defaultLanguage;
        }

        $activeCountries = $country->getActiveCountriesList($lang);
        $key = array_search($countryId, array_column($activeCountries, 'id'));

        return $activeCountries[$key]->states->isNotEmpty();
    }
}
