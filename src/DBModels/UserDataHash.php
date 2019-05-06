<?php

namespace IO\DBModels;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class UserDataHash
 *
 * @property int    $id
 * @property string $type
 * @property int    $plentyId
 * @property int    $contactId
 * @property string $hash
 * @property array  $data
 * @property string $createdAt
 * @property string $expiresAt
 *
 * @package IO\DBModels
 */
class UserDataHash extends Model implements \JsonSerializable
{
    const TYPE_CHANGE_MAIL = 'change-mail';
    const TYPE_RESET_PASSWORD = 'reset-password';

    public $id;
    public $type;
    public $plentyId;
    public $contactId;
    public $hash;
    public $data;
    public $createdAt;
    public $expiresAt;
    
    /**
     * @return string
     */
    public function getTableName():string
    {
        return 'IO::userDataHash';
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
            'type'      => $this->type,
            'plentyId'  => $this->plentyId,
            'contactId' => $this->contactId,
            'hash'      => $this->hash,
            'data'      => json_encode($this->data),
            'createdAt' => $this->createdAt,
            'expiresAt' => $this->expiresAt
        ];
    }

    public function fillByAttributes($attributes)
    {
        foreach($attributes as $attr => $val)
        {
            if(array_key_exists($attr, $this->jsonSerialize()))
            {
                $ref = &$this->getVarRef($attr);

                if ( $attr === 'data' && is_string($val) )
                {
                    $val = json_decode($val, true);
                }

                $ref = $val;
            }
        }
    }

    private function &getVarRef($varName)
    {
        switch($varName)
        {
            case 'id'       : return $this->id;
            case 'type'     : return $this->type;
            case 'plentyId' : return $this->plentyId;
            case 'contactId': return $this->contactId;
            case 'hash'     : return $this->hash;
            case 'data'     : return $this->data;
            case 'createdAt': return $this->createdAt;
            case 'expiresAt': return $this->expiresAt;
        }
    }
}
