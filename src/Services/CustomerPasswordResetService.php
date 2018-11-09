<?php

namespace IO\Services;

use IO\DBModels\PasswordReset;
use IO\Repositories\CustomerPasswordResetRepository;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Helper\AutomaticEmail\Contracts\AutomaticEmailContract;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmail;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmailTemplate;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmailContact;
use Plenty\Modules\System\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Plugin\Application;

class CustomerPasswordResetService
{
    /**
     * @var CustomerPasswordResetRepository
     */
    private $customerPasswordResetRepo;

    /**
     * @var ContactRepositoryContract
     */
    private $contactRepository;

	/**
     * @var AutomaticEmailContract
     */
    private $automaticEmailRepository;

    public function __construct(
        CustomerPasswordResetRepository $customerPasswordResetRepo,
        ContactRepositoryContract $contactRepository,
        AutomaticEmailContract $automaticEmailRepositoryContract)
    {
        $this->customerPasswordResetRepo = $customerPasswordResetRepo;
        $this->contactRepository = $contactRepository;
        $this->automaticEmailRepository = $automaticEmailRepositoryContract;
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

                /**
                 * @var AutomaticEmailContact $emailData
                 */
                $emailData = pluginApp(Application::class)->make(AutomaticEmailContact::class, ['contactId' => $contact->id, 'clientId' => $webstoreConfigugration->webstoreId]);

                 /**
                 * @var AutomaticEmail $email
                 */
                $email = pluginApp(Application::class)->make(AutomaticEmail::class, ['template' => AutomaticEmailTemplate::CONTACT_NEW_PASSWORD , 'emailData' => $emailData ]);
                $this->automaticEmailRepository->sendAutomatic($email);
            }
        }
        return true;
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