<?php

namespace IO\Services;

use Plenty\Plugin\Mail\Contracts\MailerContract;
use Plenty\Plugin\Templates\Twig;

class ContactMailService
{
    private $name = '';
    private $message = '';
    
    public function __construct()
    {
    
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function sendMail($mailTemplate, $contactData = [])
    {
        $this
            ->setName($contactData['name'])
            ->setMessage($contactData['message']);
        
        /**
         * @var Twig
         */
        $twig = pluginApp(Twig::class);
        $renderedMailTemplate = $twig->render($mailTemplate);
        
        /**
         * @var MailerContract $mailer
         */
        $mailer = pluginApp(MailerContract::class);
        $mailer->sendHtml($renderedMailTemplate, 'dominik.meyer@plentymarkets.com', $contactData['subject']);
    }
}