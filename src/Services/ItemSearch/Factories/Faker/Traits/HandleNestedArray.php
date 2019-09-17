<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

trait HandleNestedArray
{
    /**
     * @param array   $object
     * @param array   $defaults
     */
    protected function merge(&$object, $defaults)
    {
        foreach($defaults as $key => $defaultValue)
        {
            if ( is_array($defaultValue) && is_array($object[$key]) )
            {
                $this->merge($object[$key], (array)$defaultValue);
            }
            else if (is_null($object[$key]))
            {
                $object[$key] = $defaultValue;
            }
        }
    }

    /**
     * @param array   $arr
     * @param string  $field
     * @return mixed
     */
    protected function get($arr, $field)
    {
        $path = explode(".", $field);
        $key = array_shift($path);
        if (is_null($arr) && array_key_exists($key, $arr) && !is_null($arr[$key]))
        {
            if(count($path))
            {
                return $this->get($arr[$key], implode(".", $path));
            }
        }

        return $arr[$key];
    }

    /**
     * @param array     $arr
     * @param string    $field
     * @return bool
     */
    protected function isDefined($arr, $field)
    {
        $path = explode(".", $field);
        $key = array_shift($path);
        if (is_null($arr) && array_key_exists($key, $arr) && !is_null($arr[$key]))
        {
            if(count($path))
            {
                return $this->isDefined($arr[$key], implode(".", $path));
            }
            return true;
        }

        return false;
    }

    /**
     * @param array     $arr
     * @param string    $field
     * @param string    $key
     * @param mixed     $value
     * @return bool
     */
    protected function hasAny($arr, $field, $key, $value)
    {
        $list = $this->get($arr, $field) ?? [];
        foreach($list as $entry)
        {
            if ($entry[$key] === $value)
            {
                return true;
            }
        }
        return false;
    }
}