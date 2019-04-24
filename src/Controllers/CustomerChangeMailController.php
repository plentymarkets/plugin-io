<?php //strict
namespace IO\Controllers;

use IO\Constants\LogLevel;
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
                    "oldMail"   => $hashData['oldMail'],
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

            return $this->renderTemplate(
                "tpl.home",
                [
                    "data" => ""
                ],
                false
            );
        }

    }
}
