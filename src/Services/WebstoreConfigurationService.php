<?php //strict

namespace LayoutCore\Services;

use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Plugin\Application;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;

/**
 * Class WebstoreConfigurationService
 * @package LayoutCore\Services
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
		return $this->getWebstoreConfig()->languageList;
	}

	/**
	 * Get the default language of the webstore
	 */
    public function getDefaultLanguage()
    {
        return $this->getWebstoreConfig()->defaultLanguage;
    }

}
