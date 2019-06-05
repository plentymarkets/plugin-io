<?php

namespace IO\Helper;

class SafeGetter
{
    public static function get($object, $path)
    {
        $array  = ArrayHelper::toArray($object);
        $fields = explode(".", $path);
        $key    = array_shift($fields);

        while(!is_null($array) && !is_null($key))
        {
            $array = self::getField($array, $key);
            $key = array_shift($fields);
        }

        return $array;
    }

    private static function getField($array, $field)
    {
        if (is_null($array))
        {
            return null;
        }

        if(preg_match('/^{\s*(\S+)\s*,\s*(\S+)\s*}$/m', $field, $matches) !== false)
        {
            $searchKey = $matches[1];
            $searchValue = $matches[2];

            foreach($array as $entry)
            {
                if($entry[$searchKey]."" === $searchValue)
                {
                    return $entry;
                }
            }
        }

        return $array[$field];
    }
}