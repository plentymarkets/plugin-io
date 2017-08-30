<?php //strict
namespace IO\Controllers;

use IO\Constants\LogLevel;
use IO\Helper\TemplateContainer;
use IO\Services\CustomerPasswordResetService;
use IO\Services\NotificationService;

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
        /**
         * @var CustomerPasswordResetService $customerPasswordResetService
         */
        $customerPasswordResetService = pluginApp(CustomerPasswordResetService::class);
        
        if($customerPasswordResetService->checkHash((int)$contactId, $hash))
        {
            return $this->renderTemplate(
                "tpl.password-reset",
                [
                    "contactId" => $contactId,
                    "hash"      => $hash
                ]
            );
        }
        else
        {
            /**
             * @var NotificationService $notificationService
             */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->addNotificationCode(LogLevel::ERROR,3);
            
            return $this->renderTemplate(
                "tpl.home",
                [
                    "data" => ""
                ]
            );
        }
        
    }
}
