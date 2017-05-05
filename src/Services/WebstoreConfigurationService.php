<?php //strict

namespace IO\Services;

use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Plugin\Application;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;

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
            /** @var WebstoreRepositoryContract $webstoreRepository */
            $webstoreRepository = pluginApp( WebstoreRepositoryContract::class );

            /** @var Application $app */
            $app = pluginApp( Application::class );

            $this->webstoreConfig = $webstoreRepository->findByPlentyId($app->getPlentyId())->configuration;
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
