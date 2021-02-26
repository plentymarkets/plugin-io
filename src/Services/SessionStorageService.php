<?php //strict

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\Frontend\Events\FrontendLanguageChanged;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Frontend\Session\Storage\Models\Customer;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Http\Request;

/**
 * Service Class SessionStorageService
 *
 * This service class contains functions related to storing and reading data in the session.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 * @deprecated since 5.0.0 will be removed in 6.0.0
 * @see \Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract
 */
class SessionStorageService
{
    /**
     * @var string
     */
    private $language;

    /**
     * @var FrontendSessionStorageFactoryContract
     */
    private $sessionStorage;

    /**
     * SessionStorageService constructor.
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     */
    public function __construct(FrontendSessionStorageFactoryContract $sessionStorage, Dispatcher $eventDispatcher)
    {
        $this->sessionStorage = $sessionStorage;
        $eventDispatcher->listen(FrontendLanguageChanged::class, function (FrontendLanguageChanged $event) {
            $this->language = $event->getLanguage();
        });
    }

    /**
     * Set the value in the session
     * @param string $name Name/Key of the value
     * @param mixed $value The value to be stored
     *
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract::setSessionValue()
     */
    public function setSessionValue(string $name, $value)
    {
        $this->sessionStorage->getPlugin()->setValue($name, $value);
    }

    /**
     * Get a value from the session
     * @param string $name Name/Key of the value
     * @return mixed
     *
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract::getSessionValue()
     */
    public function getSessionValue(string $name)
    {
        return $this->sessionStorage->getPlugin()->getValue($name);
    }

    /**
     * Get the language from session
     * @return string
     *
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract::getLanguage()
     */
    public function getLang()
    {
        if (is_null($this->language)) {
            $this->language = $this->sessionStorage->getLocaleSettings()->language;

            if (is_null($this->language) || !strlen($this->language)) {
                /** @var Request $request */
                $request = pluginApp(Request::class);
                $splittedURL = explode('/', $request->get('plentyMarkets'));
                if (strpos(end($splittedURL), '.') === false && in_array($splittedURL[0], Utils::getLanguageList())) {
                    $this->language = $splittedURL[0];
                }
            }

            if (is_null($this->language) || !strlen($this->language)) {
                $this->language = Utils::getDefaultLang();
            }
        }

        return $this->language;
    }

    /**
     * Gets the model for the current customer from the session
     * @return Customer
     *
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract::getCustomer()
     */
    public function getCustomer()
    {
        return $this->sessionStorage->getCustomer();
    }
}
