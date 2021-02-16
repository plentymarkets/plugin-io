<?php

namespace IO\Helper;

/**
 * Class SafeGetter
 *
 * Helper class for getting nested properties out of array and objects.
 *
 * @package IO\Helper
 */
class SafeGetter
{
    /**
     * Get a nested property of an object/array.
     * Will return null if any nested property is empty.
     *
     * @param mixed     $object     The object / array to get the property from.
     * @param string    $path       The path of the property (e.g. "path.to.property").
     *                              To query list entries you can pass paths like "list.{id, 5}.name".
     *                              This will query the first list entry having the id '5'.
     * @return mixed
     */
    public static function get($object, $path)
    {
        $array  = ArrayHelper::toArray($object);
        preg_match_all('/{\s*\S+\s*,\s*\S+\s*}|\w+/m', $path, $fields);
        $fields = $fields[0];
        $key    = array_shift($fields);

        while(!is_null($array) && !is_null($key))
        {
            $array = self::getField($array, $key);
            $key = array_shift($fields);
        }

        return $array;
    }

    /**
     * @param array     $array
     * @param string    $field
     * @return mixed
     */
    private static function getField($array = [], $field = "")
    {
        if (!count($array) || !strlen($field))
        {
            return null;
        }

        if(preg_match('/^{\s*(\S+)\s*,\s*(\S+)\s*}$/m', $field, $matches))
        {
            $searchKey = $matches[1];
            $searchValue = $matches[2];

            foreach($array as $entry)
            {
                if(self::get($entry, $searchKey).'' === $searchValue)
                {
                    return $entry;
                }
            }
        }

        return $array[$field];
    }
}
