<?php //strict
namespace IO\Controllers;

use IO\Constants\LogLevel;
use IO\Helper\RouteConfig;
use IO\Services\AuthenticationService;
use IO\Services\NotificationService;
use IO\Services\UserDataHashService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;

/**
 * Class CustomerPasswordResetController
 * @package IO\Controllers
 */
class CustomerPasswordResetController extends LayoutController
{
    /**
     * Prepare and render the data for the guest registration
     * @return string
     */
    public function showReset($contactId, $hash, $category = null): string
    {
        /** @var AuthenticationService $authService */
        $authService = pluginApp(AuthenticationService::class);
        $authService->logout();

        /** @var UserDataHashService $hashService */
        $hashService = pluginApp(UserDataHashService::class);
        $hashData = $hashService->getData($hash, $contactId);

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);


        if (!is_null($hashData) || $shopBuilderRequest->isShopBuilder()) {
            return $this->renderTemplate(
                "tpl.password-reset",
                [
                    "contactId" => $contactId,
                    "hash" => $hash,
                    "category" => $category
                ],
                false
            );
        } else {
            /**
             * @var NotificationService $notificationService
             */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->addNotificationCode(LogLevel::ERROR, 3);

            /** @var HomepageController $homepageController */
            $homepageController = pluginApp(HomepageController::class);

            if (RouteConfig::getCategoryId(RouteConfig::HOME) > 0) {
                return $homepageController->showHomepageCategory();
            }

            return $homepageController->showHomepage();
        }
    }

    public function redirect($contactId, $hash)
    {
        $passwordResetParams = [];

        if ((int)$contactId > 0 && strlen($hash)) {
            $passwordResetParams['contactId'] = $contactId;
            $passwordResetParams['hash'] = $hash;
        }

        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);

        return $categoryController->redirectToCategory(
            RouteConfig::getCategoryId(RouteConfig::PASSWORD_RESET),
            '/password-reset',
            $passwordResetParams
        );
    }
}
