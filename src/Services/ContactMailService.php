<?php

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Mail\Contracts\MailerContract;
use Plenty\Plugin\Mail\Models\ReplyTo;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Translation\Translator;

/**
 * Class ContactMailService
 *
 * This service class contains a method for sending Emails.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class ContactMailService
{
    use Loggable;

    /**
     * Send an email using a template and a data object
     *
     * @param string $mailTemplate The template for the email
     * @param array $mailData Optional: Data for Email
     * @return bool
     */
    public function sendMail(string $mailTemplate, $mailData = [])
    {
        $recipient = $mailData['recipient'];

        if (!strlen($recipient)) {
            $recipient = Utils::getTemplateConfig('contact.shop_mail');
        }

        if (!strlen($recipient) || !strlen($mailTemplate)) {
            $this->getLogger(__CLASS__)->error("IO::Debug.ContactMailService_noRecipient");
            return false;
        }

        /** @var Twig */
        $twig = pluginApp(Twig::class);

        $mailBody = $twig->render(
            $mailTemplate,
            $mailData
        );

        if (!strlen($mailBody)) {
            $this->getLogger(__CLASS__)->error("IO::Debug.ContactMailService_noMailContent");
            return false;
        }

        /** @var MailerContract $mailer */
        $mailer = pluginApp(MailerContract::class);

        $replyTo = null;
        if (array_key_exists('replyTo', $mailData)) {
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
                'data'    => ''
            ]
        );

        try {
            $mailer->sendHtml($mailBody, $recipient, $subject, $mailData['cc'] ?? [], $mailData['bcc'] ?? [], $replyTo);
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }
}
