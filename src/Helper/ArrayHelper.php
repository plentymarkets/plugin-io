<?php

namespace IO\Helper;

class ArrayHelper
{
    /**
     * @param $mixed
     *
     * @return array
     */
    public static function toArray($mixed )
    {
        if (is_object($mixed)) {
            return json_decode(json_encode($mixed), true);
        }

        if (!is_array($mixed)) {
            return [];
        }

        return $mixed;
    }

    public static function isAssoc( $mixed )
    {
        if ( !is_array( $mixed ) )
        {
            return false;
        }

        return array_keys( $mixed ) !== range(0, count( $mixed ) - 1);
    }
}