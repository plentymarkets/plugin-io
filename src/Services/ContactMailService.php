<?php

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\Plugin\Storage\Contracts\StorageRepositoryContract;
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
    
    const STORAGE = 'contactMailFiles';
    
    /** @var StorageRepositoryContract $storageRepository */
    private $storageRepository;
    
    public function __construct(StorageRepositoryContract $storageRepository)
    {
        $this->storageRepository = $storageRepository;
    }
    
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
                'data' => $mailData['data']
            ]
        );
        
        $attachments = [];
        if (isset($mailData['fileKey']) && strlen($mailData['fileKey'])) {
            $attachments[] = $this->getFile($mailData['fileKey']);
        }

        try {
            $mailer->sendHtml($mailBody, $recipient, $subject, $mailData['cc'] ?? [], $mailData['bcc'] ?? [], $replyTo, $attachments);
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }
    
    /**
     * @param $fileData
     * @return string
     * @throws \Plenty\Modules\Plugin\Storage\Exceptions\StorageException
     */
    public function uploadFile($fileData)
    {
        if (is_file($tmpFile = $fileData['tmp_name'])) {
            $key = basename($fileData['name']);
        
            $response = $this->storageRepository->uploadObject('IO', false, self::STORAGE.$key . '/' . $key, $tmpFile);
        
            return $response->key;
        }
    }
    
    /**
     * @param $fileKey
     * @return \Plenty\Modules\Cloud\Storage\Models\StorageObject
     */
    public function getFile($fileKey)
    {
        return $this->storageRepository->getObject('IO', $fileKey, false);
    }
}
