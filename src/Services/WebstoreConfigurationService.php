<?php //strict

namespace IO\Services;

use Plenty\Modules\System\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\System\Models\WebstoreConfiguration;
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
     */
    public function getPlentyId()
    {
        return pluginApp(Application::class)->getPlentyId();
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

            $this->webstoreConfig = $webstoreConfig->findByPlentyId($app->getPlentyId());
        }

        return $this->webstoreConfig;
    }

	/**
	 * Get the activate languages of the webstore
	 */
    public function getActiveLanguageList()
	{
        $languageList = $this->getWebstoreConfig()->languageList;
        $languages = explode(', ', $languageList);

		return $languages;
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
        $defaultShippingCountryId = (string)$this->getWebstoreConfig()->defaultShippingCountryId;

        /** @var SessionStorageService $sessionService */
        $sessionService = pluginApp(SessionStorageService::class);

        if($defaultShippingCountryId !== null && $defaultShippingCountryId !== "")
        {
            return $defaultShippingCountryId;
        }

        return $this->getWebstoreConfig()->defaultShippingCountryList[$sessionService->getLang()];
    }

}
