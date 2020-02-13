<?php //strict
namespace IO\Controllers;

use IO\Guards\AuthGuard;
use IO\Helper\RouteConfig;
use IO\Helper\TemplateContainer;
use IO\Services\CustomerService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Plugin\Http\Request;
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
        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);

        if($customerService->getContactId() > 0 && !$shopBuilderRequest->isShopBuilder())
        {
            $this->getLogger(__CLASS__)->info("IO::Debug.LoginController_alreadyLoggedIn");
            AuthGuard::redirect($this->urlService->getHomepageURL(), []);
        }

        $shopBuilderRequest->setMainContentType('checkout');

		return $this->renderTemplate(
			"tpl.login",
			[
				"login" => ""
			],
            false
		);
	}

    public function redirect()
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);

        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);
        return $categoryController->redirectRoute(RouteConfig::LOGIN, ['backlink' => $request->get('backlink', '')]);
    }
}
