<?php //strict

namespace LayoutCore\Services;

use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;

class SessionStorageService
{
	/**
	 * @var FrontendSessionStorageFactoryContract
	 */
	private $sessionStorage;
	
	public function __construct(FrontendSessionStorageFactoryContract $sessionStorage)
	{
		$this->sessionStorage = $sessionStorage;
	}
	
	public function setSessionValue(string $name, $value)
	{
		$this->sessionStorage->getPlugin()->setValue($name, $value);
	}
	
	public function getSessionValue(string $name)
	{
		return $this->sessionStorage->getPlugin()->getValue($name);
	}
	
	public function getLang():string
	{
		return $this->sessionStorage->getLocaleSettings()->language;
	}
}
