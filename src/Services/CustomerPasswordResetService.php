<?php

namespace IO\Services;

use IO\DBModels\PasswordReset;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Plugin\Mail\Contracts\MailerContract;
use IO\Repositories\CustomerPasswordResetRepository;
use IO\Services\WebstoreConfigurationService;
use Plenty\Plugin\Application;

class CustomerPasswordResetService
{
    private $customerPasswordResetRepo;
    
    public function __construct(CustomerPasswordResetRepository $customerPasswordResetRepo)
    {
        $this->customerPasswordResetRepo = $customerPasswordResetRepo;
    }
    
    public function resetPassword($email)
    {
        $contactId = $this->getContactIdbyEmailAddress($email);
        $hash = $this->generateHash();
        
        $this->customerPasswordResetRepo->addEntry($contactId, $email, $hash);
    
        $url = $this->buildMailURL($contactId, $hash);
        
        /**
         * @var MailerContract $mailer
         */
        $mailer = pluginApp(MailerContract::class);
        $mailer->sendHtml($url, $email, 'password reset');
        
        return true;
    }
    
    public function getContactIdbyEmailAddress($email)
    {
        /**
         * @var ContactRepositoryContract $contactRepo
         */
        $contactRepo = pluginApp(ContactRepositoryContract::class);
        $contactId = $contactRepo->getContactIdByEmail($email);
        
        return $contactId;
    }
    
    private function generateHash()
    {
        return sha1(microtime(true));
    }
    
    private function buildMailURL($contactId, $hash)
    {
        /**
         * @var WebstoreConfigurationService $webstoreConfigService
         */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);
        $domain = $webstoreConfigService->getWebstoreConfig()->domainSsl;
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
}