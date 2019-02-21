<?php //strict
namespace IO\Controllers;

use IO\Extensions\Constants\ShopUrls;
use IO\Services\UrlService;
use Plenty\Plugin\Http\Response;
use IO\Guards\AuthGuard;
use IO\Helper\TemplateContainer;
use IO\Services\CustomerService;

/**
 * Class RegisterController
 * @package IO\Controllers
 */
class RegisterController extends LayoutController
{
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
     */
	public function redirectRegister(UrlService $urlService)
    {
        return $urlService->redirectTo(pluginApp(ShopUrls::class)->registration);
    }
}
