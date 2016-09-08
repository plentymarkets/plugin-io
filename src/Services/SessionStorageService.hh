<?hh //strict

namespace LayoutCore\Services;

use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;

class SessionStorageService
{
    private FrontendSessionStorageFactoryContract $sessionStorage;

    public function __construct(FrontendSessionStorageFactoryContract $sessionStorage)
    {
        $this->sessionStorage = $sessionStorage;
    }

    public function setSessionValue(string $name, mixed $value):void
    {
        $this->sessionStorage->getPlugin()->setValue($name, $value);
    }

    public function getSessionValue(string $name):mixed
    {
        return $this->sessionStorage->getPlugin()->getValue($name);
    }

    public function getLang():string
    {
      return $this->sessionStorage->getLocaleSettings()->language;
    }
}
