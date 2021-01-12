<?php //strict

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\Frontend\Services\LocaleService;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\Data\Contracts\Resources;

/**
 * Service Class LocalizationService
 *
 * This service class contains functions related to localization functionality.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class LocalizationService
{
    /**
     * Get localization data for the frontend. This data contains active shipping countries, active languages, the current shipping country and the shop language.
     * @return array
     */
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

    /**
     * Set a new language
     * @param string $newLanguage The new language (ISO-639-1)
     * @param bool $fireEvent Optional: Fire a LanguageChanged event (Default: false)
     */
    public function setLanguage($newLanguage, $fireEvent = true)
    {
        $localeService = pluginApp(LocaleService::class);
        $localeService->setLanguage($newLanguage, $fireEvent);
    }

    /**
     * Get translations for a plugin. This function is primarily used in the frontend to enable clientside translation.
     * @param string $plugin The plugin, whose translation is to be fetched. E.g "Ceres" in "Ceres::Widgets.exampleTranslation"
     * @param string $group The translation group, which is to be fetched. E.g "Widgets" in "Ceres::Widgets.exampleTranslation"
     * @param string|null $lang Optional: The language for the translations. By default uses the current active language.
     * @return mixed|null
     */
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

    /**
     * Check if a specific country has states
     * @param int $countryId An countries id
     * @return bool
     */
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
