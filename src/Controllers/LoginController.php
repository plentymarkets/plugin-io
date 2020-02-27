<?php

namespace IO\Controllers;

use IO\Guards\AuthGuard;
use IO\Helper\RouteConfig;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
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
     * @param ContactRepositoryContract $contactRepository
     * @return string
     */
    public function showLogin(ContactRepositoryContract $contactRepository): string
    {
        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);

        if ($contactRepository->getContactId() > 0 && !$shopBuilderRequest->isShopBuilder()) {
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
        if (!is_null($categoryByUrl = $this->checkForExistingCategory())) {
            return $categoryByUrl;
        }

        /** @var Request $request */
        $request = pluginApp(Request::class);
        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);
        return $categoryController->redirectRoute(RouteConfig::LOGIN, ['backlink' => $request->get('backlink', '')]);
    }
}
