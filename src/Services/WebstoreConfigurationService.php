<?php //strict

namespace LayoutCore\Services;

use Plenty\Plugin\Application;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;

/**
 * Class WebstoreConfigurationService
 * @package LayoutCore\Services
 */
class WebstoreConfigurationService
{
	/**
	 * @var WebstoreRepositoryContract
	 */
	private $webstoreRepository;

    /**
     * @var Apllication
     */
    private $app;

    /**
     * @var webstoreId
     */
    private $webstoreId;

    /**
     * WebstoreConfigurationService constructor.
     * @param WebstoreRepositoryContract $webstoreRepository
     * @param Application $app
     */
	public function __construct(Application $app, WebstoreRepositoryContract $webstoreRepository)
	{
        $this->app                = $app;
		$this->webstoreRepository = $webstoreRepository;

        $this->webstoreId         = $app->getPlentyId();
	}

	/**
	 * Get the activate languages of the webstore
	 */
    public function getActiveLanguageList()
	{
		return $this->webstoreRepository->findByPlentyId($this->webstoreId)->configuration->languageList;
	}

	/**
	 * Get the defaultlanguage of the webstore
	 */
    public function getdefaultLanguage()
    {
        return $this->webstoreRepository->findByPlentyId($this->webstoreId)->configuration->defaultLanguage;
    }

}
