<?php

namespace IO\Controllers;

use IO\Constants\LogLevel;
use IO\Services\CustomerNewsletterService;
use IO\Services\NotificationService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

class NewsletterOptOutConfirmationController extends LayoutController
{
    public function showOptOutConfirmation()
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);
        $email = $request->get('email', '');
        
        /** @var CustomerNewsletterService $newsletterService */
        $newsletterService = pluginApp(CustomerNewsletterService::class);
        $newsletterService->deleteNewsletterDataByEmail($email);
        
        /** @var NotificationService $notificationService */
        $notificationService = pluginApp(NotificationService::class);
        $notificationService->addNotificationCode(LogLevel::SUCCESS, 7);
        
        return pluginApp(Response::class)->redirectTo('/');
    }
}