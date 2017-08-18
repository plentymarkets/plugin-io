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
            $existingEntry = $this->db->query(PasswordReset::NAMESPACE)->where('contactId', '=', $contactId)->where('email', '=', $email)->where('plentyId', '=', $plentyId)->get();
            
            if(empty($existingEntry))
            {
                $passwordResetEntry = pluginApp(PasswordReset::class);
                $passwordResetEntry->plentyId = $plentyId;
                $passwordResetEntry->contactId = $contactId;
                $passwordResetEntry->email = $email;
                $passwordResetEntry->hash = $hash;
                $passwordResetEntry->timestamp = date("Y-m-d H:i:s");
            
                $createdPasswordResetEntry = $this->db->save($passwordResetEntry);
            
                return pluginApp(PasswordReset::class)->fillByAttributes(json_decode(json_encode($createdPasswordResetEntry), true));
            }
            else
            {
                //TODO delete existing
            }
        }
        else
        {
            throw new \Exception('', 401);
        }
    }
}
