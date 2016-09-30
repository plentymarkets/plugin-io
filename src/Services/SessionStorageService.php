<?php //strict

namespace LayoutCore\Services;

use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;

/**
 * Class SessionStorageService
 * @package LayoutCore\Services
 */
class SessionStorageService
{
	/**
	 * @var FrontendSessionStorageFactoryContract
	 */
	private $sessionStorage;
    
    /**
     * SessionStorageService constructor.
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     */
	public function __construct(FrontendSessionStorageFactoryContract $sessionStorage)
	{
		$this->sessionStorage = $sessionStorage;
	}
    
    /**
     * set value in session
     * @param string $name
     * @param $value
     */
	public function setSessionValue(string $name, $value)
	{
		$this->sessionStorage->getPlugin()->setValue($name, $value);
	}
    
    /**
     * get value from session
     * @param string $name
     * @return mixed
     */
	public function getSessionValue(string $name)
	{
		return $this->sessionStorage->getPlugin()->getValue($name);
	}
    
    /**
     * get language from session
     * @return string
     */
	public function getLang():string
	{
		return $this->sessionStorage->getLocaleSettings()->language;
	}
}
