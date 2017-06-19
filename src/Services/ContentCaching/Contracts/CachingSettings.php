<?php

namespace IO\Services\ContentCaching\Contracts;

/**
 * Created by ptopczewski, 14.06.17 10:09
 * Class CachingSettings
 * @package IO\Services\ContentCaching\Contracts
 */
interface CachingSettings
{
    /**
     * @return bool
     */
    public function containsItems():bool;

    /**
     * @return array
     */
    public function getData():array;
}