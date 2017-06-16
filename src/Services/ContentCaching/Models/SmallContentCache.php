<?php
namespace IO\Services\ContentCaching\Models;

/**
 * Created by ptopczewski, 16.06.17 09:59
 * Class SmallContentCache
 * @package IO\Services\ContentCaching\Models
 */
class SmallContentCache implements \JsonSerializable
{
    public $content = '';

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [];
    }
}