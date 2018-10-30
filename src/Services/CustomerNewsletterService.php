<?php

namespace IO\Services;

use Illuminate\Http\Request;
use Plenty\Modules\Account\Newsletter\Contracts\NewsletterRepositoryContract;
use Plenty\Modules\Account\Newsletter\Models\Recipient;
use Plenty\Modules\Authorization\Services\AuthHelper;

class CustomerNewsletterService
{
    /** @var NewsletterRepositoryContract */
    private $newsletterRepo;
    
    public function __construct(NewsletterRepositoryContract $newsletterRepo)
    {
        $this->newsletterRepo = $newsletterRepo;
    }
    
    public function saveNewsletterData($email, $emailFolder, $firstName = '', $lastName='')
    {
        if(strlen($email))
        {
            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);
            $newsletterRepo = $this->newsletterRepo;
    
            $recipientData = $authHelper->processUnguarded( function() use ($email, $newsletterRepo)
            {
                return $newsletterRepo->listRecipients(['*'], 1, 1, ['email' => $email], [])->getResult()[0];
            });
            
            if(!$recipientData instanceof Recipient && !($recipientData instanceof Recipient && $recipientData->email == $email))
            {
                $this->newsletterRepo->addToNewsletterList($email, $firstName, $lastName, [$emailFolder]);
            }
        }
    }
    
    public function updateOptInStatus($authString, $newsletterEmailId)
    {
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        $newsletterRepo = $this->newsletterRepo;
    
        $emailData = $authHelper->processUnguarded( function() use ($newsletterEmailId, $newsletterRepo)
        {
            return $newsletterRepo->listRecipientById($newsletterEmailId);
        });
        
        if($authString === $emailData->confirmAuthString)
        {
            $authHelper->processUnguarded( function() use ($newsletterEmailId, $newsletterRepo)
            {
                $newsletterRepo->updateRecipientById($newsletterEmailId, [
                    'confirmedTimestamp' => date('Y-m-d H:i:s', time())
                ]);
            });
            
            return true;
        }
        
        return false;
    }
    
    public function deleteNewsletterDataByEmail($email)
    {
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        $newsletterRepo = $this->newsletterRepo;
        
        $recipientList = $authHelper->processUnguarded( function() use ($email, $newsletterRepo)
        {
            return $newsletterRepo->listRecipients(['*'], 1, 100, ['email' => $email], [])->getResult();
        });
    
        if(count($recipientList))
        {
            foreach($recipientList as $recipientData)
            {
                if($recipientData instanceof Recipient)
                {
                    $authHelper->processUnguarded( function() use ($recipientData, $newsletterRepo)
                    {
                        return $this->newsletterRepo->deleteRecipientById($recipientData->id);
                    });
                }
            }
        }
        
    }
}