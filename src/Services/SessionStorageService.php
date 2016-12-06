<?php //strict

namespace IO\Services;

use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;

/**
 * Class SessionStorageService
 * @package IO\Services
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
     * Set the value in the session
     * @param string $name
     * @param $value
     */
	public function setSessionValue(string $name, $value)
	{
		$this->sessionStorage->getPlugin()->setValue($name, $value);
	}

    /**
     * Get a value from the session
     * @param string $name
     * @return mixed
     */
	public function getSessionValue(string $name)
	{
		return $this->sessionStorage->getPlugin()->getValue($name);
	}

    /**
     * Get the language from session
     * @return string|null
     */
	public function getLang()
	{
		return $this->sessionStorage->getLocaleSettings()->language;
	}
}
