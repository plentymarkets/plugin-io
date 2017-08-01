<?php
/**
 * Created by IntelliJ IDEA.
 * User: ihussein
 * Date: 31.07.17
 */

namespace IO\DBModels;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;


/**
 * Class ItemWishList
 *
 * @property int        $id                     Unique identifier of the model
 * @property int        $variationId            The variationId being referenced
 * @property int        $contactId              The contactId being referenced
 * @property int        $plentyId               The plentyId being referenced
 * @property string     $createdAt              Created timestamp
 *
 * @package IO\DBModels
 */
class ItemWishList extends Model implements \JsonSerializable
{
    const NAMESPACE = 'IO\DBModels\ItemWishList';

    public $id               ;
    public $variationId      ;
    public $contactId        ;
    public $plentyId         ;
    public $quantity         ;
    public $createdAt        ;

    /**
     * @return string
     */
    public function getTableName():string
    {
        return 'IO::itemWishList';
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
            'id'                => $this->id                ,
            'contactId'         => $this->contactId         ,
            'plentyId'          => $this->plentyId          ,
            'variationId'       => $this->variationId       ,
            'quantity'          => $this->quantity          ,
            'createdAt'         => $this->createdAt         ,
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
            case 'id'               : return $this->id               ;
            case 'contactId'        : return $this->contactId        ;
            case 'plentyId'         : return $this->plentyId         ;
            case 'variationId'      : return $this->variationId      ;
            case 'quantity'         : return $this->quantity         ;
            case 'createdAt'        : return $this->createdAt        ;
        }
    }
}