<?php

namespace IO\Controllers;

use IO\Services\CustomerNewsletterService;

class NewsletterOptInController extends LayoutController
{
    public function showOptInConfirmation($authString, $newsletterEmailId)
    {
        /** @var CustomerNewsletterService $newsletterService */
        $newsletterService = pluginApp(CustomerNewsletterService::class);
        $success = $newsletterService->updateOptInStatus($authString, $newsletterEmailId);
        
        if($success)
        {
            return $this->renderTemplate(
                'tpl.newsletter.opt-in',
                ['data' => ''],
                false
            );
        }
    
        return $this->renderTemplate(
            'tpl.page-not-found',
            ['data' => ''],
            false
        );
    }
}