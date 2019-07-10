<?php //strict
namespace IO\Controllers;

use IO\Constants\LogLevel;
use IO\Helper\RouteConfig;
use IO\Services\NotificationService;
use IO\Services\UserDataHashService;

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
    public function showReset($contactId, $hash): string
    {
        /** @var UserDataHashService $hashService */
        $hashService = pluginApp(UserDataHashService::class);
        $hashData = $hashService->getData($hash, $contactId);
        
        if(!is_null($hashData))
        {
            return $this->renderTemplate(
                "tpl.password-reset",
                [
                    "contactId" => $contactId,
                    "hash"      => $hash
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
