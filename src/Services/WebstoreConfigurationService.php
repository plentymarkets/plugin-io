<?php //strict

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;

/**
 * Service Class WebstoreConfigurationService
 *
 * This service class contains functions for getting data from the webstore configuration.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 *
 * @deprecated since 5.0.0 will be removed in 6.0.0
 * @see \Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract
 */
class WebstoreConfigurationService
{
    /**
     * Get the plentyId
     * @deprecated since 4.3.0
     * @see \IO\Helper\Utils::getPlentyId() instead
     */
    public function getPlentyId()
    {
        return Utils::getPlentyId();
    }

    /**
     * Get the webstore configuraion
     *
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract::getWebstoreConfiguration()
     */
    public function getWebstoreConfig(): WebstoreConfiguration
    {
        /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
        return $webstoreConfigurationRepository->getWebstoreConfiguration();
    }

    /**
     * Get the activate languages of the webstore
     *
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract::getActiveLanguageList()
     */
    public function getActiveLanguageList()
    {
        $activeLanguages = [];

        $languages = Utils::getTemplateConfig('language.active_languages');

        if (!is_null($languages) && strlen($languages)) {
            $activeLanguages = explode(', ', $languages);
        }

        if (!in_array($this->getWebstoreConfig()->defaultLanguage, $activeLanguages)) {
            $activeLanguages[] = $this->getWebstoreConfig()->defaultLanguage;
        }

        return $activeLanguages;
    }

    /**
     * Get the default language of the webstore
     *
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract::getDefaultLanguage()
     */
    public function getDefaultLanguage()
    {
        return $this->getWebstoreConfig()->defaultLanguage;
    }

    /**
     * Get the default parcel-service-id of the webstore
     *
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract::getDefaultParcelServiceId()
     */
    public function getDefaultParcelServiceId()
    {
        return $this->getWebstoreConfig()->defaultParcelServiceId;
    }

    /**
     * Get the default parcel-service-preset-id of the webstore
     *
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract::getDefaultParcelServicePresetId()
     */
    public function getDefaultParcelServicePresetId()
    {
        return $this->getWebstoreConfig()->defaultParcelServicePresetId;
    }

    /**
     * Get the default shipping-country-id of the webstore
     *
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract::getDefaultShippingCountryId()
     */
    public function getDefaultShippingCountryId()
    {
        $defaultShippingCountryId = $this->getWebstoreConfig()->defaultShippingCountryList[Utils::getLang()];

        if ($defaultShippingCountryId <= 0) {
            $defaultShippingCountryId = $this->getWebstoreConfig()->defaultShippingCountryId;
        }

        return $defaultShippingCountryId;
    }
}
