<?php //strict
namespace IO\Controllers;

use IO\Guards\AuthGuard;
use IO\Helper\TemplateContainer;
use IO\Services\CustomerService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Plugin\Log\Loggable;

/**
 * Class LoginController
 * @package IO\Controllers
 */
class LoginController extends LayoutController
{
    use Loggable;

    /**
     * Prepare and render the data for the login
     * @param CustomerService $customerService
     * @return string
     */
	public function showLogin(CustomerService $customerService): string
	{
        if($customerService->getContactId() > 0)
        {
            $this->getLogger(__CLASS__)->info("IO::Debug.LoginController_alreadyLoggedIn");
            AuthGuard::redirect($this->urlService->getHomepageURL(), []);
        }

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);
        $shopBuilderRequest->setMainContentType('checkout');

		return $this->renderTemplate(
			"tpl.login",
			[
				"login" => ""
			],
            false
		);
	}
}
