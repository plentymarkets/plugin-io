<?php

namespace IO\Services;

use IO\DBModels\PasswordReset;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Plugin\Mail\Contracts\MailerContract;
use IO\Repositories\CustomerPasswordResetRepository;
use IO\Services\WebstoreConfigurationService;
use Plenty\Plugin\Application;
use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Plugin\Translation\Translator;

class CustomerPasswordResetService
{
    private $customerPasswordResetRepo;
    private $contactRepository;
    private $webstoreConfig;
    
    public function __construct(CustomerPasswordResetRepository $customerPasswordResetRepo, ContactRepositoryContract $contactRepository)
    {
        $this->customerPasswordResetRepo = $customerPasswordResetRepo;
        $this->contactRepository = $contactRepository;
        $this->loadWebstoreConfig();
    }
    
    public function resetPassword($email, $template, $mailSubject = 'password reset')
    {
        $contactId = $this->getContactIdbyEmailAddress($email);
        
        if((int)$contactId > 0)
        {
            $hash = $this->generateHash();
            $this->customerPasswordResetRepo->addEntry($contactId, $email, $hash);
            $resetURL = $this->buildMailURL($contactId, $hash);
            
            $contact = $this->getContactData($contactId);
            
            $mailContent = $resetURL;
            if(strlen($template) && $contact instanceof Contact)
            {
                $mailTemplateParams = [
                    'firstname' => $contact->firstName,
                    'lastname'  => $contact->lastName,
                    'email'     => $email,
                    'url'       => $resetURL,
                    'shopname'  => $this->webstoreConfig->name
                ];
        
                /**
                 * @var Twig
                 */
                $twig = pluginApp(Twig::class);
                $renderedMailTemplate = $twig->render($template, $mailTemplateParams);
        
                if(strlen($renderedMailTemplate))
                {
                    $mailContent = $renderedMailTemplate;
                }
            }
    
            /** @var Translator $translator */
            $translator = pluginApp(Translator::class);
            
            /**
             * @var MailerContract $mailer
             */
            $mailer = pluginApp(MailerContract::class);
            $mailer->sendHtml($mailContent, $email, $translator->trans($mailSubject));
        }
        
        return true;
    }
    
    public function getContactIdbyEmailAddress($email)
    {
        $contactId = $this->contactRepository->getContactIdByEmail($email);
        
        return $contactId;
    }
    
    private function generateHash()
    {
        return sha1(microtime(true));
    }
    
    private function buildMailURL($contactId, $hash)
    {
        $domain = $this->webstoreConfig->domainSsl;
        $url = $domain.'/password-reset/'.$contactId.'/'.$hash;
        
        return $url;
    }
    
    public function checkHash($contactId, $hash)
    {
        $existingEntry = $this->customerPasswordResetRepo->findExistingEntry((int)pluginApp(Application::class)->getPlentyID(), (int)$contactId);
        if($existingEntry instanceof PasswordReset && $existingEntry->hash == $hash && $this->checkHashExpiration($existingEntry->timestamp))
        {
            return true;
        }
        
        return false;
    }
    
    public function checkHashExpiration($hashTimestamp)
    {
        $expirationDays = 1;
        $unixTimestamp = strtotime($hashTimestamp);
        if( ((int)$unixTimestamp > 0) && (time() > ($unixTimestamp + ((24*60*60)*$expirationDays))) )
        {
            return false;
        }
    
        return true;
    }
    
    public function findExistingHash($contactId)
    {
        return $this->customerPasswordResetRepo->findExistingEntry((int)pluginApp(Application::class)->getPlentyID(), $contactId);
    }
    
    public function deleteHash($contactId)
    {
        return $this->customerPasswordResetRepo->deleteEntry((int)$contactId);
    }
    
    private function loadWebstoreConfig()
    {
        /**
         * @var WebstoreConfigurationService $webstoreConfigService
         */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);
        $this->webstoreConfig = $webstoreConfigService->getWebstoreConfig();
    }
    
    private function getContactData($contactId)
    {
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        $contactRepo = $this->contactRepository;
    
        $contact = $authHelper->processUnguarded( function() use ($contactId, $contactRepo)
        {
            return $contactRepo->findContactById((int)$contactId);
        });
        
        return $contact;
    }
}