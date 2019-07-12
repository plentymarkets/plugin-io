<?php

namespace IO\Services;

use Plenty\Plugin\Mail\Contracts\MailerContract;
use Plenty\Plugin\Mail\Models\ReplyTo;
use Plenty\Plugin\Templates\Twig;
use IO\Services\TemplateConfigService;
use IO\Validators\Customer\ContactFormValidator;
use Plenty\Plugin\Translation\Translator;

class ContactMailService
{
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
        

        /*
        $bcc = [];
        $recipientBcc = $templateConfigService->get('contact.shop_mail_bcc');
        if(!is_null($recipientBcc) && strlen($recipientBcc))
        {
            $recipientBcc = explode(',', $recipientBcc);
            foreach($recipientBcc as $_bccMail)
            {
                if($_bccMail != "your@email.com")
                {
                    $bcc[] = trim($_bccMail);
                }
            }
        }

        $mailTemplateParams = [];
        foreach($contactData as $key => $value)
        {
            $mailTemplateParams[$key] = nl2br($value);
        }



        $recipientCc = $templateConfigService->get('contact.shop_mail_cc');
        if(!is_null($recipientCc) && strlen($recipientCc))
        {
            $recipientCc = explode(',', $recipientCc);
            foreach($recipientCc as $_ccMail)
            {
                if($_ccMail != "your@email.com")
                {
                    $cc[] = trim($_ccMail);
                }
            }
        }

        $translator = pluginApp(Translator::class);
        $contactData['subject'] = $translator->trans(
            'Ceres::Template.contactMailSubject',
            [
                'subject' => $contactData['subject'],
                'orderId' => $contactData['orderId']
            ]
        );

        */
        return true;
    }
}