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
     * Get the default shipping-country-Id of the webstore
     */
    public function getDefaultShippingCountryId()
    {
        return $this->getWebstoreConfig()->defaultShippingCountryId;
    }

}
