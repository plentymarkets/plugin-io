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
}