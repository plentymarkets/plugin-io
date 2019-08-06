<?php //strict
namespace IO\Controllers;

use IO\Constants\LogLevel;
use IO\Helper\RouteConfig;
use IO\Services\AuthenticationService;
use IO\Services\NotificationService;
use IO\Services\UserDataHashService;

/**
 * Class CustomerChangeMailController
 * @package IO\Controllers
 */
class CustomerChangeMailController extends LayoutController
{
    /**
     * @param $contactId
     * @param $hash
     * @return string
     * @throws \ErrorException
     */
    public function show($contactId, $hash): string
    {
        /** @var AuthenticationService $authService */
        $authService = pluginApp(AuthenticationService::class);
        $authService->logout();

        /** @var UserDataHashService $hashService */
        $hashService = pluginApp(UserDataHashService::class);
        $hashData = $hashService->getData($hash, (int)$contactId);

        if(!is_null($hashData))
        {
            return $this->renderTemplate(
                "tpl.change-mail",
                [
                    "contactId" => $contactId,
                    "hash"      => $hash,
                    "newMail"   => $hashData['newMail']
                ],
                false
            );
        }
        else
        {
            /**
             * @var NotificationService $notificationService
             */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->addNotificationCode(LogLevel::ERROR,3);
    
            /** @var HomepageController $homepageController */
            $homepageController = pluginApp(HomepageController::class);
            
            if(RouteConfig::getCategoryId(RouteConfig::HOME) > 0)
            {
                return $homepageController->showHomepageCategory();
            }
            
            return $homepageController->showHomepage();
        }

    }
}
