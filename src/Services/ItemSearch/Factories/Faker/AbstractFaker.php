<?php

namespace IO\Services\ItemSearch\Factories\Faker;

abstract class AbstractFaker
{
    public $isList = false;
    public $range = [1,2];

    private $uniques = [];

    public abstract function fill($data);

    protected function merge(&$object, $defaults)
    {
        foreach($defaults as $key => $defaultValue)
        {
            if ( is_array($defaultValue) && is_array($object[$key]) )
            {
                $this->merge($object[$key], $defaultValue);
            }
            else
            {
                $object[$key] = $defaultValue;
            }
        }
    }

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

    protected function number($min = 0, $max = -1)
    {
        if ( $max < $min )
        {
            $max = 99999;
        }

        return rand($min, $max);
    }

    protected function uniqueNumber($min = 0, $max = -1)
    {
        return $this->unique(function() use ($min, $max) {
            return $this->number($min, $max);
        });
    }

    protected function boolean()
    {
        return rand() % 2 === 0;
    }

    protected function rand($values)
    {
        $index = $this->number(0, count($values));
        return $values[$index];
    }

    protected function timestamp()
    {
        return rand(0, time());
    }

    protected function dateString($format = "Y-m-d H:i:s")
    {
        return date($format, $this->timestamp());
    }

    private function unique(\Closure $generator)
    {
        $value = $generator();
        while(in_array($value, $this->uniques))
        {
            $value = $generator();
        }

        return $value;
    }
}