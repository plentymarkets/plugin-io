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

    public static function compare( $arr1, $arr2, $fields = null )
    {
        if ( is_null($fields) )
        {
            $fields = self::getKeys( $arr1 );
        }
        $results = [];
        foreach( $fields as $field )
        {
            if ( self::get( $arr1, $field ) !== self::get( $arr2, $field ) )
            {
                $results[] = $field;
            }
        }

        return $results;
    }

    public static function get( $arr, $key )
    {
        $path = explode(".", $key);

        if ( count($path) > 0 )
        {
            $next = array_shift( $path );
            if ( count($path) === 0 )
            {
                return $arr[$next];
            }
            return self::get( $arr[$next], implode(".", $path ) );
        }

        return null;
    }

    public static function getKeys( $arr, $prefix = "" )
    {
        $result = [];

        foreach( $arr as $key => $value )
        {
            if ( is_array($value) )
            {
                $childKeys = self::getKeys( $value, $prefix . $key . ".");
                $result = array_merge( $result, $childKeys );
            }
            else
            {
                $result[] = $prefix . $key;
            }
        }

        return $result;
    }
}