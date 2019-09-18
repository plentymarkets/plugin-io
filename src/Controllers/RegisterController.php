<?php //strict

namespace IO\Controllers;

use IO\Extensions\Constants\ShopUrls;
use IO\Services\UrlService;
use IO\Guards\AuthGuard;
use IO\Services\CustomerService;
use Plenty\Plugin\Log\Loggable;

/**
 * Class RegisterController
 * @package IO\Controllers
 */
class RegisterController extends LayoutController
{
    use Loggable;

    /**
     * Prepare and render the data for the registration
     * @param CustomerService $customerService
     * @param UrlService $urlService
     * @return string
     */
	public function showRegister(CustomerService $customerService, UrlService $urlService): string
	{
	    if($customerService->getContactId() > 0)
        {
            $this->getLogger(__CLASS__)->info("IO::Debug.RegisterController_alreadyLoggedIn");
            AuthGuard::redirect($urlService->getHomepageURL(), []);
        }
	
		return $this->renderTemplate(
			"tpl.register",
			[
				"register" => ""
			],
            false
		);
	}

    /**
     * @param UrlService $urlService
     * @param ShopUrls $shopUrls
     * @return string
     */
	public function redirectRegister(UrlService $urlService, ShopUrls $shopUrls)
    {
        return $urlService->redirectTo($shopUrls->registration);
    }
}
