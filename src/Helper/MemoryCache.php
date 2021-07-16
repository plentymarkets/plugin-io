<?php

namespace IO\Helper;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Log\Loggable;

/**
 * Trait MemoryCache
 *
 * This trait allows a class to store the result of expensive operations in a static cache.
 * This type of caching has some limitations:
 * - The cache only lasts for the duration of the request. Please use other methods for more long term persistence of data.
 * - Each implementing class can only access their own cache.
 * - The cache is static, meaning all instances of a class access the same cache.
 *
 * @package IO\Helper
 */
trait MemoryCache
{
    use Loggable;

    private static $cache = [];
    private static $request;

    /**
     * Store the result of a operation in the memory cache.
     * @param string $key A cache key to store result under.
     * @param \Closure $callack The operation of which the result is to be stored.
     * @return mixed
     */
    protected function fromMemoryCache($key, \Closure $callack)
    {
        if (!array_key_exists(self::class, self::$cache)) {
            self::$cache[self::class] = [];
        }

        if (!array_key_exists($key, self::$cache[self::class])) {
            $start = microtime(true);
            self::$cache[self::class][$key] = $callack->call($this);
            /** @var Request $request */
            $request = pluginApp(Request::class);
            if($request->get('debug') === 'performance') {
                $this->getLogger(__CLASS__)->error('MemoryCache: ' . self::class . '.' . $key . ': ' . (microtime(true) - $start));
            }
        }

        return self::$cache[self::class][$key];
    }

    /**
     * Reset a key or the whole memory cache for the current class.
     * @param string|null $key A cache key to delete. If null, delete the whole cache.
     */
    protected function resetMemoryCache($key = null)
    {
        if(is_null($key))
        {
            self::$cache[self::class] = [];
        }
        else
        {
            unset(self::$cache[self::class][$key]);
        }
    }
}
