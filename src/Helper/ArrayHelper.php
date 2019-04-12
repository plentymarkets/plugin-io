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

    public static function diff( $arr1, $arr2 )
    {
        return array_udiff_assoc( $arr1, $arr2, function($val1, $val2) {
            if ( is_array($val1) )
            {
                if ( is_array($val2) )
                {
                    return count(self::diff( $val1, $val2 ));
                }
                else
                {
                    return -1;
                }
            }
            else if ( $val1 < $val2 )
            {
                return -1;
            }
            else if ( $val1 > $val2 )
            {
                return 1;
            }
            else
            {
                return 0;
            }
        });
    }
}