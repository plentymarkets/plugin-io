<?php //strict

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\System\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Modules\Webshop\Template\Contracts\TemplateConfigRepositoryContract;
use Plenty\Plugin\Application;

/**
 * Class WebstoreConfigurationService
 * @package IO\Services
 */
class WebstoreConfigurationService
{
    /**
     * @var WebstoreConfiguration
     */
    private $webstoreConfig;


    /**
     * Get the plenty-id
     * @deprecated since 4.3.0
     * Use IO\Helper\Utils::getPlentyId() instead
     */
    public function getPlentyId()
    {
        return Utils::getPlentyId();
    }

    /**
     * Get the webstore configuraion
     */
	public function getWebstoreConfig():WebstoreConfiguration
    {
        if( $this->webstoreConfig === null )
        {
            /** @var WebstoreConfigurationRepositoryContract $webstoreConfig */
            $webstoreConfig = pluginApp(WebstoreConfigurationRepositoryContract::class);

            /** @var Application $app */
            $app = pluginApp(Application::class);

            $this->webstoreConfig = $webstoreConfig->findByWebstoreId($app->getWebstoreId());
        }

        return $this->webstoreConfig;
    }

	/**
	 * Get the activate languages of the webstore
	 */
    public function getActiveLanguageList()
	{
        $activeLanguages = [];

        /** @var TemplateConfigRepositoryContract $templateConfigRepo */
        $templateConfigRepo = pluginApp(TemplateConfigRepositoryContract::class);
        $languages = $templateConfigRepo->get('language.active_languages');

        if(!is_null($languages) && strlen($languages))
        {
            $activeLanguages = explode(', ', $languages);
        }

        if(!in_array($this->getWebstoreConfig()->defaultLanguage, $activeLanguages))
        {
            $activeLanguages[] = $this->getWebstoreConfig()->defaultLanguage;
        }

		return $activeLanguages;
	}

	/**
	 * Get the default language of the webstore
	 */
    public function getDefaultLanguage()
    {
        return $this->getWebstoreConfig()->defaultLanguage;
    }

    /**
	 * Get the default parcel-service-Id of the webstore
	 */
    public function getDefaultParcelServiceId()
    {
        return $this->getWebstoreConfig()->defaultParcelServiceId;
    }

    /**
     * Get the default parcel-service-preset-Id of the webstore
     */
    public function getDefaultParcelServicePresetId()
    {
        return $this->getWebstoreConfig()->defaultParcelServicePresetId;
    }

    /**
     * Get the default shipping-country-Id of the webstore
     */
    public function getDefaultShippingCountryId()
    {
        //TODO VDI MEYER
        $sessionService = pluginApp(SessionStorageService::class);
        $defaultShippingCountryId = $this->getWebstoreConfig()->defaultShippingCountryList[$sessionService->getLang()];

        if($defaultShippingCountryId <= 0)
        {
            $defaultShippingCountryId = $this->getWebstoreConfig()->defaultShippingCountryId;
        }

        return $defaultShippingCountryId;
    }
}
