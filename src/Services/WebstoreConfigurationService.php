<?php //strict

namespace IO\Services;

use Plenty\Modules\Plugin\PluginSet\Contracts\PluginSetRepositoryContract;
use Plenty\Modules\Plugin\PluginSet\Models\PluginSet;
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
        
        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $languages = $templateConfigService->get('language.active_languages');
        
        if(!is_null($languages) && strlen($languages))
        {
            $activeLanguages = explode(', ', $languages);
        }

        if(!in_array($this->webstoreConfig->defaultLanguage, $activeLanguages))
        {
            $activeLanguages[] = $this->webstoreConfig->defaultLanguage;
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
        $sessionService = pluginApp(SessionStorageService::class);
        $defaultShippingCountryId = $this->getWebstoreConfig()->defaultShippingCountryList[$sessionService->getLang()];

        if($defaultShippingCountryId <= 0)
        {
            $defaultShippingCountryId = $this->getWebstoreConfig()->defaultShippingCountryId;
        }

        return $defaultShippingCountryId;
    }

    /**
     * @return PluginSet
     */
    public function getPluginSet()
    {
        $pluginSetId = pluginApp(Application::class)->getPluginSetId();
        return pluginApp(PluginSetRepositoryContract::class)->get($pluginSetId);
    }
}
