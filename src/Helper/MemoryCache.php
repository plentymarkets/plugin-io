<?php

namespace IO\Helper;

trait MemoryCache
{
    private static $cache = [];

    protected function fromMemoryCache( $key, \Closure $callack )
    {
        if (!array_key_exists(self::class, self::$cache))
        {
            self::$cache[self::class] = [];
        }

        if ( !array_key_exists( $key, self::$cache[self::class] ) )
        {
            self::$cache[self::class][$key] = $callack->call($this);
        }

        return self::$cache[self::class][$key];
    }
}