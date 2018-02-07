<?php

namespace IO\Helper;

trait MemoryCache
{
    private static $cache = [];

    protected function fromMemoryCache( $key, \Closure $callack )
    {
        $cacheKey = self::class . "::" . $key;
        if ( !array_key_exists( $cacheKey, self::$cache ) )
        {
            self::$cache[$cacheKey] = $callack->call($this);
        }

        return self::$cache[$cacheKey];
    }
}