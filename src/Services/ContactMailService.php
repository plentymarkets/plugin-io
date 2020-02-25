<?php

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Mail\Contracts\MailerContract;
use Plenty\Plugin\Mail\Models\ReplyTo;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Translation\Translator;

class ContactMailService
{
    use Loggable;

    public function sendMail($mailTemplate, $mailData = [])
    {
        $recipient = $mailData['recipient'];

        if ( !strlen($recipient) )
        {
            $recipient = Utils::getTemplateConfig('contact.shop_mail');
        }

        if(!strlen($recipient) || !strlen($mailTemplate))
        {
            $this->getLogger(__CLASS__)->error("IO::Debug.ContactMailService_noRecipient");
            return false;
        }

        /** @var Twig */
        $twig = pluginApp(Twig::class);

        $mailBody = $twig->render(
            $mailTemplate,
            $mailData
        );

        if(!strlen($mailBody))
        {
            $this->getLogger(__CLASS__)->error("IO::Debug.ContactMailService_noMailContent");
            return false;
        }

        /** @var MailerContract $mailer */
        $mailer = pluginApp(MailerContract::class);

        $replyTo = null;
        if ( array_key_exists('replyTo', $mailData) )
        {
            /** @var ReplyTo $replyTo */
            $replyTo = pluginApp(ReplyTo::class);
            $replyTo->mailAddress = $mailData['replyTo']['mail'];
            $replyTo->name = $mailData['replyTo']['name'];
        }

        $translator = pluginApp(Translator::class);
        $subject = $translator->trans(
            'Ceres::Template.contactMailSubject',
            [
                'subject' => $mailData['subject'],
                'data'    => $mailData['data']
            ]
        );

        try
        {
            $mailer->sendHtml($mailBody, $recipient, $subject, $mailData['cc'] ?? [], $mailData['bcc'] ?? [], $replyTo);
        }catch(\Exception $exception)
        {
            return false;
        }
        return true;
    }
}
