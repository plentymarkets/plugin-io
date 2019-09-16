<?php //strict
namespace IO\Controllers;

use IO\Extensions\Constants\ShopUrls;
use IO\Services\UrlService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
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
        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);
	    if($customerService->getContactId() > 0 && !$shopBuilderRequest->isShopBuilder())
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
     */
	public function redirectRegister(UrlService $urlService)
    {
        return $urlService->redirectTo(pluginApp(ShopUrls::class)->registration);
    }
}
