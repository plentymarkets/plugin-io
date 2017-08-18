<?php

namespace IO\DBModels;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class PasswordReset
 *
 * @property int    $id
 * @property int    $plentyId
 * @property int    $contactId
 * @property string $email
 * @property string $hash
 * @property string $timestamp
 *
 * @package IO\DBModels
 */
class PasswordReset extends Model implements \JsonSerializable
{
    const NAMESPACE = 'IO\DBModels\PasswordReset';
    
    public $id;
    public $plentyId;
    public $contactId;
    public $email;
    public $hash;
    public $timestamp;
    
    /**
     * @return string
     */
    public function getTableName():string
    {
        return 'IO::passwordReset';
    }
    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'id'        => $this->id,
            'plentyId'  => $this->plentyId,
            'contactId' => $this->contactId,
            'email'     => $this->email,
            'hash'      => $this->hash,
            'timestamp' => $this->timestamp,
        ];
    }
    
    public function fillByAttributes($attributes)
    {
        foreach($attributes as $attr => $val)
        {
            if(array_key_exists($attr, $this->jsonSerialize()))
            {
                $ref = &$this->getVarRef($attr);
                $ref = $val;
            }
        }
    }
    
    private function &getVarRef($varName)
    {
        switch($varName)
        {
            case 'id'        : return $this->id;
            case 'plentyId'  : return $this->plentyId;
            case 'contactId' : return $this->contactId;
            case 'email'     : return $this->email;
            case 'hash'      : return $this->hash;
            case 'timestamp' : return $this->timestamp;
        }
    }
}
