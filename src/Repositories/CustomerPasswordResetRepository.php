<?php

namespace IO\Repositories;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\Plugin\DataBase\Contracts\Query;
use IO\DBModels\PasswordReset;
use Plenty\Plugin\Application;

class CustomerPasswordResetRepository
{
    /** @var  DataBase */
    private $db;
    
    /**
     * CustomerPasswordResetRepository constructor.
     * @param DataBase $dataBase
     */
    public function __construct(DataBase $dataBase)
    {
        $this->db 		= $dataBase;
    }
    
    public function addEntry($contactId, $email, $hash)
    {
        $plentyId = pluginApp(Application::class)->getPlentyID();
    
        if($contactId > 0 && strlen($email) && strlen($hash))
        {
            $existingEntry = $this->findExistingEntry($plentyId, $contactId);
    
            $passwordResetEntry = pluginApp(PasswordReset::class);
            
            if(empty($existingEntry))
            {
                $passwordResetEntry->plentyId = $plentyId;
                $passwordResetEntry->contactId = $contactId;
                $passwordResetEntry->email = $email;
            }
            elseif($existingEntry instanceof PasswordReset)
            {
                $passwordResetEntry->id = $existingEntry->id;
                $passwordResetEntry->plentyId = $existingEntry->plentyId;
                $passwordResetEntry->contactId = $existingEntry->contactId;
                $passwordResetEntry->email = $existingEntry->email;
            }
    
            $passwordResetEntry->hash = $hash;
            $passwordResetEntry->timestamp = date("Y-m-d H:i:s");
    
            $createdPasswordResetEntry = $this->db->save($passwordResetEntry);
            return pluginApp(PasswordReset::class)->fillByAttributes(json_decode(json_encode($createdPasswordResetEntry), true));
        }
        else
        {
            throw new \Exception('', 401);
        }
    }
    
    public function findExistingEntry($plentyId, $contactId)
    {
        $result = $this->db->query(PasswordReset::NAMESPACE)->where('contactId', '=', (int)$contactId)->where('plentyId', '=', (int)$plentyId)->get();
        return $result[0];
    }
    
    public function deleteEntry($contactId)
    {
        $response = false;
        $plentyId = pluginApp(Application::class)->getPlentyID();
    
        if($contactId > 0)
        {
            $existingEntry = $this->findExistingEntry($plentyId, $contactId);
            
            if($existingEntry instanceof PasswordReset)
            {
                $response = $this->db->delete($existingEntry);
            }
        
            return $response;
        }
        else
        {
            throw new \Exception('', 401);
        }
    }
}
