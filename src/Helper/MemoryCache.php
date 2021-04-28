<?php

namespace IO\Helper;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Log\Loggable;

trait MemoryCache
{
    use Loggable;

    private static $cache = [];
    private static $request;

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
