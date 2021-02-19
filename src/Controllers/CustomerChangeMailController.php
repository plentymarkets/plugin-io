<?php //strict
namespace IO\Controllers;

use IO\Constants\LogLevel;
use IO\Helper\RouteConfig;
use IO\Services\AuthenticationService;
use IO\Services\NotificationService;
use IO\Services\UserDataHashService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;

/**
 * Class CustomerChangeMailController
 * @package IO\Controllers
 */
class CustomerChangeMailController extends LayoutController
{
    /**
     * @param int $contactId
     * @param string $hash
     * @return string
     * @throws \ErrorException
     */
    public function show($contactId, $hash, $category = null): string
    {
        /** @var AuthenticationService $authService */
        $authService = pluginApp(AuthenticationService::class);
        $authService->logout();

        /** @var UserDataHashService $hashService */
        $hashService = pluginApp(UserDataHashService::class);
        $hashData = $hashService->getData($hash, (int)$contactId);

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);

        if (!is_null($hashData) || $shopBuilderRequest->isShopBuilder()) {
            return $this->renderTemplate(
                "tpl.change-mail",
                [
                    "contactId" => $contactId,
                    "hash" => $hash,
                    "newMail" => $hashData['newMail'],
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
        if (!is_null($categoryByUrl = $this->checkForExistingCategory())) {
            return $categoryByUrl;
        }

        $changeMailParams = [];

        if ((int)$contactId > 0 && strlen($hash)) {
            $changeMailParams['contactId'] = $contactId;
            $changeMailParams['hash'] = $hash;
        }

        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);

        return $categoryController->redirectToCategory(
            RouteConfig::getCategoryId(RouteConfig::CHANGE_MAIL),
            '/change-mail',
            $changeMailParams
        );
    }
}
