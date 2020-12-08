<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

trait HandleNestedArray
{
    /**
     * Recursively merge values into an existing array
     *
     * @param array   $object   The original object to merge values into
     * @param array   $defaults The values to be merged into the original object if not set
     */
    protected function merge(&$object, $defaults)
    {
        if(is_array($defaults))
        {
            foreach((array)$defaults as $key => $defaultValue)
            {
                if ( is_array($defaultValue) && is_array($object[$key]) )
                {
                    $this->merge($object[$key], (array)$defaultValue);
                }
                else if (is_null($object[$key]) || $object[$key] === 0)
                {
                    $object[$key] = $defaultValue;
                }
            }
        }
    }

    /**
     * Get a nested value from an array by its path (e.g. "path.to.property")
     *
     * @param array   $arr      The array to get the nested value from
     * @param string  $field    The path to the property to read
     *
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
     * Check if a nested property is defined in an array
     *
     * @param array     $arr    The array to search the nested property in
     * @param string    $field  The path to the property to check
     *
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
     * Check if any entry of a list has a specific value.
     *
     * @param array     $arr    The object containing the list to check entries in
     * @param string    $field  The path to the list inside the object to check entries
     * @param string    $key    Key inside each list entry to check
     * @param mixed     $value  Expected value of each entry
     *
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