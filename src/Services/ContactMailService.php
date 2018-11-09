<?php

namespace IO\Services;

use Plenty\Plugin\Mail\Contracts\MailerContract;
use Plenty\Plugin\Mail\Models\ReplyTo;
use Plenty\Plugin\Templates\Twig;
use IO\Services\TemplateConfigService;
use IO\Validators\Customer\ContactFormValidator;

class ContactMailService
{
    public function __construct()
    {
    
    }
    
    public function sendMail($mailTemplate, $recipient = null, $subject = "", $cc = [], $replyToData = null, $data = [])
    {
        if ( is_null( $recipient ) )
        {
            /** @var TemplateConfigService $templateConfigService */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $recipient = $templateConfigService->get('contact.shop_mail');

        }

        if(!strlen($recipient) || !strlen($mailTemplate))
        {
            return false;
        }
        
        /** @var Twig */
        $twig = pluginApp(Twig::class);
        
        $mailBody = $twig->render(
            $mailTemplate,
            [
                'data' => $data
            ]
        );
        
        if(!strlen($mailBody))
        {
            return false;
        }
        
        /** @var MailerContract $mailer */
        $mailer = pluginApp(MailerContract::class);

        $replyTo = null;
        if ( !is_null($replyToData) )
        {
            /** @var ReplyTo $replyTo */
            $replyTo = pluginApp(ReplyTo::class);
            $replyTo->mailAddress = $replyToData['mailAddress'];
            $replyTo->name = $replyToData['name'];
        }
        $mailer->sendHtml($mailBody, $recipient, $subject, $cc, [], $replyTo);
        
        return true;
    }
}