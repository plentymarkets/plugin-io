<?php

namespace IO\Services;

use Plenty\Plugin\Mail\Contracts\MailerContract;
use Plenty\Plugin\Mail\Models\ReplyTo;
use Plenty\Plugin\Templates\Twig;
use IO\Services\TemplateConfigService;
use IO\Validators\Customer\ContactFormValidator;

class ContactMailService
{
    private $name = '';
    private $message = '';
    private $orderId = '';
    
    public function __construct()
    {
    
    }
    
    public function sendMail($mailTemplate, $contactData = [])
    {
        ContactFormValidator::validateOrFail($contactData);
    
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $recipient = $templateConfigService->get('contact.shop_mail');
        
        if(!strlen($recipient) || !strlen($mailTemplate))
        {
            return false;
        }
        
        /**
         * @var Twig
         */
        $twig = pluginApp(Twig::class);
    
        $mailTemplateParams = [];
        foreach($contactData as $key => $value)
        {
            $mailTemplateParams[$key] = nl2br($value);
        }
        
        $renderedMailTemplate = $twig->render($mailTemplate, $mailTemplateParams);
        
        if(!strlen($renderedMailTemplate))
        {
            return false;
        }
        
        $cc = [];
        if(isset($contactData['cc']) && $contactData['cc'] == 'true')
        {
            $cc[] = $contactData['userMail'];
        }
        
        /**
         * @var MailerContract $mailer
         */
        $mailer = pluginApp(MailerContract::class);

        /**
         * @var ReplyTo $replyTo
         */
        $replyTo = pluginApp(ReplyTo::class);
        $replyTo->mailAddress = $contactData['userMail'];
        $replyTo->name = $contactData['name'];

        $mailer->sendHtml($renderedMailTemplate, $recipient, $contactData['subject'], $cc, [], $replyTo);
        
        return true;
    }
}