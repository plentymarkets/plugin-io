<?php

namespace IO\Controllers;

use IO\Constants\LogLevel;
use IO\Services\CustomerNewsletterService;
use IO\Services\NotificationService;
use Plenty\Plugin\Http\Response;

class NewsletterOptInController extends LayoutController
{
    public function showOptInConfirmation($authString, $newsletterEmailId)
    {
        /** @var CustomerNewsletterService $newsletterService */
        $newsletterService = pluginApp(CustomerNewsletterService::class);
        $success = $newsletterService->updateOptInStatus($authString, $newsletterEmailId);
        
        if($success)
        {
            /** @var NotificationService $notificationService */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->addNotificationCode(LogLevel::SUCCESS, 8);
    
            return pluginApp(Response::class)->redirectTo('/');
        }
    
        return $this->renderTemplate(
            'tpl.page-not-found',
            ['data' => ''],
            false
        );
    }
}