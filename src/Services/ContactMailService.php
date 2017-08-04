<?php

namespace IO\Services;

use Plenty\Plugin\Mail\Contracts\MailerContract;
use Plenty\Plugin\Templates\Twig;
use IO\Services\TemplateConfigService;

class ContactMailService
{
    private $name = '';
    private $message = '';
    
    public function __construct()
    {
    
    }
    
    public function sendMail($mailTemplate, $contactData = [])
    {
        /**
         * @var Twig
         */
        $twig = pluginApp(Twig::class);
    
        $mailtemplateParams = [
            'name' => $contactData['name'],
            'message' => $contactData['message'],
            'user_mail' => $contactData['user_mail']
        ];
    
        $renderedMailTemplate = $twig->render($mailTemplate, $mailtemplateParams);
    
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $recipient = $templateConfigService->get('contact.shop_mail');
        
        /**
         * @var MailerContract $mailer
         */
        $mailer = pluginApp(MailerContract::class);
        $mailer->sendHtml($renderedMailTemplate, $recipient, $contactData['subject']);
    }
}