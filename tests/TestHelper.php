<?php

namespace IO\Tests;

trait MemoryCache
{
    protected function fromMemoryCache($key, \Closure $callack)
    {
        return $callack->call($this);
    }

    protected function resetMemoryCache($key = null)
    {
        return;
    }
}