<?php

namespace IO\Helper;

/**
 * Class ArrayHelper
 *
 * This helper class contains functions related to arrays.
 *
 * @package IO\Helper
 */
class ArrayHelper
{
    /**
     * Transform an object to an array. Returns an empty array if parameter cannot be array-ified.
     * @param mixed $mixed The object to transform.
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

    /**
     * Check if parameter is an associative array.
     * @param mixed $mixed An object.
     * @return bool
     */
    public static function isAssoc( $mixed )
    {
        if ( !is_array( $mixed ) )
        {
            return false;
        }

        return array_keys( $mixed ) !== range(0, count( $mixed ) - 1);
    }

    /**
     * Compare two arrays and return keys that have different values.
     * @param array $arr1 An array to compare.
     * @param array $arr2 Another array to compare.
     * @param array|null $fields Optional: What keys to compare.
     * @return array
     */
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

    /**
     * Get a value from an array.
     * @param array $arr The array to get the value from.
     * @param int|string $key A key to the value, can be nested ('depth1.depth2.value3').
     * @return mixed|null
     */
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

    /**
     * Recursively get all keys of an array.
     * @param array $arr The array.
     * @param string $prefix Optional: Previous key, used in the recursion.
     * @return array
     */
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
