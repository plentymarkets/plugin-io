<?php //strict

namespace IO\Controllers;

use IO\Extensions\Constants\ShopUrls;
use IO\Helper\RouteConfig;
use IO\Services\UrlService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use IO\Guards\AuthGuard;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
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
     * @param ContactRepositoryContract $contactRepository
     * @param UrlService $urlService
     * @return string
     */
	public function showRegister(ContactRepositoryContract $contactRepository, UrlService $urlService): string
	{
        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);
	    if($contactRepository->getContactId() > 0 && !$shopBuilderRequest->isShopBuilder())
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

    public function redirect()
    {
        if(!is_null($categoryByUrl = $this->checkForExistingCategory())) {
            return $categoryByUrl;
        }

        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);
        return $categoryController->redirectRoute(RouteConfig::REGISTER);
    }
}
