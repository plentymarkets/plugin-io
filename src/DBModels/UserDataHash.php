<?php

namespace IO\DBModels;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class PasswordReset
 *
 * @property int    $id
 * @property string $type
 * @property int    $plentyId
 * @property int    $contactId
 * @property string $hash
 * @property string $data
 * @property string $createdAt
 * @property string $expiresAt
 *
 * @package IO\DBModels
 */
class UserDataHash extends Model
{
    const TYPE_CHANGE_MAIL = 'change-mail';

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
}
