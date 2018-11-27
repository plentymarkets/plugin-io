<?php

namespace IO\Services;

use IO\DBModels\PasswordReset;
use IO\Extensions\Mail\SendMail;
use IO\Repositories\CustomerPasswordResetRepository;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmailTemplate;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmailContact;
use Plenty\Modules\System\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Plugin\Application;

class CustomerPasswordResetService
{
    use SendMail;

    /**
     * @var CustomerPasswordResetRepository
     */
    private $customerPasswordResetRepo;

    /**
     * @var ContactRepositoryContract
     */
    private $contactRepository;

    public function __construct(
        CustomerPasswordResetRepository $customerPasswordResetRepo,
        ContactRepositoryContract $contactRepository)
    {
        $this->customerPasswordResetRepo = $customerPasswordResetRepo;
        $this->contactRepository = $contactRepository;
    }
    
    public function resetPassword($email)
    {
        $contactId = $this->getContactIdbyEmailAddress($email);
        
        if( (int)$contactId > 0) {
            $contact = $this->getContactData($contactId);

            if ($contact instanceof Contact && $contact->id > 0) {

                $this->generateHash($contact->id, $email);

                /**
                 * @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository
                 */
                $webstoreConfigurationRepository= pluginApp(WebstoreConfigurationRepositoryContract::class);

                /**
                 * @var WebstoreConfiguration $webstoreConfiguration
                 */
                $webstoreConfigugration = $webstoreConfigurationRepository->findByPlentyId($contact->plentyId);

                $params = ['contactId' => $contact->id, 'clientId' => $webstoreConfigugration->webstoreId];
                $this->sendMail(AutomaticEmailTemplate::CONTACT_NEW_PASSWORD, AutomaticEmailContact::class, $params);
                return true;
            }
        }
        return false;
    }

    public function getContactIdbyEmailAddress($email)
    {
        $contactId = $this->contactRepository->getContactIdByEmail($email);

        return $contactId;
    }

    public function generateHash($contactId, $email)
    {
        $hash =  sha1(microtime(true));
        $this->customerPasswordResetRepo->addEntry($contactId, $email, $hash);
        return $hash;
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

    public function getLastHashOrCreate($contactId, $email)
    {
        $existingPasswordResetEntry = $this->findExistingHash($contactId);
        if ($existingPasswordResetEntry instanceof PasswordReset) {
            if (!$this->checkHashExpiration($existingPasswordResetEntry->timestamp)) {
                $this->deleteHash($contactId);
            } else {
                return $existingPasswordResetEntry->hash;
            }
        }
        return $this->generateHash($contactId, $email);
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