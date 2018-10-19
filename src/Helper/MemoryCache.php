<?php

namespace IO\Helper;

trait MemoryCache
{
    private static $cache = [];

    protected function fromMemoryCache( $key, \Closure $callack )
    {
        /*

         TODO:
         Make this dirty workaround obsolete by implementing a memory cache without using static stuff.
         Use the service container and register the class as singleton. Maybe develop a memory cache in
         the core codebase and make it available in the interface.

        */
        if (getenv('APP_ENV') === 'testing') {
            return $callack->call($this);
        }

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